<?php
/**
 * Protege una página para que solo el encargado (con sesión iniciada) entre.
 * Llamar al principio de cualquier página que deba quedar detrás del login.
 */

function require_admin(): void
{
    if (empty($_SESSION['is_admin'])) {
        $next = urlencode($_SERVER['REQUEST_URI'] ?? 'panel.php');
        header('Location: login.php?next=' . $next);
        exit;
    }
}
