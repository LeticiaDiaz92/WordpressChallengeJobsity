<?php
/**
 * Archive template for Actors post type
 */

get_header(); ?>

<div class="archive-actors-content">
    <div class="main-banner">
        <h1><?php _e('All Actors', 'movies-theme'); ?></h1>
        <p><?php _e('Browse our complete collection of actors', 'movies-theme'); ?></p>
    </div>

    <div class="container" id="actors-archive-container">
        <!-- Filters Section -->
        <div class="actors-filters filters-container">
            <form method="GET" action="filters-actors" class="filters-form" id="actors-filters-form">
               <div class="filters-form-actor">
                    <div class="filter-group">
                        <label for="actor-search"><?php _e('Search by Name:', 'movies-theme'); ?></label>
                        <input type="text" 
                               id="actor-search" 
                               name="actor_search" 
                               value="<?php echo esc_attr(get_query_var('actor_search')); ?>" 
                               placeholder="<?php _e('Enter actor name...', 'movies-theme'); ?>">
                    </div>

                    <div class="filter-group">
                        <label for="actor-movie"><?php _e('Featured in Movie:', 'movies-theme'); ?></label>
                        <select id="actor-movie" name="actor_movie">
                            <option value=""><?php _e('All Movies', 'movies-theme'); ?></option>
                            <?php
                            $selected_movie = get_query_var('actor_movie');
                            $movies = movies_get_movies_with_actors();
                            foreach ($movies as $movie) :
                                $movie_title = esc_html($movie->post_title);
                                if (!empty($movie->release_date)) {
                                    $year = date('Y', strtotime($movie->release_date));
                                    $movie_title .= ' (' . $year . ')';
                                }
                            ?>
                                <option value="<?php echo esc_attr($movie->ID); ?>" <?php selected($selected_movie, $movie->ID); ?>>
                                    <?php echo $movie_title; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="actor-orderby"><?php _e('Order by:', 'movies-theme'); ?></label>
                        <select id="actor-orderby" name="orderby">
                            <?php $current_order = get_query_var('orderby', 'title'); ?>
                            <option value="title" <?php selected($current_order, 'title'); ?>><?php _e('Name A-Z', 'movies-theme'); ?></option>
                            <option value="title_desc" <?php selected($current_order, 'title_desc'); ?>><?php _e('Name Z-A', 'movies-theme'); ?></option>
                            <option value="date" <?php selected($current_order, 'date'); ?>><?php _e('Newest First', 'movies-theme'); ?></option>
                            <option value="popularity" <?php selected($current_order, 'popularity'); ?>><?php _e('Most Popular', 'movies-theme'); ?></option>
                        </select>
                    </div>
                </div>
            </form>
        </div>

        <!-- Results Count -->
        <?php
        $total_actors = $wp_query->found_posts;
        $current_filters = array();
        if (get_query_var('actor_search')) $current_filters[] = __('Name', 'movies-theme');
        if (get_query_var('actor_movie')) $current_filters[] = __('Movie', 'movies-theme');
        ?>
        <div class="results-info">
            <p class="results-count">
                <?php 
                if (!empty($current_filters)) {
                    printf(
                        _n('Found %d actor matching filters: %s', 'Found %d actors matching filters: %s', $total_actors, 'movies-theme'),
                        $total_actors,
                        implode(', ', $current_filters)
                    );
                } else {
                    printf(
                        _n('Showing %d actor', 'Showing %d actors', $total_actors, 'movies-theme'),
                        $total_actors
                    );
                }
                ?>
            </p>
        </div>

        <!-- Actors Grid Container -->
        <div id="actors-results-container" class="container">
            <?php if (have_posts()) : ?>
                <div class="archive-grid" id= "actors-grid">
                    <?php while (have_posts()) : the_post(); ?>
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

                <div class="pagination-wrapper">
                    <?php
                    the_posts_pagination(array(
                        'mid_size' => 2,
                        'prev_text' => __('« Previous', 'movies-theme'),
                        'next_text' => __('Next »', 'movies-theme'),
                    ));
                    ?>
                </div>

            <?php else : ?>
                <div class="no-results-found">
                    <h2><?php _e('No actors found', 'movies-theme'); ?></h2>
                    <p><?php _e('Try adjusting your filters or search terms.', 'movies-theme'); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add AJAX support flag for JavaScript -->
<script type="text/javascript">
    window.actorsAjaxEnabled = true;
</script>

<?php get_footer(); ?> 