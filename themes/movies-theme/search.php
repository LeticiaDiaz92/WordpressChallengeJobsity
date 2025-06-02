<?php
/**
 * Search Results Template
 */

get_header(); ?>

<div class="search-results-page">
    <div class="container">
        
        <!-- Search Header -->
        <div class="search-header">
            <h1 class="search-title">
                <?php
                if (have_posts()) {
                    printf(__('Search Results for: %s', 'movies-theme'), '<span class="search-term">' . get_search_query() . '</span>');
                } else {
                    printf(__('No Results Found for: %s', 'movies-theme'), '<span class="search-term">' . get_search_query() . '</span>');
                }
                ?>
            </h1>
            
            <!-- Search Form -->
            <div class="search-again">
                <?php get_search_form(); ?>
            </div>
        </div>

        <?php if (have_posts()) : ?>
            
            <!-- Results Count -->
            <div class="search-meta">
                <p class="results-count">
                    <?php
                    global $wp_query;
                    $total = $wp_query->found_posts;
                    printf(
                        _n(
                            'Found %d result',
                            'Found %d results',
                            $total,
                            'movies-theme'
                        ),
                        $total
                    );
                    ?>
                </p>
            </div>
            
            <!-- Search Results -->
            <div class="search-results-grid">
                <?php while (have_posts()) : the_post(); ?>
                    
                    <div class="search-result-item <?php echo get_post_type(); ?>-result">
                        <article class="result-card">
                            
                            <!-- Result Image -->
                            <div class="result-image-container">
                                <a href="<?php the_permalink(); ?>">
                                    <?php if (has_post_thumbnail()) : ?>
                                        <?php the_post_thumbnail('medium', ['class' => 'result-image']); ?>
                                    <?php else : ?>
                                        <div class="result-no-image">
                                            <?php if (get_post_type() === 'movie') : ?>
                                                <i class="fas fa-film"></i>
                                            <?php elseif (get_post_type() === 'actor') : ?>
                                                <i class="fas fa-user"></i>
                                            <?php else : ?>
                                                <i class="fas fa-file-alt"></i>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </a>
                            </div>
                            
                            <!-- Result Content -->
                            <div class="result-content">
                                
                                <!-- Post Type Badge -->
                                <div class="result-type-badge">
                                    <?php
                                    $post_type = get_post_type();
                                    if ($post_type === 'movie') {
                                        _e('Movie', 'movies-theme');
                                    } elseif ($post_type === 'actor') {
                                        _e('Actor', 'movies-theme');
                                    } else {
                                        echo get_post_type_object($post_type)->labels->singular_name;
                                    }
                                    ?>
                                </div>
                                
                                <!-- Title -->
                                <h3 class="result-title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h3>
                                
                                <!-- Meta Information -->
                                <div class="result-meta">
                                    <?php if (get_post_type() === 'movie') : ?>
                                        <?php
                                        $release_date = get_post_meta(get_the_ID(), 'release_date', true);
                                        $genres = get_post_meta(get_the_ID(), 'genres', true);
                                        ?>
                                        
                                        <?php if ($release_date) : ?>
                                            <span class="meta-item">
                                                <i class="fas fa-calendar"></i>
                                                <?php echo date('Y', strtotime($release_date)); ?>
                                            </span>
                                        <?php endif; ?>
                                        
                                        <?php if ($genres) : ?>
                                            <?php 
                                            $genres_array = json_decode($genres, true);
                                            if ($genres_array && !empty($genres_array[0]['name'])) :
                                            ?>
                                                <span class="meta-item">
                                                    <i class="fas fa-tags"></i>
                                                    <?php echo esc_html($genres_array[0]['name']); ?>
                                                </span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        
                                    <?php elseif (get_post_type() === 'actor') : ?>
                                        <?php
                                        $birthday = get_post_meta(get_the_ID(), 'birthday', true);
                                        $known_for = get_post_meta(get_the_ID(), 'known_for_department', true);
                                        ?>
                                        
                                        <?php if ($known_for) : ?>
                                            <span class="meta-item">
                                                <i class="fas fa-star"></i>
                                                <?php echo esc_html($known_for); ?>
                                            </span>
                                        <?php endif; ?>
                                        
                                        <?php if ($birthday && !get_post_meta(get_the_ID(), 'deathday', true)) : ?>
                                            <?php $age = floor((time() - strtotime($birthday)) / 31556926); ?>
                                            <span class="meta-item">
                                                <i class="fas fa-birthday-cake"></i>
                                                <?php printf(__('%d years old', 'movies-theme'), $age); ?>
                                            </span>
                                        <?php endif; ?>
                                        
                                    <?php else : ?>
                                        <span class="meta-item">
                                            <i class="fas fa-calendar"></i>
                                            <?php echo get_the_date(); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Excerpt -->
                                <div class="result-excerpt">
                                    <?php
                                    if (has_excerpt()) {
                                        echo wp_trim_words(get_the_excerpt(), 25, '...');
                                    } else {
                                        echo wp_trim_words(get_the_content(), 25, '...');
                                    }
                                    ?>
                                </div>
                                
                                <!-- Read More Link -->
                                <div class="result-link">
                                    <a href="<?php the_permalink(); ?>" class="read-more-btn">
                                        <?php
                                        if (get_post_type() === 'movie') {
                                            _e('View Movie', 'movies-theme');
                                        } elseif (get_post_type() === 'actor') {
                                            _e('View Profile', 'movies-theme');
                                        } else {
                                            _e('Read More', 'movies-theme');
                                        }
                                        ?>
                                        <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                                
                            </div>
                        </article>
                    </div>
                    
                <?php endwhile; ?>
            </div>
            
            <!-- Pagination -->
            <div class="search-pagination">
                <?php
                the_posts_pagination(array(
                    'mid_size'  => 2,
                    'prev_text' => __('&laquo; Previous', 'movies-theme'),
                    'next_text' => __('Next &raquo;', 'movies-theme'),
                ));
                ?>
            </div>
            
        <?php else : ?>
            
            <!-- No Results -->
            <div class="no-search-results">
                <div class="no-results-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h2><?php _e('Nothing Found', 'movies-theme'); ?></h2>
                <p><?php _e('Sorry, but nothing matched your search terms. Please try again with different keywords.', 'movies-theme'); ?></p>
                
                <!-- Search Suggestions -->
                <div class="search-suggestions">
                    <h3><?php _e('Search Suggestions:', 'movies-theme'); ?></h3>
                    <ul>
                        <li><?php _e('Check your spelling', 'movies-theme'); ?></li>
                        <li><?php _e('Try broader keywords', 'movies-theme'); ?></li>
                        <li><?php _e('Try different keywords', 'movies-theme'); ?></li>
                        <li><?php _e('Search for actor names or movie titles', 'movies-theme'); ?></li>
                    </ul>
                </div>
                
                <!-- Browse Categories -->
                <div class="browse-categories">
                    <h3><?php _e('Or Browse by Category:', 'movies-theme'); ?></h3>
                    <div class="category-links">
                        <a href="<?php echo get_post_type_archive_link('movie'); ?>" class="category-btn">
                            <i class="fas fa-film"></i>
                            <?php _e('All Movies', 'movies-theme'); ?>
                        </a>
                        <a href="<?php echo get_post_type_archive_link('actor'); ?>" class="category-btn">
                            <i class="fas fa-users"></i>
                            <?php _e('All Actors', 'movies-theme'); ?>
                        </a>
                    </div>
                </div>
            </div>
            
        <?php endif; ?>
        
    </div>
</div>

<?php get_footer(); ?> 