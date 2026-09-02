<?php
/**
 * Deja la base de datos lista para empezar a usarse "en serio",
 * borrando lo que se generó durante las pruebas.
 *
 * Por default SOLO borra las solicitudes (los vales que se generaron
 * mientras probaban el sistema) y reinicia los folios para que el
 * primero real sea el folio 1. El catálogo de materiales (y las
 * existencias que ya hayas corregido con "Editar" o "Agregar
 * existencia" en el panel) NO se toca -- para eso ya está esa pantalla.
 *
 * Uso:
 *   php reset_db.php                    Borra solo las solicitudes (recomendado)
 *   php reset_db.php --materiales-cero  Además, deja la existencia de TODOS los
 *                                       materiales en 0 (para capturar el conteo
 *                                       real desde "Agregar / editar materiales")
 *   php reset_db.php --full             Borra todo y vuelve a cargar el catálogo
 *                                       de ejemplo original (como recién instalado)
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('Este script solo se puede ejecutar desde la línea de comandos.');
}

require_once __DIR__ . '/db.php';

$modoFull = in_array('--full', $argv, true);
$modoMaterialesCero = in_array('--materiales-cero', $argv, true);

if ($modoFull) {
    $descripcion = "Esto borrará TODAS las solicitudes y TODOS los materiales, y volverá a\ncargar el catálogo de ejemplo original (como si el sistema fuera nuevo).";
} elseif ($modoMaterialesCero) {
    $descripcion = "Esto borrará todas las solicitudes registradas y pondrá la existencia\nde TODOS los materiales en 0 (el catálogo -- nombres, mínimos, máximos --\nse conserva; después capturas las cantidades reales desde el panel).";
} else {
    $descripcion = "Esto borrará todas las solicitudes registradas (los vales de prueba).\nEl catálogo de materiales y sus existencias actuales NO se tocan.";
}

echo $descripcion . "\n";
echo "Esta acción no se puede deshacer. ¿Continuar? (escribe 'si' para confirmar): ";
$respuesta = strtolower(trim(fgets(STDIN)));

if ($respuesta !== 'si') {
    echo "Cancelado. No se modificó nada.\n";
    exit(0);
}

$pdo = get_db();

$pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
$pdo->exec('TRUNCATE TABLE solicitudes');
echo "- Solicitudes borradas (folios reiniciados desde 1).\n";

if ($modoFull) {
    $pdo->exec('TRUNCATE TABLE materiales');
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    echo "- Catálogo de materiales borrado.\n";
    require __DIR__ . '/seed_db.php';
} else {
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    if ($modoMaterialesCero) {
        $pdo->exec('UPDATE materiales SET stock = 0');
        echo "- Existencia de todos los materiales puesta en 0.\n";
    }
}

echo "\nListo. La base de datos está lista para usarse.\n";
