<?php

use Everyware\Everyboard\Exceptions\ArticleRelationNotFoundException;
use Everyware\Everyboard\Exceptions\EmptyListException;
use Everyware\Everyboard\Exceptions\InvalidListUuid;
use Everyware\Everyboard\Exceptions\ListNotFoundException;
use Everyware\Everyboard\OcApiAdapter;
use Everyware\Everyboard\OcArticleProvider;
use Everyware\Everyboard\OcListProvider;

class OcList
{
    private static $SLUG_SETTINGS = 'oclist_settings';
    private static $NOTIFIER_TYPE = 'oclist';
    public static $OPTIONS_NAME = 'OCLIST_OPTIONS';

    private static $listsHasBeenFetched = false;
    private static $listArticles = [];
    private static $listNames = [];
    private static $listArticleUuids = [];

    private static $default_options = [
        'list_query' => '*:*',
        'published_property' => '',
        'article_relation_property' => '',
        'notifier_bindings' => [],
        'notifier_registered' => false,
        'notifier_url' => ''
    ];

    /**
     * @var OcListProvider
     */
    private static $listProvider;

    /**
     * @var OcAPI
     */
    private static $ocApi;

    /**
     * @var bool
     */
    private static $notifierVerified;

    public function __construct()
    {
        if ($this->onSettingsPage()) {
            if (isset($_POST['oclist_settings_form_posted'])) {
                $this->save_settings();
            }

            $settings = static::getSettings();

            if ($settings['notifier_registered'] && ! static::testNotifierConnection($settings['notifier_url'])) {
                add_action('admin_notices', static function () {
                    ?>
                    <div class="notice notice-error is-dismissible">
                        <p><strong><?php _e('Error', 'everyboard'); ?>
                                !</strong> <?php _e('Failed to connect to Notifier', 'everyboard'); ?></p>
                    </div>
                    <?php
                });
                $settings['notifier_registered'] = false;
                static::storeSettings($settings);
            }

            add_action('admin_head', static function() {
                echo '<style type="text/css" id="oclist_settings_style">
#oclist_settings_form .description { max-width:700px; }
#oclist_settings_form select { width:165px; }
</style>';
            });
        }


        add_action('admin_menu', [&$this, 'admin_menu']);
        add_action('ew_notifier_' . static::$NOTIFIER_TYPE . '_added', function ($bindings) {
            $settings = static::getSettings();
            $settings['notifier_registered'] = true;
            $settings['notifier_bindings'] = isset($bindings) && $bindings !== '' ? $bindings : [];

            static::storeSettings($settings);
        });
    }

    public function admin_menu(): void
    {
        add_submenu_page('edit.php?post_type=everyboard', 'OC List Settings', __('OC List Settings', 'everyboard'),
            'manage_options', self::$SLUG_SETTINGS, [&$this, 'settings_page']);
    }

    public function settings_page(): void
    {
        include __DIR__ . '/views/settings.php';
    }

    public function save_settings(): void
    {
        $currentSettings = static::getSettings();
        $settings = [];

        $fieldToUpdate = [
            'list_query',
            'published_property',
            'article_relation_property',
            'notifier_url',
            'notifier_bindings'
        ];

        foreach ($fieldToUpdate as $name) {
            if (array_key_exists($name, $_POST)) {
                $settings[$name] = $_POST[$name];
            }
        }

        $addedBindings = [];
        if (isset($_POST['oc_options'])) {
            $addedBindings = array_filter($_POST['oc_options']['oc_notifier_bindings'] ?? []);
        }

        foreach ($addedBindings as $contenttype => $value) {
            if ( ! empty($value) && ! array_key_exists($value, $settings['notifier_bindings'])) {
                $settings['notifier_bindings'][$contenttype] = $value;
            }
        }

        // Only interpret "no bindings" as empty array if they are not disabled.
        if ( ! isset($settings['notifier_bindings']) && ! $currentSettings['notifier_registered']) {
            $settings['notifier_bindings'] = [];
        }

        if ( ! empty($settings['notifier_url']) && $settings['notifier_url'] !== $currentSettings['notifier_url']) {
            $settings['notifier_registered'] = static::testNotifierConnection($settings['notifier_url']);
        }

        static::storeSettings($settings);
    }

    /**
     * Fetch property names from OC, filter by contenttype and data type.
     *
     * @param $contenttype
     * @param $type
     *
     * @return array
     */
    private static function get_properties($contenttype, $type): array
    {
        return static::ocApi()->get_properties_by_type($contenttype, $type);
    }

    public static function get_all_lists(): array
    {
        // Get lists from memory
        if (static::$listsHasBeenFetched) {
            return static::$listNames;
        }

        $settings = static::getSettings();

        $result = static::ocApi()->search([
            'q' => $settings['list_query'] ?? '*:*',
            'contenttypes' => ['List'],
            'start' => 0,
            'limit' => 500,
            'properties' => [
                'uuid',
                'Name',
                'Type'
            ]
        ]);

        foreach ($result as $list) {
            if ($list instanceof OcObject && isset($list->uuid[0], $list->name[0])) {
                // Store fetched lists in memory
                static::$listNames[$list->uuid[0]] = $list->name[0];
            }
        }

        static::$listsHasBeenFetched = true;

        return static::$listNames;
    }

