<?php
require_once __DIR__ . '/bootstrap.php';

unset($_SESSION['is_admin']);
session_regenerate_id(true);
flash_set('ok', 'Cerraste la sesión de administrador.');

header('Location: solicitar.php');
exit;
