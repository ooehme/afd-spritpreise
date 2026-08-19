<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

use AFDSP\Activator;
use AFDSP\BoundingBox;
use AFDSP\Cache;
use AFDSP\ComponentRenderer;
use AFDSP\GithubUpdater;
use AFDSP\Options;
use AFDSP\Plugin;
use AFDSP\PricingCalculator;
use AFDSP\Shortcode;
use AFDSP\TankpulsClient;

$tests = [];

function test(string $name, callable $callback): void { global $tests; $tests[$name] = $callback; }
function assert_true(bool $condition, string $message = 'Assertion failed'): void { if (!$condition) throw new RuntimeException($message); }
function assert_same(mixed $expected, mixed $actual, string $message = ''): void { if ($expected !== $actual) throw new RuntimeException($message ?: 'Expected ' . var_export($expected, true) . ', got ' . var_export($actual, true)); }
function assert_near(float $expected, float $actual, float $epsilon = 0.000001): void { if (abs($expected - $actual) > $epsilon) throw new RuntimeException("Expected {$expected}, got {$actual}"); }
function response(array $rows): array { return ['response' => ['code' => 200], 'body' => json_encode($rows, JSON_THROW_ON_ERROR)]; }

test('Photon extent → Bounding Box', function (): void {
    $box = BoundingBox::from_photon_extent([13.23727, 52.5157151, 13.241757, 52.5135972]);
    assert_near(52.5135972, $box->minLat);
    assert_near(13.23727, $box->minLng);
    assert_near(52.5157151, $box->maxLat);
    assert_near(13.241757, $box->maxLng);
});

test('Bounding-Box-Validierung', function (): void {
    $thrown = false;
    try { new BoundingBox(51, 13, 50, 14); } catch (InvalidArgumentException) { $thrown = true; }
    assert_true($thrown, 'Inverted latitude must fail.');
});

test('TankPuls-Requestparameter', function (): void {
    $client = new TankpulsClient(Options::defaults()['tankpuls']);
    $url = $client->request_url(new BoundingBox(50.1, 12.2, 50.9, 13.1), 'e10');
    parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
    assert_same('50.1', $query['minLat']);
    assert_same('12.2', $query['minLng']);
    assert_same('50.9', $query['maxLat']);
    assert_same('13.1', $query['maxLng']);
    assert_same('e10', $query['fuel']);
    assert_same('200', $query['limit']);
});

test('Median ungerade', fn () => assert_near(2.00, PricingCalculator::median([1.90, 2.00, 2.10])));
test('Median gerade', fn () => assert_near(2.05, PricingCalculator::median([1.90, 2.00, 2.10, 2.20])));

test('Günstigste Tankstelle mit deterministischem Tie-Break', function (): void {
    $station = PricingCalculator::cheapest([['id' => 'b', 'price' => 1.9], ['id' => 'a', 'price' => 1.9], ['id' => 'c', 'price' => 2.0]]);
    assert_same('a', $station['id']);
});

test('Szenariorechnung', function (): void {
    $calc = (new PricingCalculator(Options::defaults()['calculation']))->calculate(2.217, 'e5');
    $expectedNet = 2.217 / 1.19;
    $expectedCo2 = 2.394 * 65 / 1000;
    $expectedGross = ($expectedNet - 0.6545 - $expectedCo2 + 0.3590) * 1.07;
    assert_near($expectedGross, $calc['scenario_gross']);
    assert_near((2.217 - $expectedGross) * 50, $calc['saving_50l']);
});

test('Cache Miss und Hit', function (): void {
    Cache::clear_all();
    $calls = 0;
    $cache = new Cache();
    $first = $cache->remember('cache-basic', 900, function () use (&$calls): array { $calls++; return ['ok']; });
    $second = $cache->remember('cache-basic', 900, function () use (&$calls): array { $calls++; return ['bad']; });
    assert_same('miss', $first['status']);
    assert_same('hit', $second['status']);
    assert_same(1, $calls);
});

test('Cache Expiry', function (): void {
    $hash = hash('sha256', 'cache-expiry');
    $GLOBALS['_transients']['afdsp_data_' . $hash] = ['data' => ['old'], 'expires_at' => time() - 1];
    $GLOBALS['_transients']['afdsp_stale_' . $hash] = ['data' => ['old'], 'expires_at' => time() - 1];
    $result = (new Cache())->remember('cache-expiry', 900, fn (): array => ['new']);
    assert_same('miss', $result['status']);
    assert_same(['new'], $result['data']);
});

test('Paralleler Refresh / Lock', function (): void {
    $hash = hash('sha256', 'cache-lock');
    $GLOBALS['_options']['afdsp_lock_' . $hash] = time() + 60;
    $GLOBALS['_transients']['afdsp_stale_' . $hash] = ['data' => ['stale'], 'expires_at' => time() - 1];
    $result = (new Cache())->remember('cache-lock', 900, fn (): array => ['new']);
    assert_same('locked-stale', $result['status']);
    assert_true($result['stale']);
    unset($GLOBALS['_options']['afdsp_lock_' . $hash]);
});

