<?php
/*
Plugin Name: Post Duplicator
Version: 1.0
Author: MyDevit-solutions
*/

if (!defined('ABSPATH'))
    exit;

add_filter('post_row_actions', 'duplicate_link', 10, 2);

// Add duplicate link for pages
add_filter('page_row_actions', 'duplicate_link', 10, 2);


// Add Duplicate link in the existing table row
function duplicate_link($actions, $post)
{
    if (current_user_can('edit_posts')) {

        $actions['duplicate'] = '<a href="' . wp_nonce_url(admin_url('admin.php?action=duplicate_post&post=' . $post->ID), 'duplicate_post_' . $post->ID) . '">Duplicate</a>';
    }
    return $actions;
}

// Handle operation for duplicating post
add_action('admin_action_duplicate_post', 'duplicate_post');

function duplicate_post()
{
    if (!isset($_GET['post']) || !isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'duplicate_post_' . $_GET['post'])) {
        wp_die("Invalid Request");
    }

    $post_id = absint($_GET['post']);
    $post = get_post($post_id);

    if (!$post) {
        wp_die("Post not found");
    }

    $new_post_id = wp_insert_post([
        'post_title' => $post->post_title . 'copy',
        'post_content' => $post->post_content,
        'post_exerpt' => $post->post_exerpt,
        'post_status' => 'draft',
        'post_type' => $post->post_type,
        'post_author' => get_current_user_id()
    ]);

    $meta_info = get_post_meta($post_id);

    foreach ($meta_info as $key => $values) {
        foreach ($values as $value) {
            add_post_meta($new_post_id, $key, maybe_unserialize($value));
        }
    }

    wp_redirect(admin_url('post.php?action=edit&post=' . $new_post_id));

    exit;
}


