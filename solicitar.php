<?php
require_once __DIR__ . '/bootstrap.php';

$pdo = get_db();
$materiales = $pdo->query(
    "SELECT * FROM materiales WHERE categoria = 'oficina' ORDER BY nombre"
)->fetchAll();

$trabajadores = $pdo->query(
  "SELECT id, nombre, area FROM trabajadores
  WHERE  status = 'Activo' AND nombre <> 'VACANTE'
  ORDER BY nombre"
)->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $trabajadorId = (int)($_POST['trabajador_id'] ?? 0);

    $nombre = '';
    $area = '';

    if ($trabajadorId > 0) {

        $stmtTrabajador = $pdo->prepare(
            "SELECT nombre, area
             FROM trabajadores
             WHERE id = ?
             AND status = 'Activo'
             LIMIT 1"
        );

        $stmtTrabajador->execute([$trabajadorId]);

        $trabajador = $stmtTrabajador->fetch();

        if ($trabajador) {
            $nombre = $trabajador['nombre'];
            $area = $trabajador['area'];
        }
    }
    $urgenciaPost = $_POST['urgencia'] ?? 'normal';
    $urgencia = in_array($urgenciaPost, ['normal', 'urgente'], true) ? $urgenciaPost : 'normal';
    $nota = trim($_POST['nota'] ?? '');
    $materialesPost = $_POST['materiales'] ?? [];

    // material_id => cantidad, solo lo que llegó con una cantidad válida
    $seleccion = [];
    foreach ($materialesPost as $materialId => $datos) {
        $materialId = (int)$materialId;
        $cantidad = (int)($datos['cantidad'] ?? 0);
        if ($materialId > 0 && $cantidad >= 1) {
            $seleccion[$materialId] = $cantidad;
        }
    }

    if ($nombre === '') {
        flash_set('error', 'Selecciona un trabajador vigente para enviar la solicitud.');
    } elseif ($area === '') {
        flash_set('error', 'Selecciona tu área.');
    } elseif (!$seleccion) {
        flash_set('error', 'Selecciona al menos un material.');
    } else {
        $fecha = date('Y-m-d H:i:s');
        $folios = [];

        try {
            $pdo->beginTransaction();

            $stmtMat = $pdo->prepare("SELECT * FROM materiales WHERE id = ? AND categoria = 'oficina'");
            $stmtInsert = $pdo->prepare(
                "INSERT INTO solicitudes (nombre, area, material_id, cantidad, urgencia, nota, fecha_creacion, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, 'pendiente')"
            );

            $paraNotificar = [];
            foreach ($seleccion as $materialId => $cantidad) {
                $stmtMat->execute([$materialId]);
                $material = $stmtMat->fetch();
                if (!$material) {
                    continue; // material inválido o ya no es de oficina; se ignora
                }

                $stmtInsert->execute([$nombre, $area, $material['id'], $cantidad, $urgencia, $nota, $fecha]);
                $folio = (int)$pdo->lastInsertId();
                $folios[] = $folio;
                $paraNotificar[] = ['folio' => $folio, 'material' => $material['nombre'], 'cantidad' => $cantidad];
            }

            $pdo->commit();

            if ($folios) {
                $_SESSION['ultimo_nombre'] = $nombre;
                notificar_teams_solicitud($nombre, $area, $urgencia, $nota, $paraNotificar);

                $foliosTxt = implode(', ', $folios);
                $mensaje = count($folios) > 1 ? "Solicitud enviada · Folios {$foliosTxt}" : "Solicitud enviada · Folio {$foliosTxt}";
                flash_set('ok', $mensaje);
                header('Location: solicitar.php');
                exit;
            }

            flash_set('error', 'Ninguno de los materiales seleccionados es válido. Intenta de nuevo.');
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            flash_set('error', 'Ocurrió un error al enviar la solicitud. Intenta de nuevo.');
        }
    }
}

