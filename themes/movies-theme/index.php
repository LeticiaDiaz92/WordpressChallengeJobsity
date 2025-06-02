<?php
/**
 * The main template file
 */

get_header(); ?>

<!-- Main Banner Section -->
<section class="hero-banner">
    <div class="container">
        <div class="hero-content">
            <h1><?php _e('Discover Amazing Movies & Actors', 'movies-theme'); ?></h1>
            <p><?php _e('Explore the latest releases, upcoming movies, and your favorite stars all in one place.', 'movies-theme'); ?></p>
            
            <!-- Search Form -->
            <div class="hero-search">
                <form role="search" method="get" class="search-form" action="<?php echo esc_url(home_url('/')); ?>">
                    <div class="search-input-group">
                        <input type="search" class="search-field" placeholder="<?php _e('Search movies, actors...', 'movies-theme'); ?>" value="<?php echo get_search_query(); ?>" name="s" />
                        <button type="submit" class="search-submit">
                            <span class="dashicons dashicons-search"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<div class="container">
    <div class="main-content">
        
        <!-- Upcoming Movies Section -->
        <section class="upcoming-movies-section">
            <header class="section-header">
                <h2><?php _e('Upcoming Movies', 'movies-theme'); ?></h2>
                <a href="<?php echo get_post_type_archive_link('movie'); ?>" class="view-all-btn">
                    <?php _e('View All Movies', 'movies-theme'); ?>
                </a>
            </header>
            
            <?php
            $upcoming_movies_grouped = movies_get_upcoming_movies(8);
            if (!empty($upcoming_movies_grouped)): ?>
                <div class="upcoming-movies-grid">
                    <?php foreach ($upcoming_movies_grouped as $month_year => $movies): ?>
                        <div class="month-group">
                            <h3 class="month-title"><?php echo esc_html($month_year); ?></h3>
                            <div class="movies-grid">
                                <?php foreach (array_slice($movies, 0, 4) as $movie): ?>
                                    <article class="movie-card">
                                        <?php if (has_post_thumbnail($movie->ID)): ?>
                                            <div class="movie-poster">
                                                <a href="<?php echo get_permalink($movie->ID); ?>">
                                                    <?php echo get_the_post_thumbnail($movie->ID, 'medium'); ?>
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="movie-info">
                                            <h4 class="movie-title">
                                                <a href="<?php echo get_permalink($movie->ID); ?>">
                                                    <?php echo esc_html($movie->post_title); ?>
                                                </a>
                                            </h4>
                                            
                                            <?php 
                                            $release_date = get_post_meta($movie->ID, 'release_date', true);
                                            if ($release_date): ?>
                                                <div class="release-date">
                                                    <?php echo date_i18n('M j, Y', strtotime($release_date)); ?>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php 
                                            $genres = get_the_terms($movie->ID, 'genre');
                                            if ($genres && !is_wp_error($genres)): ?>
                                                <div class="movie-genres">
                                                    <?php foreach (array_slice($genres, 0, 2) as $genre): ?>
                                                        <span class="genre-tag"><?php echo esc_html($genre->name); ?></span>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="no-content"><?php _e('No upcoming movies found.', 'movies-theme'); ?></p>
            <?php endif; ?>
        </section>

        <!-- Popular Actors Section -->
        <section class="popular-actors-section">
            <header class="section-header">
                <h2><?php _e('Popular Actors', 'movies-theme'); ?></h2>
                <a href="<?php echo get_post_type_archive_link('actor'); ?>" class="view-all-btn">
                    <?php _e('View All Actors', 'movies-theme'); ?>
                </a>
            </header>
            
            <?php
            $popular_actors = movies_get_popular_actors(10);
            if (!empty($popular_actors)): ?>
                <div class="actors-grid">
                    <?php foreach ($popular_actors as $actor): ?>
                        <article class="actor-card">
                            <?php if (has_post_thumbnail($actor->ID)): ?>
                                <div class="actor-photo">
                                    <a href="<?php echo get_permalink($actor->ID); ?>">
                                        <?php echo get_the_post_thumbnail($actor->ID, 'medium'); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <div class="actor-info">
                                <h4 class="actor-name">
                                    <a href="<?php echo get_permalink($actor->ID); ?>">
                                        <?php echo esc_html($actor->post_title); ?>
                                    </a>
                                </h4>
                                
                                <?php 
                                $birth_place = get_post_meta($actor->ID, 'place_of_birth', true);
                                if ($birth_place): ?>
                                    <div class="actor-location">
                                        <?php echo esc_html($birth_place); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php 
                                $popularity = get_post_meta($actor->ID, 'popularity', true);
                                if ($popularity): ?>
                                    <div class="actor-popularity">
                                        <span class="popularity-score"><?php echo number_format((float)$popularity, 1); ?></span>
                                        <span class="popularity-label"><?php _e('popularity', 'movies-theme'); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                    </article>
                    <?php endforeach; ?>
            </div>
            <?php else: ?>
                <p class="no-content"><?php _e('No popular actors found.', 'movies-theme'); ?></p>
            <?php endif; ?>
        </section>

        <!-- Popular Movies Section -->
        <section class="popular-movies-section">
            <header class="section-header">
                <h2><?php _e('Popular Movies', 'movies-theme'); ?></h2>
                <a href="<?php echo add_query_arg('orderby', 'popular', get_post_type_archive_link('movie')); ?>" class="view-all-btn">
                    <?php _e('View All Popular', 'movies-theme'); ?>
                </a>
            </header>
            
            <?php
            $popular_movies = movies_get_popular_movies(12);
            if (!empty($popular_movies)): ?>
                <div class="movies-grid popular-grid">
                    <?php foreach ($popular_movies as $movie): ?>
                        <article class="movie-card">
                            <?php if (has_post_thumbnail($movie->ID)): ?>
                                <div class="movie-poster">
                                    <a href="<?php echo get_permalink($movie->ID); ?>">
                                        <?php echo get_the_post_thumbnail($movie->ID, 'medium'); ?>
                                    </a>
                                    
                                    <?php 
                                    $vote_average = get_post_meta($movie->ID, 'vote_average', true);
                                    if ($vote_average): ?>
                                        <div class="movie-rating">
                                            <span class="rating-star">★</span>
                                            <span class="rating-value"><?php echo number_format((float)$vote_average, 1); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="movie-info">
                                <h4 class="movie-title">
                                    <a href="<?php echo get_permalink($movie->ID); ?>">
                                        <?php echo esc_html($movie->post_title); ?>
                                    </a>
                                </h4>
                                
                                <?php 
                                $release_date = get_post_meta($movie->ID, 'release_date', true);
                                if ($release_date): ?>
                                    <div class="release-year">
                                        <?php echo date('Y', strtotime($release_date)); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php 
                                $genres = get_the_terms($movie->ID, 'genre');
                                if ($genres && !is_wp_error($genres)): ?>
                                    <div class="movie-genres">
                                        <?php foreach (array_slice($genres, 0, 2) as $genre): ?>
                                            <span class="genre-tag"><?php echo esc_html($genre->name); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="no-content"><?php _e('No popular movies found.', 'movies-theme'); ?></p>
            <?php endif; ?>
        </section>

        <!-- Site Stats -->
        <section class="site-stats-section">
            <div class="stats-grid">
                <?php
                // Get counts
                $movie_count = wp_count_posts('movie')->publish ?? 0;
                $actor_count = wp_count_posts('actor')->publish ?? 0;
                $genre_count = wp_count_terms(array('taxonomy' => 'genre')) ?? 0;
                ?>
                
                <div class="stat-item">
                    <span class="stat-number"><?php echo number_format($movie_count); ?></span>
                    <span class="stat-label"><?php _e('Movies', 'movies-theme'); ?></span>
                </div>
                
                <div class="stat-item">
                    <span class="stat-number"><?php echo number_format($actor_count); ?></span>
                    <span class="stat-label"><?php _e('Actors', 'movies-theme'); ?></span>
                </div>
                
                <div class="stat-item">
                    <span class="stat-number"><?php echo number_format($genre_count); ?></span>
                    <span class="stat-label"><?php _e('Genres', 'movies-theme'); ?></span>
                </div>
                
                <div class="stat-item">
                    <span class="stat-number"><?php echo date('Y'); ?></span>
                    <span class="stat-label"><?php _e('Current Year', 'movies-theme'); ?></span>
                </div>
                </div>
            </section>
            
    </div>
</div>

<?php get_footer(); ?> 