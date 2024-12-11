<?php declare(strict_types=1);


namespace Everyware\RssFeeds;


use Everyware\ProjectPlugin\Components\SettingsHandler;
use Everyware\RssFeeds\Exceptions\InvalidListUuid;
use Everyware\RssFeeds\Exceptions\InvalidQueryOrUnauthorized;
use Infomaker\Everyware\Base\Pages;
use Infomaker\Everyware\Twig\View;

/**
 * Class FeedSettings
 *
 * Handles the metabox to admin a feed's settings.
 *
 * @package Everyware\RssFeeds
 */
class FeedSettings extends RssFeedsMetabox
{
    public static $postmetaId = 'rss-feed-settings';

    protected $metaBoxId = 'rss-feed-settings';

    /**
     * Contains the fields of the metabox form.
     *
     * @var array $fields
     *     ['description']               string|null
     *     ['link_type']                 string       'page' or 'url'
     *     ['link_page']                 int|null     Page ID. Only used if link_type is 'page'.
     *     ['link_url']                  string|null  URL. Only used if link_type is 'url'.
     *     ['feed_source']               string       'list' or 'query'.
     *     ['feed_source_list']          string|null  ?? Only used if feed_source is 'list'.
     *     ['feed_source_query']         string|null  ?? Only used if feed_source is 'query'.
     *     ['feed_source_query_sorting'] string|null
     *     ['feed_source_query_start']   int|null     Starting point for the limiter
     *     ['feed_source_query_limit']   int|null     Maximum number of items
     */
    protected $fields = [
        'description' => null,
        'link_type' => 'page',
        'link_page' => null,
        'link_url' => null,
        'feed_source' => 'list',
        'feed_source_list' => null,
        'feed_source_query'         => '*:*',
        'feed_source_query_sorting' => null,
        'feed_source_query_start'   => 0,
        'feed_source_query_limit'   => 10
    ];

    protected $version = '1.0.0';

    /**
     * @var OcAPI|mixed
     */
    private $ocApi = null;

    /**
     * Ajax function to validate the number of articles in an OC list.
     *
     * @global string $_REQUEST['uuid']
     *
     * @param OcApiHandler|mixed $ocApiHandler
     *
     * @return OcApiResponse
     */
    public static function validateOcList($ocApiHandler): OcApiResponse
    {
        $request = stripslashes_deep($_REQUEST);

        try {
            $count = $ocApiHandler->testArticleCountForList($request['uuid']);
        } catch (InvalidListUuid $e) {
            return new OcApiResponse(
                OcApiResponse::RESPONSE_TYPE_ERROR,
                __('Invalid UUID', RSS_FEEDS_TEXT_DOMAIN)
            );
        }

        if ($count === 0) {
            return new OcApiResponse(
                OcApiResponse::RESPONSE_TYPE_WARNING,
                __('No articles in list', RSS_FEEDS_TEXT_DOMAIN)
            );
        }

        return new OcApiResponse(
            OcApiResponse::RESPONSE_TYPE_OK,
            str_replace('%n', $count, __('%n articles', RSS_FEEDS_TEXT_DOMAIN))
        );
    }

    /**
     * Ajax function to validate the number of articles from an OC query.
     *
     * @global string $_REQUEST['query']
     * @global string $_REQUEST['start']
     * @global string $_REQUEST['limit']
     *
     * @param OcApiHandler|mixed $ocApiHandler
     *
     * @return OcApiResponse
     */
    public static function validateOcQuery($ocApiHandler): OcApiResponse
    {
        $request = stripslashes_deep($_REQUEST);

        try {
            $count = $ocApiHandler->testArticleCountForQuery($request['query'], (int)$request['start'], (int)$request['limit']);
        } catch (InvalidQueryOrUnauthorized $e) {
            return new OcApiResponse(
                OcApiResponse::RESPONSE_TYPE_ERROR,
                __('Invalid query, or unauthorized', RSS_FEEDS_TEXT_DOMAIN)
            );
        }

        if ($count === 0) {
            return new OcApiResponse(
                OcApiResponse::RESPONSE_TYPE_WARNING,
                __('No articles found', RSS_FEEDS_TEXT_DOMAIN)
            );
        }

        return new OcApiResponse(
            OcApiResponse::RESPONSE_TYPE_OK,
            str_replace('%n', $count, __('%n articles', RSS_FEEDS_TEXT_DOMAIN))
        );
    }

    /**
     * Function required by the metabox-child. Returns the string-representation of the metabox-form
     *
     * @param array $formSettings
     * @param array $storedData
     *
     * @return string
     */
    protected function formContent(array $formSettings, array $storedData): string
    {
        $settingsHandler = (new SettingsHandler($this->metaInputPrefix));
        $formData = $settingsHandler->generateFormData($storedData);

        return View::generate($this->templatePath . 'feed-settings', array_replace($formData, [
            'pages'    => $this->getPagesSelectData($storedData['link_page']),
            'sortings' => $this->getSortingsSelectData($storedData['feed_source_list']),
            'lists'    => $this->getListsSelectData(),
            'settings' => $formSettings,
            'form'     => $this->formBuilder
        ]));
    }

