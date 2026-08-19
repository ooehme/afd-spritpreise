<?php

namespace AFDSP;

defined('ABSPATH') || exit;

final class ComponentRenderer
{
    private const FUEL_LABELS = ['diesel' => 'Diesel', 'e5' => 'Super E5', 'e10' => 'Super E10'];
    private const DATA_FIELDS = [
        'area_label', 'fuel_label', 'station_count', 'intro',
        'current', 'scenario', 'scenario_note', 'saving', 'saving_percent', 'saving_50l',
        'station_label', 'station_name', 'station_address', 'station_price',
        'energy_change', 'energy_effect', 'co2_change', 'co2_effect', 'vat_change', 'vat_effect',
        'method',
    ];
    private const TAG_NAMES = ['div', 'p', 'span', 'strong', 'small'];

    public function __construct(private readonly Plugin $plugin)
    {
    }

    public function parent(array $attributes, string $content, object $block): string
    {
        $config = $this->plugin->normalize_render_config($attributes);
        $data = [];
        $fuels = str_contains($content, 'data-afdsp-tab') ? array_keys(self::FUEL_LABELS) : [$config['defaultFuel']];

        foreach ($fuels as $fuel) {
            try {
                $fuelConfig = $config;
                $fuelConfig['defaultFuel'] = $fuel;
                $data[$fuel] = $this->view_data($this->plugin->service()->get($config['box'], $fuel), $fuelConfig);
            } catch (\Throwable $error) {
                $data[$fuel] = null;
                error_log('AfD Spritpreise Builder (' . $fuel . '): ' . $error->getMessage()); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            }
        }

        wp_enqueue_style('afdsp-frontend');
        wp_enqueue_script('afdsp-frontend');
        $wrapper = get_block_wrapper_attributes([
            'class' => 'afdsp afdsp--builder',
            'data-afdsp-builder' => '',
            'data-afdsp-fuel' => $config['defaultFuel'],
        ]);
        $json = wp_json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

        return '<section ' . $wrapper . '>' . $content . '<script type="application/json" data-afdsp-data>' . $json . '</script></section>';
    }

    public function tabs(array $attributes, string $content, object $block): string
    {
        $fuel = $this->context_fuel($block);
        $buttons = '';
        foreach (self::FUEL_LABELS as $value => $label) {
            $active = $value === $fuel;
            $buttons .= '<button type="button" class="afdsp-tab' . ($active ? ' is-active' : '') . '" aria-pressed="' . ($active ? 'true' : 'false') . '" tabindex="' . ($active ? '0' : '-1') . '" data-afdsp-tab="' . esc_attr($value) . '">' . esc_html($label) . '</button>';
        }

        return '<div ' . get_block_wrapper_attributes(['class' => 'afdsp-tabs afdsp-builder-tabs']) . ' role="group" aria-label="' . esc_attr__('Kraftstoffart', 'afd-spritpreise') . '">' . $buttons . '</div>';
    }

    public function data_value(array $attributes, string $content, object $block): string
    {
        $field = in_array(($attributes['field'] ?? ''), self::DATA_FIELDS, true) ? (string) $attributes['field'] : 'current';
        $tagName = in_array(($attributes['tagName'] ?? ''), self::TAG_NAMES, true) ? (string) $attributes['tagName'] : 'div';
        $config = $this->context_config($block);
        $result = $this->result($config);

        if (!$result) {
            return '<' . $tagName . ' ' . get_block_wrapper_attributes(['class' => 'afdsp-data-value afdsp--error']) . ' role="status">' . esc_html__('Aktuelle Kraftstoffpreise sind derzeit nicht verfügbar.', 'afd-spritpreise') . '</' . $tagName . '>';
        }

        $data = $this->view_data($result, $config);
        $value = array_key_exists($field, $data) ? (string) $data[$field] : '';

        return '<' . $tagName . ' ' . get_block_wrapper_attributes(['class' => 'afdsp-data-value']) . ' data-afdsp-bind="' . esc_attr($field) . '">' . esc_html($value) . '</' . $tagName . '>';
    }

