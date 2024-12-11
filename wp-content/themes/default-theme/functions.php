<?php

/**
 * The Default Theme is no longer a "child theme".
 * Whenever this theme is active as a child theme,
 * it will reactivate itself to effectively remove its parent.
 */
add_action('wp_loaded', static function () {
    if (is_child_theme()) {
        switch_theme('default-theme');
    }
});
