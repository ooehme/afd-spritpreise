<?php

declare(strict_types=1);

define('ABSPATH', __DIR__ . '/wordpress/');
define('AFDSP_VERSION', '1.0.0');
define('AFDSP_DIR', dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('AFDSP_URL', 'https://example.test/wp-content/plugins/afd-spritpreise/');
define('AFDSP_FILE', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'afd-spritpreise.php');
define('DAY_IN_SECONDS', 86400);
define('HOUR_IN_SECONDS', 3600);

$GLOBALS['_options'] = ['date_format' => 'd.m.Y', 'time_format' => 'H:i'];
$GLOBALS['_transients'] = [];
$GLOBALS['_site_transients'] = [];
$GLOBALS['_object_cache'] = [];
$GLOBALS['_remote_callback'] = null;
$GLOBALS['_remote_urls'] = [];
$GLOBALS['_enqueued'] = [];

class WP_Error
{
    public function __construct(public string $code = '', public string $message = '', public array $data = []) {}
    public function get_error_message(): string { return $this->message; }
}

class WP_Upgrader {}

class AFDSP_Test_WPDB
{
    public string $options = 'wp_options';
    public string $last_query = '';
    public function esc_like(string $value): string { return $value; }
    public function prepare(string $query, mixed ...$args): string { return vsprintf(str_replace('%s', "'%s'", $query), $args); }
    public function query(string $query): int { $this->last_query = $query; foreach (array_keys($GLOBALS['_options']) as $key) { if (str_starts_with($key, 'afdsp_')) unset($GLOBALS['_options'][$key]); } return 1; }
}
$GLOBALS['wpdb'] = new AFDSP_Test_WPDB();

function __(string $text, string $domain = ''): string { return $text; }
function esc_html__(string $text, string $domain = ''): string { return $text; }
function esc_attr__(string $text, string $domain = ''): string { return $text; }
function esc_html_e(string $text, string $domain = ''): void { echo htmlspecialchars($text, ENT_QUOTES); }
function esc_attr_e(string $text, string $domain = ''): void { echo htmlspecialchars($text, ENT_QUOTES); }
function esc_html(mixed $value): string { return htmlspecialchars((string) $value, ENT_QUOTES); }
function esc_attr(mixed $value): string { return htmlspecialchars((string) $value, ENT_QUOTES); }
function esc_url(mixed $value): string { return filter_var((string) $value, FILTER_SANITIZE_URL) ?: ''; }
function esc_url_raw(mixed $value): string { return esc_url($value); }
function wp_kses_post(string $value): string { return $value; }
function sanitize_text_field(mixed $value): string { return trim(strip_tags((string) $value)); }
function sanitize_textarea_field(mixed $value): string { return trim(strip_tags((string) $value)); }
function absint(mixed $value): int { return abs((int) $value); }
function selected(mixed $a, mixed $b, bool $echo = true): string { return ''; }
function checked(mixed $a, mixed $b = true, bool $echo = true): string { return ''; }
function number_format_i18n(float $value, int $decimals = 0): string { return number_format($value, $decimals, ',', '.'); }
function wp_date(string $format, int $timestamp): string { return date($format, $timestamp); }
function wp_unique_id(string $prefix = ''): string { static $id = 0; return $prefix . ++$id; }
function current_user_can(string $capability): bool { return true; }
function home_url(string $path = ''): string { return 'https://example.test' . $path; }
function plugin_basename(string $file): string { return basename(dirname($file)) . '/' . basename($file); }
function get_option(string $name, mixed $default = false): mixed { return $GLOBALS['_options'][$name] ?? $default; }
function add_option(string $name, mixed $value, string $deprecated = '', bool|string $autoload = true): bool { if (array_key_exists($name, $GLOBALS['_options'])) return false; $GLOBALS['_options'][$name] = $value; return true; }
function update_option(string $name, mixed $value, bool|string|null $autoload = null): bool { $GLOBALS['_options'][$name] = $value; return true; }
function delete_option(string $name): bool { unset($GLOBALS['_options'][$name]); return true; }
function get_transient(string $name): mixed { return $GLOBALS['_transients'][$name] ?? false; }
function set_transient(string $name, mixed $value, int $expiration = 0): bool { $GLOBALS['_transients'][$name] = $value; return true; }
function delete_transient(string $name): bool { unset($GLOBALS['_transients'][$name]); return true; }
function get_site_transient(string $name): mixed { return $GLOBALS['_site_transients'][$name] ?? false; }
function set_site_transient(string $name, mixed $value, int $expiration = 0): bool { $GLOBALS['_site_transients'][$name] = $value; return true; }
function delete_site_transient(string $name): bool { unset($GLOBALS['_site_transients'][$name]); return true; }
function wp_cache_add(string $key, mixed $value, string $group = '', int $expiration = 0): bool { $index = $group . ':' . $key; if (array_key_exists($index, $GLOBALS['_object_cache'])) return false; $GLOBALS['_object_cache'][$index] = $value; return true; }
function wp_cache_delete(string $key, string $group = ''): bool { unset($GLOBALS['_object_cache'][$group . ':' . $key]); return true; }
function wp_remote_get(string $url, array $args = []): mixed { $GLOBALS['_remote_urls'][] = $url; return ($GLOBALS['_remote_callback'])($url, $args); }
function wp_remote_retrieve_response_code(array $response): int { return (int) ($response['response']['code'] ?? 0); }
function wp_remote_retrieve_body(array $response): string { return (string) ($response['body'] ?? ''); }
function is_wp_error(mixed $value): bool { return $value instanceof WP_Error; }
function add_query_arg(array $args, string $url): string { $separator = str_contains($url, '?') ? '&' : '?'; return $url . $separator . http_build_query($args, '', '&', PHP_QUERY_RFC3986); }
function wp_enqueue_style(string $handle): void { $GLOBALS['_enqueued'][] = $handle; }
function wp_enqueue_script(string $handle): void { $GLOBALS['_enqueued'][] = $handle; }
function shortcode_atts(array $pairs, array $atts, string $shortcode = ''): array { return array_merge($pairs, array_intersect_key($atts, $pairs)); }
function deactivate_plugins(string $plugin): void {}
function wp_die(string $message): never { throw new RuntimeException($message); }
function wp_clear_scheduled_hook(string $hook): void { $GLOBALS['_cleared_hook'] = $hook; }
function is_multisite(): bool { return false; }
function untrailingslashit(string $value): string { return rtrim($value, '/\\'); }
function trailingslashit(string $value): string { return rtrim($value, '/\\') . '/'; }

spl_autoload_register(static function (string $class): void {
    if (!str_starts_with($class, 'AFDSP\\')) return;
    $relative = substr($class, 6);
    $file = AFDSP_DIR . 'includes/class-' . strtolower(str_replace('_', '-', $relative)) . '.php';
    if (is_file($file)) require_once $file;
});
