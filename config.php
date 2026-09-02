<?php
/**
 * Configuración de la aplicación.
 *
 * Los valores sensibles (credenciales de la base de datos, contraseña del
 * panel, webhook de Teams) NO viven aquí, sino en config.local.php -- un
 * archivo que no se sube a control de versiones. set_admin_password.php lo
 * crea/actualiza. Este archivo solo define los valores por defecto
 * (pensados para MySQL de Laragon: host local, usuario root, sin
 * contraseña) y los sobreescribe si config.local.php existe.
 */

define('BASE_DIR', __DIR__);

$DB_HOST = 'localhost';
$DB_PORT = '3306';
$DB_NAME = 'inventario_desur';
$DB_USER = 'root';
$DB_PASS = '';

$ADMIN_PASSWORD_HASH = '';
$TEAMS_WEBHOOK_URL = '';

$configLocal = BASE_DIR . '/config.local.php';
if (file_exists($configLocal)) {
    require $configLocal;
}

define('DB_HOST', $DB_HOST);
define('DB_PORT', $DB_PORT);
define('DB_NAME', $DB_NAME);
define('DB_USER', $DB_USER);
define('DB_PASS', $DB_PASS);
define('ADMIN_PASSWORD_HASH', $ADMIN_PASSWORD_HASH);
define('TEAMS_WEBHOOK_URL', $TEAMS_WEBHOOK_URL);
