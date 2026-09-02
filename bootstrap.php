<?php
/**
 * Arranque común: se incluye al principio de cada página pública.
 */

declare(strict_types=1);

session_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/flash.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/teams.php';

/** Escapa texto para imprimirlo seguro dentro de HTML. */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}
