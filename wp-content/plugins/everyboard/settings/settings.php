<?php

/*
 * Global settings for boards.
 */

class EveryBoard_Global_Settings
{
    private $framework;
    private $custom_css_classes;
    private $list_widgets;

    /**
     * Settings constructor, adds actions and sets private member.s
     *
     * @param string $file Path of file that called constructor.
     */
    public function __construct(string $file = '')
    {
        global $pagenow;

        $options = get_option('render_framework');
        $this->framework = $options['framework'] ?? '';
        $classes = get_option('board_custom_css_classes', []);
        $this->custom_css_classes = $classes;
        $this->list_widgets = $this->get_list_widget_settings();

        if (is_admin()) {
            add_action('admin_menu', [&$this, 'register_board_settings_submenu_page']);
            add_action('admin_init', [&$this, 'register_board_global_settings']);

            if ($pagenow === 'edit.php' && isset($_GET['page']) && $_GET['page'] === 'boardsettings') {
                add_action('admin_enqueue_scripts', [&$this, 'eb_global_settings_enqueue_scripts']);
            }
        }

        // Ajax Actions
        add_action(
            'wp_ajax_eb_global_settings_ajax_get_css_classes',
            [&$this, 'eb_global_settings_ajax_get_css_classes']
        );
        add_action(
            'wp_ajax_eb_global_settings_ajax_add_css_class',
            [&$this, 'eb_global_settings_ajax_add_css_class']
        );
        add_action(
            'wp_ajax_eb_global_settings_ajax_delete_css_class',
            [&$this, 'eb_global_settings_ajax_delete_css_class']
        );
        add_action(
            'wp_ajax_eb_global_settings_ajax_get_css_classes_import',
            [&$this, 'eb_global_settings_ajax_get_css_classes_import']
        );

        if ($file !== '') {
            register_activation_hook($file, [&$this, 'eb_global_settings_add_defaults']);
        }
    }

    /**
     * Execute action enqueue scripts, includes needed scripts and stylesheets for this page.
     */
    public function eb_global_settings_enqueue_scripts(): void
    {
        wp_enqueue_style(
            'widgetprefab-style',
            EVERYBOARD_BASE . 'assets/dist/css/boardsettings.min.css',
            [],
            EVERYBOARD_VERSION
        );
        wp_enqueue_script(
            'eb_global_settings_js',
            EVERYBOARD_BASE . 'assets/dist/js/boardsettings.min.ugly.js',
            [],
            EVERYBOARD_VERSION
        );
        wp_localize_script('eb_global_settings_js', 'translation_boardsettings', [
            'remove_class_confirm' => __('Are you sure you want to remove this class?', 'everyboard'),
            'remove_class_failure' => __('Could not remove class, please try again or contact an administrator.',
                'everyboard'),
            'class_import_failure' => __('Import failed, please check the format and try again.', 'everyboard'),
            'add_class_failure' => __('There was a problem adding the class, please try again.', 'everyboard'),
            'class_name_not_valid' => __('CSS Class name not valid, please try again. ( Only alphanumeric characters and "-" and "_" are allowed ).',
                "everyboard")
        ]);
    }

    /**
     * Registers the settings for boards.
     */
    public function register_board_global_settings(): void
    {
        register_setting('everyboard-global-settings', 'render_framework');
        register_setting('everyboard-global-settings', 'template_settings');
        register_setting('everyboard-global-settings', 'everylist_active');
        register_setting('everyboard-global-settings', 'oclist_active');
    }

    /**
     * AJAX FUNCTION - Prints the existing custom css classes.
     */
    public function eb_global_settings_ajax_get_css_classes(): void
    {
        print json_encode($this->eb_global_settings_get_css_classes());
        die();
    }

