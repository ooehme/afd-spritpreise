<?php

namespace AFDSP;

defined('ABSPATH') || exit;

final class ComponentRenderer
{
    private const FUEL_LABELS = ['diesel' => 'Diesel', 'e5' => 'Super E5', 'e10' => 'Super E10'];

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

    public function header(array $attributes, string $content, object $block): string
    {
        $config = $this->context_config($block);
        $result = $this->result($config);
        $intro = $result
            ? sprintf(__('Median aus %1$d Tankstellen im Gebiet %2$s.', 'afd-spritpreise'), $result['station_count'], $config['areaLabel'])
            : $config['areaLabel'];

        return '<header ' . $this->wrapper('afdsp-component afdsp-builder-header') . '><p class="afdsp-eyebrow">' . esc_html((string) ($attributes['eyebrow'] ?? __('Regionale Kraftstoffpreise', 'afd-spritpreise'))) . '</p><h2 class="afdsp-title">' . esc_html((string) ($attributes['title'] ?? __('Das kostet Kraftstoff mit der AfD', 'afd-spritpreise'))) . '</h2><p class="afdsp-intro" data-afdsp-bind="intro">' . esc_html($intro) . '</p></header>';
    }

    public function tabs(array $attributes, string $content, object $block): string
    {
        $fuel = $this->context_fuel($block);
        $buttons = '';
        foreach (self::FUEL_LABELS as $value => $label) {
            $active = $value === $fuel;
            $buttons .= '<button type="button" class="afdsp-tab' . ($active ? ' is-active' : '') . '" aria-pressed="' . ($active ? 'true' : 'false') . '" tabindex="' . ($active ? '0' : '-1') . '" data-afdsp-tab="' . esc_attr($value) . '">' . esc_html($label) . '</button>';
        }
        return '<div ' . $this->wrapper('afdsp-component afdsp-tabs afdsp-builder-tabs') . ' role="group" aria-label="' . esc_attr__('Kraftstoffart', 'afd-spritpreise') . '">' . $buttons . '</div>';
    }

    public function metric(array $attributes, string $content, object $block): string
    {
        $metric = in_array(($attributes['metric'] ?? ''), ['current', 'scenario', 'saving'], true) ? (string) $attributes['metric'] : 'current';
        $labels = [
            'current' => __('Aktueller Kraftstoffpreis', 'afd-spritpreise'),
            'scenario' => __('Nach AfD-Forderungen', 'afd-spritpreise'),
            'saving' => __('Mögliche Ersparnis', 'afd-spritpreise'),
        ];
        $config = $this->context_config($block);
        $result = $this->result($config);
        if (!$result) {
            return $this->unavailable('afdsp-metric afdsp-metric--' . $metric);
        }
        $data = $this->view_data($result, $config);
        $valueKey = ['current' => 'current', 'scenario' => 'scenario', 'saving' => 'saving'][$metric];
        $subKey = ['current' => '', 'scenario' => 'scenario_note', 'saving' => 'saving_percent'][$metric];
        $subtitle = $subKey ? '<small data-afdsp-bind="' . esc_attr($subKey) . '">' . esc_html($data[$subKey]) . '</small>' : '';

        return '<div ' . $this->wrapper('afdsp-component afdsp-metric afdsp-metric--' . $metric) . ' data-afdsp-metric="' . esc_attr($metric) . '"><span class="afdsp-metric-label">' . esc_html((string) ($attributes['label'] ?? $labels[$metric])) . '</span><strong data-afdsp-bind="' . esc_attr($valueKey) . '">' . esc_html($data[$valueKey]) . '</strong>' . $subtitle . '</div>';
    }

    public function tank_saving(array $attributes, string $content, object $block): string
    {
        $config = $this->context_config($block);
        $result = $this->result($config);
        if (!$result) {
            return $this->unavailable('afdsp-tank-saving');
        }
        $data = $this->view_data($result, $config);
        return '<div ' . $this->wrapper('afdsp-component afdsp-tank-saving') . '><span>' . esc_html__('Bei 50 Litern', 'afd-spritpreise') . '</span><strong data-afdsp-bind="saving_50l">' . esc_html($data['saving_50l']) . '</strong></div>';
    }

    public function station(array $attributes, string $content, object $block): string
    {
        $config = $this->context_config($block);
        $result = $this->result($config);
        if (!$result) {
            return $this->unavailable('afdsp-builder-station');
        }
        $data = $this->view_data($result, $config);
        return '<div ' . $this->wrapper('afdsp-component afdsp-builder-station') . '><span data-afdsp-bind="station_label">' . esc_html($data['station_label']) . '</span><div class="afdsp-station-row"><div><strong data-afdsp-bind="station_name">' . esc_html($data['station_name']) . '</strong><small data-afdsp-bind="station_address">' . esc_html($data['station_address']) . '</small></div><div><small>' . esc_html__('Preis', 'afd-spritpreise') . '</small><b data-afdsp-bind="station_price">' . esc_html($data['station_price']) . '</b></div></div></div>';
    }

