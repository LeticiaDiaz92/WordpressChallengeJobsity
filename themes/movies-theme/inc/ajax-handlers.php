<?php
/**
 * AJAX Handlers for Movies Theme
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * AJAX handler for movie filters
 */
function movies_ajax_filter_movies() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'movies_nonce')) {
        wp_die('Security check failed');
    }
    
    // Get filter parameters
    $search = sanitize_text_field($_POST['movie_search'] ?? '');
    $year = intval($_POST['movie_year'] ?? 0);
    $genre = sanitize_text_field($_POST['movie_genre'] ?? '');
    $orderby = sanitize_text_field($_POST['orderby'] ?? 'title');
    $paged = intval($_POST['paged'] ?? 1);
    
    // Build query args
    $args = array(
        'post_type' => 'movie',
        'posts_per_page' => 12,
        'paged' => $paged,
        'post_status' => 'publish'
    );
    
    // Handle search
    if (!empty($search)) {
        $args['s'] = $search;
    }
    
    // Handle ordering
    switch ($orderby) {
        case 'title':
            $args['orderby'] = 'title';
            $args['order'] = 'ASC';
            break;
        case 'title_desc':
            $args['orderby'] = 'title';
            $args['order'] = 'DESC';
            break;
        case 'date':
            $args['orderby'] = 'date';
            $args['order'] = 'DESC';
            break;
        case 'date_asc':
            $args['orderby'] = 'date';
            $args['order'] = 'ASC';
            break;
        case 'popularity':
            $args['orderby'] = 'meta_value_num';
            $args['meta_key'] = 'popularity';
            $args['order'] = 'DESC';
            break;
        case 'rating':
            $args['orderby'] = 'meta_value_num';
            $args['meta_key'] = 'vote_average';
            $args['order'] = 'DESC';
            break;
    }
    
    // Handle genre filter
    if (!empty($genre)) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'genre',
                'field'    => 'slug',
                'terms'    => $genre,
            ),
        );
    }
    
    // Handle year filter
    if (!empty($year)) {
        $args['meta_query'] = array(
            array(
                'key'     => 'release_date',
                'value'   => array($year . '-01-01', $year . '-12-31'),
                'compare' => 'BETWEEN',
                'type'    => 'DATE'
            )
        );
    }
    
    // Execute query
    $query = new WP_Query($args);
    
    $response = array();
    
    if ($query->have_posts()) {
        ob_start();
        ?>
        <div class="archive-grid" id= "movies-grid">
            <?php while ($query->have_posts()) : $query->the_post(); ?>
                <article class="movie-card">
                    <?php if (has_post_thumbnail()) : ?>
                        <div class="movie-poster">
                            <a href="<?php the_permalink(); ?>">
                                <?php the_post_thumbnail('medium', array('alt' => get_the_title())); ?>
                            </a>
                        </div>
                    <?php else : ?>
                        <div class="movie-poster no-poster">
                            <div class="no-poster-placeholder">
                                <span><?php _e('No Poster', 'movies-theme'); ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="movie-info">
                        <h3 class="movie-title">
                            <a href="<?php the_permalink(); ?>">
                                <?php the_title(); ?>
                            </a>
                        </h3>
                        
                        <?php 
                        $release_date = get_post_meta(get_the_ID(), 'release_date', true);
                        if ($release_date) : ?>
                            <div class="release-date">
                                <strong><?php _e('Release Date:', 'movies-theme'); ?></strong>
                                <?php echo date_i18n('F j, Y', strtotime($release_date)); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php
                        $genres = get_the_terms(get_the_ID(), 'genre');
                        if ($genres && !is_wp_error($genres)) : ?>
                            <div class="movie-genres">
                                <strong><?php _e('Genres:', 'movies-theme'); ?></strong>
                                <?php 
                                $genre_names = array();
                                foreach ($genres as $genre_item) {
                                    $genre_names[] = $genre_item->name;
                                }
                                echo esc_html(implode(', ', $genre_names));
                                ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php 
                        $vote_average = get_post_meta(get_the_ID(), 'vote_average', true);
                        if ($vote_average) : ?>
                            <div class="movie-rating">
                                <strong><?php _e('Rating:', 'movies-theme'); ?></strong>
                                <span class="stars">★ <?php echo number_format((float)$vote_average, 1); ?>/10</span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (has_excerpt()) : ?>
                            <div class="movie-excerpt">
                                <?php the_excerpt(); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>
        <?php
        $movies_html = ob_get_clean();
        
        // Generate pagination
        ob_start();
        $pagination = paginate_links(array(
            'base' => add_query_arg('paged', '%#%'),
            'total' => $query->max_num_pages,
            'current' => $paged,
            'format' => '',
            'prev_text' => __('« Previous', 'movies-theme'),
            'next_text' => __('Next »', 'movies-theme'),
            'type' => 'array',
            'add_args' => false
        ));
        
        if ($pagination) {
            echo '<div class="pagination-wrapper pagination"><div class="nav-links page-numbers-wrapper">';
            foreach ($pagination as $page) {
                echo $page;
            }
            echo '</div></div>';
        }
        $pagination_html = ob_get_clean();
        
        $response['success'] = true;
        $response['html'] = $movies_html;
        $response['pagination'] = $pagination_html;
        $response['found_posts'] = $query->found_posts;
        $response['total_pages'] = $query->max_num_pages;
        
    } else {
        ob_start();
        ?>
        <div class="no-movies-found">
            <h2><?php _e('No movies found', 'movies-theme'); ?></h2>
            <p><?php _e('Try adjusting your filters or search terms.', 'movies-theme'); ?></p>
        </div>
        <?php
        $response['success'] = true;
        $response['html'] = ob_get_clean();
        $response['pagination'] = '';
        $response['found_posts'] = 0;
        $response['total_pages'] = 0;
    }
    
    wp_reset_postdata();
    wp_send_json($response);
}
add_action('wp_ajax_filter_movies', 'movies_ajax_filter_movies');
add_action('wp_ajax_nopriv_filter_movies', 'movies_ajax_filter_movies');

