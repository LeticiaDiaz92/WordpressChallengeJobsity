<?php
/**
 * AJAX Handlers for Movies Theme
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Handle movie filtering
function movies_handle_filter_movies() {
    check_ajax_referer('movies_nonce', 'nonce');
    
    // Parse filters from request
    parse_str($_POST['filters'], $filters);
    
    // Build query args based on filters
    $args = array(
        'post_type' => 'movie',
        'posts_per_page' => 12,
        'post_status' => 'publish'
    );
    
    // Add filter logic here
    
    $query = new WP_Query($args);
    
    if ($query->have_posts()) {
        ob_start();
        while ($query->have_posts()) {
            $query->the_post();
            // Include movie card template
            get_template_part('template-parts/movie-card');
        }
        $html = ob_get_clean();
        wp_reset_postdata();
        
        wp_send_json_success(array('html' => $html));
    } else {
        wp_send_json_success(array('html' => '<p>No movies found.</p>'));
    }
}
add_action('wp_ajax_filter_movies', 'movies_handle_filter_movies');
add_action('wp_ajax_nopriv_filter_movies', 'movies_handle_filter_movies');

// Handle wishlist actions
function movies_handle_add_to_wishlist() {
    check_ajax_referer('movies_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Please log in to add movies to wishlist.'));
    }
    
    $movie_id = intval($_POST['movie_id']);
    $user_id = get_current_user_id();
    
    // Add to wishlist logic here
    
    wp_send_json_success(array('message' => 'Movie added to wishlist!'));
}
add_action('wp_ajax_add_to_wishlist', 'movies_handle_add_to_wishlist');

// Handle live search
function movies_handle_live_search() {
    check_ajax_referer('movies_nonce', 'nonce');
    
    $query = sanitize_text_field($_POST['query']);
    
    $args = array(
        'post_type' => array('movie', 'actor'),
        'posts_per_page' => 5,
        's' => $query,
        'post_status' => 'publish'
    );
    
    $search_query = new WP_Query($args);
    $results = array();
    
    if ($search_query->have_posts()) {
        while ($search_query->have_posts()) {
            $search_query->the_post();
            $results[] = array(
                'title' => get_the_title(),
                'url' => get_permalink(),
                'excerpt' => get_the_excerpt(),
                'thumbnail' => get_the_post_thumbnail_url(get_the_ID(), 'thumbnail'),
                'type' => get_post_type()
            );
        }
        wp_reset_postdata();
    }
    
    wp_send_json_success(array('results' => $results));
}
add_action('wp_ajax_live_search', 'movies_handle_live_search');
add_action('wp_ajax_nopriv_live_search', 'movies_handle_live_search'); 