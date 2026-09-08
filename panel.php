<?php
require_once __DIR__ . '/bootstrap.php';
require_admin();

$pdo = get_db();
$materialesRaw = $pdo->query("SELECT * FROM materiales ORDER BY categoria, nombre")->fetchAll();

function calcular_estado_material(array $m): array
{
    $stock = (int)$m['stock'];
    $min = (int)$m['stock_min'];
    $max = (int)$m['stock_max'];

    if ($stock <= 0) {
        $estado = 'critical';
    } elseif ($stock <= $min) {
        $estado = 'warn';
    } else {
        $estado = 'good';
    }

    $pct = $max > 0 ? max(4, min(100, (int)round(($stock / $max) * 100))) : 4;

    return $m + ['estado' => $estado, 'pct' => $pct];
}

$inventarioPorCategoria = ['oficina' => [], 'limpieza' => []];
$stockBajo = 0;
foreach ($materialesRaw as $m) {
    $mm = calcular_estado_material($m);
    $inventarioPorCategoria[$mm['categoria']][] = $mm;
    if ($mm['estado'] !== 'good') {
        $stockBajo++;
    }
}

$pendientes = $pdo->query(
    "SELECT s.*, m.nombre AS material_nombre, m.unidad
     FROM solicitudes s JOIN materiales m ON m.id = s.material_id
     WHERE s.status = 'pendiente' ORDER BY s.folio DESC"
)->fetchAll();

$historial = $pdo->query(
    "SELECT s.*, m.nombre AS material_nombre, m.unidad
     FROM solicitudes s JOIN materiales m ON m.id = s.material_id
     WHERE s.status != 'pendiente' ORDER BY s.fecha_resolucion DESC LIMIT 8"
)->fetchAll();

$hoy = date('Y-m-d');
$stmt = $pdo->prepare("SELECT COUNT(*) FROM solicitudes WHERE fecha_creacion LIKE ?");
$stmt->execute([$hoy . '%']);
$solicitudesHoy = (int)$stmt->fetchColumn();

$pendientesCount = count($pendientes);
$totalMateriales = count($materialesRaw);
$etiquetasEstado = ['critical' => 'Agotado', 'warn' => 'Bajo', 'good' => 'Suficiente'];
$nombresCategoria = ['oficina' => 'Oficina', 'limpieza' => 'Limpieza'];

$pageTitle = 'Panel del encargado · Vale de Suministros';
$activePage = 'panel';
require __DIR__ . '/includes/header.php';
?>

<div class="panel-topbar">
  <span class="who">Sesión activa: <strong>Encargado de materiales</strong></span>
  <span style="display:flex; gap:10px; flex-wrap:wrap;">
    <a class="btn btn-primary" href="bienvenida.php">🎉 Kit de bienvenida</a>
     <a class="btn btn-ghost" href="usuario.php">Editar personal</a>
    <a class="btn btn-ghost" href="logout.php">Cerrar sesión</a>
  </span>
</div>

<div class="stat-row">
  <div class="card stat-tile <?= $pendientesCount > 0 ? 'flag' : '' ?>">
    <span class="num mono"><?= $pendientesCount ?></span>
    <span class="label">Solicitudes pendientes</span>
  </div>
  <div class="card stat-tile <?= $stockBajo > 0 ? 'flag' : '' ?>">
    <span class="num mono"><?= $stockBajo ?></span>
    <span class="label">Materiales con stock bajo</span>
  </div>
  <div class="card stat-tile">
    <span class="num mono"><?= $solicitudesHoy ?></span>
    <span class="label">Solicitudes de hoy</span>
  </div>
  <div class="card stat-tile">
    <span class="num mono"><?= $totalMateriales ?></span>
    <span class="label">Artículos en catálogo</span>
  </div>
</div>

<section class="block">
  <div class="block-head">
  <h2>Inventario</h2>

  <div class="block-head-right">
    <span class="count">Existencias actuales por categoría</span>

    <a href="materiales.php" class="btn btn-ghost btn-sm">
      Agregar / editar materiales
    </a>
  </div>
</div>

<div class="inventory-filters">
  <div class="search-box">
    <label for="buscarMaterial">Buscar material</label>
    <input
    class="btn btn-ghost btn-sm"
      type="text"
      id="buscarMaterial"
      placeholder="Escribe el nombre del material..."
      autocomplete="off"
    >
  </div>

  <div class="filter-box">
    <label for="filtroEstado" >Estado del stock</label>
    <select id="filtroEstado" class="btn btn-ghost btn-sm">
      <option value="all">Todos</option>
      <option value="good">Suficiente</option>
      <option value="warn">Bajo</option>
      <option value="critical">Agotado</option>
    </select>
  </div>
</div>

  <?php foreach ($inventarioPorCategoria as $categoria => $items): ?>
    <?php if ($items): ?>
      <h3 class="cat-heading"><span class="dot"></span><?= e($nombresCategoria[$categoria] ?? $categoria) ?></h3>
      <div class="inv-grid inv-scroll">
        <?php foreach ($items as $m): ?>
          <div class="card inv-card" data-nombre="<?= e(strtolower($m['nombre'])) ?>" data-estado="<?= e($m['estado']) ?>">
            <div class="top-row">
              <a class="name" href="materiales.php?id=<?= (int)$m['id'] ?>" title="Editar / agregar existencia"><?= e($m['nombre']) ?></a>
              <span class="chip chip-<?= $m['estado'] ?>"><?= $etiquetasEstado[$m['estado']] ?></span>
            </div>
            <div><span class="qty"><?= (int)$m['stock'] ?></span> <span class="unit"><?= e($m['unidad']) ?></span></div>
            <div class="bar-track"><div class="bar-fill" style="width:<?= (int)$m['pct'] ?>%; background: var(--<?= $m['estado'] ?>)"></div></div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  <?php endforeach; ?>
