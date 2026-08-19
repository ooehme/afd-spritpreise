<?php

namespace AFDSP;

defined('ABSPATH') || exit;

final class BoundingBox
{
    public function __construct(
        public readonly float $minLat,
        public readonly float $minLng,
        public readonly float $maxLat,
        public readonly float $maxLng
    ) {
        if ($minLat < -90 || $maxLat > 90 || $minLng < -180 || $maxLng > 180 || $minLat >= $maxLat || $minLng >= $maxLng) {
            throw new \InvalidArgumentException('Invalid bounding box.');
        }
    }

    public static function from_array(array $value): self
    {
        foreach (['minLat', 'minLng', 'maxLat', 'maxLng'] as $key) {
            if (!isset($value[$key]) || !is_numeric($value[$key])) {
                throw new \InvalidArgumentException('Incomplete bounding box.');
            }
        }
        return new self((float) $value['minLat'], (float) $value['minLng'], (float) $value['maxLat'], (float) $value['maxLng']);
    }

    /** Photon extent is [west/minLng, north/maxLat, east/maxLng, south/minLat]. */
    public static function from_photon_extent(array $extent): self
    {
        if (count($extent) !== 4 || count(array_filter($extent, 'is_numeric')) !== 4) {
            throw new \InvalidArgumentException('Invalid Photon extent.');
        }
        return new self((float) $extent[3], (float) $extent[0], (float) $extent[1], (float) $extent[2]);
    }

    public static function around_point(float $lng, float $lat, float $padding = 0.025): self
    {
        return new self(max(-90, $lat - $padding), max(-180, $lng - $padding), min(90, $lat + $padding), min(180, $lng + $padding));
    }

    public function to_array(): array
    {
        return ['minLat' => $this->minLat, 'minLng' => $this->minLng, 'maxLat' => $this->maxLat, 'maxLng' => $this->maxLng];
    }

    public function normalized_key(): string
    {
        return implode(':', array_map(static fn (float $v): string => number_format($v, 6, '.', ''), [$this->minLat, $this->minLng, $this->maxLat, $this->maxLng]));
    }
}
