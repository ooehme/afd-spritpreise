<?php

namespace AFDSP;

defined('ABSPATH') || exit;

final class FuelPriceService
{
    public function __construct(
        private readonly TankpulsClient $client,
        private readonly Cache $cache,
        private readonly PricingCalculator $calculator,
        private readonly array $apiSettings
    ) {
    }

    public function get(BoundingBox $box, string $fuel, bool $force = false): array
    {
        $key = implode('|', [$box->normalized_key(), $fuel, (string) $this->apiSettings['base_url'], (string) $this->apiSettings['limit']]);
        $cached = $this->cache->remember($key, (int) $this->apiSettings['cache_ttl'], fn (): array => $this->client->search($box, $fuel), $force);
        $stations = $cached['data'];
        if (!$stations) {
            throw new \RuntimeException('TankPuls lieferte keine gültigen aktiven Tankstellen.');
        }
        $median = PricingCalculator::median(array_column($stations, 'price'));
        return [
            'fuel' => $fuel,
            'station_count' => count($stations),
            'median' => $median,
            'cheapest' => PricingCalculator::cheapest($stations),
            'calculation' => $this->calculator->calculate($median, $fuel),
            'cache_status' => $cached['status'],
            'stale' => $cached['stale'],
        ];
    }
}
