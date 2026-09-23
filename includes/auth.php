<?php
/**
 * Autenticación: un solo login para todo el sistema.
 *
 * Cada persona tiene su propia cuenta en la tabla `usuarios` (correo +
 * contraseña), con un rol que decide qué puede ver:
 *   - 'admin'    -> panel del encargado (materiales, solicitudes, personal).
 *   - 'empleado' -> solo Solicitar material.
 *
 * Reemplaza la contraseña única que antes vivía en config.local.php.
 */

/** Devuelve el usuario en sesión (o null si nadie ha iniciado sesión). */
function usuario_actual(): ?array
{
    return $_SESSION['usuario'] ?? null;
}

/** Guarda al usuario en sesión justo después de un login o registro exitoso. */
function usuario_login(array $usuario): void
{
    session_regenerate_id(true);
    $_SESSION['usuario'] = [
        'id' => (int)$usuario['id'],
        'correo' => $usuario['correo'],
        'rol' => $usuario['rol'],
    ];
    // Compatibilidad con includes/header.php, que decide qué pestañas
    // mostrar mirando esta misma variable.
    $_SESSION['is_admin'] = $usuario['rol'] === 'admin';
}

/** A dónde mandar a alguien justo después de iniciar sesión o registrarse. */
function destino_tras_login(string $rol, ?string $next = null): string
{
    if ($next !== null && $next !== '') {
        return $next;
    }
    return $rol === 'admin' ? 'panel.php' : 'solicitar.php';
}

/** Protege una página para que solo entre alguien con sesión iniciada (cualquier rol). */
function require_login(): void
{
    if (empty($_SESSION['usuario'])) {
        $next = urlencode($_SERVER['REQUEST_URI'] ?? 'solicitar.php');
        header('Location: login.php?next=' . $next);
        exit;
    }
}

/** Protege una página para que solo entre alguien con rol 'admin'. */
function require_admin(): void
{
    require_login();
    if (($_SESSION['usuario']['rol'] ?? '') !== 'admin') {
        flash_set('error', 'Tu cuenta no tiene acceso al panel de administrador.');
        header('Location: solicitar.php');
        exit;
    }
}