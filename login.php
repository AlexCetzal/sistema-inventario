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
    $next = $_POST['next'] ?? $next;

    $pdo = get_db();
    $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE correo = ? LIMIT 1');
    $stmt->execute([$correo]);
    $usuario = $stmt->fetch();

    if (!$usuario || !password_verify($password, $usuario['password_hash'])) {
        $error = 'Usuario o contraseña incorrectos.';
    } else {
        usuario_login($usuario);
        header('Location: ' . destino_tras_login($usuario['rol'], $next));
        exit;
    }
}

$pageTitle = 'Iniciar sesión · Vale de Suministros';
$activePage = 'login';
require __DIR__ . '/includes/header.php';
?>
<div class="card login-standalone">
  <h2>Iniciar sesión</h2>
  <p class="hint" style="margin-bottom:18px;">Entra con tu cuenta de DESUR para usar el sistema.</p>

  <?php if ($error): ?>
    <p class="hint" style="color:var(--critical); font-weight:600; margin-bottom:14px;"><?= e($error) ?></p>
  <?php endif; ?>

  <form method="post" action="login.php">
    <input type="hidden" name="next" value="<?= e($next) ?>">
    <div class="field">
      <label for="correo">Usuario o correo</label>
      <input type="text" id="correo" name="correo" value="<?= e($correo) ?>" required autofocus>
    </div>
    <div class="field">
      <label for="password">Contraseña</label>
      <input type="password" id="password" name="password" required>
    </div>
    <div class="submit-row" style="justify-content: stretch;">
      <button type="submit" class="btn btn-primary" style="width:100%; text-align:center;">Entrar</button>
    </div>
  </form>

  <div class="login-divider"><span>o</span></div>

  <a href="registro.php<?= $next !== '' ? '?next=' . urlencode($next) : '' ?>" class="btn btn-ghost" style="width:100%; text-align:center;">Registrarme</a>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>