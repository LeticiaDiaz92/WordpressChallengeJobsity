<?php
/**
 * The sidebar containing the main widget area
 */

if (!is_active_sidebar('sidebar-1')) {
    return;
}
?>

<aside id="secondary" class="widget-area">
    <div class="sidebar-widgets">
        <?php dynamic_sidebar('sidebar-1'); ?>
    </div>
</aside> 