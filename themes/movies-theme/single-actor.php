<?php
/**
 * Single Actor Template
 */

get_header(); ?>

<div class="single-actor-container">
    <?php while (have_posts()): the_post(); ?>
        <?php
        // Get actor metadata
        $tmdb_id = get_post_meta(get_the_ID(), 'tmdb_id', true);
        $birthday = get_post_meta(get_the_ID(), 'birthday', true);
        $deathday = get_post_meta(get_the_ID(), 'deathday', true);
        $place_of_birth = get_post_meta(get_the_ID(), 'place_of_birth', true);
        $popularity = get_post_meta(get_the_ID(), 'popularity', true);
        $homepage = get_post_meta(get_the_ID(), 'homepage', true);
        $gender = get_post_meta(get_the_ID(), 'gender', true);
        $known_for_department = get_post_meta(get_the_ID(), 'known_for_department', true);
        $profile_path = get_post_meta(get_the_ID(), 'profile_path', true);
        $imdb_id = get_post_meta(get_the_ID(), 'imdb_id', true);
        $also_known_as = get_post_meta(get_the_ID(), 'also_known_as', true);
        $images_json = get_post_meta(get_the_ID(), 'images', true);
        $movie_credits_json = get_post_meta(get_the_ID(), 'movie_credits', true);
        
        // Parse JSON data
        $also_known_as_array = $also_known_as ? json_decode($also_known_as, true) : [];
        $images_array = $images_json ? json_decode($images_json, true) : [];
        $movie_credits = $movie_credits_json ? json_decode($movie_credits_json, true) : [];
        
        // Gender mapping
        $gender_labels = array(
            0 => __('Not specified', 'movies-theme'),
            1 => __('Female', 'movies-theme'),
            2 => __('Male', 'movies-theme'),
            3 => __('Non-binary', 'movies-theme')
        );
        
        // Get profile image
        $profile_image = '';
        if ($profile_path) {
            $profile_image = 'https://image.tmdb.org/t/p/w500' . $profile_path;
        } else {
            // Fallback to WordPress featured image
            $profile_image = get_the_post_thumbnail_url(get_the_ID(), 'large');
        }
        
        // Get bio (post content)
        $bio = get_the_content();
        ?>

        <!-- Actor Hero Section -->
        <div class="actor-hero">
            <div class="container">
                <div class="row">
                    <div class="col-lg-4">
                        <!-- Actor Photo -->
                        <div class="actor-photo-container">
                            <?php if ($profile_image): ?>
                                <img src="<?php echo esc_url($profile_image); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" class="actor-main-photo">
                            <?php else: ?>
                                <div class="no-photo-placeholder">
                                    <i class="fas fa-user"></i>
                                    <p><?php _e('No photo available', 'movies-theme'); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="col-lg-8">
                        <!-- Actor Info -->
                        <div class="actor-main-info">
                            <h1 class="actor-name"><?php the_title(); ?></h1>
                            
                            <?php if (!empty($also_known_as_array)): ?>
                                <div class="detail-item">
                                    <strong><?php _e('Also known as:', 'movies-theme'); ?></strong>
                                    <span><?php echo esc_html(implode(', ', array_slice($also_known_as_array, 0, 3))); ?></span>
                                    <?php if (count($also_known_as_array) > 3): ?>
                                        <small><?php printf(__('and %d more', 'movies-theme'), count($also_known_as_array) - 3); ?></small>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="actor-details-grid">
                                <?php if ($birthday): ?>
                                    <div class="detail-item">
                                        <strong><?php _e('Birthday:', 'movies-theme'); ?></strong>
                                        <span>
                                            <?php 
                                            echo date_i18n('F j, Y', strtotime($birthday));
                                            if (!$deathday) {
                                                $age = floor((time() - strtotime($birthday)) / 31556926);
                                                echo ' <em>(' . sprintf(__('%d years old', 'movies-theme'), $age) . ')</em>';
                                            }
                                            ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($deathday): ?>
                                    <div class="detail-item">
                                        <strong><?php _e('Day of Death:', 'movies-theme'); ?></strong>
                                        <span>
                                            <?php 
                                            echo date_i18n('F j, Y', strtotime($deathday));
                                            if ($birthday) {
                                                $age_at_death = floor((strtotime($deathday) - strtotime($birthday)) / 31556926);
                                                echo ' <em>(' . sprintf(__('age %d', 'movies-theme'), $age_at_death) . ')</em>';
                                            }
                                            ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($place_of_birth): ?>
                                    <div class="detail-item">
                                        <strong><?php _e('Place of Birth:', 'movies-theme'); ?></strong>
                                        <span><?php echo esc_html($place_of_birth); ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($gender !== '' && isset($gender_labels[$gender])): ?>
                                    <div class="detail-item">
                                        <strong><?php _e('Gender:', 'movies-theme'); ?></strong>
                                        <span><?php echo esc_html($gender_labels[$gender]); ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($known_for_department): ?>
                                    <div class="detail-item">
                                        <strong><?php _e('Known For:', 'movies-theme'); ?></strong>
                                        <span class="department-badge"><?php echo esc_html($known_for_department); ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($popularity): ?>
                                    <div class="detail-item">
                                        <strong><?php _e('Popularity:', 'movies-theme'); ?></strong>
                                        <span><?php echo number_format((float)$popularity, 1); ?></span>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($movie_credits['cast'])): ?>
                                    <div class="detail-item">
                                        <strong><?php _e('Movies:', 'movies-theme'); ?></strong>
                                        <span><?php echo count($movie_credits['cast']); ?></span>
                                    </div>
                                    <?php endif; ?>

                                     
                                    <?php if (!empty($movie_credits['crew'])): ?>
                                    <div class="detail-item">
                                        <strong><?php _e('Crew Credits:', 'movies-theme'); ?></strong>
                                        <span><?php echo count($movie_credits['crew']); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($popularity): ?>
                                    <div class="detail-item">
                                        <strong><?php _e('Popularity:', 'movies-theme'); ?></strong>
                                        <span><?php echo number_format((float)$popularity, 1); ?></span>
                                    </div>
                                    <?php endif; ?>
                                        
                                    <?php if ($birthday && !$deathday): ?>
                                        <?php 
                                        $age = floor((time() - strtotime($birthday)) / 31556926);
                                        ?>
                                        <div class="detail-item">
                                            <strong><?php _e('Years Old:', 'movies-theme'); ?></strong>
                                            <span><?php echo $age; ?></span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($birthday && $deathday): ?>
                                        <?php 
                                        $career_start = !empty($movie_credits['cast']) ? min(array_column($movie_credits['cast'], 'release_date')) : null;
                                        $career_end = !empty($movie_credits['cast']) ? max(array_column($movie_credits['cast'], 'release_date')) : null;
                                        
                                        if ($career_start && $career_end && $career_start !== $career_end):
                                            $career_years = date('Y', strtotime($career_end)) - date('Y', strtotime($career_start));
                                        ?>
                                            <div class="detail-item">
                                                <strong><?php _e('Career Years:', 'movies-theme'); ?></strong>
                                                <span><?php echo $career_years; ?></span>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    
                                    
                            </div>
                            
                            <!-- External Links -->
                            <div class="actor-links">
                                <?php if ($homepage): ?>
                                    <a href="<?php echo esc_url($homepage); ?>" target="_blank" rel="noopener" class="actor-link website-link">
                                        <i class="fas fa-globe"></i>
                                        <?php _e('Official Website', 'movies-theme'); ?>
                                    </a>
                                <?php endif; ?>
                                
                                <?php if ($tmdb_id): ?>
                                    <a href="https://www.themoviedb.org/person/<?php echo esc_attr($tmdb_id); ?>" target="_blank" rel="noopener" class="actor-link tmdb-link">
                                        <i class="fas fa-film"></i>
                                        <?php _e('TMDB Profile', 'movies-theme'); ?>
                                    </a>
                                <?php endif; ?>
                                
                                <?php if ($imdb_id): ?>
                                    <a href="https://www.imdb.com/name/<?php echo esc_attr($imdb_id); ?>/" target="_blank" rel="noopener" class="actor-link imdb-link">
                                        <i class="fab fa-imdb"></i>
                                        <?php _e('IMDb Profile', 'movies-theme'); ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                            
                           
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container actor-content-container">
            <div class="row">
                <div class="col-12">
                    
                    <!-- Biography Section -->
                    <?php if ($bio): ?>
                        <div class="actor-section">
                            <h2 class="section-title"><?php _e('Biography', 'movies-theme'); ?></h2>
                            <div class="actor-bio">
                                <?php echo wp_kses_post($bio); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Image Gallery -->
                    <?php if (!empty($images_array)): ?>
                        <div class="actor-section">
                            <h2 class="section-title"><?php _e('Gallery', 'movies-theme'); ?></h2>
                            <div class="actor-gallery-main">
                                <?php 
                                $gallery_images = array_slice($images_array, 0, 10);
                                foreach ($gallery_images as $image): 
                                    $image_url = 'https://image.tmdb.org/t/p/w500' . $image['file_path'];
                                    $thumb_url = 'https://image.tmdb.org/t/p/w300' . $image['file_path'];
                                ?>
                                    <div class="gallery-item-main">
                                        <a href="<?php echo esc_url($image_url); ?>" class="gallery-link" data-lightbox="actor-gallery">
                                            <img src="<?php echo esc_url($thumb_url); ?>" alt="<?php echo esc_attr(get_the_title()); ?>">
                                            <div class="gallery-overlay">
                                                <i class="fas fa-search-plus"></i>
                                            </div>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="actor-section">
                            <h2 class="section-title"><?php _e('Gallery', 'movies-theme'); ?></h2>
                            <div class="no-data-message">
                                <p><i class="fas fa-images"></i> <?php _e('No additional images available for this actor.', 'movies-theme'); ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Movies Section -->
                    <?php if (!empty($movie_credits['cast']) || !empty($movie_credits['crew'])): ?>
                        <div class="actor-section">
                            <h2 class="section-title">
                                <?php 
                                $total_credits = 0;
                                if (!empty($movie_credits['cast'])) $total_credits += count($movie_credits['cast']);
                                if (!empty($movie_credits['crew'])) $total_credits += count($movie_credits['crew']);
                                printf(__('Filmography (%d)', 'movies-theme'), $total_credits); 
                                ?>
                            </h2>
                            
                            <?php if (!empty($movie_credits['cast'])): ?>
                                <div class="actor-movies-section">
                                    <h3 class="movies-subsection-title"><?php printf(__('Acting Credits (%d)', 'movies-theme'), count($movie_credits['cast'])); ?></h3>
                                    <div class="actor-movies-grid">
                                        <?php 
                                        // Sort movies by release date (most recent first)
                                        $cast_movies = $movie_credits['cast'];
                                        usort($cast_movies, function($a, $b) {
                                            $date_a = $a['release_date'] ?? '1900-01-01';
                                            $date_b = $b['release_date'] ?? '1900-01-01';
                                            return strcmp($date_b, $date_a);
                                        });
                                        
                                        foreach ($cast_movies as $movie):
                                            $movie_post = movies_find_movie_by_tmdb_id($movie['id']);
                                            $poster_url = !empty($movie['poster_path']) ? 'https://image.tmdb.org/t/p/w300' . $movie['poster_path'] : '';
                                            $release_year = !empty($movie['release_date']) ? date('Y', strtotime($movie['release_date'])) : 'TBA';
                                        ?>
                                            <div class="actor-movie-item">
                                                <?php if ($movie_post): ?>
                                                    <a href="<?php echo get_permalink($movie_post->ID); ?>" class="movie-link">
                                                <?php endif; ?>
                                                    
                                                    <div class="movie-poster">
                                                        <?php if ($poster_url): ?>
                                                            <img src="<?php echo esc_url($poster_url); ?>" alt="<?php echo esc_attr($movie['title']); ?>">
                                                        <?php else: ?>
                                                            <div class="no-poster-placeholder">
                                                                <i class="fas fa-film"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    
                                                    <div class="movie-info">
                                                        <h4 class="movie-title"><?php echo esc_html($movie['title']); ?></h4>
                                                        <?php if (!empty($movie['character'])): ?>
                                                            <p class="character-name"><?php _e('as', 'movies-theme'); ?> <em><?php echo esc_html($movie['character']); ?></em></p>
                                                        <?php endif; ?>
                                                        <p class="release-date"><?php echo esc_html($release_year); ?></p>
                                                    </div>
                                                
                                                <?php if ($movie_post): ?>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($movie_credits['crew'])): ?>
                                <div class="actor-movies-section">
                                    <h3 class="movies-subsection-title"><?php printf(__('Crew Credits (%d)', 'movies-theme'), count($movie_credits['crew'])); ?></h3>
                                    <div class="actor-movies-grid">
                                        <?php 
                                        // Sort crew movies by release date (most recent first)
                                        $crew_movies = $movie_credits['crew'];
                                        usort($crew_movies, function($a, $b) {
                                            $date_a = $a['release_date'] ?? '1900-01-01';
                                            $date_b = $b['release_date'] ?? '1900-01-01';
                                            return strcmp($date_b, $date_a);
                                        });
                                        
                                        foreach ($crew_movies as $movie):
                                            $movie_post = movies_find_movie_by_tmdb_id($movie['id']);
                                            $poster_url = !empty($movie['poster_path']) ? 'https://image.tmdb.org/t/p/w300' . $movie['poster_path'] : '';
                                            $release_year = !empty($movie['release_date']) ? date('Y', strtotime($movie['release_date'])) : 'TBA';
                                        ?>
                                            <div class="actor-movie-item">
                                                <?php if ($movie_post): ?>
                                                    <a href="<?php echo get_permalink($movie_post->ID); ?>" class="movie-link">
                                                <?php endif; ?>
                                                    
                                                    <div class="movie-poster">
                                                        <?php if ($poster_url): ?>
                                                            <img src="<?php echo esc_url($poster_url); ?>" alt="<?php echo esc_attr($movie['title']); ?>">
                                                        <?php else: ?>
                                                            <div class="no-poster-placeholder">
                                                                <i class="fas fa-film"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    
                                                    <div class="movie-info">
                                                        <h4 class="movie-title"><?php echo esc_html($movie['title']); ?></h4>
                                                        <?php if (!empty($movie['job'])): ?>
                                                            <p class="character-name"><?php echo esc_html($movie['job']); ?></p>
                                                        <?php endif; ?>
                                                        <?php if (!empty($movie['department'])): ?>
                                                            <p class="department-info"><?php echo esc_html($movie['department']); ?></p>
                                                        <?php endif; ?>
                                                        <p class="release-date"><?php echo esc_html($release_year); ?></p>
                                                    </div>
                                                
                                                <?php if ($movie_post): ?>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="actor-section">
                            <h2 class="section-title"><?php _e('Filmography', 'movies-theme'); ?></h2>
                            <div class="no-data-message">
                                <p><i class="fas fa-film"></i> <?php _e('No filmography credits available for this person.', 'movies-theme'); ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                </div>
            </div>
        </div>

    <?php endwhile; ?>
