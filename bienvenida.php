<?php
/**
 * Kit de bienvenida para un integrante nuevo: entrega de golpe una lista
 * de materiales de oficina y descuenta el stock de todos, sin tener que
 * dar de alta una solicitud por cada artículo.
 *
 * La lista base (kit_base()) trae precargados los artículos que
 * normalmente se entregan; el encargado puede quitar alguno, cambiar
 * cantidades, o agregar algo más de "Otros materiales de oficina" antes
 * de confirmar -- el kit no se entrega hasta que se manda el formulario.
 */

require_once __DIR__ . '/bootstrap.php';
require_admin();

$pdo = get_db();

/**
 * Lista base del kit. "clave" debe coincidir con la clave del material en
 * el catálogo; si ese material todavía no existe (por ejemplo porque no
 * venía en el Excel que se cargó), se avisa abajo en vez de fallar.
 */
function kit_base(): array
{
    return [
        ['clave' => 'libreta_mc', 'cantidad' => 1, 'nombre_sugerido' => 'Libreta media carta', 'unidad_sugerida' => 'pza'],
        ['clave' => 'bpf_aaz', 'cantidad' => 1],    // Bolígrafo/pluma punta fina azul
        ['clave' => 'bpf_an', 'cantidad' => 1],     // Bolígrafo/pluma punta fina negro
        ['clave' => 'bpf_ar', 'cantidad' => 1],     // Bolígrafo/pluma punta fina rojo
        ['clave' => 'pitm_nam', 'cantidad' => 1],   // Paquete de post-it
        ['clave' => 'mtxt_nr', 'cantidad' => 1],    // Marcatexto naranja
        ['clave' => 'mtxt_vr', 'cantidad' => 1],    // Marcatexto verde
        ['clave' => 'lap_pmhb2', 'cantidad' => 1],  // Lápiz
        ['clave' => 'crr_lin', 'cantidad' => 1],    // Corrector
    ];
}

$materialesOficina = $pdo->query("SELECT * FROM materiales WHERE categoria = 'oficina' ORDER BY nombre")->fetchAll();
$porClave = [];
foreach ($materialesOficina as $m) {
    $porClave[$m['clave']] = $m;
}

$kitFilas = [];
$kitFaltantes = [];
foreach (kit_base() as $item) {
    if (isset($porClave[$item['clave']])) {
        $kitFilas[] = $porClave[$item['clave']] + ['cantidad_sugerida' => $item['cantidad']];
    } else {
        $kitFaltantes[] = $item;
    }
}
$kitIds = array_map('intval', array_column($kitFilas, 'id'));
$otrosMateriales = array_values(array_filter($materialesOficina, fn($m) => !in_array((int)$m['id'], $kitIds, true)));

$errores = [];
$nombreNuevo = '';
$puesto = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombreNuevo = trim($_POST['nombre_nuevo'] ?? '');
    $puesto = trim($_POST['puesto'] ?? '');
    $itemsPost = $_POST['items'] ?? [];

    if ($nombreNuevo === '') {
        $errores[] = 'Escribe el nombre del nuevo integrante.';
    }

    $seleccion = []; // material_id => cantidad
    foreach ($itemsPost as $materialId => $datos) {
        if (empty($datos['activo'])) {
            continue;
        }
        $materialId = (int)$materialId;
        $cantidad = (int)($datos['cantidad'] ?? 0);
        if ($cantidad < 1) {
            $errores[] = 'Escribe una cantidad válida (mayor a cero) para cada material marcado.';
            continue;
        }
        $seleccion[$materialId] = $cantidad;
    }

    if (!$seleccion && !$errores) {
        $errores[] = 'Marca al menos un material para el kit.';
    }

    if (!$errores) {
        try {
            $pdo->beginTransaction();

            $ahora = date('Y-m-d H:i:s');
            $areaTxt = $puesto !== '' ? $puesto : 'Bienvenida';
            $entregados = [];
            $faltoStock = [];

            $stmtMat = $pdo->prepare("SELECT * FROM materiales WHERE id = ? AND categoria = 'oficina' FOR UPDATE");
            $stmtUpdate = $pdo->prepare("UPDATE materiales SET stock = ? WHERE id = ?");
            $stmtInsert = $pdo->prepare(
                "INSERT INTO solicitudes (nombre, area, material_id, cantidad, urgencia, nota, fecha_creacion, status, fecha_resolucion)
                 VALUES (?, ?, ?, ?, 'normal', 'Kit de bienvenida', ?, 'aprobada', ?)"
            );

            foreach ($seleccion as $materialId => $cantidad) {
                $stmtMat->execute([$materialId]);
                $material = $stmtMat->fetch();
                if (!$material) {
                    continue;
                }

                if ($cantidad > (int)$material['stock']) {
                    $faltoStock[] = $material['nombre'];
                }
                $nuevoStock = max(0, (int)$material['stock'] - $cantidad);
                $stmtUpdate->execute([$nuevoStock, $material['id']]);
                $stmtInsert->execute([$nombreNuevo, $areaTxt, $material['id'], $cantidad, $ahora, $ahora]);

                $entregados[] = "{$cantidad} {$material['unidad']} · {$material['nombre']}";
            }

            $pdo->commit();

            notificar_teams_kit($nombreNuevo, $areaTxt, $entregados);

            $resumen = "Kit de bienvenida entregado a \"{$nombreNuevo}\": " . implode(', ', $entregados) . '.';
            if ($faltoStock) {
                $resumen .= ' Ojo: se quedaron en 0 porque no había suficiente existencia de: ' . implode(', ', $faltoStock) . '.';
            }
            flash_set('ok', $resumen);
            header('Location: panel.php');
            exit;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errores[] = 'Ocurrió un error al registrar el kit. Intenta de nuevo.';
        }
    }
}