    /**
     * Function for fetching an article from a specific position from a specific list
     *
     * @param     $listId
     * @param int $positionId
     *
     * @return OcArticle|null
     */
    public static function get_article_from_list($listId, $positionId = 0): ?OcArticle
    {
        if ($positionId === 0) {
            return null;
        }

        $articles = static::get_articles($listId, $positionId) ?? [];

        return array_shift($articles);
    }

    /**
     * Function for fetching one or more articles from a list, begins fetching at $positionId
     *
     * @param     $listId
     * @param int $positionId
     * @param int $limit
     *
     * @return null|array
     */
    public static function get_articles_from_list($listId, $positionId = 0, $limit = 1): ?array
    {
        if ($positionId === 0 || $limit < 1) {
            return null;
        }

        $articles = static::get_articles($listId);

        return ! empty($articles) ? $articles : null;
    }

    /**
     * Function for fetching all approved articles included in a specific list
     * Filters out articles that don't have correct status or are in the future
     *
     * @param $listId
     *
     * @return array
     */
    public static function get_approved_list_articles($listId)
    {
        // Get list from memory
        if (array_key_exists($listId, static::$listArticles)) {
            return static::$listArticles[$listId];
        }

        $settings = static::getSettings();

        if (empty($settings['article_relation_property'])) {
            return [];
        }

        $relation_property = $settings['article_relation_property'];
        $properties = self::get_approved_list_articles_properties($relation_property, $settings['published_property']);

        $result = static::ocApi()->search([
            'q' => 'uuid:' . $listId,
            'contenttypes' => ['List'],
            'start' => 0,
            'limit' => 1,
            'properties' => $properties,
            'filters' => $relation_property . '(q=' . $settings['published_property'] . ':true|start=0|limit=100)'
        ], true, false);

        $articles = [];
        if (isset($result[0], $result[0]->uuid[0], $result[0]->name[0]) && $result[0] instanceof OcObject) {

            $lower_relation_property = strtolower($relation_property);
            if (isset($result[0]->{$lower_relation_property})) {
                $articles = $result[0]->{$lower_relation_property};
            }
        }

        // Store articles in memory if the articles are requested again
        static::$listArticles[$listId] = $articles;

        return $articles;
    }

    public static function get_approved_list_articles_query($listId)
    {
        $settings = static::getSettings();

        if (empty($settings['article_relation_property'])) {
            return [];
        }

        $relation_property = $settings['article_relation_property'];
        $properties = self::get_approved_list_articles_properties($relation_property, $settings['published_property']);

        return static::ocApi()->get_search_query([
            'q' => 'uuid:' . $listId,
            'contenttypes' => ['List'],
            'start' => 0,
            'limit' => 1,
            'properties' => $properties,
            'filters' => $relation_property . '(q=' . $settings['published_property'] . ':true|start=0|limit=100)'
        ]);
    }

    private static function get_approved_list_articles_properties($relation_property, $published_property)
    {
        $article_props = self::get_article_props();
        $properties = [
            'uuid',
            'Name',
            $relation_property . '.uuid',
            $relation_property . '.contenttype',
            $relation_property . '.' . $published_property
        ];

        return array_merge($properties, $article_props);
    }

    public static function get_article_props()
    {
        $property_map = get_option('prop_map');
        $options = get_option(self::$OPTIONS_NAME);
        $options = $options !== false ? json_decode($options, true) : [
            'published_property' => '',
            'article_relation_property' => ''
        ];

        $article_properties = self::get_properties('Article', null);
        $properties = [];

        // Make sure we have an array here
        if ( ! is_array($article_properties)) {
            $article_properties = [];
        }

        if (isset($property_map['Headline']) && $property_map['Headline'] !== 'unknown') {

            foreach ($article_properties as $prop) {

                if (strtolower($prop) === $property_map['Headline']) {
                    $properties[] = $options['article_relation_property'] . '.' . $prop;
                    break;
                }
            }
        }

        if (isset($property_map['Section']) && $property_map['Section'] !== 'unknown') {

            foreach ($article_properties as $prop) {

                if (strtolower($prop) === $property_map['Section']) {
                    $properties[] = $options['article_relation_property'] . '.' . $prop;
                    break;
                }
            }
        }

        return $properties;
    }

    public static function get_list_name_by_id(string $listUuid): string
    {
        // Get name from memory
        if (array_key_exists($listUuid, static::$listNames)) {
            return static::$listNames[$listUuid];
        }

        $listProperties = static::ocListProvider()->getListProperties($listUuid, ['Name']);

        $name = $listProperties['Name'] ?? 'Cant find list name';

        // Store in memory
        static::$listNames[$listUuid] = $name;

        return $name;
    }