$ultimoNombre = $_SESSION['ultimo_nombre'] ?? '';
$recientes = [];
if ($ultimoNombre !== '') {
    $stmt = $pdo->prepare(
        "SELECT s.*, m.nombre AS material_nombre, m.unidad
         FROM solicitudes s JOIN materiales m ON m.id = s.material_id
         WHERE s.nombre = ? ORDER BY s.folio DESC LIMIT 5"
    );
    $stmt->execute([$ultimoNombre]);
    $recientes = $stmt->fetchAll();
}

$materialesJson = array_map(
    fn($m) => ['id' => (int)$m['id'], 'nombre' => $m['nombre'], 'unidad' => $m['unidad'], 'stock' => (int)$m['stock']],
    $materiales
);

$pageTitle = 'Solicitar material · Vale de Suministros';
$activePage = 'solicitar';
require __DIR__ . '/includes/header.php';
?>
<div class="form-layout">
  <div class="card voucher">
    <div class="voucher-head">
      <h2>Formulario de solicitud</h2>
      <span class="voucher-id mono">Materiales de oficina</span>
    </div>

    <form method="post" action="solicitar.php" id="form-solicitud">
      <div class="field">
    <label for="trabajador-buscar">
        Nombre de quien solicita
    </label>

    <div class="material-picker">

        <input
            type="text"
            id="trabajador-buscar"
            placeholder="Escribe tu nombre..."
            autocomplete="off"
            required
        >

        <div
            class="material-opciones"
            id="trabajador-opciones"
            hidden
        ></div>

    </div>

    <input
        type="hidden"
        id="trabajador_id"
        name="trabajador_id"
    >
</div>

    <div class="field">

        <label for="area">
            Área / departamento
        </label>

        <input
            type="text"
            id="area"
            value=""
            placeholder="Se llenará automáticamente"
            readonly
        >

    </div>

      <div class="field">
        <label for="material-buscar">Materiales</label>
        <div class="material-picker">
          <input type="text" id="material-buscar" placeholder="Escribe para buscar un material de oficina..." autocomplete="off">
          <div class="material-opciones" id="material-opciones" hidden></div>
        </div>
        <p class="hint">Los materiales de limpieza los gestiona directamente el encargado. Puedes agregar varios y ajustar la cantidad de cada uno.</p>
        <p class="hint hint-error" id="material-error" hidden>Selecciona al menos un material.</p>
        <div class="kit-list" id="material-seleccionados" hidden></div>
      </div>

      <div class="field">
        <label>Urgencia</label>
        <div class="pill-group">
          <label class="pill">
            <input type="radio" name="urgencia" value="normal" checked>
            Normal
          </label>
          <label class="pill pill-urgent">
            <input type="radio" name="urgencia" value="urgente">
            Urgente
          </label>
        </div>
      </div>

      <div class="field">
        <label for="nota">Notas (opcional)</label>
        <textarea id="nota" name="nota" placeholder="Ej. Es para la sala de juntas, se necesita antes del viernes"></textarea>
      </div>

      <div class="submit-row">
        <button type="submit" class="btn btn-primary">Enviar solicitud</button>
      </div>
    </form>
  </div>

  <div class="card side-panel">
    <h3>Tus solicitudes recientes</h3>
    <p>Se muestran las últimas solicitudes hechas con este mismo nombre.</p>
    <?php if ($recientes): ?>
      <?php foreach ($recientes as $r): ?>
        <?php
          $estilo = 'chip-neutral';
          if ($r['status'] === 'aprobada') { $estilo = 'chip-good'; }
          if ($r['status'] === 'rechazada') { $estilo = 'chip-critical'; }
        ?>
        <div class="recent-item">
          <div>
            <div class="rmat"><?= e($r['material_nombre']) ?> × <?= (int)$r['cantidad'] ?></div>
            <div class="rmeta">Folio <?= (int)$r['folio'] ?> · <?= e(substr($r['fecha_creacion'], 0, 10)) ?></div>
          </div>
          <span class="chip <?= $estilo ?>"><?= e($r['status']) ?></span>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p class="empty-note">Aún no has enviado ninguna solicitud.</p>
    <?php endif; ?>
  </div>
