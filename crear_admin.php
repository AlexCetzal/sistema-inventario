<?php
/**
 * Crea (o promueve a admin) la primera cuenta de administrador.
 * Uso: php crear_admin.php
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('Este script solo se puede ejecutar desde la línea de comandos.');
}

require_once __DIR__ . '/db.php';

echo "Usuario o correo del administrador: ";
$correo = trim(fgets(STDIN));

echo "Contraseña: ";
$password = trim(fgets(STDIN));

echo "Confírmala: ";
$confirm = trim(fgets(STDIN));

if ($correo === '' || mb_strlen($correo) < 3) {
    echo "Escribe un usuario o correo válido.\n";
    exit(1);
}
if ($password !== $confirm) {
    echo "Las contraseñas no coinciden. Intenta de nuevo.\n";
    exit(1);
}
if (strlen($password) < 6) {
    echo "Usa al menos 6 caracteres. Intenta de nuevo.\n";
    exit(1);
}

$pdo = get_db();
$hash = password_hash($password, PASSWORD_DEFAULT);

$existente = $pdo->prepare('SELECT id FROM usuarios WHERE correo = ? LIMIT 1');
$existente->execute([$correo]);
$fila = $existente->fetch();

if ($fila) {
    $pdo->prepare('UPDATE usuarios SET password_hash = ?, rol = \'admin\' WHERE id = ?')
        ->execute([$hash, $fila['id']]);
    echo "Listo. \"$correo\" ya existía y ahora es administrador (con esta nueva contraseña).\n";
} else {
    $pdo->prepare(
        'INSERT INTO usuarios (correo, password_hash, rol, fecha_creacion) VALUES (?, ?, \'admin\', ?)'
    )->execute([$correo, $hash, date('Y-m-d H:i:s')]);
    echo "Listo. Se creó la cuenta \"$correo\" como administrador.\n";
}