    public function demands(array $attributes, string $content, object $block): string
    {
        $config = $this->context_config($block);
        $result = $this->result($config);
        if (!$result) {
            return $this->unavailable('afdsp-builder-demands');
        }
        $data = $this->view_data($result, $config);
        $rows = [
            ['Energiesteuer auf das EU-Mindestmaß senken', 'Die Energiesteuer wird auf die europäischen Mindeststeuersätze abgesenkt.', 'energy_change', 'energy_effect', 'BT-Drs. 21/6332', 'https://dserver.bundestag.de/btd/21/063/2106332.pdf'],
            ['CO₂-Bepreisung abschaffen', 'Die nationale CO₂-Bepreisung wird im Szenario durch den konfigurierten Zielwert ersetzt.', 'co2_change', 'co2_effect', 'BT-Drs. 21/6334', 'https://dserver.bundestag.de/btd/21/063/2106334.pdf'],
            ['Mehrwertsteuer auf Kraftstoffe auf 7 % senken', 'Für Benzin und Diesel wird der konfigurierte ermäßigte Umsatzsteuersatz verwendet.', 'vat_change', 'vat_effect', 'BT-Drs. 21/5326', 'https://dserver.bundestag.de/btd/21/053/2105326.pdf'],
        ];
        $html = '<section ' . $this->wrapper('afdsp-component afdsp-builder-demands') . '><div class="afdsp-demands-heading"><h3>' . esc_html__('Diese drei AfD-Forderungen sind eingerechnet', 'afd-spritpreise') . '</h3><p>' . esc_html__('Sie bilden die Grundlage für die oben berechneten Kraftstoffpreise.', 'afd-spritpreise') . '</p></div><ol class="afdsp-demand-list">';
        foreach ($rows as $row) {
            $html .= '<li><div class="afdsp-demand-copy"><h4>' . esc_html($row[0]) . '</h4><p>' . esc_html($row[1]) . '</p><a href="' . esc_url($row[5]) . '" target="_blank" rel="external noopener">' . esc_html__('Quelle:', 'afd-spritpreise') . ' ' . esc_html($row[4]) . '</a></div><div class="afdsp-demand-calc"><span>' . esc_html__('In der Rechnung', 'afd-spritpreise') . '</span><strong data-afdsp-bind="' . esc_attr($row[2]) . '">' . esc_html($data[$row[2]]) . '</strong><small data-afdsp-bind="' . esc_attr($row[3]) . '">' . esc_html($data[$row[3]]) . '</small></div></li>';
        }
        $html .= '</ol><div class="afdsp-demand-note"><strong>' . esc_html__('Weitere Abgaben nicht eingerechnet', 'afd-spritpreise') . '</strong><p>' . esc_html__('Erdölbevorratungsbeitrag und THG-Quote werden ohne konkrete konfigurierte Zielgröße nicht zusätzlich abgezogen.', 'afd-spritpreise') . '</p></div></section>';
        return $html;
    }

    public function method(array $attributes, string $content, object $block): string
    {
        $config = $this->context_config($block);
        $result = $this->result($config);
        if (!$result) {
            return $this->unavailable('afdsp-builder-method');
        }
        $text = sprintf(__('Median aus %1$d gültigen, aktiven Tankstellen im Gebiet %2$s. Der Bruttopreis wird um Mehrwertsteuer, Energiesteuer und CO₂-Kosten bereinigt und anschließend mit den konfigurierten Szenariowerten neu berechnet. Intern wird nicht vorzeitig gerundet.', 'afd-spritpreise'), $result['station_count'], $config['areaLabel']);
        return '<section ' . $this->wrapper('afdsp-component afdsp-builder-method') . '><h3>' . esc_html__('Zur Berechnung', 'afd-spritpreise') . '</h3><p data-afdsp-bind="method">' . esc_html($text) . '</p><p class="afdsp-source">' . wp_kses_post(sprintf(__('Preisdaten: %s · MTS-K', 'afd-spritpreise'), '<a href="https://tankpuls.de/" rel="external nofollow">TankPuls</a>')) . '</p></section>';
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
            error_log('AfD Spritpreise Komponente: ' . $error->getMessage()); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            return null;
        }
    }

    private function view_data(array $result, array $config): array
    {
        $c = $result['calculation'];
        $station = $result['cheapest'];
        $fuel = $result['fuel'];
        return [
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
            'method' => sprintf(__('Median aus %1$d gültigen, aktiven Tankstellen im Gebiet %2$s. Intern wird ohne vorzeitige Rundung gerechnet.', 'afd-spritpreise'), $result['station_count'], $config['areaLabel']),
        ];
    }

    private function wrapper(string $class): string
    {
        return get_block_wrapper_attributes(['class' => $class]);
    }

    private function unavailable(string $class): string
    {
        return '<div ' . $this->wrapper('afdsp-component ' . $class . ' afdsp--error') . ' role="status">' . esc_html__('Aktuelle Kraftstoffpreise sind derzeit nicht verfügbar.', 'afd-spritpreise') . '</div>';
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
