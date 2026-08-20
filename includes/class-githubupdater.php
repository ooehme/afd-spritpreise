<?php

namespace AFDSP;

defined('ABSPATH') || exit;

final class GithubUpdater
{
    private const CACHE_KEY = 'afdsp_github_release';
    private const RELEASE_ASSET = 'afd-spritpreise.zip';
    private const HOMEPAGE = 'https://oliveroehme.de/werkzeuge/afd-spritpreise';
    private const AUTHOR_URL = 'https://oliveroehem.de';

    private string $pluginFile;

    public function __construct(private readonly string $owner, private readonly string $repository)
    {
        $this->pluginFile = plugin_basename(AFDSP_FILE);
    }

    public function register_hooks(): void
    {
        add_filter('update_plugins_github.com', [$this, 'update'], 10, 4);
        add_filter('plugins_api', [$this, 'plugin_information'], 20, 3);
        add_filter('upgrader_source_selection', [$this, 'normalize_source'], 10, 4);
    }

    public function update(mixed $update, array $pluginData, string $pluginFile, array $locales): mixed
    {
        if ($pluginFile !== $this->pluginFile) {
            return $update;
        }

        $release = $this->release();

        // WordPress only exposes the per-plugin automatic-update UI when the plugin
        // occurs in update_plugins->response or update_plugins->no_update. Returning
        // false for an up-to-date external plugin skips both collections and makes
        // Core treat the plugin as not supporting updates. Therefore always return
        // valid metadata for our plugin; wp_update_plugins() itself decides whether
        // it belongs in response or no_update by comparing the versions.
        return [
            'id' => 'https://github.com/' . $this->owner . '/' . $this->repository,
            'slug' => $this->repository,
            'version' => $release['version'] ?? AFDSP_VERSION,
            'url' => self::HOMEPAGE,
            'package' => $release['package'] ?? '',
            'tested' => $pluginData['Tested up to'] ?? '',
            'requires_php' => '8.1',
            'icons' => [],
            'banners' => [],
        ];
    }

    public function plugin_information(mixed $result, string $action, object $args): mixed
    {
        if ('plugin_information' !== $action || ($args->slug ?? '') !== $this->repository) {
            return $result;
        }

        $release = $this->release();
        if (!$release) {
            return $result;
        }

        return (object) [
            'name' => 'AfD Spritpreise',
            'slug' => $this->repository,
            'version' => $release['version'],
            'author' => '<a href="' . esc_url(self::AUTHOR_URL) . '">Oliver Oehme</a>',
            'homepage' => self::HOMEPAGE,
            'download_link' => $release['package'],
            'requires_php' => '8.1',
            'sections' => [
                'description' => esc_html__('Regionale Kraftstoffpreise und konfigurierbare Szenariorechnung.', 'afd-spritpreise'),
                'changelog' => wp_kses_post(nl2br($release['notes'])),
            ],
        ];
    }

    public function normalize_source(string|\WP_Error $source, string $remoteSource, \WP_Upgrader $upgrader, array $hookExtra): string|\WP_Error
    {
        if (is_wp_error($source) || ($hookExtra['plugin'] ?? '') !== $this->pluginFile || basename(untrailingslashit($source)) === $this->repository) {
            return $source;
        }

        global $wp_filesystem;
        $target = trailingslashit($remoteSource) . $this->repository;

        if ($wp_filesystem->exists($target)) {
            $wp_filesystem->delete($target, true);
        }

        if (!$wp_filesystem->move($source, $target, true)) {
            return new \WP_Error(
                'afdsp_update_move',
                __('Das GitHub-Release konnte nicht in das Plugin-Verzeichnis verschoben werden.', 'afd-spritpreise')
            );
        }

        return trailingslashit($target);
    }

    private function release(): ?array
    {
        $cached = get_site_transient(self::CACHE_KEY);
        if (is_array($cached)) {
            return !empty($cached['available']) ? $cached : null;
        }

        $response = wp_remote_get(
            'https://api.github.com/repos/' . rawurlencode($this->owner) . '/' . rawurlencode($this->repository) . '/releases/latest',
            [
                'timeout' => 10,
                'headers' => [
                    'Accept' => 'application/vnd.github+json',
                    'X-GitHub-Api-Version' => '2022-11-28',
                ],
                'user-agent' => 'AfD-Spritpreise/' . AFDSP_VERSION,
            ]
        );

        if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
            set_site_transient(self::CACHE_KEY, ['available' => false], HOUR_IN_SECONDS);
            return null;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        if (!is_array($body) || !empty($body['draft']) || !empty($body['prerelease'])) {
            set_site_transient(self::CACHE_KEY, ['available' => false], HOUR_IN_SECONDS);
            return null;
        }

        $version = ltrim((string) ($body['tag_name'] ?? ''), 'vV');
        if (!preg_match('/^\d+\.\d+\.\d+$/', $version)) {
            set_site_transient(self::CACHE_KEY, ['available' => false], HOUR_IN_SECONDS);
            return null;
        }

        // The workflow publishes afd-spritpreise.zip. The source archive remains a
        // fallback so update checks do not break if an older release lacks the asset.
        $package = (string) ($body['zipball_url'] ?? '');
        foreach ((array) ($body['assets'] ?? []) as $asset) {
            if (($asset['name'] ?? '') === self::RELEASE_ASSET && !empty($asset['browser_download_url'])) {
                $package = (string) $asset['browser_download_url'];
                break;
            }
        }

        if (!$package) {
            set_site_transient(self::CACHE_KEY, ['available' => false], HOUR_IN_SECONDS);
            return null;
        }

        $release = [
            'available' => true,
            'version' => $version,
            'package' => esc_url_raw($package),
            'notes' => sanitize_textarea_field((string) ($body['body'] ?? '')),
        ];

        set_site_transient(self::CACHE_KEY, $release, 6 * HOUR_IN_SECONDS);
        return $release;
    }
}
