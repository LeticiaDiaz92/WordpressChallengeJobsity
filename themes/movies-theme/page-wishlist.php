<?php
/**
 * Template for Wishlist Page
 */

get_header(); ?>

<div class="wishlist-page-container">
    <div class="main-banner">
        <h1><?php _e('My Wishlist', 'movies-theme'); ?></h1>
        <p><?php _e('Your saved movies collection', 'movies-theme'); ?></p>
    </div>

    <div class="container wishlist-content">
        <?php if (is_user_logged_in()) : ?>
            <?php
            $wishlist = movies_get_user_wishlist();
            if (!empty($wishlist)) :
                $wishlist_query = new WP_Query(array(
                    'post_type' => 'movie',
                    'post__in' => $wishlist,
                    'posts_per_page' => -1,
                    'orderby' => 'post__in'
                ));
                
                if ($wishlist_query->have_posts()) :
            ?>
                <div class="wishlist-stats">
                    <p class="wishlist-count-text">
                        <?php 
                        printf(
                            _n('You have %d movie in your wishlist', 'You have %d movies in your wishlist', count($wishlist), 'movies-theme'),
                            count($wishlist)
                        );
                        ?>
                    </p>
                </div>

                <div class="archive-grid wishlist-grid">
                    <?php while ($wishlist_query->have_posts()) : $wishlist_query->the_post(); ?>
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
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
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
                                
                                <div class="movie-actions">
                                    <?php 
                                    get_template_part('components/wishlist-button', null, array(
                                        'movie_id' => get_the_ID(),
                                        'size' => 'small',
                                        'show_text' => true
                                    )); 
                                    ?>
                                </div>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>
                
                <?php wp_reset_postdata(); ?>
                
            <?php else : ?>
                <div class="empty-wishlist">
                    <i class="fas fa-heart-broken empty-icon"></i>
                    <h2><?php _e('No movies in your wishlist', 'movies-theme'); ?></h2>
                    <p><?php _e('It looks like some movies were removed from the database.', 'movies-theme'); ?></p>
                    <a href="<?php echo get_post_type_archive_link('movie'); ?>" class="btn btn-primary">
                        <?php _e('Browse Movies', 'movies-theme'); ?>
                    </a>
                </div>
            <?php endif; ?>
            
            <?php else : ?>
                <div class="empty-wishlist">
                    <i class="fas fa-heart empty-icon"></i>
                    <h2><?php _e('Your wishlist is empty', 'movies-theme'); ?></h2>
                    <p><?php _e('Start adding movies to your wishlist to see them here.', 'movies-theme'); ?></p>
                    <a href="<?php echo get_post_type_archive_link('movie'); ?>" class="btn btn-primary">
                        <?php _e('Browse Movies', 'movies-theme'); ?>
                    </a>
                </div>
            <?php endif; ?>
        <?php else : ?>
            <div class="login-required">
                <i class="fas fa-user-lock empty-icon"></i>
                <h2><?php _e('Login Required', 'movies-theme'); ?></h2>
                <p><?php _e('You need to be logged in to view your wishlist.', 'movies-theme'); ?></p>
                <div class="login-actions">
                    <button class="btn btn-primary login-modal-trigger" data-modal="login">
                        <?php _e('Login', 'movies-theme'); ?>
                    </button>
                    <button class="btn btn-secondary register-modal-trigger" data-modal="register">
                        <?php _e('Create Account', 'movies-theme'); ?>
                    </button>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.wishlist-page-container {
    .wishlist-stats {
        margin-bottom: 2rem;
        padding: 1rem;
        background: var(--color-gray-100);
        border-radius: 8px;
        text-align: center;
        
        .wishlist-count-text {
            margin: 0;
            font-size: 1.125rem;
            color: var(--color-gray-700);
        }
    }
    
    .empty-wishlist,
    .login-required {
        text-align: center;
        padding: 4rem 2rem;
        
        .empty-icon {
            font-size: 4rem;
            color: var(--color-gray-400);
            margin-bottom: 2rem;
        }
        
        h2 {
            margin-bottom: 1rem;
            color: var(--color-gray-600);
        }
        
        p {
            margin-bottom: 2rem;
            color: var(--color-gray-500);
        }
        
        .login-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
    }
    
    .movie-card {
        position: relative;
        
        .movie-actions {
            margin-top: 1rem;
        }
        
        &:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
    }
}
</style>

<?php get_footer(); ?> 