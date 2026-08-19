<?php

namespace AFDSP;

defined('ABSPATH') || exit;

final class TankpulsClient
{
    public function __construct(private readonly array $settings)
    {
    }

    public function request_url(BoundingBox $box, string $fuel): string
    {
        if (!in_array($fuel, ['diesel', 'e5', 'e10'], true)) {
            throw new \InvalidArgumentException('Invalid fuel.');
        }
        return add_query_arg([
            'minLat' => $box->minLat,
            'minLng' => $box->minLng,
            'maxLat' => $box->maxLat,
            'maxLng' => $box->maxLng,
            'fuel' => $fuel,
            'limit' => (int) $this->settings['limit'],
        ], (string) $this->settings['base_url']);
    }

    public function search(BoundingBox $box, string $fuel): array
    {
        $response = wp_remote_get($this->request_url($box, $fuel), [
            'timeout' => (int) $this->settings['timeout'],
            'redirection' => 3,
            'headers' => ['Accept' => 'application/json'],
            'user-agent' => 'AfD-Spritpreise/' . AFDSP_VERSION . '; ' . home_url('/'),
        ]);
        if (is_wp_error($response)) {
            throw new \RuntimeException('TankPuls: ' . $response->get_error_message());
        }

        $status = wp_remote_retrieve_response_code($response);
        if ($status < 200 || $status >= 300) {
            throw new \RuntimeException('TankPuls HTTP-Status ' . $status);
        }
        $decoded = json_decode(wp_remote_retrieve_body($response), true, 512, JSON_THROW_ON_ERROR);
        $rows = $this->extract_rows($decoded);
        $stations = [];
        foreach ($rows as $row) {
            $station = $this->normalize_station($row);
            if (null !== $station) {
                $stations[] = $station;
            }
        }
        return $stations;
    }

    private function extract_rows(mixed $decoded): array
    {
        if (!is_array($decoded)) {
            throw new \UnexpectedValueException('TankPuls-Antwort ist kein JSON-Objekt oder -Array.');
        }
        if (array_is_list($decoded)) {
            return $decoded;
        }
        foreach (['items', 'stations', 'data', 'results'] as $key) {
            if (isset($decoded[$key]) && is_array($decoded[$key]) && array_is_list($decoded[$key])) {
                return $decoded[$key];
            }
        }
        throw new \UnexpectedValueException('TankPuls-Antwort enthält keine Stationsliste.');
    }

    private function normalize_station(mixed $row): ?array
    {
        if (!is_array($row) || !isset($row['id'], $row['priceCents']) || !is_numeric($row['priceCents'])) {
            return null;
        }
        $active = filter_var($row['isActive'] ?? true, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $status = strtolower((string) ($row['status'] ?? 'open'));
        if (false === $active || in_array($status, ['closed', 'inactive', 'unavailable'], true)) {
            return null;
        }
        // TankPuls transmits three decimal places as an integer (e.g. 2229 = 2.229 €/l).
        $price = (float) $row['priceCents'] / 1000;
        if ($price <= 0 || $price > 10) {
            return null;
        }
        return [
            'id' => sanitize_text_field((string) $row['id']),
            'name' => sanitize_text_field((string) ($row['name'] ?? $row['brandName'] ?? __('Unbenannte Tankstelle', 'afd-spritpreise'))),
            'brandName' => sanitize_text_field((string) ($row['brandName'] ?? '')),
            'street' => sanitize_text_field((string) ($row['street'] ?? '')),
            'postcode' => sanitize_text_field((string) ($row['postcode'] ?? '')),
            'city' => sanitize_text_field((string) ($row['city'] ?? '')),
            'state' => sanitize_text_field((string) ($row['state'] ?? '')),
            'lat' => isset($row['lat']) && is_numeric($row['lat']) ? (float) $row['lat'] : null,
            'lng' => isset($row['lng']) && is_numeric($row['lng']) ? (float) $row['lng'] : null,
            'price' => $price,
            'priceCents' => (float) $row['priceCents'],
            'priceTs' => sanitize_text_field((string) ($row['priceTs'] ?? '')),
            'status' => $status,
            'isActive' => true,
        ];
    }
}
