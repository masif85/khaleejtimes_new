<?php declare(strict_types=1);

namespace Everyware\Concepts;

use Everyware\Concepts\Admin\SubPage;
use Everyware\Concepts\Exceptions\ConceptRegistrationError;
use Everyware\Concepts\Exceptions\ConceptTypeRegistrationError;
use WP_Error;
use WP_Post;

/**
 * ConceptAdmin
 *
 * @link    http://infomaker.se
 * @package Everyware\Concepts
 * @since   Everyware\Concepts\ConceptAdmin 1.0.0
 */
class ConceptAdmin
{
    private $subPages = [];

    /**
     * @var string
     */
    public const POST_TYPE_SLUG = 'concepts';

    private function __construct()
    {
        $this->registerPostType();
        $this->registerTaxonomy();
        $this->addActions()->addFilters();

        if ( ! is_admin()) {
            ConceptPostRouter::init();
        }
    }

    /**
     * Call this method to get singleton
     *
     * @return ConceptAdmin
     */
    public static function instance(): ConceptAdmin
    {
        static $inst = null;
        if ($inst === null) {
            $inst = new static;
        }

        return $inst;
    }

    /**
     * @throws Exceptions\ConceptRegistrationError
     */
    public function registerPostType(): void
    {
        $postType = register_post_type(Concepts::POST_TYPE_ID, [
            'labels' => $this->getPostLabels('Concept'),
            'description' => __(
                'Concepts are used to add meta data to object from Open Content.',
                CONCEPTS_TEXT_DOMAIN),
            'public' => true,
            'query_var' => true,
            'map_meta_cap' => true,
            'menu_position' => 20,
            'menu_icon' => 'dashicons-networking',
            'hierarchical' => true,
            'capabilities_type' => 'page',
            'supports' => ['title', 'custom-fields', 'page-attributes'],
            'rewrite' => [
                'slug' => static::POST_TYPE_SLUG,
                'with_front' => false
            ],
            'show_in_rest' => true,
            'rest_base' => static::POST_TYPE_SLUG,
            'rest_controller_class' => 'WP_REST_Posts_Controller',
        ]);

        if (is_wp_error($postType)) {
            throw new ConceptRegistrationError($postType->get_error_message());
        }
    }

    /**
     *
     * @throws ConceptTypeRegistrationError
     */
    public function registerTaxonomy(): void
    {
        $result = register_taxonomy(ConceptTypes::TAXONOMY_ID, Concepts::POST_TYPE_ID, [
            'labels' => $this->getTaxonomyLabels('Type'),
            'description' => __('The type of concept.', CONCEPTS_TEXT_DOMAIN),
            'public' => false,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_nav_menus' => false,
            'show_admin_column' => true,
            'query_var' => false,
            'rewrite' => false,
        ]);

        if ($result instanceof WP_Error) {
            throw new ConceptTypeRegistrationError($result->get_error_message());
        }
    }

    /**
     * @param string $name
     * @param string $plural
     *
     * @return array
     * @since 1.0.0
     */
    private function getPostLabels($name, $plural = ''): array
    {
        $singularName = $name;
        $pluralName = empty($plural) ? "{$name}s" : $plural;

        return [
            'name' => $pluralName,
            'singular_name' => $singularName,
            'add_new' => sprintf(__('Add New %s', CONCEPTS_TEXT_DOMAIN), $singularName),
            'add_new_item' => sprintf(__('Add New %s', CONCEPTS_TEXT_DOMAIN), $singularName),
            'edit_item' => sprintf(__('Edit %s', CONCEPTS_TEXT_DOMAIN), $singularName),
            'new_item' => sprintf(__('New %s', CONCEPTS_TEXT_DOMAIN), $singularName),
            'view_item' => sprintf(__('View %s', CONCEPTS_TEXT_DOMAIN), $singularName),
            'view_items' => sprintf(__('View %s', CONCEPTS_TEXT_DOMAIN), $pluralName),
            'search_items' => sprintf(__('Search %s', CONCEPTS_TEXT_DOMAIN), $pluralName),
            'not_found' => sprintf(__('No %s found', CONCEPTS_TEXT_DOMAIN), $pluralName),
            'not_found_in_trash' => sprintf(__('No %s found in Trash', CONCEPTS_TEXT_DOMAIN), $pluralName),
            'parent_item_colon' => sprintf(__('Parent %s', CONCEPTS_TEXT_DOMAIN), $singularName),
            'all_items' => sprintf(__('All %s', CONCEPTS_TEXT_DOMAIN), $pluralName),
            'archives' => sprintf(__('%s Archives', CONCEPTS_TEXT_DOMAIN), $singularName),
            'attributes' => sprintf(__('%s Attributes', CONCEPTS_TEXT_DOMAIN), $singularName),

        ];
    }

