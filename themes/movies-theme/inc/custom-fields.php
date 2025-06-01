<?php
/**
 * Custom Fields for Movies Theme
 * This file would contain ACF field definitions or custom meta boxes
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Add custom meta boxes for movies
function movies_add_movie_meta_boxes() {
    add_meta_box(
        'movie-details',
        __('Movie Details', 'movies-theme'),
        'movies_movie_details_callback',
        'movie',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'movies_add_movie_meta_boxes');

function movies_movie_details_callback($post) {
    // Add nonce for security
    wp_nonce_field('movies_save_movie_details', 'movies_movie_details_nonce');
    
    // Get current values
    $year = get_post_meta($post->ID, '_movie_year', true);
    $runtime = get_post_meta($post->ID, '_movie_runtime', true);
    $rating = get_post_meta($post->ID, '_movie_rating', true);
    
    echo '<table class="form-table">';
    echo '<tr><th><label for="movie_year">' . __('Release Year', 'movies-theme') . '</label></th>';
    echo '<td><input type="number" id="movie_year" name="movie_year" value="' . esc_attr($year) . '" /></td></tr>';
    
    echo '<tr><th><label for="movie_runtime">' . __('Runtime (minutes)', 'movies-theme') . '</label></th>';
    echo '<td><input type="number" id="movie_runtime" name="movie_runtime" value="' . esc_attr($runtime) . '" /></td></tr>';
    
    echo '<tr><th><label for="movie_rating">' . __('Rating (1-10)', 'movies-theme') . '</label></th>';
    echo '<td><input type="number" id="movie_rating" name="movie_rating" value="' . esc_attr($rating) . '" min="1" max="10" step="0.1" /></td></tr>';
    echo '</table>';
}

// Save movie meta data
function movies_save_movie_details($post_id) {
    if (!isset($_POST['movies_movie_details_nonce']) || !wp_verify_nonce($_POST['movies_movie_details_nonce'], 'movies_save_movie_details')) {
        return;
    }
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    if (isset($_POST['movie_year'])) {
        update_post_meta($post_id, '_movie_year', sanitize_text_field($_POST['movie_year']));
    }
    
    if (isset($_POST['movie_runtime'])) {
        update_post_meta($post_id, '_movie_runtime', sanitize_text_field($_POST['movie_runtime']));
    }
    
    if (isset($_POST['movie_rating'])) {
        update_post_meta($post_id, '_movie_rating', sanitize_text_field($_POST['movie_rating']));
    }
}
add_action('save_post', 'movies_save_movie_details'); 