<?php declare(strict_types=1);

/**
 * ========================================
 * WP Offload SES Lite plugin configuration
 * ========================================
 *
 *  * Available Beanstalk Variables:
 * -  AWS_SES_FROM (string)
 * -  AWS_SES_REGION (string)
 * -  AWS_SES_REPLYTO (string)
 * -  AWS_SES_RETURNPATH (string) - Will use "AWS_SES_FROM" if not set
 * -  AWS_SES_ACCESS_KEY_ID (string) - Will use "AWS_ACCESS_KEY_ID" if not set
 * -  AWS_SES_SECRET_KEY (string) Will use "AWS_SECRET_KEY" if not set
 *
 * "AWS_SES_REGION" = Previously known in version 0.8.x as AWS_SES_ENDPOINT
 * @links https://wordpress.org/plugins/wp-ses/
 * @links https://deliciousbrains.com/wp-offload-ses/doc/settings-constants/
 */

// Backwards compatibility: map `AWS_SES_ENDPOINT` variable to `AWS_SES_REGION`.
$region = env('AWS_SES_REGION', env('AWS_SES_ENDPOINT', ''));

/*
 * Convert old WP_SES_ENDPOINT setting to region:
 *
 * Old - "email.eu-west-1.amazonaws.com"
 * New - eu-west-1
 */
if (strpos($region, '.') !== false) {
    $value = explode('.', $region);
    $region = $value[1];
}

$defaultEmail = env('AWS_SES_FROM', 'your-email@example.com');
$settings = [
    // Send site emails via Amazon SES.
    'send-via-ses' => true,
    // Amazon SES region (e.g. 'us-east-1' - leave blank for default region).
    'region' => $region,
    // Changes the default email address used by WordPress
    'default-email' => $defaultEmail,
    // Sets the "Reply-To" header for all outgoing emails.
    'reply-to' => env('AWS_SES_REPLYTO', ''),
    // Sets the "Return-Path" header used by Amazon SES.
    'return-path' => env('AWS_SES_RETURNPATH', $defaultEmail),
    // Enable open tracking.
    'enable-open-tracking' => false,
    // Enable click tracking.
    'enable-click-tracking' => false,
    // Enables the health report.
    'enable-health-report' => false,
];

$remove_empty_values = [
    'reply-to',
    'region'
];

// Remove values if not explicitly set
foreach ($remove_empty_values as $key) {
    if (empty($settings[$key])) {
        unset($settings[$key]);
    }
}

/*
 * WP-SES IAM user credentials.
 * Variables are fetched from Beanstalk configuration.
 */
define('WPOSES_AWS_ACCESS_KEY_ID', env('AWS_SES_ACCESS_KEY_ID', env('AWS_ACCESS_KEY_ID')));
define('WPOSES_AWS_SECRET_ACCESS_KEY', env('AWS_SES_SECRET_KEY', env('AWS_SECRET_KEY')));

/**
 * WP-SES Settings.
 * Variables are fetched from Beanstalk configuration.
 */
define('WPOSES_SETTINGS', serialize($settings));
define('WPOSES_HIDE_VERIFIED', true);
define('WPOSES_HIDE_STATS', true);
