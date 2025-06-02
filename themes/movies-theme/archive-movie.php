<?php
/**
 * Archive template for Movies post type
 */

get_header(); ?>

<div class="archive-movies-content">
    <div class="hero-section">
        <h1><?php _e('All Movies', 'movies-theme'); ?></h1>
        <p><?php _e('Browse our complete collection of movies', 'movies-theme'); ?></p>
    </div>

    <div class="movies-archive-container" id="movies-archive-container">
        <!-- Filters Section -->
        <div class="movies-filters">
            <form method="GET" action="" class="filters-form" id="movies-filters-form">
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="movie-search"><?php _e('Search by Title:', 'movies-theme'); ?></label>
                        <input type="text" 
                               id="movie-search" 
                               name="movie_search" 
                               value="<?php echo esc_attr(get_query_var('movie_search')); ?>" 
                               placeholder="<?php _e('Enter movie title...', 'movies-theme'); ?>">
                    </div>

                    <div class="filter-group">
                        <label for="movie-year"><?php _e('Year:', 'movies-theme'); ?></label>
                        <select id="movie-year" name="movie_year">
                            <option value=""><?php _e('All Years', 'movies-theme'); ?></option>
                            <?php
                            $selected_year = get_query_var('movie_year');
                            $years = movies_get_all_years();
                            foreach ($years as $year) :
                            ?>
                                <option value="<?php echo esc_attr($year); ?>" <?php selected($selected_year, $year); ?>>
                                    <?php echo esc_html($year); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="movie-genre"><?php _e('Genre:', 'movies-theme'); ?></label>
                        <select id="movie-genre" name="movie_genre">
                            <option value=""><?php _e('All Genres', 'movies-theme'); ?></option>
                            <?php
                            $selected_genre = get_query_var('movie_genre');
                            $genres = get_terms(array(
                                'taxonomy' => 'genre',
                                'hide_empty' => true
                            ));
                            if (!is_wp_error($genres)) :
                                foreach ($genres as $genre) :
                            ?>
                                <option value="<?php echo esc_attr($genre->slug); ?>" <?php selected($selected_genre, $genre->slug); ?>>
                                    <?php echo esc_html($genre->name); ?>
                                </option>
                            <?php 
                                endforeach;
                            endif;
                            ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="movie-orderby"><?php _e('Order by:', 'movies-theme'); ?></label>
                        <select id="movie-orderby" name="orderby">
                            <?php $current_order = get_query_var('orderby', 'title'); ?>
                            <option value="title" <?php selected($current_order, 'title'); ?>><?php _e('Title A-Z', 'movies-theme'); ?></option>
                            <option value="title_desc" <?php selected($current_order, 'title_desc'); ?>><?php _e('Title Z-A', 'movies-theme'); ?></option>
                            <option value="date" <?php selected($current_order, 'date'); ?>><?php _e('Newest First', 'movies-theme'); ?></option>
                            <option value="date_asc" <?php selected($current_order, 'date_asc'); ?>><?php _e('Oldest First', 'movies-theme'); ?></option>
                            <option value="popularity" <?php selected($current_order, 'popularity'); ?>><?php _e('Most Popular', 'movies-theme'); ?></option>
                            <option value="rating" <?php selected($current_order, 'rating'); ?>><?php _e('Highest Rated', 'movies-theme'); ?></option>
                        </select>
                    </div>
                </div>
            </form>
        </div>

        <!-- Results Count -->
        <?php
        $total_movies = $wp_query->found_posts;
        $current_filters = array();
        if (get_query_var('movie_search')) $current_filters[] = __('Title', 'movies-theme');
        if (get_query_var('movie_year')) $current_filters[] = __('Year', 'movies-theme');
        if (get_query_var('movie_genre')) $current_filters[] = __('Genre', 'movies-theme');
        ?>
        <div class="results-info">
            <p class="results-count">
                <?php 
                if (!empty($current_filters)) {
                    printf(
                        _n('Found %d movie matching filters: %s', 'Found %d movies matching filters: %s', $total_movies, 'movies-theme'),
                        $total_movies,
                        implode(', ', $current_filters)
                    );
                } else {
                    printf(
                        _n('Showing %d movie', 'Showing %d movies', $total_movies, 'movies-theme'),
                        $total_movies
                    );
                }
                ?>
            </p>
        </div>

        <!-- Movies Grid Container -->
        <div id="movies-results-container">
            <?php if (have_posts()) : ?>
                <div class="movies-grid">
                    <?php while (have_posts()) : the_post(); ?>
                        <article class="movie-card-archive">
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
                                        foreach ($genres as $genre) {
                                            $genre_names[] = $genre->name;
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
                <div class="no-movies-found">
                    <h2><?php _e('No movies found', 'movies-theme'); ?></h2>
                    <p><?php _e('Try adjusting your filters or search terms.', 'movies-theme'); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add AJAX support flag for JavaScript -->
<script type="text/javascript">
    window.moviesAjaxEnabled = true;
</script>

<?php get_footer(); ?> 