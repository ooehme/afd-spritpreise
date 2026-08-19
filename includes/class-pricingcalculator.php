<?php

namespace AFDSP;

defined('ABSPATH') || exit;

final class PricingCalculator
{
    public function __construct(private readonly array $settings)
    {
    }

    public static function median(array $prices): float
    {
        $prices = array_values(array_map('floatval', $prices));
        if (!$prices) {
            throw new \InvalidArgumentException('At least one price is required.');
        }
        sort($prices, SORT_NUMERIC);
        $count = count($prices);
        $middle = intdiv($count, 2);
        return 1 === $count % 2 ? $prices[$middle] : ($prices[$middle - 1] + $prices[$middle]) / 2;
    }

    public static function cheapest(array $stations): array
    {
        if (!$stations) {
            throw new \InvalidArgumentException('At least one station is required.');
        }
        usort($stations, static function (array $a, array $b): int {
            $price = ((float) $a['price']) <=> ((float) $b['price']);
            return 0 !== $price ? $price : strcmp((string) $a['id'], (string) $b['id']);
        });
        return $stations[0];
    }

    public function calculate(float $grossPrice, string $fuel): array
    {
        if ($grossPrice <= 0 || !in_array($fuel, ['diesel', 'e5', 'e10'], true)) {
            throw new \InvalidArgumentException('Invalid price or fuel.');
        }

        $diesel = 'diesel' === $fuel;
        $energyCurrent = (float) $this->settings[$diesel ? 'energy_diesel_current' : 'energy_petrol_current'];
        $energyScenario = (float) $this->settings[$diesel ? 'energy_diesel_scenario' : 'energy_petrol_scenario'];
        $co2Kg = (float) $this->settings[$diesel ? 'co2_diesel_kg' : 'co2_petrol_kg'];
        $vatCurrent = (float) $this->settings['vat_current'] / 100;
        $vatScenario = (float) $this->settings['vat_scenario'] / 100;
        $co2Current = $co2Kg * (float) $this->settings['co2_price_per_tonne'] / 1000;
        $co2Scenario = $co2Kg * (float) $this->settings['co2_scenario_per_tonne'] / 1000;

        $netCurrent = $grossPrice / (1 + $vatCurrent);
        $netScenario = $netCurrent - $energyCurrent - $co2Current + $energyScenario + $co2Scenario;
        $grossScenario = $netScenario * (1 + $vatScenario);
        $saving = $grossPrice - $grossScenario;

        return [
            'current_gross' => $grossPrice,
            'current_net' => $netCurrent,
            'scenario_net' => $netScenario,
            'scenario_gross' => $grossScenario,
            'saving_euro' => $saving,
            'saving_cents' => $saving * 100,
            'saving_percent' => $grossPrice > 0 ? ($saving / $grossPrice) * 100 : 0.0,
            'saving_50l' => $saving * 50,
            'energy_current' => $energyCurrent,
            'energy_scenario' => $energyScenario,
            'co2_current' => $co2Current,
            'co2_scenario' => $co2Scenario,
            'vat_current' => $vatCurrent * 100,
            'vat_scenario' => $vatScenario * 100,
        ];
    }
}
