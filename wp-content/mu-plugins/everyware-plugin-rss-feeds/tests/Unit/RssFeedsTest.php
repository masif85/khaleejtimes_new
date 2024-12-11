<?php
declare(strict_types=1);

namespace Unit\Everyware\RssFeeds;

use Everyware\RssFeeds\RssFeeds;
use PHPUnit\Framework\TestCase;

/**
 * Class RssFeedsTest
 * @package Unit\Everyware\RssFeeds
 */
class RssFeedsTest extends RssFeedsTestCase
{
    public function testModifyTemplateHierarchy(): void
    {
        $postType = RssFeeds::POST_TYPE_ID;
        $filenameForSlug = 'single-' . $postType . '-super_slug.php';
        $filenameForPostType = 'single-' . $postType . '.php';
        $filenameSingle = 'single.php';

        /** @var array $normalTemplateList A normal list of templates. */
        $normalTemplateList = [
            $filenameForSlug,
            $filenameForPostType,
            $filenameSingle
        ];

        $newArray = RssFeeds::modifyTemplateHierarchy($normalTemplateList);

        $insertedPath = '../../src/templates/' . $filenameForPostType;

        // Assert that the new path was inserted right after $filenameForPostType.
        self::assertCount(4, $newArray);
        self::assertSame($filenameForSlug, $newArray[0]);
        self::assertSame($insertedPath, $newArray[1]);
        self::assertSame($filenameForPostType, $newArray[2]);
        self::assertSame($filenameSingle, $newArray[3]);

        /** @var array $emptyTemplateList An empty list; shouldn't normally happen. */
        $emptyTemplateList = [];

        $newArray = RssFeeds::modifyTemplateHierarchy($emptyTemplateList);

        // Assert that the new path was not inserted.
        self::assertCount(0, $newArray);
    }
}
