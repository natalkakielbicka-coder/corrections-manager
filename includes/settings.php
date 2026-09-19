<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Znajduje stronę zawierającą shortcode Corrections Manager.
 *
 * @return int|null
 */
function corrections_manager_get_app_page_id(): ?int
{
    $pages = get_posts(
        [
            'post_type' => 'page',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
        ]
    );

    foreach ($pages as $page_id) {
        $content = get_post_field(
            'post_content',
            $page_id
        );

        if (
            is_string($content)
            && has_shortcode(
                $content,
                'corrections_manager'
            )
        ) {
            return (int) $page_id;
        }
    }

    return null;
}

/**
 * Dodaje stronę Lista poprawek w Ustawieniach.
 */
function corrections_manager_register_settings_page(): void
{
    add_options_page(
        'Lista poprawek',
        'Lista poprawek',
        'manage_options',
        'corrections-manager',
        'corrections_manager_render_settings_page'
    );
}

add_action(
    'admin_menu',
    'corrections_manager_register_settings_page'
);

/**
 * Wyświetla link do managera poprawek.
 */
function corrections_manager_render_settings_page(): void
{
    if (! current_user_can('manage_options')) {
        return;
    }

    $page_id =
        corrections_manager_get_app_page_id();

    ?>
    <div class="wrap">
        <h1>Lista poprawek</h1>

        <?php if ($page_id) : ?>
            <p>
                Panel poprawek jest dostępny na stronie:
            </p>

            <p>
                <a
                    href="<?php echo esc_url(
                        get_permalink($page_id)
                    ); ?>"
                    class="button button-primary"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    Otwórz listę poprawek
                </a>
            </p>

            <p class="description">
                <?php
                echo esc_html(
                    get_permalink($page_id)
                );
                ?>
            </p>
        <?php else : ?>
            <div class="notice notice-warning inline">
                <p>
                    Nie znaleziono opublikowanej strony
                    zawierającej shortcode
                    <code>[corrections_manager]</code>.
                </p>
            </div>
        <?php endif; ?>
    </div>
    <?php
}