<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */

if ( ! function_exists( 'everyware_theme_base_1_customize_register' ) ) {
	/**
	 * Register basic customizer support.
	 *
	 * @param object $wp_customize Customizer reference.
	 */
    /* Custom Appearance Functions */
    function everyware_theme_base_1_customize_register( $wp_customize )
    {

        /* --------------------------------------------------------------------------------------------------------- */
        /*                                            SITE IDENTITY SETTINGS                                         */
        /* --------------------------------------------------------------------------------------------------------- */

        $wp_customize->add_setting( 'favicon_image', array(
            'default'           => '',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Image_Control(
                $wp_customize,
                'favicon_image', array(
                'label'       => __( 'Favicon Image', 'everyware-theme-base-1' ),
                'description' => __( 'Choose the image to be used as the favicon for the site.'),
                'section'     => 'title_tagline',
                'settings'    => 'favicon_image',
            )
        ) );

        /* ---------------------------------------- Header Logo Position ------------------------------------------- */

        $wp_customize->add_setting( 'logo_position', array(
            'default'           => '',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
            'description'       => 'help',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'logo_position', array(
                    'label'       => __( ('Header Logo Position'), 'everyware-theme-base-1' ),
                    'description' => __( 'Set logo\'s default position. Can either be: right, left, center. NOTE: Opposed to this setting, you are able to use Widgets within the header area by turning on the "Header Widgets" setting following to "On".'),
                    'section'     => 'title_tagline',
                    'settings'    => 'logo_position',
                    'type'        => 'select',
                    'choices'     => array(
                        'right' => __( 'Right', 'everyware-theme-base-1' ),
                        'left'  => __( 'Left', 'everyware-theme-base-1' ),
                        'center'  => __( 'Center', 'everyware-theme-base-1' ),
                    ),
                )
            ) );

        /* ----------------------------------------- Header Logo Width ------------------------------------------ */

        $wp_customize->add_setting( 'header_logo_size_width', array(
            'default'           => '440px',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'header_logo_size_width', array(
                    'label'       => __( 'Header Logo Size - Width', 'everyware-theme-base-1' ),
                    'description' => __( 'Choose the width in pixels of the header logo. Ex. "440px" '),
                    'section'     => 'title_tagline',
                    'settings'    => 'header_logo_size_width',
                )
            ) );

        /* -------------------------------------- Move Logo to Top Bar on Mobile ------------------------------- */

        //Removing until more stable setting. KAO - 20190829
        /*$wp_customize->add_setting( 'logo_top_bar_mobile', array(
            'default'           => 'header',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'logo_top_bar_mobile', array(
                    'label'       => __( 'Put Logo to Top Bar on Mobile', 'everyware-theme-base-1' ),
                    'description' => __( 'Choose to have logo be in the Top Bar on Mobile or keep within Header. If you would like a custom Top Bar logo, choose the image in the following field.'),
                    'section'     => 'title_tagline',
                    'settings'    => 'logo_top_bar_mobile',
                    'type'        => 'select',
                    'choices'     => array(
                        'header' => __( 'Keep in Header', 'everyware-theme-base-1' ),
                        'top_bar'  => __( 'Top Bar', 'everyware-theme-base-1' ),
                    ),
                )
            ) );

            $wp_customize->add_setting( 'logo_top_bar_mobile_on_scroll', array(
                'default'           => 'move_logo',
                'type'              => 'theme_mod',
                'capability'        => 'edit_theme_options',
            ) );
    
            $wp_customize->add_control(
                new WP_Customize_Control(
                    $wp_customize,
                    'logo_top_bar_mobile_on_scroll', array(
                        'label'       => __( 'Move Logo to Top Bar on Mobile on Scroll', 'everyware-theme-base-1' ),
                        'description' => __( 'Choose to move Logo to the Top Bar after scrolling down page. NOTE: This only applies if the previous setting is set to "Keep in Header".'),
                        'section'     => 'title_tagline',
                        'settings'    => 'logo_top_bar_mobile_on_scroll',
                        'type'        => 'select',
                        'choices'     => array(
                            'header' => __( 'Keep in Header', 'everyware-theme-base-1' ),
                            'move_logo'  => __( 'Move Logo on Scroll', 'everyware-theme-base-1' ),
                        ),
                    )
                ) );
        */

        /* ------------------------------------------ Footer Logo Image ------------------------------------------- */

        //Removing until more stable setting. KAO - 20190829
        /*
        $wp_customize->add_setting( 'top_bar_logo_image', array(
            'default'           => '',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Image_Control(
                $wp_customize,
                'top_bar_logo_image', array(
                    'label'       => __( 'Top Bar Logo Image', 'everyware-theme-base-1' ),
                    'description' => __( 'If the previous setting was set to "Top Bar", and you would like a custom image, upload the image to be used here. Leave this field empty if you would to retain the header logo.'),
                    'section'     => 'title_tagline',
                    'settings'    => 'top_bar_logo_image',
                )
            ) );
        */

        /* ------------------------------------------ Footer Logo Switch ------------------------------------------- */

        $wp_customize->add_setting( 'footer_logo_switch', array(
            'default'           => 'off',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'footer_logo_switch', array(
                    'label'       => __( 'Footer Logo Switch', 'everyware-theme-base-1' ),
                    'description' => __( 'Set whether you would to switch on or off.'),
                    'section'     => 'title_tagline',
                    'settings'    => 'footer_logo_switch',
                    'type'        => 'select',
                    'choices'     => array(
                        'on' => __( 'On', 'everyware-theme-base-1' ),
                        'off'  => __( 'Off', 'everyware-theme-base-1' ),
                    ),
                )
            ) );

        /* ------------------------------------------ Footer Logo Image ------------------------------------------- */

        $wp_customize->add_setting( 'footer_logo_image', array(
            'default'           => '',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Image_Control(
                $wp_customize,
                'footer_logo_image', array(
                    'label'       => __( 'Footer Logo Image', 'everyware-theme-base-1' ),
                    'description' => __( 'Choose the image to be used in the footer.'),
                    'section'     => 'title_tagline',
                    'settings'    => 'footer_logo_image',
                )
            ) );

        $wp_customize->add_setting( 'footer_logo_size_height', array(
            'default'           => '80px',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'footer_logo_size_height', array(
                    'label'       => __( 'Footer Ad Logo Size - Height', 'everyware-theme-base-1' ),
                    'description' => __( 'Choose the height in pixels of the footer logo. Ex. "80px" '),
                    'section'     => 'title_tagline',
                    'settings'    => 'footer_logo_size_height',
                )
            ) );

        /* --------------------------------------------------------------------------------------------------------- */
        /*                                            END SITE IDENTITY SETTINGS                                     */
        /* --------------------------------------------------------------------------------------------------------- */     

        /* --------------------------------------------------------------------------------------------------------- */
        /*                                            AD SPOT SETTINGS                                               */
        /* --------------------------------------------------------------------------------------------------------- */

        $wp_customize->add_panel('ad_spots_panel',array(
            'title'=>'Ad Spots Settings',
        ));

        /* -------------------------------------------- DFP Code Switch -------------------------------------------- */

        $wp_customize->add_section( 'dfp_code_section', array(
            'title'       => __( 'DFP Code', 'everyware-theme-base-1' ),
            'capability'  => 'edit_theme_options',
            'description' => __( 'Configure Ad Spot Settings', 'everyware-theme-base-1' ),
            'panel'       => 'ad_spots_panel'
        ) );

        $wp_customize->add_setting( 'dfp_code_switch', array(
            'default'           => 'off',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'dfp_code_switch', array(
                    'label'       => __( 'Use DFP Code', 'everyware-theme-base-1' ),
                    'description' => __( 'Choose whether to use the DFP Code in the following field.', 'everyware-theme-base-1' ),
                    'section'     => 'dfp_code_section',
                    'settings'    => 'dfp_code_switch',
                    'type'        => 'select',
                    'choices'     => array(
                        'on'       => __( 'On', 'everyware-theme-base-1' ),
                        'off' => __( 'Off', 'everyware-theme-base-1' ),
                    ),
                )
            ) );

        /* DFP Code */
        $wp_customize->add_setting( 'dfp_code', array(
            'default'           => '',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'dfp_code', array(
                'label'       => __( 'DFP Code', 'everyware-theme-base-1' ),
                'description' => __( 'Add DFP Code here.'),
                'section'     => 'dfp_code_section',
                'settings'    => 'dfp_code',
                'type'        => 'textarea',
            )
        ) );

        /* --------------------------------------------- Header Ad Left -------------------------------------------- */

        $wp_customize->add_section( 'header_ad_left_section', array(
            'title'       => __( 'Header Ad Left', 'everyware-theme-base-1' ),
            'capability'  => 'edit_theme_options',
            'description' => __( 'Configure Ad Spot Settings', 'everyware-theme-base-1' ),
            'panel'       => 'ad_spots_panel'
        ) );

        $wp_customize->add_setting( 'header_left_switch', array(
            'default'           => 'off',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
            'transport' => 'refresh'
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'header_left_switch', array(
                    'label'       => __( 'Header Ad - Left', 'everyware-theme-base-1' ),
                    'description' => __( 'Choose whether to have the header leaderboard left Ad on. NOTE: Not compatible with Center logo.', 'everyware-theme-base-1' ),
                    'section'     => 'header_ad_left_section',
                    'settings'    => 'header_left_switch',
                    'type'        => 'select',
                    'choices'     => array(
                        'on'       => __( 'On', 'everyware-theme-base-1' ),
                        'off' => __( 'Off', 'everyware-theme-base-1' ),
                    ),
                )
            ) );

        $wp_customize->add_setting( 'header_left_code', array(
            'default'           => 'DFP Spot ID here...',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
            'transport' => 'refresh'
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'header_left_code', array(
                    'label'       => __( 'Header Ad ID- Left', 'everyware-theme-base-1' ),
                    'description' => __( 'Set this to the Ad Spot ID in DFP. Ex. "leaderboard"'),
                    'section'     => 'header_ad_left_section',
                    'settings'    => 'header_left_code',
                )
        ) );

        $wp_customize->add_setting( 'header_left_size', array(
            'default'           => 'DFP Spot ID Size here...',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
            'transport' => 'refresh'
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'header_left_size', array(
                    'label'       => __( 'Header Ad ID- Left', 'everyware-theme-base-1' ),
                    'description' => __( 'Set this to the size of the Ad Spot ID (Width by Height). Ex. "728x90" '),
                    'section'     => 'header_ad_left_section',
                    'settings'    => 'header_left_size',
                )
        ) );


        /* -------------------------------------------- Header Ad Right -------------------------------------------- */

        $wp_customize->add_section( 'header_ad_right_section', array(
            'title'       => __( 'Header Ad Right', 'everyware-theme-base-1' ),
            'capability'  => 'edit_theme_options',
            'description' => __( 'Configure Ad Spot Settings', 'everyware-theme-base-1' ),
            'panel'       => 'ad_spots_panel'
        ) );

        $wp_customize->add_setting( 'header_right_switch', array(
            'default'           => 'on',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'header_right_switch', array(
                    'label'       => __( 'Header Ad - Right', 'everyware-theme-base-1' ),
                    'description' => __( 'Choose whether to have the header leaderboard right ad on. NOTE: Not compatible with Center logo.', 'everyware-theme-base-1' ),
                    'section'     => 'header_ad_right_section',
                    'settings'    => 'header_right_switch',
                    'type'        => 'select',
                    'choices'     => array(
                        'on'       => __( 'On', 'everyware-theme-base-1' ),
                        'off' => __( 'Off', 'everyware-theme-base-1' ),
                    ),
                )
            ) );

        $wp_customize->add_setting( 'header_right_code', array(
            'default'           => 'DFP Spot ID here...',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'header_right_code', array(
                    'label'       => __( 'Header Ad ID - Right', 'everyware-theme-base-1' ),
                    'description' => __( 'Set this to the Ad Spot ID in DFP. Ex. "leaderboard"'),
                    'section'     => 'header_ad_right_section',
                    'settings'    => 'header_right_code',
                )
        ) );

        $wp_customize->add_setting( 'header_right_size', array(
            'default'           => 'DFP Spot ID Size here...',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'header_right_size', array(
                    'label'       => __( 'Header Ad ID - Right', 'everyware-theme-base-1' ),
                    'description' => __( 'Set this to the size of the Ad Spot ID (Width by Height). Ex. "728x90" '),
                    'section'     => 'header_ad_right_section',
                    'settings'    => 'header_right_size',
                )
        ) );

        /* ---------------------------------------------- Under Nav Ad --------------------------------------------- */

        $wp_customize->add_section( 'underNavAd_section', array(
            'title'       => __( 'Under Navigation Ad', 'everyware-theme-base-1' ),
            'capability'  => 'edit_theme_options',
            'description' => __( 'Configure Ad Spot Settings', 'everyware-theme-base-1' ),
            'panel'       => 'ad_spots_panel'
        ) );

        $wp_customize->add_setting( 'underNavAd_switch', array(
            'default'           => 'off',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'underNavAd_switch', array(
                    'label'       => __( 'Under Navigation Ad', 'everyware-theme-base-1' ),
                    'description' => __( 'Choose whether to have a leaderboard ad under the navigation on.', 'everyware-theme-base-1' ),
                    'section'     => 'underNavAd_section',
                    'settings'    => 'underNavAd_switch',
                    'type'        => 'select',
                    'choices'     => array(
                        'on'       => __( 'On', 'everyware-theme-base-1' ),
                        'off' => __( 'Off', 'everyware-theme-base-1' ),
                    ),
                )
            ) );

        $wp_customize->add_setting( 'underNavAd_code', array(
            'default'           => 'DFP Spot ID here...',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'underNavAd_code', array(
                    'label'       => __( 'Under Navigation Ad ID', 'everyware-theme-base-1' ),
                    'description' => __( 'Set this to the Ad Spot ID in DFP. Ex. "leaderboard"'),
                    'section'     => 'underNavAd_section',
                    'settings'    => 'underNavAd_code',
                )
        ) );

        $wp_customize->add_setting( 'underNavAd_size', array(
            'default'           => 'DFP Spot ID Size here...',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'underNavAd_size', array(
                    'label'       => __( 'Under Navigation Ad Size', 'everyware-theme-base-1' ),
                    'description' => __( 'Set this to the size of the Ad Spot ID (Width by Height). Ex. "728x90" '),
                    'section'     => 'underNavAd_section',
                    'settings'    => 'underNavAd_size',
                )
        ) );

        /* ----------------------------------------------- Footer Ad ----------------------------------------------- */

        $wp_customize->add_section( 'footerAd_section', array(
            'title'       => __( 'Footer Ad', 'everyware-theme-base-1' ),
            'capability'  => 'edit_theme_options',
            'description' => __( 'Configure Ad Spot Settings', 'everyware-theme-base-1' ),
            'panel'       => 'ad_spots_panel'
        ) );

        $wp_customize->add_setting( 'footerAd_switch', array(
            'default'           => 'off',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'footerAd_switch', array(
                    'label'       => __( 'Footer Ad', 'everyware-theme-base-1' ),
                    'description' => __( 'Choose whether to have a footer ad above the footer menu.', 'everyware-theme-base-1' ),
                    'section'     => 'footerAd_section',
                    'settings'    => 'footerAd_switch',
                    'type'        => 'select',
                    'choices'     => array(
                        'on'       => __( 'On', 'everyware-theme-base-1' ),
                        'off' => __( 'Off', 'everyware-theme-base-1' ),
                    ),
                )
            ) );


        $wp_customize->add_setting( 'footerAd_code', array(
            'default'           => 'DFP Spot ID here...',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'footerAd_code', array(
                    'label'       => __( 'Footer Ad ID', 'everyware-theme-base-1' ),
                    'description' => __( 'Set this to the Ad Spot ID in DFP. Ex. "leaderboard"'),
                    'section'     => 'footerAd_section',
                    'settings'    => 'footerAd_code',
                )
        ) );

        $wp_customize->add_setting( 'footerAd_size', array(
            'default'           => 'DFP Spot ID Size here...',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'footerAd_size', array(
                    'label'       => __( 'Footer Ad Size', 'everyware-theme-base-1' ),
                    'description' => __( 'Set this to the size of the Ad Spot ID (Width by Height). Ex. "728x90" '),
                    'section'     => 'footerAd_section',
                    'settings'    => 'footerAd_size',
                )
        ) );

        /* --------------------------------------------------------------------------------------------------------- */
        /*                                            END AD SPOT SETTINGS                                           */
        /* --------------------------------------------------------------------------------------------------------- */

        /* --------------------------------------------------------------------------------------------------------- */
        /*                                              ARTICLE SETTINGS                                             */
        /* --------------------------------------------------------------------------------------------------------- */

        /* -------------------------------------------- Teaser Settings -------------------------------------------- */

        $wp_customize->add_panel('custom_article_page_settings', array(
            'title'         => __('Article Settings', 'everyware-theme-base-1'),
            'description'   => __("This is where individual Article page styles can be edited.", 'everyware-theme-base-1'),
            'capability'    => 'edit_theme_options',
        ));

        $wp_customize->add_section('teaser_summary_section', array(
            'title'    => __('Teaser Settings', 'everyware-theme-base-1'),
            'panel'    => 'custom_article_page_settings',
            'description' => __('Edit the appearance of date and times.', 'everyware-theme-base-1')
        ));    

        $wp_customize->add_setting('teaser_frontpage_length', array(
            'default'           => '70',
            'capability'        => 'edit_theme_options',
            'sanitize_callback' => 'sanitize_text_field'
        ));

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'teaser_frontpage_length', array(
                    'label'       => __( 'Teaser Length - Frontpage', 'everyware-theme-base-1' ),
                    'description' => __( 'Choose the amount of characters to show in the teasers on the section fronts before being cut to ...'),
                    'section'     => 'teaser_summary_section',
                    'settings'    => 'teaser_frontpage_length',
                    'type'        => 'text',
                )
            )
        );

        $wp_customize->add_setting('teaser_section_front_length', array(
            'default'           => '70',
            'capability'        => 'edit_theme_options',
            'sanitize_callback' => 'sanitize_text_field'
        ));

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'teaser_section_front_length', array(
                    'label'       => __( 'Teaser Length - Section Front', 'everyware-theme-base-1' ),
                    'description' => __( 'Choose the amount of characters to show in the teasers on the section fronts before being cut to ...'),
                    'section'     => 'teaser_summary_section',
                    'settings'    => 'teaser_section_front_length',
                    'type'        => 'text',
                )
            )
        );

        $wp_customize->add_section('time_section', array(
            'title'    => __('Time Settings', 'everyware-theme-base-1'),
            'panel'    => 'custom_article_page_settings',
            'description' => __('Edit the appearance of date and times.', 'everyware-theme-base-1')
        ));

        $wp_customize->add_setting('timezone_setting', array(
            'default'           => 'America/New_York',
            'capability'        => 'edit_theme_options',
            'sanitize_callback' => 'sanitize_text_field'
        ));

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'timezone_setting', array(
                    'label'       => __( 'Article Timezone', 'everyware-theme-base-1' ),
                    'description' => __( 'Set the default timezone.'),
                    'section'     => 'time_section',
                    'settings'    => 'timezone_setting',
                    'type'        => 'select',
                    'choices'     => array(
                        'America/New_York' => __( 'Eastern Standard Time (EST)', 'everyware-theme-base-1' ),
                        'America/Los_Angeles'  => __( 'Pacific Standard Time (PST)', 'everyware-theme-base-1' ),
                        'America/Chicago'  => __( 'Central Standard Time (CST)', 'everyware-theme-base-1' ),
                    ),
                )
            )
        );

        $wp_customize->add_setting('published_time_label', array(
            'default'           => 'Created:',
            'capability'        => 'edit_theme_options',
            'sanitize_callback' => 'sanitize_text_field'
        ));

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'published_time_label', array(
                    'label'       => __( 'Published Time Label', 'everyware-theme-base-1' ),
                    'description' => __( 'Choose the label for the Published time on the teasers and article page. (Ex. "Created:") <b>NOTE:</b> Leave empty if you would not like to have a label before the time.'),
                    'section'     => 'time_section',
                    'settings'    => 'published_time_label',
                    'type'        => 'text',
                )
            )
        );

        $wp_customize->add_setting('modified_time_label', array(
            'default'           => 'Updated:',
            'capability'        => 'edit_theme_options',
            'sanitize_callback' => 'sanitize_text_field'
        ));

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'modified_time_label', array(
                    'label'       => __( 'Modified Time Label', 'everyware-theme-base-1' ),
                    'description' => __( 'Choose the label for the Modified time on the teasers and article page. (Ex. "Updated:") <b>NOTE:</b> Leave empty if you would not like to have a label before the time.'),
                    'section'     => 'time_section',
                    'settings'    => 'modified_time_label',
                    'type'        => 'text',
                )
            )
        );

        $wp_customize->add_setting('teaser_time_display', array(
            'default'           => 'on',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ));

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'teaser_time_display', array(
                    'label'       => __( 'Display Teaser Date/Time - Sections', 'everyware-theme-base-1' ),
                    'description' => __( 'Display the teaser\'s date/time on section fronts.'),
                    'section'     => 'time_section',
                    'settings'    => 'teaser_time_display',
                    'type'        => 'select',
                    'choices'     => array(
                        'on'      => __( 'Display', 'everyware-theme-base-1' ),
                        'off'     => __( 'Do Not Display', 'everyware-theme-base-1' ),
                    ),
                )
            )
        );

        $wp_customize->add_setting('teaser_time_format', array(
            'default'           => 'M d, Y h:i A',
            'capability'        => 'edit_theme_options',
            'type'              => 'theme_mod',
            'sanitize_callback' => 'sanitize_text_field'
        ));

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'teaser_time_format', array(
                    'label'       => __( 'Teaser Date/Time Format', 'everyware-theme-base-1' ),
                    'description' => __( 'Change date/time format for teasers. (Ex. \'M d, Y h:i A\') <b>NOTE:</b> Reference http://php.net/manual/en/function.date.php for PHP Time Settings.'),
                    'section'     => 'time_section',
                    'settings'    => 'teaser_time_format',
                    'type'        => 'text',
                )
            )
        );
        $wp_customize->add_setting('article_time_format', array(
            'default'           => 'M d, Y h:i A',
            'capability'        => 'edit_theme_options',
            'type'              => 'theme_mod',
            'sanitize_callback' => 'sanitize_text_field'
        ));
        

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'article_time_format', array(
                    'label'       => __( 'Article Date/Time Format', 'everyware-theme-base-1' ),
                    'description' => __( 'Change date/time format for articles. (Ex. \'M d, Y h:i A\') <b>NOTE:</b> Reference http://php.net/manual/en/function.date.php for PHP Time Settings.'),
                    'section'     => 'time_section',
                    'settings'    => 'article_time_format',
                    'type'        => 'text',
                )
            )
        );


        /* --------------------------------------------- Ad Settings --------------------------------------------- */

        $wp_customize->add_section('article_ad_section', array(
            'title'    => __('Ad Settings', 'everyware-theme-base-1'),
            'panel'    => 'custom_article_page_settings',
            'description' => __('Edit the appearance of images on article pages.', 'everyware-theme-base-1')
        ));

        $wp_customize->add_setting('article_ad_switch', array(
            'default'           => false,
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ));

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'article_ad_switch', array(
                    'label'       => __( 'In-Article Ads', 'everyware-theme-base-1' ),
                    'description' => __( 'Choose whether or not to have ads within the article.'),
                    'section'     => 'article_ad_section',
                    'settings'    => 'article_ad_switch',
                    'type'        => 'select',
                    'choices'     => array(
                        true      => __( 'On', 'everyware-theme-base-1' ),
                        false     => __( 'Off', 'everyware-theme-base-1' ),
                    ),
                )
            )
        );

        $wp_customize->add_setting('article_ad_frequency', array(
            'default'           => '0',
            'capability'        => 'edit_theme_options',
            'sanitize_callback' => 'sanitize_text_field'
        ));

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'article_ad_frequency', array(
                    'label'       => __( 'In-Article Ad Frequency', 'everyware-theme-base-1' ),
                    'description' => __( 'Choose the frequency that ads show between paragraphs. (ex: "2" would input ads every 2 paragraphs'),
                    'section'     => 'article_ad_section',
                    'settings'    => 'article_ad_frequency',
                    'type'        => 'number',
                )
            )
        );

        $wp_customize->add_setting('article_ad_id', array(
            'capability'        => 'edit_theme_options',
            'sanitize_callback' => 'sanitize_text_field'
        ));

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'article_ad_id', array(
                    'label'       => __( 'Article Ad ID', 'everyware-theme-base-1' ),
                    'description' => __( 'Set this to the Ad Spot ID in DFP. (Ex. "leaderboard"'),
                    'section'     => 'article_ad_section',
                    'settings'    => 'article_ad_id',
                    'type'        => 'text',
                )
            )
        );

        /* -------------------------------------------- Image settings ------------------------------------------- */

        $wp_customize->add_section('article_image_section', array(
            'title'    => __('Image Settings', 'everyware-theme-base-1'),
            'panel'    => 'custom_article_page_settings',
            'description' => __('Edit the appearance of images on article pages.', 'everyware-theme-base-1')
        ));

        $wp_customize->add_setting( 'image_endpoint_url', array(
            'default'           => '',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'image_endpoint_url', array(
                    'label'       => __( 'Image Endpoint URL', 'everyware-theme-base-1' ),
                    'description' => __( 'Set the Image Endpoint URL for your site.'),
                    'section'     => 'article_image_section',
                    'settings'    => 'image_endpoint_url',
                )
        ) );
        
        $wp_customize->add_setting('article_image_width', array(
            'default'           => '600',
            'capability'        => 'edit_theme_options',
            'type'              => 'theme_mod',
            'sanitize_callback' => 'sanitize_text_field'
        ));

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'article_image_width', array(
                    'label'       => __( 'Article Image - Width', 'everyware-theme-base-1' ),
                    'description' => __( 'Change the width of images on article pages.'),
                    'section'     => 'article_image_section',
                    'settings'    => 'article_image_width',
                    'type'        => 'number',
                )
            )
        );
        $wp_customize->add_setting('article_image_ratio', array(
            'default'           => '16:9',
            'capability'        => 'edit_theme_options',
            'type'              => 'theme_mod',
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'article_image_ratio', array(
                    'label'       => __( 'Article Image Aspect Ratio', 'everyware-theme-base-1' ),
                    'description' => __( 'Choose the aspect ratio for images on article pages.'),
                    'section'     => 'article_image_section',
                    'settings'    => 'article_image_ratio',
                    'type'        => 'select',
                    'choices'     => array(
                        'original'  => __( 'Original', 'everyware-theme-base-1'),
                        '1:1'       => __( '1:1 - Square', 'everyware-theme-base-1' ),
                        '3:2'       => __( '3:2', 'everyware-theme-base-1' ),
                        '4:3'       => __( '4:3', 'everyware-theme-base-1' ),
                        '16:9'      => __( '16:9', 'everyware-theme-base-1' ),
                        '2:3'       => __( '2:3', 'everyware-theme-base-1' ),
                        '3:4'       => __( '3:4', 'everyware-theme-base-1' ),
                        '9:16'      => __( '9:16', 'everyware-theme-base-1' ),
                    ),
                )
            )
        );

        /* --------------------------------------------------------------------------------------------------------- */
        /*                                        END OF ARTICLE SETTINGS                                            */
        /* --------------------------------------------------------------------------------------------------------- */  

        /* --------------------------------------------------------------------------------------------------------- */
        /*                                            COLOR SETTINGS                                                 */
        /* --------------------------------------------------------------------------------------------------------- */

        /* ---------------------------------------- Above Footer Widget Area Colors Section ------------------------- */

        $wp_customize->add_section( 'colors_above_footer_widget_area_section', array(
            'title'       => __( 'Above Footer Widget Area Colors', 'everyware-theme-base-1' ),
            'capability'  => 'edit_theme_options',
            'description' => __( 'Set the colors for the widget area "Above Footer".', 'everyware-theme-base-1' ),
            'panel'       => 'colors_settings'
        ) );

        /* -------------------------------- Above Footer Widget Area Color ---------------------------------------- */

        $wp_customize->add_setting( 'above_footer_widget_area_color' , array(
            'default'   => '#fdfdfd',
            'transport' => 'refresh',
            'sanitize_callback' => 'sanitize_hex_color'
        ) );

        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'above_footer_widget_area_color', array(
            'label'    => __( 'Above Footer Widget Area Color', 'everyware-theme-base-1' ),
            'description' => __( 'Set the background color for the "Above Footer Widget Area" region if turned on.'),
            'section'  => 'colors_above_footer_widget_area_section',
            'settings' => 'above_footer_widget_area_color',
        ) ) );

        /* -------------------------------- Above Footer Widget Area Text Color ---------------------------------------- */

        $wp_customize->add_setting( 'above_footer_widget_area_text_color' , array(
            'default'   => '#000000',
            'transport' => 'refresh',
            'sanitize_callback' => 'sanitize_hex_color'
        ) );

        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'above_footer_widget_area_text_color', array(
            'label'    => __( 'Above Footer Widget Area Text Color', 'everyware-theme-base-1' ),
            'description' => __( 'Set the text color for the "Above Footer Widget Area" region if turned on.'),
            'section'  => 'colors_above_footer_widget_area_section',
            'settings' => 'above_footer_widget_area_text_color',
        ) ) );

        /* ---------------------------------------- Article Colors Section ------------------------------------------ */

        $wp_customize->add_section( 'colors_article_section', array(
            'title'       => __( 'Article Page Colors', 'everyware-theme-base-1' ),
            'capability'  => 'edit_theme_options',
            'description' => __( 'Set the colors for the article page.', 'everyware-theme-base-1' ),
            'panel'       => 'colors_settings'
        ) );


        /* ------------------------------------- Article Summary Text Color ---------------------------------------- */

        $wp_customize->add_setting( 'article_summary_text' , array(
            'default'   => '#000000',
            'transport' => 'refresh',
            'sanitize_callback' => 'sanitize_hex_color'
        ) );

        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'article_summary_text', array(
            'label'    => __( 'Article Summary Text', 'everyware-theme-base-1' ),
            'description' => __( 'Set the color of the article summary text on the single article pages.'),
            'section'  => 'colors_article_section',
            'settings' => 'article_summary_text',
        ) ) );

        /* -------------------------------------- Article Body Text Color  ---------------------------------------- */

        $wp_customize->add_setting( 'article_body_text' , array(
            'default'   => '#000000',
            'transport' => 'refresh',
            'sanitize_callback' => 'sanitize_hex_color'
        ) );

        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'article_body_text', array(
            'label'    => __( 'Article Body Text', 'everyware-theme-base-1' ),
            'description' => __( 'Set the color of the article body text on the single article pages.'),
            'section'  => 'colors_article_section',
            'settings' => 'article_body_text',
        ) ) );

        /* ---------------------------------------- Breaking News Colors Section ------------------------------------------ */

        $wp_customize->add_section( 'colors_breaking_news_section', array(
            'title'       => __( 'Breaking News Colors', 'everyware-theme-base-1' ),
            'capability'  => 'edit_theme_options',
            'description' => __( 'Set the colors for the breaking news section.', 'everyware-theme-base-1' ),
            'panel'       => 'colors_settings'
        ) );

        /* --------------------------------------- Breaking News Background Color ------------------------------------------- */

        $wp_customize->add_setting( 'breaking_news_background_color' , array(
            'default'   => '#9a2323',
            'transport' => 'refresh',
            'sanitize_callback' => 'sanitize_hex_color'
        ) );

        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'breaking_news_background_color', array(
            'label'    => __( 'Breaking News Background Color', 'everyware-theme-base-1' ),
            'description' => __( 'Set the color for the background for the Breaking News section if turned on.'),
            'section'  => 'colors_breaking_news_section',
            'settings' => 'breaking_news_background_color',
        ) ) );

        /* --------------------------------------- Breaking News Text Color ------------------------------------------- */

        $wp_customize->add_setting( 'breaking_news_text_color' , array(
            'default'   => 'white',
            'transport' => 'refresh',
            'sanitize_callback' => 'sanitize_hex_color'
        ) );

        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'breaking_news_text_color', array(
            'label'    => __( 'Breaking News Text Color', 'everyware-theme-base-1' ),
            'description' => __( 'Set the color for the text for the Breaking News section if turned on.'),
            'section'  => 'colors_breaking_news_section',
            'settings' => 'breaking_news_text_color',
        ) ) );

        /* ---------------------------------------- Footer Colors Section ------------------------------------------ */

        $wp_customize->add_section( 'colors_footer_section', array(
            'title'       => __( 'Footer Colors', 'everyware-theme-base-1' ),
            'capability'  => 'edit_theme_options',
            'description' => __( 'Set the colors for the footer.', 'everyware-theme-base-1' ),
            'panel'       => 'colors_settings'
        ) );

        /* -------------------------------------------- Footer Background Color ---------------------------------------------- */

        $wp_customize->add_setting( 'footer_color' , array(
            'default'   => '#fdfdfd',
            'transport' => 'refresh',
            'sanitize_callback' => 'sanitize_hex_color'
        ) );

        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'footer_color', array(
            'label'    => __( 'Footer Background Color', 'everyware-theme-base-1' ),
            'description' => __( 'Set the background color for the footer region.'),
            'section'  => 'colors_footer_section',
            'settings' => 'footer_color',
        ) ) );

        /* ----------------------------------------- Footer Link Color -------------------------------------------- */

        $wp_customize->add_setting( 'footer_link_color' , array(
            'default'   => '#ffffff',
            'transport' => 'refresh',
            'sanitize_callback' => 'sanitize_hex_color'
        ) );

        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'footer_link_color', array(
            'label'    => __( 'Footer Link Color', 'everyware-theme-base-1' ),
            'description' => __( 'Set the color for the links in the footer.'),
            'section'  => 'colors_footer_section',
            'settings' => 'footer_link_color',
        ) ) );

        /* -------------------------------------- Footer Link Hover Color ----------------------------------------- */

        $wp_customize->add_setting( 'footer_link_hover_color' , array(
            'default'   => '#ffffff',
            'transport' => 'refresh',
            'sanitize_callback' => 'sanitize_hex_color'
        ) );

        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'footer_link_hover_color', array(
            'label'    => __( 'Footer Link Hover Color', 'everyware-theme-base-1' ),
            'description' => __( 'Set the color for the links on hover in the footer.'),
            'section'  => 'colors_footer_section',
            'settings' => 'footer_link_hover_color',
        ) ) );

        /* -------------------------------------- Footer Menu Title Color ----------------------------------------- */

        $wp_customize->add_setting( 'footer_menu_title_color' , array(
            'default'   => '#000000',
            'transport' => 'refresh',
            'sanitize_callback' => 'sanitize_hex_color'
        ) );

        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'footer_menu_title_color', array(
            'label'    => __( 'Footer Menu Title Color', 'everyware-theme-base-1' ),
            'description' => __( 'Set the color for the menu titles in the footer.'),
            'section'  => 'colors_footer_section',
            'settings' => 'footer_menu_title_color',
        ) ) );

        /* -------------------------------------- Footer Contact Info Color ----------------------------------------- */
        
        $wp_customize->add_setting( 'footer_contact_info_color' , array(
            'default'   => '#ffffff',
            'transport' => 'refresh',
            'sanitize_callback' => 'sanitize_hex_color'
        ) );

        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'footer_contact_info_color', array(
            'label'    => __( 'Footer Contact Information Color', 'everyware-theme-base-1' ),
            'description' => __( 'Set the color for the contact information in the footer.'),
            'section'  => 'colors_footer_section',
            'settings' => 'footer_contact_info_color',
        ) ) );

        /* -------------------------------------- Footer Social Media Color ----------------------------------------- */
        
        $wp_customize->add_setting( 'footer_social_media_color' , array(
            'default'   => '#ffffff',
            'transport' => 'refresh',
            'sanitize_callback' => 'sanitize_hex_color'
        ) );

        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'footer_social_media_color', array(
            'label'    => __( 'Footer Social Media Icons Color', 'everyware-theme-base-1' ),
            'description' => __( 'Set the color for the social media icons in the footer.'),
            'section'  => 'colors_footer_section',
            'settings' => 'footer_social_media_color',
        ) ) );

        /* -------------------------------------- Footer Copyright Color ----------------------------------------- */

        $wp_customize->add_setting( 'footer_copyright_color' , array(
            'default'   => '#000000',
            'transport' => 'refresh',
            'sanitize_callback' => 'sanitize_hex_color'
        ) );

        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'footer_copyright_color', array(
            'label'    => __( 'Footer Copyright Text Color', 'everyware-theme-base-1' ),
            'description' => __( 'Set the color for the Copyright text in the footer.'),
            'section'  => 'colors_footer_section',
            'settings' => 'footer_copyright_color',
        ) ) );

        /* ---------------------------------------- Text Colors Section ------------------------------------------ */

        $wp_customize->add_section( 'colors_text_section', array(
            'title'       => __( 'General Text Colors', 'everyware-theme-base-1' ),
            'capability'  => 'edit_theme_options',
            'description' => __( 'Set the colors for the general text types.', 'everyware-theme-base-1' ),
            'panel'       => 'colors_settings'
        ) );


        /* ----------------------------------------- Heading Text Color -------------------------------------------- */

        $wp_customize->add_setting( 'headline_text' , array(
            'default'   => '#000000',
            'transport' => 'refresh',
            'sanitize_callback' => 'sanitize_hex_color'
        ) );

        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'headline_text', array(
            'label'    => __( 'Headline Text', 'everyware-theme-base-1' ),
            'description' => __( 'Set the color of the headlines for the frontpage teasers and section front teasers.'),
            'section'  => 'colors_text_section',
            'settings' => 'headline_text',
        ) ) );

        /* ---------------------------------------------- Link Color ---------------------------------------------- */

        $wp_customize->add_setting( 'link_color' , array(
            'default'   => '#000000',
            'transport' => 'refresh',
            'sanitize_callback' => 'sanitize_hex_color'
        ) );

        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'link_color', array(
            'label'    => __( 'Link Color', 'everyware-theme-base-1' ),
            'description' => __( 'Set the color of the all links with a color not already specified.'),
            'section'  => 'colors_text_section',
            'settings' => 'link_color',
        ) ) );

        /* ------------------------------------------ Link Hover Color -------------------------------------------- */

        $wp_customize->add_setting( 'link_hover_color' , array(
            'default'   => '#0061ff',
            'transport' => 'refresh',
            'sanitize_callback' => 'sanitize_hex_color'
        ) );

        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'link_hover_color', array(
            'label'    => __( 'Link Hover Color', 'everyware-theme-base-1' ),
            'description' => __( 'Set the color on hover for all links with a color not already specified.'),
            'section'  => 'colors_text_section',
            'settings' => 'link_hover_color',
        ) ) );

        /* -------------------------------------------- Button Color ----------------------------------------------- */

        $wp_customize->add_setting( 'button_color' , array(
            'default'   => '#636363',
            'transport' => 'refresh',
            'sanitize_callback' => 'sanitize_hex_color'
        ) );

        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'button_color', array(
            'label'    => __( 'Button Color', 'everyware-theme-base-1' ),
            'description' => __( 'Set the color for the buttons shown on forms.'),
            'section'  => 'colors_text_section',
            'settings' => 'button_color',
        ) ) );

        /* ---------------------------------------- Header Colors Section ------------------------------------------ */

        $wp_customize->add_section( 'colors_header_section', array(
            'title'       => __( 'Header Colors', 'everyware-theme-base-1' ),
            'capability'  => 'edit_theme_options',
            'description' => __( 'Set the colors for the header.', 'everyware-theme-base-1' ),
            'panel'       => 'colors_settings'
        ) );

        /* --------------------------------------------- Header Color ---------------------------------------------- */

        $wp_customize->add_setting( 'header_color' , array(
            'default'   => '#fdfdfd',
            'transport' => 'refresh',
            'sanitize_callback' => 'sanitize_hex_color'
        ) );

        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'header_color', array(
            'label'    => __( 'Header Background Color', 'everyware-theme-base-1' ),
            'description' => __( 'Set the background color of the Header region.'),
            'section'  => 'colors_header_section',
            'settings' => 'header_color',
        ) ) );

        /* ---------------------------------------- Login Modal Section ------------------------------------------ */

        $wp_customize->add_section( 'colors_login_modal_section', array(
            'title'       => __( 'Login Modal Colors', 'everyware-theme-base-1' ),
            'capability'  => 'edit_theme_options',
            'description' => __( 'Set the colors for the login modal.', 'everyware-theme-base-1' ),
            'panel'       => 'colors_settings'
        ) );

        /* -------------------------------------- Login Modal Background Color ---------------------------------------- */
        $wp_customize->add_setting( 'login_modal_background_color' , array(
            'default'   => '#ffffff',
            'transport' => 'refresh',
            'sanitize_callback' => 'sanitize_hex_color'
        ) );

        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'login_modal_background_color', array(
            'label'    => __( 'Login Modal Background Color', 'everyware-theme-base-1' ),
            'description' => __( 'Set the background color for the login modal.'),
            'section'  => 'colors_login_modal_section',
            'settings' => 'login_modal_background_color',
        ) ) );

        /* -------------------------------------- Login Modal Border Color ---------------------------------------- */

        $wp_customize->add_setting( 'login_modal_border_color' , array(
            'default'   => '#000000',
            'transport' => 'refresh',
            'sanitize_callback' => 'sanitize_hex_color'
        ) );

        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'login_modal_border_color', array(
            'label'    => __( 'Login Modal Border Color', 'everyware-theme-base-1' ),
            'description' => __( 'Set the border color for the login modal.'),
            'section'  => 'colors_login_modal_section',
            'settings' => 'login_modal_border_color',
        ) ) );

        /* ---------------------------------------- Login Modal Text Color ----------------------------------------- */

        $wp_customize->add_setting( 'login_modal_text_color' , array(
            'default'   => '#000000',
            'transport' => 'refresh',
            'sanitize_callback' => 'sanitize_hex_color'
        ) );

        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'login_modal_text_color', array(
            'label'    => __( 'Login Modal Text Color', 'everyware-theme-base-1' ),
            'description' => __( 'Set the text color for the login modal.'),
            'section'  => 'colors_login_modal_section',
            'settings' => 'login_modal_text_color',
        ) ) );

        /* ---------------------------------------- Main Menu Colors Section ------------------------------------------ */

        $wp_customize->add_section( 'colors_main_menu_section', array(
            'title'       => __( 'Main Menu Colors', 'everyware-theme-base-1' ),
            'capability'  => 'edit_theme_options',
            'description' => __( 'Set the colors for the main menu.', 'everyware-theme-base-1' ),
            'panel'       => 'colors_settings'
        ) );


        /* ------------------------------------------- Main Menu Color --------------------------------------------- */

        $wp_customize->add_setting( 'main_menu_color' , array(
            'default'   => '#d6d6d6',
            'transport' => 'refresh',
            'sanitize_callback' => 'sanitize_hex_color'
        ) );

        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'main_menu_color', array(
            'label'    => __( 'Main Menu Background Color', 'everyware-theme-base-1' ),
            'description' => __( 'Set the background color of the main navigation.'),
            'section'  => 'colors_main_menu_section',
            'settings' => 'main_menu_color',
        ) ) );

        /* ---------------------------------------- Main Menu Link Color ------------------------------------------- */

        $wp_customize->add_setting( 'main_menu_link_color' , array(
            'default'   => '#000000',
            'transport' => 'refresh',
            'sanitize_callback' => 'sanitize_hex_color'
        ) );

        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'main_menu_link_color', array(
            'label'    => __( 'Main Menu Link Color', 'everyware-theme-base-1' ),
            'description' => __( 'Set the color of the links on the main navigation.'),
            'section'  => 'colors_main_menu_section',
            'settings' => 'main_menu_link_color',
        ) ) );

        /* --------------------------------------- Main Menu Hover Color ------------------------------------------- */

        $wp_customize->add_setting( 'link_navigation_hover_color' , array(
            'default'   => '#ffffff',
            'transport' => 'refresh',
            'sanitize_callback' => 'sanitize_hex_color'
        ) );

        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'link_navigation_hover_color', array(
            'label'    => __( 'Main Menu Link Hover Color', 'everyware-theme-base-1' ),
            'description' => __( 'Set the color on hover for the links on the main navigation.'),
            'section'  => 'colors_main_menu_section',
            'settings' => 'link_navigation_hover_color',
        ) ) );

        /* ---------------------------------------- Sidebar Colors Section ------------------------------------------ */

        $wp_customize->add_section( 'colors_sidebar_section', array(
            'title'       => __( 'Sidebar Colors', 'everyware-theme-base-1' ),
            'capability'  => 'edit_theme_options',
            'description' => __( 'Set the colors for the left sidebar, right sidebar, or both.', 'everyware-theme-base-1' ),
            'panel'       => 'colors_settings'
        ) );

        /* -------------------------------------------- Sidebar Background Color ---------------------------------------------- */

        $wp_customize->add_setting( 'sidebar_color' , array(
            'default'   => '#f2f2f2',
            'transport' => 'refresh',
            'sanitize_callback' => 'sanitize_hex_color'
        ) );

        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'sidebar_color', array(
            'label'    => __( 'Sidebar Background Color', 'everyware-theme-base-1' ),
            'description' => __( 'Set the background color of the Sidebars.'),
            'section'  => 'colors_sidebar_section',
            'settings' => 'sidebar_color',
        ) ) );

        /* ---------------------------------------- Sidebar Menu Colors Section ------------------------------------------ */

        $wp_customize->add_section( 'colors_sidebar_menu_section', array(
            'title'       => __( 'Sidebar Menu Colors', 'everyware-theme-base-1' ),
            'capability'  => 'edit_theme_options',
            'description' => __( 'Set the colors for the expandable sidebar menu, if turned on.', 'everyware-theme-base-1' ),
            'panel'       => 'colors_settings'
        ) );

        /* ----------------------------------------- Sidebar Menu Color -------------------------------------------- */

        $wp_customize->add_setting( 'sidebar_menu_color' , array(
            'default'   => '#212121',
            'transport' => 'refresh',
            'sanitize_callback' => 'sanitize_hex_color'
        ) );

        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'sidebar_menu_color', array(
            'label'    => __( 'Sidebar Menu Background Color', 'everyware-theme-base-1' ),
            'description' => __( 'Set the background color of the expandable Sidebar menu.'),
            'section'  => 'colors_sidebar_menu_section',
            'settings' => 'sidebar_menu_color',
        ) ) );

        /* -------------------------------------- Sidebar Menu Link Color ------------------------------------------ */

        $wp_customize->add_setting( 'sidebar_menu_link_color' , array(
            'default'   => '#f1f1f1',
            'transport' => 'refresh',
            'sanitize_callback' => 'sanitize_hex_color'
        ) );

        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'sidebar_menu_link_color', array(
            'label'    => __( 'Sidebar Menu Link Color', 'everyware-theme-base-1' ),
            'description' => __( 'Set the color of the links within the Sidebar menu.'),
            'section'  => 'colors_sidebar_menu_section',
            'settings' => 'sidebar_menu_link_color',
        ) ) );

        /* ---------------------------------------- Teaser Text Colors Section ------------------------------------------ */

        $wp_customize->add_section( 'colors_teaser_text_section', array(
            'title'       => __( 'Teaser Text Colors', 'everyware-theme-base-1' ),
            'capability'  => 'edit_theme_options',
            'description' => __( 'Set the colors for the general text types.', 'everyware-theme-base-1' ),
            'panel'       => 'colors_settings'
        ) );

    
        /* --------------------------------------- Teaser Body Text Color ------------------------------------------ */

        $wp_customize->add_setting( 'teaser_body_text' , array(
            'default'   => '#888888',
            'transport' => 'refresh',
            'sanitize_callback' => 'sanitize_hex_color'
        ) );

        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'teaser_body_text', array(
            'label'    => __( 'Teaser Body Text', 'everyware-theme-base-1' ),
            'description' => __( 'Set the color of the teaser body text for all teasers.'),
            'section'  => 'colors_teaser_text_section',
            'settings' => 'teaser_body_text',
        ) ) );

        /* --------------------------------------- Thin Teaser Category Text Color ------------------------------------------ */

        $wp_customize->add_setting( 'thin_teaser_category_text' , array(
            'default'   => '#3b3b84',
            'transport' => 'refresh',
            'sanitize_callback' => 'sanitize_hex_color'
        ) );

        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'thin_teaser_category_text', array(
            'label'    => __( 'Thin Teaser Category Text', 'everyware-theme-base-1' ),
            'description' => __( 'Set the color of the Category text on the Thin Teaser template teaser.'),
            'section'  => 'colors_teaser_text_section',
            'settings' => 'thin_teaser_category_text',
        ) ) );
    
        /* --------------------------------------- Thin Teaser Background Text Color ------------------------------------------ */

        $wp_customize->add_setting( 'thin_teaser_background_color' , array(
            'default'   => '#f2f2f2',
            'transport' => 'refresh',
            'sanitize_callback' => 'sanitize_hex_color'
        ) );

        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'thin_teaser_background_color', array(
            'label'    => __( 'Thin Teaser Background Color', 'everyware-theme-base-1' ),
            'description' => __( 'Set the color of the background for the Thin Teaser template teaser'),
            'section'  => 'colors_teaser_text_section',
            'settings' => 'thin_teaser_background_color',
        ) ) );

        /* --------------------------------------- Category Block Category Text Color ------------------------------------------ */

        $wp_customize->add_setting( 'category_block_category_text' , array(
            'default'   => '#1e0745',
            'transport' => 'refresh',
            'sanitize_callback' => 'sanitize_hex_color'
        ) );

        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'category_block_category_text', array(
            'label'    => __( 'Category Block Category Text', 'everyware-theme-base-1' ),
            'description' => __( 'Set the color of the Category text on the Category Block teasers.'),
            'section'  => 'colors_teaser_text_section',
            'settings' => 'category_block_category_text',
        ) ) );

        /* --------------------------------------- Category Block - Opinion Author Text Color ------------------------------------------ */

        $wp_customize->add_setting( 'category_block_opinion_author_text' , array(
            'default'   => '#1e0745',
            'transport' => 'refresh',
            'sanitize_callback' => 'sanitize_hex_color'
        ) );

        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'category_block_opinion_author_text', array(
            'label'    => __( 'Category Block Opinion Author Text', 'everyware-theme-base-1' ),
            'description' => __( 'Set the color of the Author text on the Category Block - Opinion teasers.'),
            'section'  => 'colors_teaser_text_section',
            'settings' => 'category_block_opinion_author_text',
        ) ) );

        /* ---------------------------------------- Top Bar Colors Section ------------------------------------------ */

        $wp_customize->add_panel('colors_settings',array(
            'title'=>'Colors Settings',
        ));

        $wp_customize->add_section( 'colors_top_bar_section', array(
            'title'       => __( 'Top Bar Colors', 'everyware-theme-base-1' ),
            'capability'  => 'edit_theme_options',
            'description' => __( 'Set the colors for the Top Bar.', 'everyware-theme-base-1' ),
            'panel'       => 'colors_settings'
        ) );

        /* ---------------------------------------- Topbar Background Color ---------------------------------------- */
        $wp_customize->add_setting( 'topbar_color' , array(
            'default'   => '#fdfdfd',
            'transport' => 'refresh',
            'sanitize_callback' => 'sanitize_hex_color'
        ) );

        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'topbar_color', array(
            'label'    => __( 'Top Bar Background Color', 'everyware-theme-base-1' ),
            'description' => __( 'Set the color of the Top Bar region.'),
            'section'  => 'colors_top_bar_section',
            'settings' => 'topbar_color',
        ) ) );

        /* ---------------------------------------- Topbar Text Color ---------------------------------------- */
        $wp_customize->add_setting( 'topbar_text_color' , array(
            'default'   => '#000000',
            'transport' => 'refresh',
            'sanitize_callback' => 'sanitize_hex_color'
        ) );

        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'topbar_text_color', array(
            'label'    => __( 'Top Bar Link Color', 'everyware-theme-base-1' ),
            'description' => __( 'Set the color of the links within the Top Bar region.'),
            'section'  => 'colors_top_bar_section',
            'settings' => 'topbar_text_color',
        ) ) );

        /* ---------------------------------------- Topbar Link Hover Color ---------------------------------------- */

        $wp_customize->add_setting( 'topbar_link_hover_color' , array(
            'default'   => '#000000',
            'transport' => 'refresh',
            'sanitize_callback' => 'sanitize_hex_color'
        ) );

        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'topbar_link_hover_color', array(
            'label'    => __( 'Top Bar Link Hover Color', 'everyware-theme-base-1' ),
            'description' => __( 'Set the color of the links on hover within the Top Bar region.'),
            'section'  => 'colors_top_bar_section',
            'settings' => 'topbar_link_hover_color',
        ) ) );

        /* ---------------------------------------- Topbar Link Hover Color ---------------------------------------- */

        $wp_customize->add_setting( 'hamburger_menu_trigger_color' , array(
            'default'   => '#ffffff',
            'transport' => 'refresh',
            'sanitize_callback' => 'sanitize_hex_color'
        ) );

        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'hamburger_menu_trigger_color', array(
            'label'    => __( 'Hamburger Menu Trigger Color', 'everyware-theme-base-1' ),
            'description' => __( 'Set the color of the trigger on the top bar for the Hamburger menu.'),
            'section'  => 'colors_top_bar_section',
            'settings' => 'hamburger_menu_trigger_color',
        ) ) );

        /* --------------------------------------------------------------------------------------------------------- */
        /*                                             END COLOR SETTINGS                                            */
        /* --------------------------------------------------------------------------------------------------------- */

        /* --------------------------------------------------------------------------------------------------------- */
        /*                                             FONT SETTINGS                                                 */
        /* --------------------------------------------------------------------------------------------------------- */

        $wp_customize->add_panel('fonts_panel',array(
            'title'=>'Font Settings',
        ));


        /* ---------------------------------------- Custom Font Family Section ------------------------------------------ */

        $wp_customize->add_section( 'font_family_section', array(
            'title'       => __( 'Custom Font Family', 'everyware-theme-base-1' ),
            'capability'  => 'edit_theme_options',
            'description' => __( 'Add a custom font family via adding the import URL.', 'everyware-theme-base-1' ),
            'panel'       => 'fonts_panel'
        ) );

        /* ---------------------------------------------- Custom Font Family URL --------------------------------------------- */

        $wp_customize->add_setting( 'custom_font_family_url', array(
            'default'           => '',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'custom_font_family_url', array(
                    'label'       => __( 'Import URL', 'everyware-theme-base-1' ),
                    'description' => __( 'Add the import URL for the Font Family here. Ex. https://fonts.googleapis.com/css?family=Merriweather'),
                    'section'     => 'font_family_section',
                    'settings'    => 'custom_font_family_url',
                )
        ) );

        /* ---------------------------------------------- Custom Font Family Name --------------------------------------------- */

        $wp_customize->add_setting( 'custom_font_family_name', array(
            'default'           => '',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'custom_font_family_name', array(
                    'label'       => __( 'Font Family Name', 'everyware-theme-base-1' ),
                    'description' => __( 'Add the name of the Font Family you are importing. Ex. Merriweather'),
                    'section'     => 'font_family_section',
                    'settings'    => 'custom_font_family_name',
                )
        ) );

        /* ---------------------------------------- Heading Fonts Section ------------------------------------------ */

        $wp_customize->add_section( 'heading_fonts_section', array(
            'title'       => __( 'Heading Font Settings', 'everyware-theme-base-1' ),
            'capability'  => 'edit_theme_options',
            'description' => __( 'Configure Heading Font Settings. This is used on the frontpage category blocks and the section page title.', 'everyware-theme-base-1' ),
            'panel'       => 'fonts_panel'
        ) );

        /* ---------------------------------------------- Heading Font --------------------------------------------- */

        $wp_customize->add_setting( 'heading_font', array(
            'default'           => 'Georgia, Baskerville, Helvetica, Arial, sans-serif',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'heading_font', array(
                    'label'       => __( 'Heading Font', 'everyware-theme-base-1' ),
                    'description' => __( 'Set heading font. Default is "Helvetica Neue",Helvetica,Arial,sans-serif'),
                    'section'     => 'heading_fonts_section',
                    'settings'    => 'heading_font',
                )
        ) );

        /* ---------------------------- Heading Font Size - Frontpage Featured ------------------------------------- */

        $wp_customize->add_setting( 'heading_font_size_front_featured', array(
            'default'           => '24px',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'heading_font_size_front_featured', array(
                    'label'       => __( 'Heading Font Size - Frontpage Featured', 'everyware-theme-base-1' ),
                    'description' => __( 'Set heading font size for the headline on the frontpage slider/featured top. Default is 24px'),
                    'section'     => 'heading_fonts_section',
                    'settings'    => 'heading_font_size_front_featured',
                )
        ) );

        /* Heading Font Size - Frontpage Blocks/Lists Title */
        $wp_customize->add_setting( 'heading_font_size_front_blocks_lists_title', array(
            'default'           => '20px',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'heading_font_size_front_blocks_lists_title', array(
                    'label'       => __( 'Heading Font Size - Frontpage Blocks/Lists Title', 'everyware-theme-base-1' ),
                    'description' => __( 'Set heading font size for the title on the Blocks/Lists. Default is 20px'),
                    'section'     => 'heading_fonts_section',
                    'settings'    => 'heading_font_size_front_blocks_lists_title',
                )
        ) );


        /* Heading Font Size - Teaser Title - Blocks */
        $wp_customize->add_setting( 'heading_font_size_front_teaser_title_blocks', array(
            'default'           => '18px',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'heading_font_size_front_teaser_title_blocks', array(
                    'label'       => __( 'Heading Font Size - Frontpage Teaser Titles - Blocks', 'everyware-theme-base-1' ),
                    'description' => __( 'Set heading font size for the headline on the frontpage teasers for the Category Blocks. Default is 18px'),
                    'section'     => 'heading_fonts_section',
                    'settings'    => 'heading_font_size_front_teaser_title_blocks',
                )
        ) );

        /* Heading Font Size - Teaser Title - Lists - Main */
        $wp_customize->add_setting( 'heading_font_size_front_teaser_title_lists_main', array(
            'default'           => '18px',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'heading_font_size_front_teaser_title_lists_main', array(
                    'label'       => __( 'Heading Font Size - Frontpage Teaser Titles - Lists - Main', 'everyware-theme-base-1' ),
                    'description' => __( 'Set heading font size for the headline on the main story for frontpage teasers for the Lists. Default is 18px'),
                    'section'     => 'heading_fonts_section',
                    'settings'    => 'heading_font_size_front_teaser_title_lists_main',
                )
        ) );

        /* Heading Font Size - Teaser Title - Lists */
        $wp_customize->add_setting( 'heading_font_size_front_teaser_title_lists', array(
            'default'           => '14px',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'heading_font_size_front_teaser_title_lists', array(
                    'label'       => __( 'Heading Font Size - Frontpage Teaser Titles - Lists', 'everyware-theme-base-1' ),
                    'description' => __( 'Set heading font size for the headline on the frontpage teasers for the Lists. Default is 14px'),
                    'section'     => 'heading_fonts_section',
                    'settings'    => 'heading_font_size_front_teaser_title_lists',
                )
        ) );

        /* --------------------------------- Heading Font Size - Section Title ------------------------------------- */

        $wp_customize->add_setting( 'heading_font_size_section_title', array(
            'default'           => '28px',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'heading_font_size_section_title', array(
                    'label'       => __( 'Heading Font Size - Section Title', 'everyware-theme-base-1' ),
                    'description' => __( 'Set heading font size for the section title. Default is 28px'),
                    'section'     => 'heading_fonts_section',
                    'settings'    => 'heading_font_size_section_title',
                )
        ) );

        /* ----------------------------- Heading Font Size - Section Teaser Titles --------------------------------- */

        $wp_customize->add_setting( 'heading_font_size_section_teaser_titles', array(
            'default'           => '22px',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'heading_font_size_section_teaser_titles', array(
                    'label'       => __( 'Heading Font Size - Section Teaser Titles', 'everyware-theme-base-1' ),
                    'description' => __( 'Set heading font size for the section teaser titles. Default is 22px'),
                    'section'     => 'heading_fonts_section',
                    'settings'    => 'heading_font_size_section_teaser_titles',
                )
        ) );

        /* ----------------------------- Heading Font Size - Article Page Headline --------------------------------- */

        $wp_customize->add_setting( 'heading_font_size_articlepageheadline', array(
            'default'           => '34px',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'heading_font_size_articlepageheadline', array(
                    'label'       => __( 'Heading Font Size - Article Page Headline', 'everyware-theme-base-1' ),
                    'description' => __( 'Set heading font size for the article page headline. Default is 34px'),
                    'section'     => 'heading_fonts_section',
                    'settings'    => 'heading_font_size_articlepageheadline',
                )
        ) );

        /* -------------------------------------------- Main Menu Font --------------------------------------------- */

        /* --------------------------------------- Main Menu Fonts Section ----------------------------------------- */

        $wp_customize->add_section( 'main_menu_fonts_section', array(
            'title'       => __( 'Main Menu Font Settings', 'everyware-theme-base-1' ),
            'capability'  => 'edit_theme_options',
            'description' => __( 'Configure Main Menu Font Settings. This is used on the frontpage category blocks and the section page title,', 'everyware-theme-base-1' ),
            'panel'       => 'fonts_panel'
        ) );

        $wp_customize->add_setting( 'main_menu_font', array(
            'default'           => 'Georgia, Baskerville, Helvetica, Arial, sans-serif',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'main_menu_font', array(
                    'label'       => __( 'Main Menu Font', 'everyware-theme-base-1' ),
                    'description' => __( 'Set the menu font. Default is "Georgia, Baskerville, Helvetica, Arial, sans-serif"'),
                    'section'     => 'main_menu_fonts_section',
                    'settings'    => 'main_menu_font',
                )
        ) );

        /* ----------------------------------------- Main Menu Font Size ------------------------------------------- */

        $wp_customize->add_setting( 'main_menu_font_size', array(
            'default'           => '16px',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'main_menu_font_size', array(
                    'label'       => __( 'Main Menu Font Size', 'everyware-theme-base-1' ),
                    'description' => __( 'Set the menu font size. Default is "16px"'),
                    'section'     => 'main_menu_fonts_section',
                    'settings'    => 'main_menu_font_size',
                )
        ) );

        /* -------------------------------------------- Sidebar Menu Font --------------------------------------------- */

        /* --------------------------------------- Sidebar Menu Fonts Section ----------------------------------------- */

        $wp_customize->add_section( 'sidebar_menu_fonts_section', array(
            'title'       => __( 'Sidebar Menu Font Settings', 'everyware-theme-base-1' ),
            'capability'  => 'edit_theme_options',
            'description' => __( 'Configure Main Menu Font Settings. This is used on the frontpage category blocks and the section page title,', 'everyware-theme-base-1' ),
            'panel'       => 'fonts_panel'
        ) );

        /* ----------------------------------------- Sidebar Menu Font Family ------------------------------------------- */

        $wp_customize->add_setting( 'sidebar_menu_font', array(
            'default'           => 'Georgia, Baskerville, Helvetica, Arial, sans-serif',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'sidebar_menu_font', array(
                    'label'       => __( 'Sidebar Menu Font Family', 'everyware-theme-base-1' ),
                    'description' => __( 'Set the sidebar menu font family. Default is "Georgia, Baskerville, Helvetica, Arial, sans-serif"'),
                    'section'     => 'sidebar_menu_fonts_section',
                    'settings'    => 'sidebar_menu_font',
                )
        ) );

        /* ----------------------------------------- Sidebar Menu Font Size ------------------------------------------- */

        $wp_customize->add_setting( 'sidebar_menu_font_size', array(
            'default'           => '16px',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'sidebar_menu_font_size', array(
                    'label'       => __( 'Sidebar Menu Font Size', 'everyware-theme-base-1' ),
                    'description' => __( 'Set the sidebar menu font size. Default is "16px"'),
                    'section'     => 'sidebar_menu_fonts_section',
                    'settings'    => 'sidebar_menu_font_size',
                )
        ) );

        /* --------------------------------------------- Top Bar Font --------------------------------------------- */

        $wp_customize->add_section( 'top_bar_fonts_section', array(
            'title'       => __( 'Top Bar Font Settings', 'everyware-theme-base-1' ),
            'capability'  => 'edit_theme_options',
            'description' => __( 'Configure Top Bar Font Settings. This is for modifying the text for the Top Bar navigation.', 'everyware-theme-base-1' ),
            'panel'       => 'fonts_panel'
        ) );

        $wp_customize->add_setting( 'top_bar_font', array(
            'default'           => 'Georgia, Baskerville, Helvetica, Arial, sans-serif',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'top_bar_font', array(
                    'label'       => __( 'Top Bar Font', 'everyware-theme-base-1' ),
                    'description' => __( 'Set top bar font. Default is "Georgia, Baskerville, Helvetica, Arial, sans-serif'),
                    'section'     => 'top_bar_fonts_section',
                    'settings'    => 'top_bar_font',
                )
        ) );

        /* ------------------------------------------- Top Bar Font Size ------------------------------------------- */

        $wp_customize->add_setting( 'top_bar_font_size', array(
            'default'           => '14px',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'top_bar_font_size', array(
                    'label'       => __( 'Top Bar Font Size', 'everyware-theme-base-1' ),
                    'description' => __( 'Set top bar font size. Default is 14px'),
                    'section'     => 'top_bar_fonts_section',
                    'settings'    => 'top_bar_font_size',
                )
        ) );

        /* ---------------------------------------------- Body Font ----------------------------------------------- */

        $wp_customize->add_section( 'body_fonts_section', array(
            'title'       => __( 'Body Font Settings', 'everyware-theme-base-1' ),
            'capability'  => 'edit_theme_options',
            'description' => __( 'Configure Body Font Settings. This is the text of the frontpage category blocks, section teasers, and the node content.', 'everyware-theme-base-1' ),
            'panel'       => 'fonts_panel'
        ) );

        $wp_customize->add_setting( 'body_font', array(
            'default'           => 'Georgia, Baskerville, Helvetica, Arial, sans-serif',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'body_font', array(
                    'label'       => __( 'Body Font', 'everyware-theme-base-1' ),
                    'description' => __( 'Set body font. Default is "Helvetica Neue",Helvetica,Arial,sans-serif'),
                    'section'     => 'body_fonts_section',
                    'settings'    => 'body_font',
                )
        ) );

        /* -------------------------------- Body Font Size - Frontpage Teasers ------------------------------------ */

        $wp_customize->add_setting( 'body_font_size_frontpage_teasers', array(
            'default'           => '14px',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'body_font_size_frontpage_teasers', array(
                    'label'       => __( 'Body Font Size - Frontpage Teasers', 'everyware-theme-base-1' ),
                    'description' => __( 'Set body font size for the frontpage teasers. Default is 14px'),
                    'section'     => 'body_fonts_section',
                    'settings'    => 'body_font_size_frontpage_teasers',
                )
        ) );

        /* ---------------------------------- Body Font Size - Section Teasers ------------------------------------ */

        $wp_customize->add_setting( 'body_font_size_section_teasers', array(
            'default'           => '14px',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'body_font_size_section_teasers', array(
                    'label'       => __( 'Body Font Size - Section Teasers', 'everyware-theme-base-1' ),
                    'description' => __( 'Set body font size for the section teasers. Default is 14px'),
                    'section'     => 'body_fonts_section',
                    'settings'    => 'body_font_size_section_teasers',
                )
        ) );

        /* ------------------------------------- Body Font Size - Article Posts ----------------------------------- */

        $wp_customize->add_setting( 'body_font_size_article_posts', array(
            'default'           => '14px',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'body_font_size_article_posts', array(
                    'label'       => __( 'Body Font Size - Article Posts', 'everyware-theme-base-1' ),
                    'description' => __( 'Set body font size for the article posts. Default is 14px'),
                    'section'     => 'body_fonts_section',
                    'settings'    => 'body_font_size_article_posts',
                )
        ) );


        /* Footer Font Section */
        $wp_customize->add_section( 'footer_fonts_section', array(
            'title'       => __( 'Footer Font Settings', 'everyware-theme-base-1' ),
            'capability'  => 'edit_theme_options',
            'description' => __( 'Configure Footer Font Settings. This is the text of the menus and the contact information within the footer.', 'everyware-theme-base-1' ),
            'panel'       => 'fonts_panel'
        ) );

        $wp_customize->add_setting( 'footer_font', array(
            'default'           => 'Georgia, Baskerville, Helvetica, Arial, sans-serif',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'footer_font', array(
                    'label'       => __( 'Footer Font', 'everyware-theme-base-1' ),
                    'description' => __( 'Set the footer font. Default is "Georgia, Baskerville, Helvetica, Arial, sans-serif"'),
                    'section'     => 'footer_fonts_section',
                    'settings'    => 'footer_font',
                )
        ) );

        $wp_customize->add_setting( 'footer_font_menu_title_size', array(
            'default'           => '18px',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'footer_font_menu_title_size', array(
                    'label'       => __( 'Footer Font - Menus - Title Size', 'everyware-theme-base-1' ),
                    'description' => __( 'Set the footer font size for the menu titles. Default is 18px.'),
                    'section'     => 'footer_fonts_section',
                    'settings'    => 'footer_font_menu_title_size',
                )
        ) );

        $wp_customize->add_setting( 'footer_font_menu_link_size', array(
            'default'           => '14px',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'footer_font_menu_link_size', array(
                    'label'       => __( 'Footer Font - Menus - Link Size', 'everyware-theme-base-1' ),
                    'description' => __( 'Set the footer font size for the menu links. Default is 14px.'),
                    'section'     => 'footer_fonts_section',
                    'settings'    => 'footer_font_menu_link_size',
                )
        ) );

        $wp_customize->add_setting( 'footer_font_contact_info_size', array(
            'default'           => '12px',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'footer_font_contact_info_size', array(
                    'label'       => __( 'Footer Font - Contact Information - Size', 'everyware-theme-base-1' ),
                    'description' => __( 'Set the footer font size for the contact information. Default is 12px.'),
                    'section'     => 'footer_fonts_section',
                    'settings'    => 'footer_font_contact_info_size',
                )
        ) );

        $wp_customize->add_setting( 'footer_font_social_media_icons_size', array(
            'default'           => '18px',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'footer_font_social_media_icons_size', array(
                    'label'       => __( 'Footer Font - Social Media Icons - Size', 'everyware-theme-base-1' ),
                    'description' => __( 'Set the footer font size for the social media icons. Default is 18px.'),
                    'section'     => 'footer_fonts_section',
                    'settings'    => 'footer_font_social_media_icons_size',
                )
        ) );

        $wp_customize->add_setting( 'footer_font_copyright_size', array(
            'default'           => '12px',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'footer_font_copyright_size', array(
                    'label'       => __( 'Footer Font - Copyright - Size', 'everyware-theme-base-1' ),
                    'description' => __( 'Set the footer font size for the Copyright text. Default is 12px.'),
                    'section'     => 'footer_fonts_section',
                    'settings'    => 'footer_font_copyright_size',
                )
        ) );


        /* --------------------------------------------------------------------------------------------------------- */
        /*                                             END FONT SETTINGS                                             */
        /* --------------------------------------------------------------------------------------------------------- */

        /* --------------------------------------------------------------------------------------------------------- */
        /*                                              TEASER TEMPLATE SETTING                                      */
        /* --------------------------------------------------------------------------------------------------------- */

        $wp_customize->add_panel('teaser_template_panel',array(
            'title'=>'Teaser Template Settings',
        ));

        $wp_customize->add_section('breaking_news_section', array(
            'title'    => __('Breaking News Settings', 'everyware-theme-base-1'),
            'panel'    => 'teaser_template_panel',
            'description' => __('Adjust the settings for the Breaking News section.', 'everyware-theme-base-1')
        ));

        /* Featured Top - Main Image Position */
        $wp_customize->add_setting( 'breaking_news_switch', array(
            'default'           => 'off',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'breaking_news_switch', array(
                'label'       => __( 'Switch', 'everyware-theme-base-1' ),
                'description' => __( 'Choose whether you want the Breaking News section to show on the homepage. If this is turned on, the latest story tagged with the 
                                category "Breaking" will show under the main navigation.'),
                'section'     => 'breaking_news_section',
                'settings'    => 'breaking_news_switch',
                'type'        => 'select',
                'choices'     => array(
                    'on' => __( 'Display', 'everyware-theme-base-1' ),
                    'off'  => __( 'Do Not Display', 'everyware-theme-base-1' ),
                ),
            )
        ) );

        /* Breaking News - Time Limit */
        $wp_customize->add_setting( 'breaking_news_time_limit', array(
            'default'           => 8,
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'breaking_news_time_limit', array(
                'label'       => __( 'Time Limit', 'everyware-theme-base-1' ),
                'description' => __( 'Choose how long the Breaking News section will show after the latest story tagged with the category 
                "Breaking" was published. Set to 0 if you want the Breaking News section to show regardless of time until turned off.'),
                'section'     => 'breaking_news_section',
                'settings'    => 'breaking_news_time_limit',
                'type'        => 'number'
            )
        ) );

        /* Breaking News Template */
        $wp_customize->add_setting( 'breaking_news_template', array(
            'default'           => 'full_length',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'breaking_news_template', array(
                'label'       => __( 'Template', 'everyware-theme-base-1' ),
                'description' => __( 'Choose which Breaking News template to be used if set to "On". "Full Length" will show only the headline and span 
                                    across the length of the page, and "Teaser" will show the full teaser for the story within the container.'),
                'section'     => 'breaking_news_section',
                'settings'    => 'breaking_news_template',
                'type'        => 'select',
                'choices'     => array(
                    'full_length' => __( 'Full Length', 'everyware-theme-base-1' ),
                    'teaser'  => __( 'Teaser', 'everyware-theme-base-1' ),
                ),
            )
        ) );

        $wp_customize->add_section('featured_top_settings_section', array(
            'title'    => __('Featured Top Settings', 'everyware-theme-base-1'),
            'panel'    => 'teaser_template_panel',
            'description' => __('Adjust the settings for the Featured Top template.', 'everyware-theme-base-1')
        ));

        /* Featured Top - Main Image Position */
        $wp_customize->add_setting( 'featured_top_image_position', array(
            'default'           => 'left',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'featured_top_image_position', array(
                'label'       => __( 'Main Story - Image Position', 'everyware-theme-base-1' ),
                'description' => __( 'Choose whether you want the image to be on the left side, right side, or above the story data for the Featured Top template.'),
                'section'     => 'featured_top_settings_section',
                'settings'    => 'featured_top_image_position',
                'type'        => 'select',
                'choices'     => array(
                    'right' => __( 'Right', 'everyware-theme-base-1' ),
                    'left'  => __( 'Left', 'everyware-theme-base-1' ),
                    'above'  => __( 'Above', 'everyware-theme-base-1' ),
                ),
            )
        ) );

        /* Featured Top - Show Text */
        $wp_customize->add_setting( 'featured_top_main_show_text', array(
            'default'           => 'true',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'featured_top_main_show_text', array(
                'label'       => __( 'Main Story - Show Text', 'everyware-theme-base-1' ),
                'description' => __( 'Choose whether you want to display the story summary/text.'),
                'section'     => 'featured_top_settings_section',
                'settings'    => 'featured_top_main_show_text',
                'type'        => 'select',
                'choices'     => array(
                    'true' => __( 'Display', 'everyware-theme-base-1' ),
                    'false'  => __( 'Do Not Display', 'everyware-theme-base-1' ),
                ),
            )
        ) );

        /* Featured Top - Show Author */
        $wp_customize->add_setting( 'featured_top_main_show_author', array(
            'default'           => 'true',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'featured_top_main_show_author', array(
                'label'       => __( 'Main Story - Show Author', 'everyware-theme-base-1' ),
                'description' => __( 'Choose whether you want to display the story author.'),
                'section'     => 'featured_top_settings_section',
                'settings'    => 'featured_top_main_show_author',
                'type'        => 'select',
                'choices'     => array(
                    'true' => __( 'Display', 'everyware-theme-base-1' ),
                    'false'  => __( 'Do Not Display', 'everyware-theme-base-1' ),
                ),
            )
        ) );

        /* Featured Top - Show Since Published */
        $wp_customize->add_setting( 'featured_top_main_show_since_published', array(
            'default'           => 'true',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'featured_top_main_show_since_published', array(
                'label'       => __( 'Main Story - Show Since Published', 'everyware-theme-base-1' ),
                'description' => __( 'Choose whether you want to display the story since published time.'),
                'section'     => 'featured_top_settings_section',
                'settings'    => 'featured_top_main_show_since_published',
                'type'        => 'select',
                'choices'     => array(
                    'true' => __( 'Display', 'everyware-theme-base-1' ),
                    'false'  => __( 'Do Not Display', 'everyware-theme-base-1' ),
                ),
            )
        ) );

        /* Featured Top - Bottom Image Position */
        $wp_customize->add_setting( 'featured_top_bottom_image_position', array(
            'default'           => 'above',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'featured_top_bottom_image_position', array(
                'label'       => __( 'Bottom Image Position', 'everyware-theme-base-1' ),
                'description' => __( 'Choose whether you want the image to be above, or on left or right side of the Featured Top bottom stories.'),
                'section'     => 'featured_top_settings_section',
                'settings'    => 'featured_top_bottom_image_position',
                'type'        => 'select',
                'choices'     => array(
                    'above' => __( 'Above', 'everyware-theme-base-1' ),
                    'right' => __( 'Right', 'everyware-theme-base-1' ),
                    'left'  => __( 'Left', 'everyware-theme-base-1' ),
                ),
            )
        ) );

        $wp_customize->add_section('featured_slider_settings_section', array(
            'title'    => __('Featured Slider Settings', 'everyware-theme-base-1'),
            'panel'    => 'teaser_template_panel',
            'description' => __('Adjust the settings for the Featured Slider template.', 'everyware-theme-base-1')
        ));

        /* Slider - Caption Display */
        $wp_customize->add_setting( 'featured_slider_headline_location', array(
            'default'           => 'above',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'featured_slider_headline_location', array(
                'label'       => __( 'Headline Location', 'everyware-theme-base-1' ),
                'description' => __( 'Choose if you want the headline to appear above or below the slider.'),
                'section'     => 'featured_slider_settings_section',
                'settings'    => 'featured_slider_headline_location',
                'type'        => 'select',
                'choices'     => array(
                    'above' => __( 'Above', 'everyware-theme-base-1' ),
                    'below' => __( 'Below', 'everyware-theme-base-1' ),
                ),
            )
        ) );

        /* Slider - Caption Display */
        $wp_customize->add_setting( 'featured_slider_caption_display', array(
            'default'           => 'false',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'featured_slider_caption_display', array(
                'label'       => __( 'Caption', 'everyware-theme-base-1' ),
                'description' => __( 'Choose if you want the story summary or beginning of story text to appear under the slider in the Featured Slider template.'),
                'section'     => 'featured_slider_settings_section',
                'settings'    => 'featured_slider_caption_display',
                'type'        => 'select',
                'choices'     => array(
                    'true' => __( 'Display', 'everyware-theme-base-1' ),
                    'false' => __( 'Do Not Display', 'everyware-theme-base-1' ),
                ),
            )
        ) );


        /**************************************** CATEGORY BLOCKS SECTION *********************************************/

        $wp_customize->add_section('category_blocks_section', array(
            'title'    => __('Category Blocks', 'everyware-theme-base-1'),
            'panel'    => 'teaser_template_panel',
            'description' => __('Adjust the settings for the Category Blocks template.', 'everyware-theme-base-1')
        ));

        /* Main Category Block */
        $wp_customize->add_setting( 'category_block_main_category_display', array(
            'default'           => 'true',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'category_block_main_category_display', array(
                'label'       => __( 'Category - Main Story', 'everyware-theme-base-1' ),
                'description' => __( 'Choose whether you want to show the category above the main story on the Category Block template.'),
                'section'     => 'category_blocks_section',
                'settings'    => 'category_block_main_category_display',
                'type'        => 'select',
                'choices'     => array(
                    'true' => __( 'Display', 'everyware-theme-base-1' ),
                    'false'  => __( 'Do Not Display', 'everyware-theme-base-1' ),
                ),
            )
        ) );

        $wp_customize->add_setting('category_block_main_summary_display', array(
            'default'           => 'true',
            'capability'        => 'edit_theme_options',
            'sanitize_callback' => 'sanitize_text_field'
        ));

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'category_block_main_summary_display', array(
                    'label'       => __( 'Summary - Main Story', 'everyware-theme-base-1' ),
                    'description' => __( 'Choose whether you want to show the summary below the main story on the Category Block template.'),
                    'section'     => 'category_blocks_section',
                    'settings'    => 'category_block_main_summary_display',
                    'type'        => 'select',
                    'choices'     => array(
                        'true' => __( 'Display', 'everyware-theme-base-1' ),
                        'false'  => __( 'Do Not Display', 'everyware-theme-base-1' ),
                    ),
                )
            )
        );    

        // ERD 2018-03-07
        // Main story - author
        $wp_customize->add_setting('category_block_main_author_display', array(
            'default'           => 'false',
            'capability'        => 'edit_theme_options',
            'sanitize_callback' => 'sanitize_text_field'
        ));

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'category_block_main_author_display', array(
                    'label'       => __( 'Author - Main Story', 'everyware-theme-base-1' ),
                    'description' => __( 'Choose whether you want to show the author below the main story on the Category Block template.'),
                    'section'     => 'category_blocks_section',
                    'settings'    => 'category_block_main_author_display',
                    'type'        => 'select',
                    'choices'     => array(
                        'true' => __( 'Display', 'everyware-theme-base-1' ),
                        'false'  => __( 'Do Not Display', 'everyware-theme-base-1' ),
                    ),
                )
            )
        );    

        $wp_customize->add_setting('category_block_main_time_display', array(
            'default'           => 'off',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ));

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'category_block_main_time_display', array(
                    'label'       => __( 'Date/Time - Main Story', 'everyware-theme-base-1' ),
                    'description' => __( 'Choose whether you want to show the time below the main story on the Category Block template.'),
                    'section'     => 'category_blocks_section',
                    'settings'    => 'category_block_main_time_display',
                    'type'        => 'select',
                    'choices'     => array(
                        'true'      => __( 'Display', 'everyware-theme-base-1' ),
                        'false'     => __( 'Do Not Display', 'everyware-theme-base-1' ),
                    ),
                )
            )
        );

        // ERD 2018-03-07
        // Main story - Time since published
        $wp_customize->add_setting('category_block_main_time_diff_display', array(
            'default'           => 'false',
            'capability'        => 'edit_theme_options',
            'sanitize_callback' => 'sanitize_text_field'
        ));

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'category_block_main_time_diff_display', array(
                    'label'       => __( 'Show Time Since Published - Main Story', 'everyware-theme-base-1' ),
                    'description' => __( 'Choose whether you want to show the time since story was published on the Category Block template instead of a timestamp. Ex. 2 hours ago. '),
                    'section'     => 'category_blocks_section',
                    'settings'    => 'category_block_main_time_diff_display',
                    'type'        => 'select',
                    'choices'     => array(
                        'true' => __( 'Display', 'everyware-theme-base-1' ),
                        'false'  => __( 'Do Not Display', 'everyware-theme-base-1' ),
                    ),
                )
            )
        );  

        /* Category Block - Side  */
        $wp_customize->add_setting( 'category_block_side_category_display', array(
            'default'           => 'true',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'category_block_side_category_display', array(
                'label'       => __( 'Category - Side Stories', 'everyware-theme-base-1' ),
                'description' => __( 'Choose whether you want to show the category above the side stories on the Category Block template.'),
                'section'     => 'category_blocks_section',
                'settings'    => 'category_block_side_category_display',
                'type'        => 'select',
                'choices'     => array(
                    'true' => __( 'Display', 'everyware-theme-base-1' ),
                    'false'  => __( 'Do Not Display', 'everyware-theme-base-1' ),
                ),
            )
        ) );


        $wp_customize->add_setting('category_block_side_summary_display', array(
            'default'           => 'true',
            'capability'        => 'edit_theme_options',
            'sanitize_callback' => 'sanitize_text_field'
        ));

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'category_block_side_summary_display', array(
                    'label'       => __( 'Summary - Side Stories', 'everyware-theme-base-1' ),
                    'description' => __( 'Choose whether you want to show the summary below the side stories on the Category Block template.'),
                    'section'     => 'category_blocks_section',
                    'settings'    => 'category_block_side_summary_display',
                    'type'        => 'select',
                    'choices'     => array(
                        'true' => __( 'Display', 'everyware-theme-base-1' ),
                        'false'  => __( 'Do Not Display', 'everyware-theme-base-1' ),
                    ),
                )
            )
        );    

        // ERD 2018-03-07
        // Side story - author
        $wp_customize->add_setting('category_block_side_author_display', array(
            'default'           => 'false',
            'capability'        => 'edit_theme_options',
            'sanitize_callback' => 'sanitize_text_field'
        ));

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'category_block_side_author_display', array(
                    'label'       => __( 'Author - Side Story', 'everyware-theme-base-1' ),
                    'description' => __( 'Choose whether you want to show the author below the side story on the Category Block template.'),
                    'section'     => 'category_blocks_section',
                    'settings'    => 'category_block_side_author_display',
                    'type'        => 'select',
                    'choices'     => array(
                        'true' => __( 'Display', 'everyware-theme-base-1' ),
                        'false'  => __( 'Do Not Display', 'everyware-theme-base-1' ),
                    ),
                )
            )
        );  

        $wp_customize->add_setting('category_block_side_time_display', array(
            'default'           => 'true',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ));

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'category_block_side_time_display', array(
                    'label'       => __( 'Date/Time - Side Stories', 'everyware-theme-base-1' ),
                    'description' => __( 'Choose whether you want to show the time below the side stories on the Category Block template.'),
                    'section'     => 'category_blocks_section',
                    'settings'    => 'category_block_side_time_display',
                    'type'        => 'select',
                    'choices'     => array(
                        'true'      => __( 'Display', 'everyware-theme-base-1' ),
                        'false'     => __( 'Do Not Display', 'everyware-theme-base-1' ),
                    ),
                )
            )
        );

        // ERD 2018-03-07
        // Side story - Time since published
        $wp_customize->add_setting('category_block_side_time_diff_display', array(
            'default'           => 'false',
            'capability'        => 'edit_theme_options',
            'sanitize_callback' => 'sanitize_text_field'
        ));

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'category_block_side_time_diff_display', array(
                    'label'       => __( 'Time Since Published - Side Story', 'everyware-theme-base-1' ),
                    'description' => __( 'Choose whether you want to show the time since story was published on the Category Block template instead of a timestamp. Ex. 2 hours ago. '),
                    'section'     => 'category_blocks_section',
                    'settings'    => 'category_block_side_time_diff_display',
                    'type'        => 'select',
                    'choices'     => array(
                        'true' => __( 'Display', 'everyware-theme-base-1' ),
                        'false'  => __( 'Do Not Display', 'everyware-theme-base-1' ),
                    ),
                )
            )
        );  

        /**************************************** END CATEGORY BLOCKS SECTION *********************************************/


        /**************************************** CATEGORY BLOCKS - OPINION SECTION *********************************************/

        $wp_customize->add_section('category_block_opinion_section', array(
            'title'    => __('Category Blocks - Opinion', 'everyware-theme-base-1'),
            'panel'    => 'teaser_template_panel',
            'description' => __('Adjust the settings for the Category Blocks - Opinion template.', 'everyware-theme-base-1')
        ));

        /* Main Category Block */
        $wp_customize->add_setting( 'category_block_opinion_main_category_display', array(
            'default'           => 'true',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'category_block_opinion_main_category_display', array(
                'label'       => __( 'Category - Main Story', 'everyware-theme-base-1' ),
                'description' => __( 'Choose whether you want to show the category above the main story on the Category Block - Opinion template.'),
                'section'     => 'category_block_opinion_section',
                'settings'    => 'category_block_opinion_main_category_display',
                'type'        => 'select',
                'choices'     => array(
                    'true' => __( 'Display', 'everyware-theme-base-1' ),
                    'false'  => __( 'Do Not Display', 'everyware-theme-base-1' ),
                ),
            )
        ) );

        $wp_customize->add_setting('category_block_opinion_main_summary_display', array(
            'default'           => 'true',
            'capability'        => 'edit_theme_options',
            'sanitize_callback' => 'sanitize_text_field'
        ));

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'category_block_opinion_main_summary_display', array(
                    'label'       => __( 'Summary - Main Story', 'everyware-theme-base-1' ),
                    'description' => __( 'Choose whether you want to show the summary below the main story on the Category Block - Opinion template.'),
                    'section'     => 'category_block_opinion_section',
                    'settings'    => 'category_block_opinion_main_summary_display',
                    'type'        => 'select',
                    'choices'     => array(
                        'true' => __( 'Display', 'everyware-theme-base-1' ),
                        'false'  => __( 'Do Not Display', 'everyware-theme-base-1' ),
                    ),
                )
            )
        );    

        // ERD 2018-03-07
        // Main story - author
        $wp_customize->add_setting('category_block_opinion_main_author_display', array(
            'default'           => 'false',
            'capability'        => 'edit_theme_options',
            'sanitize_callback' => 'sanitize_text_field'
        ));

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'category_block_opinion_main_author_display', array(
                    'label'       => __( 'Author - Main Story', 'everyware-theme-base-1' ),
                    'description' => __( 'Choose whether you want to show the author below the main story on the Category Block - Opinion template.'),
                    'section'     => 'category_block_opinion_section',
                    'settings'    => 'category_block_opinion_main_author_display',
                    'type'        => 'select',
                    'choices'     => array(
                        'true' => __( 'Display', 'everyware-theme-base-1' ),
                        'false'  => __( 'Do Not Display', 'everyware-theme-base-1' ),
                    ),
                )
            )
        );    

        $wp_customize->add_setting('category_block_opinion_main_time_display', array(
            'default'           => 'off',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ));

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'category_block_opinion_main_time_display', array(
                    'label'       => __( 'Date/Time - Main Story', 'everyware-theme-base-1' ),
                    'description' => __( 'Choose whether you want to show the time below the main story on the Category Block - Opinion template.'),
                    'section'     => 'category_block_opinion_section',
                    'settings'    => 'category_block_opinion_main_time_display',
                    'type'        => 'select',
                    'choices'     => array(
                        'true'      => __( 'Display', 'everyware-theme-base-1' ),
                        'false'     => __( 'Do Not Display', 'everyware-theme-base-1' ),
                    ),
                )
            )
        );

        // ERD 2018-03-07
        // Main story - Time since published
        $wp_customize->add_setting('category_block_opinion_main_time_diff_display', array(
            'default'           => 'false',
            'capability'        => 'edit_theme_options',
            'sanitize_callback' => 'sanitize_text_field'
        ));

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'category_block_opinion_main_time_diff_display', array(
                    'label'       => __( 'Time Since Published - Main Story', 'everyware-theme-base-1' ),
                    'description' => __( 'Choose whether you want to show the time since story was published on the Category Block - Opinion template instead of a timestamp. Ex. 2 hours ago.'),
                    'section'     => 'category_block_opinion_section',
                    'settings'    => 'category_block_opinion_main_time_diff_display',
                    'type'        => 'select',
                    'choices'     => array(
                        'true' => __( 'Display', 'everyware-theme-base-1' ),
                        'false'  => __( 'Do Not Display', 'everyware-theme-base-1' ),
                    ),
                )
            )
        );  

        /* Category Block - Side  */
        $wp_customize->add_setting( 'category_block_opinion_side_category_display', array(
            'default'           => 'true',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'category_block_opinion_side_category_display', array(
                'label'       => __( 'Category - Side Stories', 'everyware-theme-base-1' ),
                'description' => __( 'Choose whether you want to show the category above the side stories on the Category Block - Opinion template.'),
                'section'     => 'category_block_opinion_section',
                'settings'    => 'category_block_opinion_side_category_display',
                'type'        => 'select',
                'choices'     => array(
                    'true' => __( 'Display', 'everyware-theme-base-1' ),
                    'false'  => __( 'Do Not Display', 'everyware-theme-base-1' ),
                ),
            )
        ) );

        $wp_customize->add_setting('category_block_opinion_side_summary_display', array(
            'default'           => 'true',
            'capability'        => 'edit_theme_options',
            'sanitize_callback' => 'sanitize_text_field'
        ));

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'category_block_opinion_side_summary_display', array(
                    'label'       => __( 'Summary - Side Stories', 'everyware-theme-base-1' ),
                    'description' => __( 'Choose whether you want to show the summary below the side stories on the Category Block - Opinion template.'),
                    'section'     => 'category_block_opinion_section',
                    'settings'    => 'category_block_opinion_side_summary_display',
                    'type'        => 'select',
                    'choices'     => array(
                        'true' => __( 'Display', 'everyware-theme-base-1' ),
                        'false'  => __( 'Do Not Display', 'everyware-theme-base-1' ),
                    ),
                )
            )
        );    

        // ERD 2018-03-07
        // Side story - author
        $wp_customize->add_setting('category_block_opinion_side_author_display', array(
            'default'           => 'false',
            'capability'        => 'edit_theme_options',
            'sanitize_callback' => 'sanitize_text_field'
        ));

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'category_block_opinion_side_author_display', array(
                    'label'       => __( 'Author - Side Story', 'everyware-theme-base-1' ),
                    'description' => __( 'Choose whether you want to show the author below the side story on the Category Block - Opinion template.'),
                    'section'     => 'category_block_opinion_section',
                    'settings'    => 'category_block_opinion_side_author_display',
                    'type'        => 'select',
                    'choices'     => array(
                        'true' => __( 'Display', 'everyware-theme-base-1' ),
                        'false'  => __( 'Do Not Display', 'everyware-theme-base-1' ),
                    ),
                )
            )
        );  

        $wp_customize->add_setting('category_block_opinion_side_time_display', array(
            'default'           => 'true',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ));

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'category_block_opinion_side_time_display', array(
                    'label'       => __( 'Date/Time - Side Stories', 'everyware-theme-base-1' ),
                    'description' => __( 'Choose whether you want to show the time below the side stories on the Category Block - Opinion template.'),
                    'section'     => 'category_block_opinion_section',
                    'settings'    => 'category_block_opinion_side_time_display',
                    'type'        => 'select',
                    'choices'     => array(
                        'true'      => __( 'Display', 'everyware-theme-base-1' ),
                        'false'     => __( 'Do Not Display', 'everyware-theme-base-1' ),
                    ),
                )
            )
        );

        // ERD 2018-03-07
        // Side story - Time since published
        $wp_customize->add_setting('category_block_opinion_side_time_diff_display', array(
            'default'           => 'false',
            'capability'        => 'edit_theme_options',
            'sanitize_callback' => 'sanitize_text_field'
        ));

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'category_block_opinion_side_time_diff_display', array(
                    'label'       => __( 'Show Time Since Published - Side Story', 'everyware-theme-base-1' ),
                    'description' => __( 'Choose whether you want to show the time since story was published on the Category Block - Opinion template template instead of a timestamp. Ex. 2 hours ago.'),
                    'section'     => 'category_block_opinion_section',
                    'settings'    => 'category_block_opinion_side_time_diff_display',
                    'type'        => 'select',
                    'choices'     => array(
                        'true' => __( 'Display', 'everyware-theme-base-1' ),
                        'false'  => __( 'Do Not Display', 'everyware-theme-base-1' ),
                    ),
                )
            )
        );  

        /**************************************** END CATEGORY BLOCKS - OPINION SECTION *********************************************/

        /**************************************** START SECTION TEASER - ONE COLUMN SECTION *********************************************/

        $wp_customize->add_section('section_teaser_one_column_section', array(
            'title'    => __('Section Teaser - One Column', 'everyware-theme-base-1'),
            'panel'    => 'teaser_template_panel',
            'description' => __('Adjust the settings for the Section Teaser - One Column template.', 'everyware-theme-base-1')
        ));

        /* Thin Teaser */
        $wp_customize->add_setting( 'section_teaser_one_column_category_overlay_display', array(
            'default'           => 'false',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'section_teaser_one_column_category_overlay_display', array(
                'label'       => __( 'Category Overlay', 'everyware-theme-base-1' ),
                'description' => __( 'Choose whether you want to show the category of the story overlaying the image for the teaser.'),
                'section'     => 'section_teaser_one_column_section',
                'settings'    => 'section_teaser_one_column_category_overlay_display',
                'type'        => 'select',
                'choices'     => array(
                    'true' => __( 'Display', 'everyware-theme-base-1' ),
                    'false'  => __( 'Do Not Display', 'everyware-theme-base-1' ),
                ),
            )
        ) );

        $wp_customize->add_setting( 'section_teaser_one_column_category_overlay_background_color' , array(
            'default'   => '#1e0745',
            'transport' => 'refresh',
            'sanitize_callback' => 'sanitize_hex_color'
        ) );

        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'section_teaser_one_column_category_overlay_background_color', array(
            'label'    => __( 'Category Overlay Background Color', 'everyware-theme-base-1' ),
            'description' => __( 'Set the background color for overlay of the category if enabled.'),
            'section'  => 'section_teaser_one_column_section',
            'settings' => 'section_teaser_one_column_category_overlay_background_color',
        ) ) );

        $wp_customize->add_setting( 'section_teaser_one_column_category_overlay_text_color' , array(
            'default'   => '#ffffff',
            'transport' => 'refresh',
            'sanitize_callback' => 'sanitize_hex_color'
        ) );

        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'section_teaser_one_column_category_overlay_text_color', array(
            'label'    => __( 'Category Overlay Text Color', 'everyware-theme-base-1' ),
            'description' => __( 'Set the text color for overlay of the category if enabled.'),
            'section'  => 'section_teaser_one_column_section',
            'settings' => 'section_teaser_one_column_category_overlay_text_color',
        ) ) );

        /**************************************** END SECTION TEASER - ONE COLUMN SECTION *********************************************/

        $wp_customize->add_section('thin_teaser_section', array(
            'title'    => __('Thin Teasers', 'everyware-theme-base-1'),
            'panel'    => 'teaser_template_panel',
            'description' => __('Adjust the settings for the Thin Teasers template.', 'everyware-theme-base-1')
        ));

        /* Thin Teaser */
        $wp_customize->add_setting( 'thin_teaser_category_display', array(
            'default'           => 'true',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'thin_teaser_category_display', array(
                'label'       => __( 'Category', 'everyware-theme-base-1' ),
                'description' => __( 'Choose whether you want to show the category above the story on the Thin Teaser template.'),
                'section'     => 'thin_teaser_section',
                'settings'    => 'thin_teaser_category_display',
                'type'        => 'select',
                'choices'     => array(
                    'true' => __( 'Display', 'everyware-theme-base-1' ),
                    'false'  => __( 'Do Not Display', 'everyware-theme-base-1' ),
                ),
            )
        ) );

        $wp_customize->add_setting('thin_teaser_summary_display', array(
            'default'           => 'true',
            'capability'        => 'edit_theme_options',
            'sanitize_callback' => 'sanitize_text_field'
        ));

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'thin_teaser_summary_display', array(
                    'label'       => __( 'Summary', 'everyware-theme-base-1' ),
                    'description' => __( 'Choose whether you want to show the summary below the story on the Thin Teaser template.'),
                    'section'     => 'thin_teaser_section',
                    'settings'    => 'thin_teaser_summary_display',
                    'type'        => 'select',
                    'choices'     => array(
                        'true' => __( 'Display', 'everyware-theme-base-1' ),
                        'false'  => __( 'Do Not Display', 'everyware-theme-base-1' ),
                    ),
                )
            )
        );    

        $wp_customize->add_setting('thin_teaser_author_display', array(
            'default'           => 'false',
            'capability'        => 'edit_theme_options',
            'sanitize_callback' => 'sanitize_text_field'
        ));

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'thin_teaser_author_display', array(
                    'label'       => __( 'Author', 'everyware-theme-base-1' ),
                    'description' => __( 'Choose whether you want to show the author below the story on the Thin Teaser template.'),
                    'section'     => 'thin_teaser_section',
                    'settings'    => 'thin_teaser_author_display',
                    'type'        => 'select',
                    'choices'     => array(
                        'true' => __( 'Display', 'everyware-theme-base-1' ),
                        'false'  => __( 'Do Not Display', 'everyware-theme-base-1' ),
                    ),
                )
            )
        );    

        $wp_customize->add_setting('thin_teaser_time_display', array(
            'default'           => 'true',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ));

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'thin_teaser_time_display', array(
                    'label'       => __( 'Date/Time', 'everyware-theme-base-1' ),
                    'description' => __( 'Choose whether you want to show the time below the story on the Thin Teaser template.'),
                    'section'     => 'thin_teaser_section',
                    'settings'    => 'thin_teaser_time_display',
                    'type'        => 'select',
                    'choices'     => array(
                        'true'      => __( 'Display', 'everyware-theme-base-1' ),
                        'false'     => __( 'Do Not Display', 'everyware-theme-base-1' ),
                    ),
                )
            )
        );

        $wp_customize->add_setting('thin_teaser_time_diff_display', array(
            'default'           => 'false',
            'capability'        => 'edit_theme_options',
            'sanitize_callback' => 'sanitize_text_field'
        ));

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'thin_teaser_time_diff_display', array(
                    'label'       => __( 'Time Since Published', 'everyware-theme-base-1' ),
                    'description' => __( 'Choose whether you want to show the time since story was published on the Thin Teaser template instead of a timestamp. Ex. 2 hours ago.'),
                    'section'     => 'thin_teaser_section',
                    'settings'    => 'thin_teaser_time_diff_display',
                    'type'        => 'select',
                    'choices'     => array(
                        'true' => __( 'Display', 'everyware-theme-base-1' ),
                        'false'  => __( 'Do Not Display', 'everyware-theme-base-1' ),
                    ),
                )
            )
        );  

        /* --------------------------------------------------------------------------------------------------------- */
        /*                                            END TEASER TEMPLATE SETTINGS                                   */
        /* --------------------------------------------------------------------------------------------------------- */   

        /* --------------------------------------------------------------------------------------------------------- */
        /*                                             THEME LAYOUT SETTINGS                                         */
        /* --------------------------------------------------------------------------------------------------------- */

        $wp_customize->add_panel('custom_theme_layout_settings',array(
            'title'=>'Theme Layout Settings',
        ));



        /* --------------------------------------- Custom Top Bar Settings ----------------------------------------- */

        $wp_customize->add_section( 'custom_layout_topbar_settings', array(
            'title'       => __( 'Top Bar Settings', 'everyware-theme-base-1' ),
            'capability'  => 'edit_theme_options',
            'description' => __( 'Custom Top Bar Settings', 'everyware-theme-base-1' ),
            'panel'       => 'custom_theme_layout_settings'
        ) );


        /* --------------------------------------- Top Bar Switch ----------------------------------------- */

        $wp_customize->add_setting( 'topbar_switch', array(
            'default'           => 'on',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'topbar_switch', array(
                'label'       => __( 'Enable Top Bar', 'everyware-theme-base-1' ),
                'description' => __( 'Choose whether to enable the Top Bar region and be able to use the corresponding settings.'),
                'section'     => 'custom_layout_topbar_settings',
                'settings'    => 'topbar_switch',
                'type'        => 'select',
                'choices'     => array(
                    'on' => __( 'On', 'everyware-theme-base-1' ),
                    'off'  => __( 'Off', 'everyware-theme-base-1' ),
                ),
            )
        ) );

        /* --------------------------------------- Top Bar - Full Width ----------------------------------------- */

        $wp_customize->add_setting( 'topbar_full_width', array(
            'default'           => 'container',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'topbar_full_width', array(
                'label'       => __( 'Full Width/Container', 'everyware-theme-base-1' ),
                'description' => __( 'Choose whether to keep top bar content within container, or to take the full width of the top bar.'),
                'section'     => 'custom_layout_topbar_settings',
                'settings'    => 'topbar_full_width',
                'type'        => 'select',
                'choices'     => array(
                    'container' => __( 'Container', 'everyware-theme-base-1' ),
                    'full_width'  => __( 'Full Width', 'everyware-theme-base-1' ),
                ),
            )
        ) );

        /* --------------------------------------- Top Bar - Login Data Position ----------------------------------------- */

        $wp_customize->add_setting( 'topbar_login_data_position', array(
            'default'           => 'left',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'topbar_login_data_position', array(
                'label'       => __( 'Login Links - Position', 'everyware-theme-base-1' ),
                'description' => __( 'Choose whether to have the Login Links("Welcome, [Username]","Logout") to show on the Top Left or Top Right Menu. (NOTE: The corresponding Menu must be turned on for the links to show)'),
                'section'     => 'custom_layout_topbar_settings',
                'settings'    => 'topbar_login_data_position',
                'type'        => 'select',
                'choices'     => array(
                    'left' => __( 'Left Top Bar Menu', 'everyware-theme-base-1' ),
                    'right'  => __( 'Right Top Bar Menu', 'everyware-theme-base-1' ),
                ),
            )
        ) );
 

        /* --------------------------------------- Top Bar - Sticky Toggle ----------------------------------------- */

        $wp_customize->add_setting( 'topbar_sticky_toggle', array(
            'default'           => 'on',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
            $wp_customize,
            'topbar_sticky_toggle', array(
                'label'       => __( 'Sticky Top Bar', 'everyware-theme-base-1' ),
                'description' => __( 'Make the top bar sticky. If on, the top bar remains at the top of the page on scroll. Default is "On". (NOTE: The top bar cannot be sticky while the main navigation bar is sticky)'),
                'section'     => 'custom_layout_topbar_settings',
                'settings'    => 'topbar_sticky_toggle',
                'type'        => 'select',
                'choices'     => array(
                    'on' => __( 'On', 'everyware-theme-base-1' ),
                    'off'  => __( 'Off', 'everyware-theme-base-1' ),
                ),
            )
        ) );

        /* --------------------------------------- Top Bar - Left - Toggle ----------------------------------------- */

        $wp_customize->add_setting( 'topbar_left_toggle', array(
            'default'           => 'off',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'topbar_left_toggle', array(
                'label'       => __( 'Left Section Option', 'everyware-theme-base-1' ),
                'description' => __( 'Choose to have a top bar left section. This could be a Widget Area or a Menu based on the setting following.'),
                'section'     => 'custom_layout_topbar_settings',
                'settings'    => 'topbar_left_toggle',
                'type'        => 'select',
                'choices'     => array(
                    'on' => __( 'On', 'everyware-theme-base-1' ),
                    'off'  => __( 'Off', 'everyware-theme-base-1' ),
                ),
            )
        ) );

        $topbar_left_toggle = get_theme_mod( 'topbar_left_toggle' );

        if ($topbar_left_toggle == 'on'){
            /* ------------------------------------- Top Bar - Left - Choice --------------------------------------- */
            $wp_customize->add_setting( 'topbar_left_choice', array(
                'default'           => 'menu',
                'type'              => 'theme_mod',
                'capability'        => 'edit_theme_options',
            ) );

            $wp_customize->add_control(
                new WP_Customize_Control(
                    $wp_customize,
                    'topbar_left_choice', array(
                    'label'       => __( 'Left Section Option Choice', 'everyware-theme-base-1' ),
                    'description' => __( 'Choose to have a Widget Area or a Menu in the left Top Bar section.'),
                    'section'     => 'custom_layout_topbar_settings',
                    'settings'    => 'topbar_left_choice',
                    'type'        => 'select',
                    'choices'     => array(
                        'menu' => __( 'Menu', 'everyware-theme-base-1' ),
                        'widget_area'  => __( 'Widget Area', 'everyware-theme-base-1' ),
                    ),
                )
            ) );
        }

        /* ------------------------------------- Top Bar - Center - Choice ---------------------------------------- */

        $wp_customize->add_setting( 'topbar_center_toggle', array(
            'default'           => 'off',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'topbar_center_toggle', array(
                'label'       => __( 'Center Section Option', 'everyware-theme-base-1' ),
                'description' => __( 'Choose to have a top bar center section. This could be a Widget Area or a Menu based on the setting following.'),
                'section'     => 'custom_layout_topbar_settings',
                'settings'    => 'topbar_center_toggle',
                'type'        => 'select',
                'choices'     => array(
                    'on' => __( 'On', 'everyware-theme-base-1' ),
                    'off'  => __( 'Off', 'everyware-theme-base-1' ),
                ),
            )
        ) );

        $topbar_center_toggle = get_theme_mod( 'topbar_center_toggle' );

        if ($topbar_center_toggle == 'on'){
        /* Top Bar - Center - Choice */
            $wp_customize->add_setting( 'topbar_center_choice', array(
                'default'           => 'menu',
                'type'              => 'theme_mod',
                'capability'        => 'edit_theme_options',
            ) );

            $wp_customize->add_control(
                new WP_Customize_Control(
                    $wp_customize,
                    'topbar_center_choice', array(
                    'label'       => __( 'Center Section Option Choice', 'everyware-theme-base-1' ),
                    'description' => __( 'Choose to have a Widget Area or a Menu in the center Top Bar section.'),
                    'section'     => 'custom_layout_topbar_settings',
                    'settings'    => 'topbar_center_choice',
                    'type'        => 'select',
                    'choices'     => array(
                        'menu' => __( 'Menu', 'everyware-theme-base-1' ),
                        'widget_area'  => __( 'Widget Area', 'everyware-theme-base-1' ),
                    ),
                )
            ) );
        }

        /* ------------------------------------- Top Bar - Right - Choice ---------------------------------------- */

        $wp_customize->add_setting( 'topbar_right_toggle', array(
            'default'           => 'off',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'topbar_right_toggle', array(
                'label'       => __( 'Right Section Option', 'everyware-theme-base-1' ),
                'description' => __( 'Choose to have a top bar right section. This could be a Widget Area or a Menu based on the setting following.'),
                'section'     => 'custom_layout_topbar_settings',
                'settings'    => 'topbar_right_toggle',
                'type'        => 'select',
                'choices'     => array(
                    'on' => __( 'On', 'everyware-theme-base-1' ),
                    'off'  => __( 'Off', 'everyware-theme-base-1' ),
                ),
            )
        ) );


        $topbar_right_toggle = get_theme_mod( 'topbar_right_toggle' );

        if ($topbar_right_toggle == 'on'){
            /* Top Bar - Right - Choice */
            $wp_customize->add_setting( 'topbar_right_choice', array(
                'default'           => 'menu',
                'type'              => 'theme_mod',
                'capability'        => 'edit_theme_options',
            ) );

            $wp_customize->add_control(
                new WP_Customize_Control(
                    $wp_customize,
                    'topbar_right_choice', array(
                    'label'       => __( 'Right Section Option Choice', 'everyware-theme-base-1' ),
                    'description' => __( 'Choose to have a Widget Area or a Menu in the right Top Bar section.'),
                    'section'     => 'custom_layout_topbar_settings',
                    'settings'    => 'topbar_right_choice',
                    'type'        => 'select',
                    'choices'     => array(
                        'menu' => __( 'Menu', 'everyware-theme-base-1' ),
                        'widget_area'  => __( 'Widget Area', 'everyware-theme-base-1' ),
                    ),
                )
            ) );
        }


        /* --------------------------------------- Custom Main Navigation Settings ----------------------------------------- */

        $wp_customize->add_section( 'custom_layout_main_navigation_settings', array(
            'title'       => __( 'Main Navigation Settings', 'everyware-theme-base-1' ),
            'capability'  => 'edit_theme_options',
            'description' => __( 'Custom Main Navigation Settings', 'everyware-theme-base-1' ),
            'panel'       => 'custom_theme_layout_settings'
        ) );

        /* --------------------------------------- Top Bar Switch ----------------------------------------- */

        $wp_customize->add_setting( 'main_navigation_switch', array(
            'default'           => 'on',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'main_navigation_switch', array(
                'label'       => __( 'Enable Main Navigation', 'everyware-theme-base-1' ),
                'description' => __( 'Choose whether to enable the Main Navigation menu.'),
                'section'     => 'custom_layout_main_navigation_settings',
                'settings'    => 'main_navigation_switch',
                'type'        => 'select',
                'choices'     => array(
                    'on' => __( 'On', 'everyware-theme-base-1' ),
                    'off'  => __( 'Off', 'everyware-theme-base-1' ),
                ),
            )
        ) );

        /* -------------------------------------- Custom Sidebar Settings ----------------------------------------- */

        $wp_customize->add_section( 'custom_layout_sidebar_settings', array(
            'title'       => __( 'Sidebar Settings', 'everyware-theme-base-1' ),
            'capability'  => 'edit_theme_options',
            'description' => __( 'Custom Sidebar Settings', 'everyware-theme-base-1' ),
            'panel'       => 'custom_theme_layout_settings'
        ) );


        /* ------------------------------------- Sidebar Position Homepage ---------------------------------------- */

        $wp_customize->add_setting( 'sidebar_position_homepage', array(
            'default'           => 'right',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'sidebar_position_homepage', array(
                'label'       => __( 'Sidebar Position - Homepage', 'everyware-theme-base-1' ),
                'description' => __( 'Choose whether to have the sidebar be on the right, left, none, or on both sides for the homepage.'),
                'section'     => 'custom_layout_sidebar_settings',
                'settings'    => 'sidebar_position_homepage',
                'type'        => 'select',
                'choices'     => array(
                    'right' => __( 'Right', 'everyware-theme-base-1' ),
                    'left'  => __( 'Left', 'everyware-theme-base-1' ),
                    'both'  => __( 'Both', 'everyware-theme-base-1' ),
                    'none'  => __( 'None', 'everyware-theme-base-1' ),
                ),
            )
        ) );

        /* ---------------------------------- Sidebar Position Section Front -------------------------------------- */

        $wp_customize->add_setting( 'sidebar_position_sectionfront', array(
            'default'           => 'right',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'sidebar_position_sectionfront', array(
                'label'       => __( 'Sidebar Position - Section Front', 'everyware-theme-base-1' ),
                'description' => __( 'Choose whether to have the sidebar be on the right, left, none, or on both sides for the section front.'),
                'section'     => 'custom_layout_sidebar_settings',
                'settings'    => 'sidebar_position_sectionfront',
                'type'        => 'select',
                'choices'     => array(
                    'right' => __( 'Right', 'everyware-theme-base-1' ),
                    'left'  => __( 'Left', 'everyware-theme-base-1' ),
                    'both'  => __( 'Both', 'everyware-theme-base-1' ),
                    'none'  => __( 'None', 'everyware-theme-base-1' ),
                ),
            )
        ) );

        /* ---------------------------------- Sidebar Position Content Pages -------------------------------------- */

        $wp_customize->add_setting( 'sidebar_position_content', array(
            'default'           => 'right',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'sidebar_position_content', array(
                'label'       => __( 'Sidebar Position - Content', 'everyware-theme-base-1' ),
                'description' => __( 'Choose whether to have the sidebar be on the right, left, none, or on both sides for content pages.'),
                'section'     => 'custom_layout_sidebar_settings',
                'settings'    => 'sidebar_position_content',
                'type'        => 'select',
                'choices'     => array(
                    'right' => __( 'Right', 'everyware-theme-base-1' ),
                    'left'  => __( 'Left', 'everyware-theme-base-1' ),
                    'both'  => __( 'Both', 'everyware-theme-base-1' ),
                    'none'  => __( 'None', 'everyware-theme-base-1' ),
                ),
            )
        ) );

        /* ------------------------------------------ Sidebar - Width --------------------------------------------- */

        $wp_customize->add_setting( 'sidebar_width', array(
            'default'           => '4_columns',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'sidebar_width', array(
                'label'       => __( 'Sidebar Width', 'everyware-theme-base-1' ),
                'description' => __( 'Choose whether to have the sidebars be worth 3 (skinnier) or 4 columns (wider) of the 12 column Bootstrap Grid.'),
                'section'     => 'custom_layout_sidebar_settings',
                'settings'    => 'sidebar_width',
                'type'        => 'select',
                'choices'     => array(
                    '3_columns' => __( '3 Columns', 'everyware-theme-base-1' ),
                    '4_columns'  => __( '4 Columns', 'everyware-theme-base-1' ),
                ),
            )
        ) );

        /* --------------------------------------- Header Menu Setting -------------------------------------------- */

        $wp_customize->add_section( 'custom_layout_header_settings', array(
            'title'       => __( 'Header Settings', 'everyware-theme-base-1' ),
            'capability'  => 'edit_theme_options',
            'description' => __( 'Custom Header Menu Settings', 'everyware-theme-base-1' ),
            'panel'       => 'custom_theme_layout_settings'
        ) );

        /* --------------------------------------------- Header Widgets --------------------------------------------- */

        $wp_customize->add_setting( 'header_widgets', array(
            'default'           => 'off',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'header_widgets', array(
                    'label'       => __( 'Header Widgets', 'everyware-theme-base-1' ),
                    'description' => __( 'With this set to On, the widget area "Header Region" will be used to populate the header. If set to off, the Logo Position settings in "Site Identity" will be used to display Logo.'),
                    'section'     => 'custom_layout_header_settings',
                    'settings'    => 'header_widgets',
                    'type'        => 'select',
                    'choices'     => array(
                        'on' => __( 'On', 'everyware-theme-base-1' ),
                        'off'  => __( 'Off', 'everyware-theme-base-1' ),
                    ),
                )
            ) );

        /* --------------------------------------------- Header Widgets --------------------------------------------- */

        $wp_customize->add_setting( 'header_widgets_columns', array(
            'default'           => 'columns',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'header_widgets_columns', array(
                    'label'       => __( 'Header Widgets - Columns', 'everyware-theme-base-1' ),
                    'description' => __( 'Determine if you want the Header Widgets area to be separated into three equal columns (Each item will get col-md-4 added to div). If set to "No Columns", use CSS to format the display of the widgets.'),
                    'section'     => 'custom_layout_header_settings',
                    'settings'    => 'header_widgets_columns',
                    'type'        => 'select',
                    'choices'     => array(
                        'columns' => __( 'Columns', 'everyware-theme-base-1' ),
                        'no_columns'  => __( 'No Columns', 'everyware-theme-base-1' ),
                    ),
                )
            ) );

        /* --------------------------------------- Main Navigation Menu ------------------------------------------- */

        $wp_customize->add_setting( 'use_main_navigation_menu', array(
            'default'           => 'no',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'use_main_navigation_menu', array(
                'label'       => __( 'Main Navigation Menu', 'everyware-theme-base-1' ),
                'description' => __( 'Choose whether to use the main navigation menu or not.'),
                'section'     => 'custom_layout_header_settings',
                'settings'    => 'use_main_navigation_menu',
                'type'        => 'select',
                'choices'     => array(
                    'yes' => __( 'Yes', 'everyware-theme-base-1' ),
                    'no'  => __( 'No', 'everyware-theme-base-1' ),
                ),
            )
        ) );

        /* --------------------------------------- Main Nav - Sticky Toggle ----------------------------------------- */

        $wp_customize->add_setting( 'mainnav_sticky_toggle', array(
            'default'           => 'off',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'mainnav_sticky_toggle', array(
                    'label'       => __( 'Main Navigation - Sticky', 'everyware-theme-base-1' ),
                    'description' => __( 'Make the main navigation bar sticky. If on, the main navigation bar remains at the top of the page on scroll. Default is "Off". (NOTE: The main navigation bar cannot be sticky while the top bar is sticky)'),
                    'section'     => 'custom_layout_header_settings',
                    'settings'    => 'mainnav_sticky_toggle',
                    'type'        => 'select',
                    'choices'     => array(
                        'on' => __( 'On', 'everyware-theme-base-1' ),
                        'off'  => __( 'Off', 'everyware-theme-base-1' ),
                    ),
                )
            )
        );

        /* ------------------------------------- Hamburger Menu Settings ------------------------------------------- */

        $wp_customize->add_section( 'custom_layout_hamburger_menu_settings', array(
            'title'       => __( 'Hamburger Menu Settings', 'everyware-theme-base-1' ),
            'capability'  => 'edit_theme_options',
            'description' => __( 'Custom Sidebar Menu Settings', 'everyware-theme-base-1' ),
            'panel'       => 'custom_theme_layout_settings'
        ) );

        /* ------------------------------------------- Sidebar Menu ----------------------------------------------- */

        $wp_customize->add_setting( 'use_hamburger_menu', array(
            'default'           => 'no',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'use_hamburger_menu', array(
                'label'       => __( 'Hamburger Menu', 'everyware-theme-base-1' ),
                'description' => __( 'Choose whether to use the hamburger menu or not.'),
                'section'     => 'custom_layout_hamburger_menu_settings',
                'settings'    => 'use_hamburger_menu',
                'type'        => 'select',
                'choices'     => array(
                    'yes' => __( 'Yes', 'everyware-theme-base-1' ),
                    'no'  => __( 'No', 'everyware-theme-base-1' ),
                ),
            )
        ) );

        /* ------------------------------------------ Sidebar Menu Type --------------------------------------------------- */

        $wp_customize->add_setting( 'hamburger_menu_type', array(
            'default'           => 'sidebar',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'hamburger_menu_type', array(
                    'label'       => __( 'Hamburger Menu', 'everyware-theme-base-1' ),
                    'description' => __( 'Choose whether to use a sidebar menu that slides out, or have the menu items in a dropdown.'),
                    'section'     => 'custom_layout_hamburger_menu_settings',
                    'settings'    => 'hamburger_menu_type',
                    'type'        => 'select',
                    'choices'     => array(
                        'sidebar' => __( 'Sidebar Menu', 'everyware-theme-base-1' ),
                        'dropdown'  => __( 'Dropdown Menu', 'everyware-theme-base-1' ),
                    ),
                )
            )
        );

        /* ------------------------------------------ Sidebar Menu Trigger Text ------------------------------------------- */

        $wp_customize->add_setting( 'hamburger_menu_text', array(
            'default'           => 'MENU',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'hamburger_menu_text', array(
                    'label'       => __( 'Hamburger Trigger - Text', 'everyware-theme-base-1' ),
                    'description' => __( 'Set the text for the Sidebar Trigger within the Top Bar. NOTE: Enter "None" or leave blank if you would not like any text to show.'),
                    'section'     => 'custom_layout_hamburger_menu_settings',
                    'settings'    => 'hamburger_menu_text',
                )
        ) );


        $hamburger_menu_type = get_theme_mod('hamburger_menu_type');

        /* --------------------------------------- Sidebar Menu Width Setting -------------------------------------------- */


        $wp_customize->add_setting( 'hamburger_menu_width', array(
            'default'           => '400',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        /* if hamburger = sidebar */
        if ($hamburger_menu_type == 'sidebar') {
            $wp_customize->add_control(
                new WP_Customize_Control(
                    $wp_customize,
                    'hamburger_menu_width', array(
                        'label'       => __( 'Sidebar Menu - Width', 'everyware-theme-base-1' ),
                        'description' => __( 'Choose the width in pixels of the sidebar menu. Ex. "400px" '),
                        'section'     => 'custom_layout_hamburger_menu_settings',
                        'settings'    => 'hamburger_menu_width',
                    )
                )
            );

            /* ------------------------------------------ Sidebar Menu Search Switch ------------------------------------------- */

            $wp_customize->add_setting( 'sidebar_search_switch', array(
                'default'           => 'on',
                'type'              => 'theme_mod',
                'capability'        => 'edit_theme_options',
            ) );

            $wp_customize->add_control(
                new WP_Customize_Control(
                    $wp_customize,
                    'sidebar_search_switch', array(
                        'label'       => __( 'Sidebar Search Switch', 'everyware-theme-base-1' ),
                        'description' => __( 'Set whether you would to switch the search within the sidebar on or off.'),
                        'section'     => 'custom_layout_hamburger_menu_settings',
                        'settings'    => 'sidebar_search_switch',
                        'type'        => 'select',
                        'choices'     => array(
                            'on' => __( 'Display', 'everyware-theme-base-1' ),
                            'off'  => __( 'Do Not Display', 'everyware-theme-base-1' ),
                        ),
                    )
                ) );



            /* ------------------------------------------ Sidebar Menu Search Switch ------------------------------------------- */

            $wp_customize->add_setting( 'sidebar_search_switch_position', array(
                'default'           => 'below',
                'type'              => 'theme_mod',
                'capability'        => 'edit_theme_options',
            ) );

            $wp_customize->add_control(
                new WP_Customize_Control(
                    $wp_customize,
                    'sidebar_search_switch_position', array(
                        'label'       => __( 'Sidebar Search Switch - Position', 'everyware-theme-base-1' ),
                        'description' => __( 'Set whether you would to search (if switched on) to be above or below the menu in the sidebar.'),
                        'section'     => 'custom_layout_hamburger_menu_settings',
                        'settings'    => 'sidebar_search_switch_position',
                        'type'        => 'select',
                        'choices'     => array(
                            'above' => __( 'Above Menu', 'everyware-theme-base-1' ),
                            'below'  => __( 'Below Menu', 'everyware-theme-base-1' ),
                        ),
                    )
                ) );


            /* ------------------------------------------ Sidebar Menu Logo Switch ------------------------------------------- */

            $wp_customize->add_setting( 'sidebar_menu_logo_switch', array(
                'default'           => 'off',
                'type'              => 'theme_mod',
                'capability'        => 'edit_theme_options',
            ) );

            $wp_customize->add_control(
                new WP_Customize_Control(
                    $wp_customize,
                    'sidebar_menu_logo_switch', array(
                        'label'       => __( 'Sidebar Menu Logo Switch', 'everyware-theme-base-1' ),
                        'description' => __( 'Set whether you would like to switch the sidebar menu logo on or off.'),
                        'section'     => 'custom_layout_hamburger_menu_settings',
                        'settings'    => 'sidebar_menu_logo_switch',
                        'type'        => 'select',
                        'choices'     => array(
                            'on' => __( 'Display', 'everyware-theme-base-1' ),
                            'off'  => __( 'Do Not Display', 'everyware-theme-base-1' ),
                        ),
                    )
                ) );

            /* ------------------------------------------ Sidebar Logo Image ------------------------------------------- */

            $wp_customize->add_setting( 'sidebar_menu_logo_image', array(
                'default'           => '',
                'type'              => 'theme_mod',
                'capability'        => 'edit_theme_options',
            ) );

            $wp_customize->add_control(
                new WP_Customize_Image_Control(
                    $wp_customize,
                    'sidebar_menu_logo_image', array(
                        'label'       => __( 'Sidebar Logo Image', 'everyware-theme-base-1' ),
                        'description' => __( 'Choose the image to be used in the sidebar.'),
                        'section'     => 'custom_layout_hamburger_menu_settings',
                        'settings'    => 'sidebar_menu_logo_image',
                    )
                )
            );
        }
	
         /* --------------------------------------- Dropdown Menu Width Setting ------------------------------------------ */


        $wp_customize->add_setting( 'dropdown_menu_width', array(
            'default'           => '400',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );


        if ($hamburger_menu_type == 'dropdown') {
            $wp_customize->add_control(
                new WP_Customize_Control(
                    $wp_customize,
                    'dropdown_menu_width', array(
                        'label'       => __( 'Dropdown Menu - Width', 'everyware-theme-base-1' ),
                        'description' => __( 'Choose the width in pixels of the dropdown menu. Ex. "400" '),
                        'section'     => 'custom_layout_hamburger_menu_settings',
                        'settings'    => 'dropdown_menu_width',
                    )
                )
            );
        }

        /* --------------------------------------- Footer Menu Setting -------------------------------------------- */

        $wp_customize->add_section( 'custom_layout_footer_menu_settings', array(
            'title'       => __( 'Footer Settings', 'everyware-theme-base-1' ),
            'capability'  => 'edit_theme_options',
            'description' => __( 'Custom Footer Settings', 'everyware-theme-base-1' ),
            'panel'       => 'custom_theme_layout_settings'
        ) );

        /* ------------------------------- Enable Above Footer Menu Widget Area ------------------------------------- */

        $wp_customize->add_setting( 'above_footer_widget_area_switch', array(
            'default'           => 'off',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'above_footer_widget_area_switch', array(
                'label'       => __( 'Above Footer Widget Area - Switch', 'everyware-theme-base-1' ),
                'description' => __( 'Set to "On" to enable the "Above Footer Widget Area".'),
                'section'     => 'custom_layout_footer_menu_settings',
                'settings'    => 'above_footer_widget_area_switch',
                'type'        => 'select',
                'choices'     => array(
                    'on' => __( 'On', 'everyware-theme-base-1' ),
                    'off'  => __( 'Off', 'everyware-theme-base-1' ),
                ),
            )
        ) );

        /* ----------------------------------------- Footer Menu One ---------------------------------------------- */

        $wp_customize->add_setting( 'use_footer_menu_one', array(
            'default'           => 'yes',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'use_footer_menu_one', array(
                'label'       => __( 'Footer Menu - First', 'everyware-theme-base-1' ),
                'description' => __( 'Choose whether to use the first footer menu.'),
                'section'     => 'custom_layout_footer_menu_settings',
                'settings'    => 'use_footer_menu_one',
                'type'        => 'select',
                'choices'     => array(
                    'yes' => __( 'Yes', 'everyware-theme-base-1' ),
                    'no'  => __( 'No', 'everyware-theme-base-1' ),
                ),
            )
        ) );

        $use_footer_menu_one = get_theme_mod( 'use_footer_menu_one' );

        /* Footer Menu One - Heading */
        if ($use_footer_menu_one == 'yes'){
            $wp_customize->add_setting( 'footer_menu_one_heading', array(
                'default'           => '',
                'type'              => 'theme_mod',
                'capability'        => 'edit_theme_options',
            ) );

            $wp_customize->add_control(
                new WP_Customize_Control(
                    $wp_customize,
                    'footer_menu_one_heading', array(
                        'label'       => __( 'Footer Menu - First - Heading', 'everyware-theme-base-1' ),
                        'description' => __( 'Set the heading for the first footer menu.'),
                        'section'     => 'custom_layout_footer_menu_settings',
                        'settings'    => 'footer_menu_one_heading',
                    )
            ) );
        }

        /* ----------------------------------------- Footer Menu Two ---------------------------------------------- */

        $wp_customize->add_setting( 'use_footer_menu_two', array(
            'default'           => 'yes',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'use_footer_menu_two', array(
                'label'       => __( 'Footer Menu - Second', 'everyware-theme-base-1' ),
                'description' => __( 'Choose whether to use the second footer menu.'),
                'section'     => 'custom_layout_footer_menu_settings',
                'settings'    => 'use_footer_menu_two',
                'type'        => 'select',
                'choices'     => array(
                    'yes' => __( 'Yes', 'everyware-theme-base-1' ),
                    'no'  => __( 'No', 'everyware-theme-base-1' ),
                ),
            )
        ) );

        $use_footer_menu_two = get_theme_mod( 'use_footer_menu_two' );

        /* ----------------------------------- Footer Menu Two - Heading ------------------------------------------ */

        if ($use_footer_menu_two == 'yes'){
            $wp_customize->add_setting( 'footer_menu_two_heading', array(
                'default'           => '',
                'type'              => 'theme_mod',
                'capability'        => 'edit_theme_options',
            ) );

            $wp_customize->add_control(
                new WP_Customize_Control(
                    $wp_customize,
                    'footer_menu_two_heading', array(
                        'label'       => __( 'Footer Menu - Second - Heading', 'everyware-theme-base-1' ),
                        'description' => __( 'Set the heading for the second footer menu.'),
                        'section'     => 'custom_layout_footer_menu_settings',
                        'settings'    => 'footer_menu_two_heading',
                    )
            ) );
        }

        /* ---------------------------------------- Footer Menu Three --------------------------------------------- */

        $wp_customize->add_setting( 'use_footer_menu_three', array(
            'default'           => 'yes',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'use_footer_menu_three', array(
                'label'       => __( 'Footer Menu - Third', 'everyware-theme-base-1' ),
                'description' => __( 'Choose whether to use the third footer menu.'),
                'section'     => 'custom_layout_footer_menu_settings',
                'settings'    => 'use_footer_menu_three',
                'type'        => 'select',
                'choices'     => array(
                    'yes' => __( 'Yes', 'everyware-theme-base-1' ),
                    'no'  => __( 'No', 'everyware-theme-base-1' ),
                ),
            )
        ) );

        $use_footer_menu_three = get_theme_mod( 'use_footer_menu_three' );

        /* Footer Menu three - Heading */
        if ($use_footer_menu_three == 'yes'){
            $wp_customize->add_setting( 'footer_menu_three_heading', array(
                'default'           => '',
                'type'              => 'theme_mod',
                'capability'        => 'edit_theme_options',
            ) );

            $wp_customize->add_control(
                new WP_Customize_Control(
                    $wp_customize,
                    'footer_menu_three_heading', array(
                        'label'       => __( 'Footer Menu - Third - Heading', 'everyware-theme-base-1' ),
                        'description' => __( 'Set the heading for the third footer menu'),
                        'section'     => 'custom_layout_footer_menu_settings',
                        'settings'    => 'footer_menu_three_heading',
                    )
            ) );
        }

        /* ---------------------------------------- Footer Menu Four ---------------------------------------------- */

        $wp_customize->add_setting( 'use_footer_menu_four', array(
            'default'           => 'yes',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'use_footer_menu_four', array(
                'label'       => __( 'Footer Menu - Fourth', 'everyware-theme-base-1' ),
                'description' => __( 'Choose whether to use the fourth footer menu.'),
                'section'     => 'custom_layout_footer_menu_settings',
                'settings'    => 'use_footer_menu_four',
                'type'        => 'select',
                'choices'     => array(
                    'yes' => __( 'Yes', 'everyware-theme-base-1' ),
                    'no'  => __( 'No', 'everyware-theme-base-1' ),
                ),
            )
        ) );

        $use_footer_menu_four = get_theme_mod( 'use_footer_menu_four' );

        /* ------------------------------------ Footer Menu Four - Heading ---------------------------------------- */

        if ($use_footer_menu_four == 'yes'){
            $wp_customize->add_setting( 'footer_menu_four_heading', array(
                'default'           => '',
                'type'              => 'theme_mod',
                'capability'        => 'edit_theme_options',
            ) );

            $wp_customize->add_control(
                new WP_Customize_Control(
                    $wp_customize,
                    'footer_menu_four_heading', array(
                        'label'       => __( 'Footer Menu - Fourth - Heading', 'everyware-theme-base-1' ),
                        'description' => __( 'Set the heading for the fourth footer menu'),
                        'section'     => 'custom_layout_footer_menu_settings',
                        'settings'    => 'footer_menu_four_heading',
                    )
            ) );
        }

        /* ----------------------------------------- Footer Logo Position ------------------------------------------ */

        $wp_customize->add_setting( 'footer_main_position', array(
            'default'           => 'right',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'footer_main_position', array(
                    'label'       => __( 'Footer Main (Logo, Contact Info, Social Media) Position', 'everyware-theme-base-1' ),
                    'description' => __( 'Set the position for the Footer Main. This would include (based on what is turned on) where the logo, contact info, and social media icons will show. Can either be: right, left, center.'),
                    'section'     => 'custom_layout_footer_menu_settings',
                    'settings'    => 'footer_main_position',
                    'type'        => 'select',
                    'choices'     => array(
                        'right' => __( 'Right', 'everyware-theme-base-1' ),
                        'left'  => __( 'Left', 'everyware-theme-base-1' ),
                        'center'  => __( 'Center', 'everyware-theme-base-1' ),
                    ),
                )
            ) );

        /* ------------------------------------- Footer Menu Contact Info ----------------------------------------- */

        $wp_customize->add_setting( 'use_footer_menu_contactinfo', array(
            'default'           => 'yes',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'use_footer_menu_contactinfo', array(
                'label'       => __( 'Contact Info', 'everyware-theme-base-1' ),
                'description' => __( 'Choose whether to show Contact Info in the footer main section.'),
                'section'     => 'custom_layout_footer_menu_settings',
                'settings'    => 'use_footer_menu_contactinfo',
                'type'        => 'select',
                'choices'     => array(
                    'yes' => __( 'Yes', 'everyware-theme-base-1' ),
                    'no'  => __( 'No', 'everyware-theme-base-1' ),
                ),
            )
        ) );

        $use_footer_menu_contactinfo = get_theme_mod( 'use_footer_menu_contactinfo' );

        /* ------------------------------------ Footer Contact Info ---------------------------------------- */

        if ($use_footer_menu_contactinfo == 'yes'){
            $wp_customize->add_setting( 'footer_menu_contactinfo_street', array(
                'default'           => '',
                'type'              => 'theme_mod',
                'capability'        => 'edit_theme_options',
            ) );

            $wp_customize->add_control(
                new WP_Customize_Control(
                    $wp_customize,
                    'footer_menu_contactinfo_street', array(
                        'label'       => __( 'Contact Info - Street', 'everyware-theme-base-1' ),
                        'description' => __( 'Set the street to be shown in the contact info in the footer.'),
                        'section'     => 'custom_layout_footer_menu_settings',
                        'settings'    => 'footer_menu_contactinfo_street',
                    )
            ) );

            $wp_customize->add_setting( 'footer_menu_contactinfo_city_state_zip', array(
                'default'           => '',
                'type'              => 'theme_mod',
                'capability'        => 'edit_theme_options',
            ) );

            $wp_customize->add_control(
                new WP_Customize_Control(
                    $wp_customize,
                    'footer_menu_contactinfo_city_state_zip', array(
                        'label'       => __( 'Contact Info - City/State/Zip', 'everyware-theme-base-1' ),
                        'description' => __( 'Set the city, state, and zip code to be shown in the contact info in the footer.'),
                        'section'     => 'custom_layout_footer_menu_settings',
                        'settings'    => 'footer_menu_contactinfo_city_state_zip',
                    )
            ) );

            $wp_customize->add_setting( 'footer_menu_contactinfo_phone', array(
                'default'           => '',
                'type'              => 'theme_mod',
                'capability'        => 'edit_theme_options',
            ) );

            $wp_customize->add_control(
                new WP_Customize_Control(
                    $wp_customize,
                    'footer_menu_contactinfo_phone', array(
                        'label'       => __( 'Contact Info - Phone Number', 'everyware-theme-base-1' ),
                        'description' => __( 'Set the phone number to be shown in the contact info in the footer.'),
                        'section'     => 'custom_layout_footer_menu_settings',
                        'settings'    => 'footer_menu_contactinfo_phone',
                    )
            ) );
        }


        /* ------------------------------------- Footer Social Media Switch ----------------------------------------- */

        $wp_customize->add_setting( 'use_footer_social_media', array(
            'default'           => 'yes',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'use_footer_social_media', array(
                'label'       => __( 'Social Media Icons', 'everyware-theme-base-1' ),
                'description' => __( 'Choose whether to show social media icons in the footer main section..'),
                'section'     => 'custom_layout_footer_menu_settings',
                'settings'    => 'use_footer_social_media',
                'type'        => 'select',
                'choices'     => array(
                    'yes' => __( 'Yes', 'everyware-theme-base-1' ),
                    'no'  => __( 'No', 'everyware-theme-base-1' ),
                ),
            )
        ) );

        $use_footer_social_media = get_theme_mod( 'use_footer_social_media' );

        /* ------------------------------------ Footer Social Media Settings ---------------------------------------- */

        if ($use_footer_social_media == 'yes'){
            $wp_customize->add_setting( 'footer_social_media_facebook', array(
                'default'           => '',
                'type'              => 'theme_mod',
                'capability'        => 'edit_theme_options',
            ) );

            $wp_customize->add_control(
                new WP_Customize_Control(
                    $wp_customize,
                    'footer_social_media_facebook', array(
                        'label'       => __( 'Social Media - Facebook', 'everyware-theme-base-1' ),
                        'description' => __( 'Set the URL that will link to Facebook. If empty, the icon will not show.'),
                        'section'     => 'custom_layout_footer_menu_settings',
                        'settings'    => 'footer_social_media_facebook',
                    )
            ) );

            $wp_customize->add_setting( 'footer_social_media_instagram', array(
                'default'           => '',
                'type'              => 'theme_mod',
                'capability'        => 'edit_theme_options',
            ) );

            $wp_customize->add_control(
                new WP_Customize_Control(
                    $wp_customize,
                    'footer_social_media_instagram', array(
                        'label'       => __( 'Social Media - Instagram', 'everyware-theme-base-1' ),
                        'description' => __( 'Set the URL that will link to Instagram. If empty, the icon will not show.'),
                        'section'     => 'custom_layout_footer_menu_settings',
                        'settings'    => 'footer_social_media_instagram',
                    )
            ) );

            $wp_customize->add_setting( 'footer_social_media_youtube', array(
                'default'           => '',
                'type'              => 'theme_mod',
                'capability'        => 'edit_theme_options',
            ) );

            $wp_customize->add_control(
                new WP_Customize_Control(
                    $wp_customize,
                    'footer_social_media_youtube', array(
                        'label'       => __( 'Social Media - Youtube', 'everyware-theme-base-1' ),
                        'description' => __( 'Set the URL that will link to Youtube. If empty, the icon will not show.'),
                        'section'     => 'custom_layout_footer_menu_settings',
                        'settings'    => 'footer_social_media_youtube',
                    )
            ) );

            $wp_customize->add_setting( 'footer_social_media_twitter', array(
                'default'           => '',
                'type'              => 'theme_mod',
                'capability'        => 'edit_theme_options',
            ) );

            $wp_customize->add_control(
                new WP_Customize_Control(
                    $wp_customize,
                    'footer_social_media_twitter', array(
                        'label'       => __( 'Social Media - Twitter', 'everyware-theme-base-1' ),
                        'description' => __( 'Set the URL that will link to Twitter. If empty, the icon will not show.'),
                        'section'     => 'custom_layout_footer_menu_settings',
                        'settings'    => 'footer_social_media_twitter',
                    )
            ) );
        }

        $wp_customize->add_setting( 'use_footer_copyright', array(
            'default'           => 'yes',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'use_footer_copyright', array(
                'label'       => __( 'Use Copyright Info', 'everyware-theme-base-1' ),
                'description' => __( 'Choose whether to show a Copyright information line at the bottom of the footer.'),
                'section'     => 'custom_layout_footer_menu_settings',
                'settings'    => 'use_footer_copyright',
                'type'        => 'select',
                'choices'     => array(
                    'yes' => __( 'Yes', 'everyware-theme-base-1' ),
                    'no'  => __( 'No', 'everyware-theme-base-1' ),
                ),
            )
        ) );

        $wp_customize->add_setting( 'footer_copyright_position', array(
            'default'           => 'center',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'footer_copyright_position', array(
                'label'       => __( 'Copyright Position', 'everyware-theme-base-1' ),
                'description' => __( 'Choose whether to show the Copyright information in left, center, or right side of the footer.'),
                'section'     => 'custom_layout_footer_menu_settings',
                'settings'    => 'footer_copyright_position',
                'type'        => 'select',
                'choices'     => array(
                    'center' => __( 'Center', 'everyware-theme-base-1' ),
                    'left'  => __( 'Left', 'everyware-theme-base-1' ),
                    'right'  => __( 'Right', 'everyware-theme-base-1' ),
                ),
            )
        ) );

        $wp_customize->add_setting( 'footer_copyright_text', array(
            'default'           => '',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'footer_copyright_text', array(
                    'label'       => __( 'Copyright Info - Text', 'everyware-theme-base-1' ),
                    'description' => __( 'Set the Copyright text line to be shown at the bottom of the footer.'),
                    'section'     => 'custom_layout_footer_menu_settings',
                    'settings'    => 'footer_copyright_text',
                )
        ) );

        
        /* --------------------------------------- Search Form Settings ------------------------------------------- */

        $wp_customize->add_section( 'custom_layout_search_form_settings', array(
            'title'       => __( 'Search Form Settings', 'everyware-theme-base-1' ),
            'capability'  => 'edit_theme_options',
            'description' => __( 'Custom Search Form Settings', 'everyware-theme-base-1' ),
            'panel'       => 'custom_theme_layout_settings'
        ) );

        /* --------------------------------------- Search Form Location ------------------------------------- */

        $wp_customize->add_setting( 'search_form_location', array(
            'default'           => 'main_nav_bar',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'search_form_location', array(
                'label'       => __( 'Search Form Location - Desktop', 'everyware-theme-base-1' ),
                'description' => __( 'Choose where to display the search form on desktop.'),
                'section'     => 'custom_layout_search_form_settings',
                'settings'    => 'search_form_location',
                'type'        => 'select',
                'choices'     => array(
                    'main_nav_bar' => __( 'Main Navigation Bar', 'everyware-theme-base-1' ),
                    'right_sidebar'  => __( 'Right Sidebar', 'everyware-theme-base-1' ),
                    'left_sidebar'  => __( 'Left Sidebar', 'everyware-theme-base-1' ),
                    'do_not_display' => __( 'Do Not Display', 'everyware-theme-base-1' ),
                ),
            )
        ) );

        /* --------------------------------------- Search Form Main Navigation Mobile Toggle ----------------------- */

        $wp_customize->add_setting( 'search_form_location_mobile_toggle', array(
            'default'           => 'top_bar',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'search_form_location_mobile_toggle', array(
                'label'       => __( 'Search Form Location - Mobile', 'everyware-theme-base-1' ),
                'description' => __( 'Choose whether to display a search icon on mobile within the main navigation bar, the top bar, or to not display.'),
                'section'     => 'custom_layout_search_form_settings',
                'settings'    => 'search_form_location_mobile_toggle',
                'type'        => 'select',
                'choices'     => array(
                    'top_bar' => __( 'Top Bar', 'everyware-theme-base-1' ),
                    'main_nav_bar' => __( 'Main Navigation Bar', 'everyware-theme-base-1' ),
                    'no' => __( 'Do Not Display', 'everyware-theme-base-1' ),
                ),
            )
        ) );

        /* ------------------------------------- END Search Form Settings ----------------------------------------- */

        /* ------------------------------------------ Wrapper Section --------------------------------------------- */

        $wp_customize->add_section( 'wrapper_settings', array(
            'title'       => __( 'Wrapper Settings', 'everyware-theme-base-1' ),
            'capability'  => 'edit_theme_options',
            'description' => __( 'Custom Wrapper Settings', 'everyware-theme-base-1' ),
            'panel'       => 'custom_theme_layout_settings'
        ) );

        /* --------------------------------------- Container Width Size ------------------------------------------- */

        $wp_customize->add_setting( 'container_width_size', array(
            'default'           => '1140px',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'container_width_size', array(
                'label'       => __( 'Container Width Size', 'everyware-theme-base-1' ),
                'description' => __( 'Choose the width of the container, if used.'),
                'section'     => 'wrapper_settings',
                'settings'    => 'container_width_size',
            )
        ) );

        /* --------------------------------- Main Navigation Wrapper for Menu ------------------------------------ */

        /* Main Navigation Wrapper for Menu */
        $wp_customize->add_setting( 'main_navigation_wrapper', array(
            'default'           => '100%',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'main_navigation_wrapper', array(
                'label'       => __( 'Main Navigation Wrapper', 'everyware-theme-base-1' ),
                'description' => __( 'Choose if the menu is full width (Full) or in a wrapper (Wrapper).'),
                'section'     => 'wrapper_settings',
                'settings'    => 'main_navigation_wrapper',
                'type'        => 'select',
                'choices'     => array(
                    'wrapper' => __( 'Wrapper Width', 'everyware-theme-base-1' ),
                    'full'  => __( 'Full', 'everyware-theme-base-1' ),
                ),
            )
        ) );

        /* --------------------------------------------------------------------------------------------------------- */
        /*                                         END OF THEME LAYOUT SETTINGS                                      */
        /* --------------------------------------------------------------------------------------------------------- */

        /* --------------------------------------------------------------------------------------------------------- */
        /*                                             THIRD PARTY SETTINGS                                          */
        /* --------------------------------------------------------------------------------------------------------- */

        $wp_customize->add_panel('third_party_settings',array(
            'title'=>'Third Party Settings',
        ));

        /* ----------------------------------------Google Analytics Section ---------------------------------------- */

        $wp_customize->add_section( 'google_analytics_settings', array(
            'title'       => __( 'Google Analytics', 'everyware-theme-base-1' ),
            'capability'  => 'edit_theme_options',
            'description' => __( 'Google Analytics', 'everyware-theme-base-1' ),
            'panel'       => 'third_party_settings'
        ) );

        /* ----------------------------------------Switch for Google Analytics ------------------------------------- */
        $wp_customize->add_setting( 'use_google_analytics', array(
            'default'           => 'yes',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'use_google_analytics', array(
                'label'       => __( 'Use Google Analytics', 'everyware-theme-base-1' ),
                'description' => __( 'Choose whether or not to use Google Analytics on your site.'),
                'section'     => 'google_analytics_settings',
                'settings'    => 'use_google_analytics',
                'type'        => 'select',
                'choices'     => array(
                    'yes' => __( 'Yes', 'everyware-theme-base-1' ),
                    'no'  => __( 'No', 'everyware-theme-base-1' ),
                ),
            )
        ) );

        /* ---------------------------------------- Set Google Analytics ID ---------------------------------------- */  
        $wp_customize->add_setting( 'google_analytics_id', array(
            'default'           => '',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );


        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'google_analytics_id', array(
                    'label'       => __( 'Google Analytics ID', 'everyware-theme-base-1' ),
                    'description' => __( 'Set the Google Analytics ID associated with your Google Analytics account.'),
                    'section'     => 'google_analytics_settings',
                    'settings'    => 'google_analytics_id',
                )
        ) );

        /* ----------------------------------------Disqus Comments Section ---------------------------------------- */

        $wp_customize->add_section( 'disqus_comments_settings', array(
            'title'       => __( 'Disqus Comments', 'everyware-theme-base-1' ),
            'capability'  => 'edit_theme_options',
            'description' => __( 'Disqus Comments', 'everyware-theme-base-1' ),
            'panel'       => 'third_party_settings'
        ) );

        /* ----------------------------------------Switch for Google Analytics ------------------------------------- */
        $wp_customize->add_setting( 'use_disqus_comments', array(
            'default'           => 'yes',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'use_disqus_comments', array(
                'label'       => __( 'Use Disqus Comments', 'everyware-theme-base-1' ),
                'description' => __( 'Choose whether or not to use Disqus Comments on your site.'),
                'section'     => 'disqus_comments_settings',
                'settings'    => 'use_disqus_comments',
                'type'        => 'select',
                'choices'     => array(
                    'yes' => __( 'Yes', 'everyware-theme-base-1' ),
                    'no'  => __( 'No', 'everyware-theme-base-1' ),
                ),
            )
        ) );

        /* ---------------------------------------- Set Google Analytics ID ---------------------------------------- */  
        $wp_customize->add_setting( 'disqus_site_id', array(
            'default'           => '',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
        ) );


        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'disqus_site_id', array(
                    'label'       => __( 'Disqus Site ID', 'everyware-theme-base-1' ),
                    'description' => __( 'Set the Disqus Site ID associated with your Disqus account. Ex. "everyware-theme-base-1-everyware"'),
                    'section'     => 'disqus_comments_settings',
                    'settings'    => 'disqus_site_id',
                )
        ) );

        /* --------------------------------------------------------------------------------------------------------- */
        /*                                             END THIRD PARTY SETTINGS                                      */
        /* --------------------------------------------------------------------------------------------------------- */

    }
    add_action( 'customize_register', 'everyware_theme_base_1_customize_register');

    function everyware_theme_base_1_child_customizer_css() {

        if (current_user_can( 'edit_theme_options' ) ) {
            $css = "<style type='text/css'> ";

            $header_logo_size_width = get_theme_mod( 'header_logo_size_width', '440px' );
            $custom_font_family_url = get_theme_mod( 'custom_font_family_url' );
            $custom_font_family_name = get_theme_mod( 'custom_font_family_name' );
            $heading_font = get_theme_mod( 'heading_font', 'Georgia, Baskerville, Helvetica, Arial, sans-serif' );
            $heading_font_size_front_featured = get_theme_mod( 'heading_font_size_front_featured', '24px' );
            $heading_font_size_section_title = get_theme_mod( 'heading_font_size_section_title', '28px' );
            $heading_font_size_section_teaser_titles = get_theme_mod( 'heading_font_size_section_teaser_titles', '22px' );
            $heading_font_size_articlepageheadline = get_theme_mod( 'heading_font_size_articlepageheadline', '34px' );
            $heading_font_size_front_teaser_title_blocks = get_theme_mod( 'heading_font_size_front_teaser_title_blocks', '18px' );
            $heading_font_size_front_teaser_title_lists_main = get_theme_mod( 'heading_font_size_front_teaser_title_lists_main', '18px' );
            $heading_font_size_front_teaser_title_lists = get_theme_mod( 'heading_font_size_front_teaser_title_lists', '14px' );
            $heading_font_size_front_blocks_lists_title = get_theme_mod( 'heading_font_size_front_blocks_lists_title', '20px' );
            $body_font = get_theme_mod( 'body_font', 'Georgia, Baskerville, Helvetica, Arial, sans-serif' );
            $body_font_size_frontpage_teasers = get_theme_mod( 'body_font_size_frontpage_teasers', '14px' );
            $body_font_size_section_teasers = get_theme_mod( 'body_font_size_section_teasers', '14px' );
            $body_font_size_article_posts = get_theme_mod( 'body_font_size_article_posts', '14px' );
            $main_menu_color = get_theme_mod( 'main_menu_color', '#d6d6d6' );
            $main_menu_link_color = get_theme_mod( 'main_menu_link_color', '#000000' );
            $button_color = get_theme_mod( 'button_color', '#636363' );
            $sidebar_menu_color = get_theme_mod( 'sidebar_menu_color', '#212121' );
            $sidebar_menu_link_color = get_theme_mod( 'sidebar_menu_link_color', '#f1f1f1' );
            $sidebar_menu_font = get_theme_mod( 'sidebar_menu_font', 'Georgia, Baskerville, Helvetica, Arial, sans-serif' );
            $sidebar_menu_font_size = get_theme_mod( 'sidebar_menu_font_size', '16px' );
            $main_menu_font = get_theme_mod( 'main_menu_font', 'Georgia, Baskerville, Helvetica, Arial, sans-serif' );
            $main_menu_font_size = get_theme_mod( 'main_menu_font_size', '16px' );
            $top_bar_font = get_theme_mod( 'top_bar_font', 'Georgia, Baskerville, Helvetica, Arial, sans-serif' );
            $top_bar_font_size = get_theme_mod( 'top_bar_font_size', '14px' );
            $headline_text = get_theme_mod( 'headline_text', '#000000' );
            $teaser_body_text = get_theme_mod( 'teaser_body_text', '#353535' );
            $article_body_text = get_theme_mod( 'article_body_text', '#000000' );
            $article_summary_text = get_theme_mod( 'article_summary_text', '#000000' );
            $header_color = get_theme_mod( 'header_color', '#ffffff' );
            $sidebar_color = get_theme_mod( 'sidebar_color', '#f2f2f2' );
            $breaking_news_background_color = get_theme_mod( 'breaking_news_background_color', '#9a2323' );
            $breaking_news_text_color = get_theme_mod( 'breaking_news_text_color', 'white' );
            $link_color = get_theme_mod( 'link_color', '#000000' );
            $link_hover_color = get_theme_mod( 'link_hover_color', '#1e73be' );
            $link_navigation_hover_color = get_theme_mod( 'link_navigation_hover_color', '#ffffff' );
            $login_modal_background_color = get_theme_mod( 'login_modal_background_color', '#ffffff' );
            $login_modal_border_color = get_theme_mod( 'login_modal_border_color', '#000000' );
            $login_modal_text_color = get_theme_mod( 'login_modal_text_color', '#000000' );
            $category_block_category_text = get_theme_mod( 'category_block_category_text', '#000000' );
            $category_block_opinion_author_text = get_theme_mod( 'category_block_opinion_author_text', '#1e0745' );
            $thin_teaser_category_text = get_theme_mod( 'thin_teaser_category_text', '#3b3b84' );
            $thin_teaser_background_color = get_theme_mod( 'thin_teaser_background_color', '#f2f2f2' );
            $section_teaser_one_column_category_overlay_text_color = get_theme_mod( 'section_teaser_one_column_category_overlay_text_color', '#ffffff' );
            $section_teaser_one_column_category_overlay_background_color = get_theme_mod( 'section_teaser_one_column_category_overlay_background_color', '#1e0745' );
            $topbar_color = get_theme_mod( 'topbar_color', '#4f84ad' );
            $topbar_text_color = get_theme_mod( 'topbar_text_color', '#000000' );
            $topbar_link_hover_color = get_theme_mod( 'topbar_link_hover_color', '#ffffff' );
            $hamburger_menu_trigger_color = get_theme_mod( 'hamburger_menu_trigger_color', '#ffffff' );
            $above_footer_widget_area_color = get_theme_mod( 'above_footer_widget_area_color', '#ffffff' );
            $above_footer_widget_area_text_color = get_theme_mod( 'above_footer_widget_area_text_color', '#000000' );
            $footer_color = get_theme_mod( 'footer_color', '#566887' );
            $footer_link_color = get_theme_mod( 'footer_link_color', '#ffffff' );
            $footer_link_hover_color = get_theme_mod( 'footer_link_hover_color', '#6d6d6d' );
            $footer_logo_size_height = get_theme_mod( 'footer_logo_size_height', '100px' );
            $footer_font = get_theme_mod( 'footer_font', 'Georgia, Baskerville, Helvetica, Arial, sans-serif' );
            $footer_font_menu_title_size = get_theme_mod( 'footer_font_menu_title_size', '18px' );
            $footer_menu_title_color = get_theme_mod( 'footer_menu_title_color', '#000000' );
            $footer_font_menu_link_size = get_theme_mod( 'footer_font_menu_link_size', '14px' );
            $footer_font_contact_info_size = get_theme_mod( 'footer_font_contact_info_size', '12px' );
            $footer_font_copyright_size = get_theme_mod( 'footer_font_copyright_size', '12px' );
            $footer_contact_info_color = get_theme_mod( 'footer_contact_info_color', '#ffffff' );
            $footer_social_media_color = get_theme_mod( 'footer_social_media_color', '#ffffff' );
            $footer_font_social_media_icons_size = get_theme_mod( 'footer_font_social_media_icons_size', '18px' );
            $footer_copyright_color = get_theme_mod( 'footer_copyright_color', '#000000' );
            $container_width_size = get_theme_mod( 'container_width_size', '1200px' );
            $main_navigation_wrapper = get_theme_mod( 'main_navigation_wrapper' );
            $topbar_sticky_toggle = get_theme_mod('topbar_sticky_toggle', 'on');
            $topbar_switch = get_theme_mod('topbar_switch');
            //$logo_top_bar_mobile = get_theme_mod( 'logo_top_bar_mobile' );
            $dropdown_menu_width = get_theme_mod('dropdown_menu_width');

            if ($main_navigation_wrapper == 'wrapper') {
                $main_navigation_wrapper_size = $container_width_size;
            } else {
                $main_navigation_wrapper_size = '100%';
            }

            $css .= '
                html, body { font-family: ' . $body_font . ' !important;}
                form#login { font-family: ' . $body_font . ' !important; }
                .header-region { background-color: ' . $header_color . '; }
                @font-face { font-family: ' . $custom_font_family_name . '; src: url(' . $custom_font_family_url . '); }
                .hamburger-menu #top-main-menu { width: '. $dropdown_menu_width . 'px ; }
                .teaser-body.frontpagecategoryblock, .teaser-body.frontpagefeaturedtop, .section-teaser .teaser__leadin { color: ' . $teaser_body_text . '; } 
                a, .concept-page a { color: ' . $link_color . '; text-decoration: none !important; }
                .search_page_container a { color: ' . $link_color . '; text-decoration: none !important; }  
                a:hover span.teaser__headline-marker, div#top-bar a:hover, div#right-sidebar a:hover { color: ' . $link_hover_color . '; text-decoration: none !important;  }  
                .main-navigation a.nav-link:hover { color: ' . $link_navigation_hover_color . ' !important; }
                #top-bar, #top-bar .bg-primary { background-color: ' . $topbar_color . ' !important; } 
                div#top-bar a:hover { color: ' . $topbar_link_hover_color . '; }  
                #footer-menu .bg-primary { background-color: ' . $footer_color . ' !important; } 
                .footer-menu a { color: ' . $footer_link_color . '; } 
                .footer-menu a:hover { color: ' . $footer_link_hover_color . '; }  
                img.footer_logo_image { max-height: ' . $footer_logo_size_height . '; } 
                #wrapper-navbar .main-navigation.navbar { background-color: ' . $main_menu_color . ' !important; } 
                nav.main-navigation li a { color: ' . $main_menu_link_color . ' !important; } 
                .btn-primary, .btn-secondary, .btn, .main-nav-search input#searchsubmit { background-color: ' . $button_color . ' !important;, border-color: ' . $button_color . ' !important; } 
                i.fa.fa-window-close { color: ' . $button_color . '; }
                #sidebarMenu li a { font-size: ' . $sidebar_menu_font_size . '; font-family: ' . $sidebar_menu_font . '; } 
                .sidebarMenu { background-color: ' . $sidebar_menu_color . '} 
                #sidebarMenu li a, #sidebarMenu hr.sidebarMenuLine, .sidebarMenuCloseButton { color: ' . $sidebar_menu_link_color . '; }
                .widget-title, .frontpagefeaturedtop, .every_board .teaser__headline span { color: ' . $headline_text . '; font-family: ' . $heading_font . '; } 
                .content-area .entry-header .entry-title, .search_page_title { font-size: ' . $heading_font_size_section_title . '; } 
                .section-teaser .entry-title h2.teaser__headline { font-size: ' . $heading_font_size_section_teaser_titles . '; } 
                .section-teaser .category_overlay { background-color: ' . $section_teaser_one_column_category_overlay_background_color . '; color: ' . $section_teaser_one_column_category_overlay_text_color . '; } 
                .frontpage-category-title { font-size: ' . $heading_font_size_front_blocks_lists_title . '; font-family: ' . $heading_font . ';} 
                .frontpage-slider .teaser__headline-marker, .frontpagefeaturedtop .teaser__headline-marker { font-size: ' . $heading_font_size_front_featured . '; } 
                .article-page .article__headline { font-size: ' . $heading_font_size_articlepageheadline . '; font-family: ' . $heading_font . '; } 
                .entry-title.frontpagecategoryblock, .entry-title.frontpagecategoryblock h2, article.teaser.frontpagecategorylistrest .teaser__headline { font-size: ' . $heading_font_size_front_teaser_title_blocks . '; } 
                article.teaser.frontpagecategorylisttop .teaser__headline { font-size: ' . $heading_font_size_front_teaser_title_lists_main . '; }
                article.teaser.frontpagecategorylistrest .teaser__headline { font-size: ' . $heading_font_size_front_teaser_title_lists . '; } 
                .above_footer_widget_area { background-color: ' . $above_footer_widget_area_color . '; color: ' . $above_footer_widget_area_text_color . '; } 
                #wrapper-footer { background-color: ' . $footer_color . ';} 
                .content-area.entry-content, .article__body .article-restofcontent { font-family: ' . $body_font . ';}
                .article__body .article-restofcontent { color: ' . $article_body_text . ';}
                span.article__body.article__leadin { color: ' . $article_summary_text . '; font-family: ' . $body_font . ';}
                .teaser-body.frontpagecategoryblock, .teaser-body.frontpagefeaturedtop { font-size: ' . $body_font_size_frontpage_teasers . '; }
                .section-teaser .teaser-body { font-size: ' . $body_font_size_section_teasers . '; }
                .article__body .article-restofcontent { font-size: ' . $body_font_size_article_posts . '; }
                .article-date { font-family: ' . $body_font . ';}
                div#wrapper-navbar { max-width: ' . $main_navigation_wrapper_size . '; margin: auto; }
                nav.main-navigation, nav.main-navigation li a { font-family: ' . $main_menu_font . '; font-size: ' . $main_menu_font_size . '; }
                .container, .breakingnews-top { max-width: ' . $container_width_size . ' !important ; }
                @media (min-width: 1050px) { a.navbar-brand.custom-logo-link { width: ' . $header_logo_size_width . ' ; } }
                form#login { background-color: ' . $login_modal_background_color . ' ; color: ' . $login_modal_text_color . '; border: 1px solid ' . $login_modal_border_color . '; }
                div#top-bar, div#top-bar a { font-family: ' . $top_bar_font . '; font-size: ' . $top_bar_font_size . '; color: ' . $topbar_text_color . ';  }
                .sidemenuTrigger, .sideMenuTrigger.mobile, .hamburger-menu button#dropdownMenuButton { font-family: ' . $top_bar_font . '; font-size: ' . $top_bar_font_size . '; color: ' . $hamburger_menu_trigger_color . ';  }
                .footer-menu { font-family: ' . $footer_font . ';  }
                .footer-menu .footer-heading { font-size: ' . $footer_font_menu_title_size . '; color: ' . $footer_menu_title_color . '; }
                .footer-menu a { font-size: ' . $footer_font_menu_link_size . ';  }
                .footer-logo-contactinfo { font-size: ' . $footer_font_contact_info_size . '; font-family: ' . $footer_font . '; color: ' . $footer_contact_info_color . '; }
                .footer-social-media-icon a { color: ' . $footer_social_media_color . '; font-size: ' . $footer_font_social_media_icons_size . '; }
                .footer_copyright { color: ' . $footer_copyright_color . '; font-size: ' . $footer_font_copyright_size . ';   }
                #right-sidebar, #left-sidebar, .sidebar { background-color: ' . $sidebar_color . '; } 
                article.breaking-section, article.breaking_fullLength { background-color: ' . $breaking_news_background_color . '; } 
                .breaking_title, .breaking-teaser, .breaking-teaser .teaser-article-date, .breaking-teaser a, 
                article.breaking_fullLength, article.breaking_fullLength a, .section-teaser.breaking-teaser a:hover { color: ' . $breaking_news_text_color . '; } 
                .teaser-thin-teaser-category { color: ' . $thin_teaser_category_text . '; } 
                article.thin-teaser { background-color: ' . $thin_teaser_background_color . '; } 
                .frontpagecategoryblock.teaser-category, .teaser-frontpage-category-main-category { color: ' . $category_block_category_text . ' } 
                .teaser-frontpage-category-side.opinion .teaser-frontpage-category-side-author, .frontpage-category-main.opinion .teaser-frontpage-category-main-author { color: ' . $category_block_opinion_author_text . ' } ' ;

                if ($topbar_sticky_toggle == 'on' && $topbar_switch == 'on') {
                        $top_bar_css = 'div#top-bar { position: fixed; width: 100%;width: -moz-available; width: -webkit-fill-available;z-index: 10;box-shadow: 0px 1px 10px rgba(0,0,0,0.3); padding: 0 2em;}
                        @media all and (max-width: 478px) {.header-region {padding-top: 58px;}.sidebarMenu {padding-top: 58px;}}
                        @media all and (min-width: 479px) {.header-region {padding-top: 58px;}.sidebarMenu {padding-top: 58px;}}
                        @media all and (min-width: 504px) {.header-region {padding-top: 58px;}.sidebarMenu {padding-top: 58px;}}
                        @media all and (min-width: 768px) {.header-region {padding-top: 58px;}.sidebarMenu {padding-top: 58px;}}
                        @media all and (min-width: 888px) {.header-region {padding-top: 58px;}.sidebarMenu {padding-top: 58px;}}
                        @media all and (min-width: 992px) {.header-region {padding-top: 58px;}.sidebarMenu {padding-top: 58px;}}';                   
                    $css = $css . $top_bar_css;
                }
                
                $css = $css . "</style>";
            set_theme_mod( 'customizer_css', $css );
        }            
            
        echo get_theme_mod ('customizer_css'); 
    }
}
