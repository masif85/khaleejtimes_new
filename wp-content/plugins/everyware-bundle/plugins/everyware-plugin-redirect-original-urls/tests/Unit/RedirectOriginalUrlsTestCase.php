<?php
declare(strict_types=1);

namespace Unit\Everyware\Plugin\RedirectOriginalUrls;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/wp-functions.php';

if (!defined('REDIRECT_ORIGINAL_URLS_TEXT_DOMAIN')) {
    define('REDIRECT_ORIGINAL_URLS_TEXT_DOMAIN', 'ew-redirect-original-urls');
}

/**
 * Class RedirectOriginalUrlsTestCase
 *
 * @package Unit\Everyware\Plugin\RedirectOriginalUrls
 */
abstract class RedirectOriginalUrlsTestCase extends TestCase
{
}
