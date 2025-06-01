<?php
/**
 * Template Name: Movies Page
 */

get_header(); ?>

<div class="movies-page">
    <div class="page-header">
        <h1><?php _e('Movies', 'movies-theme'); ?></h1>
        <?php if (get_the_content()): ?>
            <div class="page-description">
                <?php the_content(); ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="movies-filters">
        <div class="filter-bar">
            <div class="search-filter">
                <input type="text" id="movie-search" placeholder="<?php _e('Search movies...', 'movies-theme'); ?>">
            </div>
            
            <div class="genre-filter">
                <select id="genre-filter">
                    <option value=""><?php _e('All Genres', 'movies-theme'); ?></option>
                    <?php
                    $genres = get_terms(array(
                        'taxonomy' => 'genre',
                        'hide_empty' => true,
                    ));
                    foreach ($genres as $genre): ?>
                        <option value="<?php echo esc_attr($genre->slug); ?>"><?php echo esc_html($genre->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="rating-filter">
                <select id="rating-filter">
                    <option value=""><?php _e('All Ratings', 'movies-theme'); ?></option>
                    <?php
                    $ratings = get_terms(array(
                        'taxonomy' => 'rating',
                        'hide_empty' => true,
                    ));
                    foreach ($ratings as $rating): ?>
                        <option value="<?php echo esc_attr($rating->slug); ?>"><?php echo esc_html($rating->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="sort-filter">
                <select id="sort-filter">
                    <option value="date"><?php _e('Latest First', 'movies-theme'); ?></option>
                    <option value="title"><?php _e('Alphabetical', 'movies-theme'); ?></option>
                    <option value="rating"><?php _e('Highest Rated', 'movies-theme'); ?></option>
                </select>
            </div>
        </div>
    </div>

    <div class="movies-grid" id="movies-container">
        <?php
        $movies_query = new WP_Query(array(
            'post_type' => 'movie',
            'posts_per_page' => 12,
            'post_status' => 'publish'
        ));

        if ($movies_query->have_posts()):
            while ($movies_query->have_posts()): $movies_query->the_post();
                get_template_part('template-parts/movie', 'card');
            endwhile;
            wp_reset_postdata();
        else: ?>
            <div class="no-movies">
                <p><?php _e('No movies found.', 'movies-theme'); ?></p>
            </div>
        <?php endif; ?>
    </div>

    <div class="pagination">
        <?php
        echo paginate_links(array(
            'total' => $movies_query->max_num_pages,
            'prev_text' => __('&laquo; Previous', 'movies-theme'),
            'next_text' => __('Next &raquo;', 'movies-theme'),
        ));
        ?>
    </div>
</div>

<?php get_footer(); ?> 