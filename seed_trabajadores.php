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
    ['JORGE CARLOS MONTALVO COBO', 'Administración', 'Director de Corporativo', 'Activo'],
    ['ADRIANA MARGARITA IZQUIERDO RODRIGUEZ', 'Administración', 'Gerente Administración y Finanzas', 'Activo'],
    ['FERNANDO GABRIEL RODRIGUEZ SANSORES', 'Administración', 'Gerente de Fiscal y Jurídico', 'Activo'],
    ['LETICIA CAROLINA ACEVEDO GOMEZ', 'Administración', 'Jefe Administrativo de Corporativo', 'Activo'],
    ['ANDRES FRANCISCO DZIB GALERA', 'Administración', 'Jefe de Finanzas', 'Activo'],
    ['AURY ESTEFANIA VARGUEZ GUERRERO', 'Administración', 'Jefe Administrativo', 'Activo'],
    ['NELSY MARISSA UC PACHECO', 'Administración', 'Líder Proyecto', 'Activo'],

    /**----------------------------------
     * TESORERÍA
     * ----------------------------------*/
    ['DIEGO ALEJANDRO SERRANO HERNANDEZ', 'Tesorería', 'Jefe de Tesorería', 'Activo'],
    ['PALOMA DEL CARMEN CAMARA SALAS', 'Tesorería', 'Analista de Pagos', 'Activo'],
    ['VERONICA DE JESUS SULU CANCHE', 'Tesorería', 'Registro y Control de Bancos', 'Activo'],
    ['RIGEL ENRIQUE CANUL TZUC', 'Tesorería', 'Auxiliar de Tesorería', 'Activo'],
    ['ANEL CRISTINA HOYOS CERVERA', 'Tesorería', 'Cajera', 'Activo'],
    ['EDWIN DANIEL AKE POOT', 'Tesorería', 'Becario de Tesorería', 'Activo'],
    ['KRISTHIAN HIRAM GOMEZ NEGROE', 'Tesorería', 'Diligenciero', 'Activo'],

    /**----------------------------------
     * COBRANZA
     * ----------------------------------*/
    ['CATHERIN ELIZABETH MENDOZA VARGAS', 'Cobranza', 'Jefa de Cobranza', 'Activo'],
    ['GLENDY NOEMI ITZINCAB CETINA', 'Cobranza', 'Auxiliar de Cobranza', 'Activo'],

    /**----------------------------------
     * RECURSOS HUMANOS
     * ----------------------------------*/
    ['KARLA BERENICE PALOMO CIME', 'Recursos Humanos', 'Jefe de Recursos Humanos Corporativo', 'Activo'],
    ['SHEYLA LUCIA RODRIGUEZ MENDEZ', 'Recursos Humanos', 'Encargada de Atracción y Desarrollo de Talento', 'Activo'],
    ['VERONICA DEL CARMEN ESQUIVEL JESUS', 'Recursos Humanos', 'Nominista', 'Activo'],
    ['ALVARO GOMEZ LARA', 'Recursos Humanos', 'Auxiliar de Recursos Humanos', 'Activo'],
    ['BRUNO FERNANDO RAMIREZ CARAPIA', 'Recursos Humanos', 'Becario de Recursos Humanos', 'Activo'],

    /**----------------------------------
     * RECURSOS MATERIALES
     * ----------------------------------*/
    ['DANIEL FERNANDO CASTRO PECH', 'Recursos Materiales', 'Coordinador de Recursos Materiales', 'Activo'],
    ['JOSÉ GUADALUPE BE ACOSTA', 'Recursos Materiales', 'Diligenciero', 'Activo'],
    ['VICTOR MANUEL PEREZ KU', 'Recursos Materiales', 'Intendente', 'Activo'],
    ['MARIBEL DE JESUS GRAJALES MARTINEZ', 'Recursos Materiales', 'Intendente', 'Activo'],
    ['CARMELO MATU HUCHIN', 'Recursos Materiales', 'Vigilante', 'Activo'],
    ['ITZA ANDREA ZAPATA MAYA', 'Recursos Materiales', 'Recepcionista', 'Activo'],

    /**----------------------------------
     * BANCO DESUR
     * ----------------------------------*/
    ['FABIOLA MORALES MORALES', 'Banco DESUR', 'Encargado Créditos Corporativos', 'Activo'],
    ['LEYDI VERONICA CHAN BALAM', 'Banco DESUR', 'Auxiliar de Créditos', 'Activo'],

    /**----------------------------------
     * TECNOLOGÍAS DE LA INFORMACIÓN
     * ----------------------------------*/
    ['MARIA VALERIA SAUL CUEVAS', 'Departamento TI', 'Líder TI', 'Activo'],
    ['REINALDO ARTURO CHAN PEREIRA', 'Departamento TI', 'Encargado de TI', 'Activo'],
    ['ERICK VAZQUEZ CASTILLA', 'Departamento TI', 'Auxiliar de TI', 'Activo'],

    // ESPECIALISTA TI - VACANTE
    // No se agrega porque no hay trabajador asignado.

    /**----------------------------------
     * FISCAL
     * ----------------------------------*/
    ['BRIANDA ERNILDA POOT RAMOS', 'Departamento Fiscal', 'Supervisor Fiscal', 'Activo'],
    ['MARGARITA CANUL PUC', 'Departamento Fiscal', 'Contador', 'Activo'],
    ['REANDY ENRIQUE ISRAEL FARFAN GOMEZ', 'Departamento Fiscal', 'Supervisor Fiscal', 'Activo'],
    ['CINTHIA NAYELI CHALE PECH', 'Departamento Fiscal', 'Contador', 'Activo'],
    ['LUIS FERNANDO HEREDIA VAZQUEZ', 'Departamento Fiscal', 'Contador', 'Activo'],
    ['CHRISTIAN ASAEL SOBERANIS SONDA', 'Departamento Fiscal', 'Contador', 'Activo'],

    /**----------------------------------
     * JURÍDICO
     * ----------------------------------*/
    ['YULEANA AMAPOLA GARCIA TORRES', 'Departamento Jurídico', 'Jefa de Legal', 'Activo'],
    ['ROSY MARY CARRILLO TUYUB', 'Departamento Jurídico', 'Auxiliar Legal', 'Activo'],
    ['MERLINA GUADALUPE MENDEZ EK', 'Departamento Jurídico', 'Auxiliar Legal', 'Activo'],
    ['ZOE PAMELA POSAN JAIMES', 'Departamento Jurídico', 'Becario de Legal', 'Activo'],

    /**----------------------------------
     * DIRECCIÓN
     * ----------------------------------*/
    ['MARIA ISABEL MATU CHACON', 'Departamento Dirección', 'Asistente de Dirección', 'Activo'],
    ['MARIA PATRICIA CANCHE EK', 'Departamento Dirección', 'Asistente de Dirección', 'Activo'],
    ['JUAN DIEGO CHI POOT', 'Departamento Dirección', 'Chofer Dirección', 'Activo'],

    /**----------------------------------
     * AUDITORÍA
     * ----------------------------------*/
    ['ITZEL GUADALUPE MORALES BACELIS', 'Departamento Auditoría', 'Auxiliar de Auditoría', 'Activo'],

     /**----------------------------------
     * UNE VIVIENDA
     * ----------------------------------*/
     ['MONTALVO VALES RAUL', 'Une Vivienda', 'DIRECTOR DE VIVIENDA Y LOTES', 'Activo'],
     ['MASSA PEREZ ENRIQUE MANUEL', 'Une Vivienda', 'GERENTE OPERACIÓN DE VIVIENDA', 'Activo'],
     ['CANTO EK CINTHYA JAZMIN', 'Une Vivienda', 'PROJECT MANAGER VIVIENDA', 'Activo'],
     ['MEJIA BRAGA DIONNE DEL CARMEN', 'Une Vivienda', 'PROJECT MANAGER VIVIENDA', 'Activo'],
     ['EK TORRES BENITA MONSSERRAT', 'Une Vivienda', 'PROJECT MANAGER VIVIENDA', 'Activo'],
     ['CHAN CANUL SANDRA DEL CARMEN', 'Une Vivienda', 'AUXILIAR OPERATIVO DE VIVIENDA', 'Activo'],
     ['QUINTAL LOPEZ ANA YADIRA', 'Une Vivienda', 'JEFE ADMINISTRATIVO DE VIVIENDA', 'Activo'],
     ['UH UC JOSE ABELARDO', 'Une Vivienda', 'JEFE ADMINISTRATIVO DE VIVIENDA', 'Activo'],
     ['GONZALEZ GIL STEFANIA ALEJANDRA', 'Une Vivienda', 'JEFE ADMINISTRATIVO DE VIVIENDA', 'Activo'],
     ['PERALTA OCAMPO ALEXIS', 'Une Vivienda', 'PROJECT MANAGER VIVIENDA', 'Activo'],
     ['PEREZ CHI FANNY DIANELLY', 'Une Vivienda', 'POST VENTA', 'Activo'],
     ['HOMA CARREON CYNTHIA DONAJI', 'Une Vivienda', 'ABOGADA', 'Activo'],
     ['FLORES VARGUEZ MARIA JOSE', 'Une Vivienda', 'AUXILIAR LEGAL', 'Activo'],
     /**----------------------------------
     * DECA
     * ----------------------------------*/
     ['MONTALVO VALES MAURICIO', 'DECA', 'DIRECTOR DE BANCO DE TIERRA', 'Activo'],
     ['CAMACHO ROSADO ALVARO', 'DECA', 'GERENTE DE OPERACIÓN DE BANCO DE TIERRA', 'Activo'],
     ['CERVANTES ZALDIVAR GENY DANEYRA', 'DECA', 'JEFE ADMINISTRATIVO DE BANCO DE TIERRA', 'Activo'],
     ['SANCHEZ NADAL LEIRA ISIEL', 'DECA', 'PROJECT MANAGER DE BANCO DE TIERRA', 'Activo'],
     ['RAMAYO CETINA GABRIELA NAYELI', 'DECA', 'ARQUITECTO URBANISTA', 'Activo'],
     ['LEON CAB MARTHA PATRICIA', 'DECA', 'AUXILIAR LEGAL', 'Activo'],
     ['SOLIS SOSA ENMANUEL ANTONIO', 'DECA', 'ARQUITECTO DIBUJANTE', 'Activo'],
     ['VACANTE', 'DECA', 'ABOGADA', 'Activo'],
     /**----------------------------------
     * UNE ARQUITECTURA
     * ----------------------------------*/
     ['GONZALEZ VALES MIGUEL ANGEL', 'UNE Arquitectura', 'DIRECTOR DE ARQUITECTURA', 'Activo'],
     ['ALDAMA SALVATIERRA LEONEL ENRIQUE', 'UNE Arquitectura', 'JEFE DE TALLER DE ARQUITECTURA', 'Activo'],
     ['GOMEZ MONTALVO DANTE CUAUHTEMOC', 'UNE Arquitectura', 'ARQUITECTO PROYECTISTA', 'Activo'],
     ['ROSAS CABALLERO JULIO AGUSTIN', 'UNE Arquitectura', 'ARQUITECTO PROYECTISTA', 'Activo'],
     ['CONTRERAS LEON SERGIO EDUARDO', 'UNE Arquitectura', 'SUPERVISOR ARQUITECTONICO', 'Activo'],
     ['YAM AYIM JESUS ANDRES', 'UNE Arquitectura', 'ARQUITECTO RENDERISTA', 'Activo'],
     ['XOOL UC JOSE EDGARDO', 'UNE Arquitectura', 'ARQUITECTO DIBUJANTE', 'Activo'],
     ['SANCHEZ VAZQUEZ EDGAR', 'UNE Arquitectura', 'ARQUITECTO DIBUJANTE', 'Activo'],
     ['RIVERO SANTOS DANIEL JESUS', 'UNE Arquitectura', 'ARQUITECTO DIBUJANTE', 'Activo'],
     ['LORIA POOL ROCIO DEL MAR', 'UNE Arquitectura', 'ARQUITECTO DIBUJANTE', 'Activo'],
     ['GONZALEZ ARRIGUNAGA MIGUEL ANGEL', 'UNE Arquitectura', 'BECARIO DE ARQUITECTURA', 'Activo'],
     ['NAVARRETE RODRIGUEZ GUILLERMO', 'UNE Arquitectura', 'BECARIO DE ARQUITECTURA', 'Activo'],
     /**----------------------------------
     * ADMON ADMINISTRADOS
     * ----------------------------------*/
     ['BURGOS MENA SAMUEL YAIR', 'ADMON Administrados', 'JEFE ADMINISTRATIVO DE PROYECTOS', 'Activo'],
     /**----------------------------------
     * SUPERVISION DE OBRAS 
     * ----------------------------------*/
     ['HERRERA CANTO KENNY FERNANDO', 'Supervision de obras', 'JEFE SUPERVISION DE OBRA', 'Activo'],
     ['PERAZA ZAPATA JESUS EDUARDO', 'Supervision de obras', 'SUPERVISOR DE OBRA', 'Activo'],
     ['HERNANDEZ QUINTAL JESSICA ISABEL', 'Supervision de obras', 'INGENIERA DE COSTOS', 'Activo'],
     /**----------------------------------
     * UNE VENTAS 
     * ----------------------------------*/
     ['ARREDONDO HEDE MARISOL', 'Une ventas', 'GERENTE COMERCIAL', 'Activo'],
     ['LUNA ROSALES ULISES', 'Une ventas', 'COORDINADOR DE MARKETING', 'Activo'],
     ['KUMUL TUN SANDY CRISTINA', 'Une ventas', 'DISEÑADOR GRAFICO', 'Activo'],
     ['RODRIGUEZ RODRIGUEZ RUTH MARISELA', 'Une ventas', 'COORDINADOR DE VENTAS', 'Activo'],
     ['MONGE YAMA ITZAYANA LIBERTAD', 'Une ventas', 'COORDINADOR DE VENTAS PDC', 'Activo'],
     ['NEGRETE HERNANDEZ LAURA ALEJANDRA', 'Une ventas', 'EJECUTIVO DE VENTAS', 'Activo'],
     ['LARA MACHADO MARISOL', 'Une ventas', 'EJECUTIVO DE VENTAS VIP', 'Activo'],
     ['CHARLES ZARAZUA ANEYDY', 'Une ventas', 'EJECUTIVO DE VENTAS INDIRECTAS', 'Activo'],
     ['OSORIO BURGOS ADRIAN GIOVANNY', 'Une ventas', 'EJECUTIVO DE VENTAS', 'Activo'],

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