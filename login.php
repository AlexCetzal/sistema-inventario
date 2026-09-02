<?php
require_once __DIR__ . '/bootstrap.php';

$error = null;
$next = $_GET['next'] ?? 'panel.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $next = $_POST['next'] ?? $next;

    if (ADMIN_PASSWORD_HASH === '') {
        $error = 'Todavía no se configuró la contraseña del panel (ejecuta set_admin_password.php).';
    } elseif (password_verify($password, ADMIN_PASSWORD_HASH)) {
        session_regenerate_id(true);
        $_SESSION['is_admin'] = true;
        header('Location: ' . ($next !== '' ? $next : 'panel.php'));
        exit;
    } else {
        $error = 'Contraseña incorrecta.';
    }
}

$pageTitle = 'Acceso administrador · Vale de Suministros';
$activePage = 'login';
require __DIR__ . '/includes/header.php';
?>
<div class="card login-standalone">
  <h2>Acceso administrador</h2>
  <p class="hint" style="margin-bottom:18px;">Esta sección solo la debe usar el encargado de materiales.</p>

  <?php if ($error): ?>
    <p class="hint" style="color:var(--critical); font-weight:600; margin-bottom:14px;"><?= e($error) ?></p>
  <?php endif; ?>

  <form method="post" action="login.php">
    <input type="hidden" name="next" value="<?= e($next) ?>">
    <div class="field">
      <label for="password">Contraseña</label>
      <input type="password" id="password" name="password" required autofocus>
    </div>
    <div class="submit-row">
      <button type="submit" class="btn btn-primary">Entrar</button>
    </div>
  </form>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
