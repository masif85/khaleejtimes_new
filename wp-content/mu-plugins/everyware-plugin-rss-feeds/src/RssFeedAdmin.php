<?php declare(strict_types=1);

namespace Everyware\RssFeeds;

use Everyware\RssFeeds\Exceptions\RssFeedRegistrationError;

class RssFeedAdmin
{
    /**
     * @var string
     */
    public const POST_TYPE_SLUG = 'rss';

    /**
     * @return RssFeedAdmin
     */
    public static function init(): RssFeedAdmin
    {
        return static::instance();
    }

    /**
     * Call this method to get singleton
     *
     * @return RssFeedAdmin
     */
    public static function instance(): RssFeedAdmin
    {
        static $inst = null;
        if ($inst === null) {
            $inst = new self();
        }

        return $inst;
    }

    private function __construct()
    {
        $this->registerPostType();
    }

    /**
     * @throws RssFeedRegistrationError
     */
    private function registerPostType(): void
    {
        $postType = register_post_type(RssFeeds::POST_TYPE_ID, [
            'labels' => $this->getPostLabels('RSS feed'),
            'description' => __(
                'RSS feeds allow people to subscribe to articles that match their interests.',
                RSS_FEEDS_TEXT_DOMAIN),
            'public' => true,
            'query_var' => true,
            'map_meta_cap' => true,
            'show_in_menu' => true,
            'menu_position' => 25,
            'menu_icon' => 'dashicons-rss',
            'supports' => ['title','author'],
            'rewrite' => [
                'slug' => static::POST_TYPE_SLUG,
                'with_front' => true
            ]
        ]);

        if (is_wp_error($postType)) {
            throw new RssFeedRegistrationError($postType->get_error_message());
        }
    }

    /**
     * @param string $name
     * @param string $plural
     *
     * @return string[]
     */
    private function getPostLabels(string $name, string $plural = ''): array
    {
        $singularName = $name;
        $pluralName = empty($plural) ? "{$name}s" : $plural;

        return [
            'name' => $pluralName,
            'singular_name' => $singularName,
            'add_new' => sprintf(__('Add New %s', RSS_FEEDS_TEXT_DOMAIN), $singularName),
            'add_new_item' => sprintf(__('Add New %s', RSS_FEEDS_TEXT_DOMAIN), $singularName),
            'edit_item' => sprintf(__('Edit %s', RSS_FEEDS_TEXT_DOMAIN), $singularName),
            'new_item' => sprintf(__('New %s', RSS_FEEDS_TEXT_DOMAIN), $singularName),
            'view_item' => sprintf(__('View %s', RSS_FEEDS_TEXT_DOMAIN), $singularName),
            'view_items' => sprintf(__('View %s', RSS_FEEDS_TEXT_DOMAIN), $pluralName),
            'search_items' => sprintf(__('Search %s', RSS_FEEDS_TEXT_DOMAIN), $pluralName),
            'not_found' => sprintf(__('No %s found', RSS_FEEDS_TEXT_DOMAIN), $pluralName),
            'not_found_in_trash' => sprintf(__('No %s found in Trash', RSS_FEEDS_TEXT_DOMAIN), $pluralName),
            'all_items' => sprintf(__('All %s', RSS_FEEDS_TEXT_DOMAIN), $pluralName),
            'archives' => sprintf(__('%s Archives', RSS_FEEDS_TEXT_DOMAIN), $singularName),
            'attributes' => sprintf(__('%s Attributes', RSS_FEEDS_TEXT_DOMAIN), $singularName),
        ];
    }
}