    /**
     * Fires on saving updated metabox
     *
     * @param array $newInstance
     * @param array $oldInstance
     *
     * @return array $newInstance The form data to be saved
     */
    protected function onChange(array $newInstance, array $oldInstance = []): array
    {
        $this->cleanUpFormData($newInstance);

        return $newInstance;
    }

    /**
     * Fires on saving new metabox
     *
     * @param array $newInstance
     *
     * @return array $newInstance The form data to be saved
     */
    protected function onStore(array $newInstance): array
    {
        $this->cleanUpFormData($newInstance);

        return $newInstance;
    }

    /**
     * Function for validating the form
     *
     * @param array $newInstance
     * @param array $rules
     *
     * @todo Make sure the user gets an error message if form is submitted with invalid data.
     *
     * @return bool
     */
    protected function validate(array $newInstance, array $rules): bool
    {
        switch ($newInstance['link_type']) {
            case 'page':
                if (!$this->inSelectData($newInstance['link_page'], Pages::getSelectData())) {
                    return false;
                }
                break;
            case 'url':
                /** @todo detect valid URL syntax more thoroughly */
                if (parse_url($newInstance['link_url']) === false) {
                    return false;
                }
                break;
            default:
                return false;
        }

        switch ($newInstance['feed_source']) {
            case 'list':
                if (!$this->inSelectData($newInstance['feed_source_list'], $this->getListsSelectData())) {
                    return false;
                }
                break;
            case 'query':
                if (!$this->inSelectData($newInstance['feed_source_query_sorting'], $this->getSortingsSelectData())) {
                    return false;
                }
                break;
            default:
                return false;
        }

        return true;
    }

    /**
     * If previously selected option is no longer available, add a blank option first.
     *
     * @param array      $options        [ [ 'value' => string, 'text' => string ], ... ]
     * @param mixed|null $selectedValue
     *
     * @return array [ [ 'value' => string, 'text' => string ], ... ]
     */
    private static function fallBackIfSelectedOptionIsMissing(array $options, $selectedValue = null): array
    {
        foreach ($options as $option) {
            if ($option['value'] === $selectedValue) {
                return $options;
            }
        }
        array_unshift($options, [
            'value' => '',
            'text' => ''
        ]);
        return $options;
    }

    /**
     * @param int|null $savedOption
     *
     * @return array [ [ 'value' => string, 'text' => string ], ... ]
     */
    private function getPagesSelectData(int $savedOption = null): array
    {
        $options = Pages::getSelectData();
        return self::fallBackIfSelectedOptionIsMissing($options, $savedOption);
    }

    /**
     * @param string|null $savedOption
     *
     * @return array [ [ 'value' => string, 'text' => string ], ... ]
     */
    private function getSortingsSelectData(string $savedOption = null): array
    {
        $ocApiHandler = new OcApiHandler();
        $sort_options = $ocApiHandler->getSortOptions();
        $result = [];

        if (isset($sort_options->sortings)) {
            $result =  array_map(static function ($option) {
                return [
                    'value' => $option->name,
                    'text' => $option->name
                ];
            }, $sort_options->sortings);
        }

        $result = self::fallBackIfSelectedOptionIsMissing($result, $savedOption);

        return $result;
    }

    /**
     * @return array [ [ 'value' => string, 'text' => string ], ... ]
     */
    private function getListsSelectData(): array
    {
        $ocApiHandler = new OcApiHandler();
        $lists = $ocApiHandler->getLists();
        $result = [];

        foreach ($lists as $key => $text) {
            $result[] = [
                'value' => $key,
                'text' => $text
            ];
        }

        return $result;
    }

    /**
     * Returns true if $haystack has any element where the key 'value' matches $needle.
     *
     * @param string $needle
     * @param array $haystack [ [ 'value' => string, 'text' => string ], ... ]
     * @return bool
     */
    private function inSelectData(string $needle, array $haystack): bool
    {
        foreach ($haystack as $straw) {
            if ((string)$straw['value'] === $needle) {
                return true;
            }
        }
        return false;
    }

    /**
     * Clean up form data before saving.
     *
     * @param array $data
     */
    private function cleanUpFormData(array &$data): void
    {
        // Make sure unused fields are reset to null.
        if ($data['description'] === '') {
            $data['description'] = null;
        }
        if ($data['link_type'] === 'page') {
            $data['link_page'] = (int)$data['link_page'];
            $data['link_url'] = null;
        } else {
            $data['link_page'] = null;
        }
        if ($data['feed_source'] === 'list') {
            $data['feed_source_query'] = null;
            $data['feed_source_query_sorting'] = null;
            $data['feed_source_query_start'] = null;
            $data['feed_source_query_limit'] = null;
        } else {
            $data['feed_source_list'] = null;
        }

        // Make sure numeric fields are not saved as strings.
        if ($data['feed_source'] === 'query') {
            $data['feed_source_query_start'] = max(0, (int)$data['feed_source_query_start']);
            $data['feed_source_query_limit'] = is_numeric($data['feed_source_query_limit'])
                ? max(1, (int)$data['feed_source_query_limit'])
                : null;
        }
    }
}
