<?php

namespace AFDSP;

defined('ABSPATH') || exit;

final class PhotonController
{
    public function register_hooks(): void
    {
        add_action('rest_api_init', [$this, 'register_route']);
    }

    public function register_route(): void
    {
        register_rest_route('afd-spritpreise/v1', '/photon', [
            'methods' => 'GET',
            'permission_callback' => static fn (): bool => current_user_can('edit_posts') || current_user_can('manage_options'),
            'callback' => [$this, 'search'],
            'args' => ['q' => ['required' => true, 'sanitize_callback' => 'sanitize_text_field']],
        ]);
    }

    public function search(\WP_REST_Request $request): \WP_REST_Response|\WP_Error
    {
        $query = trim((string) $request->get_param('q'));
        $length = function_exists('mb_strlen') ? mb_strlen($query) : strlen($query);
        if ($length < 3) {
            return new \WP_Error('afdsp_short_query', __('Bitte mindestens drei Zeichen eingeben.', 'afd-spritpreise'), ['status' => 400]);
        }
        $endpoint = Options::get()['photon']['endpoint'];
        $url = add_query_arg(['q' => $query, 'lang' => 'de', 'limit' => 5, 'countrycode' => 'DE'], $endpoint);
        $response = wp_remote_get($url, ['timeout' => 10, 'redirection' => 2, 'headers' => ['Accept' => 'application/geo+json, application/json']]);
        if (is_wp_error($response)) {
            return new \WP_Error('afdsp_photon_error', __('Ortssuche derzeit nicht verfügbar.', 'afd-spritpreise'), ['status' => 502]);
        }
        $status = wp_remote_retrieve_response_code($response);
        $json = json_decode(wp_remote_retrieve_body($response), true);
        if ($status < 200 || $status >= 300 || !is_array($json['features'] ?? null)) {
            return new \WP_Error('afdsp_photon_response', __('Photon hat eine ungültige Antwort geliefert.', 'afd-spritpreise'), ['status' => 502]);
        }

        $results = [];
        foreach (array_slice($json['features'], 0, 5) as $feature) {
            $coordinates = $feature['geometry']['coordinates'] ?? null;
            $properties = is_array($feature['properties'] ?? null) ? $feature['properties'] : [];
            if (!is_array($coordinates) || count($coordinates) < 2 || !is_numeric($coordinates[0]) || !is_numeric($coordinates[1])) {
                continue;
            }
            try {
                $box = isset($properties['extent'])
                    ? BoundingBox::from_photon_extent((array) $properties['extent'])
                    : BoundingBox::around_point((float) $coordinates[0], (float) $coordinates[1]);
            } catch (\InvalidArgumentException) {
                continue;
            }
            $parts = array_filter([
                sanitize_text_field((string) ($properties['name'] ?? $properties['city'] ?? '')),
                sanitize_text_field((string) ($properties['postcode'] ?? '')),
                sanitize_text_field((string) ($properties['state'] ?? '')),
            ]);
            $results[] = [
                'label' => implode(', ', array_unique($parts)),
                'lng' => (float) $coordinates[0],
                'lat' => (float) $coordinates[1],
                'bbox' => $box->to_array(),
            ];
        }
        return rest_ensure_response($results);
    }
}
