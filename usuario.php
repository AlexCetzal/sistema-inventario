<?php
require_once __DIR__ . '/bootstrap.php';
require_admin();

$pdo = get_db();

/*
 * Obtener trabajadores
 */
$trabajadores = $pdo->query(
    "SELECT id, nombre, area, puesto, status
     FROM trabajadores
     ORDER BY area, nombre"
)->fetchAll();

$pageTitle = 'Personal · Vale de Suministros';
$activePage = 'usuarios';

require __DIR__ . '/includes/header.php';
?>

<div class="panel-topbar">
  <span class="who">
    Gestión de personal
  </span>

  <span style="display:flex; gap:10px; flex-wrap:wrap;">
    <a class="btn btn-ghost" href="panel.php">
      ← Panel
    </a>

    <a class="btn btn-ghost" href="logout.php">
      Cerrar sesión
    </a>
  </span>
</div>


<section class="block">

  <div class="block-head personal-head">

    <div>
      <h2>Personal</h2>
      <span class="count">
        <?= count($trabajadores) ?>
        trabajador<?= count($trabajadores) !== 1 ? 'es' : '' ?> registrados
      </span>
    </div>

  </div>


  <!-- FILTROS -->

  <div class="card personal-filters">

    <div class="personal-search">

      <label for="buscarTrabajador">
        Buscar personal
      </label>

      <div class="search-control">

        <span class="search-icon">⌕</span>

        <input
          type="text"
          id="buscarTrabajador"
          placeholder="Nombre, área o puesto..."
          autocomplete="off"
        >

      </div>

    </div>


    <div class="personal-status">

      <label for="filtroStatus">
        Estado
      </label>

      <select id="filtroStatus">

        <option value="all">
          Todos
        </option>

        <option value="activo">
          Activos
        </option>

        <option value="inactivo">
          Inactivos
        </option>

      </select>

    </div>

  </div>


  <!-- LISTADO -->

  <div class="card personal-list">

    <div class="personal-list-head">

      <span>Trabajador</span>
      <span>Área</span>
      <span>Puesto</span>
      <span>Estado</span>
      <span></span>

    </div>


    <div id="tablaTrabajadores">

      <?php if ($trabajadores): ?>

        <?php foreach ($trabajadores as $trabajador): ?>

          <div
            class="personal-row"
            data-status="<?= e($trabajador['status']) ?>"
            data-busqueda="<?= e(
              strtolower(
                $trabajador['nombre'] . ' ' .
                $trabajador['area'] . ' ' .
                $trabajador['puesto']
              )
            ) ?>"
          >

            <div class="personal-name">

              <span class="personal-avatar">
                <?= e(mb_strtoupper(mb_substr($trabajador['nombre'], 0, 1))) ?>
              </span>

              <div>
                <strong>
                  <?= e($trabajador['nombre']) ?>
                </strong>

                <span class="personal-mobile-area">
                  <?= e($trabajador['area']) ?>
                </span>
              </div>

            </div>


            <div class="personal-area">
              <?= e($trabajador['area']) ?>
            </div>


            <div class="personal-puesto">
              <?= e($trabajador['puesto']) ?>
            </div>


            <div>

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


            <div class="personal-action">

              <a
                href="editar_trabajadores.php?id=<?= (int)$trabajador['id'] ?>"
                class="btn btn-ghost btn-sm"
              >
                Editar
              </a>

            </div>

          </div>

        <?php endforeach; ?>

      <?php else: ?>

        <div class="personal-empty">
          <span class="empty-icon">○</span>

          <strong>No hay trabajadores registrados</strong>

          <span>
            Actualmente no existen trabajadores en el sistema.
          </span>
        </div>

      <?php endif; ?>

    </div>


    <div
      id="sinResultados"
      class="personal-empty"
      style="display:none;"
    >
      <span class="empty-icon">⌕</span>

      <strong>No se encontraron trabajadores</strong>

      <span>
        Prueba con otro nombre, área o puesto.
      </span>
    </div>

  </div>

</section>


<style>

/* ==========================================
   PERSONAL
   ========================================== */

.personal-head {
  margin-bottom: 16px;
}

.personal-head h2 {
  font-size: 1.15rem;
}


/* ---------- Filtros ---------- */

.personal-filters {
  display: grid;
  grid-template-columns: minmax(0, 1fr) 190px;
  gap: 14px;
  padding: 14px;
  margin-bottom: 14px;
  box-shadow: none;
}

.personal-filters label {
  display: block;
  font-size: 0.72rem;
  font-weight: 600;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  color: var(--ink-muted);
  margin-bottom: 7px;
}

.personal-search,
.personal-status {
  min-width: 0;
}

.search-control {
  display: flex;
  align-items: center;
  gap: 8px;
  background: var(--bg);
  border: 1px solid var(--border);
  border-radius: 8px;
  padding: 0 11px;
}

.search-icon {
  color: var(--ink-muted);
  font-size: 1.2rem;
  line-height: 1;
}

