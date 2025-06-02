<?php
/**
 * Custom Gutenberg Blocks Registration for Movies Theme
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Include block components
require_once get_template_directory() . '/components/upcoming-movies-block.php';
require_once get_template_directory() . '/components/popular-actors-block.php';

/**
 * Register custom block category
 */
function movies_theme_register_block_category($categories) {
    return array_merge(
        $categories,
        array(
            array(
                'slug'  => 'movies-theme',
                'title' => __('Movies Theme', 'movies-theme'),
                'icon'  => 'video-alt2',
            ),
        )
    );
}
add_filter('block_categories_all', 'movies_theme_register_block_category', 10, 2);

/**
 * Register custom blocks
 */
function movies_theme_register_blocks() {
    // Register Upcoming Movies Block
    register_block_type('movies-theme/upcoming-movies', array(
        'attributes' => array(
            'limit' => array(
                'type' => 'number',
                'default' => 5
            ),
            'showDate' => array(
                'type' => 'boolean',
                'default' => true
            ),
            'showGenre' => array(
                'type' => 'boolean',
                'default' => true
            ),
        ),
        'render_callback' => 'movies_theme_render_upcoming_movies_block',
        'editor_script' => 'movies-theme-blocks',
        'editor_style' => 'movies-theme-blocks-editor',
        'style' => 'movies-theme-blocks',
    ));

    // Register Popular Actors Block
    register_block_type('movies-theme/popular-actors', array(
        'attributes' => array(
            'limit' => array(
                'type' => 'number',
                'default' => 10
            ),
            'showPhoto' => array(
                'type' => 'boolean',
                'default' => true
            ),
            'showBio' => array(
                'type' => 'boolean',
                'default' => false
            ),
        ),
        'render_callback' => 'movies_theme_render_popular_actors_block',
        'editor_script' => 'movies-theme-blocks',
        'editor_style' => 'movies-theme-blocks-editor',
        'style' => 'movies-theme-blocks',
    ));
}
add_action('init', 'movies_theme_register_blocks');

/**
 * Enqueue block assets
 */
function movies_theme_enqueue_block_assets() {
    $blocks_js_path = get_template_directory() . '/assets/js/blocks.js';
    $blocks_css_path = get_template_directory() . '/assets/css/blocks.css';
    $upcoming_css_path = get_template_directory() . '/assets/css/upcoming-movies-slider.css';
    
    // Enqueue block editor script
    if (file_exists($blocks_js_path)) {
        wp_enqueue_script(
            'movies-theme-blocks',
            get_template_directory_uri() . '/assets/js/blocks.js',
            array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n'),
            filemtime($blocks_js_path)
        );
    }

    // Enqueue block styles for both frontend and backend
    if (file_exists($blocks_css_path)) {
        wp_enqueue_style(
            'movies-theme-blocks',
            get_template_directory_uri() . '/assets/css/blocks.css',
            array(),
            filemtime($blocks_css_path)
        );
    }

    // Enqueue upcoming movies grid styles
    if (file_exists($upcoming_css_path)) {
        wp_enqueue_style(
            'movies-theme-upcoming-grid',
            get_template_directory_uri() . '/assets/css/upcoming-movies-slider.css',
            array(),
            filemtime($upcoming_css_path)
        );
    }
}
add_action('enqueue_block_editor_assets', 'movies_theme_enqueue_block_assets');
add_action('wp_enqueue_scripts', 'movies_theme_enqueue_block_assets');