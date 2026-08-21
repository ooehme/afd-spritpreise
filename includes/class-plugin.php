<?php

namespace AFDSP;

defined('ABSPATH') || exit;

final class Plugin
{
    private static ?self $instance = null;
    private ?FuelPriceService $service = null;

    public static function instance(): self
    {
        return self::$instance ??= new self();
    }

    public function boot(): void
    {
        load_plugin_textdomain('afd-spritpreise', false, dirname(plugin_basename(AFDSP_FILE)) . '/languages');

        (new Assets())->register_hooks();
        (new Block($this))->register_hooks();
        (new Shortcode($this))->register_hooks();
        (new Admin($this))->register_hooks();
        (new PhotonController())->register_hooks();
        (new GithubUpdater('ooehme', 'afd-spritpreise'))->register_hooks();
    }

    public function service(): FuelPriceService
    {
        if (null === $this->service) {
            $options = Options::get();
            $this->service = new FuelPriceService(
                new TankpulsClient($options['tankpuls']),
                new Cache(),
                new PricingCalculator($options['calculation']),
                $options['tankpuls']
            );
        }
        return $this->service;
    }

    public function render(array $attributes): string
    {
        try {
            $config = $this->normalize_render_config($attributes);
            wp_enqueue_style('afdsp-frontend');
            wp_enqueue_script('afdsp-frontend');

            if ('compact' === $config['displayMode']) {
                wp_enqueue_style('afdsp-compact-site');
                return (new CompactRenderer($this->service()))->render($config);
            }

            wp_enqueue_style('afdsp-full-site');
            return (new Renderer($this->service()))->render($config);
        } catch (\Throwable $error) {
            if (current_user_can('manage_options')) {
                error_log('AfD Spritpreise: ' . $error->getMessage()); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            }
            return '<div class="afdsp afdsp--error" role="status">' . esc_html__('Aktuelle Kraftstoffpreise sind derzeit nicht verfügbar.', 'afd-spritpreise') . '</div>';
        }
    }

    public function normalize_render_config(array $attributes): array
    {
        $options = Options::get();
        $display = array_merge($options['display'], array_intersect_key($attributes, $options['display']));
        $areaSource = $attributes;
        foreach (['minLat', 'minLng', 'maxLat', 'maxLng'] as $key) {
            if (!isset($attributes[$key]) || !is_numeric($attributes[$key])) {
                $areaSource = $options['area'];
                break;
            }
        }
        $box = BoundingBox::from_array($areaSource);
        $fuel = in_array(($attributes['defaultFuel'] ?? $display['defaultFuel']), ['diesel', 'e5', 'e10'], true)
            ? (string) ($attributes['defaultFuel'] ?? $display['defaultFuel'])
            : 'diesel';
        $mode = in_array(($attributes['displayMode'] ?? $display['displayMode']), ['full', 'compact'], true)
            ? (string) ($attributes['displayMode'] ?? $display['displayMode'])
            : 'full';

        $config = [
            'box' => $box,
            'areaLabel' => sanitize_text_field((string) (trim((string) ($attributes['areaLabel'] ?? '')) ?: $options['area']['areaLabel'])),
            'defaultFuel' => $fuel,
            'displayMode' => $mode,
        ];
        foreach (['showTitle', 'showDemands', 'showMethod', 'showCheapestStation', 'showTankSaving', 'showDetailsLink'] as $key) {
            $config[$key] = array_key_exists($key, $attributes) ? (bool) $attributes[$key] : (bool) $display[$key];
        }
        return $config;
    }
}
