<?php
/**
 * Establece o cambia la contraseña del panel de administrador.
 *
 * La contraseña nunca se guarda en texto plano: este script genera un hash
 * seguro (password_hash de PHP) y lo escribe en config.local.php, junto
 * con las credenciales de la base de datos y el webhook de Teams si ya
 * los habías configurado (este script reescribe el archivo completo, pero
 * conserva lo que ya tenía).
 *
 * Uso: php set_admin_password.php
 *
 * Nota: por simplicidad (y para que funcione igual en Windows, macOS y
 * Linux) lo que escribas se muestra en pantalla mientras lo tecleas.
 * Corre este script en un lugar donde nadie más esté viendo tu pantalla.
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('Este script solo se puede ejecutar desde la línea de comandos.');
}

$configLocalPath = __DIR__ . '/config.local.php';

// Valores por defecto (Laragon) -- se conservan los que ya estén guardados.
$DB_HOST = 'localhost';
$DB_PORT = '3306';
$DB_NAME = 'inventario_desur';
$DB_USER = 'root';
$DB_PASS = '';
$ADMIN_PASSWORD_HASH = '';
$TEAMS_WEBHOOK_URL = '';
if (file_exists($configLocalPath)) {
    require $configLocalPath;
}

echo "Nueva contraseña para el panel de administrador: ";
$password = trim(fgets(STDIN));

echo "Confírmala: ";
$confirm = trim(fgets(STDIN));

if ($password !== $confirm) {
    echo "Las contraseñas no coinciden. Intenta de nuevo.\n";
    exit(1);
}
if (strlen($password) < 6) {
    echo "Usa al menos 6 caracteres. Intenta de nuevo.\n";
    exit(1);
}

$hash = password_hash($password, PASSWORD_DEFAULT);

$contenido = "<?php\n"
    . "\$DB_HOST = " . var_export($DB_HOST, true) . ";\n"
    . "\$DB_PORT = " . var_export($DB_PORT, true) . ";\n"
    . "\$DB_NAME = " . var_export($DB_NAME, true) . ";\n"
    . "\$DB_USER = " . var_export($DB_USER, true) . ";\n"
    . "\$DB_PASS = " . var_export($DB_PASS, true) . ";\n"
    . "\$ADMIN_PASSWORD_HASH = " . var_export($hash, true) . ";\n"
    . "\$TEAMS_WEBHOOK_URL = " . var_export($TEAMS_WEBHOOK_URL, true) . ";\n";

file_put_contents($configLocalPath, $contenido);

echo "Listo. La contraseña quedó guardada (como hash) en config.local.php\n";
