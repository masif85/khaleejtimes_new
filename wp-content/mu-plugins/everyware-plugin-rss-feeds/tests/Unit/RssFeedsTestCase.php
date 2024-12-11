<?php
declare(strict_types=1);

namespace Unit\Everyware\RssFeeds;

use Infomaker\Everyware\Twig\ViewSetup;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/wp-functions.php';

if (!defined('RSS_FEEDS_TEXT_DOMAIN')) {
    define('RSS_FEEDS_TEXT_DOMAIN', 'ew-rss-feeds');
}

/**
 * Class RssFeedsTestCase
 * @package Unit\Everyware\RssFeeds
 */
abstract class RssFeedsTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        ViewSetup::getInstance()->registerTwigFolder('rssFeedsPlugin', dirname(__FILE__, 3) . '/src/templates/');
    }
}
