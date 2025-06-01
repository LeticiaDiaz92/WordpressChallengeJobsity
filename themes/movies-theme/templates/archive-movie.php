<?php
/**
 * Archive template for Movies
 */

get_header(); ?>

<div class="archive-movies">
    <div class="archive-header">
        <h1 class="archive-title">
            <?php
            if (is_category()) {
                single_cat_title();
            } elseif (is_tag()) {
                single_tag_title();
            } elseif (is_tax()) {
                single_term_title();
            } else {
                _e('Movies Archive', 'movies-theme');
            }
            ?>
        </h1>
        
        <?php if (is_tax('genre')): ?>
            <div class="archive-description">
                <?php echo term_description(); ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="archive-filters">
        <div class="filter-bar">
            <div class="results-count">
                <?php
                global $wp_query;
                $total = $wp_query->found_posts;
                printf(
                    _n('%d movie found', '%d movies found', $total, 'movies-theme'),
                    $total
                );
                ?>
            </div>
            
            <div class="sort-filter">
                <select id="archive-sort" onchange="location = this.value;">
                    <option value="<?php echo add_query_arg('orderby', 'date'); ?>" <?php selected(get_query_var('orderby'), 'date'); ?>>
                        <?php _e('Latest First', 'movies-theme'); ?>
                    </option>
                    <option value="<?php echo add_query_arg('orderby', 'title'); ?>" <?php selected(get_query_var('orderby'), 'title'); ?>>
                        <?php _e('Alphabetical', 'movies-theme'); ?>
                    </option>
                    <option value="<?php echo add_query_arg('orderby', 'meta_value_num'); ?>" <?php selected(get_query_var('orderby'), 'meta_value_num'); ?>>
                        <?php _e('Highest Rated', 'movies-theme'); ?>
                    </option>
                </select>
            </div>
        </div>
    </div>

    <div class="archive-content">
        <?php if (have_posts()): ?>
            <div class="movies-grid">
                <?php while (have_posts()): the_post(); ?>
                    <?php get_template_part('template-parts/movie', 'card'); ?>
                <?php endwhile; ?>
            </div>

            <div class="archive-pagination">
                <?php
                the_posts_pagination(array(
                    'mid_size'  => 2,
                    'prev_text' => __('&laquo; Previous', 'movies-theme'),
                    'next_text' => __('Next &raquo;', 'movies-theme'),
                ));
                ?>
            </div>
            
        <?php else: ?>
            <div class="no-movies-found">
                <h2><?php _e('No movies found', 'movies-theme'); ?></h2>
                <p><?php _e('Sorry, no movies were found in this category.', 'movies-theme'); ?></p>
                <a href="<?php echo get_post_type_archive_link('movie'); ?>" class="btn btn-primary">
                    <?php _e('View All Movies', 'movies-theme'); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>

    <div class="archive-sidebar">
        <div class="widget-area">
            <div class="widget">
                <h3 class="widget-title"><?php _e('Browse by Genre', 'movies-theme'); ?></h3>
                <ul class="genre-list">
                    <?php
                    $genres = get_terms(array(
                        'taxonomy' => 'genre',
                        'hide_empty' => true,
                    ));
                    foreach ($genres as $genre): ?>
                        <li>
                            <a href="<?php echo get_term_link($genre); ?>">
                                <?php echo esc_html($genre->name); ?>
                                <span class="count">(<?php echo $genre->count; ?>)</span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="widget">
                <h3 class="widget-title"><?php _e('Browse by Rating', 'movies-theme'); ?></h3>
                <ul class="rating-list">
                    <?php
                    $ratings = get_terms(array(
                        'taxonomy' => 'rating',
                        'hide_empty' => true,
                    ));
                    foreach ($ratings as $rating): ?>
                        <li>
                            <a href="<?php echo get_term_link($rating); ?>">
                                <?php echo esc_html($rating->name); ?>
                                <span class="count">(<?php echo $rating->count; ?>)</span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?> 