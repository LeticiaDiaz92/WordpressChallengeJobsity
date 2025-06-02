<?php
/**
 * Single Movie Template
 */

get_header(); ?>

<div class="single-movie-container">
    <?php while (have_posts()): the_post(); ?>
        <?php
        // Debug: Let's see what metadata we have available
        $all_meta = get_post_meta(get_the_ID());
        $tmdb_id = get_post_meta(get_the_ID(), 'tmdb_id', true);
        $movie_data = get_post_meta(get_the_ID(), 'movie_data', true);

        // Get movie metadata
        $movie_data = is_string($movie_data) ? json_decode($movie_data, true) : $movie_data;
        
        // If movie_data doesn't exist, try to get data from individual meta fields
        if (empty($movie_data)) {
            $movie_data = array(
                'poster_path' => get_post_meta(get_the_ID(), 'poster_path', true),
                'backdrop_path' => get_post_meta(get_the_ID(), 'backdrop_path', true),
                'release_date' => get_post_meta(get_the_ID(), 'release_date', true),
                'runtime' => get_post_meta(get_the_ID(), 'runtime', true),
                'vote_average' => get_post_meta(get_the_ID(), 'vote_average', true),
                'popularity' => get_post_meta(get_the_ID(), 'popularity', true),
                'original_language' => get_post_meta(get_the_ID(), 'original_language', true),
                'overview' => get_post_meta(get_the_ID(), 'overview', true),
                'budget' => get_post_meta(get_the_ID(), 'budget', true),
                'revenue' => get_post_meta(get_the_ID(), 'revenue', true),
                'homepage' => get_post_meta(get_the_ID(), 'homepage', true),
                'imdb_id' => get_post_meta(get_the_ID(), 'imdb_id', true),
                'tagline' => get_post_meta(get_the_ID(), 'tagline', true),
                'status' => get_post_meta(get_the_ID(), 'status', true),
                'vote_count' => get_post_meta(get_the_ID(), 'vote_count', true)
            );
            
            // Get JSON fields
            $genres_json = get_post_meta(get_the_ID(), 'genres', true);
            $movie_data['genres'] = $genres_json ? json_decode($genres_json, true) : [];
            
            $production_companies_json = get_post_meta(get_the_ID(), 'production_companies', true);
            $movie_data['production_companies'] = $production_companies_json ? json_decode($production_companies_json, true) : [];
            
            $videos_json = get_post_meta(get_the_ID(), 'videos', true);
            $videos_data = $videos_json ? json_decode($videos_json, true) : [];
            $movie_data['videos'] = array('results' => $videos_data);
            
            $credits_json = get_post_meta(get_the_ID(), 'credits', true);
            $movie_data['credits'] = $credits_json ? json_decode($credits_json, true) : [];
            
            $reviews_json = get_post_meta(get_the_ID(), 'reviews', true);
            $reviews_data = $reviews_json ? json_decode($reviews_json, true) : [];
            $movie_data['reviews'] = array('results' => is_array($reviews_data) ? $reviews_data : []);
            
            $similar_json = get_post_meta(get_the_ID(), 'similar', true);
            $similar_data = $similar_json ? json_decode($similar_json, true) : [];
            $movie_data['similar'] = array('results' => is_array($similar_data) ? $similar_data : []);
            
            $alternative_titles_json = get_post_meta(get_the_ID(), 'alternative_titles', true);
            $alternative_titles_data = $alternative_titles_json ? json_decode($alternative_titles_json, true) : [];
            $movie_data['alternative_titles'] = array('titles' => is_array($alternative_titles_data) ? $alternative_titles_data : []);
        }
        
        // Extract movie details
        $poster_path = !empty($movie_data['poster_path']) ? 'https://image.tmdb.org/t/p/w500' . $movie_data['poster_path'] : '';
        $backdrop_path = !empty($movie_data['backdrop_path']) ? 'https://image.tmdb.org/t/p/w1280' . $movie_data['backdrop_path'] : '';
        
    
        $release_date = !empty($movie_data['release_date']) ? $movie_data['release_date'] : '';
        
        // Add fallback for release_date if empty
        if (empty($release_date)) {
            // Try individual meta field
            $release_date = get_post_meta(get_the_ID(), 'release_date', true);
            
            // If still empty, use WordPress post date as final fallback
            if (empty($release_date)) {
                $release_date = get_the_date('Y-m-d');
            }
        }
        
        $runtime = !empty($movie_data['runtime']) ? $movie_data['runtime'] : '';
        $vote_average = !empty($movie_data['vote_average']) ? $movie_data['vote_average'] : '';
        $popularity = !empty($movie_data['popularity']) ? $movie_data['popularity'] : '';
        $original_language = !empty($movie_data['original_language']) ? $movie_data['original_language'] : '';
        $overview = !empty($movie_data['overview']) ? $movie_data['overview'] : get_the_content();
        
        // Add fallbacks for other important fields
        if (empty($runtime)) {
            $runtime = get_post_meta(get_the_ID(), 'runtime', true);
        }
        
        if (empty($vote_average)) {
            $vote_average = get_post_meta(get_the_ID(), 'vote_average', true);
        }
        
        if (empty($popularity)) {
            $popularity = get_post_meta(get_the_ID(), 'popularity', true);
        }
        
        if (empty($original_language)) {
            $original_language = get_post_meta(get_the_ID(), 'original_language', true);
        }
        
        if (empty($overview)) {
            // If still empty after get_the_content(), try meta field
            $overview_meta = get_post_meta(get_the_ID(), 'overview', true);
            if (!empty($overview_meta)) {
                $overview = $overview_meta;
            }
        }
        
        $genres = !empty($movie_data['genres']) ? $movie_data['genres'] : [];
        $production_companies = !empty($movie_data['production_companies']) ? $movie_data['production_companies'] : [];
        $videos = !empty($movie_data['videos']['results']) ? $movie_data['videos']['results'] : [];
        $credits = !empty($movie_data['credits']) ? $movie_data['credits'] : [];
        $reviews = !empty($movie_data['reviews']['results']) ? $movie_data['reviews']['results'] : [];
        $similar_movies = !empty($movie_data['similar']['results']) ? $movie_data['similar']['results'] : [];
        $alternative_titles = !empty($movie_data['alternative_titles']['titles']) ? $movie_data['alternative_titles']['titles'] : [];
        
        // Find trailer
        $trailer_key = '';
        foreach ($videos as $video) {
            if ($video['type'] === 'Trailer' && $video['site'] === 'YouTube') {
                $trailer_key = $video['key'];
                break;
            }
        }

        // Determine background image: backdrop first, then poster, then featured image as fallback
        $hero_bg = '';
        if ($backdrop_path) {
            $hero_bg = $backdrop_path;
        } elseif ($poster_path) {
            $hero_bg = $poster_path;
        } else {
            // Try featured image as final fallback
            $featured_image_id = get_post_thumbnail_id(get_the_ID());
            if ($featured_image_id) {
                $hero_bg = wp_get_attachment_image_url($featured_image_id, 'full');
            }
        }
        ?>

        <!-- Movie Hero Section -->
        <div class="movie-hero" <?php if ($hero_bg): ?>style="background-image: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('<?php echo esc_url($hero_bg); ?>');"<?php endif; ?>>
            <div class="container">
                <div class="movie-hero-content">

                    <div class="movie-info">
                        <h1 class="movie-title"><?php the_title(); ?></h1>
                        
                        <?php if ($release_date): ?>
                            <div class="movie-year">(<?php echo date('Y', strtotime($release_date)); ?>)</div>
                        <?php endif; ?>
                        
                        <div class="movie-meta-row">
                            <?php if ($release_date): ?>
                                <span class="meta-item">
                                    <i class="far fa-calendar"></i>
                                    <?php echo date('F j, Y', strtotime($release_date)); ?>
                                </span>
                            <?php endif; ?>
                            
                            <?php if ($runtime): ?>
                                <span class="meta-item">
                                    <i class="far fa-clock"></i>
                                    <?php echo esc_html($runtime); ?> min
                                </span>
                            <?php endif; ?>
                            
                            <?php if ($vote_average): ?>
                                <span class="meta-item rating">
                                    <i class="fas fa-star"></i>
                                    <?php echo number_format($vote_average, 1); ?>/10
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!empty($genres)): ?>
                            <div class="movie-genres">
                                <?php foreach ($genres as $genre): ?>
                                    <span class="genre-tag"><?php echo esc_html($genre['name']); ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($overview): ?>
                            <div class="movie-overview">
                                <h3><?php _e('Overview', 'movies-theme'); ?></h3>
                                <p><?php echo wp_kses_post($overview); ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($trailer_key): ?>
                            <div class="movie-actions">
                                <button class="btn btn-primary play-trailer" data-trailer="<?php echo esc_attr($trailer_key); ?>">
                                    <i class="fas fa-play"></i>
                                    <?php _e('Watch Trailer', 'movies-theme'); ?>
                                </button>
                            </div>
                        <?php else: ?>
                            <div class="movie-actions">
                                <button class="btn btn-secondary btn-disabled" disabled>
                                    <i class="fas fa-video-slash"></i>
                                    <?php _e('Trailer Not Available', 'movies-theme'); ?>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="container movie-details-container">
            <div class="row">
                <div class="col-lg-8">
                    
                    <!-- Movie Details Section -->
                    <div class="movie-section">
                        <h2 class="section-title"><?php _e('Movie Details', 'movies-theme'); ?></h2>
                        
                        <div class="movie-details-grid">
                            <?php if ($original_language): ?>
                                <div class="detail-item">
                                    <strong><?php _e('Original Language:', 'movies-theme'); ?></strong>
                                    <span><?php echo esc_html(strtoupper($original_language)); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($popularity): ?>
                                <div class="detail-item">
                                    <strong><?php _e('Popularity:', 'movies-theme'); ?></strong>
                                    <span><?php echo number_format($popularity, 1); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($movie_data['budget'])): ?>
                                <div class="detail-item">
                                    <strong><?php _e('Budget:', 'movies-theme'); ?></strong>
                                    <span>$<?php echo number_format($movie_data['budget']); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($movie_data['revenue'])): ?>
                                <div class="detail-item">
                                    <strong><?php _e('Revenue:', 'movies-theme'); ?></strong>
                                    <span>$<?php echo number_format($movie_data['revenue']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!empty($production_companies)): ?>
                            <div class="production-companies-section">
                                <h4><?php _e('Production Companies', 'movies-theme'); ?></h4>
                                <div class="production-companies">
                                    <?php foreach ($production_companies as $company): ?>
                                        <div class="company-item">
                                            <?php if (!empty($company['logo_path'])): ?>
                                                <img src="https://image.tmdb.org/t/p/w200<?php echo esc_attr($company['logo_path']); ?>" 
                                                     alt="<?php echo esc_attr($company['name']); ?>" class="company-logo">
                                            <?php endif; ?>
                                            <span class="company-name"><?php echo esc_html($company['name']); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Alternative Titles -->
                    <div class="movie-section">
                        <h2 class="section-title"><?php _e('Alternative Titles', 'movies-theme'); ?></h2>
                        <?php if (!empty($alternative_titles)): ?>
                            <div class="alternative-titles">
                                <?php foreach (array_slice($alternative_titles, 0, 10) as $title): ?>
                                    <div class="alt-title-item">
                                        <strong><?php echo esc_html($title['title']); ?></strong>
                                        <span class="country">(<?php echo esc_html($title['iso_3166_1']); ?>)</span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="no-data-message">
                                <p><i class="fas fa-info-circle"></i> <?php _e('No alternative titles available for this movie.', 'movies-theme'); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Cast Section -->
                    <div class="movie-section">
                        <h2 class="section-title"><?php _e('Cast', 'movies-theme'); ?></h2>
                        <?php if (!empty($credits['cast'])): ?>
                            <div class="cast-grid">
                                <?php foreach (array_slice($credits['cast'], 0, 12) as $cast_member): ?>
                                    <div class="cast-member">
                                        <?php
                                        $actor_post = movies_find_actor_by_tmdb_id($cast_member['id']);
                                        $profile_path = !empty($cast_member['profile_path']) ? 'https://image.tmdb.org/t/p/w185' . $cast_member['profile_path'] : '';
                                        
                                        // Create various profile links
                                        $tmdb_actor_url = '';
                                        $imdb_search_url = '';
                                        
                                        if (!empty($cast_member['id'])) {
                                            // TMDB profile link
                                            $tmdb_actor_url = 'https://www.themoviedb.org/person/' . $cast_member['id'];
                                            
                                            // IMDb search link (using actor name as fallback)
                                            $actor_name_encoded = urlencode($cast_member['name']);
                                            $imdb_search_url = 'https://www.imdb.com/find?q=' . $actor_name_encoded . '&s=nm';
                                        }
                                        ?>
                                        
                                        <div class="cast-photo">
                                            <?php if ($profile_path): ?>
                                                <img src="<?php echo esc_url($profile_path); ?>" alt="<?php echo esc_attr($cast_member['name']); ?>">
                                            <?php else: ?>
                                                <div class="no-photo-placeholder">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="cast-info">
                                            <h4 class="actor-name"><?php echo esc_html($cast_member['name']); ?></h4>
                                            <p class="character-name"><?php echo esc_html($cast_member['character']); ?></p>
                                            
                                            <div class="cast-links">
                                                <?php if ($actor_post): ?>
                                                    <a href="<?php echo get_permalink($actor_post->ID); ?>" class="cast-link local-link">
                                                        <i class="fas fa-user-circle"></i>
                                                        <?php _e('View Profile', 'movies-theme'); ?>
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($tmdb_actor_url)): ?>
                                                    <a href="<?php echo esc_url($tmdb_actor_url); ?>" class="cast-link tmdb-link" target="_blank" rel="noopener">
                                                        <i class="fas fa-film"></i>
                                                        <?php _e('TMDB', 'movies-theme'); ?>
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($imdb_search_url)): ?>
                                                    <a href="<?php echo esc_url($imdb_search_url); ?>" class="cast-link imdb-link" target="_blank" rel="noopener">
                                                        <i class="fab fa-imdb"></i>
                                                        <?php _e('IMDb', 'movies-theme'); ?>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="no-data-message">
                                <p><i class="fas fa-users"></i> <?php _e('Cast information is not available for this movie.', 'movies-theme'); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Reviews Section -->
                    <div class="movie-section">
                        <h2 class="section-title"><?php _e('Reviews', 'movies-theme'); ?></h2>
                        <?php if (!empty($reviews)): ?>
                            <div class="reviews-container">
                                <?php foreach (array_slice($reviews, 0, 3) as $review): ?>
                                    <div class="review-item">
                                        <div class="review-header">
                                            <h4 class="reviewer-name"><?php echo esc_html($review['author']); ?></h4>
                                            <?php if (!empty($review['author_details']['rating'])): ?>
                                                <div class="review-rating">
                                                    <i class="fas fa-star"></i>
                                                    <?php echo esc_html($review['author_details']['rating']); ?>/10
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="review-content">
                                            <?php echo wp_trim_words(wp_kses_post($review['content']), 50); ?>
                                        </div>
                                        <div class="review-date">
                                            <?php echo date('F j, Y', strtotime($review['created_at'])); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="no-data-message">
                                <p><i class="fas fa-comment-alt"></i> <?php _e('No reviews available for this movie yet. Be the first to write one!', 'movies-theme'); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Similar Movies -->
                    <div class="movie-section">
                        <h2 class="section-title"><?php _e('Similar Movies', 'movies-theme'); ?></h2>
                        <?php if (!empty($similar_movies)): ?>
                            <div class="similar-movies-grid">
                                <?php foreach (array_slice($similar_movies, 0, 8) as $similar_movie): ?>
                                    <?php
                                    $movie_post = movies_find_movie_by_tmdb_id($similar_movie['id']);
                                    $similar_poster = !empty($similar_movie['poster_path']) ? 'https://image.tmdb.org/t/p/w300' . $similar_movie['poster_path'] : '';
                                    ?>
                                    <div class="similar-movie-item">
                                        <?php if ($movie_post): ?>
                                            <a href="<?php echo get_permalink($movie_post->ID); ?>" class="similar-movie-link">
                                        <?php endif; ?>
                                            
                                            <div class="similar-movie-poster">
                                                <?php if ($similar_poster): ?>
                                                    <img src="<?php echo esc_url($similar_poster); ?>" alt="<?php echo esc_attr($similar_movie['title']); ?>">
                                                <?php else: ?>
                                                    <div class="no-poster-placeholder">
                                                        <i class="fas fa-film"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div class="similar-movie-info">
                                                <h4><?php echo esc_html($similar_movie['title']); ?></h4>
                                                <?php if (!empty($similar_movie['release_date'])): ?>
                                                    <p class="release-year">(<?php echo date('Y', strtotime($similar_movie['release_date'])); ?>)</p>
                                                <?php endif; ?>
                                                <?php if (!empty($similar_movie['vote_average'])): ?>
                                                    <div class="movie-rating">
                                                        <i class="fas fa-star"></i>
                                                        <?php echo number_format($similar_movie['vote_average'], 1); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        
                                        <?php if ($movie_post): ?>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="no-data-message">
                                <p><i class="fas fa-film"></i> <?php _e('No similar movies found for this title.', 'movies-theme'); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                </div>

                <div class="col-lg-4">
                    <!-- Sidebar -->
                    <div class="movie-sidebar">
                        
                        <!-- Trailer Section -->
                        <div class="sidebar-section">
                            <h3><?php _e('Trailer', 'movies-theme'); ?></h3>
                            <?php if ($trailer_key): ?>
                                <div class="trailer-container">
                                    <div class="trailer-placeholder" data-trailer="<?php echo esc_attr($trailer_key); ?>">
                                        <img src="https://img.youtube.com/vi/<?php echo esc_attr($trailer_key); ?>/mqdefault.jpg" alt="<?php _e('Play Trailer', 'movies-theme'); ?>">
                                        <div class="play-button">
                                            <i class="fas fa-play"></i>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="no-trailer-message">
                                    <div class="no-trailer-placeholder">
                                        <i class="fas fa-video-slash"></i>
                                        <p><?php _e('No trailer available for this movie', 'movies-theme'); ?></p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Quick Facts -->
                        <div class="sidebar-section">
                            <h3><?php _e('Quick Facts', 'movies-theme'); ?></h3>
                            <div class="quick-facts">
                                
                                <?php if ($tmdb_id): ?>
                                    <div class="fact-item">
                                        <strong><?php _e('TMDB ID:', 'movies-theme'); ?></strong>
                                        <a href="https://www.themoviedb.org/movie/<?php echo esc_attr($tmdb_id); ?>" target="_blank" rel="noopener">
                                            <?php echo esc_html($tmdb_id); ?>
                                        </a>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($movie_data['imdb_id'])): ?>
                                    <div class="fact-item">
                                        <strong><?php _e('IMDb:', 'movies-theme'); ?></strong>
                                        <a href="https://www.imdb.com/title/<?php echo esc_attr($movie_data['imdb_id']); ?>/" target="_blank" rel="noopener">
                                            <?php echo esc_html($movie_data['imdb_id']); ?>
                                        </a>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($movie_data['homepage'])): ?>
                                    <div class="fact-item">
                                        <strong><?php _e('Official Website:', 'movies-theme'); ?></strong>
                                        <a href="<?php echo esc_url($movie_data['homepage']); ?>" target="_blank" rel="noopener">
                                            <?php _e('Visit Website', 'movies-theme'); ?>
                                        </a>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($vote_average): ?>
                                    <div class="fact-item">
                                        <strong><?php _e('User Rating:', 'movies-theme'); ?></strong>
                                        <div class="rating-display">
                                            <span class="rating-number"><?php echo number_format($vote_average, 1); ?>/10</span>
                                            <div class="star-rating">
                                                <?php
                                                $stars = round($vote_average / 2);
                                                for ($i = 1; $i <= 5; $i++) {
                                                    if ($i <= $stars) {
                                                        echo '<i class="fas fa-star"></i>';
                                                    } else {
                                                        echo '<i class="far fa-star"></i>';
                                                    }
                                                }
                                                ?>
                                            </div>
                                            <?php if (!empty($movie_data['vote_count'])): ?>
                                                <span class="vote-count">(<?php echo number_format($movie_data['vote_count']); ?> votes)</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($movie_data['status'])): ?>
                                    <div class="fact-item">
                                        <strong><?php _e('Status:', 'movies-theme'); ?></strong>
                                        <span class="status-badge status-<?php echo esc_attr(strtolower(str_replace(' ', '-', $movie_data['status']))); ?>">
                                            <?php echo esc_html($movie_data['status']); ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($movie_data['tagline'])): ?>
                                    <div class="fact-item">
                                        <strong><?php _e('Tagline:', 'movies-theme'); ?></strong>
                                        <em>"<?php echo esc_html($movie_data['tagline']); ?>"</em>
                                    </div>
                                <?php endif; ?>
                                
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>

    <?php endwhile; ?>
