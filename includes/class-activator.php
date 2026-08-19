<?php

namespace AFDSP;

defined('ABSPATH') || exit;

final class Activator
{
    public static function activate(): void
    {
        if (version_compare(PHP_VERSION, '8.1', '<')) {
            deactivate_plugins(plugin_basename(AFDSP_FILE));
            wp_die(esc_html__('AfD Spritpreise benötigt PHP 8.1 oder neuer.', 'afd-spritpreise'));
        }

        if (false === get_option(Options::OPTION, false)) {
            add_option(Options::OPTION, Options::defaults(), '', false);
        }
        update_option('afdsp_version', AFDSP_VERSION, false);
    }

    public static function deactivate(): void
    {
        wp_clear_scheduled_hook('afdsp_scheduled_refresh');
        Cache::clear_locks();
    }
}
