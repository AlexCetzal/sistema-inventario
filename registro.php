<?php
require_once __DIR__ . '/bootstrap.php';

// Si ya inició sesión, no tiene nada que hacer aquí.
if (usuario_actual()) {
    header('Location: ' . destino_tras_login(usuario_actual()['rol']));
    exit;
}

$error = null;
$next = $_GET['next'] ?? '';
$correo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmar = $_POST['confirmar'] ?? '';
    $next = $_POST['next'] ?? $next;

    if ($correo === '' || mb_strlen($correo) < 3) {
        $error = 'Escribe un usuario o correo válido.';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    } elseif ($password !== $confirmar) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        $pdo = get_db();
        $existe = $pdo->prepare('SELECT id FROM usuarios WHERE correo = ? LIMIT 1');
        $existe->execute([$correo]);

        if ($existe->fetch()) {
            $error = 'Ya existe una cuenta con ese usuario o correo. Inicia sesión en vez de registrarte.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $insertar = $pdo->prepare(
                'INSERT INTO usuarios (correo, password_hash, rol, fecha_creacion) VALUES (?, ?, \'empleado\', ?)'
            );
            $insertar->execute([$correo, $hash, date('Y-m-d H:i:s')]);

            $usuario = [
                'id' => (int)$pdo->lastInsertId(),
                'correo' => $correo,
                'rol' => 'empleado',
            ];
            usuario_login($usuario);
            flash_set('ok', 'Tu cuenta quedó creada.');
            header('Location: ' . destino_tras_login('empleado', $next));
            exit;
        }
    }
}

$pageTitle = 'Crear cuenta · Vale de Suministros';
$activePage = 'login';
require __DIR__ . '/includes/header.php';
?>
<div class="card login-standalone">
  <h2>Crea tu cuenta</h2>
  <p class="hint" style="margin-bottom:18px;">Regístrate para entrar al sistema de materiales de DESUR.</p>

  <?php if ($error): ?>
    <p class="hint" style="color:var(--critical); font-weight:600; margin-bottom:14px;"><?= e($error) ?></p>
  <?php endif; ?>

  <form method="post" action="registro.php">
    <input type="hidden" name="next" value="<?= e($next) ?>">
    <div class="field">
      <label for="correo">Usuario o correo</label>
      <input type="text" id="correo" name="correo" value="<?= e($correo) ?>" required autofocus>
    </div>
    <div class="field">
      <label for="password">Contraseña</label>
      <input type="password" id="password" name="password" required minlength="6">
    </div>
    <div class="field">
      <label for="confirmar">Confirmar contraseña</label>
      <input type="password" id="confirmar" name="confirmar" required minlength="6">
    </div>
    <div class="submit-row" style="justify-content: stretch;">
      <button type="submit" class="btn btn-primary" style="width:100%; text-align:center;">Crear cuenta y entrar</button>
    </div>
  </form>

  <p class="hint" style="margin-top:16px; padding-top:14px; border-top:1px dashed var(--border);">
    Al crear tu cuenta entras directo al sistema. Tu nombre y área los captura el encargado desde el panel, no aquí.
    ¿Ya tienes cuenta? <a href="login.php<?= $next !== '' ? '?next=' . urlencode($next) : '' ?>">Inicia sesión</a>.
  </p>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>