    public static function get_wp_sites()
    {
        $sites = get_sites([
            'site__not_in' => get_current_blog_id()
        ]);

        $wp_sites = [];
        foreach ($sites as $site) {
            $site_id = (int)$site->blog_id;
            $wp_sites[$site_id] = $site->domain;
        }

        return $wp_sites;
    }

    /**
     * Function for fetching one or more articles from a list, begins fetching at $positionId
     *
     * @param     $listUuid
     * @param int $positionId
     * @param int $limit
     *
     * @return array
     */
    public static function get_articles(string $listUuid, int $positionId = 0, int $limit = 1): array
    {
        if ($positionId === 0 || $limit < 1) {
            return [];
        }

        $articleUuids = array_slice(static::get_article_uuids($listUuid), $positionId - 1, $limit);
        $articleProvider = OcArticleProvider::create();

        return array_map([$articleProvider, 'getArticle'], $articleUuids);
    }

    /**
     * @param string $listUuid
     *
     * @return array
     */
    public static function get_article_uuids(string $listUuid): array
    {
        if (empty($listUuid)) {
            return [];
        }

        try {
            return static::get_article_uuids_or_fail($listUuid);
        } catch (InvalidListUuid | ListNotFoundException | ArticleRelationNotFoundException $e) {
            error_log($e->getMessage());
        }

        return [];
    }

    /**
     * @param string $listUuid
     *
     * @return array
     * @throws InvalidListUuid|ListNotFoundException|ArticleRelationNotFoundException
     */
    public static function get_article_uuids_or_fail(string $listUuid): array
    {
        // Get uuids from memory
        if (array_key_exists($listUuid, static::$listArticleUuids)) {
            return static::$listArticleUuids[$listUuid];
        }

        if ( ! static::hasArticleRelation()) {
            throw new ArticleRelationNotFoundException(
                __('Could not find an relation property between List and Articles from the OC List settings.',
                    'everyboard')
            );
        }

        $uuids = static::ocListProvider()->getRelatedUuids(
            $listUuid,
            static::getArticleRelation(),
            static::getRelatedQueryFilter()
        );

        // Store in memory
        static::$listArticleUuids[$listUuid] = $uuids;

        return $uuids;
    }

    private static function getArticleRelation(): string
    {
        $settings = static::getSettings();

        return $settings['article_relation_property'] ?? '';
    }

    private static function getPublishedProperty(): string
    {
        $settings = static::getSettings();

        return $settings['published_property'] ?? '';
    }

    private static function getRelatedQueryFilter(): string
    {
        $published = static::getPublishedProperty();

        return ! empty($published) ? "{$published}:true" : '';
    }

    private static function hasArticleRelation(): bool
    {
        return ! empty(static::getArticleRelation());
    }

    public static function getSettings(): array
    {
        $options = get_option(self::$OPTIONS_NAME, '');
        $options = ! empty($options) ? json_decode($options, true) : [];
        $settings = array_replace_recursive(static::$default_options, $options);

        return static::enforceArticleRelation($settings);
    }

    private static function enforceArticleRelation(array $settings): array
    {
        if ( ! empty($settings['article_relation_property'] ?? '')) {
            return $settings;
        }

        try {
            $relations = static::ocApi()->get_properties_by_type('List', 'Article');

            if (empty($relations)) {
                throw new ArticleRelationNotFoundException('Could not fetch article relation property from Open Content');
            }

            $defaultRelation = 'Articles';

            $settings['article_relation_property'] = in_array($defaultRelation, $relations, true) ?
                $defaultRelation : $relations[0];
            static::storeSettings($settings);

        } catch (Exception $e) {
            trigger_error($e->getMessage());
        }

        return $settings;
    }

    private static function storeSettings(array $settings): void
    {
        $mergedSettings = array_replace(static::$default_options, $settings);
        update_option(self::$OPTIONS_NAME, json_encode($mergedSettings));
    }

    private static function testNotifierConnection(string $url): bool
    {
        if (empty($url)) {
            return false;
        }

        if (static::$notifierVerified === null) {
            static::$notifierVerified = filter_var(
                OpenContent::getInstance()->getNotifierRegistered($url, static::$NOTIFIER_TYPE),
                FILTER_VALIDATE_BOOLEAN);
        }

        return static::$notifierVerified;
    }

    public static function init(): OcList
    {
        return new static();
    }

    protected static function ocListProvider(): OcListProvider
    {
        if ( ! static::$listProvider) {
            static::$listProvider = new OcListProvider(new OcApiAdapter(new OcAPI()));
        }

        return static::$listProvider;
    }

    protected static function ocApi(): OcAPI
    {
        if ( ! static::$ocApi) {
            static::$ocApi = new OcAPI();
        }

        return static::$ocApi;
    }

    private function onSettingsPage(): bool
    {
        if ( ! is_admin()) {
            return false;
        }

        if ( ! isset($_GET['post_type'], $_GET['page'])) {
            return false;
        }

        global $pagenow;

        return $pagenow === 'edit.php' &&
            $_GET['post_type'] === 'everyboard' &&
            $_GET['page'] === 'oclist_settings';
    }
}
