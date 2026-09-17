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