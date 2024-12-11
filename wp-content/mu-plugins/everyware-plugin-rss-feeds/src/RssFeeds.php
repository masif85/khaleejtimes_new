<?php declare(strict_types=1);

namespace Everyware\RssFeeds;

/**
 * Class RssFeeds
 * @package Everyware\RssFeeds
 */
class RssFeeds
{
    public const POST_TYPE_ID = 'rss-feed';

    /**
     * Enable the plugin to load an RSS feed template from its own directory. The theme may override it.
     *
     * @param string[] $pageTemplates A list of possible filenames, in order of priority.
     *
     * @return string[]
     *
     * @see get_query_template()
     */
    public static function modifyTemplateHierarchy(array $pageTemplates): array
    {
        $filename = 'single-' . self::POST_TYPE_ID . '.php';

        $themeDir = get_template_directory();
        $pluginTemplateDir = __DIR__ . '/templates';

        /**
         * @var string Relative path between the theme directory and the plugin's template directory.
         *             Doesn't work if it's an absolute path.
         */
        $relativePath = self::getRelativePathBetween($themeDir, $pluginTemplateDir);

        $pathToInsert = $relativePath . '/' . $filename;

        foreach ($pageTemplates as $i => $pageTemplate) {
            if ($pageTemplate !== $filename) {
                continue;
            }

            // By inserting the new path directly after the original one, the theme still has the option to override it.
            array_splice($pageTemplates, $i, 0, [$pathToInsert]);

            return $pageTemplates;
        }

        return $pageTemplates;
    }

    /**
     * Get the relative path between two absolute paths.
     *
     * @param string $dirA Example: "/best/captain/is/james/kirk"
     * @param string $dirB Example: "/best/captain/is/jean/luc/picard"
     *
     * @return string      Example: "../../jean/luc/picard"
     */
    private static function getRelativePathBetween(string $dirA, string $dirB): string
    {
        $ds = DIRECTORY_SEPARATOR;
        $partsA = explode($ds, $dirA);
        $partsB = explode($ds, $dirB);

        // Remove common beginnings.
        while (count($partsA) > 0 && count($partsB) > 0 && $partsA[0] === $partsB[0]) {
            array_shift($partsA);
            array_shift($partsB);
        }

        // Climb up from A.
        $result = str_repeat('..' . $ds, count($partsA));

        // Climb down into B.
        $result .= implode($ds, $partsB);

        return $result;
    }
}