$pageTitle = 'Kit de bienvenida · Vale de Suministros';
$activePage = 'panel';
require __DIR__ . '/includes/header.php';
?>

<div class="panel-topbar">
  <span class="who">Sesión activa: <strong>Encargado de materiales</strong></span>
  <a class="btn btn-ghost" href="panel.php">&larr; Volver al panel</a>
</div>

<div class="card voucher" style="max-width: 720px; margin: 0 auto;">
  <div class="voucher-head">
    <h2>🎉 Kit de bienvenida</h2>
    <span class="voucher-id mono">Se descuenta del inventario de oficina al confirmar</span>
  </div>

  <?php if ($errores): ?>
    <div class="flash-list" style="margin-bottom: 18px;">
      <?php foreach ($errores as $err): ?>
        <div class="flash flash-error"><?= e($err) ?></div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if ($kitFaltantes): ?>
    <div class="flash-list" style="margin-bottom: 18px;">
      <?php foreach ($kitFaltantes as $falt): ?>
        <div class="flash flash-error">
          "<?= e($falt['nombre_sugerido']) ?>" todavía no está en tu catálogo, así que no aparece abajo.
          <a href="materiales.php?nombre=<?= urlencode($falt['nombre_sugerido']) ?>&unidad=<?= urlencode($falt['unidad_sugerida']) ?>">Agrégalo aquí</a> y luego vuelve a esta pantalla para incluirlo en el kit.
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <form method="post" action="bienvenida.php" id="form-bienvenida">
    <div class="field">
      <label for="nombre_nuevo">Nombre del nuevo integrante</label>
      <input type="text" id="nombre_nuevo" name="nombre_nuevo" value="<?= e($nombreNuevo) ?>" placeholder="Ej. Ana López" required autofocus>
    </div>

    <div class="field">
      <label for="puesto">Puesto o área (opcional)</label>
      <input type="text" id="puesto" name="puesto" value="<?= e($puesto) ?>" placeholder="Ej. Recepción">
    </div>

    <div class="field">
      <label>Kit estándar</label>
      <p class="hint" style="margin-top:-2px;">Precargado con lo de siempre. Desmarca lo que no aplique o cambia la cantidad.</p>
      <div class="kit-list">
        <?php foreach ($kitFilas as $m): ?>
          <div class="kit-row">
            <label class="kit-row-check">
              <input type="checkbox" name="items[<?= (int)$m['id'] ?>][activo]" value="1" checked>
              <span><?= e($m['nombre']) ?> <span class="rmeta">(<?= (int)$m['stock'] ?> <?= e($m['unidad']) ?> disponibles)</span></span>
            </label>
            <input type="number" name="items[<?= (int)$m['id'] ?>][cantidad]" value="<?= (int)$m['cantidad_sugerida'] ?>" min="1" step="1" class="qty-input qty-input-sm">
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="field">
      <label for="buscar-otros">Agregar algo más (opcional)</label>
      <input type="text" id="buscar-otros" placeholder="Buscar en el catálogo de oficina..." autocomplete="off">
      <div class="kit-list kit-list-scroll" id="lista-otros">
        <?php foreach ($otrosMateriales as $m): ?>
          <div class="kit-row" data-nombre="<?= e(mb_strtolower($m['nombre'], 'UTF-8')) ?>">
            <label class="kit-row-check">
              <input type="checkbox" name="items[<?= (int)$m['id'] ?>][activo]" value="1">
              <span><?= e($m['nombre']) ?> <span class="rmeta">(<?= (int)$m['stock'] ?> <?= e($m['unidad']) ?> disponibles)</span></span>
            </label>
            <input type="number" name="items[<?= (int)$m['id'] ?>][cantidad]" value="1" min="1" step="1" class="qty-input qty-input-sm">
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="submit-row">
      <button type="submit" class="btn btn-primary">Entregar kit y descontar inventario</button>
    </div>
  </form>
</div>

<script>
  (function () {
    var buscador = document.getElementById('buscar-otros');
    var lista = document.getElementById('lista-otros');
    if (!buscador || !lista) return;
    buscador.addEventListener('input', function () {
      var q = buscador.value.trim().toLowerCase();
      var filas = lista.querySelectorAll('.kit-row');
      filas.forEach(function (fila) {
        var nombre = fila.getAttribute('data-nombre') || '';
        fila.hidden = q !== '' && nombre.indexOf(q) === -1;
      });
    });
  })();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