    /**
     * AJAX FUNCTION - Adds a given CSS class to the settings.
     * @return string either "success" or "fail" depending on status.
     */
    public function eb_global_settings_ajax_add_css_class(): string
    {
        $post_data = array_replace([
            'css_name' => '',
            'css_class' => '',
            'board_class' => false,
            'row_class' => false,
            'col_class' => false,
            'art_class' => false,
            'wid_class' => false,
            'id' => uniqid('class_', true),
        ], $_POST);

        if (empty($post_data['css_name']) || empty($post_data['css_class'])) {
            print 'false';
            die();
        }

        $unique_id = $post_data['id'];

        $insert_array = $this->eb_global_settings_get_css_classes();

        if (in_array($unique_id, $insert_array, true)) {
            print 'false';
            die();
        }

        $insert_array[] = [
            'name' => $post_data['css_name'],
            'class' => $post_data['css_class'],
            'board' => $post_data['board_class'],
            'row' => $post_data['row_class'],
            'column' => $post_data['col_class'],
            'article' => $post_data['art_class'],
            'widget' => $post_data['wid_class'],
            'id' => $unique_id
        ];

        if ( ! update_option('board_custom_css_classes', $insert_array)) {
            print 'false';
            die();
        }

        $this->custom_css_classes = $insert_array;
        print $unique_id;
        die();
    }

    public function eb_global_settings_ajax_delete_css_class(): void
    {
        $classId = $_POST['classId'] ?? '';

        if (empty($_POST['classId'])) {
            print 'false';
            die();
        }

        $stored_classes = $this->eb_global_settings_get_css_classes();

        $stored_classes = array_filter($stored_classes, static function ($class) use ($classId) {
            return $class['id'] !== $classId;
        });

        if ( ! update_option('board_custom_css_classes', array_values($stored_classes))) {
            print 'false';
            die();
        }

        $this->custom_css_classes = $stored_classes;
        print 'success';
        die();
    }

    /**
     * Returns existing CSS classes.
     * @return array List of current classes name of class is key and the actual class is the value.
     */
    public function eb_global_settings_get_css_classes(): array
    {
        return $this->custom_css_classes;
    }

    /**
     * Registers menu item for settings page, a submenu page of "Boards".
     */
    public function register_board_settings_submenu_page(): void
    {
        add_submenu_page(
            'edit.php?post_type=everyboard',
            'Board Settings',
            __('Board Settings', 'everyboard'),
            'manage_options',
            'boardsettings',
            [&$this, 'admin_page']
        );
    }

    /**
     * Adds default values for board settings, runs on activation of plugin.
     */
    public function eb_global_settings_add_defaults(): void
    {
        $eb_framework_helper = new EveryBoard_Framework_Helper();
        update_option('render_framework', ['framework' => $eb_framework_helper->eb_default_framework]);
    }

    /**
     * Get a list of current CSS classes formatted for export / import.
     */
    public function eb_global_settings_ajax_get_css_classes_export(): void
    {
        $classes = $this->eb_global_settings_get_css_classes();
        $ret_str = '';

        foreach ($classes as $key => $value) {
            $ret_str .= $key . '|' . $value . "\n";
        }

        print $ret_str;

        die();
    }

    /**
     * Imports a list of CSS classes.
     */
    public function eb_global_settings_ajax_get_css_classes_import(): void
    {
        $importData = wp_unslash($_POST['importData'] ?? '');

        if (empty($importData)) {
            print __('false', 'everyboard');
            die();
        }

        try {
            $import_data = json_decode($importData, true);
            $insert_array = $this->eb_global_settings_get_css_classes();

            $existing_classes = array_column($insert_array, 'id');

            if (is_array($import_data)) {
                foreach ($import_data as $item) {
                    $id = $item['id'] ?? uniqid('class_', true);
                    $name = $item['name'] ?? '';
                    $class = $item['class'] ?? '';

                    if (in_array($id, $existing_classes, true)) {
                        continue;
                    }

                    if (empty($class) || empty($name)) {
                        continue;
                    }

                    $insert_array[] = [
                        'name' => $name,
                        'class' => $class,
                        'board' => $item['board'] ?? false,
                        'row' => $item['row'] ?? false,
                        'column' => $item['column'] ?? false,
                        'article' => $item['article'] ?? false,
                        'widget' => $item['widget'] ?? false,
                        'id' => $id
                    ];
                }
            }
        } catch (Exception $e) {
            print __('false', 'everyboard');
            die();
        }

        if ( ! update_option('board_custom_css_classes', $insert_array)) {
            print __('false', 'everyboard');
            die();
        }

        print __('success', 'everyboard');
        die();
    }

