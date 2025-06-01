<?php
/**
 * Template part for displaying actor card
 */

$actor_nationality = get_post_meta(get_the_ID(), 'actor_nationality', true);
$actor_birthdate = get_post_meta(get_the_ID(), 'actor_birthdate', true);
?>

<article id="actor-<?php the_ID(); ?>" <?php post_class('actor-card'); ?>>
    <div class="actor-card-inner">
        <div class="actor-photo">
            <a href="<?php the_permalink(); ?>">
                <?php if (has_post_thumbnail()): ?>
                    <?php the_post_thumbnail('medium', array('class' => 'actor-photo-image')); ?>
                <?php else: ?>
                    <div class="no-photo-placeholder">
                        <span><?php _e('No Photo', 'movies-theme'); ?></span>
                    </div>
                <?php endif; ?>
            </a>
            
            <div class="actor-actions">
                <button class="btn btn-sm follow-actor" data-actor-id="<?php echo get_the_ID(); ?>" title="<?php _e('Follow Actor', 'movies-theme'); ?>">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="8.5" cy="7" r="4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <line x1="20" y1="8" x2="20" y2="14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <line x1="23" y1="11" x2="17" y2="11" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
        </div>
        
        <div class="actor-content">
            <h3 class="actor-name">
                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
            </h3>
            
            <div class="actor-meta">
                <?php if ($actor_nationality): ?>
                    <span class="actor-nationality"><?php echo esc_html($actor_nationality); ?></span>
                <?php endif; ?>
                
                <?php if ($actor_birthdate): ?>
                    <span class="actor-age">
                        <?php 
                        $age = date_diff(date_create($actor_birthdate), date_create('today'))->y;
                        printf(__('Age %d', 'movies-theme'), $age);
                        ?>
                    </span>
                <?php endif; ?>
            </div>
            
            <?php
            // Count movies this actor has appeared in
            $movie_count = new WP_Query(array(
                'post_type' => 'movie',
                'posts_per_page' => -1,
                'meta_query' => array(
                    array(
                        'key' => 'movie_cast',
                        'value' => get_the_ID(),
                        'compare' => 'LIKE'
                    )
                ),
                'fields' => 'ids'
            ));
            $total_movies = $movie_count->found_posts;
            wp_reset_postdata();
            
            if ($total_movies > 0): ?>
                <div class="actor-movie-count">
                    <?php printf(
                        _n('%d movie', '%d movies', $total_movies, 'movies-theme'),
                        $total_movies
                    ); ?>
                </div>
            <?php endif; ?>
            
            <?php if (has_excerpt()): ?>
                <div class="actor-excerpt">
                    <?php echo wp_trim_words(get_the_excerpt(), 15, '...'); ?>
                </div>
            <?php endif; ?>
            
            <div class="actor-actions-bottom">
                <a href="<?php the_permalink(); ?>" class="btn btn-primary btn-sm">
                    <?php _e('View Profile', 'movies-theme'); ?>
                </a>
            </div>
        </div>
    </div>
</article> 