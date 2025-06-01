<?php
/**
 * Search Results Template
 */

get_header(); ?>

<div class="search-results">
    <div class="search-header">
        <h1 class="search-title">
            <?php if (get_search_query()): ?>
                <?php printf(__('Search Results for: %s', 'movies-theme'), '<span class="search-term">' . get_search_query() . '</span>'); ?>
            <?php else: ?>
                <?php _e('Search Results', 'movies-theme'); ?>
            <?php endif; ?>
        </h1>
        
        <div class="search-stats">
            <?php
            global $wp_query;
            $total = $wp_query->found_posts;
            if ($total > 0) {
                printf(
                    _n('%d result found', '%d results found', $total, 'movies-theme'),
                    $total
                );
            } else {
                _e('No results found', 'movies-theme');
            }
            ?>
        </div>
    </div>

    <div class="search-form-container">
        <form role="search" method="get" class="search-form" action="<?php echo home_url('/'); ?>">
            <div class="search-inputs">
                <input type="text" name="s" value="<?php echo get_search_query(); ?>" placeholder="<?php _e('Search movies and actors...', 'movies-theme'); ?>" class="search-field">
                
                <select name="post_type" class="search-type">
                    <option value=""><?php _e('All Types', 'movies-theme'); ?></option>
                    <option value="movie" <?php selected(get_query_var('post_type'), 'movie'); ?>><?php _e('Movies Only', 'movies-theme'); ?></option>
                    <option value="actor" <?php selected(get_query_var('post_type'), 'actor'); ?>><?php _e('Actors Only', 'movies-theme'); ?></option>
                </select>
                
                <button type="submit" class="search-submit"><?php _e('Search', 'movies-theme'); ?></button>
            </div>
        </form>
    </div>

    <div class="search-content">
        <?php if (have_posts()): ?>
            <div class="search-filters">
                <div class="filter-tabs">
                    <button class="filter-tab active" data-type="all"><?php _e('All Results', 'movies-theme'); ?></button>
                    <button class="filter-tab" data-type="movie"><?php _e('Movies', 'movies-theme'); ?></button>
                    <button class="filter-tab" data-type="actor"><?php _e('Actors', 'movies-theme'); ?></button>
                </div>
                
                <div class="sort-options">
                    <select id="search-sort">
                        <option value="relevance"><?php _e('Most Relevant', 'movies-theme'); ?></option>
                        <option value="date"><?php _e('Latest First', 'movies-theme'); ?></option>
                        <option value="title"><?php _e('Alphabetical', 'movies-theme'); ?></option>
                    </select>
                </div>
            </div>

            <div class="search-results-list">
                <?php while (have_posts()): the_post(); ?>
                    <article class="search-result-item <?php echo get_post_type(); ?>-result">
                        <div class="result-thumbnail">
                            <a href="<?php the_permalink(); ?>">
                                <?php if (has_post_thumbnail()): ?>
                                    <?php the_post_thumbnail('medium'); ?>
                                <?php else: ?>
                                    <div class="no-image-placeholder">
                                        <span><?php echo get_post_type() === 'movie' ? __('No Poster', 'movies-theme') : __('No Photo', 'movies-theme'); ?></span>
                                    </div>
                                <?php endif; ?>
                            </a>
                        </div>
                        
                        <div class="result-content">
                            <div class="result-meta">
                                <span class="result-type"><?php echo get_post_type() === 'movie' ? __('Movie', 'movies-theme') : __('Actor', 'movies-theme'); ?></span>
                                <span class="result-date"><?php echo get_the_date(); ?></span>
                            </div>
                            
                            <h2 class="result-title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h2>
                            
                            <div class="result-excerpt">
                                <?php if (has_excerpt()): ?>
                                    <?php the_excerpt(); ?>
                                <?php else: ?>
                                    <?php echo wp_trim_words(get_the_content(), 30, '...'); ?>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (get_post_type() === 'movie'): ?>
                                <div class="movie-meta">
                                    <?php
                                    $movie_year = get_post_meta(get_the_ID(), 'movie_year', true);
                                    $movie_rating = get_post_meta(get_the_ID(), 'movie_rating', true);
                                    ?>
                                    
                                    <?php if ($movie_year): ?>
                                        <span class="meta-year"><?php echo esc_html($movie_year); ?></span>
                                    <?php endif; ?>
                                    
                                    <?php if ($movie_rating): ?>
                                        <span class="meta-rating"><?php echo esc_html($movie_rating); ?>/10</span>
                                    <?php endif; ?>
                                    
                                    <?php
                                    $genres = get_the_terms(get_the_ID(), 'genre');
                                    if ($genres && !is_wp_error($genres)): ?>
                                        <div class="meta-genres">
                                            <?php foreach (array_slice($genres, 0, 3) as $genre): ?>
                                                <span class="genre-tag"><?php echo esc_html($genre->name); ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="result-actions">
                                <a href="<?php the_permalink(); ?>" class="read-more">
                                    <?php echo get_post_type() === 'movie' ? __('View Movie', 'movies-theme') : __('View Actor', 'movies-theme'); ?>
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>

            <div class="search-pagination">
                <?php
                the_posts_pagination(array(
                    'mid_size'  => 2,
                    'prev_text' => __('&laquo; Previous', 'movies-theme'),
                    'next_text' => __('Next &raquo;', 'movies-theme'),
                ));
                ?>
            </div>
            
        <?php else: ?>
            <div class="no-results">
                <h2><?php _e('Nothing Found', 'movies-theme'); ?></h2>
                <p><?php _e('Sorry, but nothing matched your search terms. Please try again with different keywords.', 'movies-theme'); ?></p>
                
                <div class="search-suggestions">
                    <h3><?php _e('Suggestions:', 'movies-theme'); ?></h3>
                    <ul>
                        <li><?php _e('Check your spelling', 'movies-theme'); ?></li>
                        <li><?php _e('Try different keywords', 'movies-theme'); ?></li>
                        <li><?php _e('Try more general keywords', 'movies-theme'); ?></li>
                        <li><?php _e('Try fewer keywords', 'movies-theme'); ?></li>
                    </ul>
                </div>
                
                <div class="alternative-content">
                    <div class="popular-content">
                        <h3><?php _e('Popular Movies', 'movies-theme'); ?></h3>
                        <?php
                        $popular_movies = new WP_Query(array(
                            'post_type' => 'movie',
                            'posts_per_page' => 3,
                            'meta_key' => 'movie_rating',
                            'orderby' => 'meta_value_num',
                            'order' => 'DESC'
                        ));

                        if ($popular_movies->have_posts()): ?>
                            <div class="popular-movies-grid">
                                <?php while ($popular_movies->have_posts()): $popular_movies->the_post(); ?>
                                    <?php get_template_part('template-parts/movie', 'card'); ?>
                                <?php endwhile; wp_reset_postdata(); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php get_footer(); ?> 