    /**
     * Renders the actual options page for board settings.
     */
    public function admin_page(): void
    {
        $eb_framework_helper = new EveryBoard_Framework_Helper();
        $frameworks = $eb_framework_helper->get_frameworks();
        $active_framework = esc_attr($this->framework);
        $templates = EveryBoard_Settings::get_all_templates() ?: [];
        $saved_templates = EveryBoard_Settings::get_templates();

        # Start HTML
        ?>
        <div class="wrap">
            <h2><?php _e('EveryBoard Settings', 'everyboard'); ?></h2>
            <?php settings_errors(); ?>
            <form method="post" action="options.php">
                <?php settings_fields('everyboard-global-settings'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><h3><?php _e('Board Rendering Framework', 'everyboard'); ?></h3></th>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="board_settings_framework_selector"><?php _e('Rendering Framework:',
                                    'everyboard'); ?></label>
                            <p class="description"><?php _e('Choose the framework to use when rendering the board data. Normally this should be the same framework that your theme is built on (if any).',
                                    'everyboard'); ?></p>
                        </th>
                        <td>
                            <select id="board_settings_framework_selector" name="render_framework[framework]">
                                <?php foreach ($frameworks as $slug => $framework) { ?>
                                    <option value="<?php echo $slug; ?>" <?php selected($slug,
                                        $active_framework); ?>><?php echo $framework; ?></option>
                                <?php } ?>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><h3><?php _e('List widgets', 'everyboard'); ?></h3></th>
                    </tr>

                    <?php foreach ($this->list_widgets as $key => $widget) { ?>
                        <?php $input_id = "board_settings_active_list_{$key}"; ?>
                        <tr>
                            <th scope="row">
                                <label for="<?php echo $input_id; ?>"><?php echo $widget['name']; ?></label>
                                <p class="description"><?php echo $widget['description']; ?></p>
                            </th>
                            <th><?php _e('Active', 'everyboard') ?>:<br/><input id="<?php echo $input_id; ?>"
                                                                                type="checkbox" style="margin: 10px;"
                                                                                value="true"
                                                                                name="<?php echo $key; ?>_active" <?php checked($widget['active']); ?>/>
                            </th>
                        </tr>
                    <?php } ?>

                    <tr>
                        <th scope="row"><h3><?php _e('Board Custom CSS Classes', 'everyboard'); ?></h3></th>
                    </tr>

                    <tr class="table_row_extra_bottom_space">
                        <th scope="row">
                            <label
                                for="board_settings_css_classes_add_name_input"><?php _e('Add board custom CSS class:',
                                    'everyboard'); ?></label>
                            <p class="description"><?php _e('Add a new CSS class that will be available when editing an board.',
                                    'everyboard'); ?></p>
                        </th>

                        <td>
                            <label for="board_settings_css_classes_add_name_input"><?php _e('Class Name',
                                    'everyboard'); ?>
                                <span class="description">(<?php _e('the exact name of the actual css class',
                                        'everyboard'); ?>):</span></label><br/>
                            <input type="text" name="" value="" id="board_settings_css_classes_add_name_input"/><br/>

                            <label for="board_settings_css_classes_add_class_input"><?php _e('Class Description',
                                    'everyboard'); ?>
                                <span class="description">(<?php _e('a description of the class for the end user',
                                        'everyboard'); ?>):</span></label><br/>
                            <input type="text" name="" value="" id="board_settings_css_classes_add_class_input"/><br/>

                            <div id="class-type-container">
                                <?php _e('Accessible to items of type:', 'everyboard'); ?>
                                <table class="class-type-table board-settings-table">
                                    <tr>
                                        <td><label for="board_settings_css_board_class"><?php _e('Board:',
                                                    'everyboard'); ?></label></td>
                                        <td><input id="board_settings_css_board_class" type="checkbox"
                                                   name="board-class"/></td>
                                    </tr>
                                    <tr>
                                        <td><label for="board_settings_css_row_class"><?php _e('Row:',
                                                    'everyboard'); ?></label></td>
                                        <td><input id="board_settings_css_row_class" type="checkbox" name="row-class"/>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><label for="board_settings_css_col_class"><?php _e('Column:',
                                                    'everyboard'); ?></label></td>
                                        <td><input id="board_settings_css_col_class" type="checkbox" name="col-class"/>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><label for="board_settings_css_art_class"><?php _e('Article:',
                                                    'everyboard'); ?></label></td>
                                        <td><input id="board_settings_css_art_class" type="checkbox" name="art-class"/>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><label for="board_settings_css_wid_class"><?php _e('Widget:',
                                                    'everyboard'); ?></label></td>
                                        <td><input id="board_settings_css_wid_class" type="checkbox" name="wid-class"/>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <a href="#" class="button"
                               id="board_settings_css_classes_add_button"><?php _e('Add class',
                                    'everyboard'); ?></a>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label
                                for="board_settings_css_classes_list"><?php _e('Current board custom CSS classes:',
                                    'everyboard'); ?></label>
                            <p class="description"><?php _e('The CSS classes that are available to users when editing a board.',
                                    'everyboard'); ?></p>
                        </th>
                        <td>
                            <table id="board_settings_css_classes_table" class="board-settings-table">
                                <tr>
                                    <th scope="col"><?php _e('Class name', 'everyboard'); ?></th>
                                    <th scope="col"><?php _e('Description', 'everyboard'); ?></th>
                                    <th scope="col"><?php _e('For types', 'everyboard'); ?></th>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="board_settings_css_classes_export_area">
                                <?php _e('Export / Import board custom CSS classes:', 'everyboard'); ?>
                            </label>
                            <p class="description"><?php _e('Paste CSS classes from another site or copy the existing ones to export to another site.',
                                    'everyboard'); ?></p>
                        </th>
                        <td>
                            <textarea class="board_settings_export_area" id="board_settings_css_classes_export_area"
                                      rows="10" cols="100"></textarea>
                            <p>
                                <a href="#" class="button"
                                   id="board_settings_css_classes_import_button"><?php _e('Import CSS Classes',
                                        'everyboard'); ?></a>
                                <a href="#" class="button"
                                   id="board_settings_css_classes_export_button"><?php _e('Export CSS Classes',
                                        'everyboard'); ?></a>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="board_settings_framework_selector"><?php _e('Templates:',
                                    'everyboard'); ?></label>
                            <p class="description"><?php _e('Tick the teaser layout templates you want to register for use in boards and widgets.',
                                    'everyboard'); ?></p>
                        </th>
                        <td>
                            <table class="class-type-table board-settings-table">
                                <?php foreach ($templates as $template => $temp) { ?>
                                    <?php $filename = urlencode(pathinfo($temp, PATHINFO_FILENAME)) ?>
                                    <tr>
                                        <th>
                                            <label for="<?php echo "board_settings_template_{$filename}"; ?>"
                                                   class="check-boxes"><?php echo $template ?></label>
                                        </th>
                                        <?php if ($temp === 'ocarticle-default.php') { ?>
                                            <td>
                                                <strong>Default Template</strong>
                                                <input type="hidden" value="<?php echo $temp ?>"
                                                       name="template_settings[<?php echo $template; ?>]">
                                            </td>
                                        <?php } else { ?>
                                            <td>
                                                <input id="<?php echo "board_settings_template_{$filename}"; ?>"
                                                       type="checkbox" <?php checked(in_array($temp, $saved_templates,
                                                    true)) ?> value="<?php echo $temp; ?>"
                                                       name="template_settings[<?php echo $template; ?>]">
                                            </td>
                                        <?php } ?>
                                    </tr>
                                <?php } ?>
                            </table>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php # End HTML
    }

    public function get_list_widget_settings(): array
    {
        $list_types = [];

        // Only show Everylist option to users that already have it active
        if (static::is_everylist_active()) {
            $list_types['everylist'] = [
                'name' => 'EveryList',
                'description' => __('Internal lists in Wordpress, lets you structure lists of articles and use them in boards.',
                    'everyboard'),
                'active' => static::is_everylist_active()
            ];
        }

        $list_types['oclist'] = [
            'name' => 'OC List',
            'description' => __('External lists saved in Open Content, requires Open Content 2.1.', 'everyboard'),
            'active' => static::is_oclist_active()
        ];

        return $list_types;
    }

    public static function is_everylist_active(): bool
    {
        return get_option('everylist_active', 'true') === 'true';
    }

    public static function is_oclist_active(): bool
    {
        return get_option('oclist_active', 'true') === 'true';
    }
}
