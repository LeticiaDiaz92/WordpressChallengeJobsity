<?php
/**
 * Archive template for Actors
 */

get_header(); ?>

<div class="archive-actors">
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
                _e('Actors Archive', 'movies-theme');
            }
            ?>
        </h1>
        
        <div class="archive-description">
            <p><?php _e('Browse through our collection of talented actors and discover their filmographies.', 'movies-theme'); ?></p>
        </div>
    </div>

    <div class="archive-filters">
        <div class="filter-bar">
            <div class="results-count">
                <?php
                global $wp_query;
                $total = $wp_query->found_posts;
                printf(
                    _n('%d actor found', '%d actors found', $total, 'movies-theme'),
                    $total
                );
                ?>
            </div>
            
            <div class="search-filter">
                <form role="search" method="get" action="<?php echo home_url('/'); ?>">
                    <input type="text" name="s" value="<?php echo get_search_query(); ?>" placeholder="<?php _e('Search actors...', 'movies-theme'); ?>">
                    <input type="hidden" name="post_type" value="actor">
                    <button type="submit"><?php _e('Search', 'movies-theme'); ?></button>
                </form>
            </div>
            
            <div class="sort-filter">
                <select id="archive-sort" onchange="location = this.value;">
                    <option value="<?php echo add_query_arg('orderby', 'date'); ?>" <?php selected(get_query_var('orderby'), 'date'); ?>>
                        <?php _e('Latest First', 'movies-theme'); ?>
                    </option>
                    <option value="<?php echo add_query_arg('orderby', 'title'); ?>" <?php selected(get_query_var('orderby'), 'title'); ?>>
                        <?php _e('Alphabetical', 'movies-theme'); ?>
                    </option>
                    <option value="<?php echo add_query_arg('orderby', 'meta_value'); ?>" <?php selected(get_query_var('orderby'), 'meta_value'); ?>>
                        <?php _e('Most Popular', 'movies-theme'); ?>
                    </option>
                </select>
            </div>
        </div>
    </div>

    <div class="archive-content">
        <?php if (have_posts()): ?>
            <div class="actors-grid">
                <?php while (have_posts()): the_post(); ?>
                    <?php get_template_part('template-parts/actor', 'card'); ?>
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
            <div class="no-actors-found">
                <h2><?php _e('No actors found', 'movies-theme'); ?></h2>
                <p><?php _e('Sorry, no actors were found matching your criteria.', 'movies-theme'); ?></p>
                <a href="<?php echo get_post_type_archive_link('actor'); ?>" class="btn btn-primary">
                    <?php _e('View All Actors', 'movies-theme'); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>

    <div class="archive-sidebar">
        <div class="widget-area">
            <div class="widget">
                <h3 class="widget-title"><?php _e('Browse Alphabetically', 'movies-theme'); ?></h3>
                <div class="alphabet-filter">
                    <?php
                    $letters = range('A', 'Z');
                    foreach ($letters as $letter): ?>
                        <a href="<?php echo add_query_arg('letter', $letter); ?>" class="letter-link">
                            <?php echo $letter; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="widget">
                <h3 class="widget-title"><?php _e('Popular Actors', 'movies-theme'); ?></h3>
                <?php
                $popular_actors = new WP_Query(array(
                    'post_type' => 'actor',
                    'posts_per_page' => 5,
                    'meta_key' => 'actor_popularity',
                    'orderby' => 'meta_value_num',
                    'order' => 'DESC'
                ));

                if ($popular_actors->have_posts()): ?>
                    <ul class="popular-actors-list">
                        <?php while ($popular_actors->have_posts()): $popular_actors->the_post(); ?>
                            <li>
                                <a href="<?php the_permalink(); ?>">
                                    <?php if (has_post_thumbnail()): ?>
                                        <?php the_post_thumbnail('thumbnail'); ?>
                                    <?php endif; ?>
                                    <span><?php the_title(); ?></span>
                                </a>
                            </li>
                        <?php endwhile; wp_reset_postdata(); ?>
                    </ul>
                <?php endif; ?>
            </div>

            <div class="widget">
                <h3 class="widget-title"><?php _e('Featured Movies', 'movies-theme'); ?></h3>
                <?php
                $featured_movies = new WP_Query(array(
                    'post_type' => 'movie',
                    'posts_per_page' => 3,
                    'meta_key' => 'featured_movie',
                    'meta_value' => '1'
                ));

                if ($featured_movies->have_posts()): ?>
                    <ul class="featured-movies-list">
                        <?php while ($featured_movies->have_posts()): $featured_movies->the_post(); ?>
                            <li>
                                <a href="<?php the_permalink(); ?>">
                                    <?php if (has_post_thumbnail()): ?>
                                        <?php the_post_thumbnail('thumbnail'); ?>
                                    <?php endif; ?>
                                    <span><?php the_title(); ?></span>
                                </a>
                            </li>
                        <?php endwhile; wp_reset_postdata(); ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?> 