    private function context_config(object $block): array
    {
        $context = (array) ($block->context ?? []);
        $attributes = [];
        foreach (['minLat', 'minLng', 'maxLat', 'maxLng', 'areaLabel', 'defaultFuel'] as $key) {
            if (array_key_exists('afdsp/' . $key, $context)) {
                $attributes[$key] = $context['afdsp/' . $key];
            }
        }
        return $this->plugin->normalize_render_config($attributes);
    }

    private function context_fuel(object $block): string
    {
        $fuel = (string) (($block->context ?? [])['afdsp/defaultFuel'] ?? Options::get()['display']['defaultFuel']);
        return isset(self::FUEL_LABELS[$fuel]) ? $fuel : 'diesel';
    }

    private function result(array $config): ?array
    {
        try {
            return $this->plugin->service()->get($config['box'], $config['defaultFuel']);
        } catch (\Throwable $error) {
            error_log('AfD Spritpreise Datenwert: ' . $error->getMessage()); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            return null;
        }
    }

    private function view_data(array $result, array $config): array
    {
        $c = $result['calculation'];
        $station = $result['cheapest'];
        $fuel = $result['fuel'];

        return [
            'area_label' => $config['areaLabel'],
            'fuel_label' => self::FUEL_LABELS[$fuel],
            'station_count' => (string) $result['station_count'],
            'intro' => sprintf(__('Median aus %1$d Tankstellen im Gebiet %2$s.', 'afd-spritpreise'), $result['station_count'], $config['areaLabel']),
            'current' => $this->price($c['current_gross']),
            'scenario' => $this->price($c['scenario_gross']),
            'scenario_note' => __('wenn die Entlastungen vollständig ankommen', 'afd-spritpreise'),
            'saving' => $this->number($c['saving_cents'], 1) . ' ct/l',
            'saving_percent' => $this->number($c['saving_percent'], 1) . ' % weniger',
            'saving_50l' => $this->money($c['saving_50l']) . ' weniger',
            'station_label' => sprintf(__('Günstigste Tankstelle für %s', 'afd-spritpreise'), self::FUEL_LABELS[$fuel]),
            'station_name' => $station['name'],
            'station_address' => trim($station['street'] . ', ' . $station['postcode'] . ' ' . $station['city'], ' ,'),
            'station_price' => $this->price($station['price']),
            'energy_change' => $this->number($c['energy_current'] * 100, 1) . ' ct → ' . $this->number($c['energy_scenario'] * 100, 1) . ' ct',
            'energy_effect' => $this->number(($c['energy_current'] - $c['energy_scenario']) * 100, 1) . ' ct weniger je Liter',
            'co2_change' => $this->number($c['co2_current'] * 100, 1) . ' ct → ' . $this->number($c['co2_scenario'] * 100, 1) . ' ct',
            'co2_effect' => $this->number(($c['co2_current'] - $c['co2_scenario']) * 100, 1) . ' ct weniger je Liter',
            'vat_change' => $this->number($c['vat_current'], 0) . ' % → ' . $this->number($c['vat_scenario'], 0) . ' %',
            'vat_effect' => __('auf den verbleibenden Nettopreis', 'afd-spritpreise'),
            'method' => sprintf(__('Median aus %1$d gültigen, aktiven Tankstellen im Gebiet %2$s. Der Bruttopreis wird um Mehrwertsteuer, Energiesteuer und CO₂-Kosten bereinigt und anschließend mit den konfigurierten Szenariowerten neu berechnet. Intern wird nicht vorzeitig gerundet.', 'afd-spritpreise'), $result['station_count'], $config['areaLabel']),
        ];
    }

    private function number(float $value, int $decimals): string
    {
        return number_format_i18n($value, $decimals);
    }

    private function price(float $value): string
    {
        return $this->number($value, 3) . ' €/l';
    }

    private function money(float $value): string
    {
        return $this->number($value, 2) . ' €';
    }
}
