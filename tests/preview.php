<?php

require __DIR__ . '/bootstrap.php';

$GLOBALS['_remote_callback'] = static function (string $url): array {
    parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
    $base = ['diesel' => 1900, 'e5' => 2000, 'e10' => 1950][$query['fuel'] ?? 'diesel'];
    return [
        'response' => ['code' => 200],
        'body' => json_encode([
            ['id' => 'a', 'name' => 'Tankstelle am Markt', 'street' => 'Marktstraße 1', 'postcode' => '09111', 'city' => 'Chemnitz', 'priceCents' => $base, 'priceTs' => '2026-08-19T10:00:00Z', 'isActive' => true],
            ['id' => 'b', 'name' => 'City Tank', 'street' => 'Bahnhofstraße 8', 'postcode' => '09111', 'city' => 'Chemnitz', 'priceCents' => $base + 100, 'priceTs' => '2026-08-19T10:02:00Z', 'isActive' => true],
            ['id' => 'c', 'name' => 'Nord Tank', 'street' => 'Leipziger Straße 20', 'postcode' => '09113', 'city' => 'Chemnitz', 'priceCents' => $base + 200, 'priceTs' => '2026-08-19T10:03:00Z', 'isActive' => true],
        ], JSON_THROW_ON_ERROR),
    ];
};

$plugin = AFDSP\Plugin::instance();
?><!doctype html>
<html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>AfD Spritpreise – Layout QA</title><link rel="stylesheet" href="../assets/css/frontend.css"><style>body{margin:0;padding:20px;background:#dfe7ed;font-family:system-ui}.qa{display:grid;gap:24px;max-width:1100px;margin:auto}.qa-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,320px),1fr));gap:16px}</style></head><body><main class="qa">
<?php echo $plugin->render(['displayMode' => 'full', 'areaLabel' => 'Chemnitzer Stadtgebiet']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
<div class="qa-grid"><?php echo $plugin->render(['displayMode' => 'compact', 'defaultFuel' => 'diesel', 'areaLabel' => 'Chemnitz']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo $plugin->render(['displayMode' => 'compact', 'defaultFuel' => 'e10', 'areaLabel' => 'Sehr lange Gebietsbezeichnung für eine schmale Gutenberg-Spalte']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
</main><script src="../assets/js/frontend.js"></script></body></html>