.search-control input {
  width: 100%;
  min-width: 0;
  border: none !important;
  outline: none;
  background: transparent !important;
  color: var(--ink);
  font: inherit;
  padding: 10px 2px;
}

.search-control:focus-within {
  border-color: var(--accent);
  outline: 2px solid var(--accent-soft);
}

.personal-status select {
  width: 100%;
  font: inherit;
  color: var(--ink);
  background: var(--bg);
  border: 1px solid var(--border);
  border-radius: 8px;
  padding: 10px 12px;
}


/* ---------- Lista ---------- */

.personal-list {
  overflow: hidden;
}

.personal-list-head,
.personal-row {
  display: grid;
  grid-template-columns:
    minmax(230px, 1.5fr)
    minmax(130px, 0.9fr)
    minmax(180px, 1.2fr)
    100px
    80px;

  gap: 14px;
  align-items: center;
}

.personal-list-head {
  padding: 11px 16px;
  background: var(--surface-2);
  color: var(--ink-muted);
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
}

.personal-row {
  padding: 13px 16px;
  border-top: 1px solid var(--border);
  font-size: 0.84rem;
  transition: background 0.15s ease;
}

.personal-row:hover {
  background: var(--accent-soft);
}

.personal-name {
  display: flex;
  align-items: center;
  gap: 10px;
  min-width: 0;
}

.personal-name strong {
  display: block;
  font-size: 0.86rem;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.personal-avatar {
  width: 34px;
  height: 34px;
  flex: none;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 9px;
  background: var(--accent-soft);
  color: var(--accent-strong);
  font-weight: 700;
  font-family: "Poppins", sans-serif;
}

.personal-area,
.personal-puesto {
  color: var(--ink-muted);
}

.personal-puesto {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.personal-action {
  display: flex;
  justify-content: flex-end;
}

.personal-action .btn {
  white-space: nowrap;
}

.personal-mobile-area {
  display: none;
  color: var(--ink-muted);
  font-size: 0.73rem;
  margin-top: 2px;
}


/* ---------- Vacío ---------- */

.personal-empty {
  min-height: 170px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 5px;
  padding: 30px;
  color: var(--ink-muted);
  text-align: center;
}

.personal-empty strong {
  color: var(--ink);
  font-size: 0.9rem;
}

.personal-empty span:not(.empty-icon) {
  font-size: 0.78rem;
}

.empty-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 38px;
  height: 38px;
  margin-bottom: 4px;
  border-radius: 50%;
  background: var(--surface-2);
  color: var(--ink-muted);
  font-size: 1.2rem;
}


/* ---------- Responsive ---------- */

@media (max-width: 820px) {

  .personal-list-head {
    display: none;
  }

  .personal-row {
    grid-template-columns: 1fr auto;
    gap: 8px 14px;
    padding: 14px 16px;
  }

  .personal-name {
    grid-column: 1;
  }

  .personal-area,
  .personal-puesto {
    grid-column: 1;
    font-size: 0.76rem;
  }

  .personal-area {
    display: none;
  }

  .personal-mobile-area {
    display: block;
  }

  .personal-puesto {
    margin-left: 44px;
  }

  .personal-row > div:nth-child(4) {
    grid-column: 2;
    grid-row: 1;
  }

  .personal-action {
    grid-column: 2;
    grid-row: 2 / span 2;
  }

}


@media (max-width: 560px) {

  .personal-filters {
    grid-template-columns: 1fr;
  }

  .personal-row {
    grid-template-columns: 1fr;
  }

  .personal-row > div:nth-child(4),
  .personal-action {
    grid-column: 1;
    grid-row: auto;
  }

  .personal-action {
    justify-content: flex-start;
    margin-top: 4px;
  }

  .personal-puesto {
    margin-left: 44px;
  }

}

</style>


<script>
document.addEventListener('DOMContentLoaded', function () {

  const buscador = document.getElementById('buscarTrabajador');
  const filtroStatus = document.getElementById('filtroStatus');

  const filas = document.querySelectorAll('.personal-row');
  const sinResultados = document.getElementById('sinResultados');


  function filtrarTrabajadores() {

    const texto = buscador.value
      .toLowerCase()
      .trim();

    const statusSeleccionado = filtroStatus.value;

    let visibles = 0;


    filas.forEach(function (fila) {

      const busqueda = fila.dataset.busqueda;
      const status = fila.dataset.status;


      const coincideTexto =
        busqueda.includes(texto);


      const coincideStatus =
        statusSeleccionado === 'all' ||
        status === statusSeleccionado;


      if (coincideTexto && coincideStatus) {

        fila.style.display = '';

        visibles++;

      } else {

        fila.style.display = 'none';

      }

    });


    sinResultados.style.display =
      visibles === 0 ? 'flex' : 'none';

  }


  buscador.addEventListener(
    'input',
    filtrarTrabajadores
  );


  filtroStatus.addEventListener(
    'change',
    filtrarTrabajadores
  );

});
</script>


<?php require __DIR__ . '/includes/footer.php'; ?>