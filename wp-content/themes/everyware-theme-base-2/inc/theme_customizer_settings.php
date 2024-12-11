<?php

if (!function_exists('init_customizer')) {
    function init_customizer($wp_customize)
    {
        articleCustomize($wp_customize);
        socialmediaCustomize($wp_customize);
        googleCustomize($wp_customize);
        themeCustomize($wp_customize);
        if (function_exists('cd_customizer_settings')) {
            cd_customizer_settings($wp_customize);
        }
    }
}

add_action('customize_register', 'init_customizer');


function themeCustomize($wp_customize)
{
    $wp_customize->add_setting('custom_logo', array(
        'type'       => 'option',
        'capability' => 'manage_options',
        'transport'      => 'postMessage',
    ));

    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'custom_logo', array(
        'label'         => _x('Logo', 'Admin', 'ew-base-theme-2'),
        'section'       => 'title_tagline',
        'priority'      => 8,
        'button_labels' => array(
            'select'       => _x('Select logo', 'Admin', 'ew-base-theme-2'),
            'change'       => _x('Change logo', 'Admin', 'ew-base-theme-2'),
            'remove'       => _x('Remove', 'Admin', 'ew-base-theme-2'),
            'default'      => _x('Default', 'Admin', 'ew-base-theme-2'),
            'placeholder'  => _x('No logo selected', 'Admin', 'ew-base-theme-2'),
            'frame_title'  => _x('Select logo', 'Admin', 'ew-base-theme-2'),
            'frame_button' => _x('Choose logo', 'Admin', 'ew-base-theme-2'),
        ),
    )));

    $wp_customize->selective_refresh->add_partial('custom_logo', array(
        'settings'            => array('custom_logo'),
        'selector'            => '.custom-logo-link',
        'render_callback'     => array($wp_customize, '_render_custom_logo_partial'),
        'container_inclusive' => true,
    ));

    $wp_customize->add_setting('custom_logo_nav', array(
        'type'       => 'option',
        'capability' => 'manage_options',
        'transport'      => 'postMessage',
    ));

    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'custom_logo_nav', array(
        'label'         => _x('Navigation Logo', 'Admin', 'ew-base-theme-2'),
        'section'       => 'title_tagline',
        'priority'      => 8,
        'button_labels' => array(
            'select'       => _x('Select logo', 'Admin', 'ew-base-theme-2'),
            'change'       => _x('Change logo', 'Admin', 'ew-base-theme-2'),
            'remove'       => _x('Remove', 'Admin', 'ew-base-theme-2'),
            'default'      => _x('Default', 'Admin', 'ew-base-theme-2'),
            'placeholder'  => _x('No logo selected', 'Admin', 'ew-base-theme-2'),
            'frame_title'  => _x('Select logo', 'Admin', 'ew-base-theme-2'),
            'frame_button' => _x('Choose logo', 'Admin', 'ew-base-theme-2'),
        ),
    )));
}

