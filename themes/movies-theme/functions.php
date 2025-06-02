<?php
/**
 * Movies Theme Functions
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define theme constants
define('MOVIES_THEME_VERSION', '1.0');
define('MOVIES_THEME_PATH', get_template_directory());
define('MOVIES_THEME_URL', get_template_directory_uri());

// Theme setup
function movies_theme_setup() {
    // Add theme support
    add_theme_support('post-thumbnails');
    add_theme_support('title-tag');
    add_theme_support('custom-logo');
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption'));
    
    // Register navigation menus
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'movies-theme'),
        'footer' => __('Footer Menu', 'movies-theme'),
    ));
}
add_action('after_setup_theme', 'movies_theme_setup');

// Enqueue scripts and styles
function movies_theme_scripts() {
    // Check if we're in development or production mode
    $is_development = defined('WP_DEBUG') && WP_DEBUG;
    
    // Enqueue main stylesheet (compiled from Sass)
    wp_enqueue_style(
        'movies-theme-style', 
        MOVIES_THEME_URL . '/assets/css/main.css', 
        array(), 
        MOVIES_THEME_VERSION
    );
    
    // Enqueue JavaScript files
    if ($is_development) {
        // Development mode - load individual files
        wp_enqueue_script('movies-theme-main', MOVIES_THEME_URL . '/assets/js/main.js', array('jquery'), MOVIES_THEME_VERSION, true);
        wp_enqueue_script('movies-theme-ajax-filters', MOVIES_THEME_URL . '/assets/js/ajax-filters.js', array('jquery'), MOVIES_THEME_VERSION, true);
        wp_enqueue_script('movies-theme-wishlist', MOVIES_THEME_URL . '/assets/js/wishlist.js', array('jquery'), MOVIES_THEME_VERSION, true);
        wp_enqueue_script('movies-theme-search', MOVIES_THEME_URL . '/assets/js/search.js', array('jquery'), MOVIES_THEME_VERSION, true);
    } else {
        // Production mode - load minified bundle
        if (file_exists(MOVIES_THEME_PATH . '/assets/js/main.min.js')) {
            wp_enqueue_script('movies-theme-bundle', MOVIES_THEME_URL . '/assets/js/main.min.js', array('jquery'), MOVIES_THEME_VERSION, true);
        } else {
            // Fallback to individual files if minified version doesn't exist
            wp_enqueue_script('movies-theme-main', MOVIES_THEME_URL . '/assets/js/main.js', array('jquery'), MOVIES_THEME_VERSION, true);
            wp_enqueue_script('movies-theme-ajax-filters', MOVIES_THEME_URL . '/assets/js/ajax-filters.js', array('jquery'), MOVIES_THEME_VERSION, true);
            wp_enqueue_script('movies-theme-wishlist', MOVIES_THEME_URL . '/assets/js/wishlist.js', array('jquery'), MOVIES_THEME_VERSION, true);
            wp_enqueue_script('movies-theme-search', MOVIES_THEME_URL . '/assets/js/search.js', array('jquery'), MOVIES_THEME_VERSION, true);
        }
    }
    
    // Localize script for AJAX
    wp_localize_script($is_development ? 'movies-theme-ajax-filters' : 'movies-theme-bundle', 'movies_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('movies_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'movies_theme_scripts');

// Include theme files
require_once MOVIES_THEME_PATH . '/inc/custom-post-type-movies.php';
require_once MOVIES_THEME_PATH . '/inc/custom-post-type-actors.php';
require_once MOVIES_THEME_PATH . '/inc/custom-post-type-upcoming.php';
require_once MOVIES_THEME_PATH . '/inc/custom-fields.php';
require_once MOVIES_THEME_PATH . '/inc/api-functions.php';
require_once MOVIES_THEME_PATH . '/inc/widgets.php';
require_once MOVIES_THEME_PATH . '/inc/ajax-handlers.php';
require_once MOVIES_THEME_PATH . '/inc/user-functions.php';
require_once MOVIES_THEME_PATH . '/inc/helpers.php';

// Include custom widgets
require_once MOVIES_THEME_PATH . '/widgets/upcoming-movies-widget.php';
require_once MOVIES_THEME_PATH . '/widgets/popular-actors-widget.php';
require_once MOVIES_THEME_PATH . '/widgets/movie-search-widget.php';

// Include blocks
require_once MOVIES_THEME_PATH . '/inc/blocks.php'; 