<?php

namespace AFDSP;

defined('ABSPATH') || exit;

final class Cache
{
    private const INDEX_OPTION = 'afdsp_cache_index';
    private const LOCK_INDEX_OPTION = 'afdsp_lock_index';
    private const STALE_TTL = 604800;

    public function remember(string $rawKey, int $ttl, callable $loader, bool $force = false): array
    {
        $key = hash('sha256', $rawKey);
        $now = time();
        $freshKey = 'afdsp_data_' . $key;
        $staleKey = 'afdsp_stale_' . $key;
        $cached = get_transient($freshKey);
        if (!$force && is_array($cached) && ($cached['expires_at'] ?? 0) > $now && isset($cached['data'])) {
            return ['data' => $cached['data'], 'status' => 'hit', 'stale' => false];
        }

        $stale = get_transient($staleKey);
        if (!$this->acquire_lock($key)) {
            if (is_array($stale) && isset($stale['data'])) {
                return ['data' => $stale['data'], 'status' => 'locked-stale', 'stale' => true];
            }
            throw new \RuntimeException('Für dieses Gebiet läuft bereits eine Aktualisierung.');
        }

        try {
            $data = $loader();
            $payload = ['data' => $data, 'stored_at' => $now, 'expires_at' => $now + $ttl];
            set_transient($freshKey, $payload, $ttl + 60);
            set_transient($staleKey, $payload, self::STALE_TTL);
            $this->track($key);
            update_option('afdsp_last_success', gmdate('c'), false);
            delete_option('afdsp_last_error');
            return ['data' => $data, 'status' => $force ? 'refresh' : 'miss', 'stale' => false];
        } catch (\Throwable $error) {
            update_option('afdsp_last_error', ['time' => gmdate('c'), 'message' => sanitize_text_field($error->getMessage())], false);
            if (is_array($stale) && isset($stale['data'])) {
                return ['data' => $stale['data'], 'status' => 'error-stale', 'stale' => true];
            }
            throw $error;
        } finally {
            $this->release_lock($key);
        }
    }

    public static function clear_all(): void
    {
        foreach ((array) get_option(self::INDEX_OPTION, []) as $key) {
            delete_transient('afdsp_data_' . $key);
            delete_transient('afdsp_stale_' . $key);
        }
        delete_option(self::INDEX_OPTION);
        self::clear_locks();
    }

    public static function clear_locks(): void
    {
        foreach ((array) get_option(self::LOCK_INDEX_OPTION, []) as $key) {
            delete_option('afdsp_lock_' . $key);
            wp_cache_delete($key, 'afdsp_locks');
        }
        delete_option(self::LOCK_INDEX_OPTION);
    }

    public static function status(): array
    {
        return [
            'entries' => count((array) get_option(self::INDEX_OPTION, [])),
            'last_success' => get_option('afdsp_last_success', ''),
            'last_error' => get_option('afdsp_last_error', []),
        ];
    }

    private function acquire_lock(string $key): bool
    {
        $option = 'afdsp_lock_' . $key;
        $now = time();
        $existing = (int) get_option($option, 0);
        if ($existing && $existing < $now) {
            delete_option($option);
            wp_cache_delete($key, 'afdsp_locks');
        }
        if (!wp_cache_add($key, $now + 90, 'afdsp_locks', 90)) {
            return false;
        }
        if (!add_option($option, $now + 90, '', false)) {
            wp_cache_delete($key, 'afdsp_locks');
            return false;
        }
        $locks = (array) get_option(self::LOCK_INDEX_OPTION, []);
        if (!in_array($key, $locks, true)) {
            $locks[] = $key;
            update_option(self::LOCK_INDEX_OPTION, array_slice($locks, -500), false);
        }
        return true;
    }

    private function release_lock(string $key): void
    {
        delete_option('afdsp_lock_' . $key);
        wp_cache_delete($key, 'afdsp_locks');
    }

    private function track(string $key): void
    {
        $keys = (array) get_option(self::INDEX_OPTION, []);
        if (!in_array($key, $keys, true)) {
            $keys[] = $key;
            update_option(self::INDEX_OPTION, array_slice($keys, -500), false);
        }
    }
}
