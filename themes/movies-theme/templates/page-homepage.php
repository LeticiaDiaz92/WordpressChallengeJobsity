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

    <?php the_content(); ?>
</div>

<?php get_footer(); ?> 