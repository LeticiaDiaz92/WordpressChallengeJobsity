<?php
/**
 * Wishlist Button Component
 * 
 * @param int $movie_id - ID of the movie
 * @param string $size - Size variant: 'small', 'medium', 'large'
 * @param bool $show_text - Whether to show text or just icon
 */

// Get passed arguments
$movie_id = $args['movie_id'] ?? get_the_ID();
$size = $args['size'] ?? 'medium';
$show_text = $args['show_text'] ?? true;

if (!$movie_id || get_post_type($movie_id) !== 'movie') {
    return;
}
?>

<div class="wishlist-button-wrapper">
    <?php if (is_user_logged_in()) : ?>
        <?php 
        $is_in_wishlist = movies_is_in_wishlist($movie_id);
        $button_class = "wishlist-btn wishlist-btn--{$size}";
        if ($is_in_wishlist) {
            $button_class .= ' wishlist-btn--active';
        }
        ?>
        <button class="<?php echo esc_attr($button_class); ?>" 
                data-movie-id="<?php echo esc_attr($movie_id); ?>"
                aria-label="<?php echo $is_in_wishlist ? __('Remove from wishlist', 'movies-theme') : __('Add to wishlist', 'movies-theme'); ?>">
            <i class="fas fa-heart wishlist-icon"></i>
            <?php if ($show_text) : ?>
                <span class="wishlist-text">
                    <?php echo $is_in_wishlist ? __('Remove from Wishlist', 'movies-theme') : __('Add to Wishlist', 'movies-theme'); ?>
                </span>
            <?php endif; ?>
        </button>
    <?php else : ?>
        <button class="wishlist-btn wishlist-btn--<?php echo esc_attr($size); ?> wishlist-btn--login-required" 
                data-action="login-required"
                aria-label="<?php _e('Login required to add to wishlist', 'movies-theme'); ?>">
            <i class="fas fa-heart wishlist-icon"></i>
            <?php if ($show_text) : ?>
                <span class="wishlist-text">
                    <?php _e('Add to Wishlist', 'movies-theme'); ?>
                </span>
            <?php endif; ?>
        </button>
    <?php endif; ?>
</div> 