test('Stale-if-error', function (): void {
    $hash = hash('sha256', 'cache-error');
    $GLOBALS['_transients']['afdsp_stale_' . $hash] = ['data' => ['last-good'], 'expires_at' => time() - 1];
    $result = (new Cache())->remember('cache-error', 900, function (): never { throw new RuntimeException('offline'); });
    assert_same('error-stale', $result['status']);
    assert_same(['last-good'], $result['data']);
});

test('TankPuls-Antwortfilter', function (): void {
    $GLOBALS['_remote_callback'] = fn (): array => [
        'response' => ['code' => 200],
        'body' => json_encode(['items' => [
            ['id' => 'ok', 'name' => 'Aktiv', 'priceCents' => 1999, 'status' => 'open', 'isActive' => true],
            ['id' => 'closed', 'priceCents' => 1899, 'status' => 'closed', 'isActive' => true],
            ['id' => 'inactive', 'priceCents' => 1799, 'status' => 'open', 'isActive' => false],
        ]], JSON_THROW_ON_ERROR),
    ];
    $rows = (new TankpulsClient(Options::defaults()['tankpuls']))->search(new BoundingBox(50, 12, 51, 13), 'diesel');
    assert_same(1, count($rows));
    assert_near(1.999, $rows[0]['price']);
});

test('Full- und Compact-Renderer', function (): void {
    Cache::clear_all();
    $GLOBALS['_remote_callback'] = function (string $url): array {
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
        $base = ['diesel' => 1900, 'e5' => 2000, 'e10' => 1950][$query['fuel']];
        return response([
            ['id' => 'b', 'name' => 'Tank B', 'street' => 'B-Straße 2', 'postcode' => '09111', 'city' => 'Chemnitz', 'priceCents' => $base + 100, 'priceTs' => '2026-08-19T10:00:00Z', 'isActive' => true],
            ['id' => 'a', 'name' => 'Tank A', 'street' => 'A-Straße 1', 'postcode' => '09111', 'city' => 'Chemnitz', 'priceCents' => $base, 'priceTs' => '2026-08-19T10:00:00Z', 'isActive' => true],
            ['id' => 'c', 'name' => 'Tank C', 'priceCents' => $base + 200, 'isActive' => true],
        ]);
    };
    $plugin = Plugin::instance();
    $full = $plugin->render(['displayMode' => 'full']);
    $compact = $plugin->render(['displayMode' => 'compact', 'defaultFuel' => 'e10']);
    assert_true(str_contains($full, 'afdsp--full') && str_contains($full, 'Forderungen und Quellen'));
    assert_true(str_contains($compact, 'afdsp--compact') && !str_contains($compact, 'afdsp-receipt'));
});

test('Gutenberg-Komponenten und Datenbindung', function (): void {
    Cache::clear_all();
    $GLOBALS['_remote_callback'] = function (string $url): array {
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
        $base = ['diesel' => 1900, 'e5' => 2000, 'e10' => 1950][$query['fuel']];
        return response([
            ['id' => 'a', 'name' => 'Tank A', 'street' => 'A-Straße 1', 'postcode' => '09111', 'city' => 'Chemnitz', 'priceCents' => $base, 'isActive' => true],
            ['id' => 'b', 'name' => 'Tank B', 'priceCents' => $base + 100, 'isActive' => true],
        ]);
    };
    $renderer = new ComponentRenderer(Plugin::instance());
    $context = new stdClass();
    $context->context = [
        'afdsp/minLat' => 50.7, 'afdsp/minLng' => 12.7, 'afdsp/maxLat' => 50.9, 'afdsp/maxLng' => 13.1,
        'afdsp/areaLabel' => 'Chemnitz', 'afdsp/defaultFuel' => 'diesel',
    ];
    $metric = $renderer->metric(['metric' => 'current', 'fontFamily' => 'heading', 'fontWeight' => '700'], '', $context);
    $parent = $renderer->parent(['minLat' => 50.7, 'minLng' => 12.7, 'maxLat' => 50.9, 'maxLng' => 13.1, 'areaLabel' => 'Chemnitz', 'defaultFuel' => 'diesel'], '<div data-afdsp-tab>Komponenten</div>', $context);
    assert_true(str_contains($metric, 'data-afdsp-bind="current"'));
    assert_true(str_contains($metric, '--wp--preset--font-family--heading'));
    assert_true(str_contains($parent, 'data-afdsp-builder'));
    assert_true(str_contains($parent, '"e5"'));
    assert_true(str_contains($parent, 'Komponenten</div>'));
});

test('Price-Board Raster, Liste und Spalten', function (): void {
    $renderer = new ComponentRenderer(Plugin::instance());
    $grid = $renderer->price_board(['layoutMode' => 'grid', 'columns' => 4], '<span>A</span>');
    $list = $renderer->price_board(['layoutMode' => 'list', 'columns' => 2], '<span>B</span>');
    assert_true(str_contains($grid, 'afdsp-layout--grid') && str_contains($grid, '--afdsp-columns:4'));
    assert_true(str_contains($list, 'afdsp-layout--list'));
});

