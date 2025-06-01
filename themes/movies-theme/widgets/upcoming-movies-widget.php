<?php
/**
 * Upcoming Movies Widget
 */

class Upcoming_Movies_Widget extends WP_Widget {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct(
            'upcoming_movies_widget',
            __('Upcoming Movies', 'movies-theme'),
            array(
                'description' => __('Display upcoming movie releases', 'movies-theme'),
                'classname' => 'upcoming-movies-widget'
            )
        );
    }

    /**
     * Widget Output
     */
    public function widget($args, $instance) {
        echo $args['before_widget'];

        $title = !empty($instance['title']) ? $instance['title'] : __('Upcoming Movies', 'movies-theme');
        $number = !empty($instance['number']) ? absint($instance['number']) : 5;
        $show_date = isset($instance['show_date']) ? $instance['show_date'] : true;
        $show_poster = isset($instance['show_poster']) ? $instance['show_poster'] : true;

        echo $args['before_title'] . apply_filters('widget_title', $title) . $args['after_title'];

        // Use the new API function to get upcoming movies
        $upcoming_movies_grouped = movies_get_upcoming_movies($number);

        if (!empty($upcoming_movies_grouped)): ?>
            <div class="upcoming-movies-list">
                <?php foreach ($upcoming_movies_grouped as $month_year => $movies): ?>
                    <div class="month-group">
                        <h5 class="month-header"><?php echo esc_html($month_year); ?></h5>
                        <?php foreach ($movies as $movie): ?>
                            <div class="upcoming-movie-item">
                                <?php if ($show_poster && has_post_thumbnail($movie->ID)): ?>
                                    <div class="movie-poster">
                                        <a href="<?php echo get_permalink($movie->ID); ?>">
                                            <?php echo get_the_post_thumbnail($movie->ID, 'thumbnail'); ?>
                                        </a>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="movie-info">
                                    <h4 class="movie-title">
                                        <a href="<?php echo get_permalink($movie->ID); ?>"><?php echo esc_html($movie->post_title); ?></a>
                                    </h4>
                                    
                                    <?php if ($show_date): ?>
                                        <?php $release_date = get_post_meta($movie->ID, 'release_date', true); ?>
                                        <?php if ($release_date): ?>
                                            <div class="release-date">
                                                <span class="date-label"><?php _e('Release Date:', 'movies-theme'); ?></span>
                                                <span class="date-value"><?php echo date_i18n(get_option('date_format'), strtotime($release_date)); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    
                                    <?php
                                    $genres = get_the_terms($movie->ID, 'genre');
                                    if ($genres && !is_wp_error($genres)): ?>
                                        <div class="movie-genres">
                                            <?php foreach (array_slice($genres, 0, 2) as $genre): ?>
                                                <span class="genre-tag"><?php echo esc_html($genre->name); ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="widget-footer">
                <a href="<?php echo get_post_type_archive_link('movie'); ?>" class="view-all-link">
                    <?php _e('View All Movies', 'movies-theme'); ?>
                </a>
            </div>
        <?php else: ?>
            <p class="no-upcoming-movies"><?php _e('No upcoming movies found.', 'movies-theme'); ?></p>
        <?php endif;

        echo $args['after_widget'];
    }

    /**
     * Widget Form
     */
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : __('Upcoming Movies', 'movies-theme');
        $number = !empty($instance['number']) ? absint($instance['number']) : 5;
        $show_date = isset($instance['show_date']) ? (bool) $instance['show_date'] : true;
        $show_poster = isset($instance['show_poster']) ? (bool) $instance['show_poster'] : true;
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php _e('Title:', 'movies-theme'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('number')); ?>"><?php _e('Number of movies to show:', 'movies-theme'); ?></label>
            <input class="tiny-text" id="<?php echo esc_attr($this->get_field_id('number')); ?>" name="<?php echo esc_attr($this->get_field_name('number')); ?>" type="number" step="1" min="1" value="<?php echo esc_attr($number); ?>" size="3">
        </p>
        
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_date); ?> id="<?php echo esc_attr($this->get_field_id('show_date')); ?>" name="<?php echo esc_attr($this->get_field_name('show_date')); ?>">
            <label for="<?php echo esc_attr($this->get_field_id('show_date')); ?>"><?php _e('Show release date', 'movies-theme'); ?></label>
        </p>
        
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_poster); ?> id="<?php echo esc_attr($this->get_field_id('show_poster')); ?>" name="<?php echo esc_attr($this->get_field_name('show_poster')); ?>">
            <label for="<?php echo esc_attr($this->get_field_id('show_poster')); ?>"><?php _e('Show movie poster', 'movies-theme'); ?></label>
        </p>
        <?php
    }

    /**
     * Update Widget
     */
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['number'] = (!empty($new_instance['number'])) ? absint($new_instance['number']) : 5;
        $instance['show_date'] = isset($new_instance['show_date']) ? (bool) $new_instance['show_date'] : false;
        $instance['show_poster'] = isset($new_instance['show_poster']) ? (bool) $new_instance['show_poster'] : false;

        return $instance;
    }
}

// Register the widget
function register_upcoming_movies_widget() {
    register_widget('Upcoming_Movies_Widget');
}
add_action('widgets_init', 'register_upcoming_movies_widget'); 