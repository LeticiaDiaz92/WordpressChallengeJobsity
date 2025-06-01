<?php
/**
 * Custom Post Types for Movies Theme
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Register Movie post type
function movies_register_movie_post_type() {
    $labels = array(
        'name'               => _x('Movies', 'post type general name', 'movies-theme'),
        'singular_name'      => _x('Movie', 'post type singular name', 'movies-theme'),
        'menu_name'          => _x('Movies', 'admin menu', 'movies-theme'),
        'add_new'            => _x('Add New', 'movie', 'movies-theme'),
        'add_new_item'       => __('Add New Movie', 'movies-theme'),
        'new_item'           => __('New Movie', 'movies-theme'),
        'edit_item'          => __('Edit Movie', 'movies-theme'),
        'view_item'          => __('View Movie', 'movies-theme'),
        'all_items'          => __('All Movies', 'movies-theme'),
        'search_items'       => __('Search Movies', 'movies-theme'),
        'not_found'          => __('No movies found.', 'movies-theme'),
        'not_found_in_trash' => __('No movies found in Trash.', 'movies-theme')
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'movies'),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 5,
        'menu_icon'          => 'dashicons-video-alt3',
        'supports'           => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
        'show_in_rest'       => true,
    );

    register_post_type('movie', $args);
}
add_action('init', 'movies_register_movie_post_type');

// Register Actor post type
function movies_register_actor_post_type() {
    $labels = array(
        'name'               => _x('Actors', 'post type general name', 'movies-theme'),
        'singular_name'      => _x('Actor', 'post type singular name', 'movies-theme'),
        'menu_name'          => _x('Actors', 'admin menu', 'movies-theme'),
        'add_new'            => _x('Add New', 'actor', 'movies-theme'),
        'add_new_item'       => __('Add New Actor', 'movies-theme'),
        'new_item'           => __('New Actor', 'movies-theme'),
        'edit_item'          => __('Edit Actor', 'movies-theme'),
        'view_item'          => __('View Actor', 'movies-theme'),
        'all_items'          => __('All Actors', 'movies-theme'),
        'search_items'       => __('Search Actors', 'movies-theme'),
        'not_found'          => __('No actors found.', 'movies-theme'),
        'not_found_in_trash' => __('No actors found in Trash.', 'movies-theme')
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'actors'),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 6,
        'menu_icon'          => 'dashicons-groups',
        'supports'           => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
        'show_in_rest'       => true,
    );

    register_post_type('actor', $args);
}
add_action('init', 'movies_register_actor_post_type');

// Register taxonomies
function movies_register_taxonomies() {
    // Genre taxonomy for movies
    register_taxonomy('genre', 'movie', array(
        'hierarchical'      => true,
        'labels'            => array(
            'name'              => _x('Genres', 'taxonomy general name', 'movies-theme'),
            'singular_name'     => _x('Genre', 'taxonomy singular name', 'movies-theme'),
        ),
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'genre'),
        'show_in_rest'      => true,
    ));

    // Rating taxonomy for movies
    register_taxonomy('rating', 'movie', array(
        'hierarchical'      => true,
        'labels'            => array(
            'name'              => _x('Ratings', 'taxonomy general name', 'movies-theme'),
            'singular_name'     => _x('Rating', 'taxonomy singular name', 'movies-theme'),
        ),
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'rating'),
        'show_in_rest'      => true,
    ));
}
add_action('init', 'movies_register_taxonomies'); 