    /**
     * @param string $name
     * @param string $plural
     *
     * @return array
     * @since 1.0.0
     */
    private function getTaxonomyLabels($name, $plural = ''): array
    {
        $singularName = _x($name, CONCEPTS_TEXT_DOMAIN);
        $pluralName = _x(empty($plural) ? "{$name}s" : $plural, CONCEPTS_TEXT_DOMAIN);

        return [
            'name' => $singularName,
            'singular_name' => $singularName,
            'menu_name' => $pluralName,
            'all_items' => sprintf(__('All %s', CONCEPTS_TEXT_DOMAIN), $pluralName),
            'edit_item' => sprintf(__('Edit %s', CONCEPTS_TEXT_DOMAIN), $singularName),
            'view_item' => sprintf(__('View %s', CONCEPTS_TEXT_DOMAIN), $singularName),
            'update_item' => sprintf(__('Update %s', CONCEPTS_TEXT_DOMAIN), $singularName),
            'add_new_item' => sprintf(__('Add New %s', CONCEPTS_TEXT_DOMAIN), $singularName),
            'new_item_name' => sprintf(__('New %s Name', CONCEPTS_TEXT_DOMAIN), $singularName),
            'search_items' => sprintf(__('Search %s', CONCEPTS_TEXT_DOMAIN), $pluralName),
            'popular_items' => sprintf(__('Popular %s', CONCEPTS_TEXT_DOMAIN), $pluralName),
            'add_or_remove_items' => sprintf(__('Add or remove %s', CONCEPTS_TEXT_DOMAIN), $pluralName),
            'choose_from_most_used' => sprintf(__('Choose from the most used %s', CONCEPTS_TEXT_DOMAIN), $pluralName),
            'not_found' => sprintf(__('No %s found', CONCEPTS_TEXT_DOMAIN), $pluralName),
            'back_to_items' => sprintf(__('← Back to %s', CONCEPTS_TEXT_DOMAIN), $pluralName),
            'parent_item' => null,
            'parent_item_colon' => null,
            'separate_items_with_commas' => null,
        ];
    }

    /**
     * @param string  $permalink
     * @param WP_Post $post
     *
     * @return string
     * @since 1.0.0
     */
    public function filterPermalink($permalink, WP_Post $post): string
    {
        return $post->post_type === Concepts::POST_TYPE_ID ?
            str_replace(static::POST_TYPE_SLUG . '/', '', $permalink) :
            $permalink;
    }

    public function addSubPage(Admin\SubPage $subPage): ConceptAdmin
    {
        $this->subPages[] = $subPage;

        return $this;
    }

    /**
     * Function to render menu item
     *
     * @return void
     */
    public function addSubmenus(): void
    {
        foreach ($this->subPages as $subPage) {
            if ($subPage instanceof SubPage) {
                $subPage->addSubPage(Concepts::POST_TYPE_ID);
            }
        }
    }

    /**
     * @return self
     */
    private function addActions(): self
    {
        // Register pages for menu.
        add_action('admin_menu', [&$this, 'addSubmenus']);

        return $this;
    }

    /**
     * @return self
     * @since 1.0.0
     */
    private function addFilters(): self
    {
        add_filter('post_type_link', [&$this, 'filterPermalink'], 1, 2);
        add_filter('post_link', [&$this, 'filterPermalink'], 1, 2);

        return $this;
    }

    /**
     * @return ConceptAdmin
     * @since 1.0.0
     */
    public static function init(): ConceptAdmin
    {
        return static::instance();
    }
}
