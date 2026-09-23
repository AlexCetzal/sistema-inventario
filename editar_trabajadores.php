<?php
require_once __DIR__ . '/bootstrap.php';
require_admin();

$pdo = get_db();


/*
 * Obtener ID
 */
$id = filter_input(
  INPUT_GET,
  'id',
  FILTER_VALIDATE_INT
);

if (!$id) {
  header('Location: usuario.php');
  exit;
}


/*
 * Buscar trabajador
 */
$stmt = $pdo->prepare(
  "SELECT id, nombre, area, puesto, status
     FROM trabajadores
     WHERE id = ?"
);

$stmt->execute([$id]);

$trabajador = $stmt->fetch();


if (!$trabajador) {

  http_response_code(404);

  exit('Trabajador no encontrado.');
}


$errores = [];


/*
 * Guardar cambios
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $nombre = trim($_POST['nombre'] ?? '');
  $area = trim($_POST['area'] ?? '');
  $puesto = trim($_POST['puesto'] ?? '');
  $status = $_POST['status'] ?? 'activo';


  /*
     * Validaciones
     */

  if ($nombre === '') {
    $errores[] = 'El nombre es obligatorio.';
  }

  if ($area === '') {
    $errores[] = 'El área es obligatoria.';
  }

  if ($puesto === '') {
    $errores[] = 'El puesto es obligatorio.';
  }

  if (!in_array($status, ['activo', 'inactivo'], true)) {
    $errores[] = 'El estado seleccionado no es válido.';
  }


  /*
     * Actualizar
     */
  if (!$errores) {

    $stmt = $pdo->prepare(
      "UPDATE trabajadores
             SET nombre = ?,
                 area = ?,
                 puesto = ?,
                 status = ?
             WHERE id = ?"
    );

    $stmt->execute([
      $nombre,
      $area,
      $puesto,
      $status,
      $id
    ]);


    flash_set('ok', 'Cambios guardados.');
    header(
      'Location: usuario.php'
    );

    exit;
  }


  /*
     * Mantener valores introducidos
     * si hubo algún error
     */

  $trabajador['nombre'] = $nombre;
  $trabajador['area'] = $area;
  $trabajador['puesto'] = $puesto;
  $trabajador['status'] = $status;
}


$pageTitle = 'Editar personal · Vale de Suministros';
$activePage = 'usuarios';

require __DIR__ . '/includes/header.php';
?>


<div class="panel-topbar">

  <span class="who">
    Editando personal
  </span>

  <span style="display:flex; gap:10px; flex-wrap:wrap;">

    <a
      class="btn btn-ghost"
      href="usuario.php">
      ← Personal
    </a>

    <a
      class="btn btn-ghost"
      href="logout.php">
      Cerrar sesión
    </a>

  </span>

</div>


<section class="block">

  <div class="block-head">

    <div>

      <h2>Editar trabajador</h2>

      <span class="count">
        Actualiza la información del personal
      </span>

    </div>

  </div>


  <?php if ($errores): ?>

    <div class="flash-list">

      <div class="flash flash-error">

        <?php foreach ($errores as $error): ?>

          <div>
            <?= e($error) ?>
          </div>

        <?php endforeach; ?>

      </div>

    </div>

  <?php endif; ?>


  <div class="form-layout">


    <!-- FORMULARIO -->

    <div class="card voucher">

      <div class="voucher-head">

        <div>

          <h2>
            Información del trabajador
          </h2>

        </div>

        <span class="voucher-id mono">
          ID #<?= (int)$trabajador['id'] ?>
        </span>

      </div>


      <form
        method="post"
        action="editar_trabajadores.php?id=<?= (int)$trabajador['id'] ?>">


        <!-- NOMBRE -->

        <div class="field">

          <label for="nombre">
            Nombre completo
          </label>

          <input
            type="text"
            id="nombre"
            name="nombre"
            value="<?= e($trabajador['nombre']) ?>"
            maxlength="150"
            autocomplete="off"
            required>

        </div>


        <!-- AREA -->

        <div class="field">

          <label for="area">
            Área
          </label>

          <input
            type="text"
            id="area"
            name="area"
            value="<?= e($trabajador['area']) ?>"
            maxlength="100"
            autocomplete="off"
            required>

        </div>


        <!-- PUESTO -->

        <div class="field">

          <label for="puesto">
            Puesto
          </label>

          <input
            type="text"
            id="puesto"
            name="puesto"
            value="<?= e($trabajador['puesto']) ?>"
            maxlength="100"
            autocomplete="off"
            required>

        </div>


        <!-- ESTADO -->

        <div class="field">

          <label for="status">
            Estado del trabajador
          </label>

          <select
            id="status"
            name="status"
            required>

            <option
              value="activo"
              <?= $trabajador['status'] === 'activo'
                ? 'selected'
                : '' ?>>
              Activo
            </option>

            <option
              value="inactivo"
              <?= $trabajador['status'] === 'inactivo'
                ? 'selected'
                : '' ?>>
              Inactivo
            </option>

          </select>

        </div>


        <div class="submit-row">

          <a
            href="usuario.php"
            class="btn btn-ghost">
            Cancelar
          </a>

          <button
            type="submit"
            class="btn btn-primary">
            Guardar cambios
          </button>

        </div>


      </form>

    </div>


    <!-- RESUMEN -->

    <aside class="card side-panel personal-summary">

      <div class="personal-summary-icon">
        <?= e(mb_strtoupper(mb_substr($trabajador['nombre'], 0, 1))) ?>
      </div>

      <h3>
        <?= e($trabajador['nombre']) ?>
      </h3>

      <p>
        Información actual
      </p>


      <div class="personal-detail">

        <span>Área</span>

        <strong>
          <?= e($trabajador['area']) ?>
        </strong>

      </div>


      <div class="personal-detail">

        <span>Puesto</span>

        <strong>
          <?= e($trabajador['puesto']) ?>
        </strong>

      </div>


      <div class="personal-detail">

        <span>Estado</span>

        <?php if ($trabajador['status'] === 'activo'): ?>

          <span class="chip chip-good">
            Activo
          </span>

        <?php else: ?>

          <span class="chip chip-critical">
            Inactivo
          </span>

        <?php endif; ?>

      </div>


      <p class="hint" style="margin-top:16px;">
        Los cambios realizados aquí se actualizarán
        directamente en el registro del trabajador.
      </p>

    </aside>


  </div>

</section>


<style>
  /* ==========================================
   RESUMEN DEL TRABAJADOR
   ========================================== */

  .personal-summary {
    position: sticky;
    top: 20px;
  }

  .personal-summary-icon {
    width: 46px;
    height: 46px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 12px;
    border-radius: 12px;
    background: var(--accent-soft);
    color: var(--accent-strong);
    font-family: "Poppins", sans-serif;
    font-size: 1.15rem;
    font-weight: 700;
  }

  .personal-summary h3 {
    font-size: 0.98rem;
    line-height: 1.3;
    margin-bottom: 3px;
  }

  .personal-summary>p {
    margin: 0 0 16px;
  }

  .personal-detail {
    display: flex;
    flex-direction: column;
    gap: 2px;
    padding: 11px 0;
    border-top: 1px solid var(--border);
  }

  .personal-detail span:first-child {
    color: var(--ink-muted);
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }

  .personal-detail strong {
    font-size: 0.83rem;
  }


  /* ---------- Responsive ---------- */

  @media (max-width: 760px) {

    .personal-summary {
      position: static;
    }

  }
</style>


<?php require __DIR__ . '/includes/footer.php'; ?>