/**
 * AJAX handler for searching movies (for autocomplete)
 */
function movies_ajax_search() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'movies_nonce')) {
        wp_die('Security check failed');
    }
    
    $search_term = sanitize_text_field($_POST['search_term'] ?? '');
    
    if (strlen($search_term) < 3) {
        wp_send_json_error('Search term too short');
    }
    
    $movies = get_posts(array(
        'post_type' => 'movie',
        'posts_per_page' => 10,
        's' => $search_term,
        'orderby' => 'relevance'
    ));
    
    $results = array();
    foreach ($movies as $movie) {
        $results[] = array(
            'id' => $movie->ID,
            'title' => $movie->post_title,
            'url' => get_permalink($movie->ID),
            'poster' => get_the_post_thumbnail_url($movie->ID, 'thumbnail')
        );
    }
    
    wp_send_json_success($results);
}
add_action('wp_ajax_search_movies', 'movies_ajax_search');
add_action('wp_ajax_nopriv_search_movies', 'movies_ajax_search');

// Handle wishlist actions
function movies_handle_add_to_wishlist() {
    check_ajax_referer('movies_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Please log in to add movies to wishlist.'));
    }
    
    $movie_id = intval($_POST['movie_id']);
    $user_id = get_current_user_id();
    
    // Add to wishlist logic here
    
    wp_send_json_success(array('message' => 'Movie added to wishlist!'));
}
add_action('wp_ajax_add_to_wishlist', 'movies_handle_add_to_wishlist');

// Handle live search
function movies_handle_live_search() {
    check_ajax_referer('movies_nonce', 'nonce');
    
    $query = sanitize_text_field($_POST['query']);
    
    $args = array(
        'post_type' => array('movie', 'actor'),
        'posts_per_page' => 5,
        's' => $query,
        'post_status' => 'publish'
    );
    
    $search_query = new WP_Query($args);
    $results = array();
    
    if ($search_query->have_posts()) {
        while ($search_query->have_posts()) {
            $search_query->the_post();
            $results[] = array(
                'title' => get_the_title(),
                'url' => get_permalink(),
                'excerpt' => get_the_excerpt(),
                'thumbnail' => get_the_post_thumbnail_url(get_the_ID(), 'thumbnail'),
                'type' => get_post_type()
            );
        }
        wp_reset_postdata();
    }
    
    wp_send_json_success(array('results' => $results));
}
add_action('wp_ajax_live_search', 'movies_handle_live_search');
add_action('wp_ajax_nopriv_live_search', 'movies_handle_live_search');

/**
 * AJAX handler for actor filters
 */
