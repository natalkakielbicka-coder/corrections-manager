<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Rejestruje endpointy REST API wtyczki.
 */
function corrections_manager_register_rest_routes(): void
{
    register_rest_route(
        'corrections-manager/v1',
        '/corrections',
        [
            'methods' => WP_REST_Server::READABLE,
            'callback' => 'corrections_manager_get_corrections',
            'permission_callback' => '__return_true',
        ]
    );

    register_rest_route(
        'corrections-manager/v1',
        '/pages',
        [
            'methods' => WP_REST_Server::READABLE,
            'callback' => 'corrections_manager_get_pages',
            'permission_callback' => '__return_true',
        ]
    );

    register_rest_route(
        'corrections-manager/v1',
        '/corrections',
        [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => 'corrections_manager_create_correction',
            'permission_callback' => 'corrections_manager_verify_nonce',
            'args' => [
                'title' => [
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'description' => [
                    'required' => true,
                    'sanitize_callback' => 'sanitize_textarea_field',
                ],
                'pageId' => [
                    'required' => true,
                    'sanitize_callback' => 'absint',
                ],
                'author' => [
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]
    );

    register_rest_route(
        'corrections-manager/v1',
        '/corrections/(?P<id>\d+)',
        [
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => 'corrections_manager_update_correction',
            'permission_callback' => 'corrections_manager_verify_nonce',
            'args' => [
                'id' => [
                    'sanitize_callback' => 'absint',
                ],
                'title' => [
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'description' => [
                    'required' => true,
                    'sanitize_callback' => 'sanitize_textarea_field',
                ],
                'pageId' => [
                    'required' => true,
                    'sanitize_callback' => 'absint',
                ],
            ],
        ]
    );

    register_rest_route(
        'corrections-manager/v1',
        '/corrections/(?P<id>\d+)/status',
        [
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => 'corrections_manager_update_status',
            'permission_callback' => 'corrections_manager_verify_nonce',
            'args' => [
                'id' => [
                    'sanitize_callback' => 'absint',
                ],
                'status' => [
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field',
                    'validate_callback' => function (
                        $status
                    ): bool {
                        return in_array(
                            $status,
                            [
                                'inProgress',
                                'review',
                                'ready',
                            ],
                            true
                        );
                    },
                ],
            ],
        ]
    );
}

add_action(
    'rest_api_init',
    'corrections_manager_register_rest_routes'
);

/**
 * Pobiera poprawki wraz z komentarzami.
 *
 * @return WP_REST_Response
 */
function corrections_manager_get_corrections(): WP_REST_Response
{
    global $wpdb;

    $corrections_table = $wpdb->prefix . 'corrections_manager_corrections';
    $comments_table = $wpdb->prefix . 'corrections_manager_comments';

    $corrections = $wpdb->get_results(
        "SELECT * FROM {$corrections_table} ORDER BY created_at DESC",
        ARRAY_A
    );

    $comments = $wpdb->get_results(
        "SELECT * FROM {$comments_table} ORDER BY created_at ASC",
        ARRAY_A
    );

    $comments_by_correction = [];

    foreach ($comments as $comment) {
        $correction_id = (int) $comment['correction_id'];

        $comments_by_correction[$correction_id][] = [
            'id' => (int) $comment['id'],
            'author' => $comment['author'],
            'content' => $comment['content'],
            'createdAt' => $comment['created_at'],
        ];
    }

    $response = [];

    foreach ($corrections as $correction) {
        $correction_id = (int) $correction['id'];
        $image_id = $correction['image_id']
            ? (int) $correction['image_id']
            : null;

        $response[] = [
            'id' => $correction_id,
            'number' => (int) $correction['correction_number'],
            'title' => $correction['title'],
            'description' => $correction['description'],
            'pageId' => (int) $correction['page_id'],
            'status' => $correction['status'],
            'author' => $correction['author'],
            'imageId' => $image_id,
            'imageUrl' => $image_id
                ? wp_get_attachment_url($image_id)
                : '',
            'isNew' => (bool) $correction['is_new'],
            'createdAt' => $correction['created_at'],
            'updatedAt' => $correction['updated_at'],
            'comments' => $comments_by_correction[$correction_id] ?? [],
        ];
    }

    return rest_ensure_response($response);
}

/**
 * Pobiera opublikowane strony WordPressa.
 *
 * @return WP_REST_Response
 */
function corrections_manager_get_pages(
    WP_REST_Request $request
): WP_REST_Response {
    $excluded_page_id = absint(
        $request->get_param('exclude')
    );

    $wordpress_pages = get_pages(
        [
            'post_status' => 'publish',
            'sort_column' => 'menu_order,post_title',
            'sort_order' => 'ASC',
            'exclude' => $excluded_page_id
                ? [$excluded_page_id]
                : [],
        ]
    );

    $pages = [];

    foreach ($wordpress_pages as $wordpress_page) {
        $pages[] = [
            'id' => (int) $wordpress_page->ID,
            'title' => get_the_title($wordpress_page->ID),
            'url' => get_permalink($wordpress_page->ID),
        ];
    }

    return rest_ensure_response($pages);
}

/**
 * Sprawdza token bezpieczeństwa żądania.
 *
 * @param WP_REST_Request $request Dane żądania REST API.
 *
 * @return true|WP_Error
 */
function corrections_manager_verify_nonce(
    WP_REST_Request $request
) {
    $nonce = $request->get_header('X-WP-Nonce');

    if (! $nonce) {
        $nonce = $request->get_param('nonce');
    }

    if (! wp_verify_nonce($nonce, 'wp_rest')) {
        return new WP_Error(
            'corrections_manager_invalid_nonce',
            'Nie udało się potwierdzić żądania.',
            ['status' => 403]
        );
    }

    return true;
}

/**
 * Zapisuje nową poprawkę w bazie danych.
 *
 * @param WP_REST_Request $request Dane żądania REST API.
 *
 * @return WP_REST_Response|WP_Error
 */
function corrections_manager_create_correction(
    WP_REST_Request $request
) {
    global $wpdb;

    $table_name =
        $wpdb->prefix . 'corrections_manager_corrections';

    $page_id = absint(
        $request->get_param('pageId')
    );

    if (
        'page' !== get_post_type($page_id)
        || 'publish' !== get_post_status($page_id)
    ) {
        return new WP_Error(
            'corrections_manager_invalid_page',
            'Wybrana strona nie istnieje.',
            ['status' => 400]
        );
    }

    $last_number = (int) $wpdb->get_var(
        "SELECT MAX(correction_number)
        FROM {$table_name}"
    );

    $created_at = current_time('mysql', true);

    $inserted = $wpdb->insert(
        $table_name,
        [
            'correction_number' => $last_number + 1,
            'title' => $request->get_param('title'),
            'description' => $request->get_param('description'),
            'page_id' => $page_id,
            'status' => 'inProgress',
            'author' => $request->get_param('author'),
            'image_id' => null,
            'is_new' => 1,
            'created_at' => $created_at,
            'updated_at' => null,
        ],
        [
            '%d',
            '%s',
            '%s',
            '%d',
            '%s',
            '%s',
            '%d',
            '%d',
            '%s',
            '%s',
        ]
    );

    if (false === $inserted) {
        return new WP_Error(
            'corrections_manager_insert_failed',
            'Nie udało się zapisać poprawki.',
            ['status' => 500]
        );
    }

    $correction_id = (int) $wpdb->insert_id;

    return new WP_REST_Response(
        [
            'id' => $correction_id,
            'number' => $last_number + 1,
            'title' => $request->get_param('title'),
            'description' => $request->get_param('description'),
            'pageId' => $page_id,
            'status' => 'inProgress',
            'author' => $request->get_param('author'),
            'imageId' => null,
            'imageUrl' => '',
            'isNew' => true,
            'createdAt' => $created_at,
            'updatedAt' => null,
            'comments' => [],
        ],
        201
    );
}

/**
 * Aktualizuje istniejącą poprawkę.
 *
 * @param WP_REST_Request $request Dane żądania.
 *
 * @return WP_REST_Response|WP_Error
 */
function corrections_manager_update_correction(
    WP_REST_Request $request
) {
    global $wpdb;

    $table_name =
        $wpdb->prefix . 'corrections_manager_corrections';

    $correction_id = absint(
        $request->get_param('id')
    );

    $existing_correction = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT id
            FROM {$table_name}
            WHERE id = %d",
            $correction_id
        )
    );

    if (! $existing_correction) {
        return new WP_Error(
            'corrections_manager_not_found',
            'Nie znaleziono poprawki.',
            ['status' => 404]
        );
    }

    $page_id = absint(
        $request->get_param('pageId')
    );

    if (
        'page' !== get_post_type($page_id)
        || 'publish' !== get_post_status($page_id)
    ) {
        return new WP_Error(
            'corrections_manager_invalid_page',
            'Wybrana strona nie istnieje.',
            ['status' => 400]
        );
    }

    $updated_at = current_time('mysql', true);

    $updated = $wpdb->update(
        $table_name,
        [
            'title' => $request->get_param('title'),
            'description' => $request->get_param(
                'description'
            ),
            'page_id' => $page_id,
            'updated_at' => $updated_at,
        ],
        [
            'id' => $correction_id,
        ],
        [
            '%s',
            '%s',
            '%d',
            '%s',
        ],
        [
            '%d',
        ]
    );

    if (false === $updated) {
        return new WP_Error(
            'corrections_manager_update_failed',
            'Nie udało się zaktualizować poprawki.',
            ['status' => 500]
        );
    }

    return rest_ensure_response(
        [
            'id' => $correction_id,
            'title' => $request->get_param('title'),
            'description' => $request->get_param(
                'description'
            ),
            'pageId' => $page_id,
            'updatedAt' => $updated_at,
        ]
    );
}

/**
 * Aktualizuje status poprawki.
 *
 * @param WP_REST_Request $request Dane żądania.
 *
 * @return WP_REST_Response|WP_Error
 */
function corrections_manager_update_status(
    WP_REST_Request $request
) {
    global $wpdb;

    $table_name =
        $wpdb->prefix . 'corrections_manager_corrections';

    $correction_id = absint(
        $request->get_param('id')
    );

    $status = $request->get_param('status');

    $existing_correction = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT id
            FROM {$table_name}
            WHERE id = %d",
            $correction_id
        )
    );

    if (! $existing_correction) {
        return new WP_Error(
            'corrections_manager_not_found',
            'Nie znaleziono poprawki.',
            ['status' => 404]
        );
    }

    $updated_at = current_time('mysql', true);

    $updated = $wpdb->update(
        $table_name,
        [
            'status' => $status,
            'is_new' => 0,
            'updated_at' => $updated_at,
        ],
        [
            'id' => $correction_id,
        ],
        [
            '%s',
            '%d',
            '%s',
        ],
        [
            '%d',
        ]
    );

    if (false === $updated) {
        return new WP_Error(
            'corrections_manager_status_failed',
            'Nie udało się zmienić statusu.',
            ['status' => 500]
        );
    }

    return rest_ensure_response(
        [
            'id' => $correction_id,
            'status' => $status,
            'isNew' => false,
            'updatedAt' => $updated_at,
        ]
    );
}