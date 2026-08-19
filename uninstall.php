<?php

defined('WP_UNINSTALL_PLUGIN') || exit;

function afdsp_uninstall_site(): void
{
    global $wpdb;
    wp_clear_scheduled_hook('afdsp_scheduled_refresh');
    foreach ((array) get_option('afdsp_cache_index', []) as $key) {
        delete_transient('afdsp_data_' . $key);
        delete_transient('afdsp_stale_' . $key);
    }
    foreach ((array) get_option('afdsp_lock_index', []) as $key) {
        delete_option('afdsp_lock_' . $key);
    }
    foreach (['afdsp_settings', 'afdsp_version', 'afdsp_cache_index', 'afdsp_lock_index', 'afdsp_last_success', 'afdsp_last_error'] as $option) {
        delete_option($option);
    }
    delete_site_transient('afdsp_github_release');

    // Defensive cleanup for interrupted writes or old cache indexes.
    $like = $wpdb->esc_like('_transient_afdsp_') . '%';
    $timeoutLike = $wpdb->esc_like('_transient_timeout_afdsp_') . '%';
    $optionLike = $wpdb->esc_like('afdsp_') . '%';
    $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s OR option_name LIKE %s", $like, $timeoutLike, $optionLike)); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
}

if (is_multisite()) {
    $siteIds = get_sites(['fields' => 'ids', 'number' => 0]);
    foreach ($siteIds as $siteId) {
        switch_to_blog((int) $siteId);
        afdsp_uninstall_site();
        restore_current_blog();
    }
} else {
    afdsp_uninstall_site();
}