function googleCustomize($wp_customize)
{
    $wp_customize->add_section('google_tag', array(
        'title'    => _x('Google Tag', 'Admin', 'ew-base-theme-2'),
        'priority' => 22,
    ));

    $wp_customize->add_setting('defineSlot_1', array(
        'default'    => 'googletag.defineSlot(\'/13991....',
        'type'       => 'option',
        'capability' => 'manage_options',
    ));

    $wp_customize->add_control('defineSlot_1', array(
        'label'      => _x('defineSlot 1', 'Admin', 'ew-base-theme-2'),
        'section'    => 'google_tag',
    ));

    $wp_customize->add_setting('defineSlot_2', array(
        'default'    => 'googletag.defineSlot(\'/13991....',
        'type'       => 'option',
        'capability' => 'manage_options',
    ));

    $wp_customize->add_control('defineSlot_2', array(
        'label'      => _x('defineSlot 2', 'Admin', 'ew-base-theme-2'),
        'section'    => 'google_tag',
    ));

    $wp_customize->add_setting('defineSlot_3', array(
        'default'    => 'googletag.defineSlot(\'/13991....',
        'type'       => 'option',
        'capability' => 'manage_options',
    ));

    $wp_customize->add_control('defineSlot_3', array(
        'label'      => _x('defineSlot 3', 'Admin', 'ew-base-theme-2'),
        'section'    => 'google_tag',
    ));

    $wp_customize->add_setting('defineSlot_4', array(
        'default'    => 'googletag.defineSlot(\'/13991....',
        'type'       => 'option',
        'capability' => 'manage_options',
    ));

    $wp_customize->add_control('defineSlot_4', array(
        'label'      => _x('defineSlot 4', 'Admin', 'ew-base-theme-2'),
        'section'    => 'google_tag',
    ));

    $wp_customize->add_setting('defineSlot_5', array(
        'default'    => 'googletag.defineSlot(\'/13991....',
        'type'       => 'option',
        'capability' => 'manage_options',
    ));

    $wp_customize->add_control('defineSlot_5', array(
        'label'      => _x('defineSlot 5', 'Admin', 'ew-base-theme-2'),
        'section'    => 'google_tag',
    ));

    $wp_customize->add_setting('defineSlot_6', array(
        'default'    => 'googletag.defineSlot(\'/13991....',
        'type'       => 'option',
        'capability' => 'manage_options',
    ));

    $wp_customize->add_control('defineSlot_6', array(
        'label'      => _x('defineSlot 6', 'Admin', 'ew-base-theme-2'),
        'section'    => 'google_tag',
    ));

    $wp_customize->add_setting('defineSlot_7', array(
        'default'    => 'googletag.defineSlot(\'/13991....',
        'type'       => 'option',
        'capability' => 'manage_options',
    ));

    $wp_customize->add_control('defineSlot_7', array(
        'label'      => _x('defineSlot 7', 'Admin',  'ew-base-theme-2'),
        'section'    => 'google_tag',
    ));

    $wp_customize->add_setting('defineSlot_8', array(
        'default'    => 'googletag.defineSlot(\'/13991....',
        'type'       => 'option',
        'capability' => 'manage_options',
    ));

    $wp_customize->add_control('defineSlot_8', array(
        'label'      => _x('defineSlot 8', 'Admin', 'ew-base-theme-2'),
        'section'    => 'google_tag',
    ));

    $wp_customize->add_setting('defineSlot_9', array(
        'default'    => 'googletag.defineSlot(\'/13991....',
        'type'       => 'option',
        'capability' => 'manage_options',
    ));

    $wp_customize->add_control('defineSlot_9', array(
        'label'      => _x('defineSlot 9', 'Admin', 'ew-base-theme-2'),
        'section'    => 'google_tag',
    ));

    $wp_customize->add_setting('defineSlot_10', array(
        'default'    => 'googletag.defineSlot(\'/13991....',
        'type'       => 'option',
        'capability' => 'manage_options',
    ));

    $wp_customize->add_control('defineSlot_10', array(
        'label'      => _x('defineSlot 10', 'Admin', 'ew-base-theme-2'),
        'section'    => 'google_tag',
    ));
}

