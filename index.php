<?php
if(!isset($_COOKIE['boxx_token_id_kt'])) {
$arr_cookie_options = array (
                'expires' => 2147483647, 
                'path' => '/', 
                'domain' => '', 
                'secure' => true,     // or false
                'httponly' => true,    // or false
                'samesite' => 'Strict' 
                );
   $cookie_val=substr(hash('sha256', mt_rand()), 0, 48);
   $cookie_val=rtrim(chunk_split( $cookie_val, 12, '-'), "-");
  $cookie_name = "boxx_token_id_kt";
  setcookie($cookie_name,$cookie_val,$arr_cookie_options);
} 
//serror_reporting(E_All);
/**
 * Front to the WordPress application. This file doesn't do anything, but loads
 * wp-blog-header.php which does and tells WordPress to load the theme.
 *
 * @package WordPress
 */

/**
 * Tells WordPress to load the WordPress theme and output it.
 *
 * @var bool
 */

define( 'WP_USE_THEMES', true );

/** Loads the WordPress Environment and Template */
require __DIR__ . '/wp-blog-header.php';





/*global $wpdb;
$charset_collate = $wpdb->get_charset_collate();


$sql = "
DROP TABLE IF EXISTS `{$wpdb->base_prefix}tbl_fuel_prices`;

CREATE TABLE `{$wpdb->base_prefix}tbl_fuel_prices2`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) NULL DEFAULT NULL,
  `price` decimal(10, 2) NOT NULL,
  `modified_date_cms` datetime(0) NOT NULL,
  `created_date_cms` datetime(0) NULL DEFAULT NULL,
  `modified_date` date NULL DEFAULT NULL,
  `modified_year` varchar(255)  NULL DEFAULT NULL,
  `modified_month` varchar(255)  NULL DEFAULT NULL,
  `modified_day2` varchar(255)  NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `unique`(`type`, `modified_year`, `modified_month`) USING BTREE
) $charset_collate;";

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
echo dbDelta($sql);

exit;*/
