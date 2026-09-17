<?php
/**
 * Plugin Name: Corrections Manager
 * Description: Panel do zgłaszania i obsługi poprawek na stronie internetowej.
 * Version: 1.0.0
 * Author: Natalia Kiełbicka
 * Text Domain: corrections-manager
 */

if (! defined('ABSPATH')) {
    exit;
}

define('CORRECTIONS_MANAGER_VERSION', '1.0.0');
define('CORRECTIONS_MANAGER_FILE', __FILE__);
define('CORRECTIONS_MANAGER_PATH', plugin_dir_path(__FILE__));
define('CORRECTIONS_MANAGER_URL', plugin_dir_url(__FILE__));

require_once CORRECTIONS_MANAGER_PATH . 'includes/rest-api.php';

/**
 * Wyświetla kontener aplikacji Vue.
 *
 * @return string
 */
function corrections_manager_render_app(): string
{
    $corrections_url = rest_url(
        'corrections-manager/v1/corrections'
    );

    $pages_url = rest_url(
        'corrections-manager/v1/pages'
    );

    $current_page_id = get_queried_object_id();

    $rest_nonce = wp_create_nonce('wp_rest');

    return sprintf(
        '<div
            id="corrections-manager-app"
            data-corrections-url="%s"
            data-pages-url="%s"
            data-current-page-id="%d"
            data-rest-nonce="%s"
        >
            <p>Ładowanie panelu poprawek...</p>
        </div>',
        esc_url($corrections_url),
        esc_url($pages_url),
        absint($current_page_id),
        esc_attr($rest_nonce)
    );
}

add_shortcode('corrections_manager', 'corrections_manager_render_app');

/**
 * Ładuje pliki aplikacji Vue na stronie zawierającej shortcode.
 */
function corrections_manager_enqueue_assets(): void
{
    global $post;

    if (! $post instanceof WP_Post) {
        return;
    }

    if (! has_shortcode($post->post_content, 'corrections_manager')) {
        return;
    }

    $manifest_path = CORRECTIONS_MANAGER_PATH . 'build/.vite/manifest.json';

    if (! file_exists($manifest_path)) {
        return;
    }

    $manifest = json_decode(
        file_get_contents($manifest_path),
        true
    );

    if (! isset($manifest['index.html'])) {
        return;
    }

    $entry = $manifest['index.html'];

    if (! empty($entry['css'])) {
        foreach ($entry['css'] as $index => $css_file) {
            wp_enqueue_style(
                'corrections-manager-' . $index,
                CORRECTIONS_MANAGER_URL . 'build/' . $css_file,
                [],
                CORRECTIONS_MANAGER_VERSION
            );
        }
    }

    if (! empty($entry['file'])) {
        wp_enqueue_script_module(
            'corrections-manager-app',
            CORRECTIONS_MANAGER_URL . 'build/' . $entry['file'],
            [],
            CORRECTIONS_MANAGER_VERSION
        );
    }
}

add_action(
    'wp_enqueue_scripts',
    'corrections_manager_enqueue_assets'
);

/**
 * Tworzy tabele potrzebne do działania wtyczki.
 */
function corrections_manager_activate(): void
{
    global $wpdb;

    $corrections_table = $wpdb->prefix . 'corrections_manager_corrections';
    $comments_table = $wpdb->prefix . 'corrections_manager_comments';
    $charset_collate = $wpdb->get_charset_collate();

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    $corrections_sql = "CREATE TABLE {$corrections_table} (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        correction_number bigint(20) unsigned NOT NULL,
        title varchar(255) NOT NULL,
        description longtext NOT NULL,
        page_id bigint(20) unsigned NOT NULL,
        status varchar(30) NOT NULL DEFAULT 'inProgress',
        author varchar(150) NOT NULL,
        image_id bigint(20) unsigned DEFAULT NULL,
        is_new tinyint(1) NOT NULL DEFAULT 1,
        created_at datetime NOT NULL,
        updated_at datetime DEFAULT NULL,
        PRIMARY KEY  (id),
        KEY correction_number (correction_number),
        KEY page_id (page_id),
        KEY status (status)
    ) {$charset_collate};";

    $comments_sql = "CREATE TABLE {$comments_table} (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        correction_id bigint(20) unsigned NOT NULL,
        author varchar(150) NOT NULL,
        content longtext NOT NULL,
        created_at datetime NOT NULL,
        PRIMARY KEY  (id),
        KEY correction_id (correction_id)
    ) {$charset_collate};";

    dbDelta($corrections_sql);
    dbDelta($comments_sql);

    update_option(
        'corrections_manager_db_version',
        CORRECTIONS_MANAGER_VERSION
    );
}

register_activation_hook(
    CORRECTIONS_MANAGER_FILE,
    'corrections_manager_activate'
);