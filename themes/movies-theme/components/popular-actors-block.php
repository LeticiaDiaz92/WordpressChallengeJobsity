<?php
/**
 * Popular Actors Block Component
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Render callback for Popular Actors block
 */
function movies_theme_render_popular_actors_block($attributes) {
    $limit = isset($attributes['limit']) ? (int) $attributes['limit'] : 10;
    $show_photo = isset($attributes['showPhoto']) ? $attributes['showPhoto'] : true;
    $show_bio = isset($attributes['showBio']) ? $attributes['showBio'] : false;

    ob_start();
    
    $popular_actors = movies_get_popular_actors($limit);
    
    if (!empty($popular_actors)): ?>
        <div class="wp-block-movies-theme-popular-actors">
        <h1 class="archive-title"><?php _e('Popular Actors', 'movies-theme'); ?></h1>
            <div class="archive-grid" id= "actors-grid">
                <?php foreach ($popular_actors as $actor): ?>
                    <div class="actor-card">
                        <?php if ($show_photo && has_post_thumbnail($actor->ID)): ?>
                            <div class="actor-photo">
                                <a href="<?php echo get_permalink($actor->ID); ?>">
                                    <?php echo get_the_post_thumbnail($actor->ID, 'medium', array('alt' => esc_attr($actor->post_title))); ?>
                                </a>
                            </div>
                        <?php elseif ($show_photo): ?>
                            <div class="actor-photo no-photo">
                                <div class="no-photo-placeholder">
                                    <span><?php _e('No Photo', 'movies-theme'); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="actor-info">
                            <h4 class="actor-name">
                                <a href="<?php echo get_permalink($actor->ID); ?>">
                                    <?php echo esc_html($actor->post_title); ?>
                                </a>
                            </h4>
                            
                            <?php if ($show_bio): ?>
                                <?php 
                                $bio = wp_trim_words($actor->post_content, 15, '...');
                                if (!empty($bio)): ?>
                                    <div class="actor-bio">
                                        <?php echo wp_kses_post($bio); ?>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="wp-block-movies-theme-popular-actors">
            <p class="no-actors"><?php _e('No popular actors found.', 'movies-theme'); ?></p>
        </div>
    <?php endif;
    
    return ob_get_clean();
} 