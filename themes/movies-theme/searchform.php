<?php
/**
 * Custom Search Form Template
 */
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url(home_url('/')); ?>">
    <div class="search-input-group">
        <label class="screen-reader-text" for="search-field">
            <?php _e('Search for:', 'movies-theme'); ?>
        </label>
        <input 
            type="search" 
            class="search-field" 
            id="search-field"
            placeholder="<?php esc_attr_e('Search for movie or actor', 'movies-theme'); ?>" 
            value="<?php echo get_search_query(); ?>" 
            name="s" 
            autocomplete="off"
        />
        <button type="submit" class="search-submit" aria-label="<?php esc_attr_e('Submit search', 'movies-theme'); ?>">
            <span class="search-button-text"><?php _e('Find', 'movies-theme'); ?></span>
            <span class="search-icon" aria-hidden="true">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M21 21L15 15M17 10C17 13.866 13.866 17 10 17C6.13401 17 3 13.866 3 10C3 6.13401 6.13401 3 10 3C13.866 3 17 6.13401 17 10Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
        </button>
    </div>
    <div class="search-results" style="display: none;"></div>
</form> 