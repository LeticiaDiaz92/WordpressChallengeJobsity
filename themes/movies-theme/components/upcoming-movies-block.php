<?php
/**
 * Upcoming Movies Block Component
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Render callback for Upcoming Movies block
 */
function movies_theme_render_upcoming_movies_block($attributes) {
    $limit = isset($attributes['limit']) ? (int) $attributes['limit'] : 0;
    $show_date = isset($attributes['showDate']) ? $attributes['showDate'] : true;
    $show_genre = isset($attributes['showGenre']) ? $attributes['showGenre'] : true;

    // Use the proper function that groups by release_date metadata
    $grouped_movies = movies_get_upcoming_movies($limit);

    ob_start();

    if (!empty($grouped_movies)): ?>
        <div class="wp-block-movies-theme-upcoming-movies">
            <div class="archive-grid-by-month">
                <?php foreach ($grouped_movies as $month_year => $movies): ?>
                    <div class="month-column">
                        <h3 class="month-title">
                            <span class="calendar-icon">🗓️</span>
                            <?php echo esc_html($month_year); ?>
                        </h3>
                        <div class="month-movies">
                            <?php foreach ($movies as $movie): ?>
                                <div class="movie-item">
                                    <?php echo movies_theme_render_movie_card($movie, $show_date, $show_genre); ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="wp-block-movies-theme-upcoming-movies">
            <p class="no-movies"><?php _e('No upcoming movies found.', 'movies-theme'); ?></p>
        </div>
    <?php endif;

    return ob_get_clean();
}

/**
 * Helper function to render movie card
 */
function movies_theme_render_movie_card($movie, $show_date = true, $show_genre = true) {
    ob_start(); ?>
    
    <div class="movie-card">
        <?php if (has_post_thumbnail($movie->ID)): ?>
            <div class="movie-poster">
                <a href="<?php echo get_permalink($movie->ID); ?>">
                    <?php echo get_the_post_thumbnail($movie->ID, 'medium', array('alt' => esc_attr($movie->post_title))); ?>
                </a>
            </div>
        <?php else: ?>
            <div class="movie-poster no-poster">
                <div class="no-poster-placeholder">
                    <span><?php _e('No Poster', 'movies-theme'); ?></span>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="movie-info">
            <h4 class="movie-title">
                <a href="<?php echo get_permalink($movie->ID); ?>">
                    <?php echo esc_html($movie->post_title); ?>
                </a>
            </h4>
            
            <?php if ($show_date): ?>
                <?php 
                $release_date = get_post_meta($movie->ID, 'release_date', true);
                if ($release_date): ?>
                    <div class="release-date">
                        <strong><?php _e('Release Date:', 'movies-theme'); ?></strong>
                        <?php echo date_i18n('F j, Y', strtotime($release_date)); ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <?php if ($show_genre): ?>
                <?php
                // Determine the correct genre taxonomy based on post type
                $post_type = get_post_type($movie->ID);
                $genre_taxonomy = ($post_type === 'upcoming') ? 'upcoming_genre' : 'genre';
                
                $genres = get_the_terms($movie->ID, $genre_taxonomy);
                if ($genres && !is_wp_error($genres)): ?>
                    <div class="movie-genres">
                        <strong><?php _e('Genre:', 'movies-theme'); ?></strong>
                        <?php 
                        $genre_names = array();
                        foreach ($genres as $genre) {
                            $genre_names[] = $genre->name;
                        }
                        echo esc_html(implode(', ', $genre_names));
                        ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <?php return ob_get_clean();
} 