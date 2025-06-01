<?php
/**
 * Movie Search Widget
 */

class Movie_Search_Widget extends WP_Widget {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct(
            'movie_search_widget',
            __('Movie Search', 'movies-theme'),
            array(
                'description' => __('Advanced search for movies and actors', 'movies-theme'),
                'classname' => 'movie-search-widget'
            )
        );
    }

    /**
     * Widget Output
     */
    public function widget($args, $instance) {
        echo $args['before_widget'];

        $title = !empty($instance['title']) ? $instance['title'] : __('Search Movies & Actors', 'movies-theme');
        $show_genre_filter = isset($instance['show_genre_filter']) ? $instance['show_genre_filter'] : true;
        $show_rating_filter = isset($instance['show_rating_filter']) ? $instance['show_rating_filter'] : true;
        $show_type_filter = isset($instance['show_type_filter']) ? $instance['show_type_filter'] : true;
        $placeholder_text = !empty($instance['placeholder_text']) ? $instance['placeholder_text'] : __('Search...', 'movies-theme');

        echo $args['before_title'] . apply_filters('widget_title', $title) . $args['after_title'];
        ?>
        
        <form role="search" method="get" class="movie-search-form" action="<?php echo home_url('/'); ?>">
            <div class="search-field-wrapper">
                <input type="text" name="s" value="<?php echo get_search_query(); ?>" placeholder="<?php echo esc_attr($placeholder_text); ?>" class="search-field" autocomplete="off">
                <button type="submit" class="search-submit">
                    <span class="screen-reader-text"><?php _e('Search', 'movies-theme'); ?></span>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M21 21L16.514 16.506L21 21ZM19 10.5C19 15.194 15.194 19 10.5 19C5.806 19 2 15.194 2 10.5C2 5.806 5.806 2 10.5 2C15.194 2 19 5.806 19 10.5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
            
            <?php if ($show_type_filter): ?>
                <div class="filter-group">
                    <label for="<?php echo $this->get_field_id('search_type'); ?>" class="filter-label">
                        <?php _e('Search in:', 'movies-theme'); ?>
                    </label>
                    <select name="post_type" id="<?php echo $this->get_field_id('search_type'); ?>" class="filter-select">
                        <option value=""><?php _e('Movies & Actors', 'movies-theme'); ?></option>
                        <option value="movie" <?php selected(get_query_var('post_type'), 'movie'); ?>><?php _e('Movies Only', 'movies-theme'); ?></option>
                        <option value="actor" <?php selected(get_query_var('post_type'), 'actor'); ?>><?php _e('Actors Only', 'movies-theme'); ?></option>
                    </select>
                </div>
            <?php endif; ?>
            
            <?php if ($show_genre_filter): ?>
                <div class="filter-group">
                    <label for="<?php echo $this->get_field_id('genre_filter'); ?>" class="filter-label">
                        <?php _e('Genre:', 'movies-theme'); ?>
                    </label>
                    <select name="genre" id="<?php echo $this->get_field_id('genre_filter'); ?>" class="filter-select">
                        <option value=""><?php _e('All Genres', 'movies-theme'); ?></option>
                        <?php
                        $genres = get_terms(array(
                            'taxonomy' => 'genre',
                            'hide_empty' => true,
                            'orderby' => 'name',
                            'order' => 'ASC'
                        ));
                        foreach ($genres as $genre): ?>
                            <option value="<?php echo esc_attr($genre->slug); ?>" <?php selected(get_query_var('genre'), $genre->slug); ?>>
                                <?php echo esc_html($genre->name); ?> (<?php echo $genre->count; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>
            
            <?php if ($show_rating_filter): ?>
                <div class="filter-group">
                    <label for="<?php echo $this->get_field_id('rating_filter'); ?>" class="filter-label">
                        <?php _e('Rating:', 'movies-theme'); ?>
                    </label>
                    <select name="rating" id="<?php echo $this->get_field_id('rating_filter'); ?>" class="filter-select">
                        <option value=""><?php _e('All Ratings', 'movies-theme'); ?></option>
                        <?php
                        $ratings = get_terms(array(
                            'taxonomy' => 'rating',
                            'hide_empty' => true,
                            'orderby' => 'name',
                            'order' => 'ASC'
                        ));
                        foreach ($ratings as $rating): ?>
                            <option value="<?php echo esc_attr($rating->slug); ?>" <?php selected(get_query_var('rating'), $rating->slug); ?>>
                                <?php echo esc_html($rating->name); ?> (<?php echo $rating->count; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>
            
            <div class="search-actions">
                <button type="submit" class="search-button">
                    <?php _e('Search', 'movies-theme'); ?>
                </button>
                
                <button type="button" class="clear-filters" onclick="this.form.reset();">
                    <?php _e('Clear', 'movies-theme'); ?>
                </button>
            </div>
        </form>
        
        <div class="search-suggestions">
            <h5><?php _e('Popular Searches:', 'movies-theme'); ?></h5>
            <div class="suggestion-tags">
                <?php
                // Get popular search terms or featured genres
                $popular_genres = get_terms(array(
                    'taxonomy' => 'genre',
                    'hide_empty' => true,
                    'number' => 5,
                    'orderby' => 'count',
                    'order' => 'DESC'
                ));
                
                foreach ($popular_genres as $genre): ?>
                    <a href="<?php echo get_term_link($genre); ?>" class="suggestion-tag">
                        <?php echo esc_html($genre->name); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        
        <?php
        echo $args['after_widget'];
    }

    /**
     * Widget Form
     */
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : __('Search Movies & Actors', 'movies-theme');
        $placeholder_text = !empty($instance['placeholder_text']) ? $instance['placeholder_text'] : __('Search...', 'movies-theme');
        $show_genre_filter = isset($instance['show_genre_filter']) ? (bool) $instance['show_genre_filter'] : true;
        $show_rating_filter = isset($instance['show_rating_filter']) ? (bool) $instance['show_rating_filter'] : true;
        $show_type_filter = isset($instance['show_type_filter']) ? (bool) $instance['show_type_filter'] : true;
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php _e('Title:', 'movies-theme'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('placeholder_text')); ?>"><?php _e('Placeholder Text:', 'movies-theme'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('placeholder_text')); ?>" name="<?php echo esc_attr($this->get_field_name('placeholder_text')); ?>" type="text" value="<?php echo esc_attr($placeholder_text); ?>">
        </p>
        
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_type_filter); ?> id="<?php echo esc_attr($this->get_field_id('show_type_filter')); ?>" name="<?php echo esc_attr($this->get_field_name('show_type_filter')); ?>">
            <label for="<?php echo esc_attr($this->get_field_id('show_type_filter')); ?>"><?php _e('Show content type filter', 'movies-theme'); ?></label>
        </p>
        
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_genre_filter); ?> id="<?php echo esc_attr($this->get_field_id('show_genre_filter')); ?>" name="<?php echo esc_attr($this->get_field_name('show_genre_filter')); ?>">
            <label for="<?php echo esc_attr($this->get_field_id('show_genre_filter')); ?>"><?php _e('Show genre filter', 'movies-theme'); ?></label>
        </p>
        
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_rating_filter); ?> id="<?php echo esc_attr($this->get_field_id('show_rating_filter')); ?>" name="<?php echo esc_attr($this->get_field_name('show_rating_filter')); ?>">
            <label for="<?php echo esc_attr($this->get_field_id('show_rating_filter')); ?>"><?php _e('Show rating filter', 'movies-theme'); ?></label>
        </p>
        <?php
    }

    /**
     * Update Widget
     */
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['placeholder_text'] = (!empty($new_instance['placeholder_text'])) ? sanitize_text_field($new_instance['placeholder_text']) : __('Search...', 'movies-theme');
        $instance['show_type_filter'] = isset($new_instance['show_type_filter']) ? (bool) $new_instance['show_type_filter'] : false;
        $instance['show_genre_filter'] = isset($new_instance['show_genre_filter']) ? (bool) $new_instance['show_genre_filter'] : false;
        $instance['show_rating_filter'] = isset($new_instance['show_rating_filter']) ? (bool) $new_instance['show_rating_filter'] : false;

        return $instance;
    }
}

// Register the widget
function register_movie_search_widget() {
    register_widget('Movie_Search_Widget');
}
add_action('widgets_init', 'register_movie_search_widget'); 