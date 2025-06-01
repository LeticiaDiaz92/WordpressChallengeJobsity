<?php
/**
 * Template Name: Homepage
 */

get_header(); ?>

<div class="homepage-content">
    <div class="hero-section">
        <h1><?php _e('Welcome to Movies Database', 'movies-theme'); ?></h1>
        <p><?php _e('Discover your favorite movies and actors', 'movies-theme'); ?></p>
    </div>
    
    <div class="featured-movies">
        <h2><?php _e('Featured Movies', 'movies-theme'); ?></h2>
        <!-- Movie grid will be populated here -->
    </div>
    
    <div class="popular-actors">
        <h2><?php _e('Popular Actors', 'movies-theme'); ?></h2>
        <!-- Actor grid will be populated here -->
    </div>
</div>

<?php get_footer(); ?> 