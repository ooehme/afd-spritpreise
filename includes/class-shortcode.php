<?php

namespace AFDSP;

defined('ABSPATH') || exit;

final class Shortcode
{
    public function __construct(private readonly Plugin $plugin)
    {
    }

    public function register_hooks(): void
    {
        add_shortcode('afd_spritpreise', [$this, 'render']);
    }

    public function render(array|string $attributes = []): string
    {
        $attributes = shortcode_atts([
            'fuel' => null,
            'display' => null,
            'min_lat' => null,
            'min_lng' => null,
            'max_lat' => null,
            'max_lng' => null,
            'area' => null,
            'show_title' => null,
            'show_demands' => null,
            'show_method' => null,
            'show_cheapest_station' => null,
            'show_tank_saving' => null,
            'show_details_link' => null,
        ], is_array($attributes) ? $attributes : [], 'afd_spritpreise');

        $mapped = [];
        $map = [
            'fuel' => 'defaultFuel', 'display' => 'displayMode', 'min_lat' => 'minLat', 'min_lng' => 'minLng',
            'max_lat' => 'maxLat', 'max_lng' => 'maxLng', 'area' => 'areaLabel',
        ];
        foreach ($map as $from => $to) {
            if (null !== $attributes[$from] && '' !== $attributes[$from]) {
                $mapped[$to] = in_array($from, ['min_lat', 'min_lng', 'max_lat', 'max_lng'], true) ? (float) $attributes[$from] : sanitize_text_field((string) $attributes[$from]);
            }
        }
        foreach (['show_title', 'show_demands', 'show_method', 'show_cheapest_station', 'show_tank_saving', 'show_details_link'] as $name) {
            if (null !== $attributes[$name] && '' !== $attributes[$name]) {
                $mapped[str_replace(' ', '', ucwords(str_replace('_', ' ', $name)))] = filter_var($attributes[$name], FILTER_VALIDATE_BOOLEAN);
            }
        }
        foreach (array_keys($mapped) as $key) {
            if (str_starts_with($key, 'Show')) {
                $mapped[lcfirst($key)] = $mapped[$key];
                unset($mapped[$key]);
            }
        }
        return $this->plugin->render($mapped);
    }
}
