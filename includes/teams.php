<?php
/**
 * Avisos a Microsoft Teams. Si no hay webhook configurado, no hacen nada.
 * Un fallo de red aquí nunca debe tumbar la operación: lo que sea que se
 * esté avisando ya quedó guardado en la base de datos antes de llamar a
 * estas funciones.
 */

/**
 * Envía cualquier payload de MessageCard al webhook configurado.
 */
function enviar_teams(array $payload): void
{
    $webhookUrl = TEAMS_WEBHOOK_URL;
    if ($webhookUrl === '') {
        return;
    }

    $ch = curl_init($webhookUrl);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_CONNECTTIMEOUT => 5,
    ]);
    curl_exec($ch); // si falla, simplemente no se notifica; ya se guardó en la base de datos
    curl_close($ch);
}

/**
 * Aviso cuando llega una solicitud nueva de un empleado.
 */
function notificar_teams(
    int $folio,
    string $nombre,
    string $area,
    string $materialNombre,
    int $cantidad,
    string $urgencia,
    string $nota
): void {
    $texto = "🔔 **Nueva solicitud pendiente de revisión**\n\n";
    $texto .= "**{$nombre}** de {$area} solicitó **{$cantidad} {$materialNombre}**.";
    $texto .= "\n\n**Folio:** {$folio}";
    $texto .= "\n**Urgencia:** " . strtoupper($urgencia);

    if ($nota !== '') {
        $texto .= "\n**Nota:** {$nota}";
    }

    $texto .= "\n\nPor favor, revisa la solicitud.";

    enviar_teams([
        '@type' => 'MessageCard',
        '@context' => 'http://schema.org/extensions',
        'themeColor' => $urgencia === 'urgente' ? 'B4392F' : '2C5F63',
        'summary' => "Nueva solicitud de material · Folio {$folio}",
        'title' => "Nueva solicitud de material · Folio {$folio}",
        'text' => $texto,
    ]);
}

/**
 * Aviso cuando llega una solicitud nueva de un empleado, con uno o varios
 * materiales de golpe: manda UNA sola notificación con todo listado, en vez
 * de una por cada material.
 *
 * $items: arreglo de ['folio' => int, 'material' => string, 'cantidad' => int]
 */
function notificar_teams_solicitud(string $nombre, string $area, string $urgencia, string $nota, array $items): void
{
    $lista = implode("\n", array_map(
        fn($it) => "- {$it['cantidad']} {$it['material']} (folio {$it['folio']})",
        $items
    ));
    $folios = implode(', ', array_column($items, 'folio'));

    $texto = "🔔 **Nueva solicitud pendiente de revisión**\n\n";
    $texto .= "**{$nombre}** de {$area} solicitó:\n\n{$lista}";
    $texto .= "\n\n**Urgencia:** " . strtoupper($urgencia);

    if ($nota !== '') {
        $texto .= "\n**Nota:** {$nota}";
    }

    $texto .= "\n\nPor favor, revisa la solicitud.";

    enviar_teams([
        '@type' => 'MessageCard',
        '@context' => 'http://schema.org/extensions',
        'themeColor' => $urgencia === 'urgente' ? 'B4392F' : '2C5F63',
        'summary' => "Nueva solicitud de material · Folio" . (count($items) > 1 ? 's ' : ' ') . $folios,
        'title' => "Nueva solicitud de material · Folio" . (count($items) > 1 ? 's ' : ' ') . $folios,
        'text' => $texto,
    ]);
}

/**
 * Aviso cuando el encargado entrega un kit de bienvenida (varios materiales
 * de golpe, ya aprobados).
 */
function notificar_teams_kit(string $nombre, string $area, array $entregados): void
{
    $lista = implode("\n", array_map(fn($linea) => "- {$linea}", $entregados));
    $texto = "🎉 **Kit de bienvenida entregado**\n\n";
    $texto .= "Se le entregó a **{$nombre}** ({$area}):\n\n{$lista}";

    enviar_teams([
        '@type' => 'MessageCard',
        '@context' => 'http://schema.org/extensions',
        'themeColor' => '2C5F63',
        'summary' => "Kit de bienvenida entregado a {$nombre}",
        'title' => 'Kit de bienvenida entregado',
        'text' => $texto,
    ]);
}
