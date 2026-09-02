<?php
/**
 * Crea la base de datos (si no existe) y las tablas (borra los datos
 * existentes en ellas). Uso: php init_db.php
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('Este script solo se puede ejecutar desde la línea de comandos.');
}

require_once __DIR__ . '/db.php';

$server = get_server_connection();
$server->exec(
    'CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
);
echo "Base de datos \"" . DB_NAME . "\" lista.\n";

$pdo = get_db();
$schema = file_get_contents(__DIR__ . '/schema.sql');

// Quita las líneas de comentario ("-- ...") antes de trocear por ";",
// para que ningún bloque le llegue a MySQL vacío o solo con comentarios.
$schemaSinComentarios = preg_replace('/^\s*--.*$/m', '', $schema);

foreach (array_filter(array_map('trim', explode(';', $schemaSinComentarios))) as $sentencia) {
    $pdo->exec($sentencia);
}

echo "Tablas creadas.\n";
