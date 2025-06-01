<?php
/**
 * Popular Actors Widget
 */

class Popular_Actors_Widget extends WP_Widget {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct(
            'popular_actors_widget',
            __('Popular Actors', 'movies-theme'),
            array(
                'description' => __('Display popular actors', 'movies-theme'),
                'classname' => 'popular-actors-widget'
            )
        );
    }

    /**
     * Widget Output
     */
    public function widget($args, $instance) {
        echo $args['before_widget'];

        $title = !empty($instance['title']) ? $instance['title'] : __('Popular Actors', 'movies-theme');
        $number = !empty($instance['number']) ? absint($instance['number']) : 10;
        $show_photo = isset($instance['show_photo']) ? $instance['show_photo'] : true;
        $show_bio = isset($instance['show_bio']) ? $instance['show_bio'] : true;

        echo $args['before_title'] . apply_filters('widget_title', $title) . $args['after_title'];

        // Use the new API function to get popular actors
        $popular_actors = movies_get_popular_actors($number);

        if (!empty($popular_actors)): ?>
            <div class="popular-actors-list">
                <?php foreach ($popular_actors as $actor): ?>
                    <div class="popular-actor-item">
                        <?php if ($show_photo && has_post_thumbnail($actor->ID)): ?>
                            <div class="actor-photo">
                                <a href="<?php echo get_permalink($actor->ID); ?>">
                                    <?php echo get_the_post_thumbnail($actor->ID, 'thumbnail'); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <div class="actor-info">
                            <h4 class="actor-name">
                                <a href="<?php echo get_permalink($actor->ID); ?>"><?php echo esc_html($actor->post_title); ?></a>
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
                            
                            <?php 
                            // Show popularity score
                            $popularity = get_post_meta($actor->ID, 'popularity', true);
                            if ($popularity): ?>
                                <div class="actor-popularity">
                                    <span class="popularity-label"><?php _e('Popularity:', 'movies-theme'); ?></span>
                                    <span class="popularity-score"><?php echo number_format((float)$popularity, 1); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <?php 
                            // Show birth place if available
                            $birth_place = get_post_meta($actor->ID, 'place_of_birth', true);
                            if ($birth_place): ?>
                                <div class="actor-birthplace">
                                    <span class="birthplace-label"><?php _e('From:', 'movies-theme'); ?></span>
                                    <span class="birthplace-value"><?php echo esc_html($birth_place); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="widget-footer">
                <a href="<?php echo get_post_type_archive_link('actor'); ?>" class="view-all-link">
                    <?php _e('View All Actors', 'movies-theme'); ?>
                </a>
            </div>
        <?php else: ?>
            <p class="no-popular-actors"><?php _e('No popular actors found.', 'movies-theme'); ?></p>
        <?php endif;

        echo $args['after_widget'];
    }

    /**
     * Widget Form
     */
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : __('Popular Actors', 'movies-theme');
        $number = !empty($instance['number']) ? absint($instance['number']) : 10;
        $show_photo = isset($instance['show_photo']) ? (bool) $instance['show_photo'] : true;
        $show_bio = isset($instance['show_bio']) ? (bool) $instance['show_bio'] : true;
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php _e('Title:', 'movies-theme'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('number')); ?>"><?php _e('Number of actors to show:', 'movies-theme'); ?></label>
            <input class="tiny-text" id="<?php echo esc_attr($this->get_field_id('number')); ?>" name="<?php echo esc_attr($this->get_field_name('number')); ?>" type="number" step="1" min="1" value="<?php echo esc_attr($number); ?>" size="3">
        </p>
        
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_photo); ?> id="<?php echo esc_attr($this->get_field_id('show_photo')); ?>" name="<?php echo esc_attr($this->get_field_name('show_photo')); ?>">
            <label for="<?php echo esc_attr($this->get_field_id('show_photo')); ?>"><?php _e('Show actor photo', 'movies-theme'); ?></label>
        </p>
        
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_bio); ?> id="<?php echo esc_attr($this->get_field_id('show_bio')); ?>" name="<?php echo esc_attr($this->get_field_name('show_bio')); ?>">
            <label for="<?php echo esc_attr($this->get_field_id('show_bio')); ?>"><?php _e('Show short bio', 'movies-theme'); ?></label>
        </p>
        <?php
    }

    /**
     * Update Widget
     */
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['number'] = (!empty($new_instance['number'])) ? absint($new_instance['number']) : 10;
        $instance['show_photo'] = isset($new_instance['show_photo']) ? (bool) $new_instance['show_photo'] : false;
        $instance['show_bio'] = isset($new_instance['show_bio']) ? (bool) $new_instance['show_bio'] : false;

        return $instance;
    }
}

// Register the widget
function register_popular_actors_widget() {
    register_widget('Popular_Actors_Widget');
}
add_action('widgets_init', 'register_popular_actors_widget'); 