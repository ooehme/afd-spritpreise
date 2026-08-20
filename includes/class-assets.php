<?php

namespace AFDSP;

defined('ABSPATH') || exit;

final class Assets
{
    public function register_hooks(): void
    {
        add_action('init', [$this, 'register']);
    }

    public function register(): void
    {
        wp_register_style('afdsp-frontend', AFDSP_URL . 'assets/css/frontend.css', [], AFDSP_VERSION);
        wp_register_style('afdsp-compact-site', AFDSP_URL . 'assets/css/compact-site.css', ['afdsp-frontend'], AFDSP_VERSION);
        wp_register_script('afdsp-frontend', AFDSP_URL . 'assets/js/frontend.js', [], AFDSP_VERSION, true);

        wp_register_style('afdsp-admin', AFDSP_URL . 'assets/css/admin.css', [], AFDSP_VERSION);
        wp_register_script('afdsp-area-picker', AFDSP_URL . 'assets/js/area-picker.js', ['wp-api-fetch'], AFDSP_VERSION, true);
        wp_register_script(
            'afdsp-block-editor',
            AFDSP_URL . 'assets/js/block.js',
            ['wp-blocks', 'wp-block-editor', 'wp-components', 'wp-element', 'wp-i18n', 'wp-server-side-render', 'afdsp-area-picker'],
            AFDSP_VERSION,
            true
        );
    }
}
