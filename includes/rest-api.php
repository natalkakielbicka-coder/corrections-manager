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
              'expectedVersion' => [
                    'required' => true,
                    'sanitize_callback' => 'absint',
                ],
                'removeImage' => [
                    'sanitize_callback' => 'rest_sanitize_boolean',
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
                'expectedVersion' => [
                    'required' => true,
                    'sanitize_callback' => 'absint',
                ],
            ],
        ]
    );

    register_rest_route(
        'corrections-manager/v1',
        '/corrections/(?P<id>\d+)',
        [
            'methods' => WP_REST_Server::DELETABLE,
            'callback' => 'corrections_manager_delete_correction',
            'permission_callback' => 'corrections_manager_verify_nonce',
            'args' => [
                'id' => [
                    'sanitize_callback' => 'absint',
                ],
            ],
        ]
    );

    register_rest_route(
        'corrections-manager/v1',
        '/corrections/(?P<id>\d+)/comments',
        [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => 'corrections_manager_add_comment',
            'permission_callback' => 'corrections_manager_verify_nonce',
            'args' => [
                'id' => [
                    'sanitize_callback' => 'absint',
                ],
                'content' => [
                    'required' => true,
                    'sanitize_callback' => 'sanitize_textarea_field',
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
        '/corrections/(?P<id>\d+)/comments/(?P<commentId>\d+)',
        [
            'methods' => WP_REST_Server::DELETABLE,
            'callback' => 'corrections_manager_delete_comment',
            'permission_callback' => 'corrections_manager_verify_nonce',
            'args' => [
                'id' => [
                    'sanitize_callback' => 'absint',
                ],
                'commentId' => [
                    'sanitize_callback' => 'absint',
                ],
            ],
        ]
    );

    register_rest_route(
        'corrections-manager/v1',
        '/corrections/(?P<id>\d+)/editing',
        [
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => 'corrections_manager_start_editing',
                'permission_callback' => 'corrections_manager_verify_nonce',
                'args' => [
                    'id' => [
                        'sanitize_callback' => 'absint',
                    ],
                    'editor' => [
                        'required' => true,
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'token' => [
                        'required' => true,
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
            ],
            [
                'methods' => WP_REST_Server::DELETABLE,
                'callback' => 'corrections_manager_stop_editing',
                'permission_callback' => 'corrections_manager_verify_nonce',
                'args' => [
                    'id' => [
                        'sanitize_callback' => 'absint',
                    ],
                    'token' => [
                        'required' => true,
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
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

        $editing_by = null;
        $editing_expires_at = null;

        if (
            ! empty($correction['editing_expires_at'])
            && strtotime($correction['editing_expires_at']) > time()
        ) {
            $editing_by = $correction['editing_by'];
            $editing_expires_at =
                $correction['editing_expires_at'];
        }

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
            'version' => (int) $correction['version'],
            'editingBy' => $editing_by,
            'editingExpiresAt' => $editing_expires_at,
            'comments' => $comments_by_correction[$correction_id] ?? [],
        ];
    }

    return corrections_manager_disable_rest_cache(
        rest_ensure_response($response)
    );
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
 * Zapisuje obraz przesłany przez REST API.
 *
 * @param WP_REST_Request $request Dane żądania.
 *
 * @return int|null|WP_Error
 */
function corrections_manager_upload_image(
    WP_REST_Request $request
) {
    $files = $request->get_file_params();

    if (empty($files['image'])) {
        return null;
    }

    $image = $files['image'];

    if (UPLOAD_ERR_OK !== $image['error']) {
        return new WP_Error(
            'corrections_manager_upload_error',
            'Nie udało się przesłać obrazu.',
            ['status' => 400]
        );
    }

    $max_image_size = 5 * 1024 * 1024;

    if ($image['size'] > $max_image_size) {
        return new WP_Error(
            'corrections_manager_image_too_large',
            'Obraz nie może być większy niż 5 MB.',
            ['status' => 400]
        );
    }

    $allowed_mime_types = [
        'jpg|jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'webp' => 'image/webp',
    ];

    $checked_file = wp_check_filetype_and_ext(
        $image['tmp_name'],
        $image['name'],
        $allowed_mime_types
    );

    if (
        empty($checked_file['ext'])
        || empty($checked_file['type'])
    ) {
        return new WP_Error(
            'corrections_manager_invalid_image',
            'Dozwolone są tylko obrazy JPG, PNG i WebP.',
            ['status' => 400]
        );
    }

    require_once ABSPATH
        . 'wp-admin/includes/file.php';

    require_once ABSPATH
        . 'wp-admin/includes/media.php';

    require_once ABSPATH
        . 'wp-admin/includes/image.php';

    $image_id = media_handle_upload(
        'image',
        0,
        [
            'post_title' => $request->get_param(
                'title'
            ),
        ],
        [
            'test_form' => false,
        ]
    );

    if (is_wp_error($image_id)) {
        return new WP_Error(
            'corrections_manager_image_save_failed',
            $image_id->get_error_message(),
            ['status' => 400]
        );
    }

    update_post_meta(
        $image_id,
        '_corrections_manager_upload',
        1
    );

    return (int) $image_id;
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

    $image_id =
    corrections_manager_upload_image($request);

    if (is_wp_error($image_id)) {
        return $image_id;
    }

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

    $created_at = current_time('mysql', true);

    $inserted = $wpdb->insert(
        $table_name,
        [
            'correction_number' => 0,
            'title' => $request->get_param('title'),
            'description' => $request->get_param('description'),
            'page_id' => $page_id,
            'status' => 'inProgress',
            'author' => $request->get_param('author'),
            'image_id' => $image_id,
            'is_new' => 1,
            'created_at' => $created_at,
            'updated_at' => null,
            'version' => 1,
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
            '%d',
        ]
    );

    if (false === $inserted) {
        if ($image_id) {
            wp_delete_attachment($image_id, true);
        }

        return new WP_Error(
            'corrections_manager_insert_failed',
            'Nie udało się zapisać poprawki.',
            ['status' => 500]
        );
    }

    $correction_id = (int) $wpdb->insert_id;

    $number_updated = $wpdb->update(
    $table_name,
        [
            'correction_number' => $correction_id,
        ],
        [
            'id' => $correction_id,
        ],
        [
            '%d',
        ],
        [
            '%d',
        ]
    );

    if (false === $number_updated) {
        $wpdb->delete(
            $table_name,
            [
                'id' => $correction_id,
            ],
            [
                '%d',
            ]
        );

        if ($image_id) {
            wp_delete_attachment($image_id, true);
        }

        return new WP_Error(
            'corrections_manager_number_update_failed',
            'Nie udało się nadać numeru poprawce.',
            ['status' => 500]
        );
    }

    return new WP_REST_Response(
        [
            'id' => $correction_id,
            'number' => $correction_id,
            'title' => $request->get_param('title'),
            'description' => $request->get_param('description'),
            'pageId' => $page_id,
            'status' => 'inProgress',
            'author' => $request->get_param('author'),
            'imageId' => $image_id
                ? (int) $image_id
                : null,
            'imageUrl' => $image_id
                ? wp_get_attachment_url($image_id)
                : '',
            'isNew' => true,
            'createdAt' => $created_at,
            'updatedAt' => null,
            'version' => 1,
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
            "SELECT id, image_id, version
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

    $expected_version = absint(
        $request->get_param('expectedVersion')
    );

    if (
        (int) $existing_correction->version
        !== $expected_version
    ) {
        return new WP_Error(
            'corrections_manager_edit_conflict',
            'Ta poprawka została zmieniona przez inną osobę. Pobierz najnowszą wersję i spróbuj ponownie.',
            ['status' => 409]
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

    $old_image_id = $existing_correction->image_id
    ? (int) $existing_correction->image_id
    : null;

    $new_image_id =
        corrections_manager_upload_image($request);

    if (is_wp_error($new_image_id)) {
        return $new_image_id;
    }

    $remove_image = rest_sanitize_boolean(
        $request->get_param('removeImage')
    );

    $final_image_id = $old_image_id;

    if ($new_image_id) {
        $final_image_id = $new_image_id;
    } elseif ($remove_image) {
        $final_image_id = null;
    }

    $updated_at = current_time('mysql', true);

    $new_version = $expected_version + 1;

    $updated = $wpdb->update(
        $table_name,
        [
            'title' => $request->get_param('title'),
            'description' => $request->get_param(
                'description'
            ),
            'page_id' => $page_id,
            'image_id' => $final_image_id,
            'updated_at' => $updated_at,
            'version' => $new_version,
        ],
        [
            'id' => $correction_id,
            'version' => $expected_version,
        ],
        [
            '%s',
            '%s',
            '%d',
            '%d',
            '%s',
            '%d',
        ],
        [
            '%d',
            '%d',
        ]
    );

    if (false === $updated) {
        if ($new_image_id) {
            wp_delete_attachment(
                $new_image_id,
                true
            );
        }

        return new WP_Error(
            'corrections_manager_update_failed',
            'Nie udało się zaktualizować poprawki.',
            ['status' => 500]
        );
    }

    if (0 === $updated) {
        if ($new_image_id) {
            wp_delete_attachment(
                $new_image_id,
                true
            );
        }

        return new WP_Error(
            'corrections_manager_edit_conflict',
            'Ta poprawka została właśnie zmieniona przez inną osobę. Pobierz najnowszą wersję i spróbuj ponownie.',
            ['status' => 409]
        );
    }

    $should_delete_old_image =
        $old_image_id
        && $old_image_id !== $final_image_id
        && get_post_meta(
            $old_image_id,
            '_corrections_manager_upload',
            true
        );

    if ($should_delete_old_image) {
        wp_delete_attachment(
            $old_image_id,
            true
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
            'imageId' => $final_image_id,
            'imageUrl' => $final_image_id
                ? wp_get_attachment_url(
                    $final_image_id
                )
                : '',
            'updatedAt' => $updated_at,
            'version' => $new_version,
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

    $expected_version = absint(
        $request->get_param('expectedVersion')
    );

    $new_version = $expected_version + 1;

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
            'version' => $new_version,
        ],
        [
            'id' => $correction_id,
            'version' => $expected_version,
        ],
        [
            '%s',
            '%d',
            '%s',
            '%d',
        ],
        [
            '%d',
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

    if (0 === $updated) {
        return new WP_Error(
            'corrections_manager_status_conflict',
            'Status tej poprawki został już zmieniony przez inną osobę.',
            ['status' => 409]
        );
    }

    return rest_ensure_response(
        [
            'id' => $correction_id,
            'status' => $status,
            'isNew' => false,
            'updatedAt' => $updated_at,
            'version' => $new_version,
        ]
    );
}

/**
 * Usuwa poprawkę wraz z jej komentarzami.
 *
 * @param WP_REST_Request $request Dane żądania.
 *
 * @return WP_REST_Response|WP_Error
 */
function corrections_manager_delete_correction(
    WP_REST_Request $request
) {
    global $wpdb;

    $corrections_table =
        $wpdb->prefix . 'corrections_manager_corrections';

    $comments_table =
        $wpdb->prefix . 'corrections_manager_comments';

    $correction_id = absint(
        $request->get_param('id')
    );

    $existing_correction = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT id, image_id
            FROM {$corrections_table}
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

    $comments_deleted = $wpdb->delete(
        $comments_table,
        [
            'correction_id' => $correction_id,
        ],
        [
            '%d',
        ]
    );

    if (false === $comments_deleted) {
        return new WP_Error(
            'corrections_manager_comments_delete_failed',
            'Nie udało się usunąć komentarzy poprawki.',
            ['status' => 500]
        );
    }

    $correction_deleted = $wpdb->delete(
        $corrections_table,
        [
            'id' => $correction_id,
        ],
        [
            '%d',
        ]
    );

    if (false === $correction_deleted) {
        return new WP_Error(
            'corrections_manager_delete_failed',
            'Nie udało się usunąć poprawki.',
            ['status' => 500]
        );
    }

    $image_id = $existing_correction->image_id
        ? (int) $existing_correction->image_id
        : null;

    if (
        $image_id
        && get_post_meta($image_id, '_corrections_manager_upload', true)
    ) {
        wp_delete_attachment($image_id, true);
    }

    return rest_ensure_response(
        [
            'id' => $correction_id,
            'deleted' => true,
        ]
    );
}

/**
 * Dodaje komentarz do poprawki.
 *
 * @param WP_REST_Request $request Dane żądania.
 *
 * @return WP_REST_Response|WP_Error
 */
function corrections_manager_add_comment(
    WP_REST_Request $request
) {
    global $wpdb;

    $corrections_table =
        $wpdb->prefix . 'corrections_manager_corrections';

    $comments_table =
        $wpdb->prefix . 'corrections_manager_comments';

    $correction_id = absint(
        $request->get_param('id')
    );

    $correction_exists = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT id
            FROM {$corrections_table}
            WHERE id = %d",
            $correction_id
        )
    );

    if (! $correction_exists) {
        return new WP_Error(
            'corrections_manager_not_found',
            'Nie znaleziono poprawki.',
            ['status' => 404]
        );
    }

    $created_at = current_time('mysql', true);

    $inserted = $wpdb->insert(
        $comments_table,
        [
            'correction_id' => $correction_id,
            'author' => $request->get_param('author'),
            'content' => $request->get_param('content'),
            'created_at' => $created_at,
        ],
        [
            '%d',
            '%s',
            '%s',
            '%s',
        ]
    );

    if (false === $inserted) {
        return new WP_Error(
            'corrections_manager_comment_failed',
            'Nie udało się dodać komentarza.',
            ['status' => 500]
        );
    }

    return new WP_REST_Response(
        [
            'id' => (int) $wpdb->insert_id,
            'author' => $request->get_param('author'),
            'content' => $request->get_param('content'),
            'createdAt' => $created_at,
        ],
        201
    );
}

/**
 * Usuwa komentarz poprawki.
 *
 * @param WP_REST_Request $request Dane żądania.
 *
 * @return WP_REST_Response|WP_Error
 */
function corrections_manager_delete_comment(
    WP_REST_Request $request
) {
    global $wpdb;

    $comments_table =
        $wpdb->prefix . 'corrections_manager_comments';

    $correction_id = absint(
        $request->get_param('id')
    );

    $comment_id = absint(
        $request->get_param('commentId')
    );

    $comment_exists = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT id
            FROM {$comments_table}
            WHERE id = %d AND correction_id = %d",
            $comment_id,
            $correction_id
        )
    );

    if (! $comment_exists) {
        return new WP_Error(
            'corrections_manager_comment_not_found',
            'Nie znaleziono komentarza.',
            ['status' => 404]
        );
    }

    $deleted = $wpdb->delete(
        $comments_table,
        [
            'id' => $comment_id,
        ],
        [
            '%d',
        ]
    );

    if (false === $deleted) {
        return new WP_Error(
            'corrections_manager_comment_delete_failed',
            'Nie udało się usunąć komentarza.',
            ['status' => 500]
        );
    }

    return rest_ensure_response(
        [
            'id' => $comment_id,
            'deleted' => true,
        ]
    );
}

function corrections_manager_get_editor_name(
    WP_REST_Request $request
): string {
    if (is_user_logged_in()) {
        $current_user = wp_get_current_user();

        return sanitize_text_field(
            $current_user->display_name
        );
    }

    return sanitize_text_field(
        $request->get_param('editor')
    );
}

function corrections_manager_start_editing(
    WP_REST_Request $request
) {
    global $wpdb;

    $table_name =
        $wpdb->prefix . 'corrections_manager_corrections';

    $correction_id = absint(
        $request->get_param('id')
    );

    $token = sanitize_text_field(
        $request->get_param('token')
    );

    $editor = corrections_manager_get_editor_name(
        $request
    );

    if (! $editor || ! $token) {
        return new WP_Error(
            'corrections_manager_invalid_editor',
            'Nie udało się ustalić osoby edytującej.',
            ['status' => 400]
        );
    }

    $now = current_time('mysql', true);

    $expires_at = gmdate(
        'Y-m-d H:i:s',
        time() + 90
    );

    $updated = $wpdb->query(
        $wpdb->prepare(
            "UPDATE {$table_name}
            SET editing_by = %s,
                editing_token = %s,
                editing_expires_at = %s
            WHERE id = %d
            AND (
                editing_expires_at IS NULL
                OR editing_expires_at < %s
                OR editing_token = %s
            )",
            $editor,
            $token,
            $expires_at,
            $correction_id,
            $now,
            $token
        )
    );

    if (false === $updated) {
        return new WP_Error(
            'corrections_manager_editing_failed',
            'Nie udało się rozpocząć edycji.',
            ['status' => 500]
        );
    }

    if ($updated > 0) {
        return rest_ensure_response(
            [
                'acquired' => true,
                'editingBy' => $editor,
                'editingExpiresAt' => $expires_at,
            ]
        );
    }

    $current_editor = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT editing_by, editing_token, editing_expires_at
            FROM {$table_name}
            WHERE id = %d",
            $correction_id
        ),
        ARRAY_A
    );

    if (! $current_editor) {
        return new WP_Error(
            'corrections_manager_not_found',
            'Nie znaleziono poprawki.',
            ['status' => 404]
        );
    }

    if ($current_editor['editing_token'] === $token) {
        return rest_ensure_response(
            [
                'acquired' => true,
                'editingBy' => $editor,
                'editingExpiresAt' =>
                    $current_editor['editing_expires_at'],
            ]
        );
    }

    return rest_ensure_response(
        [
            'acquired' => false,
            'editingBy' =>
                $current_editor['editing_by'],
            'editingExpiresAt' =>
                $current_editor['editing_expires_at'],
        ]
    );
}

function corrections_manager_stop_editing(
    WP_REST_Request $request
) {
    global $wpdb;

    $table_name =
        $wpdb->prefix . 'corrections_manager_corrections';

    $correction_id = absint(
        $request->get_param('id')
    );

    $token = sanitize_text_field(
        $request->get_param('token')
    );

    $wpdb->update(
        $table_name,
        [
            'editing_by' => null,
            'editing_token' => null,
            'editing_expires_at' => null,
        ],
        [
            'id' => $correction_id,
            'editing_token' => $token,
        ],
        [
            '%s',
            '%s',
            '%s',
        ],
        [
            '%d',
            '%s',
        ]
    );

    return rest_ensure_response(
        [
            'released' => true,
        ]
    );
}

/**
 * Dodaje nagłówki zapobiegające cache'owaniu
 * odpowiedzi Corrections Manager REST API.
 */
function corrections_manager_disable_rest_cache(
    WP_REST_Response $response
): WP_REST_Response {
    $response->header(
        'Cache-Control',
        'no-store, no-cache, must-revalidate, max-age=0'
    );

    return $response;
}