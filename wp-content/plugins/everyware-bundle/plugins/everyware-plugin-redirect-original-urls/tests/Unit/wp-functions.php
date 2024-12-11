<?php

/**
 * Mockups of Wordpress functions that are invoked from the tested classes.
 */

if ( ! function_exists('get_option')) {
    function get_option(...$args)
    {
    }
}
if (!function_exists('is_404')) {
    /**
     * @param bool|null $setNextResponse If set, defines what the function will return the next time(s) it is called without an argument.
     *
     * @return bool
     */
    function is_404(bool $setNextResponse=null)
    {
        static $response = false;

        if ($setNextResponse !== null) {
            $response = $setNextResponse;
        }

        return $response;
    }
}
if (!function_exists('wp_safe_redirect')) {
    function wp_safe_redirect($url, $status)
    {
        throw new RuntimeException(
            sprintf('Called wp_safe_redirect with arguments `%s` and `%d`', $url, $status)
        );
    }
}
if (!function_exists('__')) {
    function __($string)
    {
        return $string;
    }
}
