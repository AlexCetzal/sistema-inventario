<?php
/**
 * Agregar un material nuevo al catálogo, o editar uno que ya existe
 * (nombre, categoría, unidad, mínimos/máximos y existencia actual).
 * Solo el encargado, con sesión iniciada, puede entrar aquí.
 */

require_once __DIR__ . '/bootstrap.php';
require_admin();

$pdo = get_db();

/**
 * Genera una "clave" única (slug) a partir del nombre del material,
 * para no tener que pedírsela al encargado a mano.
 * Ej. "Corrector líquido" -> "corrector_liquido"
 */
function generar_clave(PDO $pdo, string $nombre, ?int $excluirId = null): string
{
    $base = mb_strtolower($nombre, 'UTF-8');
    $base = strtr($base, [
        'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
        'ñ' => 'n', 'ü' => 'u',
    ]);
    $base = preg_replace('/[^a-z0-9]+/', '_', $base);
    $base = trim($base, '_');
    if ($base === '') {
        $base = 'material';
    }

    $clave = $base;
    $sufijo = 2;
    $sql = 'SELECT COUNT(*) FROM materiales WHERE clave = ?' . ($excluirId ? ' AND id != ?' : '');
    $stmt = $pdo->prepare($sql);
    while (true) {
        $params = $excluirId ? [$clave, $excluirId] : [$clave];
        $stmt->execute($params);
        if ((int)$stmt->fetchColumn() === 0) {
            break;
        }
        $clave = $base . '_' . $sufijo;
        $sufijo++;
    }
    return $clave;
}

// ¿Estamos editando un material existente? El id puede venir por la URL
// (al abrir el formulario) o por un campo oculto (al reenviar el formulario).
$editId = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$editando = $editId > 0;
$materialActual = null;

if ($editando) {
    $stmt = $pdo->prepare('SELECT * FROM materiales WHERE id = ?');
    $stmt->execute([$editId]);
    $materialActual = $stmt->fetch();
    if (!$materialActual) {
        flash_set('error', 'Ese material ya no existe.');
        header('Location: materiales.php');
        exit;
    }
}

