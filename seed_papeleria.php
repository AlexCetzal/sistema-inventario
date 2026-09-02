<?php
/**
 * Carga el catálogo REAL de papelería (a partir del Excel "Inv Papeleria RM 2.xlsx")
 * y reemplaza los materiales de categoría "oficina" que hubiera antes (los de
 * "limpieza" no se tocan).
 *
 * Qué hace, en orden:
 *   1. Pide confirmación (no se puede deshacer).
 *   2. Borra TODAS las solicitudes (los folios se reinician desde 1) -- las
 *      solicitudes viejas apuntaban a materiales que están a punto de
 *      desaparecer, así que no tendría sentido conservarlas.
 *   3. Borra los materiales de categoría "oficina" que hubiera (de pruebas).
 *   4. Inserta los 139 artículos reales del Excel.
 *
 * Valores que NO venían en el Excel y se rellenaron con un supuesto (ajústalos
 * después desde el panel, en "Editar", para los artículos que lo necesiten):
 *   - unidad: se puso "pza" (pieza) para todos. Si algo se maneja distinto
 *     (por rollo, por litro, por paquete, etc.) cámbialo ahí.
 *   - nivel mínimo / nivel máximo: no venían en el Excel, así que se calcularon
 *     con una fórmula simple: máximo = 1.5 veces tu existencia actual (o 15 si
 *     estaba en cero), mínimo = 20% de ese máximo. Son solo un punto de partida
 *     para que la barra de existencias se vea razonable desde el día uno --
 *     revísalos con calma y corrígelos donde tengas mejor idea del consumo real.
 *
 * Uso: php seed_papeleria.php
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('Este script solo se puede ejecutar desde la línea de comandos.');
}

require_once __DIR__ . '/db.php';

// clave, nombre, categoria, unidad, stock, stock_min, stock_max
$materiales = [
    ['bpp_05', 'Berol Puntillas p 0.5 HB (12pzas)', 'oficina', 'pza', 44, 13, 66],
    ['blq_tcl', 'Block Tamaño Carta e Líneas', 'oficina', 'pza', 5, 3, 15],
    ['bpf_aaz', 'Boligrafo Punta Fina Azor Azul', 'oficina', 'pza', 94, 28, 141],
    ['bpf_an', 'Boligrafo Punta Fina Azor Negro', 'oficina', 'pza', 108, 32, 162],
    ['bpf_ar', 'Boligrafo Punta Fina Azor Rojo', 'oficina', 'pza', 96, 29, 144],
    ['bpm_baz', 'Boligrafo Punta Mediana Bic Azul', 'oficina', 'pza', 2, 3, 15],
    ['bpm_bn', 'Boligrafo Punta Mediana Bic Negro', 'oficina', 'pza', 23, 7, 34],
    ['bpm_pma', 'Boligrafo Punta Mediana Paper Mate Azul', 'oficina', 'pza', 3, 3, 15],
    ['br_piz', 'Borrador para pizarron', 'oficina', 'pza', 3, 3, 15],
    ['brc_nxt', 'Broche Nextep', 'oficina', 'pza', 0, 3, 15],
    ['bro_smty', 'Brochas Smarty', 'oficina', 'pza', 30, 9, 45],
    ['cps_ofam', 'Carpeta con palanca Smart File oficio Amarilla', 'oficina', 'pza', 1, 3, 15],
    ['cps_ofaz', 'Carpeta con palanca Smart File oficio Azul', 'oficina', 'pza', 0, 3, 15],
    ['cps_ofm', 'Carpeta con palanca Smart File oficio Morada', 'oficina', 'pza', 0, 3, 15],
    ['cps_ofn', 'Carpeta con palanca Smart File oficio Negra', 'oficina', 'pza', 0, 3, 15],
    ['cps_ofr', 'Carpeta con palanca Smart File oficio Roja', 'oficina', 'pza', 0, 3, 15],
    ['cps_ofv', 'Carpeta con palanca Smart File oficio Verde', 'oficina', 'pza', 0, 3, 15],
    ['cj_adh', 'Cinta Adhesiva Janel', 'oficina', 'pza', 14, 4, 21],
    ['cj_can', 'Cinta Canela Janel', 'oficina', 'pza', 11, 3, 16],
    ['cs_mag', 'Cinta Magica Scotch', 'oficina', 'pza', 12, 4, 18],
    ['cin_sco', 'Cintha Scoutch', 'oficina', 'pza', 0, 3, 15],
    ['crr_lin', 'Corrector Dryline Grip', 'oficina', 'pza', 8, 3, 15],
    ['cuadf_jb', 'Cuaderno Francesa Norma JeanBook', 'oficina', 'pza', 2, 3, 15],
    ['cuadp_jb', 'Cuaderno Profesional Norma JeanBook', 'oficina', 'pza', 10, 3, 15],
    ['eng_fifa', 'Engrapadora FIFA Pilot', 'oficina', 'pza', 0, 3, 15],
    ['eng_peg', 'Engrapadora Pegaso', 'oficina', 'pza', 0, 3, 15],
    ['et_env4', 'Etiquetas de Envio Avery 4" x 3 1/3"', 'oficina', 'pza', 0, 3, 15],
    ['et_env8', 'Etiquetas de Envio Avery 8 1/2" x 11"', 'oficina', 'pza', 0, 3, 15],
    ['fol_tc', 'Folder Apsa Tamaño Carta Azul Rojo', 'oficina', 'pza', 0, 3, 15],
    ['folc_oxf', 'Folder colgante Oxford Tam. Carta', 'oficina', 'pza', 7, 3, 15],
    ['folc_wil', 'Folder colgante Wilson Jones paq.', 'oficina', 'pza', 0, 3, 15],
    ['fol_mptcaz', 'Folder MAPASA Tamaño Carta Azul paq.', 'oficina', 'pza', 0, 3, 15],
    ['fol_mptcam', 'Folder MAPASA Tamaño Carta Crema paq', 'oficina', 'pza', 3, 3, 15],
    ['fol_nxtcam', 'Folder Nextep Tamaño Carta Azul Marino paq.', 'oficina', 'pza', 0, 3, 15],
    ['fol_nxtcc', 'Folder Nextep Tamaño Carta Crema paq.', 'oficina', 'pza', 0, 3, 15],
    ['fol_tovc', 'Folder Tamaño Oficio Variedad de colores', 'oficina', 'pza', 0, 3, 15],
    ['fdcder', 'Formato de Declaración General de Derechos (50 pzas)', 'oficina', 'pza', 0, 3, 15],
    ['gobo_m20', 'Goma de borrar Migajon M20', 'oficina', 'pza', 0, 3, 15],
    ['gr_fifa', 'Grapa FIFAS 3/8"', 'oficina', 'pza', 2, 3, 15],
    ['gr_nxt', 'Grapas Nextep', 'oficina', 'pza', 8, 3, 15],
    ['hs_ots', 'Hoja de Seguridad Oak Tree Safety (50pzas)', 'oficina', 'pza', 16, 5, 24],
    ['hb_hpp', 'Hojas de papel en Blanco HP Tam Carta paquete', 'oficina', 'pza', 0, 3, 15],
    ['hb_tof', 'Hojas de papel en blanco Tam. Oficio paquete', 'oficina', 'pza', 0, 3, 15],
    ['hb_tofam', 'Hojas de papel en blanco Tam. Oficio Americano paquete', 'oficina', 'pza', 1, 3, 15],
    ['htc_col', 'Hojas de papel Tam Carta de colores (pzas)', 'oficina', 'pza', 0, 3, 15],
    ['hop_tc', 'Hojas de Papel Opalina Tam Carta (100 pzas)', 'oficina', 'pza', 0, 3, 15],
    ['hop_to', 'Hojas de Papel Opalina Tam Oficio (100 pzas)', 'oficina', 'pza', 0, 3, 15],
    ['lap_pmhb2', 'Lapiz Paper Mate HB2', 'oficina', 'pza', 33, 10, 50],
    ['lap_bi', 'Lapiz Bicolor Azul/Rojo', 'oficina', 'pza', 0, 3, 15],
    ['lap_pnt', 'Lapiz Zebra de puntilla 0.7', 'oficina', 'pza', 12, 4, 18],
    ['lef_tof', 'Lefort Verde tamaño Oficio', 'oficina', 'pza', 0, 3, 15],
    ['lib_a48', 'Libro Flerete Para Actas MR Estrella 48 hojas', 'oficina', 'pza', 1, 3, 15],
    ['lib_a96', 'Libro Flerete Para Actas MR Estrella 96 hojas', 'oficina', 'pza', 12, 4, 18],
    ['lib_o96', 'Libro Flerete Para Obra MR Estrella 96 hojas', 'oficina', 'pza', 0, 3, 15],
    ['lg_hua', 'Ligas de Hule Aguila (paq)', 'oficina', 'pza', 0, 3, 15],
    ['lp_piz', 'Limpia pizarron', 'oficina', 'pza', 1, 3, 15],
    ['key_col', 'Llavero Identificador Colores Surtidos.', 'oficina', 'pza', 0, 3, 15],
    ['mpa_1pv', 'Marcador Permanente Azor Verde 1P', 'oficina', 'pza', 1, 3, 15],
    ['mpa_2p', 'Marcador Permanente Azor Negro 2P', 'oficina', 'pza', 1, 3, 15],
    ['mps_pc', 'Marcador permanente Negro Punta Cincel', 'oficina', 'pza', 0, 3, 15],
    ['mps_a1p', 'Marcadores permanentes Sharpie Azul 1P', 'oficina', 'pza', 12, 4, 18],
    ['mps_n1p', 'Marcadores permanentes Sharpie Negro 1P', 'oficina', 'pza', 1, 3, 15],
    ['mps_a2p', 'Marcadores permanentes Sharpie Azul 2P', 'oficina', 'pza', 22, 7, 33],
    ['mps_n2p', 'Marcadores permanentes Sharpie Negro 2P', 'oficina', 'pza', 1, 3, 15],
    ['mps_r2p', 'Marcadores permanentes Sharpie Rojo 2P', 'oficina', 'pza', 0, 3, 15],
    ['mpe_ag', 'Marcadores permanentes Esterbrook Azul', 'oficina', 'pza', 24, 7, 36],
    ['mpe_ng', 'Marcadores permanentes Esterbrook Negro', 'oficina', 'pza', 15, 4, 22],
    ['mpe_rg', 'Marcadores permanentes Esterbrook Rojo', 'oficina', 'pza', 15, 4, 22],
    ['mpurmpa', 'Marcadores Purpurina Glitter Maped Azul', 'oficina', 'pza', 0, 3, 15],
    ['mpurmpc', 'Marcadores Purpurina Glitter Maped Café', 'oficina', 'pza', 0, 3, 15],
    ['mpurmpd', 'Marcadores Purpurina Glitter Maped Dorado', 'oficina', 'pza', 0, 3, 15],
    ['mtxt_am', 'Marcatexto Florecente Nextep Amarillo', 'oficina', 'pza', 17, 5, 26],
    ['mtxt_az', 'Marcatexto florecente Nextep Azul', 'oficina', 'pza', 0, 3, 15],
    ['mtxt_nr', 'Marcatexto florecente Nextep Naranja', 'oficina', 'pza', 7, 3, 15],
    ['mtxt_vr', 'Marcatexto florecente Nextep Verde', 'oficina', 'pza', 0, 3, 15],
    ['mit_naa', 'Notas Adhesivas MemoTip Amarillas', 'oficina', 'pza', 6, 3, 15],
    ['op_tcp', 'Papel Opalina Tam Carta Premium', 'oficina', 'pza', 0, 3, 15],
    ['op_tomr', 'Papel Opalina Tam Oficio MR DIEM', 'oficina', 'pza', 0, 3, 15],
    ['pcar_tc', 'Papel Carbón tam Carta 100 pzas', 'oficina', 'pza', 0, 3, 15],
    ['pmm_lpz', 'Paper Mate Mirado Lapiz Grafito', 'oficina', 'pza', 0, 3, 15],
    ['pmp_05', 'Paper Mate Puntillas p 0.5 HB', 'oficina', 'pza', 0, 3, 15],
    ['peg_p11', 'Pegamento Printt 11g', 'oficina', 'pza', 8, 3, 15],
    ['peg_p22', 'Pegamento Printt 22g', 'oficina', 'pza', 0, 3, 15],
    ['peg_p42', 'Pegamento Printt 42g', 'oficina', 'pza', 1, 3, 15],
    ['p2o_nxt', 'Perforadora 2 Orificios Nextep', 'oficina', 'pza', 0, 3, 15],
    ['p2o_peg', 'Perforadora 2 Orificios Pegaso', 'oficina', 'pza', 0, 3, 15],
    ['pines_pq', 'Pines Pelikan 100pzas', 'oficina', 'pza', 0, 3, 15],
    ['pin_smty', 'Pincel Smarty', 'oficina', 'pza', 46, 14, 69],
    ['pint_paa', 'Pintura Acrilica Azul', 'oficina', 'pza', 10, 3, 15],
    ['pint_paa_2', 'Pintura Acrilica Amarilla', 'oficina', 'pza', 10, 3, 15],
    ['pint_paa_3', 'Pintura Acrilica Negro', 'oficina', 'pza', 10, 3, 15],
    ['pint_paa_4', 'Pintura Acrilica Rojo', 'oficina', 'pza', 10, 3, 15],
    ['pint_paa_5', 'Pintura Acrilica Verde', 'oficina', 'pza', 10, 3, 15],
    ['pac_it', 'Plumon Azor Check it', 'oficina', 'pza', 0, 3, 15],
    ['plp_pq', 'Plumones Pizarrón paquete', 'oficina', 'pza', 0, 3, 15],
    ['port_mnxt', 'Portalapiz Metal Nextep', 'oficina', 'pza', 0, 3, 15],
    ['pit_bam', 'Post it Banderillas Amarillas', 'oficina', 'pza', 0, 3, 15],
    ['pit_az', 'Post it Banderillas Azules', 'oficina', 'pza', 0, 3, 15],
    ['pit_rj', 'Post it Banderillas Rojas', 'oficina', 'pza', 0, 3, 15],
    ['pit_vr', 'Post it Banderillas Verdes', 'oficina', 'pza', 0, 3, 15],
    ['pit_fl', 'Post it Flecha', 'oficina', 'pza', 0, 3, 15],
    ['pitm_adhg', 'Post it MAE Nota Adhesiva Florecente Grande', 'oficina', 'pza', 0, 3, 15],
    ['pitn_adhp', 'Post it Memo tip Nota Adhesiva Pastel Pequeño', 'oficina', 'pza', 4, 3, 15],
    ['pitn_adhg', 'Post it Nextep Nota Adhesiva Florecente Grande', 'oficina', 'pza', 0, 3, 15],
    ['pitn_popp', 'Post it Notas Acordeón Pop Up Acordeon', 'oficina', 'pza', 0, 3, 15],
    ['pit_p96', 'Post it Pack Banderillas (96pzas)', 'oficina', 'pza', 0, 3, 15],
    ['pit_p140', 'Post it Pack Banderillas Flecha (140)', 'oficina', 'pza', 0, 3, 15],
    ['pitm_nam', 'Post it MemoTip Nota Adhesiva Mixtas', 'oficina', 'pza', 18, 5, 27],
    ['prh_kc', 'Protector de hoja Kinera Carta (100 pzas)', 'oficina', 'pza', 8, 3, 15],
    ['prh_ko', 'Protector de hoja Kinera Oficio (100 pzas)', 'oficina', 'pza', 2, 3, 15],
    ['prh_5o', 'Protector de hoja sueltas oficio (100 pzas)', 'oficina', 'pza', 0, 3, 15],
    ['qgr_nxt', 'Quita Grapas Nextep', 'oficina', 'pza', 0, 3, 15],
    ['qgr_ofd', 'Quita Grapas Office depot', 'oficina', 'pza', 0, 3, 15],
    ['rm_off', 'Regla Metalica Office', 'oficina', 'pza', 0, 3, 15],
    ['rm_pil', 'Regla Metalica Pilot', 'oficina', 'pza', 0, 3, 15],
    ['rmt_acme', 'Regla Metalica Triangular ACME', 'oficina', 'pza', 0, 3, 15],
    ['rp_bco', 'Regla Plastico BACO', 'oficina', 'pza', 0, 3, 15],
    ['rv_nsb', 'Revistero de plastico Negro Sablon', 'oficina', 'pza', 0, 3, 15],
    ['scp_vc', 'Sacapunta Variedad de Colores', 'oficina', 'pza', 0, 3, 15],
    ['stc_man', 'Sobres Carta Manila', 'oficina', 'pza', 200, 60, 300],
    ['stmc_man', 'Sobres Media Carta Manila', 'oficina', 'pza', 250, 75, 375],
    ['sto_man', 'Sobres Oficio Manila', 'oficina', 'pza', 100, 30, 150],
    ['stp_09', 'Staedtler Puntillas p 0.9 HB (12pzas)', 'oficina', 'pza', 0, 3, 15],
    ['sj_docs', 'Sujeta Doc NEXTEP CH', 'oficina', 'pza', 0, 3, 15],
    ['sj_docgd', 'Sujeta Doc NEXTEP GD', 'oficina', 'pza', 0, 3, 15],
    ['sj_docmd', 'Sujeta Doc NEXTEP MD', 'oficina', 'pza', 0, 3, 15],
    ['sjdoc_mn', 'Sujeta Doc NEXTEP MINI', 'oficina', 'pza', 0, 3, 15],
    ['tbs_nxt', 'Tabla con Sujetador Nextep', 'oficina', 'pza', 0, 3, 15],
    ['tij_ppl', 'Tijeras para papel', 'oficina', 'pza', 0, 3, 15],
    ['cadh_nxt', 'Cinta Adhesiva Nextep', 'oficina', 'pza', 5, 3, 15],
    ['siliq', 'Silicon Liquido', 'oficina', 'pza', 2, 3, 15],
    ['smul_05', 'Separador Mulltidex 05 Div t/carta', 'oficina', 'pza', 25, 8, 38],
    ['smul_08', 'Separador Mulltidex 08 Div t/carta', 'oficina', 'pza', 0, 3, 15],
    ['smul_10', 'Separador Mulltidex 10 Div t/carta', 'oficina', 'pza', 12, 4, 18],
    ['smul_12', 'Separador Mulltidex 12 Div t/carta', 'oficina', 'pza', 0, 3, 15],
    ['scol_15', 'Separador Colorinex C/15 Div t/carta', 'oficina', 'pza', 0, 3, 15],
    ['smul_31', 'Separador Mulltidex 31 Div t/carta', 'oficina', 'pza', 5, 3, 15],
    ['ne_042b', 'Clip Mariposa #2 40mm', 'oficina', 'pza', 15, 4, 22],
    ['ne_042a', 'Clip Mariposa #1 60mm', 'oficina', 'pza', 66, 20, 99],
];

echo "Esto borrará todas las solicitudes registradas y todos los materiales de\n";
echo "categoría \"oficina\" que hubiera ahora mismo, y los reemplazará con los " . count($materiales) . " artículos\n";
echo "reales del Excel de papelería. Los materiales de \"limpieza\" no se tocan.\n";
echo "Esta acción no se puede deshacer. ¿Continuar? (escribe 'si' para confirmar): ";
$respuesta = strtolower(trim(fgets(STDIN)));

if ($respuesta !== 'si') {
    echo "Cancelado. No se modificó nada.\n";
    exit(0);
}

$pdo = get_db();

$pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
$pdo->exec('TRUNCATE TABLE solicitudes');
$pdo->exec("DELETE FROM materiales WHERE categoria = 'oficina'");
$pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
echo "- Solicitudes borradas y materiales de oficina anteriores eliminados.\n";

$stmt = $pdo->prepare(
    "INSERT INTO materiales (clave, nombre, categoria, unidad, stock, stock_min, stock_max)
     VALUES (?, ?, ?, ?, ?, ?, ?)"
);
foreach ($materiales as $m) {
    $stmt->execute($m);
}

echo "- Se cargaron " . count($materiales) . " artículos de oficina.\n";
echo "\nListo. Ya puedes entrar al panel y revisar/ajustar unidades y niveles\n";
echo "mínimo/máximo desde \"Agregar / editar materiales\".\n";
