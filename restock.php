<?php
/**
 * Acción rápida: sumar existencia a un material cuando llega producto nuevo,
 * sin tener que abrir el formulario completo de edición.
 */

require_once __DIR__ . '/bootstrap.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: materiales.php');
    exit;
}

$id = (int)($_GET['id'] ?? 0);
$cantidad = (int)($_POST['cantidad'] ?? 0);

if ($id <= 0 || $cantidad <= 0) {
    flash_set('error', 'Escribe una cantidad válida (mayor a cero) para agregar.');
    header('Location: materiales.php');
    exit;
}

$pdo = get_db();
$stmt = $pdo->prepare('SELECT nombre, unidad, stock FROM materiales WHERE id = ?');
$stmt->execute([$id]);
$material = $stmt->fetch();

if (!$material) {
    flash_set('error', 'Ese material ya no existe.');
    header('Location: materiales.php');
    exit;
}

$stmt = $pdo->prepare('UPDATE materiales SET stock = stock + ? WHERE id = ?');
$stmt->execute([$cantidad, $id]);

$nuevoStock = (int)$material['stock'] + $cantidad;
flash_set('ok', "Se agregaron {$cantidad} {$material['unidad']} a \"{$material['nombre']}\" (ahora: {$nuevoStock}).");
header('Location: materiales.php');
exit;
