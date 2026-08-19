<?php

namespace AFDSP;

defined('ABSPATH') || exit;

final class Options
{
    public const OPTION = 'afdsp_settings';

    public static function defaults(): array
    {
        return [
            'area' => [
                'minLat' => 50.7413804,
                'minLng' => 12.7275333,
                'maxLat' => 50.9039377,
                'maxLng' => 13.0540169,
                'areaLabel' => 'Chemnitzer Stadtgebiet',
            ],
            'tankpuls' => [
                'base_url' => 'https://api.tankpuls.de/api/search/cheapest',
                'timeout' => 60,
                'cache_ttl' => 900,
                'limit' => 200,
            ],
            'photon' => [
                'endpoint' => 'https://photon.komoot.io/api/',
            ],
            'calculation' => [
                'energy_petrol_current' => 0.6545,
                'energy_diesel_current' => 0.4704,
                'energy_petrol_scenario' => 0.3590,
                'energy_diesel_scenario' => 0.3300,
                'vat_current' => 19.0,
                'vat_scenario' => 7.0,
                'co2_petrol_kg' => 2.394,
                'co2_diesel_kg' => 2.676,
                'co2_price_per_tonne' => 65.0,
                'co2_scenario_per_tonne' => 0.0,
            ],
            'display' => [
                'defaultFuel' => 'diesel',
                'displayMode' => 'full',
                'showTitle' => true,
                'showDemands' => true,
                'showMethod' => true,
                'showCheapestStation' => true,
                'showTankSaving' => true,
                'showDetailsLink' => true,
            ],
        ];
    }

    public static function get(): array
    {
        return self::merge(self::defaults(), (array) get_option(self::OPTION, []));
    }

    public static function reset(): void
    {
        update_option(self::OPTION, self::defaults(), false);
    }

    public static function sanitize(mixed $input): array
    {
        $input = is_array($input) ? $input : [];
        $defaults = self::defaults();

        try {
            $box = BoundingBox::from_array((array) ($input['area'] ?? []));
            $area = $box->to_array();
        } catch (\InvalidArgumentException) {
            add_settings_error(self::OPTION, 'invalid_bbox', __('Die Bounding Box ist ungültig; das bisherige Gebiet wurde beibehalten.', 'afd-spritpreise'));
            $area = self::get()['area'];
        }
        $area['areaLabel'] = sanitize_text_field((string) ($input['area']['areaLabel'] ?? $area['areaLabel'] ?? ''));

        $url = esc_url_raw((string) ($input['tankpuls']['base_url'] ?? $defaults['tankpuls']['base_url']));
        $photon = esc_url_raw((string) ($input['photon']['endpoint'] ?? $defaults['photon']['endpoint']));

        return [
            'area' => $area,
            'tankpuls' => [
                'base_url' => $url ?: $defaults['tankpuls']['base_url'],
                'timeout' => self::clamp_int($input['tankpuls']['timeout'] ?? 60, 3, 120),
                'cache_ttl' => self::clamp_int($input['tankpuls']['cache_ttl'] ?? 900, 60, DAY_IN_SECONDS),
                'limit' => self::clamp_int($input['tankpuls']['limit'] ?? 200, 1, 500),
            ],
            'photon' => ['endpoint' => $photon ?: $defaults['photon']['endpoint']],
            'calculation' => self::sanitize_calculation((array) ($input['calculation'] ?? []), $defaults['calculation']),
            'display' => self::sanitize_display((array) ($input['display'] ?? []), $defaults['display']),
        ];
    }

    private static function sanitize_calculation(array $values, array $defaults): array
    {
        $out = [];
        foreach ($defaults as $key => $default) {
            $value = filter_var($values[$key] ?? $default, FILTER_VALIDATE_FLOAT);
            $out[$key] = false === $value ? $default : max(0.0, min(1000.0, (float) $value));
        }
        $out['vat_current'] = min(100.0, $out['vat_current']);
        $out['vat_scenario'] = min(100.0, $out['vat_scenario']);
        return $out;
    }

    private static function sanitize_display(array $values, array $defaults): array
    {
        $out = [
            'defaultFuel' => in_array(($values['defaultFuel'] ?? ''), ['diesel', 'e5', 'e10'], true) ? $values['defaultFuel'] : $defaults['defaultFuel'],
            'displayMode' => in_array(($values['displayMode'] ?? ''), ['full', 'compact'], true) ? $values['displayMode'] : $defaults['displayMode'],
        ];
        foreach (['showTitle', 'showDemands', 'showMethod', 'showCheapestStation', 'showTankSaving', 'showDetailsLink'] as $key) {
            $out[$key] = !empty($values[$key]);
        }
        return $out;
    }

    private static function clamp_int(mixed $value, int $min, int $max): int
    {
        return max($min, min($max, absint($value)));
    }

    private static function merge(array $defaults, array $values): array
    {
        foreach ($defaults as $key => $default) {
            if (is_array($default)) {
                $values[$key] = self::merge($default, is_array($values[$key] ?? null) ? $values[$key] : []);
            } elseif (!array_key_exists($key, $values)) {
                $values[$key] = $default;
            }
        }
        return $values;
    }
}
