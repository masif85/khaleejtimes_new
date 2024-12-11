<?php

include_once (get_theme_file_path('inc/theme_customizer_settings.php'));
include_once (get_theme_file_path('inc/theme_ajax_hooks.php'));

if ($text_domain = wp_get_theme(basename(__DIR__))->get('TextDomain')) {
  load_theme_textdomain($text_domain, get_template_directory() . '/languages');
}
