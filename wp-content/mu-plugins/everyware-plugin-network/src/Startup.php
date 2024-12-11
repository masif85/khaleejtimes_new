<?php
namespace Everyware\Plugin\Network;

use Infomaker\Everyware\Base\FeedRouter;
use Infomaker\Everyware\Support\Arr;
use Infomaker\Everyware\Base\Interfaces\NetworkStartup;

class Startup implements NetworkStartup
{
    public function bootstrap(): void
    {
        $this->setupConstants();
        \add_action('init', [$this, 'onWpInit']);

        FeedRouter::init();
    }

    public function onWpInit(): void
    {
        $this->removeHeadLinks();
        $this->removeWpEmojiIcons();
        $this->updateHealthTests();
    }

    /**
     * @see   https://digwp.com/2009/06/xmlrpc-php-security/
     */
    private function removeHeadLinks(): void
    {
        remove_action('wp_head', 'rsd_link');
        remove_action('wp_head', 'wlwmanifest_link');
    }

    private function removeWpEmojiIcons(): void
    {
        // All actions related to emojis
        remove_action('admin_print_styles', 'print_emoji_styles');
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('admin_print_scripts', 'print_emoji_detection_script');
        remove_action('wp_print_styles', 'print_emoji_styles');
        remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
        remove_filter('the_content_feed', 'wp_staticize_emoji');
        remove_filter('comment_text_rss', 'wp_staticize_emoji');
    }

    private function updateHealthTests(): void
    {
        add_filter( 'site_status_tests', static function ($tests) {
            unset( $tests['async']['background_updates'],
                $tests['direct']['php_extensions'],
                $tests['direct']['plugin_version'],
                $tests['direct']['wordpress_version'] );
            return $tests;
        } );
    }

    private function setupConstants(): void
    {
        \define('EVERY_DEVICE', $_SERVER['EVERY_DEVICE'] ?? 'desktop');
        \define('EW_AUTH', Arr::get($_SERVER, 'HTTP_X_EW_AUTH', false));
    }
}
