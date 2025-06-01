<?php
/**
 * Single Movie Template
 */

get_header(); ?>

<div class="single-movie">
    <?php while (have_posts()): the_post(); ?>
        <div class="movie-header">
            <div class="movie-poster">
                <?php if (has_post_thumbnail()): ?>
                    <?php the_post_thumbnail('large', array('class' => 'movie-poster-image')); ?>
                <?php else: ?>
                    <div class="no-poster-placeholder">
                        <span><?php _e('No Poster Available', 'movies-theme'); ?></span>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="movie-info">
                <h1 class="movie-title"><?php the_title(); ?></h1>
                
                <div class="movie-meta">
                    <?php
                    $movie_year = get_post_meta(get_the_ID(), 'movie_year', true);
                    $movie_runtime = get_post_meta(get_the_ID(), 'movie_runtime', true);
                    $movie_rating = get_post_meta(get_the_ID(), 'movie_rating', true);
                    ?>
                    
                    <?php if ($movie_year): ?>
                        <div class="meta-item">
                            <span class="meta-label"><?php _e('Year:', 'movies-theme'); ?></span>
                            <span class="meta-value"><?php echo esc_html($movie_year); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($movie_runtime): ?>
                        <div class="meta-item">
                            <span class="meta-label"><?php _e('Runtime:', 'movies-theme'); ?></span>
                            <span class="meta-value"><?php echo esc_html($movie_runtime); ?> <?php _e('minutes', 'movies-theme'); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($movie_rating): ?>
                        <div class="meta-item">
                            <span class="meta-label"><?php _e('Rating:', 'movies-theme'); ?></span>
                            <span class="meta-value"><?php echo esc_html($movie_rating); ?>/10</span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="movie-genres">
                    <?php
                    $genres = get_the_terms(get_the_ID(), 'genre');
                    if ($genres && !is_wp_error($genres)): ?>
                        <div class="meta-item">
                            <span class="meta-label"><?php _e('Genres:', 'movies-theme'); ?></span>
                            <div class="genre-tags">
                                <?php foreach ($genres as $genre): ?>
                                    <a href="<?php echo get_term_link($genre); ?>" class="genre-tag">
                                        <?php echo esc_html($genre->name); ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="movie-actions">
                    <button class="btn btn-primary add-to-wishlist" data-movie-id="<?php echo get_the_ID(); ?>">
                        <?php _e('Add to Wishlist', 'movies-theme'); ?>
                    </button>
                </div>
            </div>
        </div>

        <div class="movie-content">
            <div class="movie-description">
                <h2><?php _e('Synopsis', 'movies-theme'); ?></h2>
                <?php the_content(); ?>
            </div>

            <?php if (has_excerpt()): ?>
                <div class="movie-excerpt">
                    <h3><?php _e('Quick Summary', 'movies-theme'); ?></h3>
                    <?php the_excerpt(); ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="movie-cast">
            <h2><?php _e('Cast', 'movies-theme'); ?></h2>
            <?php
            // Get related actors (this would need custom field or relationship)
            $cast_members = get_post_meta(get_the_ID(), 'movie_cast', true);
            if ($cast_members): ?>
                <div class="cast-grid">
                    <?php foreach ($cast_members as $actor_id): 
                        $actor = get_post($actor_id);
                        if ($actor): ?>
                            <div class="cast-member">
                                <a href="<?php echo get_permalink($actor_id); ?>">
                                    <?php echo get_the_post_thumbnail($actor_id, 'thumbnail'); ?>
                                    <h4><?php echo esc_html($actor->post_title); ?></h4>
                                </a>
                            </div>
                        <?php endif;
                    endforeach; ?>
                </div>
            <?php else: ?>
                <p><?php _e('Cast information not available.', 'movies-theme'); ?></p>
            <?php endif; ?>
        </div>

        <div class="related-movies">
            <h2><?php _e('Related Movies', 'movies-theme'); ?></h2>
            <?php
            $related_movies = new WP_Query(array(
                'post_type' => 'movie',
                'posts_per_page' => 4,
                'post__not_in' => array(get_the_ID()),
                'tax_query' => array(
                    array(
                        'taxonomy' => 'genre',
                        'field' => 'term_id',
                        'terms' => wp_get_post_terms(get_the_ID(), 'genre', array('fields' => 'ids')),
                    ),
                ),
            ));

            if ($related_movies->have_posts()): ?>
                <div class="related-movies-grid">
                    <?php while ($related_movies->have_posts()): $related_movies->the_post(); ?>
                        <?php get_template_part('template-parts/movie', 'card'); ?>
                    <?php endwhile; wp_reset_postdata(); ?>
                </div>
            <?php else: ?>
                <p><?php _e('No related movies found.', 'movies-theme'); ?></p>
            <?php endif; ?>
        </div>

    <?php endwhile; ?>
</div>

<?php get_footer(); ?> 