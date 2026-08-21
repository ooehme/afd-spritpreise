<?php
/**
 * Plugin Name:       AfD Spritpreise
 * Plugin URI:        https://oliveroehme.de/werkzeuge/afd-spritpreise
 * Description:       Zeigt regionale Median-Kraftstoffpreise und ein konfigurierbares Steuerszenario.
 * Version:           1.2.4
 * Requires at least: 6.6
 * Tested up to:      7.0
 * Requires PHP:      8.1
 * Author:            Oliver Oehme
 * Author URI:        https://oliveroehem.de
 * Text Domain:       afd-spritpreise
 * Update URI:        https://github.com/ooehme/afd-spritpreise
 * License:           GPL-2.0-or-later
 */

defined('ABSPATH') || exit;

define('AFDSP_VERSION', '1.2.4');
define('AFDSP_FILE', __FILE__);
define('AFDSP_DIR', plugin_dir_path(__FILE__));
define('AFDSP_URL', plugin_dir_url(__FILE__));

spl_autoload_register(static function (string $class): void {
    $prefix = 'AFDSP\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $file = AFDSP_DIR . 'includes/class-' . strtolower(str_replace('_', '-', $relative)) . '.php';
    if (is_readable($file)) {
        require_once $file;
    }
});

register_activation_hook(__FILE__, ['AFDSP\\Activator', 'activate']);
register_deactivation_hook(__FILE__, ['AFDSP\\Activator', 'deactivate']);

add_action('plugins_loaded', static function (): void {
    if (version_compare(PHP_VERSION, '8.1', '<')) {
        return;
    }
    AFDSP\Plugin::instance()->boot();
});