</div>

<!-- Lightbox Modal for Gallery -->
<div id="lightboxModal" class="lightbox-modal">
    <div class="lightbox-content">
        <span class="lightbox-close">&times;</span>
        <img class="lightbox-image" src="" alt="">
        <div class="lightbox-nav">
            <button class="lightbox-prev"></button>
            <button class="lightbox-next"></button>
        </div
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Enhanced lightbox functionality
    const galleryLinks = document.querySelectorAll('.gallery-link');
    const modal = document.getElementById('lightboxModal');
    const modalImg = document.querySelector('.lightbox-image');
    const closeBtn = document.querySelector('.lightbox-close');
    const prevBtn = document.querySelector('.lightbox-prev');
    const nextBtn = document.querySelector('.lightbox-next');
    const currentImageSpan = document.querySelector('.current-image');
    const totalImagesSpan = document.querySelector('.total-images');
    
    let currentImageIndex = 0;
    let images = [];
    let imageAlts = [];
    
    // Collect all gallery images
    galleryLinks.forEach((link, index) => {
        images.push(link.href);
        imageAlts.push(link.querySelector('img') ? link.querySelector('img').alt : '');
        
        link.addEventListener('click', function(e) {
            e.preventDefault();
            currentImageIndex = index;
            showLightbox(this.href, imageAlts[index]);
        });
    });
    
    // Update total images counter
    if (totalImagesSpan) {
        totalImagesSpan.textContent = images.length;
    }
    
    function showLightbox(src, alt = '') {
        modalImg.src = src;
        modalImg.alt = alt;
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden'; // Prevent scrolling
        updateCounter();
        
        // Focus management for accessibility
        modal.focus();
    }
    
    function closeLightbox() {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto'; // Restore scrolling
        modalImg.src = '';
    }
    
    function updateCounter() {
        if (currentImageSpan) {
            currentImageSpan.textContent = currentImageIndex + 1;
        }
    }
    
    function navigateImage(direction) {
        currentImageIndex += direction;
        
        // Loop around
        if (currentImageIndex < 0) {
            currentImageIndex = images.length - 1;
        }
        if (currentImageIndex >= images.length) {
            currentImageIndex = 0;
        }
        
        modalImg.src = images[currentImageIndex];
        modalImg.alt = imageAlts[currentImageIndex];
        updateCounter();
    }
    
    // Event listeners
    closeBtn.addEventListener('click', closeLightbox);
    prevBtn.addEventListener('click', () => navigateImage(-1));
    nextBtn.addEventListener('click', () => navigateImage(1));
    
    // Close modal when clicking outside the image
    modal.addEventListener('click', function(e) {
        if (e.target === modal || e.target === modal.querySelector('.lightbox-content')) {
            closeLightbox();
        }
    });
    
    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (modal.style.display === 'block') {
            switch(e.key) {
                case 'Escape':
                    closeLightbox();
                    break;
                case 'ArrowLeft':
                    navigateImage(-1);
                    break;
                case 'ArrowRight':
                    navigateImage(1);
                    break;
                case 'Home':
                    currentImageIndex = 0;
                    modalImg.src = images[currentImageIndex];
                    modalImg.alt = imageAlts[currentImageIndex];
                    updateCounter();
                    break;
                case 'End':
                    currentImageIndex = images.length - 1;
                    modalImg.src = images[currentImageIndex];
                    modalImg.alt = imageAlts[currentImageIndex];
                    updateCounter();
                    break;
            }
        }
    });
    
    // Touch/swipe support for mobile
    let startX = 0;
    let endX = 0;
    
    modalImg.addEventListener('touchstart', function(e) {
        startX = e.touches[0].clientX;
    });
    
    modalImg.addEventListener('touchend', function(e) {
        endX = e.changedTouches[0].clientX;
        handleSwipe();
    });
    
    function handleSwipe() {
        const threshold = 50; // Minimum swipe distance
        const distance = startX - endX;
        
        if (Math.abs(distance) > threshold) {
            if (distance > 0) {
                // Swipe left - next image
                navigateImage(1);
            } else {
                // Swipe right - previous image
                navigateImage(-1);
            }
        }
    }
    
    // Hide navigation buttons if only one image
    if (images.length <= 1) {
        prevBtn.style.display = 'none';
        nextBtn.style.display = 'none';
    }
});
</script>

<?php get_footer(); ?> 