<?php
/*
 * Plugin-part for prefab widgets in Wordpress
 */

class EveryBoard_WidgetPrefab
{
    private static $instance;

    private $widget_to_board_map = [];
    private $widget_initialize_map = [];
    private $sidebar_widget_initialize_map = [];
    private $blog_switched = false;

    public static function get_instance(): EveryBoard_WidgetPrefab
    {
        if (is_null(self::$instance)) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    private function __construct()
    {
        add_action('admin_menu', [&$this, 'register_widgetprefab_submenu_page']);

        if ($this->onPrefabPage()) {
            add_action('admin_enqueue_scripts', [&$this, 'prefab_enqueue_widget_scripts']);
            add_action('admin_enqueue_scripts', [&$this, 'prefab_enqueue_scripts']);
        }

        // Call for register of sidebar
        $this->_register_sidebar();

        if ( ! function_exists('wp_list_widgets') && is_admin()) {
            require_once ABSPATH . '/wp-admin/includes/widgets.php';
        }

        // Register custom settings filters for widgets
        add_filter('in_widget_form', [&$this, 'widget_settings_callback'], 10, 3);
        add_filter('widget_update_callback', [&$this, 'widget_update_callback'], 10, 3);
    }

    public function reset_widget_sidebars(): void
    {
        global $_wp_sidebars_widgets;

        $_wp_sidebars_widgets = [];
        wp_get_sidebars_widgets();
    }

    private function initialize_widgets_by_site_id($site_id): void
    {
        if (get_current_blog_id() !== $site_id) {
            switch_to_blog($site_id);
            $this->blog_switched = true;
        }

        global $wp_registered_widgets;
        global $_wp_sidebars_widgets;

        $_wp_sidebars_widgets = [];
        $wp_registered_widgets = [];

        wp_get_sidebars_widgets();
        do_action('widgets_init');

        $this->add_widgets_to_initialize_map($site_id);
    }

    private function add_widgets_to_initialize_map($site_id): void
    {
        global $wp_registered_widgets;
        $sidebar_widgets = wp_get_sidebars_widgets();

        if ( ! isset($this->widget_initialize_map[$site_id]) || (empty($this->widget_initialize_map[$site_id]) && ! empty($wp_registered_widgets))) {
            $this->widget_initialize_map[$site_id] = $wp_registered_widgets;
        }

        if ( ! isset($this->sidebar_widget_initialize_map[$site_id]) || (empty($this->sidebar_widget_initialize_map[$site_id]) && ! empty($sidebar_widgets))) {
            $this->sidebar_widget_initialize_map[$site_id] = $sidebar_widgets;
        }
    }

    private function get_widgets_from_initialize_map($site): array
    {
        return $this->widget_initialize_map[$site] ?? [];
    }

    private function get_sidebar_widgets_from_initialize_map($site): array
    {
        return $this->sidebar_widget_initialize_map[$site] ?? [];
    }

    /**
     * This function enqueue all scripts necessary to use native wordpress widgets.
     * It enqueues wordpress scripts in wp-admin/js/widgets and also custom scripts in Everyboard which extends the
     * wordpress scripts.
     *
     * @return void
     */
    public function prefab_enqueue_widget_scripts(): void
    {
        do_action('admin_footer-widgets.php');
        do_action('admin_print_scripts-widgets.php');
        wp_enqueue_script(
            'nativewidgets',
            EVERYBOARD_BASE . 'assets/dist/js/nativewidgets.min.ugly.js',
            ['jquery', 'jquery-ui-sortable', 'jquery-ui-draggable', 'jquery-ui-droppable'],
            EVERYBOARD_VERSION
        );
        $settings = wp_enqueue_code_editor([
            'type' => 'text/html',
            'codemirror' => [
                'indentUnit' => 2,
                'tabSize' => 2,
            ],
        ]);

        if (empty($settings)) {
            $settings = [
                'disabled' => true,
            ];
        }
        wp_add_inline_script('nativewidgets', sprintf('pwCustomHTML.init( %s );', wp_json_encode($settings)), 'after');
    }

    public function prefab_enqueue_scripts(): void
    {
        wp_enqueue_script(
            'widgetprefab',
            EVERYBOARD_BASE . 'assets/dist/js/boardwidgets.min.ugly.js',
            ['ever-admin'],
            EVERYBOARD_VERSION
        );
        wp_localize_script('widgetprefab', 'widget_data', $this->get_widget_data());
        wp_localize_script('widgetprefab', 'widgetTags', get_option('ew_board_wiget_tags'));
        wp_localize_script('widgetprefab', 'translation_boardwidgets', [
            'remove_widget_confirm' => __("Should the widget be removed?", "everyboard"),
            'hide_widget_filters' => __("Hide widget filters", "everyboard"),
            'show_widget_filters' => __("Show widget filters", "everyboard"),
            'filter_by_type' => __("Filter by type", "everyboard"),
            'filter_by_tag' => __("Filter by tag", "everyboard"),
            'filter_by_inactive_widgets' => __("Filter by inactive widgets", "everyboard"),
            'inactive' => __("Inactive", "everyboard"),
            'showing' => __("Showing", "everyboard"),
            'of' => __("of", "everyboard"),
            'widgets' => __("widgets", "everyboard"),
            'clear_all_filters' => __("Clear all filters", "everyboard"),
        ]);
        wp_enqueue_style(
            'widgetprefab-style',
            EVERYBOARD_BASE . 'assets/dist/css/boardsettings.min.css',
            [],
            EVERYBOARD_VERSION
        );
        wp_enqueue_style(
            'ew-widget-extension-style',
            EVERYBOARD_BASE . 'assets/dist/css/boardwidgetextension.min.css',
            [],
            EVERYBOARD_VERSION
        );
    }

    // Add custom fields on every widget, called by filter
    public function widget_settings_callback($widget, $return, $instance): void
    {
        $boards = (array)($this->widget_to_board_map[$widget->id] ?? []);

        ?>
        <hr style="border: 1px solid #ccc"/>
        <div class="ew-custom-widget">
            <p>
                <label for="<?php echo $widget->get_field_id('board_widget_name'); ?>"><?php _e('Custom name:',
                        'everyboard'); ?></label>
                <br>
                <input
                    type="text"
                    name="<?php echo $widget->get_field_name('board_widget_name'); ?>"
                    id="<?php echo $widget->get_field_id('board_widget_name'); ?>"
                    class="board_widget_name"
                    placeholder="<?php _e('Name for EveryBoard...', 'everyboard'); ?>"
                    value="<?php echo $instance['board_widget_name'] ?? ''; ?>"
                />
                <input type="hidden" name="orgin_name" value="<?php echo $widget->name; ?>"/>
            </p>

            <p>
                <label for="<?php echo $widget->get_field_id('board_widget_tags'); ?>"><?php _e('Widget tags:',
                        'everyboard'); ?></label>
                <br>
                <input
                    type="text"
                    name="<?php echo $widget->get_field_name('board_widget_tags'); ?>"
                    id="<?php echo $widget->get_field_id('board_widget_tags'); ?>"
                    class="board_widget_tags"
                    placeholder="<?php _e('Tags for widget...', 'everyboard'); ?>"
                    value="<?php echo $instance['board_widget_tags'] ?? ''; ?>"
                />
            </p>
            <?php if ( ! empty($boards)) : ?>
                <p><?php _e('Active on boards:', 'everyboard'); ?></p>
                <ul>
                    <?php foreach ($boards as $board) : ?>
                        <li>
                            <a href=" <?php echo get_edit_post_link($board['id']); ?>"
                               target="_blank"><?php echo $board['title']; ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else : ?>
                <p><?php _e('Not active on any board.', 'everyboard'); ?></p>
            <?php endif; ?>
        </div>
        <?php
        $return = null;
    }

    // Save custom field settings, called by filter
    public function widget_update_callback($instance, $new_instance, $old_instance)
    {
        $instance['board_widget_name'] = $new_instance['board_widget_name'];
        $instance['board_widget_tags'] = $new_instance['board_widget_tags'];

        $option_key = "ew_board_wiget_tags";
        if (isset($instance['board_widget_tags']) && $instance['board_widget_tags'] !== '') {

            $existing_tags = get_option($option_key);
            if ( ! $existing_tags) {
                $existing_tags = [];
            }

            $tags = explode(',', $instance['board_widget_tags']);
            if ($tags !== false && count($tags) > 0) {
                foreach ($tags as $tag) {
                    if ( ! in_array($tag, $existing_tags, true)) {
                        $existing_tags[] = $tag;
                    }
                }
            }

            update_option($option_key, $existing_tags);
        }

        return $instance;
    }

    /*
     * Function to get all prefab widgets
     * @return widgets, array
     */
    public static function get_widgets(): array
    {
        global $wp_registered_widgets;
        $sidebar_widgets = wp_get_sidebars_widgets();

        $widgets = [];
        if ( ! isset($sidebar_widgets['everyboard_widgets'])) {
            return $widgets;
        }

        $active_widgets = $sidebar_widgets['everyboard_widgets'];
        sort($active_widgets);

        foreach ($active_widgets as $widget_id) {
            if (isset($wp_registered_widgets[$widget_id])) {
                $settings = $wp_registered_widgets[$widget_id]['callback'][0]->get_settings();
                $settings = $settings[$wp_registered_widgets[$widget_id]['params'][0]['number']];

                $widgets[] = [
                    'name' => ! empty($settings['board_widget_name'] ?? '') ? $settings['board_widget_name'] : $wp_registered_widgets[$widget_id]['name'],
                    'tags' => $settings['board_widget_tags'] ?? '',
                    'description' => '',
                    'id' => $widget_id,
                    'orgin_name' => $wp_registered_widgets[$widget_id]['name'],
                ];
            }
        }

        return $widgets;
    }

    /*
     * Function to get all prefab widgets
     * @return widget, array
     */
    public static function get_widget_by_id($widget_id): array
    {
        global $wp_registered_widgets;
        $sidebar_widgets = wp_get_sidebars_widgets();

        if ( ! isset($sidebar_widgets['everyboard_widgets'], $wp_registered_widgets[$widget_id])) {
            return [];
        }

        $settings = $wp_registered_widgets[$widget_id]['callback'][0]->get_settings();
        $settings = $settings[$wp_registered_widgets[$widget_id]['params'][0]['number']];

        return [
            'name' => ! empty($settings['board_widget_name'] ?? '') ? $settings['board_widget_name'] : $wp_registered_widgets[$widget_id]['name'],
            'tags' => $settings['board_widget_tags'] ?? '',
            'description' => '',
            'id' => $widget_id,
            'orgin_name' => $wp_registered_widgets[$widget_id]['name'],
        ];
    }

    public function get_widget_instance($widget_id, $site_id = null): ?array
    {
        global $wp_registered_widgets;
        global $_wp_sidebars_widgets;

        if (is_null($site_id)) {
            $site_id = get_current_blog_id();
        }

        $ret = null;
        $reinit_widgets = false;
        $reinit_sidebar_widgets = false;

        # Try fetch widget content from map
        $widgets = $this->get_widgets_from_initialize_map($site_id);
        $sidebar_widgets = $this->get_sidebar_widgets_from_initialize_map($site_id);

        # Check if empty widget data - set initializing to true
        if (empty($widgets)) {
            $reinit_widgets = true;
        }

        if (empty($sidebar_widgets)) {
            $reinit_sidebar_widgets = true;
        }

        # If either sidebar or widgets array for this side id is empty, we need to initialize it
        if (($reinit_widgets || $reinit_sidebar_widgets)) {
            $this->initialize_widgets_by_site_id($site_id);
        }

        # Get the
        if ($reinit_widgets) {
            $widgets = $wp_registered_widgets;
        }
        if ($reinit_sidebar_widgets) {
            $sidebar_widgets = wp_get_sidebars_widgets();
        }

        $switched = false;
        if (get_current_blog_id() !== $site_id) {
            $switched = true;
            switch_to_blog($site_id);
        }

        if (isset($widgets[$widget_id]) && in_array($widget_id, $sidebar_widgets['everyboard_widgets'], true)) {
            $classname = get_class($widgets[$widget_id]['callback'][0]);
            if ( ! $widgets[$widget_id]['callback'][0]) {
                $ret = null;
            } else {
                $settings = $widgets[$widget_id]['callback'][0]->get_settings();
                $settings = $settings[$widgets[$widget_id]['params'][0]['number']];

                $ret = ['classname' => $classname, 'settings' => $settings];
            }
        }

        if ($switched || $this->blog_switched) {
            restore_current_blog();

            $_wp_sidebars_widgets = [];
            wp_get_sidebars_widgets();
        }

        return $ret;
    }

    // Register our widget sidebar for prefabed widgets
    private function _register_sidebar(): void
    {
        register_sidebar([
            'name' => 'EveryBoard Widgets',
            'id' => 'everyboard_widgets',
            'before_widget' => '',
            'after_widget' => '',
            'before_title' => '',
            'after_title' => '',
        ]);
    }

    public function register_widgetprefab_submenu_page(): void
    {
        add_submenu_page('edit.php?post_type=everyboard', 'Board Widgets', 'Board Widgets', 'manage_options',
            'widget-prefab', [&$this, 'admin_page']);
    }

    public function admin_page(): void
    {
        ?>

        <?php wp_nonce_field('save-sidebar-widgets', '_wpnonce_widgets', false); ?>

        <div class="wrap widgetprefab">
            <div id="icon-themes" class="icon32"><br/></div>
            <h2><?php _e('Board widgets', 'everyboard'); ?></h2>

            <div class="filter-container">
                <div class="filter-header">
                    <input type="text" id="input-filter-active-widgets" value=""
                           placeholder="<?php _e('Filter active widgets..', 'everyboard'); ?>"/>
                    <div class="loader">
                        <div class="sk-fading-circle">
                            <div class="sk-circle1 sk-circle"></div>
                            <div class="sk-circle2 sk-circle"></div>
                            <div class="sk-circle3 sk-circle"></div>
                            <div class="sk-circle4 sk-circle"></div>
                            <div class="sk-circle5 sk-circle"></div>
                            <div class="sk-circle6 sk-circle"></div>
                            <div class="sk-circle7 sk-circle"></div>
                            <div class="sk-circle8 sk-circle"></div>
                            <div class="sk-circle9 sk-circle"></div>
                            <div class="sk-circle10 sk-circle"></div>
                            <div class="sk-circle11 sk-circle"></div>
                            <div class="sk-circle12 sk-circle"></div>
                        </div>
                    </div>
                </div>
                <div class="filter-action">
                    <input type="button" value="<?php _e('Show widget filters', 'everyboard'); ?>"
                           class="filter-btn toggle-filters"/>
                </div>
                <div class="filter-body">
                </div>
            </div>

            <div class="widget-liquid-right">
                <div id="widgets-right">
                    <div id="available-widgets" class="widgets-holder-wrap">
                        <div class="sidebar-name">
                            <h3><?php _e('Available widgets', 'everyboard'); ?></h3>
                        </div>
                        <div class="widget-holder">
                            <input type="text" placeholder="<?php _e('Filter available widgets', 'everyboard'); ?>"
                                   id="filter_widgets"/>
                            <div id="widget-list">
                                <?php wp_list_widgets(); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php
            global $wp_registered_widgets;

            // Replace name for widgets

            foreach ($wp_registered_widgets as &$widget) {
                $widgetObj = $widget["callback"][0];
                $settings = $widgetObj->get_settings();

                if (isset($widget["id"]) && isset($settings[$widget["params"][0]["number"]]["board_widget_name"]) && strlen($settings[$widget["params"][0]["number"]]["board_widget_name"]) > 0) {
                    $widget["tmpname"] = $widget["name"];
                    $widget["name"] = $settings[$widget["params"][0]["number"]]["board_widget_name"];
                }

                if (isset($widget["id"]) && isset($settings[$widget["params"][0]["number"]]["board_widget_tags"])) {
                    $widgets["tags"] = $settings[$widget["params"][0]["number"]]["board_widget_tags"];
                }
            }
            ?>

            <div class="widget-liquid-left">
                <div id="widgets-left">
                    <div class="widgets-holder-wrap">
                        <div id='top_banner_widget' class=''>
                            <?php
                            global $wp_registered_sidebars;
                            $sidebar = "everyboard_widgets";
                            $registered_sidebar = $wp_registered_sidebars["everyboard_widgets"];

                            $wrap_class = 'widgets-holder-wrap';
                            if ( ! empty($registered_sidebar['class'])) {
                                $wrap_class .= ' sidebar-' . $registered_sidebar['class'];
                            }

                            ?>

                            <div class="<?php echo esc_attr($wrap_class); ?> active-widgets">
                                <div class="widget-holder inactive">
                                    <h3 class="section-header"><?php _e('Active widgets', 'everyboard'); ?></h3>
                                    <div class="filter-information"></div>
                                    <br/>
                                    <div id='wp_inactive_widgets' class=''>
                                        <?php wp_list_widget_controls($sidebar); // Show the control forms for each of the widgets in this sidebar
                                        ?>
                                        <div class="clear"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php

            $other_sidebar = $wp_registered_sidebars;
            unset($other_sidebar["everyboard_widgets"]);

            echo '<div style="display: none;">';
            foreach ($other_sidebar as $sidebaren) {
                wp_list_widget_controls($sidebaren["id"]);
            }
            echo '</div>';

            // Reset widget names
            foreach ($wp_registered_widgets as &$widget) {
                if (isset($widget['tmpname'])) {
                    $widget['name'] = $widget['tmpname'];
                }
            }
            ?>
        </div>
        <?php
    }

    private function get_widget_data(): array
    {
        $board_args = [
            'posts_per_page' => -1,
            'post_type' => 'everyboard',
            'post_status' => 'any',
        ];

        $boards = get_posts($board_args);

        $boards_data = [];

        foreach ($boards as $board) {
            $json = get_post_meta($board->ID, 'everyboard_json', true);
            $boards_data[$board->ID]['title'] = $board->post_title;
            $boards_data[$board->ID]['json'] = $json;
        }

        $data = [];

        $widgets = self::get_instance()->get_widgets();

        foreach ($widgets as $widget) {

            $boards = [];

            array_walk($boards_data, static function ($board, $key) use (&$boards, $widget) {
                if (false !== strpos($board['json'], $widget['id'])) {
                    $boards[] = [
                        'id' => $key,
                        'title' => $board['title'],
                    ];
                }
            });

            $data[] = [
                'name' => $widget['name'],
                'desc' => $widget['description'],
                'id' => $widget['id'],
                'type' => $widget['orgin_name'],
                'tags' => isset($widget['tags']) && $widget['tags'] !== '' ? explode(',', $widget['tags']) : [],
                'boards' => $boards,
                'active' => count($boards) > 0,
            ];

            $this->widget_to_board_map[$widget['id']] = (object)$boards;
        }

        return $data;
    }

    private function onPrefabPage(): bool
    {
        global $pagenow;

        if ( ! is_admin()) {
            return false;
        }

        return $pagenow === 'edit.php' && ($_GET['page'] ?? '') === 'widget-prefab';
    }
}

global $widgetprefab;
$widgetprefab = EveryBoard_WidgetPrefab::get_instance();
