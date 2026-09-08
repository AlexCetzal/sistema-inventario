<?php
/**
 * carga de trabajadores iniciales 
 * usa este comando: php seed_trabajadores.php
 */

if (php_sapi_name() !== 'cli'){
    http_response_code(403);
    exit('Estes script solo se puede ejecutar desde la linea de comandos');
}

require_once __DIR__ . '/db.php';

$trabajadores = [

    /**----------------------------------
     * ADMINISTRACIÓN
     * ----------------------------------*/
    ['JORGE CARLOS MONTALVO COBO', 'Administración', 'Director de Corporativo', 'activo'],
    ['ADRIANA MARGARITA IZQUIERDO RODRIGUEZ', 'Administración', 'Gerente Administración y Finanzas', 'activo'],
    ['FERNANDO GABRIEL RODRIGUEZ SANSORES', 'Administración', 'Gerente de Fiscal y Jurídico', 'activo'],
    ['LETICIA CAROLINA ACEVEDO GOMEZ', 'Administración', 'Jefe Administrativo de Corporativo', 'activo'],
    ['ANDRES FRANCISCO DZIB GALERA', 'Administración', 'Jefe de Finanzas', 'activo'],
    ['AURY ESTEFANIA VARGUEZ GUERRERO', 'Administración', 'Jefe Administrativo', 'activo'],
    ['NELSY MARISSA UC PACHECO', 'Administración', 'Líder Proyecto', 'activo'],

    /**----------------------------------
     * TESORERÍA
     * ----------------------------------*/
    ['DIEGO ALEJANDRO SERRANO HERNANDEZ', 'Tesorería', 'Jefe de Tesorería', 'activo'],
    ['PALOMA DEL CARMEN CAMARA SALAS', 'Tesorería', 'Analista de Pagos', 'activo'],
    ['VERONICA DE JESUS SULU CANCHE', 'Tesorería', 'Registro y Control de Bancos', 'activo'],
    ['RIGEL ENRIQUE CANUL TZUC', 'Tesorería', 'Auxiliar de Tesorería', 'activo'],
    ['ANEL CRISTINA HOYOS CERVERA', 'Tesorería', 'Cajera', 'activo'],
    ['EDWIN DANIEL AKE POOT', 'Tesorería', 'Becario de Tesorería', 'activo'],
    ['KRISTHIAN HIRAM GOMEZ NEGROE', 'Tesorería', 'Diligenciero', 'activo'],

    /**----------------------------------
     * COBRANZA
     * ----------------------------------*/
    ['CATHERIN ELIZABETH MENDOZA VARGAS', 'Cobranza', 'Jefa de Cobranza', 'activo'],
    ['GLENDY NOEMI ITZINCAB CETINA', 'Cobranza', 'Auxiliar de Cobranza', 'activo'],

    /**----------------------------------
     * RECURSOS HUMANOS
     * ----------------------------------*/
    ['KARLA BERENICE PALOMO CIME', 'Recursos Humanos', 'Jefe de Recursos Humanos Corporativo', 'activo'],
    ['SHEYLA LUCIA RODRIGUEZ MENDEZ', 'Recursos Humanos', 'Encargada de Atracción y Desarrollo de Talento', 'activo'],
    ['VERONICA DEL CARMEN ESQUIVEL JESUS', 'Recursos Humanos', 'Nominista', 'activo'],
    ['ALVARO GOMEZ LARA', 'Recursos Humanos', 'Auxiliar de Recursos Humanos', 'activo'],
    ['BRUNO FERNANDO RAMIREZ CARAPIA', 'Recursos Humanos', 'Becario de Recursos Humanos', 'activo'],

    /**----------------------------------
     * RECURSOS MATERIALES
     * ----------------------------------*/
    ['DANIEL FERNANDO CASTRO PECH', 'Recursos Materiales', 'Coordinador de Recursos Materiales', 'activo'],
    ['JOSÉ GUADALUPE BE ACOSTA', 'Recursos Materiales', 'Diligenciero', 'activo'],
    ['VICTOR MANUEL PEREZ KU', 'Recursos Materiales', 'Intendente', 'activo'],
    ['MARIBEL DE JESUS GRAJALES MARTINEZ', 'Recursos Materiales', 'Intendente', 'activo'],
    ['CARMELO MATU HUCHIN', 'Recursos Materiales', 'Vigilante', 'activo'],
    ['ITZA ANDREA ZAPATA MAYA', 'Recursos Materiales', 'Recepcionista', 'activo'],

    /**----------------------------------
     * BANCO DESUR
     * ----------------------------------*/
    ['FABIOLA MORALES MORALES', 'Banco DESUR', 'Encargado Créditos Corporativos', 'activo'],
    ['LEYDI VERONICA CHAN BALAM', 'Banco DESUR', 'Auxiliar de Créditos', 'activo'],

    /**----------------------------------
     * TECNOLOGÍAS DE LA INFORMACIÓN
     * ----------------------------------*/
    ['MARIA VALERIA SAUL CUEVAS', 'Departamento TI', 'Líder TI', 'activo'],
    ['REINALDO ARTURO CHAN PEREIRA', 'Departamento TI', 'Encargado de TI', 'activo'],
    ['ERICK VAZQUEZ CASTILLA', 'Departamento TI', 'Auxiliar de TI', 'activo'],

    // ESPECIALISTA TI - VACANTE
    // No se agrega porque no hay trabajador asignado.

    /**----------------------------------
     * FISCAL
     * ----------------------------------*/
    ['BRIANDA ERNILDA POOT RAMOS', 'Departamento Fiscal', 'Supervisor Fiscal', 'activo'],
    ['MARGARITA CANUL PUC', 'Departamento Fiscal', 'Contador', 'activo'],
    ['REANDY ENRIQUE ISRAEL FARFAN GOMEZ', 'Departamento Fiscal', 'Supervisor Fiscal', 'activo'],
    ['CINTHIA NAYELI CHALE PECH', 'Departamento Fiscal', 'Contador', 'activo'],
    ['LUIS FERNANDO HEREDIA VAZQUEZ', 'Departamento Fiscal', 'Contador', 'activo'],
    ['CHRISTIAN ASAEL SOBERANIS SONDA', 'Departamento Fiscal', 'Contador', 'activo'],

    /**----------------------------------
     * JURÍDICO
     * ----------------------------------*/
    ['YULEANA AMAPOLA GARCIA TORRES', 'Departamento Jurídico', 'Jefa de Legal', 'activo'],
    ['ROSY MARY CARRILLO TUYUB', 'Departamento Jurídico', 'Auxiliar Legal', 'activo'],
    ['MERLINA GUADALUPE MENDEZ EK', 'Departamento Jurídico', 'Auxiliar Legal', 'activo'],
    ['ZOE PAMELA POSAN JAIMES', 'Departamento Jurídico', 'Becario de Legal', 'activo'],

    /**----------------------------------
     * DIRECCIÓN
     * ----------------------------------*/
    ['MARIA ISABEL MATU CHACON', 'Departamento Dirección', 'Asistente de Dirección', 'activo'],
    ['MARIA PATRICIA CANCHE EK', 'Departamento Dirección', 'Asistente de Dirección', 'activo'],
    ['JUAN DIEGO CHI POOT', 'Departamento Dirección', 'Chofer Dirección', 'activo'],

    /**----------------------------------
     * AUDITORÍA
     * ----------------------------------*/
    ['ITZEL GUADALUPE MORALES BACELIS', 'Departamento Auditoría', 'Auxiliar de Auditoría', 'activo'],

     /**----------------------------------
     * UNE VIVIENDA
     * ----------------------------------*/
     ['MONTALVO VALES RAUL', 'Une Vivienda', 'DIRECTOR DE VIVIENDA Y LOTES', 'activo'],
     ['MASSA PEREZ ENRIQUE MANUEL', 'Une Vivienda', 'GERENTE OPERACIÓN DE VIVIENDA', 'activo'],
     ['CANTO EK CINTHYA JAZMIN', 'Une Vivienda', 'PROJECT MANAGER VIVIENDA', 'activo'],
     ['MEJIA BRAGA DIONNE DEL CARMEN', 'Une Vivienda', 'PROJECT MANAGER VIVIENDA', 'activo'],
     ['EK TORRES BENITA MONSSERRAT', 'Une Vivienda', 'PROJECT MANAGER VIVIENDA', 'activo'],
     ['CHAN CANUL SANDRA DEL CARMEN', 'Une Vivienda', 'AUXILIAR OPERATIVO DE VIVIENDA', 'activo'],
     ['QUINTAL LOPEZ ANA YADIRA', 'Une Vivienda', 'JEFE ADMINISTRATIVO DE VIVIENDA', 'activo'],
     ['UH UC JOSE ABELARDO', 'Une Vivienda', 'JEFE ADMINISTRATIVO DE VIVIENDA', 'activo'],
     ['GONZALEZ GIL STEFANIA ALEJANDRA', 'Une Vivienda', 'JEFE ADMINISTRATIVO DE VIVIENDA', 'activo'],
     ['PERALTA OCAMPO ALEXIS', 'Une Vivienda', 'PROJECT MANAGER VIVIENDA', 'activo'],
     ['PEREZ CHI FANNY DIANELLY', 'Une Vivienda', 'POST VENTA', 'activo'],
     ['HOMA CARREON CYNTHIA DONAJI', 'Une Vivienda', 'ABOGADA', 'activo'],
     ['FLORES VARGUEZ MARIA JOSE', 'Une Vivienda', 'AUXILIAR LEGAL', 'activo'],
     /**----------------------------------
     * DECA
     * ----------------------------------*/
     ['MONTALVO VALES MAURICIO', 'DECA', 'DIRECTOR DE BANCO DE TIERRA', 'activo'],
     ['CAMACHO ROSADO ALVARO', 'DECA', 'GERENTE DE OPERACIÓN DE BANCO DE TIERRA', 'activo'],
     ['CERVANTES ZALDIVAR GENY DANEYRA', 'DECA', 'JEFE ADMINISTRATIVO DE BANCO DE TIERRA', 'activo'],
     ['SANCHEZ NADAL LEIRA ISIEL', 'DECA', 'PROJECT MANAGER DE BANCO DE TIERRA', 'activo'],
     ['RAMAYO CETINA GABRIELA NAYELI', 'DECA', 'ARQUITECTO URBANISTA', 'activo'],
     ['LEON CAB MARTHA PATRICIA', 'DECA', 'AUXILIAR LEGAL', 'activo'],
     ['SOLIS SOSA ENMANUEL ANTONIO', 'DECA', 'ARQUITECTO DIBUJANTE', 'activo'],
     ['VACANTE', 'DECA', 'ABOGADA', 'activo'],
     /**----------------------------------
     * UNE ARQUITECTURA
     * ----------------------------------*/
     ['GONZALEZ VALES MIGUEL ANGEL', 'UNE Arquitectura', 'DIRECTOR DE ARQUITECTURA', 'activo'],
     ['ALDAMA SALVATIERRA LEONEL ENRIQUE', 'UNE Arquitectura', 'JEFE DE TALLER DE ARQUITECTURA', 'activo'],
     ['GOMEZ MONTALVO DANTE CUAUHTEMOC', 'UNE Arquitectura', 'ARQUITECTO PROYECTISTA', 'activo'],
     ['ROSAS CABALLERO JULIO AGUSTIN', 'UNE Arquitectura', 'ARQUITECTO PROYECTISTA', 'activo'],
     ['CONTRERAS LEON SERGIO EDUARDO', 'UNE Arquitectura', 'SUPERVISOR ARQUITECTONICO', 'activo'],
     ['YAM AYIM JESUS ANDRES', 'UNE Arquitectura', 'ARQUITECTO RENDERISTA', 'activo'],
     ['XOOL UC JOSE EDGARDO', 'UNE Arquitectura', 'ARQUITECTO DIBUJANTE', 'activo'],
     ['SANCHEZ VAZQUEZ EDGAR', 'UNE Arquitectura', 'ARQUITECTO DIBUJANTE', 'activo'],
     ['RIVERO SANTOS DANIEL JESUS', 'UNE Arquitectura', 'ARQUITECTO DIBUJANTE', 'activo'],
     ['LORIA POOL ROCIO DEL MAR', 'UNE Arquitectura', 'ARQUITECTO DIBUJANTE', 'activo'],
     ['GONZALEZ ARRIGUNAGA MIGUEL ANGEL', 'UNE Arquitectura', 'BECARIO DE ARQUITECTURA', 'activo'],
     ['NAVARRETE RODRIGUEZ GUILLERMO', 'UNE Arquitectura', 'BECARIO DE ARQUITECTURA', 'activo'],
     /**----------------------------------
     * ADMON ADMINISTRADOS
     * ----------------------------------*/
     ['BURGOS MENA SAMUEL YAIR', 'ADMON Administrados', 'JEFE ADMINISTRATIVO DE PROYECTOS', 'activo'],
     /**----------------------------------
     * SUPERVISION DE OBRAS 
     * ----------------------------------*/
     ['HERRERA CANTO KENNY FERNANDO', 'Supervision de obras', 'JEFE SUPERVISION DE OBRA', 'activo'],
     ['PERAZA ZAPATA JESUS EDUARDO', 'Supervision de obras', 'SUPERVISOR DE OBRA', 'activo'],
     ['HERNANDEZ QUINTAL JESSICA ISABEL', 'Supervision de obras', 'INGENIERA DE COSTOS', 'activo'],
     /**----------------------------------
     * UNE VENTAS 
     * ----------------------------------*/
     ['ARREDONDO HEDE MARISOL', 'Une ventas', 'GERENTE COMERCIAL', 'activo'],
     ['LUNA ROSALES ULISES', 'Une ventas', 'COORDINADOR DE MARKETING', 'activo'],
     ['KUMUL TUN SANDY CRISTINA', 'Une ventas', 'DISEÑADOR GRAFICO', 'activo'],
     ['RODRIGUEZ RODRIGUEZ RUTH MARISELA', 'Une ventas', 'COORDINADOR DE VENTAS', 'activo'],
     ['MONGE YAMA ITZAYANA LIBERTAD', 'Une ventas', 'COORDINADOR DE VENTAS PDC', 'activo'],
     ['NEGRETE HERNANDEZ LAURA ALEJANDRA', 'Une ventas', 'EJECUTIVO DE VENTAS', 'activo'],
     ['LARA MACHADO MARISOL', 'Une ventas', 'EJECUTIVO DE VENTAS VIP', 'activo'],
     ['CHARLES ZARAZUA ANEYDY', 'Une ventas', 'EJECUTIVO DE VENTAS INDIRECTAS', 'activo'],
     ['OSORIO BURGOS ADRIAN GIOVANNY', 'Une ventas', 'EJECUTIVO DE VENTAS', 'activo'],

];


$pdo = get_db();
$stmt = $pdo->prepare(
    "INSERT INTO trabajadores(nombre, area, puesto, status)
    VALUES (?,?,?,?)"
);

foreach ($trabajadores as $trabajador){
    $stmt->execute($trabajador);
}

echo "Trabajadores cargados correctamente. \n";