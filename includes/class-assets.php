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
        $this->attach_theme_button_active_state();

        wp_register_style('afdsp-full-site', AFDSP_URL . 'assets/css/full-site.css', ['afdsp-frontend'], AFDSP_VERSION);
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
        wp_register_script(
            'afdsp-block-variations',
            AFDSP_URL . 'assets/js/block-variations.js',
            ['afdsp-block-editor', 'wp-blocks', 'wp-i18n'],
            AFDSP_VERSION,
            true
        );
        wp_register_script(
            'afdsp-block-compact-editor',
            AFDSP_URL . 'assets/js/block-compact.js',
            ['afdsp-block-editor', 'wp-blocks'],
            AFDSP_VERSION,
            true
        );
    }

    private function attach_theme_button_active_state(): void
    {
        if (!function_exists('wp_get_global_styles') || !function_exists('wp_style_engine_get_styles')) {
            return;
        }

        $styles = wp_get_global_styles();
        if (!is_array($styles)) {
            return;
        }

        $coreButton = $styles['blocks']['core/button'] ?? [];
        $active = [];

        foreach ([
            $styles['elements']['button'][':active'] ?? [],
            is_array($coreButton) ? ($coreButton[':active'] ?? []) : [],
            is_array($coreButton) ? ($coreButton['elements']['button'][':active'] ?? []) : [],
            is_array($coreButton) ? ($coreButton['variations']['fill'][':active'] ?? []) : [],
            is_array($coreButton) ? ($coreButton['variations']['fill']['elements']['button'][':active'] ?? []) : [],
        ] as $candidate) {
            if (is_array($candidate) && $candidate) {
                $active = array_replace_recursive($active, $candidate);
            }
        }

        if (!$active) {
            return;
        }

        $compiled = wp_style_engine_get_styles($active, [
            'selector' => '.afdsp--builder .afdsp-tab[aria-pressed="true"]',
            'convert_vars_to_classnames' => false,
        ]);

        if (!empty($compiled['css'])) {
            wp_add_inline_style('afdsp-frontend', $compiled['css']);
        }
    }
}
