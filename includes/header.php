<?php
/**
 * Encabezado compartido. Espera (opcionalmente) $pageTitle y $activePage
 * definidas por la página que lo incluye.
 */
$pageTitle = $pageTitle ?? 'Vale de Suministros';
$activePage = $activePage ?? '';
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800&family=IBM+Plex+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <div class="wrap">
    <header class="page-header">
      <div class="brand-mark">
        <img class="brand-logo brand-logo-light" src="img/desur-logo-light.png" alt="DESUR">
        <img class="brand-logo brand-logo-dark" src="img/desur-logo-dark.png" alt="DESUR">
        <span class="brand-divider" aria-hidden="true"></span>
        <div>
          <h1>Vale de Suministros</h1>
          <p>Materiales de oficina y limpieza</p>
        </div>
      </div>
      <nav class="tabs" role="tablist">
        <?php if (!empty($_SESSION['usuario'])): ?>
          <a href="solicitar.php" class="<?= $activePage === 'solicitar' ? 'active' : '' ?>">Solicitar material</a>
        <?php endif; ?>
        <?php if (!empty($_SESSION['is_admin'])): ?>
          <a href="panel.php" class="<?= $activePage === 'panel' ? 'active' : '' ?>">Panel del encargado</a>
        <?php endif; ?>
        <?php if (!empty($_SESSION['usuario'])): ?>
          <a href="logout.php">
            <svg class="lock-ico" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2"></rect><path d="M8 11V7a4 4 0 0 1 8 0v4"></path></svg>
            Cerrar sesión
          </a>
        <?php else: ?>
          <a href="login.php" class="<?= $activePage === 'login' ? 'active' : '' ?>">
            <svg class="lock-ico" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2"></rect><path d="M8 11V7a4 4 0 0 1 8 0v4"></path></svg>
            Iniciar sesión
          </a>
        <?php endif; ?>
      </nav>
    </header>

    <?php $mensajes = flash_get_all(); ?>
    <?php if ($mensajes): ?>
      <div class="flash-list">
        <?php foreach ($mensajes as [$categoria, $mensaje]): ?>
          <div class="flash flash-<?= $categoria === 'ok' ? 'ok' : 'error' ?>"><?= e($mensaje) ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
