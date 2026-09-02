<?php
/**
 * Aprobar o rechazar una solicitud pendiente. Solo acepta POST y solo el
 * encargado (con sesión iniciada) puede llamarla.
 *   actions.php?do=aprobar&folio=123
 *   actions.php?do=rechazar&folio=123
 */

require_once __DIR__ . '/bootstrap.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: panel.php');
    exit;
}

$do = $_GET['do'] ?? '';
$folio = (int)($_GET['folio'] ?? 0);

$pdo = get_db();
$stmt = $pdo->prepare("SELECT * FROM solicitudes WHERE folio = ?");
$stmt->execute([$folio]);
$solicitud = $stmt->fetch();

if (!$solicitud || $solicitud['status'] !== 'pendiente') {
    flash_set('error', 'La solicitud ya no está pendiente o no existe.');
    header('Location: panel.php');
    exit;
}

$ahora = date('Y-m-d H:i:s');

if ($do === 'aprobar') {
    $stmtMat = $pdo->prepare("SELECT * FROM materiales WHERE id = ?");
    $stmtMat->execute([$solicitud['material_id']]);
    $material = $stmtMat->fetch();

    $nuevoStock = max(0, (int)$material['stock'] - (int)$solicitud['cantidad']);
    $pdo->prepare("UPDATE materiales SET stock = ? WHERE id = ?")
        ->execute([$nuevoStock, $material['id']]);
    $pdo->prepare("UPDATE solicitudes SET status = 'aprobada', fecha_resolucion = ? WHERE folio = ?")
        ->execute([$ahora, $folio]);

    flash_set('ok', "Folio {$folio} aprobado y descontado del inventario.");
} elseif ($do === 'rechazar') {
    $pdo->prepare("UPDATE solicitudes SET status = 'rechazada', fecha_resolucion = ? WHERE folio = ?")
        ->execute([$ahora, $folio]);

    flash_set('ok', "Folio {$folio} rechazado.");
} else {
    flash_set('error', 'Acción no reconocida.');
}

header('Location: panel.php');
exit;