</div>

<!-- Trailer Modal -->
<div id="trailerModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <div class="modal-body">
            <div id="trailerEmbed"></div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Trailer functionality
    const trailerButtons = document.querySelectorAll('.play-trailer, .trailer-placeholder');
    const modal = document.getElementById('trailerModal');
    const closeBtn = document.querySelector('.close');
    const trailerEmbed = document.getElementById('trailerEmbed');
    
    trailerButtons.forEach(button => {
        button.addEventListener('click', function() {
            const trailerKey = this.getAttribute('data-trailer');
            if (trailerKey) {
                const iframe = document.createElement('iframe');
                iframe.src = `https://www.youtube.com/embed/${trailerKey}?autoplay=1`;
                iframe.frameBorder = '0';
                iframe.allowFullscreen = true;
                iframe.allow = 'autoplay; encrypted-media';
                
                trailerEmbed.innerHTML = '';
                trailerEmbed.appendChild(iframe);
                modal.style.display = 'block';
            }
        });
    });
    
    // Close modal
    function closeModal() {
        modal.style.display = 'none';
        trailerEmbed.innerHTML = '';
    }
    
    closeBtn.addEventListener('click', closeModal);
    
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            closeModal();
        }
    });
    
    // Escape key to close modal
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && modal.style.display === 'block') {
            closeModal();
        }
    });
});
</script>

<?php get_footer(); ?>   