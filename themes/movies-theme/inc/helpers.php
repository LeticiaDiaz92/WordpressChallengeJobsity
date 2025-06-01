<?php
/**
 * Helper functions for Movies Theme
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get movie poster URL
 */
function get_movie_poster($post_id = null, $size = 'medium') {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    
    $poster_url = get_the_post_thumbnail_url($post_id, $size);
    
    if (!$poster_url) {
        $poster_url = MOVIES_THEME_URL . '/assets/images/placeholder-movie.jpg';
    }
    
    return $poster_url;
}

/**
 * Get actor photo URL
 */
function get_actor_photo($post_id = null, $size = 'medium') {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    
    $photo_url = get_the_post_thumbnail_url($post_id, $size);
    
    if (!$photo_url) {
        $photo_url = MOVIES_THEME_URL . '/assets/images/placeholder-actor.jpg';
    }
    
    return $photo_url;
}

/**
 * Get movie rating stars
 */
function get_movie_rating_stars($rating) {
    $stars = '';
    $full_stars = floor($rating);
    $half_star = ($rating - $full_stars) >= 0.5;
    
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $full_stars) {
            $stars .= '★';
        } elseif ($i == $full_stars + 1 && $half_star) {
            $stars .= '☆';
        } else {
            $stars .= '☆';
        }
    }
    
    return $stars;
}

/**
 * Get movie genres
 */
function get_movie_genres($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    
    return get_the_terms($post_id, 'genre');
}

/**
 * Format movie runtime
 */
function format_movie_runtime($minutes) {
    if (!$minutes) {
        return '';
    }
    
    $hours = floor($minutes / 60);
    $mins = $minutes % 60;
    
    if ($hours > 0) {
        return sprintf('%dh %dm', $hours, $mins);
    } else {
        return sprintf('%dm', $mins);
    }
}

/**
 * Truncate text
 */
function truncate_text($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    
    return substr($text, 0, $length) . $suffix;
} 