test('Block-Metadaten aktivieren native Gutenberg-Stile', function (): void {
    foreach (['header', 'fuel-tabs', 'price-board', 'metric', 'facts', 'tank-saving', 'cheapest-station', 'demands', 'method'] as $directory) {
        $metadata = json_decode(file_get_contents(AFDSP_DIR . 'block/' . $directory . '/block.json'), true, 512, JSON_THROW_ON_ERROR);
        assert_true(!empty($metadata['supports']['color']['background']), $directory . ': background support missing');
        assert_true(!empty($metadata['supports']['typography']['fontSize']), $directory . ': font-size support missing');
        assert_true(!empty($metadata['supports']['spacing']['padding']), $directory . ': padding support missing');
        assert_true(!empty($metadata['supports']['border']['radius']), $directory . ': border support missing');
    }
});

test('Mehrere Blockinstanzen mit unterschiedlichen Bounding Boxes', function (): void {
    Cache::clear_all();
    $GLOBALS['_remote_urls'] = [];
    $GLOBALS['_remote_callback'] = fn (): array => response([
        ['id' => 'a', 'name' => 'Tank A', 'priceCents' => 1900, 'isActive' => true],
        ['id' => 'b', 'name' => 'Tank B', 'priceCents' => 2000, 'isActive' => true],
    ]);
    $plugin = Plugin::instance();
    $plugin->render(['displayMode' => 'compact', 'minLat' => 50.0, 'minLng' => 12.0, 'maxLat' => 50.5, 'maxLng' => 12.5, 'areaLabel' => 'A']);
    $plugin->render(['displayMode' => 'compact', 'minLat' => 51.0, 'minLng' => 13.0, 'maxLat' => 51.5, 'maxLng' => 13.5, 'areaLabel' => 'B']);
    assert_true(count(array_filter($GLOBALS['_remote_urls'], fn ($url) => str_contains($url, 'minLat=50'))) === 1);
    assert_true(count(array_filter($GLOBALS['_remote_urls'], fn ($url) => str_contains($url, 'minLat=51'))) === 1);
});

test('Shortcode', function (): void {
    $html = (new Shortcode(Plugin::instance()))->render(['fuel' => 'diesel', 'display' => 'compact', 'area' => 'Testgebiet']);
    assert_true(str_contains($html, 'Testgebiet · Diesel'));
});

test('Aktivierung und Deaktivierung', function (): void {
    unset($GLOBALS['_options'][Options::OPTION]);
    Activator::activate();
    assert_true(is_array($GLOBALS['_options'][Options::OPTION]));
    assert_same(AFDSP_VERSION, $GLOBALS['_options']['afdsp_version']);
    Activator::deactivate();
    assert_same('afdsp_scheduled_refresh', $GLOBALS['_cleared_hook']);
});

test('GitHub-Updater', function (): void {
    unset($GLOBALS['_site_transients']['afdsp_github_release']);
    $GLOBALS['_remote_callback'] = fn (): array => ['response' => ['code' => 200], 'body' => json_encode(['tag_name' => 'v1.2.0', 'zipball_url' => 'https://api.github.com/repos/ooehme/afd-spritpreise/zipball/v1.2.0', 'body' => 'Release'], JSON_THROW_ON_ERROR)];
    $update = (new GithubUpdater('ooehme', 'afd-spritpreise'))->update(false, ['Tested up to' => '6.9'], 'afd-spritpreise/afd-spritpreise.php', []);
    assert_same('1.2.0', $update['version']);
    assert_true(str_contains($update['package'], 'zipball'));
});

test('Vollständiger Uninstall', function (): void {
    $hash = hash('sha256', 'uninstall');
    $GLOBALS['_options']['afdsp_settings'] = ['test' => true];
    $GLOBALS['_options']['afdsp_cache_index'] = [$hash];
    $GLOBALS['_options']['afdsp_lock_index'] = [$hash];
    $GLOBALS['_options']['afdsp_lock_' . $hash] = time() + 60;
    $GLOBALS['_transients']['afdsp_data_' . $hash] = ['data' => ['x']];
    $GLOBALS['_transients']['afdsp_stale_' . $hash] = ['data' => ['x']];
    $GLOBALS['_site_transients']['afdsp_github_release'] = ['available' => true];
    define('WP_UNINSTALL_PLUGIN', true);
    require AFDSP_DIR . 'uninstall.php';
    assert_same(false, get_option('afdsp_settings', false));
    assert_same(false, get_transient('afdsp_data_' . $hash));
    assert_same(false, get_transient('afdsp_stale_' . $hash));
    assert_same(false, get_site_transient('afdsp_github_release'));
    assert_true(str_contains($GLOBALS['wpdb']->last_query, 'DELETE FROM wp_options'));
});

$passed = 0;
foreach ($tests as $name => $callback) {
    try {
        $callback();
        echo "PASS  {$name}\n";
        $passed++;
    } catch (Throwable $error) {
        fwrite(STDERR, "FAIL  {$name}: {$error->getMessage()}\n");
    }
}
echo "\n{$passed}/" . count($tests) . " Tests bestanden.\n";
exit($passed === count($tests) ? 0 : 1);
