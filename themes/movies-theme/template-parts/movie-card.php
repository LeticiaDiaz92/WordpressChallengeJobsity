<?php
/**
 * Template part for displaying movie card
 */

$movie_year = get_post_meta(get_the_ID(), 'movie_year', true);
$movie_rating = get_post_meta(get_the_ID(), 'movie_rating', true);
$movie_runtime = get_post_meta(get_the_ID(), 'movie_runtime', true);
?>

<article id="movie-<?php the_ID(); ?>" <?php post_class('movie-card'); ?>>
    <div class="movie-card-inner">
        <div class="movie-poster">
            <a href="<?php the_permalink(); ?>">
                <?php if (has_post_thumbnail()): ?>
                    <?php the_post_thumbnail('medium', array('class' => 'movie-poster-image')); ?>
                <?php else: ?>
                    <div class="no-poster-placeholder">
                        <span><?php _e('No Poster', 'movies-theme'); ?></span>
                    </div>
                <?php endif; ?>
            </a>
            
            <div class="movie-actions">
                <button class="btn btn-sm add-to-wishlist" data-movie-id="<?php echo get_the_ID(); ?>" title="<?php _e('Add to Wishlist', 'movies-theme'); ?>">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
            
            <?php if ($movie_rating): ?>
                <div class="movie-rating-badge">
                    <span class="rating-value"><?php echo esc_html($movie_rating); ?></span>
                    <span class="rating-max">/10</span>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="movie-content">
            <h3 class="movie-title">
                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
            </h3>
            
            <div class="movie-meta">
                <?php if ($movie_year): ?>
                    <span class="movie-year"><?php echo esc_html($movie_year); ?></span>
                <?php endif; ?>
                
                <?php if ($movie_runtime): ?>
                    <span class="movie-runtime"><?php echo esc_html($movie_runtime); ?> <?php _e('min', 'movies-theme'); ?></span>
                <?php endif; ?>
            </div>
            
            <?php
            $genres = get_the_terms(get_the_ID(), 'genre');
            if ($genres && !is_wp_error($genres)): ?>
                <div class="movie-genres">
                    <?php foreach (array_slice($genres, 0, 2) as $genre): ?>
                        <a href="<?php echo get_term_link($genre); ?>" class="genre-tag">
                            <?php echo esc_html($genre->name); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <?php if (has_excerpt()): ?>
                <div class="movie-excerpt">
                    <?php echo wp_trim_words(get_the_excerpt(), 15, '...'); ?>
                </div>
            <?php endif; ?>
            
            <div class="movie-actions-bottom">
                <a href="<?php the_permalink(); ?>" class="btn btn-primary btn-sm">
                    <?php _e('View Details', 'movies-theme'); ?>
                </a>
            </div>
        </div>
    </div>
</article> 