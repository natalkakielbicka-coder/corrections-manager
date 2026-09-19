<?php
/**
 * Plugin Name: Corrections Manager
 * Description: Panel do zgłaszania i obsługi poprawek na stronie internetowej.
 * Version: 1.0.0
 * Requires at least: 6.5
 * Requires PHP: 7.4
 * Author: Natalia Kiełbicka
 * Text Domain: corrections-manager
 */

if (! defined('ABSPATH')) {
    exit;
}

define('CORRECTIONS_MANAGER_VERSION', '1.0.0');
define('CORRECTIONS_MANAGER_DB_VERSION', '1.2.1');
define('CORRECTIONS_MANAGER_FILE', __FILE__);
define('CORRECTIONS_MANAGER_PATH', plugin_dir_path(__FILE__));
define('CORRECTIONS_MANAGER_URL', plugin_dir_url(__FILE__));

require_once CORRECTIONS_MANAGER_PATH . 'includes/rest-api.php';

/**
 * Pobiera wpis aplikacji z manifestu Vite.
 *
 * @return array|WP_Error
 */
function corrections_manager_get_build_entry()
{
    $manifest_path =
        CORRECTIONS_MANAGER_PATH . 'build/.vite/manifest.json';

    if (! is_readable($manifest_path)) {
        return new WP_Error(
            'corrections_manager_manifest_missing',
            'Nie znaleziono pliku build/.vite/manifest.json.'
        );
    }

    $manifest_contents = file_get_contents(
        $manifest_path
    );

    if (false === $manifest_contents) {
        return new WP_Error(
            'corrections_manager_manifest_read_failed',
            'Nie udało się odczytać manifestu aplikacji.'
        );
    }

    $manifest = json_decode(
        $manifest_contents,
        true
    );

    if (
        ! is_array($manifest)
        || JSON_ERROR_NONE !== json_last_error()
    ) {
        return new WP_Error(
            'corrections_manager_manifest_invalid',
            'Manifest aplikacji zawiera nieprawidłowy JSON.'
        );
    }

    if (
        empty($manifest['index.html'])
        || empty($manifest['index.html']['file'])
    ) {
        return new WP_Error(
            'corrections_manager_manifest_entry_missing',
            'W manifeście brakuje głównego pliku aplikacji.'
        );
    }

    $entry = $manifest['index.html'];

    $script_path =
        CORRECTIONS_MANAGER_PATH
        . 'build/'
        . $entry['file'];

    if (! is_readable($script_path)) {
        return new WP_Error(
            'corrections_manager_script_missing',
            'Nie znaleziono głównego pliku JavaScript aplikacji.'
        );
    }

    if (! empty($entry['css'])) {
        foreach ($entry['css'] as $css_file) {
            $css_path =
                CORRECTIONS_MANAGER_PATH
                . 'build/'
                . $css_file;

            if (! is_readable($css_path)) {
                return new WP_Error(
                    'corrections_manager_css_missing',
                    'Nie znaleziono pliku CSS aplikacji.'
                );
            }
        }
    }

    return $entry;
}

/**
 * Wyświetla kontener aplikacji Vue.
 *
 * @return string
 */
function corrections_manager_render_app(): string
{
    $build_entry =
    corrections_manager_get_build_entry();

    if (is_wp_error($build_entry)) {
        if (current_user_can('manage_options')) {
            return sprintf(
                '<div class="corrections-manager-error">
                    <strong>Corrections Manager:</strong> %s
                </div>',
                esc_html(
                    $build_entry->get_error_message()
                )
            );
        }

        return '<div class="corrections-manager-error">
            Panel poprawek jest obecnie niedostępny.
        </div>';
    }

    $corrections_url = rest_url(
        'corrections-manager/v1/corrections'
    );

    $pages_url = rest_url(
        'corrections-manager/v1/pages'
    );

    $current_page_id = get_queried_object_id();

    $site_name = get_bloginfo('name');

    $rest_nonce = wp_create_nonce('wp_rest');

    $current_user = wp_get_current_user();

    $current_user_name = is_user_logged_in()
        ? $current_user->display_name
        : '';

    return sprintf(
        '<div
            id="corrections-manager-app"
            data-corrections-url="%s"
            data-pages-url="%s"
            data-current-page-id="%d"
            data-rest-nonce="%s"
            data-current-user-name="%s"
            data-site-name="%s"
        >
            <p>Ładowanie panelu poprawek...</p>
        </div>',
        esc_url($corrections_url),
        esc_url($pages_url),
        absint($current_page_id),
        esc_attr($rest_nonce),
        esc_attr($current_user_name),
        esc_attr($site_name)
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

    $entry = corrections_manager_get_build_entry();

    if (is_wp_error($entry)) {
        return;
    }

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
        version bigint(20) unsigned NOT NULL DEFAULT 1,
        editing_by varchar(150) DEFAULT NULL,
        editing_token varchar(64) DEFAULT NULL,
        editing_expires_at datetime DEFAULT NULL,
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
        CORRECTIONS_MANAGER_DB_VERSION
    );
}

register_activation_hook(
    CORRECTIONS_MANAGER_FILE,
    'corrections_manager_activate'
);

/**
 * Aktualizuje strukturę bazy po zmianie wersji schematu.
 */
function corrections_manager_maybe_upgrade_database(): void
{
    $installed_version = get_option(
        'corrections_manager_db_version'
    );

    if (
        $installed_version ===
        CORRECTIONS_MANAGER_DB_VERSION
    ) {
        return;
    }

    corrections_manager_activate();
}

add_action(
    'plugins_loaded',
    'corrections_manager_maybe_upgrade_database'
);

/**
 * Informuje administratora o brakującym buildzie aplikacji.
 */
function corrections_manager_build_admin_notice(): void
{
    $build_entry =
        corrections_manager_get_build_entry();

    if (! is_wp_error($build_entry)) {
        return;
    }

    ?>
    <div class="notice notice-error">
        <p>
            <strong>Corrections Manager:</strong>
            <?php
            echo esc_html(
                $build_entry->get_error_message()
            );
            ?>
        </p>
    </div>
    <?php
}

add_action(
    'admin_notices',
    'corrections_manager_build_admin_notice'
);