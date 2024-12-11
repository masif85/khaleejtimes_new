<?php

if ( ! function_exists('wp_parse_args')) {
    function wp_parse_args(...$args)
    {
        return [];
    }
}

if ( ! function_exists('home_url')) {
    function home_url()
    {
        return 'https://example.com';
    }
}