</div>

<script>
  var MATERIALES = <?= json_encode($materialesJson, JSON_UNESCAPED_UNICODE) ?>;
  var TRABAJADORES = <?= json_encode($trabajadores, JSON_UNESCAPED_UNICODE) ?>;

  (function () {

    var buscarTrabajador = document.getElementById(
        'trabajador-buscar'
    );

    var opcionesTrabajador = document.getElementById(
        'trabajador-opciones'
    );

    var trabajadorId = document.getElementById(
        'trabajador_id'
    );

    var area = document.getElementById(
        'area'
    );


    function buscarTrabajadores(consulta) {

        consulta = consulta.trim().toLowerCase();

        opcionesTrabajador.innerHTML = '';

        if (consulta === '') {
            opcionesTrabajador.hidden = true;
            return;
        }


        var coincidencias = TRABAJADORES.filter(function (trabajador) {

            return trabajador.nombre
                .toLowerCase()
                .indexOf(consulta) !== -1;

        }).slice(0, 8);


        if (coincidencias.length === 0) {

            opcionesTrabajador.innerHTML =
                '<div class="material-opcion material-opcion-vacio">' +
                'No se encontró ningún trabajador vigente' +
                '</div>';

            opcionesTrabajador.hidden = false;

            return;
        }


        coincidencias.forEach(function (trabajador) {

            var opcion = document.createElement('div');

            opcion.className = 'material-opcion';

            opcion.innerHTML =
                '<strong>' + trabajador.nombre + '</strong>' +
                '<br>' +
                '<small>' + trabajador.area + '</small>';


            opcion.addEventListener(
                'mousedown',
                function (e) {

                    e.preventDefault();

                    seleccionarTrabajador(trabajador);

                }
            );


            opcionesTrabajador.appendChild(opcion);

        });


        opcionesTrabajador.hidden = false;

    }


    function seleccionarTrabajador(trabajador) {

        buscarTrabajador.value = trabajador.nombre;

        trabajadorId.value = trabajador.id;

        area.value = trabajador.area;

        opcionesTrabajador.hidden = true;

    }


    buscarTrabajador.addEventListener(
        'input',
        function () {

            // Si modifica el nombre después de seleccionarlo,
            // se elimina la selección anterior.
            trabajadorId.value = '';

            area.value = '';

            buscarTrabajadores(
                buscarTrabajador.value
            );

        }
    );


    buscarTrabajador.addEventListener(
        'focus',
        function () {

            if (
                buscarTrabajador.value.trim() !== ''
            ) {

                buscarTrabajadores(
                    buscarTrabajador.value
                );

            }

        }
    );


    document.addEventListener(
        'click',
        function (e) {

            if (
                e.target !== buscarTrabajador &&
                !opcionesTrabajador.contains(e.target)
            ) {

                opcionesTrabajador.hidden = true;

            }

        }
    );

})();

  (function () {
    var buscar = document.getElementById('material-buscar');
    var opciones = document.getElementById('material-opciones');
    var lista = document.getElementById('material-seleccionados');
    var errorMsg = document.getElementById('material-error');
    var form = document.getElementById('form-solicitud');
    var seleccionados = {}; // id (string) => material

    function escapeHtml(texto) {
      var div = document.createElement('div');
      div.textContent = texto;
      return div.innerHTML;
    }

    function renderOpciones(consulta) {
      consulta = consulta.trim().toLowerCase();
      opciones.innerHTML = '';
      if (consulta === '') {
        opciones.hidden = true;
        return;
      }
      var coincidencias = MATERIALES.filter(function (m) {
        return !seleccionados[m.id] && m.nombre.toLowerCase().indexOf(consulta) !== -1;
      }).slice(0, 8);

      if (coincidencias.length === 0) {
        opciones.innerHTML = '<div class="material-opcion material-opcion-vacio">Sin resultados</div>';
        opciones.hidden = false;
        return;
      }

      coincidencias.forEach(function (m) {
        var div = document.createElement('div');
        div.className = 'material-opcion';
        div.textContent = m.nombre;
        div.addEventListener('mousedown', function (e) {
          e.preventDefault(); // no perder el foco del input antes del click
          agregar(m);
        });
        opciones.appendChild(div);
      });
      opciones.hidden = false;
    }

    function agregar(material) {
      seleccionados[material.id] = material;
      buscar.value = '';
      opciones.hidden = true;
      errorMsg.hidden = true;
      renderSeleccionados();
      buscar.focus();
    }

    function quitar(id) {
      delete seleccionados[id];
      renderSeleccionados();
    }

    function textoAlerta(m, cantidad) {
      if (m.stock <= 0) {
        return 'No hay existencia disponible de este material ahora mismo.';
      }
      if (cantidad > m.stock) {
        return 'Solo hay ' + m.stock + ' ' + m.unidad + ' disponibles (pediste ' + cantidad + ').';
      }
      return '';
    }

    function actualizarAlerta(id) {
      var m = seleccionados[id];
      var item = lista.querySelector('.material-item[data-id="' + id + '"]');
      if (!m || !item) return;
      var input = item.querySelector('input[type="number"]');
      var alerta = item.querySelector('.material-alerta');
      var cantidad = parseInt(input.value, 10) || 0;
      var texto = textoAlerta(m, cantidad);
      alerta.textContent = texto;
      alerta.hidden = texto === '';
    }

    function renderSeleccionados() {
      var ids = Object.keys(seleccionados);
      lista.innerHTML = '';
      lista.hidden = ids.length === 0;

      ids.forEach(function (id) {
        var m = seleccionados[id];
        var item = document.createElement('div');
        item.className = 'material-item';
        item.setAttribute('data-id', id);

        var fila = document.createElement('div');
        fila.className = 'kit-row';
        fila.innerHTML =
          '<span class="kit-row-check"><span>' + escapeHtml(m.nombre) + '</span></span>' +
          '<span style="display:flex; align-items:center; gap:8px;">' +
          '<input type="number" name="materiales[' + m.id + '][cantidad]" value="1" min="1" step="1" class="qty-input qty-input-sm">' +
          '<button type="button" class="btn btn-ghost btn-sm material-quitar" data-id="' + m.id + '">Quitar</button>' +
          '</span>';

        var alerta = document.createElement('p');
        alerta.className = 'hint hint-error material-alerta';
        alerta.hidden = true;

        item.appendChild(fila);
        item.appendChild(alerta);
        lista.appendChild(item);

        fila.querySelector('input[type="number"]').addEventListener('input', function () {
          actualizarAlerta(id);
        });
        actualizarAlerta(id);
      });

      lista.querySelectorAll('.material-quitar').forEach(function (btn) {
        btn.addEventListener('click', function () {
          quitar(btn.getAttribute('data-id'));
        });
      });
    }

    buscar.addEventListener('input', function () {
      renderOpciones(buscar.value);
    });
    buscar.addEventListener('focus', function () {
      if (buscar.value.trim() !== '') {
        renderOpciones(buscar.value);
      }
    });
    document.addEventListener('click', function (e) {
      if (e.target !== buscar && !opciones.contains(e.target)) {
        opciones.hidden = true;
      }
    });

    form.addEventListener('submit', function (e) {
      if (Object.keys(seleccionados).length === 0) {
        e.preventDefault();
        errorMsg.hidden = false;
        buscar.focus();
      }
    });
  })();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
