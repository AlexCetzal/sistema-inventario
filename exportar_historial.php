<?php

require_once __DIR__ . '/bootstrap.php';

require_admin();

$pdo = get_db();

$periodo = $_GET['periodo'] ?? 'todo';

$sql = "
    SELECT 
        s.folio,
        s.nombre,
        m.nombre AS material_nombre,
        s.cantidad,
        m.unidad,
        s.area,
        s.urgencia,
        s.status,
        s.fecha_creacion,
        s.fecha_resolucion,
        s.nota
    FROM solicitudes s
    JOIN materiales m ON m.id = s.material_id
    WHERE s.status != 'pendiente'
";

$params = [];

if ($periodo === 'semana') {

    $sql .= "
        AND s.fecha_resolucion >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ";

    $nombrePeriodo = 'semanal';

} elseif ($periodo === 'mes') {

    $sql .= "
        AND s.fecha_resolucion >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
    ";

    $nombrePeriodo = 'mensual';

} else {

    $nombrePeriodo = 'completo';
}

$sql .= "
    ORDER BY s.fecha_resolucion DESC
";

$stmt = $pdo->prepare($sql);

$stmt->execute($params);

$historial = $stmt->fetchAll(PDO::FETCH_ASSOC);


$filename = 'historial_' . $nombrePeriodo . '_' . date('Y-m-d_H-i-s') . '.xls';


header('Content-Type: application/vnd.ms-excel; charset=UTF-8');

header(
    'Content-Disposition: attachment; filename="' . $filename . '"'
);

header('Pragma: no-cache');

header('Expires: 0');


echo "\xEF\xBB\xBF";

?>

<table border="1">

    <tr>
        <th>Folio</th>
        <th>Nombre</th>
        <th>Material</th>
        <th>Cantidad</th>
        <th>Unidad</th>
        <th>Área</th>
        <th>Urgencia</th>
        <th>Estado</th>
        <th>Fecha de creación</th>
        <th>Fecha de resolución</th>
        <th>Nota</th>
    </tr>


    <?php foreach ($historial as $r): ?>

        <tr>

            <td><?= htmlspecialchars($r['folio']) ?></td>

            <td><?= htmlspecialchars($r['nombre']) ?></td>

            <td><?= htmlspecialchars($r['material_nombre']) ?></td>

            <td><?= htmlspecialchars($r['cantidad']) ?></td>

            <td><?= htmlspecialchars($r['unidad']) ?></td>

            <td><?= htmlspecialchars($r['area']) ?></td>

            <td><?= htmlspecialchars($r['urgencia']) ?></td>

            <td><?= htmlspecialchars($r['status']) ?></td>

            <td><?= htmlspecialchars($r['fecha_creacion']) ?></td>

            <td><?= htmlspecialchars($r['fecha_resolucion']) ?></td>

            <td><?= htmlspecialchars($r['nota'] ?? '') ?></td>

        </tr>

    <?php endforeach; ?>

</table>