$errores = [];
if ($editando && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Precargar el formulario con los datos actuales del material.
    $valores = [
        'nombre' => $materialActual['nombre'],
        'categoria' => $materialActual['categoria'],
        'unidad' => $materialActual['unidad'],
        'stock' => (string)$materialActual['stock'],
        'stock_min' => (string)$materialActual['stock_min'],
        'stock_max' => (string)$materialActual['stock_max'],
    ];
} else {
    // Al agregar (no editar), se puede precargar nombre/unidad por la URL
    // -- por ejemplo, desde el aviso de "material faltante" en el kit de bienvenida.
    $valores = [
        'nombre' => trim($_GET['nombre'] ?? ''),
        'categoria' => 'oficina',
        'unidad' => trim($_GET['unidad'] ?? ''),
        'stock' => '0',
        'stock_min' => '0',
        'stock_max' => '',
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $valores['nombre'] = trim($_POST['nombre'] ?? '');
    $valores['categoria'] = $_POST['categoria'] ?? '';
    $valores['unidad'] = trim($_POST['unidad'] ?? '');
    $valores['stock'] = $_POST['stock'] ?? '0';
    $valores['stock_min'] = $_POST['stock_min'] ?? '0';
    $valores['stock_max'] = $_POST['stock_max'] ?? '0';

    $stock = (int)$valores['stock'];
    $stockMin = (int)$valores['stock_min'];
    $stockMax = (int)$valores['stock_max'];

    if ($valores['nombre'] === '') {
        $errores[] = 'Escribe el nombre del material.';
    }
    if (!in_array($valores['categoria'], ['oficina', 'limpieza'], true)) {
        $errores[] = 'Selecciona una categoría válida.';
    }
    if ($valores['unidad'] === '') {
        $errores[] = 'Escribe la unidad (pza, paq, L, caja...).';
    }
    if ($stock < 0 || $stockMin < 0 || $stockMax < 0) {
        $errores[] = 'Las cantidades no pueden ser negativas.';
    }
    if ($stockMax < 1) {
        $errores[] = 'El nivel máximo debe ser mayor a cero (se usa para la barra de existencias).';
    }
    if ($stockMax > 0 && $stockMax < $stockMin) {
        $errores[] = 'El nivel máximo no puede ser menor que el mínimo.';
    }

    if (!$errores && $editando) {
        $stmt = $pdo->prepare(
            "UPDATE materiales
             SET nombre = ?, categoria = ?, unidad = ?, stock = ?, stock_min = ?, stock_max = ?
             WHERE id = ?"
        );
        $stmt->execute([$valores['nombre'], $valores['categoria'], $valores['unidad'], $stock, $stockMin, $stockMax, $editId]);

        flash_set('ok', "Se actualizó \"{$valores['nombre']}\".");
        header('Location: materiales.php');
        exit;
    }

    if (!$errores && !$editando) {
        $clave = generar_clave($pdo, $valores['nombre']);
        $stmt = $pdo->prepare(
            "INSERT INTO materiales (clave, nombre, categoria, unidad, stock, stock_min, stock_max)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$clave, $valores['nombre'], $valores['categoria'], $valores['unidad'], $stock, $stockMin, $stockMax]);

        flash_set('ok', "Se agregó \"{$valores['nombre']}\" al catálogo.");
        header('Location: materiales.php');
        exit;
    }
}

$materiales = $pdo->query("SELECT * FROM materiales ORDER BY categoria, nombre")->fetchAll();

$pageTitle = ($editando ? 'Editar material' : 'Agregar material') . ' · Vale de Suministros';
$activePage = 'panel';
require __DIR__ . '/includes/header.php';
?>

<div class="panel-topbar">
  <span class="who">Sesión activa: <strong>Encargado de materiales</strong></span>
  <a class="btn btn-ghost" href="panel.php">&larr; Volver al panel</a>
</div>

<div class="form-layout">
  <div class="card voucher">
    <div class="voucher-head">
      <h2><?= $editando ? 'Editar material' : 'Agregar material al catálogo' ?></h2>
    </div>

    <?php if ($errores): ?>
      <div class="flash-list" style="margin-bottom: 18px;">
        <?php foreach ($errores as $err): ?>
          <div class="flash flash-error"><?= e($err) ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="post" action="materiales.php<?= $editando ? '?id=' . $editId : '' ?>">
      <?php if ($editando): ?>
        <input type="hidden" name="id" value="<?= $editId ?>">
      <?php endif; ?>

      <div class="field">
        <label for="nombre">Nombre del material</label>
        <input type="text" id="nombre" name="nombre" value="<?= e($valores['nombre']) ?>" placeholder="Ej. Corrector líquido" required autofocus>
      </div>

      <div class="field">
        <label>Categoría</label>
        <div class="pill-group">
          <label class="pill">
            <input type="radio" name="categoria" value="oficina" <?= $valores['categoria'] === 'oficina' ? 'checked' : '' ?>>
            Oficina
          </label>
          <label class="pill">
            <input type="radio" name="categoria" value="limpieza" <?= $valores['categoria'] === 'limpieza' ? 'checked' : '' ?>>
            Limpieza
          </label>
        </div>
      </div>

      <div class="field">
        <label for="unidad">Unidad</label>
        <input type="text" id="unidad" name="unidad" value="<?= e($valores['unidad']) ?>" placeholder="Ej. pza, paq, L, caja, rollo" required>
        <p class="hint">Cómo se cuenta: piezas, paquetes, litros, cajas...</p>
      </div>

      <div class="field">
        <label for="stock"><?= $editando ? 'Existencia actual' : 'Existencia inicial' ?></label>
        <input type="number" id="stock" name="stock" min="0" step="1" value="<?= e($valores['stock']) ?>" class="qty-input" required>
        <?php if ($editando): ?><p class="hint">Puedes corregirla aquí directamente, o usar "Agregar existencia" en la lista de la derecha cuando llegue producto nuevo.</p><?php endif; ?>
      </div>

      <div class="field">
        <label for="stock_min">Nivel mínimo</label>
        <input type="number" id="stock_min" name="stock_min" min="0" step="1" value="<?= e($valores['stock_min']) ?>" class="qty-input" required>
        <p class="hint">Cuando la existencia baje a esto o menos, el panel lo marca como "Bajo".</p>
      </div>

      <div class="field">
        <label for="stock_max">Nivel máximo</label>
        <input type="number" id="stock_max" name="stock_max" min="1" step="1" value="<?= e($valores['stock_max']) ?>" class="qty-input" required>
        <p class="hint">La cantidad que consideras "lleno" -- se usa para dibujar la barra de existencias.</p>
      </div>

      <div class="submit-row">
        <button type="submit" class="btn btn-primary"><?= $editando ? 'Guardar cambios' : 'Agregar material' ?></button>
        <?php if ($editando): ?><a href="materiales.php" class="btn btn-ghost">Cancelar</a><?php endif; ?>
      </div>
    </form>
  </div>

  <div class="card side-panel">
    <h3>Catálogo actual</h3>
    <p><?= count($materiales) ?> material<?= count($materiales) === 1 ? '' : 'es' ?> registrados.</p>
    <?php foreach ($materiales as $m): ?>
      <div class="recent-item mat-row<?= $editando && (int)$m['id'] === $editId ? ' mat-row-active' : '' ?>">
        <div>
          <div class="rmat"><?= e($m['nombre']) ?></div>
          <div class="rmeta"><?= $m['categoria'] === 'oficina' ? 'Oficina' : 'Limpieza' ?> · <?= (int)$m['stock'] ?> <?= e($m['unidad']) ?></div>
        </div>
        <div class="mat-row-actions">
          <a href="materiales.php?id=<?= (int)$m['id'] ?>" class="btn btn-ghost btn-sm">Editar</a>
          <form method="post" action="restock.php?id=<?= (int)$m['id'] ?>" class="restock-form">
            <input type="number" name="cantidad" min="1" step="1" placeholder="+ cant." class="qty-input qty-input-sm" required>
            <button type="submit" class="btn btn-ghost btn-sm">Agregar</button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
