<?php

if (! defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

/*
 * Usuń wszystkie obrazy dodane przez Corrections Manager.
 *
 * Każdy obraz wgrywany przez poprawkę otrzymuje meta:
 * _corrections_manager_upload = 1
 */
$attachment_ids = get_posts(
    [
        'post_type' => 'attachment',
        'post_status' => 'any',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'meta_key' => '_corrections_manager_upload',
        'meta_value' => '1',
        'no_found_rows' => true,
    ]
);

foreach ($attachment_ids as $attachment_id) {
    wp_delete_attachment(
        (int) $attachment_id,
        true
    );
}

/*
 * Usuń tabele utworzone przez wtyczkę.
 */
$comments_table =
    $wpdb->prefix . 'corrections_manager_comments';

$corrections_table =
    $wpdb->prefix . 'corrections_manager_corrections';

$wpdb->query(
    "DROP TABLE IF EXISTS {$comments_table}"
);

$wpdb->query(
    "DROP TABLE IF EXISTS {$corrections_table}"
);

/*
 * Usuń opcje wtyczki.
 */
delete_option('corrections_manager_db_version');