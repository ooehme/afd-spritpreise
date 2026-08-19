<?php

namespace AFDSP;

defined('ABSPATH') || exit;

final class Block
{
    private readonly ComponentRenderer $renderer;

    public function __construct(private readonly Plugin $plugin)
    {
        $this->renderer = new ComponentRenderer($plugin);
    }

    public function register_hooks(): void
    {
        add_action('init', [$this, 'register']);
        add_action('enqueue_block_editor_assets', [$this, 'editor_config']);
    }

    public function register(): void
    {
        register_block_type(AFDSP_DIR . 'block', [
            'render_callback' => [$this->renderer, 'parent'],
        ]);
        register_block_type(AFDSP_DIR . 'block/header', [
            'render_callback' => [$this->renderer, 'header'],
        ]);
        register_block_type(AFDSP_DIR . 'block/fuel-tabs', [
            'render_callback' => [$this->renderer, 'tabs'],
        ]);
        register_block_type(AFDSP_DIR . 'block/metric', [
            'render_callback' => [$this->renderer, 'metric'],
        ]);
        register_block_type(AFDSP_DIR . 'block/tank-saving', [
            'render_callback' => [$this->renderer, 'tank_saving'],
        ]);
        register_block_type(AFDSP_DIR . 'block/cheapest-station', [
            'render_callback' => [$this->renderer, 'station'],
        ]);
        register_block_type(AFDSP_DIR . 'block/demands', [
            'render_callback' => [$this->renderer, 'demands'],
        ]);
        register_block_type(AFDSP_DIR . 'block/method', [
            'render_callback' => [$this->renderer, 'method'],
        ]);
    }

    public function editor_config(): void
    {
        $options = Options::get();

        wp_localize_script('afdsp-area-picker', 'afdspAreaPickerConfig', [
            'endpoint' => esc_url_raw(rest_url('afd-spritpreise/v1/photon')),
            'nonce' => wp_create_nonce('wp_rest'),
            'area' => $options['area'],
            'strings' => [
                'search' => __('Ort oder PLZ suchen', 'afd-spritpreise'),
                'noResults' => __('Keine Orte gefunden.', 'afd-spritpreise'),
                'error' => __('Ortssuche derzeit nicht verfügbar.', 'afd-spritpreise'),
            ],
        ]);

        wp_enqueue_style('afdsp-admin');
    }
}
