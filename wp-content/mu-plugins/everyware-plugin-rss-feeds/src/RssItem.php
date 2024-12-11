<?php declare(strict_types=1);


namespace Everyware\RssFeeds;


use DateTimeImmutable;
use Everyware\RssFeeds\Contracts\RssItemInterface;
use Infomaker\Everyware\NewsML\NewsMLTransformerManager;
use Infomaker\Everyware\NewsML\Objects\ImageItem;
use Infomaker\Everyware\NewsML\Objects\TeaserItem;
use Infomaker\Everyware\NewsML\Parsers\TeaserParser;
use Infomaker\Imengine\CropData\CropResizeData;
use Infomaker\Imengine\Helpers\ImengineHelper;
use Infomaker\Imengine\Imengine;
use OcArticle;
use OcObject;

/**
 * Class RssItem
 * @package Everyware\RssFeeds
 */
class RssItem implements RssItemInterface
{
    private const IMAGE_RATIO = '16:9';

    private const IMAGE_FORMAT = 'jpg';

    /**
     * @var OcArticle
     */
    private $article;

    /**
     * array
     */
    private $viewItems;

    private function __construct()
    {
        $this->viewItems = [];
    }

    /**
     * Initialize item with the elements from contents of an article, filtered by item settings.
     *
     * @param OcArticle $article
     * @param array     $itemSettings
     *
     * @return RssItemInterface
     */
    public static function fromOcArticle(/*OcArticle*/ $article, array $itemSettings): RssItemInterface
    {
        $result = new self();
        $result->article = $article;

        $teaser = $result->getTeaser();

        $image = ($teaser instanceof TeaserItem && $teaser->hasImage()) ? $teaser->getImage() : null;

        $result->viewItems = [
            'link' => $article->get_permalink(),
            'guid' => $article->get_value('uuid'),
        ];

        if ($itemSettings['has_image'] === true && $image instanceof ImageItem) {
            $result->viewItems['images'] = [];
            $imageWidth = $itemSettings['image_width'];
            $imageHeight = ImengineHelper::calculateRatioHeight($imageWidth, self::IMAGE_RATIO);
            $imageItem = [
                'width'  => $imageWidth,
                'height' => $imageHeight,
                'url'    => self::getImageUrl($image, $imageWidth, $imageHeight),
                'type'   => 'image/' . self::IMAGE_FORMAT
            ];

            if ($itemSettings['has_media_credit'] === true && $image->authors !== null) {
                $imageItem['authors'] = [];
                foreach ($image->authors as $author) {
                    $imageItem['authors'][] = $author['title'];
                }
            }

            $result->viewItems['images'][] = $imageItem;
        }

        if ($itemSettings['has_title'] === true) {
            // Fallback if the preferred value is missing.
            if ($teaser instanceof TeaserItem && !empty((string)$teaser->headline)) {
                $value = (string)$teaser->headline;
            } elseif (!empty($article->get_value('TeaserHeadline'))) {
                $value = $article->get_value('TeaserHeadline');
            } else {
                $value = $article->get_value('headline');
            }

            if ($value !== '') {
                $result->viewItems['title'] = self::cleanUpString(strip_tags($value));
            }
        }

        if ($itemSettings['has_description'] === true) {
            // Fallback if the preferred value is missing.
            $value = ($teaser instanceof TeaserItem) ? $teaser->text : $article->get_value('TeaserBody');

            $result->viewItems['description'] = self::cleanUpString($value);
        }

        if ($itemSettings['has_dc_creator'] === true) {

            /** @var OcObject $authors */
            $authors = $article->get_value('Authors');

            if ($authors instanceof OcObject) {
                $result->viewItems['creators'] = [];
                foreach ($authors->get('name') as $authorName) {
                    $result->viewItems['creators'][] = $authorName;
                }
            }
        }

        if ($itemSettings['has_pub_date'] === true && !empty($article->get_value('Pubdate'))) {

            $result->viewItems['pubdate'] = new DateTimeImmutable($article->get_value('Pubdate'));
        }

        if ($itemSettings['has_category'] === true) {

            /** @var OcObject|null $categories */
            $categories = $article->get_value('Categories');

            if ($categories instanceof OcObject) {
                $result->viewItems['categories'] = [];
                foreach ($categories->get('Name') as $categoryName) {
                    $result->viewItems['categories'][] = $categoryName;
                }
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getViewItems(): array
    {
        return $this->viewItems;
    }

    /**
     * Clean up some unwanted things that are sometimes present in OcArticle XML/HTML strings.
     *
     * @param string $string
     *
     * @return string
     */
    private static function cleanUpString(string $string): string
    {
        // Fix unencoded ampersands.
        $string = str_replace('&amp;amp;', '&amp;', str_replace('&', '&amp;', $string));

        // Remove redundant whitespaces.
        $string = preg_replace('/\s+/', ' ', $string);

        return trim($string);
    }

    /**
     * @return TeaserItem|null
     */
    private function getTeaser(): ?TeaserItem
    {
        $xmlString = $this->article->get_value('TeaserRaw');
        if (empty($xmlString)) {
            return null;
        }

        NewsMLTransformerManager::registerObjectParser(TeaserParser::OBJECT_TYPE, new TeaserParser());
        $transformer = NewsMLTransformerManager::createObjectTransformer();

        /** @var TeaserItem $teaser */
        $result = $transformer->transform($xmlString);
        foreach ($result as $object) {
            if ($object instanceof TeaserItem) {
                return $object;
            }
        }

        return null;
    }

    /**
     * @param ImageItem $image
     * @param int       $targetWidth
     * @param int       $targetHeight
     *
     * @return string
     */
    private static function getImageUrl(ImageItem $image, int $targetWidth, int $targetHeight): string
    {
        if ($image->hasCropForRatio(self::IMAGE_RATIO)) {
            $crop = $image->getCropForRatio(self::IMAGE_RATIO);
            $cropResizeData = (new CropResizeData($crop['width'], $crop['height']))
                ->setCropX($crop['x'])
                ->setCropY($crop['y'])
                ->setWidth($targetWidth)
                ->setHeight($targetHeight);
            $imengine = Imengine::cropResize($cropResizeData);
        } else {
            $imengine = Imengine::thumbnail($targetWidth, $targetHeight);
        }

        return $imengine->format(self::IMAGE_FORMAT)->fromUuid($image->uuid);
    }
}
