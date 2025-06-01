<?php
/**
 * Widgets for Movies Theme
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Register widget areas
function movies_widgets_init() {
    register_sidebar(array(
        'name'          => __('Sidebar', 'movies-theme'),
        'id'            => 'sidebar-1',
        'description'   => __('Add widgets here.', 'movies-theme'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ));

    register_sidebar(array(
        'name'          => __('Footer 1', 'movies-theme'),
        'id'            => 'footer-1',
        'description'   => __('Add widgets here.', 'movies-theme'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ));

    register_sidebar(array(
        'name'          => __('Footer 2', 'movies-theme'),
        'id'            => 'footer-2',
        'description'   => __('Add widgets here.', 'movies-theme'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ));

    register_sidebar(array(
        'name'          => __('Footer 3', 'movies-theme'),
        'id'            => 'footer-3',
        'description'   => __('Add widgets here.', 'movies-theme'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ));
}
add_action('widgets_init', 'movies_widgets_init'); 