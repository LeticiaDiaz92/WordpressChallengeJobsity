<?php
/**
 * Single Actor Template
 */

get_header(); ?>

<div class="single-actor">
    <?php while (have_posts()): the_post(); ?>
        <div class="actor-header">
            <div class="actor-photo">
                <?php if (has_post_thumbnail()): ?>
                    <?php the_post_thumbnail('large', array('class' => 'actor-photo-image')); ?>
                <?php else: ?>
                    <div class="no-photo-placeholder">
                        <span><?php _e('No Photo Available', 'movies-theme'); ?></span>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="actor-info">
                <h1 class="actor-name"><?php the_title(); ?></h1>
                
                <div class="actor-meta">
                    <?php
                    $actor_birthdate = get_post_meta(get_the_ID(), 'actor_birthdate', true);
                    $actor_nationality = get_post_meta(get_the_ID(), 'actor_nationality', true);
                    $actor_awards = get_post_meta(get_the_ID(), 'actor_awards', true);
                    ?>
                    
                    <?php if ($actor_birthdate): ?>
                        <div class="meta-item">
                            <span class="meta-label"><?php _e('Born:', 'movies-theme'); ?></span>
                            <span class="meta-value"><?php echo esc_html(date('F j, Y', strtotime($actor_birthdate))); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($actor_nationality): ?>
                        <div class="meta-item">
                            <span class="meta-label"><?php _e('Nationality:', 'movies-theme'); ?></span>
                            <span class="meta-value"><?php echo esc_html($actor_nationality); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($actor_awards): ?>
                        <div class="meta-item">
                            <span class="meta-label"><?php _e('Awards:', 'movies-theme'); ?></span>
                            <span class="meta-value"><?php echo esc_html($actor_awards); ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="actor-actions">
                    <button class="btn btn-primary follow-actor" data-actor-id="<?php echo get_the_ID(); ?>">
                        <?php _e('Follow Actor', 'movies-theme'); ?>
                    </button>
                </div>
            </div>
        </div>

        <div class="actor-content">
            <div class="actor-biography">
                <h2><?php _e('Biography', 'movies-theme'); ?></h2>
                <?php if (get_the_content()): ?>
                    <?php the_content(); ?>
                <?php else: ?>
                    <p><?php _e('Biography not available.', 'movies-theme'); ?></p>
                <?php endif; ?>
            </div>

            <?php if (has_excerpt()): ?>
                <div class="actor-excerpt">
                    <h3><?php _e('Quick Bio', 'movies-theme'); ?></h3>
                    <?php the_excerpt(); ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="actor-filmography">
            <h2><?php _e('Filmography', 'movies-theme'); ?></h2>
            <?php
            // Get movies this actor has appeared in
            $filmography = new WP_Query(array(
                'post_type' => 'movie',
                'posts_per_page' => -1,
                'meta_query' => array(
                    array(
                        'key' => 'movie_cast',
                        'value' => get_the_ID(),
                        'compare' => 'LIKE'
                    )
                ),
                'orderby' => 'date',
                'order' => 'DESC'
            ));

            if ($filmography->have_posts()): ?>
                <div class="filmography-grid">
                    <?php while ($filmography->have_posts()): $filmography->the_post(); ?>
                        <?php get_template_part('template-parts/movie', 'card'); ?>
                    <?php endwhile; wp_reset_postdata(); ?>
                </div>
            <?php else: ?>
                <p><?php _e('No movies found for this actor.', 'movies-theme'); ?></p>
            <?php endif; ?>
        </div>

        <div class="related-actors">
            <h2><?php _e('Related Actors', 'movies-theme'); ?></h2>
            <?php
            // Get actors who have appeared in similar movies
            $related_actors = new WP_Query(array(
                'post_type' => 'actor',
                'posts_per_page' => 4,
                'post__not_in' => array(get_the_ID()),
                'orderby' => 'rand'
            ));

            if ($related_actors->have_posts()): ?>
                <div class="related-actors-grid">
                    <?php while ($related_actors->have_posts()): $related_actors->the_post(); ?>
                        <?php get_template_part('template-parts/actor', 'card'); ?>
                    <?php endwhile; wp_reset_postdata(); ?>
                </div>
            <?php else: ?>
                <p><?php _e('No related actors found.', 'movies-theme'); ?></p>
            <?php endif; ?>
        </div>

    <?php endwhile; ?>
</div>

<?php get_footer(); ?> 