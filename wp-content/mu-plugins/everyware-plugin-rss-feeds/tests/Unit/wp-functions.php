<?php

use Everyware\RssFeeds\OcApiHandler;
use Infomaker\Everyware\Base\Models\Page;

/**
 * Mockups of Wordpress functions that are invoked from the tested classes.
 */
if (!function_exists('get_post')) {
    function get_post($id)
    {
        if ($id === 135) {
            return new Page([
                'ancestors' => [],
                'id' => 135,
                'post_parent' => '',
                'post_title' => 'Test Page',
                'post_content' => ''
            ]);
        }
    }
}
if (!function_exists('get_option')) {
    function get_option(...$args)
    {
        if ($args[0] === OcApiHandler::$OCLIST_OPTIONS_NAME) {
            return json_encode([
                // todo verify these
                'article_relation_property' => 'Article',
                'published_property' => 'Published'
            ]);
        }

        return '';
    }
}
if (!function_exists('get_template_directory')) {
    function get_template_directory()
    {
        return dirname(__FILE__, 3) . '/foo/bar';
    }
}
if (!function_exists('get_permalink')) {
    function get_permalink($post_id)
    {
        return 'https://example.com/'.$post_id;
    }
}
if (!function_exists('stripslashes_deep')) {
    function stripslashes_deep($string)
    {
        return $string;
    }
}
if (!function_exists('__')) {
    function __($string)
    {
        return $string;
    }
}
