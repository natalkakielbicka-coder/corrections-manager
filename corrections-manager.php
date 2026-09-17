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

/**
 * Wyświetla kontener aplikacji Vue.
 *
 * @return string
 */
function corrections_manager_render_app(): string
{
    return '<div id="corrections-manager-app">
        <p>Ładowanie panelu poprawek...</p>
    </div>';
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