<?php
/**
 * Mensajes flash: un aviso que se guarda antes de una redirección y se
 * muestra (una sola vez) en la siguiente página.
 */

function flash_set(string $category, string $message): void
{
    if (!isset($_SESSION['flash'])) {
        $_SESSION['flash'] = [];
    }
    $_SESSION['flash'][] = [$category, $message];
}

function flash_get_all(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}
