<?php declare(strict_types=1);

namespace Everyware\Plugin\MenuHandler;

use Everyware\ProjectPlugin\Components\ComponentSettingsRepository;
use Everyware\ProjectPlugin\Components\SettingsProviders\CollectionDbProvider;
use Infomaker\Everyware\Support\Storage\CollectionDB;
use Infomaker\Everyware\Support\Str;

/**
 * Class SettingsDB
 * @package Everyware\Plugin\MenuHandler
 */
class PluginSettings extends ComponentSettingsRepository
{
    public const OPTION_NAME = 'ew_menu_handler_settings';

    private static $fields = [];

    public static function create()
    {
        return new static(new CollectionDbProvider(new CollectionDB(static::OPTION_NAME), static::$fields));
    }

    public static function create_fields()
    {
        return new CollectionDB(static::OPTION_NAME);
    }

    public function __construct () {
        add_filter( 'wp_setup_nav_menu_item', array( $this, 'add_custom_nav_field_color' ) );
        add_filter( 'wp_setup_nav_menu_item', array( $this, 'add_custom_nav_field_section' ) );
        add_action( 'wp_update_nav_menu_item', array( $this, 'update_custom_nav_fields'), 10, 3 );
        add_filter( 'wp_edit_nav_menu_walker', array( $this, 'return_walker'), 10, 2 );
    }

    /**
     * Define menu custom field value for color.
     */    
    public function add_custom_nav_field_color( $menu_item ) {
        $menu_item->color = get_post_meta( $menu_item->ID, '_menu_item_color', true );
        return $menu_item;
    }

    /**
     * Define menu custom field value for section.
     */    
    public function add_custom_nav_field_section( $menu_item ) {
        $menu_item->section = get_post_meta( $menu_item->ID, '_menu_item_section', true );
        return $menu_item;
    }

    /**
     * Update menu custom field value based on form field for color and sections.
     */    
    public function update_custom_nav_fields( $menu_id, $menu_item_db_id, $args ) {
        if ( is_array( $_REQUEST['menu-item-color']) ) {
            $color_value = $_REQUEST['menu-item-color'][$menu_item_db_id];
            update_post_meta( $menu_item_db_id, '_menu_item_color', $color_value );
        }
        if ( is_array( $_REQUEST['menu-item-section']) ) {
            $section_value = $_REQUEST['menu-item-section'][$menu_item_db_id];
            update_post_meta( $menu_item_db_id, '_menu_item_section', $section_value );
        }
    }

    /**
     * Add in form field for setting value.
     */
    public function return_walker($walker,$menu_id) {
        return 'Walker_Nav_Menu_Edit_Custom';
    }
}
