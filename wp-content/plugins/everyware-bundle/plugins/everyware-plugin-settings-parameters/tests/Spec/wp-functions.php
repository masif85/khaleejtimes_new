<?php

if ( ! function_exists('get_option')) {
    function get_option(...$args)
    {
        return [
            'parameters' => [
                ['key' => 'keyvalue', 'value' => 'valuevalue'],
                ['key' => 'keyvalue', 'value' => 'valuevalue'],
                ['key' => 'keyvalue', 'value' => 'valuevalue'],
                ['key' => 'keyvalue', 'value' => 'valuevalue']
            ]
        ];
    }
}
if(!function_exists('is_admin')) {
    function is_admin() 
    {
        return true;
    }
}
if(!function_exists('update_option')) {
    function update_option(string $optionName, $value): bool
    {
        return true;
    }
}
if(!function_exists('add_action')) {
    function add_action() 
    {
        return [];
    }
}
if(!function_exists('wp_send_json')) {
    function wp_send_json(...$args)
    {
        return [];
    }
}
if(!function_exists('__')) {
    function __()
    {
        return 'Settings Parameters';
    }
}