function actors_ajax_filter_actors() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'actors_nonce')) {
        wp_die('Security check failed');
    }
    
    // Get filter parameters
    $search = sanitize_text_field($_POST['actor_search'] ?? '');
    $movie_id = intval($_POST['actor_movie'] ?? 0);
    $orderby = sanitize_text_field($_POST['orderby'] ?? 'title');
    $paged = intval($_POST['paged'] ?? 1);
    
    // Build query args
    $args = array(
        'post_type' => 'actor',
        'posts_per_page' => 12,
        'paged' => $paged,
        'post_status' => 'publish'
    );
    
    // Handle search
    if (!empty($search)) {
        $args['s'] = $search;
    }
    
    // Handle ordering
    switch ($orderby) {
        case 'title':
            $args['orderby'] = 'title';
            $args['order'] = 'ASC';
            break;
        case 'title_desc':
            $args['orderby'] = 'title';
            $args['order'] = 'DESC';
            break;
        case 'date':
            $args['orderby'] = 'date';
            $args['order'] = 'DESC';
            break;
        case 'popularity':
            $args['orderby'] = 'meta_value_num';
            $args['meta_key'] = 'popularity';
            $args['order'] = 'DESC';
            break;
    }
    
    // Handle movie filter - search actors who appear in the specified movie
    if (!empty($movie_id)) {
        global $wpdb;
        
        // Get actors associated with the movie (search in both cast and crew sections)
        // Note: $movie_id is now the TMDB movie ID, not WordPress post ID
        $actor_ids = $wpdb->get_col($wpdb->prepare("
            SELECT DISTINCT pm.post_id 
            FROM {$wpdb->postmeta} pm 
            INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
            WHERE pm.meta_key = 'movie_credits' 
            AND p.post_type = 'actor'
            AND p.post_status = 'publish'
            AND (
                (pm.meta_value LIKE %s AND pm.meta_value LIKE %s) OR
                (pm.meta_value LIKE %s AND pm.meta_value LIKE %s)
            )
        ", 
        '%"cast":%', '%"id":' . $movie_id . '%',
        '%"crew":%', '%"id":' . $movie_id . '%'
        ));
        
        if (!empty($actor_ids)) {
            $args['post__in'] = $actor_ids;
        } else {
            // No actors found for this movie
            $args['post__in'] = array(0);
        }
    }
    
    // Execute query
    $query = new WP_Query($args);
    
    $response = array();
    
    if ($query->have_posts()) {
        ob_start();
        ?>
        <div class="archive-grid" id= "actors-grid">
            <?php while ($query->have_posts()) : $query->the_post(); ?>
                <article class="actor-card">
                    <?php if (has_post_thumbnail()) : ?>
                        <div class="actor-photo">
                            <a href="<?php the_permalink(); ?>">
                                <?php the_post_thumbnail('medium', array('alt' => get_the_title())); ?>
                            </a>
                        </div>
                    <?php else : ?>
                        <div class="actor-photo no-photo">
                            <div class="no-photo-placeholder">
                                <span><?php _e('No Photo', 'movies-theme'); ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="actor-info">
                        <h3 class="actor-name">
                            <a href="<?php the_permalink(); ?>">
                                <?php the_title(); ?>
                            </a>
                        </h3>
                        
                        <?php 
                        $birthday = get_post_meta(get_the_ID(), 'birthday', true);
                        if ($birthday) : ?>
                            <div class="actor-birthday">
                                <strong><?php _e('Born:', 'movies-theme'); ?></strong>
                                <?php echo date_i18n('F j, Y', strtotime($birthday)); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php 
                        $place_of_birth = get_post_meta(get_the_ID(), 'place_of_birth', true);
                        if ($place_of_birth) : ?>
                            <div class="actor-birthplace">
                                <strong><?php _e('Birthplace:', 'movies-theme'); ?></strong>
                                <?php echo esc_html($place_of_birth); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php 
                        $popularity = get_post_meta(get_the_ID(), 'popularity', true);
                        if ($popularity) : ?>
                            <div class="actor-popularity">
                                <strong><?php _e('Popularity:', 'movies-theme'); ?></strong>
                                <span class="popularity-score"><?php echo number_format((float)$popularity, 1); ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (has_excerpt()) : ?>
                            <div class="actor-excerpt">
                                <?php the_excerpt(); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>
        <?php
        $actors_html = ob_get_clean();
        
        // Generate pagination
        ob_start();
        $pagination = paginate_links(array(
            'base' => add_query_arg('paged', '%#%'),
            'total' => $query->max_num_pages,
            'current' => $paged,
            'format' => '',
            'prev_text' => __('« Previous', 'movies-theme'),
            'next_text' => __('Next »', 'movies-theme'),
            'type' => 'array',
            'add_args' => false
        ));
        
        if ($pagination) {
            echo '<div class="pagination-wrapper pagination"><div class="nav-links page-numbers-wrapper">';
            foreach ($pagination as $page) {
                echo $page;
            }
            echo '</div></div>';
        }
        $pagination_html = ob_get_clean();
        
        $response['success'] = true;
        $response['html'] = $actors_html;
        $response['pagination'] = $pagination_html;
        $response['found_posts'] = $query->found_posts;
        $response['total_pages'] = $query->max_num_pages;
        
    } else {
        ob_start();
        ?>
        <div class="no-results-found">
            <h2><?php _e('No actors found', 'movies-theme'); ?></h2>
            <p><?php _e('Try adjusting your filters or search terms.', 'movies-theme'); ?></p>
        </div>
        <?php
        $response['success'] = true;
        $response['html'] = ob_get_clean();
        $response['pagination'] = '';
        $response['found_posts'] = 0;
        $response['total_pages'] = 0;
    }
    
    wp_reset_postdata();
    wp_send_json($response);
}

// Register AJAX actions for actors
add_action('wp_ajax_filter_actors', 'actors_ajax_filter_actors');
add_action('wp_ajax_nopriv_filter_actors', 'actors_ajax_filter_actors'); 