<?php

namespace AFDSP;

defined('ABSPATH') || exit;

final class Block
{
    public function __construct(private readonly Plugin $plugin)
    {
    }

    public function register_hooks(): void
    {
        add_action('init', [$this, 'register']);
        add_action('enqueue_block_editor_assets', [$this, 'editor_config']);
    }

    public function register(): void
    {
        register_block_type(AFDSP_DIR . 'block', ['render_callback' => [$this, 'render']]);
    }

    public function render(array $attributes): string
    {
        return sprintf(
            '<div %s>%s</div>',
            get_block_wrapper_attributes(),
            $this->plugin->render($attributes)
        );
    }

    public function editor_config(): void
    {
        $options = Options::get();
        wp_localize_script('afdsp-area-picker', 'afdspAreaPickerConfig', [
            'endpoint' => esc_url_raw(rest_url('afd-spritpreise/v1/photon')),
            'nonce' => wp_create_nonce('wp_rest'),
            'area' => $options['area'],
            'display' => $options['display'],
            'strings' => [
                'search' => __('Ort oder PLZ suchen', 'afd-spritpreise'),
                'noResults' => __('Keine Orte gefunden.', 'afd-spritpreise'),
                'error' => __('Ortssuche derzeit nicht verfügbar.', 'afd-spritpreise'),
            ],
        ]);
        wp_enqueue_style('afdsp-admin');
    }
}