function socialmediaCustomize($wp_customize)
{
    $wp_customize->add_section('social_media', array(
        'title'    => _x('Social Media', 'Admin', 'ew-base-theme-2'),
        'priority' => 21,
    ));

    $wp_customize->add_setting('social_facebook_url', array(
        'default'    => 'https://',
        'type'       => 'option',
        'capability' => 'manage_options',
    ));

    $wp_customize->add_control('social_facebook_url', array(
        'label'      => _x('Facebook', 'Admin', 'ew-base-theme-2'),
        'section'    => 'social_media',
    ));

    $wp_customize->add_setting('social_twitter_url', array(
        'default'    => 'https://',
        'type'       => 'option',
        'capability' => 'manage_options',
    ));

    $wp_customize->add_control('social_twitter_url', array(
        'label'      => _x('Twitter', 'Admin', 'ew-base-theme-2'),
        'section'    => 'social_media',
    ));

    $wp_customize->add_setting('social_whatsapp_url', array(
        'default'    => 'https://',
        'type'       => 'option',
        'capability' => 'manage_options',
    ));

    $wp_customize->add_control('social_whatsapp_url', array(
        'label'      => _x('WhatsApp', 'Admin', 'ew-base-theme-2'),
        'section'    => 'social_media',
    ));

    $wp_customize->add_setting('social_youtube_url', array(
        'default'    => 'https://',
        'type'       => 'option',
        'capability' => 'manage_options',
    ));

    $wp_customize->add_control('social_youtube_url', array(
        'label'      => _x('YouTube', 'Admin', 'ew-base-theme-2'),
        'section'    => 'social_media',
    ));
}

function articleCustomize($wp_customize)
{
    $wp_customize->add_section('article', array(
        'title'    => _x('Article', 'Admin', 'ew-base-theme-2'),
        'priority' => 21,
        'description' => _x("You can choose what's displayed on the article page template of your site", 'Admin', 'ew-base-theme-2')
    ));
    $wp_customize->add_setting('article_author_short_desc', array(
        'default'    => false,
        'capability' => 'manage_options',
    ));
    $wp_customize->add_control('article_author_short_desc', array(
        'label'      => _x('Short description', 'Admin', 'ew-base-theme-2'),
        'section'    => 'article',
        'type'      => 'checkbox'
    ));
    $wp_customize->add_setting('article_author_facebook', array(
        'default'    => false,
        'capability' => 'manage_options',
    ));
    $wp_customize->add_control('article_author_facebook', array(
        'label'      => _x('Facebook', 'Admin', 'ew-base-theme-2'),
        'section'    => 'article',
        'type'      => 'checkbox'
    ));
    $wp_customize->add_setting('article_author_twitter', array(
        'default'    => false,
        'capability' => 'manage_options',
    ));
    $wp_customize->add_control('article_author_twitter', array(
        'label'      => _x('Twitter', 'Admin', 'ew-base-theme-2'),
        'section'    => 'article',
        'type'      => 'checkbox'
    ));
    $wp_customize->add_setting('article_author_email', array(
        'default'    => false,
        'capability' => 'manage_options',
    ));
    $wp_customize->add_control('article_author_email', array(
        'label'      => _x('E-mail', 'Admin', 'ew-base-theme-2'),
        'section'    => 'article',
        'type'      => 'checkbox'
    ));
    $wp_customize->add_setting('article_author_phone', array(
        'default'    => false,
        'capability' => 'manage_options',
    ));
    $wp_customize->add_control('article_author_phone', array(
        'label'      => _x('Phone', 'Admin', 'ew-base-theme-2'),
        'section'    => 'article',
        'type'      => 'checkbox'
    ));
    $wp_customize->add_setting('article_author_mobile', array(
        'default'    => false,
        'capability' => 'manage_options',
    ));
    $wp_customize->add_control('article_author_mobile', array(
        'label'      => _x('Mobile', 'Admin', 'ew-base-theme-2'),
        'section'    => 'article',
        'type'      => 'checkbox'
    ));
    $wp_customize->add_setting('article_author_image', array(
        'default'    => false,
        'capability' => 'manage_options',
    ));
    $wp_customize->add_control('article_author_image', array(
        'label'      => _x('Author image', 'Admin', 'ew-base-theme-2'),
        'section'    => 'article',
        'type'      => 'checkbox'
    ));
    $wp_customize->add_setting('article_author_address', array(
        'default'    => false,
        'capability' => 'manage_options',
    ));
    $wp_customize->add_control('article_author_address', array(
        'label'      => _x('Author address', 'Admin', 'ew-base-theme-2'),
        'section'    => 'article',
        'type'      => 'checkbox'
    ));
}
