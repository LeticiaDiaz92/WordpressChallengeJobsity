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
        $number = !empty($instance['number']) ? absint($instance['number']) : 5;
        $show_photo = isset($instance['show_photo']) ? $instance['show_photo'] : true;
        $show_movie_count = isset($instance['show_movie_count']) ? $instance['show_movie_count'] : true;
        $sort_by = !empty($instance['sort_by']) ? $instance['sort_by'] : 'random';

        echo $args['before_title'] . apply_filters('widget_title', $title) . $args['after_title'];

        // Build query args based on sorting preference
        $query_args = array(
            'post_type' => 'actor',
            'posts_per_page' => $number,
            'post_status' => 'publish'
        );

        switch ($sort_by) {
            case 'recent':
                $query_args['orderby'] = 'date';
                $query_args['order'] = 'DESC';
                break;
            case 'popular':
                $query_args['meta_key'] = 'actor_popularity';
                $query_args['orderby'] = 'meta_value_num';
                $query_args['order'] = 'DESC';
                break;
            case 'alphabetical':
                $query_args['orderby'] = 'title';
                $query_args['order'] = 'ASC';
                break;
            case 'random':
            default:
                $query_args['orderby'] = 'rand';
                break;
        }

        $popular_actors = new WP_Query($query_args);

        if ($popular_actors->have_posts()): ?>
            <div class="popular-actors-list">
                <?php while ($popular_actors->have_posts()): $popular_actors->the_post(); ?>
                    <div class="popular-actor-item">
                        <?php if ($show_photo && has_post_thumbnail()): ?>
                            <div class="actor-photo">
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_post_thumbnail('thumbnail'); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <div class="actor-info">
                            <h4 class="actor-name">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h4>
                            
                            <?php if ($show_movie_count): ?>
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
                                ?>
                                
                                <?php if ($total_movies > 0): ?>
                                    <div class="movie-count">
                                        <?php printf(
                                            _n('%d movie', '%d movies', $total_movies, 'movies-theme'),
                                            $total_movies
                                        ); ?>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <?php
                            $nationality = get_post_meta(get_the_ID(), 'actor_nationality', true);
                            if ($nationality): ?>
                                <div class="actor-nationality">
                                    <span class="nationality-label"><?php _e('From:', 'movies-theme'); ?></span>
                                    <span class="nationality-value"><?php echo esc_html($nationality); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (has_excerpt()): ?>
                                <div class="actor-excerpt">
                                    <?php echo wp_trim_words(get_the_excerpt(), 15, '...'); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
            
            <div class="widget-footer">
                <a href="<?php echo get_post_type_archive_link('actor'); ?>" class="view-all-link">
                    <?php _e('View All Actors', 'movies-theme'); ?>
                </a>
            </div>
        <?php else: ?>
            <p class="no-popular-actors"><?php _e('No actors found.', 'movies-theme'); ?></p>
        <?php endif;

        echo $args['after_widget'];
    }

    /**
     * Widget Form
     */
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : __('Popular Actors', 'movies-theme');
        $number = !empty($instance['number']) ? absint($instance['number']) : 5;
        $show_photo = isset($instance['show_photo']) ? (bool) $instance['show_photo'] : true;
        $show_movie_count = isset($instance['show_movie_count']) ? (bool) $instance['show_movie_count'] : true;
        $sort_by = !empty($instance['sort_by']) ? $instance['sort_by'] : 'random';
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
            <label for="<?php echo esc_attr($this->get_field_id('sort_by')); ?>"><?php _e('Sort by:', 'movies-theme'); ?></label>
            <select class="widefat" id="<?php echo esc_attr($this->get_field_id('sort_by')); ?>" name="<?php echo esc_attr($this->get_field_name('sort_by')); ?>">
                <option value="random" <?php selected($sort_by, 'random'); ?>><?php _e('Random', 'movies-theme'); ?></option>
                <option value="popular" <?php selected($sort_by, 'popular'); ?>><?php _e('Most Popular', 'movies-theme'); ?></option>
                <option value="recent" <?php selected($sort_by, 'recent'); ?>><?php _e('Most Recent', 'movies-theme'); ?></option>
                <option value="alphabetical" <?php selected($sort_by, 'alphabetical'); ?>><?php _e('Alphabetical', 'movies-theme'); ?></option>
            </select>
        </p>
        
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_photo); ?> id="<?php echo esc_attr($this->get_field_id('show_photo')); ?>" name="<?php echo esc_attr($this->get_field_name('show_photo')); ?>">
            <label for="<?php echo esc_attr($this->get_field_id('show_photo')); ?>"><?php _e('Show actor photo', 'movies-theme'); ?></label>
        </p>
        
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_movie_count); ?> id="<?php echo esc_attr($this->get_field_id('show_movie_count')); ?>" name="<?php echo esc_attr($this->get_field_name('show_movie_count')); ?>">
            <label for="<?php echo esc_attr($this->get_field_id('show_movie_count')); ?>"><?php _e('Show movie count', 'movies-theme'); ?></label>
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
        $instance['sort_by'] = (!empty($new_instance['sort_by'])) ? sanitize_text_field($new_instance['sort_by']) : 'random';
        $instance['show_photo'] = isset($new_instance['show_photo']) ? (bool) $new_instance['show_photo'] : false;
        $instance['show_movie_count'] = isset($new_instance['show_movie_count']) ? (bool) $new_instance['show_movie_count'] : false;

        return $instance;
    }
}

// Register the widget
function register_popular_actors_widget() {
    register_widget('Popular_Actors_Widget');
}
add_action('widgets_init', 'register_popular_actors_widget'); 