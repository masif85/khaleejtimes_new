<?php
if (!headers_sent()) {
$url= $_SERVER['REQUEST_URI'];
if (strpos($url, "wp-admin") == false && strpos($url,"scorecard-cricket-world-cup-2023") == false  && strpos($url,"feeds?") == false) {
$default="600";	
if (strpos($url, "games") !== false) {
$default="1200";	
}

if (strpos($url, "summary/?competition") !== false || strpos($url, "detail?tvwidgetsymbol=") !== false) {
	if(strpos($url, "&_refresh=true") !== false)
		{
			$urls="";
		}
		else
		{
			$urls="URL=".$url."&_refresh=true";
		}
		}
	else {

			$urls="URL=?_refresh=true";
		}	
$param=@$_GET["amp"];	
if($param){
	header("Refresh: $default;URL=?_refresh=true&amp=true");
}
else{		
   header("Refresh: $default;$urls");
	}
	}
}
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

require_once __DIR__ . '/vendor/autoload.php';

/**
 * Determines if AWS- or dev-environment
 */
$configFile = isset($_SERVER['IMHOST']) ? 'wp-config-aws.php' : 'wp-config-dev.php';

/** @noinspection PhpIncludeInspection */
require_once __DIR__ . "/wp-config/{$configFile}";

/**
 * ==================
 * Database variables
 * ==================
 */
define('DB_NAME', env('DB_NAME', 'everyware'));
define('DB_USER', env('DB_USER', 'admin'));
define('DB_PASSWORD', env('DB_PASSWORD', 'EveryWare'));
define('DB_HOST', env('DB_HOST', 'mysql'));
define('DB_CHARSET', env('DB_CHARSET', 'utf8'));
define('DB_COLLATE', env('DB_COLLATE', ''));

/**
 * ==================
 * 20240701 NAV-MY: changing cookie security
 * ==================
 */
@ini_set('session.cookie_httponly', true);
@ini_set('session.cookie_secure', true);
@ini_set('session.use_only_cookies', true);

/**
 * =================
 * Debugging in WordPress
 * =================
 *
 * @links https://wordpress.org/support/article/debugging-in-wordpress/
 */

// Enable WP_DEBUG mode. Make sure to valitade APP_DEBUG to boolean
define('WP_DEBUG', filter_var(env('APP_DEBUG', false), FILTER_VALIDATE_BOOLEAN));

// Enable Debug logging to the /wp-content/debug.log file
// WP_DEBUG must be enabled for this to take effect
define('WP_DEBUG_LOG', WP_DEBUG);

// Enable display of errors and warnings.
// WP_DEBUG must be enabled for this to take effect
define('WP_DEBUG_DISPLAY', WP_DEBUG);

// Use dev versions of core JS and CSS files
define('SCRIPT_DEBUG', false);

// Causes each query to be saved in global $wpdb->queries.
define('SAVEQUERIES', false);

/**
 * =================
 * CloudFront server
 * =================
 */
define('CF_PDF', env('CF_PDF', ''));
define('CF_STATIC', env('CF_STATIC', ''));
define('CF_IMENGINE', env('CF_IMENGINE', ''));

/**
 * =====================
 * Amazon Access Id/Keys
 * =====================
 */
define('AWS_ACCESS_KEY_ID', env('AWS_ACCESS_KEY_ID', ''));
define('AWS_SECRET_ACCESS_KEY', env('AWS_SECRET_KEY', ''));

/**
 * =====================
 * Amazon SES configuration
 * =====================
 */
if (AWS_ACCESS_KEY_ID) {
    require_once __DIR__ . '/wp-config/aws-ses-config.php';
}

/**
 * ===================
 * Redis configuration
 * ===================
 */
require_once __DIR__ . '/wp-config/redis-config.php';

/**
 * Apply WordPress salt for cookies.
 *
 * Auto-generate through `make wordpress-add-salt`
 */
require_once __DIR__ . '/wp-config/wp-salt.php';

/**
 * WordPress Database Table prefix.
 */
$table_prefix = env('DB_TABLE_PREFIX', 'wp_');

define('WP_AUTO_UPDATE_CORE', false);   // Disable auto-update
define('DISALLOW_FILE_MODS', true);     // Disable file editing from WP-admin

/**
 * =================
 * Multi site config
 * =================
 */
define('WP_ALLOW_MULTISITE', true);
define('WP_CACHE', true);
define('MULTISITE', true);
define('SUBDOMAIN_INSTALL', true);
define('PATH_CURRENT_SITE', '/');
define('SITE_ID_CURRENT_SITE', 1);
define('BLOG_ID_CURRENT_SITE', 1);
define('SUNRISE', 'on');

/**
 * Set correct domain according to ENVIRONMENT var.
 */
$devDomain = env('APP_DEV_DOMAIN', 'everywarestarterkit.local');
define('DOMAIN_CURRENT_SITE', env('APP_DOMAIN', $devDomain));

/**  Set url to https if forwarded from loadbalancer and protocol is https */
if ($_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
}

if ( ! defined('GIT_COMMIT')) {
    define('GIT_COMMIT', '1337');
}

/**
 * ==============
 * Default values
 * ==============
 */
if ( ! defined('PHP_BIN')) {
    define('PHP_BIN', '/usr/bin/php');
}

/**
 * ===============
 * WordPress setup
 * ===============
 */
if ( ! defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/'); // Absolute path to the WordPress directory.
}

define('WP_DEFAULT_THEME', 'default-theme');

require_once ABSPATH . 'wp-settings.php';