</section>

<section class="block">
  <div class="block-head">
    <h2>Solicitudes pendientes</h2>
    <span class="count"><?= $pendientesCount ?> solicitud<?= $pendientesCount !== 1 ? 'es' : '' ?></span>
  </div>
  <div class="requests-list">
    <?php if ($pendientes): ?>
      <?php foreach ($pendientes as $r): ?>
        <div class="card req-row">
          <div class="req-main">
            <span class="req-title"><?= e($r['nombre']) ?> — <?= e($r['material_nombre']) ?> × <?= (int)$r['cantidad'] ?></span>
            <span class="req-meta">
              <span class="chip <?= $r['urgencia'] === 'urgente' ? 'chip-critical' : 'chip-neutral' ?>"><?= $r['urgencia'] === 'urgente' ? 'Urgente' : 'Normal' ?></span>
              <span>Folio <?= (int)$r['folio'] ?></span>
              <span><?= e($r['area']) ?></span>
              <span><?= e(substr($r['fecha_creacion'], 0, 10)) ?></span>
              <?php if (!empty($r['nota'])): ?><span>"<?= e($r['nota']) ?>"</span><?php endif; ?>
            </span>
          </div>
          <div class="req-actions">
            <form method="post" action="actions.php?do=aprobar&folio=<?= (int)$r['folio'] ?>">
              <button type="submit" class="btn btn-approve">Aprobar</button>
            </form>
            <form method="post" action="actions.php?do=rechazar&folio=<?= (int)$r['folio'] ?>">
              <button type="submit" class="btn btn-reject">Rechazar</button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p class="empty-note">No hay solicitudes pendientes por revisar.</p>
    <?php endif; ?>
  </div>
</section>

<section class="block">
  <div class="block-head">
      <h2>Historial reciente</h2>

      <div class="block-head-right">
          <span class="count">Últimos movimientos resueltos</span>

          <a href="exportar_historial.php?periodo=semana"
            class="btn btn-ghost btn-sm">
              Exportar semana
          </a>

          <a href="exportar_historial.php?periodo=mes"
            class="btn btn-ghost btn-sm">
              Exportar mes
          </a>

          <a href="exportar_historial.php?periodo=todo"
            class="btn btn-ghost btn-sm">
              Exportar todo
          </a>
      </div>
  </div>
  <div class="card">
    <?php if ($historial): ?>
      <?php foreach ($historial as $r): ?>
        <div class="history-row">
          <span><?= e($r['nombre']) ?> · <?= e($r['material_nombre']) ?> × <?= (int)$r['cantidad'] ?> <span style="color:var(--ink-muted)">(<?= e($r['area']) ?>, <?= e(substr($r['fecha_resolucion'] ?? '', 0, 10)) ?>)</span></span>
          <span class="chip <?= $r['status'] === 'aprobada' ? 'chip-good' : 'chip-critical' ?>"><?= e($r['status']) ?></span>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p class="empty-note" style="padding:14px 16px;">Sin movimientos todavía.</p>
    <?php endif; ?>
  </div>
</section>
<script>
document.addEventListener('DOMContentLoaded', function () {

  const buscador = document.getElementById('buscarMaterial');
  const filtroEstado = document.getElementById('filtroEstado');

  const tarjetas = document.querySelectorAll('.inv-card');
  const categorias = document.querySelectorAll('.cat-heading');

  function filtrarInventario() {

    const texto = buscador.value
      .toLowerCase()
      .trim();

    const estadoSeleccionado = filtroEstado.value;

    tarjetas.forEach(function (tarjeta) {

      const nombre = tarjeta.dataset.nombre;
      const estado = tarjeta.dataset.estado;

      const coincideNombre =
        nombre.includes(texto);

      const coincideEstado =
        estadoSeleccionado === 'all' ||
        estado === estadoSeleccionado;

      if (coincideNombre && coincideEstado) {
        tarjeta.style.display = '';
      } else {
        tarjeta.style.display = 'none';
      }
    });

    // Ocultar categorías que no tengan resultados
    document.querySelectorAll('.inv-grid').forEach(function (grid) {

      const visibles = grid.querySelectorAll(
        '.inv-card:not([style*="display: none"])'
      );

      const heading = grid.previousElementSibling;

      if (visibles.length === 0) {
        grid.style.display = 'none';

        if (heading && heading.classList.contains('cat-heading')) {
          heading.style.display = 'none';
        }
      } else {
        grid.style.display = '';

        if (heading && heading.classList.contains('cat-heading')) {
          heading.style.display = '';
        }
      }
    });
  }

  buscador.addEventListener('input', filtrarInventario);
  filtroEstado.addEventListener('change', filtrarInventario);

});
</script>
<?php require __DIR__ . '/includes/footer.php'; ?>
