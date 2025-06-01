<?php
/**
 * Template Name: Actors Page
 */

get_header(); ?>

<div class="actors-page">
    <div class="page-header">
        <h1><?php _e('Actors', 'movies-theme'); ?></h1>
        <?php if (get_the_content()): ?>
            <div class="page-description">
                <?php the_content(); ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="actors-filters">
        <div class="filter-bar">
            <div class="search-filter">
                <input type="text" id="actor-search" placeholder="<?php _e('Search actors...', 'movies-theme'); ?>">
            </div>
            
            <div class="sort-filter">
                <select id="sort-filter">
                    <option value="date"><?php _e('Latest First', 'movies-theme'); ?></option>
                    <option value="title"><?php _e('Alphabetical', 'movies-theme'); ?></option>
                    <option value="popular"><?php _e('Most Popular', 'movies-theme'); ?></option>
                </select>
            </div>
        </div>
    </div>

    <div class="actors-grid" id="actors-container">
        <?php
        $actors_query = new WP_Query(array(
            'post_type' => 'actor',
            'posts_per_page' => 12,
            'post_status' => 'publish'
        ));

        if ($actors_query->have_posts()):
            while ($actors_query->have_posts()): $actors_query->the_post();
                get_template_part('template-parts/actor', 'card');
            endwhile;
            wp_reset_postdata();
        else: ?>
            <div class="no-actors">
                <p><?php _e('No actors found.', 'movies-theme'); ?></p>
            </div>
        <?php endif; ?>
    </div>

    <div class="pagination">
        <?php
        echo paginate_links(array(
            'total' => $actors_query->max_num_pages,
            'prev_text' => __('&laquo; Previous', 'movies-theme'),
            'next_text' => __('Next &raquo;', 'movies-theme'),
        ));
        ?>
    </div>
</div>

<?php get_footer(); ?> 