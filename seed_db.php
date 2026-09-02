<?php
/**
 * Carga el catálogo inicial de materiales (si no existen ya).
 * Uso: php seed_db.php
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('Este script solo se puede ejecutar desde la línea de comandos.');
}

require_once __DIR__ . '/db.php';

// clave, nombre, categoria, unidad, stock, stock_min, stock_max
$materiales = [
    ['boligrafo', 'Bolígrafos', 'oficina', 'pza', 84, 20, 150],
    ['pluma', 'Plumas de tinta', 'oficina', 'pza', 18, 20, 100],
    ['lapiz', 'Lápices', 'oficina', 'pza', 60, 15, 120],
    ['libreta', 'Libretas profesionales', 'oficina', 'pza', 24, 10, 60],
    ['postit', 'Notas adhesivas (Post-it)', 'oficina', 'paq', 9, 8, 40],
    ['folder', 'Folders tamaño carta', 'oficina', 'pza', 130, 30, 200],
    ['hojas', 'Hojas blancas', 'oficina', 'paq', 6, 6, 30],
    ['marcador', 'Marcadores para pizarrón', 'oficina', 'pza', 14, 6, 40],
    ['grapas', 'Grapas', 'oficina', 'caja', 22, 5, 40],
    ['cloro', 'Cloro', 'limpieza', 'L', 3, 6, 30],
    ['jabon', 'Jabón para manos', 'limpieza', 'pza', 16, 8, 40],
    ['papel_hig', 'Papel higiénico', 'limpieza', 'paq', 5, 10, 40],
    ['servitoalla', 'Servitoallas', 'limpieza', 'paq', 21, 10, 50],
    ['multiusos', 'Limpiador multiusos', 'limpieza', 'L', 12, 6, 30],
    ['bolsas', 'Bolsas de basura', 'limpieza', 'rollo', 28, 8, 40],
    ['desinfectante', 'Desinfectante en aerosol', 'limpieza', 'pza', 4, 5, 25],
    ['franela', 'Franelas', 'limpieza', 'pza', 33, 10, 50],
];

$pdo = get_db();
$stmt = $pdo->prepare(
    "INSERT IGNORE INTO materiales (clave, nombre, categoria, unidad, stock, stock_min, stock_max)
     VALUES (?, ?, ?, ?, ?, ?, ?)"
);
foreach ($materiales as $m) {
    $stmt->execute($m);
}

echo "Catálogo de materiales cargado.\n";
