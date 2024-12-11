<?php

use PlaceholderTheme\Startup;
use Infomaker\Everyware\Base\ProjectStartup;

/**
 * Make sure that this theme never gets activated on the "Organisation root"
 */
add_action('wp_loaded', static function () {
    if (is_main_site()) {
        switch_theme('default-theme');
    }
});


ProjectStartup::registerThemeStartup(new Startup());
