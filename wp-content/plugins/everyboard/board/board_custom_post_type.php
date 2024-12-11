<?php
global $post;

use Everyware\Everyboard\Exceptions\BoardNotFoundException;
use Everyware\Everyboard\Exceptions\BoardInTrashException;
use Everyware\Everyboard\Exceptions\NotSelectedException;
use Everyware\Everyboard\OcArticleProvider;
use Everyware\Everyboard\OcListAdapter;
use Everyware\Everyboard\OcValidator;

/**
 * Class EveryBoard_CustomPostType
 */
class EveryBoard_CustomPostType
{
    /**
     * Contains the custom field where the draft board json is stored
     * @var string
     */
    public const TEMP_BOARD_JSON_FIELD = 'everyboard_json_temp';

    /**
     * Contains the custom field where the board json is stored
     * @var string
     */
    public const BOARD_JSON_FIELD = 'everyboard_json';

    /**
     * Contains the custom field where the board "Versions" are stored
     * @var string
     */
    public const BOARD_VERSION_FIELD = 'board_json_version';

    /**
     * Contains the html-images with the action-buttons for rows and columns
     *
     * @var string;
     */
    protected $default_structure_icons;

    /**
     * Contains the html-images with the action-buttons for articles and widgets
     *
     * @var string
     */
    protected $default_structure_content_icons;

    /**
     * Contains an array of settings for rows and columns
     *
     * @var array
     */
    protected $default_structure_settings;

    /**
     * Contains an array of settings for the board
     *
     * @var array
     */
    protected $default_board_settings = [
        'type' => 'board',
        'cssclass' => '',
        'description' => ''
    ];

    /**
     * @return array
     */
    public function getDefaultStructureSettings()
    {
        return $this->default_structure_settings;
    }

    /**
     * Contains the path for Everyboards assets
     *
     * @var string;
     */
    protected $assets_url;

    /**
     * Contains an instance of the framework-helper used by EveryBoard
     *
     * @var EveryBoard_Framework_Helper
     */
    protected $eb_framework_helper;

    /**
     * Contains an instance of the OcAPI-class
     *
     * @var OcAPI
     */
    protected $oc_api;

    /**
     * Contains the max number of columns fetched from the framework-helper
     *
     * @var int
     */
    protected $max_cols;

    /**
     * Contains the currently used post
     *
     * @var WP_Post
     */
    protected $post;

    /**
     * Contains an html img-tag with remove
     *
     * @var string
     */
    protected $remove_icon;

    /**
     * Contains an instance of environment-settings used by EveryBoard
     *
     * @var EnvSettings
     */
    protected $env_settings;

    /**
     * Internal cache for network available boards.
     *
     * @var null
     */
    protected $published_network_boards;

    /**
     * Which content types that can contain empty positions (to be filled with data separately).
     */
    private const WIDGET_TYPES_WITH_POSITIONS = [
        'contentcontainer_widget'
    ];

    /**
     * EveryBoard_CustomPostType constructor
     *
     * Initialize EveryBoard_CustomPostType
     */
    public function __construct()
    {
        // WP bug fix for roles
        global $pagenow;

        add_action('init', function () {
            $this->oc_api = new OcAPI();
            $this->settings = new EveryBoard_Settings();
            if (isset($_GET["post"])) {
                $this->post = get_post($_GET["post"]);
            }
        });

        $this->assets_url = plugin_dir_url(__DIR__) . 'assets/';

        $this->eb_framework_helper = new EveryBoard_Framework_Helper();
        $this->max_cols = $this->eb_framework_helper->get_max_cols();

        $this->env_settings = new EnvSettings();

        // Enqueue style/scripts
        if (is_admin() && ($pagenow == "post.php" && $pagenow !== "post-new.php") && ((isset($_GET["post"]) && get_post_type($_GET["post"]) == "everyboard") || (isset($_GET["post_type"]) && $_GET["post_type"] == "everyboard"))) {
            add_action('admin_enqueue_scripts', [
                &$this,
                'custom_board_enqueue'
            ]);
            add_action('admin_init', [
                &$this,
                'disable_autosave'
            ]);
        }

        add_action('init', [
            &$this,
            'cpt_board_register_post_type'
        ]); // Registers custom post type.
        if ($pagenow !== 'post-new.php') {
            add_action('add_meta_boxes', [
                &$this,
                'cpt_board_add_metaboxes'
            ]); // Adds custom meta boxes to edit page.
        }
        add_action('add_meta_boxes', [
            &$this,
            'register_meta_sidebar'
        ]);
        add_filter('post_row_actions', [
            &$this,
            'cpt_board_action_row'
        ], 10, 2); // Adds custom actions to post rows in admin interface.
        add_action('admin_action_copy_board', [
            &$this,
            'cpt_board_duplicate_action'
        ]); // Runs on our custom action, duplicates a board.
        add_action('admin_action_export_board', [
            &$this,
            'cpt_board_export_action'
        ]); // Runs on our custom action, exports a board.
        add_action('save_post', [
            &$this,
            'import_board_json'
        ]); // Upload JSON to import when post is saved
        add_action('post_edit_form_tag', [
            &$this,
            'update_edit_form'
        ]); // Allow the form to handle files

        $this->cpt_add_ajax_actions();

        $this->isadmin = false;

        // Setup default-settings for Rows and Columns

        $this->remove_icon = '<img src="' . $this->assets_url . 'img/icons/close_alt.png" class="remove_item" />';
        $settings_icon = '<img src="' . $this->assets_url . 'img/icons/wheel_alt.png" class="settings_item" />';
        $min_icon = '<img src="' . $this->assets_url . 'img/icons/min.png" rel="' . $this->assets_url . 'img/icons/max.png" class="toggle_item" />';

        $this->default_structure_icons = "{$settings_icon} {$min_icon} {$this->remove_icon}";
        $this->default_structure_content_icons = "{$settings_icon} {$this->remove_icon}";

        $this->default_structure_settings = [
            'name' => __('Row', 'everyboard'),
            'type' => 'row',
            'cssclass' => '',
            'devices' => [
                'mobile' => [
                    'hidden' => 'false',
                    'removed' => 'false',
                    'colspan' => '12',

                ],
                'tablet' => [
                    'hidden' => 'false',
                    'removed' => 'false',
                    'colspan' => '12'

                ],
                'smallscreen' => [
                    'hidden' => 'false',
                    'removed' => 'false',
                    'colspan' => '12'


                ],
                'largescreen' => [
                    'hidden' => 'false',
                    'removed' => 'false',
                    'colspan' => '12'
                ],
            ]
        ];
    }

    /**
     * Api that's called with ajax from the js-files
     */
    public function everyboard_api(): void
    {
        $request = [
            'method' => $_SERVER['REQUEST_METHOD'],
            'data' => $_REQUEST['data'],
            'route' => $_REQUEST['route']
        ];

        $this->everyboard_router($request);
        die();
    }

    /**
     * Handles the routing of the everyboard ajax-api
     *
     * @param array $request
     *
     * @return array
     */
    private function everyboard_router(array $request): array
    {
        switch ($request['route']) {
            case 'row/save':
                $this->cpt_board_save_row();
                break;
            case 'row/remove':
                $this->cpt_remove_saved_row();
                break;
            case 'search':
                $this->cpt_oc_text_search();
                break;
            case 'suggest':
                $this->cpt_oc_get_suggest();
                break;
            case 'search/save':
                $this->cpt_oc_save_search();
                break;
            case 'search/remove':
                $this->cpt_oc_remove_search();
                break;
            case 'versions/restore':
                $this->cpt_restore_version();
                break;
            case 'versions':
                $this->cpt_get_board_versions();
                break;
        }

        return $request;
    }

    /**
     * Function to make a connection to a page from board view
     *
     * @return void indicating success
     */
    public function cpt_ajax_make_connection(): void
    {
        $board_id = $this->cpt_get_posted_id_by_key('board_id');
        $page_id = $this->cpt_get_posted_id_by_key('page_id');

        if (is_int($board_id) && is_int($page_id)) {
            //Print result to JavaScript
            print $this->cpt_update_board_data($page_id, 'everyboard_id', $board_id);
        } else {
            print 0;
        }

        exit(1);
    }

    /**
     * Function to enqueue styles and scripts
     *
     * @return void
     */
    public function custom_board_enqueue(): void
    {
        $this->cpt_add_styles();
        $this->cpt_add_scripts();
        $this->cpt_add_localized_script();
    }

    public function update_edit_form(): void
    {
        echo ' enctype="multipart/form-data"';
    }

    /**
     * Function to disable wordpress autosave functionality
     */
    public function disable_autosave(): void
    {
        wp_deregister_script('autosave');
    }

    /**
     * Remove saving board json when saves post.
     */
    public function cpt_save_post(): void
    {
        wp_update_post([
            'ID' => $_POST["post_id"],
            'post_title' => $_POST["post_title"],
            'post_status' => "publish"
        ]);

        if (isset($_POST["post_tags"]) && $_POST["post_tags"] !== "") {
            wp_set_post_tags($_POST["post_id"], $_POST["post_tags"]);
        }

        if (isset($_POST['is_shared'])) {
            update_post_meta($_POST["post_id"], 'board_is_shared', $_POST['is_shared']);
        }

        $this->save_board_json_version($_POST["post_id"]);

        die(1);
    }

    /**
     * Save board version.
     *
     * @param int $postId
     */
    public function save_board_json_version(int $postId): void
    {
        $board_versions = get_post_meta($postId, self::BOARD_VERSION_FIELD, true);
        $board_json = get_post_meta($postId, self::BOARD_JSON_FIELD, true);

        while (is_string($board_versions)) {
            $board_versions = unserialize($board_versions, ['allowed_classes' => true]);
        }

        // Check if unserialize-function returns something other then array
        if ( ! is_array($board_versions)) {
            $board_versions = [];
        }

        $board_version_count = count($board_versions);
        $board_version_index = $board_version_count > 1 ? $board_version_count - 1 : 0;

        // Decode the json data to objects.
        $temp_new_json = json_decode($board_json);
        $latest_json_version = '';
        if (isset($board_versions[$board_version_index]['json'])) {
            $latest_json_version = json_decode($board_versions[$board_version_index]['json']);
        }

        // Remove date from the JSON objects to compare them.
        unset($temp_new_json->date, $latest_json_version->date);

        // Encode objects to string and compare them.
        $temp_new_json = json_encode($temp_new_json);
        $latest_json_version = json_encode($latest_json_version);
        if ($temp_new_json === $latest_json_version) {

            // No changes have been made, update the json with new date, do not create new version item.
            $board_versions[$board_version_index]['json'] = $board_json;
        } else {

            // Changes have been made, save the board json as a new version.
            if ($board_version_count >= 5) {
                array_shift($board_versions);
            }

            $board_json_item = [];
            $board_json_item['id'] = uniqid('version_');
            $board_json_item['json'] = $board_json;

            $board_versions[] = $board_json_item;
        }

        // Recount versions if something changed
        $board_version_count = count($board_versions);

        for ($i = 0; $i < $board_version_count; $i++) {
            $board_versions[$i]['json'] = addslashes($board_versions[$i]['json']);
        }

        update_post_meta($postId, self::BOARD_VERSION_FIELD, $board_versions);
    }

    /**
     * Restores an earlier version of the board.
     */
    public function cpt_restore_version(): void
    {
        if (isset($_POST['data']['boardId'], $_POST['data']['versionId'])) {

            $board_id = $_POST['data']['boardId'];
            $version_id = $_POST['data']['versionId'];
            $board_versions = get_post_meta($board_id, self::BOARD_VERSION_FIELD, true);

            foreach ($board_versions as $version) {

                if ($version_id === $version['id']) {
                    $board_json = addslashes($version['json']);
                    $this->cpt_update_board_data($board_id, self::TEMP_BOARD_JSON_FIELD, $board_json);
                    $this->cpt_update_board_data($board_id, self::BOARD_JSON_FIELD, $board_json);
                    print __('success', 'everyboard');
                    break;
                }
            }
        }
    }

    /**
     * AJAX Function that gets a given boards versions.
     */
    public function cpt_get_board_versions(): void
    {
        $board_id = $_GET['data']['boardId'] ?? null;

        if (isset($board_id)) {

            $stored_versions = (array)get_post_custom_values(self::BOARD_VERSION_FIELD, $board_id);
            $board_versions = $stored_versions[0] ?? [];

            if (empty($board_versions)) {
                wp_die();
            }

            while (is_string($board_versions)) {
                $board_versions = unserialize($board_versions, ['allowed_classes' => true]);
            }

            $board_versions = array_reverse($board_versions);

            print json_encode($board_versions);
        }
    }

    /**
     * Heartbeat received, check if temp json has been changed somewhere else.
     *
     * @param array $response
     * @param array $data
     *
     * @return array
     */
    public function cpt_heartbeat_received(array $response, array $data): array
    {
        $action = $data['cpt_action'] ?? '';

        if ($action !== 'cpt_check_temp_json' || ! isset($data['cpt_board_date'], $data['cpt_board_id'])) {
            return $response;
        }

        $receiveTime = (int)$data['cpt_board_date'];
        $boardJson = (string)get_post_meta($data['cpt_board_id'], self::TEMP_BOARD_JSON_FIELD, true);

        $boardData = ! empty($boardJson) ? json_decode($boardJson, true) : [];

        // If board has not been saved yet
        if ( ! isset($boardData['date'])) {
            return $response;
        }

        $boardLastModified = (int)$boardData['date'];

        $response['db_date'] = $boardLastModified;
        $response['req_date'] = $receiveTime;
        $response['board_temp_json'] = $boardLastModified > $receiveTime ? 'expired' : 'valid';

        return $response;
    }

    /**
     * Registers all meta-boxes in the meta sidebar
     *
     * @return void
     */
    public function register_meta_sidebar(): void
    {
        global $pagenow;

        if ($pagenow !== "post-new.php") {
            remove_meta_box('submitdiv', 'EveryBoard', 'side');

            add_meta_box('board_sidebar', __('Board Settings', 'everyboard'), [
                &$this,
                'meta_board_sidebar'
            ], 'EveryBoard', 'side', 'core');
        }
    }

    public function meta_board_sidebar(): void
    {
        $this->cpt_render_everyboard_side_area();
    }

    /**
     * Render the "Save board" box in meta sidebar
     *
     * @return void
     */
    public function meta_saveboard_sidebar(): void
    {
        $this->cpt_render_saveboard_box();
    }

    /**
     * TODO: Checkout if this is really being used correctly
     */
    public function meta_import_sidebar(): void
    {
        wp_nonce_field(plugin_basename(__FILE__), 'board_import_nonce');
        # Start HTML
        ?>

        <p class="description"><?php echo __('Upload JSON to import. <strong>Will override current board layout</strong>',
                'everyboard'); ?></p>
        <input type="file" name="import_json" id="import_json"/>
        <input type="submit" class="button pull-right" name="submit"
               value="<?php echo __('Upload', 'everyboard') ?>"/>

        <?php # End HTML
    }

    /**
     *
     * TODO: Checkout if this is really being used correctly
     * TODO: Validate JSON data
     *
     * @param $id
     */
    public function import_board_json($id): void
    {
        if ( ! empty($_FILES['import_json']['name'])) {
            $json = file_get_contents($_FILES['import_json']['tmp_name']);
            $this->cpt_update_board_data($id, self::BOARD_JSON_FIELD, $json);
            $this->cpt_update_board_data($id, self::TEMP_BOARD_JSON_FIELD, $json);
        }
    }

    /**
     * Function to add Make connection meta box
     * TODO: Check for optimization
     */
    public function meta_connections_sidebar(): void
    {
        $pages = get_pages([
            'sort_order' => 'ASC',
            'sort_column' => 'post_title',
            'hierarchical' => 1,
            'exclude' => '',
            'include' => '',
            'meta_key' => '',
            'meta_value' => '',
            'authors' => '',
            'child_of' => 0,
            'parent' => -1,
            'exclude_tree' => '',
            'number' => '',
            'offset' => 0,
            'post_type' => 'page',
            'post_status' => 'publish'
        ]);
        $page_arr = $this->get_board_pages($_GET['post']);

        if (count($page_arr) == 0) {
            print '<p class="no_connections"><em>' . __('No connections made yet', 'everyboard') . '</em></p>';
            print '<ul class="connected-list"></ul>';
        } else {
            print '<p><strong>' . __('This board is active on:', 'everyboard') . '</strong><p>';
            print '<ul class="connected-list">';
            foreach ($page_arr as $page) {
                $url = add_query_arg([
                    'board_preview' => 'true',
                    'preview_id' => $page->ID
                ], get_permalink($page->ID));
                echo '<li><a href="' . $url . '" target="_blank">' . $page->{"post_title"} . '</a></li>';
            }
            print '</ul>';
        }

        /* Make connection now moved here */
        print '<p><strong>' . __('Use this board on page', 'everyboard') . '</strong><p>';

        print '<select id="make_connection_select">';
        foreach ($pages as $page) {

            $active_board_id = $this->cpt_get_board_json('everyboard_id', $page->ID);

            if (get_the_id() !== (int)$active_board_id) {
                print '<option data-page_id="' . $page->ID . '" data-pageurl="' . get_permalink($page->ID) . '" >' . $page->post_title . '</option>';
            }
        }
        print '</select>';

        print '<br>';
        print '<a style="margin-top: 2px;" href="#" id="make_connection_link" class="button">' . __('Add',
                'everyboard') . '</a>';
        /* End make connection */

        # Start HTML
        ?>

        <div class="list-container list-hidden">
            <div class="list-heading list-toggler" title="Click to toggle">
                <h3>
                    <?php echo __('Preview on page', 'everyboard'); ?>
                    <span class="fa fa-chevron-down list-toggler-icon"></span>
                </h3>
            </div>
            <ul class="list-body">
                <?php foreach (get_pages() as $page) {
                    $args = [
                        'board_preview' => 'true',
                        'preview_id' => $page->ID,
                        'board_id' => get_the_ID()
                    ];
                    $url = add_query_arg($args, get_permalink($page->ID));
                    ?>
                    <li class="list-item"><a href="<?= $url ?>" target="_blank"><?= $page->{"post_title"} ?></a></li>
                <?php } ?>
            </ul>
        </div>
        <button type="button" class="button"
                id="reload_preview"><?php echo __('Reload preview', 'everyboard'); ?></button>

        <?php
        # End HTML

    }

    /**
     * Function to setup args for and register EveryBoards as a custom post type.
     */
    public function cpt_board_register_post_type(): void
    {
        if (current_user_can('manage_options')) {
            $this->isadmin = true;
        }

        $board_labels = $this->cpt_get_posttype_labels([
            'single' => 'board',
            'list' => 'boards'
        ]);

        $everyBoard_args = [
            'labels' => $board_labels,
            'description' => __('EveryBoards are templates that you can fill with content and set a page structure. After they are saved they can be applied to pages.',
                'everyboard'),
            'public' => true,
            'exclude_from_search' => true,
            'publicly_queryable' => false,
            'show_in_nav_menus' => false,
            'show_in_menu' => true,
            'menu_position' => 5,
            'menu_icon' => $this->assets_url . 'img/icons/board.png',
            'supports' => [
                'title',
                'custom-fields'
            ],
            'taxonomies' => ['post_tag'],
            'query_var' => false
        ];

        register_post_type('EveryBoard', $everyBoard_args);
    }

    /**
     * Uses Wordpress global function to adds custom meta boxes to board admin-page.
     *
     * @use add_meta_box()
     * @return void
     */
    public function cpt_board_add_metaboxes(): void
    {
        add_meta_box('everyboard_main_meta', 'Board', [
            &$this,
            'cpt_board_main_meta'
        ], 'EveryBoard', 'normal', 'high');
        add_meta_box('everyboard_version_meta', __('Versions', 'everyboard'), [
            &$this,
            'cpt_board_version_meta'
        ], 'EveryBoard', 'side', 'low');
        add_meta_box('everyboard_share_meta', __('Share', 'everyboard'), [
            &$this,
            'cpt_board_share_meta'
        ], 'EveryBoard', 'side', 'low');
    }

    /**
     * Main meta box, renders the board and it's content.
     *
     * @return void
     */
    public function cpt_board_main_meta(): void
    {
        $this->cpt_render_everyboard_main_area();
    }


    /**
     * Versions meta box, renders the box content.
     */
    public function cpt_board_version_meta()
    {

    }

    /**
     * Share meta box, renders box content.
     */
    public function cpt_board_share_meta(): void
    {
        global $post;

        // Get the location data if its already been entered
        $is_shared = get_post_meta($post->ID, 'board_is_shared', true);
        $checked_html = $is_shared === 'true' ? 'checked="checked"' : '';
        $html = '<input type="checkbox" name="board_is_shared" id="board-is-shared-checkbox" value="true" ' . $checked_html . ' />';
        $html .= '<label for="board-is-shared-checkbox">' . __('Make this board available to use by other sites within this network.',
                'everyboard') . '</label>';
        print $html;
    }

    /**
     * Render board from json
     */
    private function cpt_render_board($board_json): void
    {
        if (isset($board_json['date'])) {
            $board_time = $board_json['date'];
        } else {
            $board_time = time() * 1000; // In milliseconds for javascript.
        }

        print '<input type="hidden" name="board_initial_changedate" value="' . $board_time . '" />';

        // Loop through all rows and render them
        if (isset($board_json) && array_key_exists('rows', $board_json)) {
            foreach ($board_json['rows'] as $row) {

                $this->cpt_render_row($row);

            }
        }
    }

    /**
     * AJAX Function that saves board json as a custom field.
     * TODO: Optimize?
     *
     * @return void
     */
    public function cpt_board_save_json(): void
    {
        if (isset($_POST['post_id'], $_POST['board_json']) && is_string($_POST['board_json'])) {
            $post_id = (int)$_POST['post_id'];
            $board_json = $_POST['board_json'];
            $board_json = addslashes(stripslashes($board_json));

            $json_name = $_POST["json_name"] ?? self::BOARD_JSON_FIELD;

            //NOTE: If the meta_value passed to this function is the same as the value that is already in the database, this function returns false.
            if ($this->cpt_update_board_data($post_id, $json_name, $board_json)) {
                print 'success';
            } else {
                print 'error';
            }

            if ($json_name === self::BOARD_JSON_FIELD) {
                $post_id = isset($_GET['post']) ? $_GET['post'] : $post_id;
                $page_arr = $this->get_board_pages($post_id);

                if (count($page_arr) > 0) {
                    foreach ($page_arr as $page) {
                        do_action('board_changed_hook', $page->ID);
                    }
                }
            }
        } else {
            print 'error';
        }

        die();
    }

    /**
     * AJAX Function to do a article search
     *
     * @return void
     */
    public function cpt_oc_save_search(): void
    {
        if (isset($_POST["data"]["name"], $_POST["data"]["oc_query"], $_POST["data"]["sorting"])) {
            $saved_queries = $this->cpt_get_global_queries();

            $saved_queries[] = [
                "name" => $_POST["data"]["name"],
                "query" => $_POST["data"]["oc_query"],
                "sorting" => $_POST["data"]["sorting"]
            ];

            $this->cpt_update_global_queries($saved_queries);

            foreach ($saved_queries as $key => $query) {
                # Start HTML
                ?>
                <li class="list-item">
                    <?php $this->cpt_render_saved_article_query($key, $query); ?>
                </li>
                <?php

            }
        }

        die();
    }

    /**
     * Creates a new Open Content article, for example when it is dropped from external source.
     *
     * @return void
     */
    public function cpt_oc_make_article(): void
    {
        $article_id = $_POST["uuid"];

        $article = $this->cpt_oc_get_article($article_id);

        if ($article !== null) {

            $article_data = $this->cpt_create_default_article_data($article);

            if (isset($_POST['render']) && $_POST['render'] === 'false') {
                $ret = $article->get_all_properties();
                wp_send_json($ret);
            } else {
                echo $this->cpt_render_article($article, $article_data);
            }
        }

        die();
    }

    /**
     * Save a row as template from ajax
     *
     * @return void
     */
    public function cpt_board_save_row(): void
    {
        if (isset($_POST["data"]["row"])) {
            $row = $_POST["data"]["row"];
            $saved_rows = $this->cpt_get_saved_rows();
            $saved_rows[] = $row;
            $this->cpt_update_saved_rows($saved_rows);

            foreach ($saved_rows as $key => $row) :
                $row['row_id'] = $key;

                # Start HTML
                ?>
                <li class="list-item"><?php $this->cpt_render_row($row); ?></li>
            <?php
                # End HTML

            endforeach;
        }

        die();
    }

    /**
     * Remove a saved row
     * TODO: AJAX?
     *
     * @return void
     */
    public function cpt_remove_saved_row(): void
    {
        $saved_rows = $this->cpt_get_saved_rows();

        if ($saved_rows && isset($_POST["data"]["remove_row_id"])) {
            $remove_row_id = $_POST["data"]["remove_row_id"];
            unset($saved_rows[$remove_row_id]);

            $this->cpt_update_saved_rows($saved_rows);

            foreach ($saved_rows as $key => $row) :
                $row['row_id'] = $key;

                # Start HTML
                ?>
                <li class="list-item"><?php $this->cpt_render_row($row); ?></li>
            <?php
                # End HTML

            endforeach;
        }

        die();
    }

    /**
     * Removes a saved query against OC.
     * TODO: Is this being used with AJAX? in that case should it die?
     *
     * @return void
     */
    public function cpt_oc_remove_search(): void
    {
        if (isset($_POST["data"]["id"])) {

            $saved_queries = $this->cpt_get_global_queries();
            unset($saved_queries[$_POST["data"]["id"]]);
            $this->cpt_update_global_queries($saved_queries);
        }
    }

    /**
     * TODO: What does this do? Is it being used with AJAX? Optimize?
     *
     * @return void
     */
    public function cpt_oc_text_search()
    {
        if (isset($_POST['data']['oc_query']) && $_POST['data']['oc_query'] !== "") {

            $query = stripcslashes(trim($_POST['data']['oc_query']));
            $query = trim(str_replace('AND contenttype:Article', '', $query));
            if ($query !== "*:*") {
                $query = str_replace(':', '\\:', $query);
                $query = str_replace('?', '\\?', $query);
            }

            $query .= " AND contenttype:Article";
            $section = isset($_POST['data']['section']) ? stripcslashes(trim($_POST['data']['section'])) : '';
            $date_query = $query;
            $sort = $_POST['data']['sorting'] ?? null;
            $start = $_POST['data']['start'] ?? 0;
            $pubdate_start = $_POST['data']['pubdate_start'] ?? null;
            $pubdate_stop = $_POST['data']['pubdate_stop'] ?? null;
            $limit = 10;

            if (isset($pubdate_start, $pubdate_stop) && $pubdate_start !== "" && $pubdate_stop !== "") {

                $date_start = $this->time_to_oc_date(strtotime($pubdate_start . ' UTC'));
                $date_stop = $this->time_to_oc_date(strtotime($pubdate_stop . ' UTC'));

                $date_query .= " AND Pubdate:[" . $date_start . " TO " . $date_stop . "]";
            }

            if (isset($section) && ! empty($section)) {
                $date_query .= ' AND Section:"' . $section . '"';
            }

            $result = $this->oc_api->text_search($date_query, [], $sort, null, null, $start, $limit);
            $hits = $result['hits'] ?? 0;
            $query = trim(str_replace('AND contenttype:Article', '', $query));
            $query = trim(str_replace('\\', '', $query));

            $number_of_pages = (int)ceil($hits / $limit); // Get number of pages needed to display all articles in result.
            $active_page = $hits === 0 ? 1 : ($start / $limit) + 1; // Get the active page.

            $response_html = '<h3 class="search_result_header">' . __('Result for:',
                    'everyboard') . ' ' . $query . '</h3>';
            $response_html .= '<p class="hits-stats">' . __('Total hits:',
                    'everyboard') . ' <strong>' . number_format($hits, 0, '.', '&thinsp;') . '</strong></p>';

            if ($number_of_pages > 0) {
                $response_html .= '<p class="hits-stats">' . __('Page:',
                        'everyboard') . ' <strong>' . $active_page . ' ' . __('of',
                        'everyboard') . ' ' . number_format($number_of_pages, 0, '.', '&thinsp;') . '</strong></p>';
            }

            $response_html .= '<a href="#" class="save_search button button-small">' . __('Save Search',
                    'everyboard') . '</a>';

            foreach ($result as $article) {
                if (is_object($article) && get_class($article) === 'OcArticle') {

                    $article_data = $this->cpt_create_default_article_data($article);
                    $response_html .= $this->cpt_render_article($article, $article_data);
                }
            }

            if ($number_of_pages > 0) {

                $response_html .= '<ul class="pagination">';

                if ($active_page > 1) { // If active page is larger than 1 show previous link.
                    $response_html .= '<li class="prev"><a href="#" data-start="' . ($start - $limit) . '"><</a></li>';
                }

                for ($i = 2; $i > 0; $i--) { // Print up to 2 pages before the active page.
                    if ($active_page - $i > 0) {
                        $response_html .= '<li><a href="#" data-start="' . ($limit * ($active_page - ($i + 1))) . '">' . ($active_page - $i) . '</a></li>';
                    }
                }

                // Print the active page.
                $response_html .= '<li class="active"><a href="#" data-start="' . ($limit * ($active_page - 1)) . '">' . $active_page . '</a></li>';

                for ($i = $active_page + 1; $i < $active_page + 3; $i++) { // Print up to 2 pages after the active page.
                    if ($i <= $number_of_pages) {
                        $response_html .= '<li><a href="#" data-start="' . ($limit * ($i - 1)) . '">' . ($i) . '</a></li>';
                    }
                }

                if ($active_page < $number_of_pages) { // Print next page link.
                    $response_html .= '<li class="next"><a href="#" data-start="' . ($start + $limit) . '">></a></li>';
                }

                $response_html .= '</ul>';
            }

            print $response_html;
        } else {
            print '<h2>' . __('No Hits:',
                    'everyboard') . '</h2><div class="structure_item" ><p>' . __('Empty or invalid query',
                    'everyboard') . '</p></div>';
        }

        die();
    }

    public function time_to_oc_date($timestamp)
    {
        return date("Y-m-d\TH:i:s\Z", $timestamp);
    }

    public function cpt_oc_get_suggest()
    {
        header('Content-type: application/json');
        $field = $_GET['data']['field'] ?? null;
        $q = $_GET['q'] ?? null;
        $incompleteWord = $_GET['incompleteWord'] ?? null;
        $incompleteWordInText = $_GET['incompleteWordInText'] ?? null;

        $result = $this->oc_api->ajax_get_suggest($field, $q, $incompleteWord, $incompleteWordInText);
        echo $result;
    }

    /**
     * Fetch article with id from OC
     *
     * @use EveryBoard_Article_Helper::get_oc_article()
     *
     * @param $uuid
     *
     * @return OcArticle|null
     */
    private function cpt_oc_get_article($uuid): ?OcArticle
    {
        return EveryBoard_Article_Helper::get_oc_article($uuid);
    }

    public function cpt_get_meta_content()
    {
        if ( ! EveryBoard_Global_Settings::is_everylist_active()) {
            return 'EveryList is not active.';
        }

        $list_id = isset($_POST['listId']) ? $_POST['listId'] : null;
        $list_pos = isset($_POST['listPosition']) ? $_POST['listPosition'] : null;
        $listlimit = isset($_POST['listLimit']) ? $_POST['listLimit'] : null;
        $site_id = isset($_POST['siteId']) ? $_POST['siteId'] : null;

        $current_site_id = get_current_blog_id();
        $switched = false;

        if ( ! is_null($list_id) && ! is_null($list_pos)) {
            if ( ! is_null($site_id) && $site_id !== $current_site_id) {
                switch_to_blog($site_id);
                $switched = true;
            }

            if ($listlimit > 1) {
                $list_articles = EveryList::get_articles_from_list($list_id, $list_pos, $listlimit);

                if (isset($list_articles)) {
                    $content = '<div class="meta_info">';
                    foreach ($list_articles as $article) {
                        if ( ! is_null($article)) {
                            $headline = isset($article->headline[0]) ? $article->headline[0] : $article->uuid[0];
                            $content .= '<p><strong>' . $headline . '</strong></p>';
                        }
                    }
                    $content .= "</div>";
                    print $content;
                } else {
                    $listname = EveryList::get_list_name_by_id($list_pos);
                    print "<p>Can't find articles with starting position " . $list_pos . " in " . $listname . "</p>";
                }
            } else {
                $article_info = EveryList::get_article_from_list($list_id, $list_pos);

                if ( ! is_null($article_info)) {
                    $article = $this->cpt_oc_get_article($article_info->uuid[0]);

                    if ( ! is_null($article)) {
                        print($this->cpt_create_article_meta_content($article));
                    } else {
                        //Can't connect to OC
                        print "<p>Trouble finding the article " . $article_info->uuid[0] . " in OpenContent.</p>";
                    }
                } else {
                    $listname = EveryList::get_list_name_by_id($list_id);
                    print "<p>Can't find article with position " . $list_pos . " in " . $listname . "</p>";
                }
            }

            if ($switched) {
                restore_current_blog();
            }
        } else {
            print "<p>Missing list id and/or list position</p>";
        }

        die(1);
    }

    /**
     * Output JSON with Template board meta content.
     */
    public function cpt_get_template_board_meta_content(): void
    {
        wp_send_json($this->cpt_get_template_board_meta_content_html($_POST['settings']));
    }

    public function cpt_get_widget_meta_content(): void
    {
        $type = $_POST['type'] ?? '';

        if (empty($type)) {
            wp_send_json([
                'headline' => 'Error',
                'content' => "Widget type not set"
            ]);
        }

        if ( ! isset($_POST['settings'], $_POST['colSettings'])) {
            wp_send_json([
                'headline' => 'Error',
                'content' => "Missing widget settings for widget of type: {$type}"
            ]);
        }

        $settings = json_decode(wp_unslash($_POST['settings']), true);
        $colsettings = json_decode(wp_unslash($_POST['colSettings']), true);

        switch ($type) {
            case 'oclist':
                $response = $this->cpt_get_oclist_meta_content_html($settings, $colsettings);
                break;
            case 'contentcontainer':
                $response = $this->cpt_get_contentcontainer_meta_content_html($settings, $colsettings);
                break;
            default:
                $response = [
                    'headline' => 'Error',
                    'content' => "Could not handle widget of type: {$type}"
                ];
        }

        wp_send_json($response);
    }

    /**
     * Grab information from OcArticle and create content for
     * article html
     *
     * @param OcArticle $article
     *
     * @return string
     */
    private function cpt_create_article_meta_content(OcArticle $article): string
    {
        $meta_content = '';

        // Add Product
        if (isset($article->product[0]) && strlen($article->product[0]) > 1) {
            $meta_content .= '<div class="meta_info"><p>' . __('Product:',
                    'everyboard') . '<strong> ' . $article->product[0] . '</strong></p></div>';
        }

        // Add Section
        if (isset($article->section[0]) && strlen($article->section[0]) > 1) {
            $meta_content .= '<div class="meta_info"><p>' . __('Section:',
                    'everyboard') . '<strong> ' . $article->section[0] . '</strong></p></div>';
        }

        // Add Pubdate
        if (isset($article->Pubdate[0]) || isset($article->pubdate[0])) {
            $pubDate = isset($article->Pubdate[0]) ? $article->Pubdate[0] : $article->pubdate[0];

            $meta_content .= '<div class="meta_info"><p>' . __('Pubdate:',
                    'everyboard') . '<strong> ' . date("Y-m-d H:i:s", strtotime($pubDate)) . '</strong></p></div>';
        }

        // Add Tags
        if (isset($article->tags) && count($article->tags) > 0) {
            $tags = [];

            // Check if we handling with Concepts
            if (is_object($article->tags[0])) {
                foreach ($article->tags as $tag) {
                    $tags[] = $tag->name[0];
                }
            } else {
                $tags = $article->tags;
            }

            $meta_content .= '<div class="meta_info"><p>' . __('Tags:',
                    'everyboard') . '<span class="article-meta-tag">' . join('</span><span class="article-meta-tag">',
                    $tags) . '</span></p></div>';
        }

        // Add Author-information
        if (isset($article->Author[0]) && strlen($article->Author[0]) > 1) {
            $meta_content .= '<div class="meta_info"><p>' . __('Author:',
                    'everyboard') . '<strong>' . $article->Author[0] . '</strong></p></div>';
        }

        // Add Concept author-information
        if (isset($article->authors) && count($article->authors) > 0) {
            $authors = [];

            // Check if we handling with Concepts
            if (is_object($article->authors[0])) {
                foreach ($article->authors as $author) {
                    $authors[] = $author->name[0];
                }
            } else {
                $authors = $article->authors;
            }

            $meta_content .= '<div class="meta_info"><p>' . (count($authors) > 1 ? __('Authors:',
                    'everyboard') : __('Author:', 'everyboard')) . ' <strong>' . join(', ',
                    $authors) . '</strong></p></div>';
        }

        $oc = OpenContent::getInstance();
        $image_service = $oc->getImageService();
        if ($image_service === 'imgix') {
            if (isset($article->imagepreviews[0])) {
                $url_data = parse_url($article->imagepreviews[0]);
                $imgix_url = $oc->getImgixUrl() . $url_data['path'];
                $params = '?w=50&h=50&fit=crop&crop=entropy&auto=format';
                $image_src = $imgix_url . $params;
                $meta_content .= '<div class="meta_info"><img class="article_preview_thumb structure-content-thumb" src="' . $image_src . '" /></div>';
            }
        } else {
            if (isset($article->imageuuid[0])) {
                $imengine_url = $oc->getImengineUrl();
                $params = "?uuid={$article->imageuuid[0]}&type=preview&type=preview&function=cover&width=50&height=50";
                $image_src = $imengine_url . $params;
                $meta_content .= '<div class="meta_info"><img class="article_preview_thumb structure-content-thumb" src="' . $image_src . '" /></div>';
            }
        }

        return $meta_content;
    }

    /**
     * Renders an article according to its settings and the settings inherited
     * by the parent column
     *
     * @param OcArticle $article
     * @param array     $article_data
     * @param array     $colsettings
     *
     * @return string
     */
    public function cpt_render_article(OcArticle $article, $article_data = [], $colsettings = [])
    {
        if ( ! isset($article->uuid[0])) {
            return false;
        }

        $uuid = $article->uuid[0];
        $headline = $article->headline[0] ?? __('No headline', 'everyboard');
        $settings = $article_data['settings'];

        // Strip '-char from name if it is there.
        if (isset($settings['name'])) {
            $settings['name'] = str_replace("'", "", $settings['name']);
            $settings['name'] = str_replace("´", "", $settings['name']);
        }

        // Decode JSON values from settings
        $settings_json = $this->cpt_get_structure_settings_json($article_data);

        $response_html = "";

        $type = $settings['type'];
        $name = $settings['name'];

        if (isset($uuid)) {

            $hidden = false;
            if (isset($article->pubdate[0]) && $this->env_settings->get_pubdate_hide_property()) { // If articles with pubdates in the future should be hidden.
                $pubdate_timestamp = strtotime($article->pubdate[0]);
                $now_timestamp = time();

                if ($pubdate_timestamp > $now_timestamp) {
                    $hidden = true;
                }
            }

            $article_class = '';
            if ($hidden) {
                $article_class = 'pubdate-future';
            }

            $oc = OpenContent::getInstance();

            $response_html .= '<article class="structure-item article-preview structure_item article_preview ' . $article_class . '"' .
                ' role="article"' .
                ' title="' . OcUtilities::truncate_article_text($headline, 40) . '"' .
                ' data-type="' . $type . '"' .
                ' data-uuid="' . $uuid . '"' .
                ' data-settings="' . $settings_json . '">';
            $response_html .= '<div class="drag_handle structure-drag-handle">';
            $response_html .= '<span class="structure_item_name structure-item-name">' . OcUtilities::truncate_article_text($name,
                    40) . '</span>';
            $response_html .= '<span class="article-action structure-action">';
            $response_html .= '<a href="' . $oc->getOcBaseUrl() . 'objects/' . $uuid . '/"><i class="fa fa-external-link" aria-hidden="true"></i></a>';
            $response_html .= $this->default_structure_content_icons;
            $response_html .= '</span>';
            $response_html .= '</div>';
            $response_html .= '<div class="article-content structure-content">';
            $response_html .= '<div class="meta_info">';
            $response_html .= $this->get_template_tag($settings, $colsettings['layout'] ?? 'None');
            $response_html .= '</div>';
            $response_html .= $this->cpt_create_article_meta_content($article);

            if ($hidden) {
                $response_html .= '<div class="article-pubdate-future-info">';
                $response_html .= '<i class="fa fa-clock-o"></i> ' .
                    __('Published: ', 'everyboard') . date('Y-m-d H:i:s', $pubdate_timestamp);
                $response_html .= '</div>';
            }
            $response_html .= '</div>';
            $response_html .= '</article>';

        }

        return $response_html;
    }

    public function cpt_render_removed_article($article_data = [], $colsettings = []): string
    {
        // Strip '-char from name if it is there.
        if (isset($article_data['settings']['name'])) {
            $article_data['settings']['name'] = str_replace("'", "", $article_data['settings']['name']);
            $article_data['settings']['name'] = str_replace("´", "", $article_data['settings']['name']);
        }

        $type = $article_data['settings']['type'];
        $uuid = $article_data['oc_uuid'];

        // Decode JSON values from settings
        $settings_json = $this->cpt_get_structure_settings_json($article_data);

        $response_html = '<article class="structure-item article-preview structure_item article_preview removed-article"' .
            ' role="article"' .
            ' data-type="' . $type . '"' .
            ' data-uuid="' . $uuid . '"' .
            ' data-settings="' . $settings_json . '">';
        $response_html .= '<div class="drag_handle structure-drag-handle">';
        $response_html .= '<span class="structure_item_name structure-item-name">Removed Article</span>';
        $response_html .= '<span class="article-action structure-action">' . $this->remove_icon . '</span>';
        $response_html .= '</div>';
        $response_html .= '<div class="article-content">';
        $response_html .= '<div class="meta_info"><p>' . __('The article',
                'everyboard') . '<strong> "' . $article_data['settings']['name'] . '"</strong> ' . __('was removed from Open Content.',
                'everyboard') . '</p></div>';

        $response_html .= '</div>';
        $response_html .= '</article>';

        return $response_html;
    }

    /**
     * Fetch all prefabbed widgets or of a specific type if specified
     *
     * @param array $options
     *
     * @return array
     */
    private function cpt_get_widget_list(array $options = [])
    {
        // Set default options
        $default_options = [
            'sorted' => true,
            'type' => 'all'
        ];

        // Merge options into the default options to make sure all options are set
        $options = array_merge($default_options, $options);

        // Get all widgets
        $prefabbed_widgets = EveryBoard_WidgetPrefab::get_instance()->get_widgets();
        $widget_list = [];

        // return empty array if no widgets where found
        if ( ! isset($prefabbed_widgets) || count($prefabbed_widgets) === 0) {
            return $widget_list;
        }

        // Extract widgets of a single type if specified in options
        if ($options['type'] !== 'all') {
            foreach ($prefabbed_widgets as $widget) {

                if (strtolower($widget['orgin_name']) === strtolower($options['type'])) {
                    $widget_list[] = $widget;
                }
            }
        } else {
            $widget_list = $prefabbed_widgets;
        }

        // if sorted list is specified
        if (is_bool($options['sorted']) && $options['sorted']) {
            $lasttype = "";
            $sorted_widget_list = [];

            // add all widgets under the same origin name key
            foreach ($widget_list as $widget) {

                // Create new typ-list fore each new type found
                if ($lasttype !== $widget["orgin_name"]) {
                    $sorted_widget_list[$widget["orgin_name"]] = [];
                }

                $sorted_widget_list[$widget["orgin_name"]][] = $widget;
                $lasttype = $widget["orgin_name"];
            }
            $widget_list = $sorted_widget_list;
        }

        return $widget_list;
    }

    /**
     * Renders prefabbed sidebar widgets
     * TODO: Is this the one being used?
     *
     * @return void
     */
    public function cpt_render_sidebar_widgets(): void
    {
        $widget_list = $this->cpt_get_widget_list();
        ksort($widget_list);

        foreach ($widget_list as $type => $widget_types) {
            asort($widget_types);
            $widget_list[$type] = $widget_types;
        }

        $linked_widget = $this->cpt_create_default_static_widget_data([
            'name' => __('Linked board', 'everyboard'),
            'type' => 'linked_widget',
            'settings' => [
                'board_id' => '0',
                'network_board_id' => 0,
                'network_board_site_id' => 0,
                'network_board_source_render' => 'false'
            ]
        ]);

        $template_board_widget = $this->cpt_create_default_static_widget_data([
            'name' => __('Template board', 'everyboard'),
            'type' => 'template_board_widget',
            'settings' => [
                'board_id' => 0,
                'network_board_id' => 0,
                'data_source' => 'list',
                'list' => null,
                'oc_query' => '*:*',
                'oc_query_sort' => null,
                'oc_query_start' => 1,
                'oc_query_limit' => '',
            ]
        ]);

        $embed_widget = $this->cpt_create_default_static_widget_data([
            'name' => __('Embed Widget', 'everyboard'),
            'type' => 'embed_widget',
            'settings' => ['content' => '']
        ]);

        $articlelist_widget = $this->cpt_create_default_static_widget_data([
            'name' => __('List item', 'everyboard'),
            'type' => 'articlelist_widget',
            'settings' => [
                'list' => '0',
                'listposition' => '0',
                'network_list' => '0',
                'network_list_site_id' => '0',
                'listlimit' => '1'
            ]
        ]);

        $oclist_widget = $this->cpt_create_default_static_widget_data([
            'name' => __('OC List item', 'everyboard'),
            'type' => 'oclist_widget',
            'settings' => [
                'list' => '0',
                'listposition' => 1,
                'listlimit' => 1,
                'site_id' => '0',
                'site_source_render' => 'false'
            ]
        ]);

        $contentcontainer_widget = $this->cpt_create_default_static_widget_data([
            'name' => __('Content container', 'everyboard'),
            'type' => 'contentcontainer_widget',
            'settings' => [
                'position' => '1',
                'limit' => '1'
            ]
        ]);

        # Start HTML
        ?>
        <div class="widget-list-container list-container list-hidden">
            <div class="list-heading list-toggler" title="<?php echo __('Click to toggle', 'everyboard'); ?>">
                <h3 rel="static">
                    <?php echo __('Static widgets', 'everyboard'); ?><span
                        class="fa fa-chevron-down list-toggler-icon"></span>
                </h3>
            </div>
            <ul class="widget-list list-body">
                <li class="list-item"><?php $this->cpt_render_embed_widget($embed_widget); ?></li>
                <li class="list-item"><?php $this->cpt_render_linked_widget($linked_widget); ?></li>
                <li class="list-item"><?php $this->cpt_render_template_board_widget($template_board_widget); ?></li>
                <?php if (EveryBoard_Global_Settings::is_everylist_active()) { ?>
                    <li class="list-item"><?php $this->cpt_render_articlelist_widget($articlelist_widget); ?></li>
                <?php } ?>
                <?php if (EveryBoard_Global_Settings::is_oclist_active()) { ?>
                    <li class="list-item"><?php $this->cpt_render_oclist_widget($oclist_widget); ?></li>
                <?php } ?>
                <li class="list-item"><?php $this->cpt_render_contentcontainer_widget($contentcontainer_widget); ?></li>
            </ul>
        </div>

        <?php # Loop through all widget-types
        foreach ($widget_list as $type => $widget_types) :?>

            <div class="widget-list-container list-container list-hidden">
                <div class="list-heading list-toggler" title="<?php echo __('Click to toggle', 'everyboard'); ?>">
                    <h3 rel="<?= $type ?>">
                        <?= $type . ' widgets' ?><span class="fa fa-chevron-down list-toggler-icon"></span>
                    </h3>
                </div>
                <ul class="widget-list list-body">
                    <?php # render all widgets according to type
                    foreach ($widget_types as $widget) {

                        $widget_data = $this->cpt_create_default_widget_data($widget);

                        # Start HTML
                        ?>

                        <li class="widget-list-item"><?php $this->cpt_render_widget($widget, $widget_data); ?></li>

                        <?php
                        # End HTML

                    }
                    ?>
                </ul>
            </div>

        <?php endforeach; ?>

        <?php
        # End HTML
    }

    /**
     * Fetch widget content
     *
     * @use EveryBoard_WidgetPrefab::get_widget_instance
     *
     * @param array $widget
     *
     * @return string HTML
     */
    private function cpt_get_widget_content(array $widget = []): string
    {
        if ( ! isset($widget['id'])) {
            return '';
        }

        $widget_instance = EveryBoard_WidgetPrefab::get_instance()->get_widget_instance($widget['id']);

        if ($widget_instance === null) {
            return '';
        }

        // Create new widget-object according to the instance type
        $widget_obj = new $widget_instance["classname"];

        // the content that the widget_board function, specified in the widget,
        // will be returned in form of html.
        if (method_exists($widget_obj, "widget_board")) {
            ob_start();
            $widget_obj->widget_board($widget_instance["settings"]);

            return ob_get_clean();
        }

        $widget_description = $widget['description'] ?? '';

        if ( ! empty($widget_description)) {
            return sprintf("<p>%s: {$widget_description}</p>", __('Description:', 'everyboard'));
        }

        return __('No description found', 'everyboard');
    }

    /**
     * Render a specific widget and its content if any.
     *
     * @param array $widget
     * @param array $widget_data
     *
     * @return void
     */
    private function cpt_render_widget(array $widget = [], array $widget_data = []): void
    {
        if ( ! isset($widget['id'])) {
            return;
        }

        $widget_content = $this->cpt_get_widget_content($widget);
        $settings_json = $this->cpt_get_structure_settings_json($widget_data);
        $name = $widget_data['settings']['name'];
        $widget_instance = EveryBoard_WidgetPrefab::get_instance()->get_widget_instance($widget['id']);

        if ($widget_instance === null || ! class_exists($widget_instance["classname"])) {
            return;
        }

        $widget_obj = new $widget_instance["classname"];
        $widget_type = $widget_obj->board_widget_type ?? '1';
        $widget_type_class = 'board-widget-type-' . $widget_type;

        # Start HTML
        ?>
        <div class="board_widget board-widget structure_item structure-item <?= $widget_type_class ?>"
             role="widget"
             rel="<?= $widget['orgin_name'] ?>"
             title="<?= $widget['name'] ?> widget"
             data-type="widget"
             data-widgetid="<?= $widget['id'] ?>"
             data-settings="<?= $settings_json ?>">
            <div class="drag_handle structure-drag-handle">
                <span class="structure_item_name structure-item-name"><?= $name ?></span>
                <span class="board_widget-action structure-action"
                      style="">
	                <?= $this->default_structure_content_icons ?>
                </span>
            </div>
            <div class="widget_content widget-content structure-content">
                <p>widget name: <?php echo $widget['name'] ?? __('Undefined', 'everyboard'); ?></p>
                <p>widget type: <?php echo $widget['orgin_name'] ?? __('Undefined', 'everyboard'); ?></p>
                <?= $widget_content ?>
            </div>
        </div>

        <?php
        # End HTML
    }

    /**
     * Function that handles the rendering of linked widgets
     *
     * TODO: Optimize.
     * TODO: Move rendering part into a render widget-function.
     *
     * @param array $widget_data
     *
     * @return void
     */
    private function cpt_render_embed_widget(array $widget_data = []): void
    {
        // Decode JSON values from settings
        $settings_json = $this->cpt_get_structure_settings_json($widget_data);

        $settings = $widget_data['settings'];
        $type = $settings['type'];
        $name = $settings['name'];

        $settings_content = (array_key_exists('content',
                $settings) && $settings['content'] !== '') ? htmlspecialchars(base64_decode($settings['content'])) : '<p>' . __('No content yet',
                'everyboard') . '</p>';
        # Start HTML
        ?>

        <div class="board_widget board-widget board_embed_widget structure_item structure-item"
             role="widget"
             data-type="<?= $type ?>"
             data-settings="<?= $settings_json ?>">
            <div class="drag_handle structure-drag-handle">
                <span class="structure_item_name structure-item-name"><?= $name ?></span>
                <span
                    class="board_widget-action structure-action"><?= $this->default_structure_content_icons ?></span>
            </div>
            <div class="widget_content widget-content structure-content">
                <?= $settings_content ?>
            </div>
        </div>

        <?php # End HTML
    }

    /**
     * Function that handles the rendering of linked widgets
     * TODO: Optimize.
     * TODO: Move rendering part into a render widget-function.
     *
     * @param array $widget_data
     */
    private function cpt_render_linked_widget(array $widget_data = []): void
    {
        // Decode JSON values from settings
        $settings_json = $this->cpt_get_structure_settings_json($widget_data);

        $settings = $widget_data['settings'];
        $type = $settings['type'];
        $name = $settings['name'];

        try {
            $link_settings = $this->get_valid_linked_board_settings($settings);

            $message = sprintf(__('Board selected: %s', 'everyboard'), $link_settings['presentation_link']);
        } catch (Exception $e) {
            $message = $e->getMessage();
        }

        # Start HTML
        ?>
        <div class="board_widget board-widget board_linked_widget structure_item structure-item"
             role="widget"
             data-type="<?= $type ?>"
             data-settings="<?= $settings_json ?>">
            <div class="drag_handle structure-drag-handle">
                <span class="structure_item_name structure-item-name"><?= $name ?></span>
                <span
                    class="board_widget-action structure-action"><?= $this->default_structure_content_icons ?></span>
            </div>
            <div class="widget_content widget-content structure-content">
                <p class="linked-board-message"><?= $message ?></p>
            </div>
        </div>
        <?php # End HTML

    }

    /**
     * Render a template board widget in board admin.
     *
     * @param array $widget_data
     */
    private function cpt_render_template_board_widget(array $widget_data = []): void
    {
        // Decode JSON values from settings
        $settings_json = $this->cpt_get_structure_settings_json($widget_data);

        $settings = $widget_data['settings'];

        $result = $this->cpt_get_template_board_meta_content_html($settings);

        $class_string = '';
        foreach ($result['variable_classes'] as $class => $use) {
            if ($use) {
                $class_string .= ' ' . $class;
            }
        }

        # Start HTML
        ?>
        <div
            class="board_widget board-widget board_template_board_widget structure_item structure-item<?= $class_string ?>"
            role="widget"
            data-type="<?= $settings['type'] ?>"
            data-settings="<?= $settings_json ?>">
            <div class="drag_handle structure-drag-handle">
                <span class="structure_item_name structure-item-name"><?= htmlentities($settings['name']) ?></span>
                <span
                    class="board_widget-action structure-action"><?= $this->default_structure_content_icons ?></span>
            </div>
            <div class="widget_content widget-content structure-content">
                <p><?= implode('</p><p>', $result['content']) ?></p>
            </div>
        </div>
        <?php # End HTML
    }

    /**
     * Generate raw meta data for a template board widget.
     *
     * @param array $settings
     *
     * @return array [
     *                  'content'          => (array) HTML strings, one paragraph each
     *                  'variable_classes' => (array) Class names for keys, BOOL for values indicating if this class
     *                                        should be included or not.
     *              ]
     */
    private function cpt_get_template_board_meta_content_html(array $settings = []): array
    {
        $content = [
            'board' => __('No board selected', 'everyboard'),
            'data_source' => __('No data source connected', 'everyboard'),
            'warning' => '',
        ];
        $variable_classes = [
            'board_widget_warning' => false
        ];

        $num_positions = 0;
        $num_articles = 0;
        $warning = '<strong class="warning">%s</strong>';

        $is_board_connected = false;
        try {
            $link_settings = $this->get_valid_linked_board_settings($settings);
            $num_positions = $link_settings['positions'];

            $content['board'] = sprintf(__('Board selected: %s', 'everyboard'), $link_settings['presentation_link']);

            $is_board_connected = true;

            if ($link_settings['in_trash']) {
                $variable_classes['board_widget_warning'] = true;
                $is_board_connected = false;
            } else {
                $content['board'] .= ' ' . sprintf(__('Requires %s articles', 'everyboard'), $link_settings['positions']);
            }

        } catch (NotSelectedException $e) {
            $content['board'] = $e->getMessage();
        } catch (BoardNotFoundException $e) {
            $content['board'] = sprintf($warning, $e->getMessage());
            $variable_classes['board_widget_warning'] = true;
        } catch (Exception $e) {
            $content['board'] = sprintf($warning, __('No board selected', 'everyboard'));
            $variable_classes['board_widget_warning'] = true;
        }

        $is_data_source_available = false;

        try {
            $dataSource = $settings['data_source'] ?? '';

            if (empty($dataSource)) {
                throw new InvalidArgumentException(__('Missing data source.', 'everyboard'));
            }

            $listUuid = $settings['list'] ?? '';
            $query = $settings['oc_query'] ?? '';
            $limit = $settings['oc_query_limit'] ?? '';
            $start = $settings['oc_query_start'] ?? 1;

            $startFrom = max((int)$start - 1, 0);

            $validator = new OcValidator();

            switch ($settings['data_source']) {
                case 'list':
                    if (empty($listUuid)) {
                        throw new NotSelectedException(__('No data source connected', 'everyboard'));
                    }

                    $is_data_source_available = $validator->validateList($listUuid, new OcListAdapter());

                    $count = max(($validator->getArticleCount() - $startFrom), 0);

                    $num_articles = min($count, $limit);

                    $string = __('%num% articles from list (%list%)', 'everyboard');
                    $trans = [
                        '%num%' => $num_articles,
                        '%list%' => OcList::get_list_name_by_id($listUuid)
                    ];
                    break;

                case 'query':
                    if (empty($query) || $limit === 0) {
                        throw new NotSelectedException(__('No data source connected', 'everyboard'));
                    }

                    $is_data_source_available = $validator->validateOcQuery($query, OcArticleProvider::create());

                    $count = max(($validator->getArticleCount() - $startFrom), 0);

                    $num_articles = min($count, $limit);

                    $string = __('%num% articles from query (%query%)', 'everyboard');
                    $trans = [
                        '%num%' => $num_articles,
                        '%query%' => $query
                    ];
                    break;
                default:
                    throw new UnexpectedValueException(
                        sprintf(__('Unknown data source "%s"', 'everyboard'), $dataSource)
                    );
            }

            if ($validator->validationFailed()) {
                $dataSourceMessage = sprintf(
                    $warning,
                    implode(', ', $validator->getValidationMessages())
                );
            } else {
                $dataSourceMessage = sprintf(
                    __('Data source: %s', 'everyboard'),
                    '<strong>' . strtr($string, $trans) . '</strong>'
                );
            }

        } catch (NotSelectedException $e) {
            $dataSourceMessage = $e->getMessage();
        } catch (Exception $e) {
            $dataSourceMessage = sprintf($warning, $e->getMessage());
        }

        $content['data_source'] = $dataSourceMessage;

        /**
         * Display extra warnings if board or data feed is unset.
         * (But having neither board nor data feed is OK, since it is the default settings.)
         */
        if ( ! $is_board_connected xor ! $is_data_source_available) {

            $variable_classes['board_widget_warning'] = true;

            if ( ! $is_board_connected) {
                $content['board'] = sprintf($warning, $content['board']);
            }
            if ( ! $is_data_source_available) {
                $content['data_source'] = sprintf($warning, $content['data_source']);
            }
        }
        /**
         * Display extra warning if data feed is insufficient for the board.
         */
        if ($is_board_connected && $is_data_source_available && $num_articles < $num_positions) {
            $content['warning'] = '<em>' . __('Not enough articles for positions in board', 'everyboard') . '</em>';
        }

        return [
            'content' => array_values($content),
            'variable_classes' => $variable_classes
        ];
    }

    /**
     * @param array $components
     *
     * @return string
     * @see \EveryBoard_CustomPostType::get_board_link_components
     *
     */
    private function get_board_link(array $components): string
    {
        $text = $components['text'];
        $link = ($components['in_trash'])
            ? $text . ' (' . __('In Trash', 'everyboard') . ')'
            : '<strong><a href="' . $components['href'] . '" target="_blank">' . $text . '</a></strong>';

        if ($components['site_name'] !== null) {
            $link .= ' (' . sprintf(__('Shared from %s', 'everyboard'), $components['site_name']) . ')';
        }

        return $link;
    }

    /**
     * @param int      $board_id
     * @param int|null $site_id
     *
     * @return null|array
     * @throws BoardNotFoundException
     */
    private function get_board_link_components(int $board_id, int $site_id = null): array
    {
        if ($site_id !== null) {
            switch_to_blog($site_id);
        }

        // TODO: Se if there is an easier way to get the link an the boards name
        $board = get_post($board_id);
        if ($board === null) {
            if ($site_id !== null) {
                restore_current_blog();
            }
            throw new BoardNotFoundException("Could not find the board with ID: {$board_id}");
        }

        if ($site_id !== null) {
            $site_name = get_bloginfo('name');
            restore_current_blog();
        }

        return [
            'href' => get_edit_post_link($board_id),
            'text' => $board->post_title,
            'site_name' => $site_name ?? null,
            'in_trash' => ($board->post_status === 'trash')
        ];
    }

    /**
     * Get the highest position needed by any content container in the board.
     *
     * This is synonymous with the number of articles needed to fill the board.
     *
     * @param int      $board_id
     * @param int|null $site_id
     *
     * @return int
     */
    private function get_highest_position_in_board(int $board_id, int $site_id = null): int
    {
        if ($site_id !== null) {
            switch_to_blog($site_id);
        }
        $metadata = get_metadata('post', $board_id, self::BOARD_JSON_FIELD);
        if ($site_id !== null) {
            restore_current_blog();
        }
        if ( ! isset($metadata[0])) {
            return 0;
        }
        $json = json_decode($metadata[0]);
        $highest_position = 0;
        foreach ($json->rows as $row) {
            foreach ($row->cols as $col) {
                foreach ($col->content as $content) {
                    if ( ! in_array($content->type, self::WIDGET_TYPES_WITH_POSITIONS, true)) {
                        continue;
                    }

                    $offset = (int)$content->settings->position;
                    $limit = (int)$content->settings->limit;

                    $highest_position = max($highest_position, $offset - 1 + $limit);
                }
            }
        }

        return $highest_position;
    }

    /**
     * Function that handles the rendering of linked widgets
     *
     * @param array $widget_data
     */
    private function cpt_render_articlelist_widget(array $widget_data = []): void
    {
        // Decode JSON values from settings
        $settings_json = $this->cpt_get_structure_settings_json($widget_data);

        $settings = $widget_data['settings'];
        $type = $settings['type'];
        $name = $settings['name'];

        $content = '';
        $switched = false;

        $list = (int)$settings['list'];

        if (isset($settings['network_list_site_id']) && $settings['network_list_site_id'] !== '' && $settings['network_list_site_id'] !== '0') {
            $list = (int)$settings['network_list'];
            $network_site_id = (int)$settings['network_list_site_id'];

            if ($network_site_id !== get_current_blog_id()) {
                switch_to_blog($network_site_id);
                $switched = true;
            }
        }

        $listposition = (int)$settings['listposition'];
        $listlimit = isset($settings['listlimit']) ? (int)$settings['listlimit'] : 1;
        $template = $settings['layout'] ?? '';

        if ( ! empty($list) && ! empty($listposition)) {
            if ($listlimit > 1) {
                $list_articles = EveryList::get_articles_from_list($list, $listposition, $listlimit);
                if (isset($list_articles)) {
                    $content = '<div class="meta_info">';
                    foreach ($list_articles as $article) {
                        if ( ! is_null($article)) {
                            $headline = $article->headline[0] ?? $article->uuid[0];
                            $content .= '<p><strong>' . $headline . '</strong></p>';
                        }
                    }
                    $content .= "</div>";
                } else {
                    $listname = EveryList::get_list_name_by_id($list);
                    $content = "<p>Can't find articles with starting position " . $listposition . " in " . $listname . "</p>";
                }
            } else {
                $article_info = EveryList::get_article_from_list($list, $listposition);

                if (isset($article_info->uuid[0])) {
                    $article = $this->cpt_oc_get_article($article_info->uuid[0]);

                    if ( ! is_null($article)) {
                        $content = $this->cpt_create_article_meta_content($article);
                        $name = $article->headline[0] ?? $name;
                    } else {
                        $content = "<p>Trouble connecting to Open Content, but article [ " . $article_info->uuid[0] . " ] is loaded.</p>";
                    }
                } else {
                    $listname = EveryList::get_list_name_by_id($list);
                    $content = "<p>Can't find article with position " . $listposition . " in " . $listname . "</p>";
                }
            }
        } else {
            $content = "<p>Missing list id and/or list position</p>";
        }

        if ($switched) {
            restore_current_blog();
        }

        ?>

        <div class="board_widget board-widget board_articlelist_widget structure_item structure-item"
             role="widget"
             data-type="<?= $type ?>"
             data-settings="<?= $settings_json ?>">
            <div class="drag_handle structure-drag-handle">
                <span class="structure_item_name structure-item-name"><?= $name ?></span>
                <span
                    class="board_widget-action structure-action"><?= $this->default_structure_content_icons ?></span>
            </div>
            <div class="articlelist-content structure-content">
                <?php echo $content; ?>
            </div>
        </div>

        <?php # End HTML
    }

    private function cpt_render_oclist_widget(array $widget_data = [], array $colsettings = []): void
    {
        $settings_json = $this->cpt_get_structure_settings_json($widget_data);

        $settings = $widget_data['settings'];
        $type = $settings['type'];
        $content = $this->cpt_get_oclist_meta_content_html($settings, $colsettings);

        ?>

        <div class="board_widget board-widget board_oclist_widget structure_item structure-item"
             role="widget"
             data-type="<?= $type ?>"
             data-settings="<?= $settings_json ?>">
            <div class="drag_handle structure-drag-handle">
                <span class="structure_item_name structure-item-name"><?= $content['headline'] ?></span>
                <span
                    class="board_widget-action structure-action"><?= $this->default_structure_content_icons ?></span>
            </div>
            <div class="oclist-content structure-content">
                <?= $content['content'] ?>
            </div>
        </div>

        <?php
    }

    private function cpt_get_oclist_meta_content_html(array $settings, array $colsettings = []): array
    {
        $errorMessages = [];
        $content = [];
        $response = [
            'headline' => $settings['name'] ?? __('OC List item', 'everyboard'),
            'content' => "<p>Missing list id and/or list position</p>"
        ];

        if ( ! EveryBoard_Global_Settings::is_oclist_active()) {
            $response['content'] = "<h3>OC List is not active.</h3>";

            return $response;
        }

        $list_id = $settings['list'];
        $listposition = (int)$settings['listposition'];
        $listlimit = isset($settings['listlimit']) ? (int)$settings['listlimit'] : 0;

        if (empty($list_id)) {
            $errorMessages[] = "<p>No List selected.</p>";
        }

        if (empty($listposition) || $listposition < 0) {
            $listposition = 0;
        }

        if ($listlimit < 1) {
            $errorMessages[] = '<p>Limit is set to <strong>0</strong>.</p>';
        }

        if ($listposition < 1) {
            $errorMessages[] = '<p>Position is set <strong>0</strong>.</p>';
        }

        if ( ! empty($errorMessages)) {
            $response['content'] = implode('', $errorMessages);

            return $response;
        }

        $listName = OcList::get_list_name_by_id($list_id);

        $content[] = $this->get_template_tag($settings, $colsettings['layout'] ?? 'None');

        if ($listlimit > 1) {
            $content[] = sprintf(
                "<p>Fetch articles <strong>%s-%s</strong> from \"<strong>{$listName}</strong>\".</p>",
                $listposition,
                ($listposition + $listlimit) - 1
            );
        } else {
            $content[] = "<p>Fetch article <strong>{$listposition}</strong> from \"<strong>{$listName}</strong>\".</p>";
        }

        $articles = OcList::get_articles($list_id, $listposition, $listlimit);

        if (empty($articles)) {
            $response['content'] = "<p>Can't find articles with starting position " . $listposition . " in " . $listName . "</p>";

            return $response;
        }

        $metaInfo = '';
        foreach ($articles as $article) {
            if ($article instanceof OcArticle) {
                $headline = $article->headline[0] ?? $article->uuid[0];
                $metaInfo .= '<li><strong>"' . $headline . '"</strong></li>';
            }
        }
        $content[] = '<div class="meta_info"><ul>' . $metaInfo . '</ul></div>';

        $response['headline'] = $listName;
        $response['content'] = implode('', $content);

        return $response;
    }

    /**
     * Function that handles the rendering of content containers.
     *
     * @param array $widget_data
     * @param array $colsettings
     */
    private function cpt_render_contentcontainer_widget(array $widget_data = [], array $colsettings = []): void
    {
        // Decode JSON values from settings
        $settings_json = $this->cpt_get_structure_settings_json($widget_data);

        $settings = $widget_data['settings'];
        $type = $settings['type'];
        $content = $this->cpt_get_contentcontainer_meta_content_html($settings, $colsettings);

        ?>

        <div class="board_widget board-widget board_contentcontainer_widget structure_item structure-item"
             role="widget"
             data-type="<?= $type ?>"
             data-settings="<?= $settings_json ?>">
            <div class="drag_handle structure-drag-handle">
                <span class="structure_item_name structure-item-name"><?= $content['headline'] ?></span>
                <span
                    class="board_widget-action structure-action"><?= $this->default_structure_content_icons ?></span>
            </div>
            <div class="contentcontainer-content structure-content">
                <?= $content['content'] ?>
            </div>
        </div>

        <?php # End HTML
    }

    /**
     * Function to setup args for and register OC article as custom post type
     *
     * @param array $actions
     * @param post object $post
     *
     * @return array
     */
    public function cpt_board_action_row($actions = [], $post): array
    {
        if ($post->post_type === 'everyboard') {
            $actions['test'] = '<a href="' . admin_url('admin.php?action=copy_board&post=' . $post->ID) . '">' . __('Copy',
                    'everyboard') . '</a>';
            $actions['export'] = '<a href="' . admin_url('admin.php?action=export_board&post=' . $post->ID) . '">' . __('Export',
                    'everyboard') . '</a>';
        }

        return $actions;
    }

    /**
     * Exports a board.
     */
    public function cpt_board_export_action(): void
    {
        if ( ! isset($_GET['post'], $_GET['action']) || $_GET['action'] !== 'export_board') {
            wp_die('Can not find board to export.');
        }

        $post_id = $_GET['post'] ?? '';
        $board = $this->cpt_get_board_json(self::TEMP_BOARD_JSON_FIELD, $post_id);
        $json = $this->cpt_cleanup_board_content($board);

        header('Content-disposition: attachment; filename=export_board_' . $post_id . '.json');
        header('Content-type: application/json');
        echo $json;
    }

    /**
     * Duplicates a board, redirects to the created boards edit page.
     */
    public function cpt_board_duplicate_action(): void
    {
        if ( ! isset($_GET['post'], $_GET['action']) || $_GET['action'] !== 'copy_board') {
            wp_die('Can not find board to duplicate.');
        }

        $post_id = $_GET['post'] ?? '';
        $post = get_post($post_id);


        if ($post instanceof WP_Post) {
            $new_post_id = $this->cpt_board_duplicate($post);
            wp_redirect(admin_url('post.php?action=edit&post=' . $new_post_id));
        }
    }

    /**
     * Duplicates a given post and creates a new instance of it, returns new post id.
     *
     * @param $post , post object
     *
     * @return mixed post id
     */
    public function cpt_board_duplicate(WP_Post $post)
    {
        if ($post->post_type !== 'everyboard') {
            return null;
        }

        $new_post = [
            'post_author' => $post->post_author,
            'post_content' => $post->post_content,
            'post_title' => $post->post_title . ' (copy)',
            'post_excerpt' => $post->post_excerpt,
            'post_status' => 'draft',
            'comment_status' => $post->comment_status,
            'ping_status' => $post->ping_status,
            'post_password' => $post->post_password,
            'post_name' => $post->post_title . ' (copy)',
            'post_content_filtered' => $post->post_content_filtered,
            'post_parent' => $post->post_parent,
            'menu_order' => $post->menu_order,
            'post_type' => $post->post_type,
            'post_mime_type' => $post->post_mime_type
        ];

        // Creates a new post.
        $new_post_id = wp_insert_post($new_post);

        if ($new_post_id === 0) {
            return null;
        }

        // Loops through custom fields for old post and adds them to the new post.
        $custom_fields = get_post_custom($post->ID);

        foreach ($custom_fields as $field_name => $values) {

            foreach ($values as $value) {
                add_post_meta($new_post_id, $field_name, wp_slash($value));
            }
        }

        // Return new post id.
        return $new_post_id;
    }

    /**
     *
     * @param int $post_id
     *
     * @return array|mixed
     */
    public function get_board_pages($post_id)
    {
        return get_posts([
            'post_type' => 'page',
            'meta_query' => [
                [
                    'key' => 'everyboard_id',
                    'value' => $post_id,
                    'compare' => '='
                ]
            ]
        ]);
    }

    ############################# NEW/Updated functions #############################

    /**
     * Add all ajax-actions that's used by board.js
     *
     * @use add_action('wp_ajax_*') global wordpress-function
     * @return void
     */
    private function cpt_add_ajax_actions()
    {
        add_action('wp_ajax_save_board_json', [
            &$this,
            'cpt_board_save_json'
        ]);      // Ajax Admin function to save board json data.
        add_action('wp_ajax_cpt_oc_make_article', [
            &$this,
            'cpt_oc_make_article'
        ]);      // Ajax function for remove saved searches
        add_action('wp_ajax_cpt_make_connection', [
            &$this,
            'cpt_ajax_make_connection'
        ]); // Ajax function to connect board to a page
        add_action('wp_ajax_cpt_save_post', [
            &$this,
            'cpt_save_post'
        ]);            // Ajax function to save board_json as post

        add_action('wp_ajax_cpt_get_meta_content', [
            &$this,
            'cpt_get_meta_content'
        ]);    // Ajax function to fetch meta content

        add_action('wp_ajax_cpt_get_widget_meta_content', [
            &$this,
            'cpt_get_widget_meta_content'
        ]);

        add_action('wp_ajax_cpt_get_template_board_meta_content', [
            &$this,
            'cpt_get_template_board_meta_content'
        ]);

        /**
         * Test if one function can be used as router
         */
        add_action('wp_ajax_everyboard_api', [
            &$this,
            'everyboard_api'
        ]);

        // Heartbeat filter.
        add_filter('heartbeat_received', [
            &$this,
            'cpt_heartbeat_received'
        ], 10, 2);
    }

    /**
     * Add the localized script used in Everyboard javascripts
     *
     * @use wp_localize_script() global wordpress-function
     * @return void
     */
    private function cpt_add_localized_script()
    {
        $scriptSettings = [
            'Everyboard' => ['globalsettings' => $this->cpt_get_everyboard_global_settings()],
            'oc' => ['url' => OpenContent::getInstance()->getOcBaseUrl()],
            'framework' => ['columns' => $this->max_cols],
            'article_templates' => ['parts' => EveryBoard_Settings::get_templates()],
            'admin' => ['boards_url' => admin_url('edit.php?post_type=everyboard')]
        ];

        foreach ($scriptSettings as $name => $value) {
            wp_localize_script('boardadmin', $name, $value);
        }
    }

    /**
     * Retrieve all needed global settings for localization in board.js
     *
     * @return array
     */
    private function cpt_get_everyboard_global_settings(): array
    {
        return [
            'boards' => $this->cpt_get_all_published_boards(),
            'networkBoards' => $this->cpt_network_get_all_published_boards(),
            'version' => EVERYBOARD_VERSION,
            'jsonFormatVersion' => EVERYBOARD_JSON_FORMAT_VERSION,
            'frameworkSettings' => ['columns' => $this->max_cols],
            'articleTemplates' => $this->cpt_get_article_templates(),
            'cssClasses' => $this->cpt_get_saved_css_classes(),
            'lists' => EveryBoard_Global_Settings::is_everylist_active() ? EveryList::get_all_lists() : [],
            'oclists' => EveryBoard_Global_Settings::is_oclist_active() ? OcList::get_all_lists() : [],
            'ocsortings' => $this->cpt_get_oc_sort_option_names(),
            'networkLists' => EveryBoard_Global_Settings::is_everylist_active() ? EveryList::get_networked_lists() : [],
            'sites' => EveryBoard_Global_Settings::is_oclist_active() ? OcList::get_wp_sites() : []
        ];
    }

    /**
     * Retrieve all templates available for usage
     *
     * @use EveryBoard_Settings
     * @return array
     */
    private function cpt_get_article_templates(): array
    {
        $templates = EveryBoard_Settings::get_templates();

        $article_templates = [];
        foreach ($templates as $name => $path) {
            $article_templates[] = [
                'name' => $name,
                'path' => $path
            ];
        }

        return $article_templates;
    }

    /**
     * Retrieve all added classes from global settings
     *
     * @use EveryBoard_Global_Settings
     * @return array
     */
    private function cpt_get_saved_css_classes(): array
    {
        $cssObj = new EveryBoard_Global_Settings(false);
        $available_css = $cssObj->eb_global_settings_get_css_classes();

        $default_class = [
            'name' => "",
            'description' => "",
            'board' => 'false',
            'row' => 'false',
            'column' => 'false',
            'article' => 'false',
            'widget' => 'false'

        ];
        $available_css = $available_css ?? [];


        $class_arr = [];

        foreach ($available_css as $css_class) {

            $css_class = array_replace($default_class, $css_class);

            $class_arr[] = [
                'className' => $css_class['name'],
                'description' => $css_class['class'],
                'board' => $css_class['board'],
                'row' => $css_class['row'],
                'column' => $css_class['column'],
                'article' => $css_class['article'],
                'widget' => $css_class['widget']
            ];
        }

        return $class_arr;
    }

    /**
     * Retrieve available board names and id
     *
     * @use WP_Query
     * @return array
     */
    private function cpt_get_all_published_boards()
    {
        $args = [
            'post_type' => 'everyboard',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
            'post_status' => 'publish'
        ];

        $wpBoards = get_posts($args);

        $boards = [
            [
                'name' => '-- Select board --',
                'boardId' => '0'
            ]
        ];
        foreach ($wpBoards as $board) {

            // Don't include current board to the list of available boards
            if ($board->ID !== $this->post->ID) {
                $boards[] = [
                    'name' => $board->post_title,
                    'boardId' => $board->ID
                ];
            }
        }

        return $boards;
    }

    /**
     * Retrieve available board names and id FOR ALL NETWORK SITES
     *
     * @use WP_Query
     * @return array
     */
    private function cpt_network_get_all_published_boards()
    {
        if ( ! is_multisite()) {
            return [];
        }
        if (isset($this->published_network_boards)) {
            return $this->published_network_boards;
        }

        $original_blog_id = get_current_blog_id();
        $sites = get_sites();
        $boards = [
            [
                'name' => '',
                'siteId' => 0,
                'boards' => [
                    [
                        'name' => '-- Select board --',
                        'boardId' => '0'
                    ]
                ]
            ]
        ];

        foreach ($sites as $site) {

            if (isset($site->blog_id)) {

                $site_id = (int)$site->blog_id;
                if ($original_blog_id !== $site_id) {

                    switch_to_blog($site_id);

                    $args = [
                        'post_type' => 'everyboard',
                        'posts_per_page' => -1,
                        'orderby' => 'title',
                        'order' => 'ASC',
                        'post_status' => 'publish',
                        'meta_key' => 'board_is_shared',
                        'meta_value' => 'true'
                    ];

                    $wp_boards = get_posts($args);
                    $site_boards = [
                        'name' => get_bloginfo('name'),
                        'siteId' => $site_id,
                        'boards' => []
                    ];

                    foreach ($wp_boards as $board) {
                        $site_boards['boards'][] = [
                            'name' => $board->post_title,
                            'boardId' => $board->ID
                        ];
                    }

                    // Only add the site if it has shared boards.
                    if (count($site_boards['boards']) > 0) {
                        $boards[] = $site_boards;
                    }

                    restore_current_blog();
                }
            }
        }

        $this->published_network_boards = $boards;

        return $boards;
    }

    /**
     * Add all javascript-files used by Everyboard
     *
     * @use wp_enqueue_script() global wordpress-function
     * @return void
     */
    private function cpt_add_scripts()
    {
        wp_enqueue_script(
            'boardadmin',
            EVERYBOARD_BASE . 'assets/dist/js/boardadmin.min.ugly.js',
            ['jquery-ui-core'],
            EVERYBOARD_VERSION
        );
        wp_localize_script('boardadmin', 'translation_boardadmin', [
            'error_jquery_not_found' => __("EveryBoard requires jQuery to work properly but couldn't find it!",
                "everyboard"),
            'error_everywarejs_not_found' => __("EveryBoard requires Everyware.js to work properly but couldn't find it!",
                "everyboard"),
            'error_everyware_variable' => __("EveryBoard need to have the Everyboard-variable to be localized by Wordpress in order to work properly!",
                "everyboard"),
            'error_ajaxurl_variable' => __("EveryBoard need to have the ajaxurl-variable to be localized by Wordpress in order to work properly!",
                "everyboard"),
            'error_article_template_variable' => __("EveryBoard need to have the article_templates-variable to be localized by Wordpress in order to work properly!",
                "everyboard"),
            'error_framework_variable' => __("EveryBoard need to have the framework-variable to be localized by Wordpress in order to work properly!",
                "everyboard"),
            'error_handlebars' => __("EveryBoard requires Handlebars to work properly but couldn't find it!",
                "everyboard"),
            'header_settings' => __("Settings", "everyboard"),
            'header_settings_template_board' => __("Template Board Settings", "everyboard"),
            'label_active_layout' => __("Active layout", "everyboard"),
            'label_linked_board' => __("Internal linked board", "everyboard"),
            'label_linked_network_board' => __("External linked board", "everyboard"),
            'label_linked_template_board' => __("Board", "everyboard"),
            'label_linked_network_template_board' => __("Shared board", "everyboard"),
            'label_name' => __("Name", "everyboard"),
            'label_description' => __("Description", "everyboard"),
            'label_content' => __("Content", "everyboard"),
            'label_data_source' => __("Data source", "everyboard"),
            'placeholder_oc_list' => __("Select List", "everyboard"),
            'placeholder_oc_query' => __("Enter query", "everyboard"),
            'placeholder_oc_query_sort' => __("Select", "everyboard"),
            'label_oc_list' => __("List", "everyboard"),
            'label_oc_query' => __("Query", "everyboard"),
            'label_oc_query_sort' => __("Sorting order", "everyboard"),
            'label_oc_query_start' => __("Start from", "everyboard"),
            'label_oc_query_limit' => __("Limit", "everyboard"),
            'button_validate_query' => __("Validate query", "everyboard"),
            'label_list_name' => __("Internal List", "everyboard"),
            'label_external_list_name' => __("External List", "everyboard"),
            'label_list_position' => __("List start", "everyboard"),
            'label_list_limit' => __("List limit", "everyboard"),
            'label_position' => __("Position", "everyboard"),
            'label_limit' => __("Limit", "everyboard"),
            'tablehead_device' => __("device", "everyboard"),
            'tablehead_width' => __("width", "everyboard"),
            'tablehead_hide' => __("hide", "everyboard"),
            'tablehead_remove' => __("remove", "everyboard"),
            'buttontitle_save_row_and_settings' => __("Save this row and its current settings as a template",
                "everyboard"),
            'buttontitle_close_window' => __("Close window", "everyboard"),
            'buttontitle_close_without_save' => __("Close form without saving", "everyboard"),
            'buttontitle_save_changes' => __("Save changes without closing form", "everyboard"),
            'buttontitle_save_changes_and_close' => __("Save changes & close form", "everyboard"),
            'button_save_as_template' => __("Save as template", "everyboard"),
            'placeholder_description' => __("Description", "everyboard"),
            'placeholder_add_html_here' => __("Add your HTML here!", "everyboard"),
            'span_edit_classes' => __("Edit classes", "everyboard"),
            'header_active_classes' => __("active classes", "everyboard"),
            'label_deactivate_wrapper' => __("deactivate wrapper", "everyboard"),
            'header_classlist' => __("Classlist", "everyboard"),
            'button_done' => __("Done", "everyboard"),
            'button_cancel' => __("Cancel", "everyboard"),
            'button_apply' => __("Apply", "everyboard"),
            'button_ok' => __("OK", "everyboard"),
            'template_inherited' => __("Inherited", "everyboard"),
            'template_templatename' => __("Template:", "everyboard"),
            'board_json_saving' => __("Saving", "everyboard"),
            'linked_board_no_widget_connection' => __("This widget is not connected to a board", "everyboard"),
            'linked_board_widget_connected' => __("This widget is connected to", "everyboard"),
            'structure_item_remove_confirm' => __("Remove item and its content?", "everyboard"),
            'query_remove_confirm' => __("Do you want to remove this query permanently?", "everyboard"),
            'query_save_config' => __("Do you want to save this query?", "everyboard"),
            'remove_row_confirm' => __("Do you want to remove this row permanently?", "everyboard"),
            'bloodhound_hits' => __("Hits", "everyboard"),
            'restore_boardversion_confirm' => __("Are you sure you want to restore the board to an earlier version?",
                "everyboard"),
            'restore_boardversion_failure' => __("Could not restore to this version.", "everyboard"),
            'render_boardversion_no_earlier_versions' => __("No earlier versions of this board exists. If you publish the board a version will be created.",
                "everyboard"),
            'boardconnection_active_on' => __("This board is active on", "everyboard"),
            'boardconnection_no_page_chosen' => __("No page was chosen!", "everyboard"),
            'board_external_loading_article' => __("Loading article", "everyboard"),
            'board_external_could_not_find_article' => __("Could not find article from OC.", "everyboard"),
            'board_external_no_oc_connection' => __("No OC connection found.", "everyboard"),
            'imengine_load_image' => __("Loading image", "everyboard"),
            'board_changed' => __("Board Changed", "everyboard"),
            'board_changed_info' => __("The Board content has changed. This change could have been made from another browser, user, or tab. Please go back or close this window and continue working with the board in the active window.",
                "everyboard"),
            'board_go_back' => __("Go back", "everyboard"),
            'board_confirm_delete' => __("Confirm delete", "everyboard"),
            'board_confirm_delete_sure' => __("Are you sure you want to delete this", "everyboard"),
            'board_confirm_delete_col' => __("column", "everyboard"),
            'board_confirm_delete_row' => __("row", "everyboard"),
            'board_confirm_delete_checkbox' => __("Please confirm delete by checking this.", "everyboard"),
            'drop_outside_textarea' => __("Your drop was outside the content area", "everyboard"),
            'from' => __("from", "everyboard"),
            'board_render_from_source' => __("Render board from source site.", "everyboard"),
            'oclist_name' => __("List name", "everyboard"),
            'label_site_name' => __("Render from site", "everyboard"),
            'oclist_site_source_render' => __("Render items from source site.", "everyboard")
        ]);
    }

    /**
     * Add all css-files used by Everyboard
     *
     * @use wp_enqueue_style() global wordpress-function
     * @return void
     */
    private function cpt_add_styles()
    {
        wp_enqueue_style(
            'boardadmin-css',
            EVERYBOARD_BASE . 'assets/dist/css/boardadmin.min.css',
            [],
            EVERYBOARD_VERSION
        );
    }

    /**
     * Function to clean out all articles and widgets in the board
     *
     * @param string $board
     *
     * @return string
     */
    private function cpt_cleanup_board_content($board = '')
    {
        $board_content = json_decode($board);

        // Cleanup
        if (isset($board_content->rows)) {
            foreach ($board_content->rows as $row) {

                if (isset($row->cols)) {
                    foreach ($row->cols as $col) {

                        if (isset($col->content)) {
                            $col->content = [];
                        }
                    }
                }
            }
        }

        return json_encode($board_content, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Function to update structure data to 0.3 version
     *
     * @param array  $old_structure_data
     * @param string $typeString
     * @param string $board
     * @param int    $index
     *
     * @return array
     */
    public function cpt_update_structure_data_array(
        array $old_structure_data,
        $typeString = '',
        $board = '',
        $index = 0
    ) {

        $new_structure_data = $old_structure_data;

        switch ($typeString) {
            case 'row':
                return $this->update_rows($board, $old_structure_data, $index);
            case 'col':
                return $this->update_column($board, $old_structure_data, $index);
            case 'item':
                return $this->update_content_item($board, $old_structure_data, $index);
            default:
        }

        // Update settings
        $new_structure_data['settings'] = $this->cpt_update_old_settings($old_structure_data['settings'], $typeString);

        return $new_structure_data;
    }


    public function update_board($board): BoardBoard
    {
        echo "<h3>Update Board</h3>";

        $newBoard = new BoardBoard();
        if (array_key_exists('board', $board)) {
            $newBoard->setName($board['board']);
            unset($board['board']);
        }

        if (array_key_exists('date', $board)) {
            $newBoard->setDate($board['date']);
            unset($board['date']);
        }

        if (array_key_exists('version', $board)) {
            $newBoard->setVersion($board['version']);
            unset($board['version']);
        }

        if (array_key_exists('index', $board)) {
            $newBoard->setIndex($board['index']);
            unset($board['index']);
        }

        unset($board['rows']);
        // If we have any keys left add those to an array on Board object
        foreach ($board as $key => $property) {
            $newBoard->$key = $property;
        }

        return $newBoard;
    }

    public function update_rows(BoardBoard $board, $row): BoardRow
    {
        echo "<h3>Update Rows</h3>";
        $newRow = new BoardRow();

        $rowSettings = $this->cpt_update_old_settings($row['settings'], 'row');
        unset($row['settings']);

        $newRow->addSettings($rowSettings);
        unset($row['cols']);

        if (array_key_exists('index', $row)) {
            $newRow->setIndex($row['index']);
            unset($row['index']);
        }

        if (array_key_exists('type', $row)) {
            unset($row['type']);
        }

        $newRow->setType("row");

        foreach ($row as $key => $old) {
            $newRow->$key = $old;
        }

        $board->addRow($newRow);

        return $newRow;
    }

    public function update_column(BoardRow $row, $col): BoardColumn
    {
        $newColumn = new BoardColumn();
        unset($col['content']);

        if (array_key_exists('width', $col)) {
            $newColumn->setWidth($col['width']);
            $width = $col['width'];
            unset($col['width']);
        } else {
            $width = 100;
        }

        // Add colspan depending on column width
        $col_span = (int)round($this->max_cols * ($width / 100));
        $col['settings']['colspan'] = $col_span;

        $colSettings = $this->cpt_update_old_settings($col['settings'], 'col');
        unset($col['settings']);
        $newColumn->addSettings($colSettings);

        if (array_key_exists('type', $col)) {
            $newColumn->setType('col'); //TODO added col value, previously $col['type']
            unset($col['type']);
        }

        if (array_key_exists('index', $col)) {
            $newColumn->setIndex($col['index']);
            unset($col['index']);
        }

        foreach ($col as $key => $old) {
            $newColumn->$key = $old;
        }

        $row->addColumn($newColumn);

        return $newColumn;
    }

    public function update_content_item(BoardColumn $col, $item, $index = 0): BoardItem
    {
        $itemSettings = $this->cpt_update_old_settings($item['settings']);

        if ( ! empty($item['type'])) {
            $itemSettings['type'] = $item['type'];

            if (strpos($item['type'], 'widget') !== false) {
                $itemSettings['name'] = $item['type'];
            }
        } else {
            $itemSettings['type'] = 'oc_article';
        }

        unset($item['settings']);

        $newItem = new BoardItem();
        $newItem->addSettings($itemSettings);

        if (array_key_exists('type', $item)) {
            $newItem->setType($item['type']);
            unset($item['type']);
        }

        $newItem->setIndex($index);

        if (array_key_exists('widget_id', $item)) {
            if ( ! empty($item['widget_id'])) {
                $newItem->setWidgetId($item['widget_id']);
            }
            unset($item['widget_id']);
        }

        if (array_key_exists('oc_uuid', $item)) {
            $newItem->setOcUuid($item['oc_uuid']);
            unset($item['oc_uuid']);
        }

        foreach ($item as $key => $old) {
            $newItem->$key = $old;
        }

        $col->addContent($newItem);

        return $newItem;
    }


    /**
     * Function to convert a board v0.2 into a v0.3
     *
     * @param array $old_board
     *
     * @return array $new_board
     */
    private function cpt_convert_board_to_0_3(array $old_board)
    {
        $new_board = $old_board;

        $rows = $old_board['rows'] ?? [];

        // loop through all rows to convert them anr their content
        foreach ($rows as $row_key => $row) {

            $columns = $row['cols'] ?? [];

            // loop through columns if any
            foreach ($columns as $col_key => $col) {

                $content = $col['content'] ?? [];
                // loop through content if any
                foreach ($content as $item_key => $item) {

                    // Convert every content item in column
                    $content[$item_key] = $this->cpt_update_structure_data_array($item);
                }

                // Add new content to column
                $col['content'] = $content;

                // Add new column to columns
                // Convert every column in the row
                $columns[$col_key] = $this->cpt_update_structure_data_array($col);

            }

            // Add new columns to row
            $row['cols'] = $columns;

            // Add new row to rows
            // Convert every row in the board
            $rows[$row_key] = $this->cpt_update_structure_data_array($row);

        }
        // Add new rows to board
        $new_board['rows'] = $rows;
        $new_board['VERSION'] = '0.3';

        return $new_board;
    }

    /**
     * Function to make sure the board json is updated to the correct version
     *
     * @param array $options
     *
     * @return mixed|string
     */
    private function cpt_convert_to_correct_version(array $options)
    {
        $board_json = $options['board_json'];

        // Do not continue if there's no board to convert
        if ($board_json === '') {
            return $board_json;
        }

        // Decode json into an array to be able to handle it.
        $board_array = json_decode($board_json, true);

        // Set version to 0.2 if no version exist on the board
        if ( ! array_key_exists('VERSION', $board_array)) {
            $board_array['VERSION'] = '0.2';
        }

        // Do nothing if board is up to date
        if ($board_array['VERSION'] === EVERYBOARD_VERSION) {
            return $board_json;
        }

        // Check the boards version and make the appropriate conversion
        switch ($board_array['VERSION']) {
            case '0.2':
                $board_array = $this->cpt_convert_board_to_0_3($board_array);
                break;
        }

        // Convert to json again
        return json_encode($board_array, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Function to fetch the requested json from Everyboard
     *
     * @param string $key
     * @param int    $post_id
     *
     * @return mixed
     */
    private function cpt_get_board_json($key = '', $post_id)
    {
        $json = get_post_custom_values($key, $post_id);

        // Add the requested option if it doesn't exist
        if ( ! $json) {
            $option = [];
            add_option($key, $option);
        }

        return (isset($json)) ? $json[0] : '';
    }

    /**
     * Use Wordpress global function to fetches the global queries
     *
     * @use get_option()
     * @return array
     */
    private function cpt_get_global_queries()
    {
        return $this->cpt_get_option("board_global_queries");
    }

    /**
     * Use Wordpress global function to fetches the requested option
     *
     * @use get_option()
     *
     * @param string $key
     *
     * @return array
     */
    private function cpt_get_option($key)
    {
        $option = get_option($key);

        // Add the requested option if it doesn't exist
        if ( ! $option) {
            $option = [];
            add_option($key, $option);
        }

        return $option;
    }

    /**
     * Return the id of the requested key
     *
     * @use $_POST
     * @param string $key
     *
     * @return int|null
     */
    private function cpt_get_posted_id_by_key($key)
    {
        return isset($_POST[$key]) ? (int)$_POST[$key] : null;
    }

    /**
     * Uses the postname to set all posttype labels
     *
     * @param array $post_name
     *
     * @return array
     */
    private function cpt_get_posttype_labels(array $post_name = []): array
    {
        $default_post_name = [
            'single' => 'custom post',
            'list' => 'custom posts'
        ];
        $post_name = array_merge($default_post_name, $post_name);

        $single = ucfirst(strtolower($post_name['single']));
        $list = ucfirst(strtolower($post_name['list']));

        return [
            'name' => $list,
            'singular_name' => $single,
            'add_new' => __('Add New', 'everyboard') . ' ' . $single,
            'add_new_item' => __('Add New', 'everyboard') . ' ' . $single,
            'edit_item' => __('Edit', 'everyboard') . ' ' . $single,
            'new_item' => __('New', 'everyboard') . ' ' . $single,
            'view_item' => __('View', 'everyboard') . ' ' . $single,
            'search_items' => __('Search', 'everyboard') . ' ' . $list,
            'not_found' => __("Does not contain any", 'everyboard') . ' ' . $list,
            'not_found_in_trash' => __("Trash does not contain any", "everyboard") . ' ' . $list
        ];
    }

    private function cpt_get_sites()
    {
        global $wp_version;
        if (version_compare($wp_version, '4.6', '>=') || version_compare($wp_version, '4.6.0', '>=')) {
            return get_sites();
        }

        return wp_get_sites();
    }

    protected function add_colspan_if_exist($settings, &$newSettings): void
    {
        if (array_key_exists('colspan', $settings)) {
            $newSettings['colspan'] = (string)$settings['colspan'];
        }
    }

    /**
     * Add new properties to the board array
     *
     * @param array $board
     *
     * @return array
     */
    private function cpt_update_board_properties(array $board = []): array
    {
        $default_board_properties = [
            'settings' => $this->default_board_settings
        ];

        return array_replace_recursive($default_board_properties, $board);
    }

    /**
     * Update settings for compatibility with earlier versions
     *
     * @param array  $settings
     * @param string $typeString
     *
     * @return array
     */
    private function cpt_update_old_settings(array $settings = [], $typeString = ''): array
    {
        $device_settings = $settings['devices'] ?? $this->default_structure_settings['devices'];
        $newSettings = $this->getDefaultStructureSettings();
        /**
         * Move ismobile to it's new position
         *
         * New position = $settings['devices']['mobile']['hidden'];
         */
        if (array_key_exists('ismobile', $settings)) {

            $hidden = ($settings['ismobile'] === 'true') ? 'false' : 'true';
            $newSettings['devices']['mobile']['hidden'] = $hidden;
            unset($settings['ismobile']);
        }

        /**
         * Move istablet to it's new position
         *
         * New position = $settings['devices']['tablet']['hidden'];
         */
        if (array_key_exists('istablet', $settings)) {
            $hidden = ($settings['istablet'] === 'true') ? 'false' : 'true';
            $newSettings['devices']['tablet']['hidden'] = $hidden;
            $this->add_colspan_if_exist($settings, $newSettings['devices']['tablet']);
            unset($settings['istablet']);
        }

        /**
         * Move isdesktop to it's new positions.
         * desktop is now divided in small and large screens.
         *
         * New position = $settings['devices']['smallscreen']['hidden'];
         * New position = $settings['devices']['largescreen']['hidden'];
         */
        if (array_key_exists('isdesktop', $settings)) {
            $hidden = ($settings['isdesktop'] === 'true') ? 'false' : 'true';
            $newSettings['devices']['smallscreen']['hidden'] = $hidden;
            $newSettings['devices']['largescreen']['hidden'] = $hidden;
            $this->add_colspan_if_exist($settings, $newSettings['devices']['smallscreen']);
            $this->add_colspan_if_exist($settings, $newSettings['devices']['largescreen']);
            unset($settings['isdesktop']);
        }

        /**
         * Move css_class to it's new position
         * New position = $settings['cssclass']
         */
        if (array_key_exists('css_class', $settings)) {
            if (is_array($settings['css_class'])) {
                $classes = '';
                foreach ($settings['css_class'] as $class) {
                    $classes .= $class . ' ';
                }

                $newSettings['cssclass'] = $classes;
            } else {
                $newSettings['cssclass'] = $settings['css_class'];
            }
            unset($settings['css_class']);
        }

        // Move type-data into the settings array
        if (array_key_exists('type', $settings)) {
            $newSettings['type'] = $settings['type'];
        } elseif ($settings['name'] === 'Column') {
            $newSettings['type'] = 'col';
        } elseif ($settings['name'] === 'Row') {
            $newSettings['type'] = 'row';
        } else {
            // No type given, use the typeString param
            $newSettings['type'] = strtolower($typeString);
        }

        // Move name into the settings array
        if (array_key_exists('name', $settings)) {
            $newSettings['name'] = $settings['name'];
        }
        // Move name into the settings array
        if (array_key_exists('layout', $settings)) {
            $newSettings['layout'] = $settings['layout'];
        } else {
            $newSettings['layout'] = 'None';
        }

        // Move name into the settings array
        if (array_key_exists('content', $settings)) {
            $newSettings['content'] = htmlspecialchars(base64_encode($settings['content']));
            unset($settings['content']);
        }
        // Move name into the settings array
        if (array_key_exists('board', $settings)) {
            $newSettings['board_id'] = $settings['board'];
            unset($settings['board']);
        }
        //Any keys left, for example for old widgets?
        foreach ($settings as $settingKey => $setting) {
            $newSettings[$settingKey] = $setting;
            unset($settings[$settingKey]);
        }

        // Add new properties
        $newSettings['description'] = '';
        $newSettings['hiddenwrapper'] = 'true';

        return $newSettings;
    }

    /**
     * Use Wordpress global function to fetches the saved board-templates
     * This function will also make sure the saved rows data will be up to date.
     *
     * @use get_option()
     * @return array
     */
    private function cpt_get_saved_rows(): array
    {
        // Fetch the saved rows
        $saved_rows = $this->cpt_get_option("board_saved_rows");

        // Update all rows and its columns data to the new 0.3 version
        foreach ($saved_rows as $row_index => $row) {

            $saved_rows[$row_index] = $this->cpt_update_structure_data_array($row);

            // loop through columns if any
            $columns = $row['cols'] ?? [];
            foreach ($columns as $col_index => $col) {

                $columns[$col_index] = $this->cpt_update_structure_data_array($col);
            }
        }

        return $saved_rows;
    }

    /**
     * Function to handle the fetching the board data
     *
     * @return mixed
     */
    private function cpt_get_saved_board_data()
    {
        $post = $this->cpt_get_post();

        $board_json = [
            'published_data' => $this->cpt_get_board_json(self::BOARD_JSON_FIELD, $post->ID),
            'draft_data' => $this->cpt_get_board_json(self::TEMP_BOARD_JSON_FIELD, $post->ID)
        ];

        // Get unpublished data
        $saved_board = ($this->cpt_json_exists($board_json['draft_data'])) ? $board_json['draft_data'] : '';

        // Show the draft-message if the user is working with an unsaved board
        if ($this->cpt_is_draft($board_json)) {
            # Start HTML
            ?>
            <div class="error draft_message">
                <p><strong><?php echo __('You are working with draft.', 'everyboard'); ?></strong>
                    [<?php echo __('Place link to no draft here', 'everyboard'); ?>]</p>
            </div>
            <?php
            # End HTML
        }

        return $saved_board;
    }

    /**
     * Extracts the settings from a given structure item
     *
     * @param array|Object $structure_item
     *
     * @return string
     */
    private function cpt_get_structure_settings_json($structure_item): string
    {
        if ( ! is_array($structure_item)) {
            $structure_item = (array)$structure_item;
        }

        if (array_key_exists('settings', $structure_item)) {
            return htmlspecialchars(json_encode($structure_item['settings'], JSON_UNESCAPED_UNICODE));
        }

        return '';
    }

    /**
     * Function to check if user is working with an unpublished board
     *
     * @param array $board_json
     *
     * @return bool
     */
    private function cpt_is_draft(array $board_json): bool
    {
        $published = json_decode($board_json['published_data']);
        $draft = json_decode($board_json['draft_data']);

        if ($draft) {

            // Compare time if both data has been set
            if ($published && isset($draft->date, $published->date)) {
                return ($draft->date - $published->date) > 600;
            }

            return true; # True if draft exists
        }

        return false;
    }

    /**
     * Check if a saved board json exists
     *
     * @param $json
     *
     * @return bool
     */
    private function cpt_json_exists($json): bool
    {
        return (isset($json) && ! empty($json));
    }

    /**
     * Convert json to array
     *
     * @param $json
     *
     * @return array
     */
    private function cpt_json_to_array($json): array
    {
        return $this->cpt_json_exists($json) ? json_decode($json, true) : [];
    }

    /**
     * Function to add the default settings to the structure-data
     *
     * @param array $structure
     *
     * @return array
     */
    private function cpt_add_default_structure_settings(array $structure): array
    {
        $structure['settings'] = array_merge($this->default_structure_settings, $structure['settings']);

        return $structure;
    }

    /**
     * Function to create a row with its default values.
     * It receives an optional array of columns
     *
     * @param array $cols
     *
     * @return array
     */
    private function cpt_create_default_row(array $cols = []): array
    {
        $row = [
            'type' => 'row',
            'settings' => ['description' => ''],
            'cols' => $cols
        ];

        return $this->cpt_add_default_structure_settings($row);
    }

    /**
     * Function to create a widget using the data sent in
     *
     * @param array $widget_data
     *
     * @return array
     */
    private function cpt_create_default_widget_data(array $widget_data): array
    {
        $name = ($widget_data['name'] !== '') ? $widget_data['name'] : 'Widget';
        $widget = [
            'type' => 'widget',
            'settings' => [
                'name' => $name,
                'type' => 'widget'
            ]
        ];

        return $this->cpt_add_default_structure_settings($widget);
    }

    /**
     * Function to create an article using values from the OcArticle-object
     *
     * @use OcArticle
     *
     * @param OcArticle $article_obj
     *
     * @return array
     */
    private function cpt_create_default_article_data(OcArticle $article_obj): array
    {
        $name_fallback = $article_obj->contenttype[0] ?? 'Article';
        $name = $article_obj->headline[0] ?? $name_fallback;

        $article = [
            'oc_uuid' => $article_obj->uuid[0],
            'type' => 'oc_article',
            'settings' => [
                'name' => $name,
                'type' => 'oc_article',
                'layout' => 'None'
            ]
        ];

        return $this->cpt_add_default_structure_settings($article);
    }

    /**
     * Function to create a static widget using the array of data sent in
     *
     * @param array $widget_data
     *
     * @return array
     */
    private function cpt_create_default_static_widget_data(array $widget_data = []): array
    {
        $default_widget_data = [
            'type' => 'static_widget',
            'name' => 'Static widget',
            'settings' => []
        ];

        $widget_data = array_merge($default_widget_data, $widget_data);

        $widget = [
            'type' => $widget_data['type'],
            'settings' => [
                'name' => $widget_data['name'],
                'type' => $widget_data['type']
            ]
        ];

        // Add the optional extra settings to the static widget data
        if ( ! empty($widget_data)) {
            $widget['settings'] = array_merge($widget['settings'], $widget_data['settings']);
        }

        return $this->cpt_add_default_structure_settings($widget);
    }

    /**
     * Function to create a column from thw width sent in via the parameter
     *
     * @param int $col_width
     *
     * @return array
     */
    private function cpt_create_default_column($col_width = 100): array
    {
        // Calculate the correct col span of the column according to
        // column-width in percent and the maximum amount of columns allowed in a row
        $col_span = (int)round($this->max_cols * ($col_width / 100));

        // Set Column specific default settings
        $col = [
            'width' => $col_width,
            'type' => 'col',
            'content' => [],
            'settings' => [
                'name' => __('Column', 'everyboard'),
                'type' => 'col',
                'layout' => 'None',
                'description' => '',
                'hiddenwrapper' => 'true'
            ]
        ];

        $col_device_setting = [
            'mobile' => ['colspan' => $this->max_cols],
            'tablet' => ['colspan' => $col_span],
            'smallscreen' => ['colspan' => $col_span],
            'largescreen' => ['colspan' => $col_span]
        ];

        // Merge column-specific settings with the default settings
        $col['settings'] = array_merge($this->default_structure_settings, $col['settings']);

        // Add the column-specific device-settings
        foreach ($col['settings']['devices'] as $device => $settings) {

            $col['settings']['devices'][$device] = array_merge($settings, $col_device_setting[$device]);
        }

        return $col;
    }

    /**
     * Function that returns the template structure_items, for the structure tab, in an array
     *
     * @return array
     */
    private function cpt_get_structure_templates(): array
    {
        $template_arr = [];

        // Add a row with one full-span column
        $template_arr[] = $this->cpt_create_default_row([
            $this->cpt_create_default_column()
        ]);

        // Add a row with two half-span column
        $template_arr[] = $this->cpt_create_default_row([
            $this->cpt_create_default_column(50),
            $this->cpt_create_default_column(50)
        ]);

        // Add a row with three third-span column
        $template_arr[] = $this->cpt_create_default_row([
            $this->cpt_create_default_column(33),
            $this->cpt_create_default_column(33),
            $this->cpt_create_default_column(33)
        ]);

        // Add a row with four fourth-span column
        $template_arr[] = $this->cpt_create_default_row([
            $this->cpt_create_default_column(25),
            $this->cpt_create_default_column(25),
            $this->cpt_create_default_column(25),
            $this->cpt_create_default_column(25)
        ]);

        // Add a row with one 2/3-span and one 1/3-span column
        $template_arr[] = $this->cpt_create_default_row([
            $this->cpt_create_default_column(67),
            $this->cpt_create_default_column(33)
        ]);

        // Add a row with one 2/3-span and one 1/3-span column in reverse
        $template_arr[] = $this->cpt_create_default_row([
            $this->cpt_create_default_column(33),
            $this->cpt_create_default_column(67)
        ]);

        return $template_arr;
    }

    /**
     * Render the options for sorting articles in board sidebar article tad
     *
     * @return void
     */
    private function cpt_render_article_sorting_options(): void
    {
        $options = $this->cpt_get_oc_sort_option_names();

        foreach ($options as $name) {
            /** @todo There is no variable available to compare with $name! */
            $selected = (false) ? ' selected="selected"' : '';

            # Start HTML ?>
            <option value="<?= $name ?>"<?= $selected ?>><?= $name ?></option>
            <?php # End HTML
        }
    }

    /**
     * @return string[]
     */
    private function cpt_get_oc_sort_option_names(): array
    {
        $options = $this->oc_api->get_oc_sort_options();
        if ($options === []) {
            return [];
        }
        $result = [];
        foreach ($options->sortings as $sorting) {
            if ($sorting->contentType === null) {
                $result[] = $sorting->name;
            }
        }

        return $result;
    }

    /**
     * Render the article tab in the board sidebar
     *
     * @return void
     */
    private function cpt_render_article_tab(): void
    {
        $saved_queries = $this->cpt_get_global_queries();

        # Render HTML
        ?>

        <div id="tab_articles" class="tab">
            <div id="oc-search-form">

                <p>
                    <input type="text" id="search_query" name="search_query"
                           placeholder="<?php echo __('Search query', 'everyboard'); ?>"/>
                </p>

                <p>
                    <input type="text" class="input-section" name="section"
                           placeholder="<?php echo __('Section', 'everyboard'); ?>"/>
                </p>

                <p>
                    <input type="text" class="input-date" name="pubdate_start"
                           placeholder="<?php echo __('Pubdate start', 'everyboard'); ?>"/>
                </p>

                <p>
                    <input type="text" class="input-date" name="pubdate_stop"
                           placeholder="<?php echo __('Pubdate end', 'everyboard'); ?>"/>
                </p>

                <p>
                    <label for="oc_search_sort"><?php echo __('Sort by:', 'everyboard'); ?> </label>
                    <select class="sort_select" id="oc_search_sort" name="oc_search_sort">
                        <?php $this->cpt_render_article_sorting_options(); ?>
                    </select><!-- /#oc_search_sort -->
                </p>

                <p>
                    <input name="search_articles" type="button" class="button" id="search_articles"
                           value="<?php echo __('Search', 'everyboard'); ?>">
                </p>

                <div class="saved-search-queries query-list-container list-container list-hidden">

                    <div title="<?php echo __('Click to toggle', 'everyboard'); ?>" class="list-heading list-toggler">
                        <h3><?php echo __('Saved searches:', 'everyboard'); ?><span
                                class="fa fa-chevron-down list-toggler-icon"></span></h3>
                    </div>

                    <ul class="query-list list-body">

                        <?php if ($saved_queries) : ?>
                            <?php foreach ($saved_queries as $key => $query) { ?>
                                <li class="list-item">
                                    <?php $this->cpt_render_saved_article_query($key, $query); ?>
                                </li>
                            <?php } ?>
                        <?php endif; ?>

                    </ul>
                </div> <!-- /.saved-search-queries -->

                <img class="oc_search_ajax" src="<?= $this->assets_url; ?>img/ajax-loader.gif"/>
            </div>

            <div id="search_result">
                <!-- Ajax result here... -->
            </div>

        </div> <!-- /#tab_articles -->

        <?php # End HTML
    }

    /**
     * Render the publish tab in the board sidebar
     *
     * @return void
     */
    private function cpt_render_publish_tab(): void
    {
        # Render HTML
        ?>

        <div id="tab_publish" class="tab">
            <div class="tab-meta-container">
                <h3 id="tab-board-connections"><?php echo __('Make Board Connections:', 'everyboard'); ?></h3>
                <?php $this->meta_connections_sidebar(); ?>
            </div>
            <div class="tab-meta-container">
                <h3 id="tab-board-import"><?php echo __('Import Board:', 'everyboard'); ?></h3>
                <?php $this->meta_import_sidebar(); ?>
            </div>
        </div> <!-- /#tab_publish -->

        <?php # End HTML
    }

    /**
     * Renders a column according to size and layout-settings
     *
     * @param array $col
     */
    private function cpt_render_column(array $col): void
    {
        $col = $this->cpt_update_col_settings($col);

        // Fetch settings ass json-string
        $settings_json = $this->cpt_get_structure_settings_json($col);

        $settings = $col['settings'];
        $width = $col['width'];

        // Set content to empty array if none exists in column
        $content = $col['content'] ?? [];

        $name = $settings['name'];
        $type = $settings['type'];
        $colspan = $settings['devices']['smallscreen']['colspan'];

        $col_width = ((int)$colspan / $this->max_cols) * 100;

        # Render HTML
        ?>

        <div class="col structure_item structure-item"
             style="width: <?= $col_width ?>%;"
             role="<?= $type ?>"
             data-type="<?= $type ?>"
             data-width="<?= $width ?>"
             data-settings="<?= $settings_json ?>">

            <div class="drag_handle structure-drag-handle">
                <span class="structure_item_name structure-item-name"><?= $name ?></span>
                <span class="col-width"><?= $colspan ?></span>
                <span class="col-action structure-action"><?= $this->default_structure_icons ?></span>
            </div>

            <div class="structure-content col_inside">
                <?php foreach ($content as $content_item) {
                    $this->cpt_render_column_content($content_item, $settings);
                } ?>
            </div>
        </div> <!-- /.col -->
        <?php # End HTML
    }

    /**
     * Render the content of a column in the main board area
     *
     * @param array $content
     * @param array $colsettings
     *
     * @return void
     */
    private function cpt_render_column_content(array $content = [], $colsettings = []): void
    {
        if (isset($content['type'])) {
            if ($content['type'] === 'widget') {
                $widget = EveryBoard_WidgetPrefab::get_instance()->get_widget_by_id($content['widget_id']);

                $this->cpt_render_widget($widget, $content);
            }

            if ($content['type'] === 'embed_widget') {
                $this->cpt_render_embed_widget($content);
            }

            if ($content['type'] === 'linked_widget') {
                $this->cpt_render_linked_widget($content);
            }

            if ($content['type'] === 'template_board_widget') {
                $this->cpt_render_template_board_widget($content);
            }

            if ($content['type'] === 'articlelist_widget' && EveryBoard_Global_Settings::is_everylist_active()) {
                $this->cpt_render_articlelist_widget($content);
            }

            if ($content['type'] === 'oclist_widget' && EveryBoard_Global_Settings::is_oclist_active()) {
                $this->cpt_render_oclist_widget($content, $colsettings);
            }

            if ($content['type'] === 'contentcontainer_widget') {
                $this->cpt_render_contentcontainer_widget($content, $colsettings);
            }

            if ($content['type'] === 'oc_article') {
                $article = $this->cpt_oc_get_article($content['oc_uuid']);
                if ($this->env_settings->get_use_trashed() && isset($article->everytrashed) && is_array($article->everytrashed)) {

                    if (in_array(OcUtilities::get_site_shortname(), $article->everytrashed)) {
                        echo $this->cpt_render_removed_article($content, $colsettings);
                    } else {
                        echo $this->cpt_render_article($article, $content, $colsettings);
                    }
                } else {
                    if ($article !== null) {
                        echo $this->cpt_render_article($article, $content, $colsettings);
                    } else {
                        echo $this->cpt_render_removed_article($content, $colsettings);
                    }
                }
            }
        }
    }

    /**
     * Fetches the used post from a saved variable
     *
     * @use WP_Post global wordpress-variable
     * @return null|WP_Post
     */
    private function cpt_get_post(): ?WP_Post
    {
        if (isset($this->post)) {
            return $this->post;
        }
        global $post;

        $this->post = $post;

        return $this->post;
    }

    /**
     * Render the board main area
     *
     * @return void
     */
    private function cpt_render_everyboard_main_area(): void
    {
        $board_array = $this->cpt_json_to_array($this->cpt_get_saved_board_data());

        $board_array = $this->cpt_update_board_properties($board_array);

        $board_json_settings = $this->cpt_get_structure_settings_json($board_array);

        # Render HTML
        ?>

        <div id="everyboard_main_area" class="structure-item" data-settings="<?= $board_json_settings ?>">

            <section class="device-layout">
                <a href="#mobile-layout"
                   data-device="mobile"
                   class="device-layout-icon showmobile"
                   title="<?php echo __('Show board with mobile layout', 'everyboard'); ?>">
                    <i class="fa fa-mobile-phone"></i>
                </a>
                <a href="#tablet-layout"
                   data-device="tablet"
                   class="device-layout-icon showtablet"
                   title="<?php echo __('Show board with tablet layout', 'everyboard'); ?>">
                    <i class="fa fa-tablet"></i>
                </a>
                <a href="#desktop-layout"
                   data-device="smallscreen"
                   class="device-layout-icon active-layout showdesktop"
                   title="<?php echo __('Show board with desktop layout', 'everyboard'); ?>">
                    <i class="fa fa-laptop"></i>
                </a>
                <a href="#bigscreen-layout"
                   data-device="largescreen"
                   class="device-layout-icon showbigscreen"
                   title="<?php echo __('Show board with big screen layout', 'everyboard'); ?>">
                    <i class="fa fa-desktop"></i>
                </a>

                <img src="<?= $this->assets_url ?>img/icons/wheel_alt.png" class="board_wheel settings_item"/>

            </section>

            <?php $this->cpt_render_board($board_array); ?>

        </div> <!-- /#everyboard_main_area -->

        <div class="clear" style="clear: both;"></div>
        <?php # End HTML
    }

    /**
     * Render the board sidebar
     *
     * @return void
     */
    private function cpt_render_everyboard_side_area(): void
    {
        # Render HTML
        ?>

        <div id="everyboard_side_area">

            <div class="tab-meta-container">
                <h3 id="tab-board-save"><?php echo __('Save Board:', 'everyboard'); ?></h3>
                <?php $this->meta_saveboard_sidebar(); ?>
            </div>

            <section id="tabs">
                <ul>
                    <li><a href="#tab_structure"><?php echo __('Structure', 'everyboard'); ?></a></li>
                    <li><a href="#tab_widgets"><?php echo __('Widgets', 'everyboard'); ?></a></li>
                    <li><a href="#tab_articles"><?php echo __('Articles', 'everyboard'); ?></a></li>
                    <li><a href="#tab_publish"><?php echo __('Publish', 'everyboard'); ?></a></li>
                </ul>

                <?php $this->cpt_render_structure_tab(); ?>

                <?php $this->cpt_render_widget_tab(); ?>

                <?php $this->cpt_render_article_tab(); ?>

                <?php $this->cpt_render_publish_tab(); ?>

            </section> <!-- /#tabs -->

        </div><!-- /#everyboard_side_area -->

        <?php # End HTML
    }

    /**
     * Render a Row and it's accompanied columns
     *
     * @param array $row
     */
    private function cpt_render_row(array $row = []): void
    {
        // A set row_id means that this is a saved template
        // An extra class accompanied with a data-attribute is added
        // Start by setting up the class-list for all rows
        $row_classes = 'row structure_item structure-item';
        $row_data_id = '';
        if (isset($row['row_id']) && is_int($row['row_id'])) {
            $row_data_id = 'data-rowid="' . $row['row_id'] . '"';
            $row_classes .= ' saved-row';
        }

        // Extract the Values into variables for easier handling during the rendering of the html.
        $settings_json = $this->cpt_get_structure_settings_json($row);

        $settings = $row['settings'];
        $type = $settings['type'];
        $name = $settings['name'];

        // Set cols to empty array if none exists in row
        $cols = $row['cols'] ?? [];

        # Render HTML
        ?>
        <div class="<?= $row_classes ?>"
             role="<?= $type ?>"
             data-type="<?= $type ?>"
            <?= $row_data_id ?>
             data-settings="<?= $settings_json ?>">

            <div class="drag_handle structure-drag-handle">
                <span class="structure_item_name structure-item-name"><?= $name ?></span>
                <span class="row-action structure-action"><?= $this->default_structure_icons ?></span>
            </div>

            <div class="structure-content">
                <?php # Render accompanied Columns
                foreach ($cols as $col) {
                    $this->cpt_render_column($col);
                } ?>
            </div>
        </div>  <!-- /.row  -->
        <?php # End HTML
    }

    /**
     * Render the "Save board" box in meta sidebar
     *
     * @return void
     */
    private function cpt_render_saveboard_box(): void
    {
        # Render HTML
        ?>
        <input name="save_board" type="button" class="button button-primary button-large" id="publish"
               value="<?php echo __('Publish board', 'everyboard'); ?>">
        <input name="save_board" type="button" class="button button-large" id="save_board"
               value="<?php echo __('Save draft', 'everyboard'); ?>">
        <em><span class="is_draft"></span></em>

        <?php # End HTML
    }

    /**
     *
     * Render a saved query for fetching articles in board sidebar article tab
     *
     * @param int   $key
     * @param array $query
     *
     * @return void
     */
    private function cpt_render_saved_article_query($key, array $query): void
    {
        # Start HTML
        ?>
        <span class="saved_search_span">
                <a href="#"
                   data-query="<?= stripslashes($query['query']) ?>"
                   data-sorting="<?= $query['sorting'] ?>"
                   class="prefab_search"><?= $query['name'] ?>
                </a>
            <span data-id="<?= $key ?>" class="prefab_remove">&times;</span>
            </span>

        <?php # End HTML
    }

    /**
     * Render the structure tab in the board sidebar
     *
     * @return void
     */
    private function cpt_render_structure_tab(): void
    {
        $default_row = $this->cpt_create_default_row();
        $default_column = $this->cpt_create_default_column();
        $structure_templates = $this->cpt_get_structure_templates();

        $saved_rows = $this->cpt_get_saved_rows();

        # Render HTML
        ?>

        <div id="tab_structure" class="tab">

            <h3><?php echo __('Structure Items:', 'everyboard'); ?></h3>

            <?php $this->cpt_render_row($default_row); ?>

            <div class="column-wrapper">
                <?php $this->cpt_render_column($default_column); ?>
            </div>

            <hr/>

            <div class="list-container list-hidden">
                <div class="list-heading list-toggler" title="<?php echo __('Click to toggle', 'everyboard'); ?>">
                    <h3>
                        <?php echo __('Templates', 'everyboard'); ?><span
                            class="fa fa-chevron-down list-toggler-icon"></span>
                    </h3>
                </div>
                <ul class="list-body">
                    <?php foreach ($structure_templates as $row) : ?>
                        <li class="list-item"><?php $this->cpt_render_row($row); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="list-container list-hidden">
                <div class="list-heading list-toggler" title="<?php echo __('Click to toggle', 'everyboard'); ?>">
                    <h3>
                        <?php echo __('Saved Templates', 'everyboard'); ?><span
                            class="fa fa-chevron-down list-toggler-icon"></span>
                    </h3>
                </div>
                <ul id="saved-templates-list" class="list-body">
                    <?php if ($saved_rows) : ?>

                        <?php foreach ($saved_rows as $key => $row) : $row['row_id'] = $key; ?>
                            <li class="list-item"><?php $this->cpt_render_row($row); ?></li>
                        <?php endforeach; ?>

                    <?php endif; ?>
                </ul>
            </div>

        </div> <!-- /#tab_structure -->

        <?php # End of HTML
    }

    /**
     * Render the settings for structure visibility in meta sidebar
     *
     * @return void
     */
    private function cpt_render_visibility_settings(): void
    {
        # Start HTML
        ?>

        <section class="visibility-settings">
            <h3><?php echo __('Visibility', 'everyboard'); ?></h3>
            <ul>
                <li>
                    <input
                        type="checkbox"
                        id="mobile-visibility"
                        name="device-visibility"
                        class="device-visibility-box">
                    <label for="mobile-visibility"><?php echo __('On mobile', 'everyboard'); ?></label>
                </li>
                <li>
                    <input
                        type="checkbox"
                        id="tablet-visibility"
                        name="device-visibility"
                        class="device-visibility-box">
                    <label for="tablet-visibility"><?php echo __('On tablet', 'everyboard'); ?></label>
                </li>
                <li>
                    <input
                        type="checkbox"
                        id="desktop-visibility"
                        name="device-visibility"
                        class="device-visibility-box">
                    <label for="desktop-visibility"><?php echo __('On desktop', 'everyboard'); ?></label>
                </li>
            </ul>
        </section> <!-- /.visibility-settings -->

        <?php # End HTML
    }

    /**
     * Render the widget tab in the board sidebar
     *
     * @return void
     */
    private function cpt_render_widget_tab(): void
    {
        # Render HTML
        ?>

        <div id="tab_widgets" class="tab">
            <div class="widget-filter-container">
                <input type="search" id="widget_filter"
                       placeholder="<?php echo __('Filter widgets...', 'everyboard'); ?>"/>
                <span title="Display all widgets" class="fa fa-chevron-down list-toggle-all-icon"></span>
            </div>
            <?php $this->cpt_render_sidebar_widgets(); ?>
        </div> <!-- /#tab_widget -->

        <?php # End of HTML
    }

    /**
     * Use Wordpress global function to updates the specified meta post
     *
     * @use update_post_meta()
     *
     * @param int    $post_id
     * @param string $key
     * @param mixed  $value
     *
     * @return bool
     */
    public function cpt_update_board_data($post_id, $key, $value): bool
    {
        //NOTE: This function returns false if the meta_value passed is the same as the value in the database.
        $updated = update_post_meta($post_id, $key, $value);

        // update_post_meta returns meta_id if the meta doesn't exist
        // otherwise returns true on success and false on failure.
        if ( ! is_bool($updated)) {
            return true;
        }

        return $updated;
    }

    /**
     * Function to update a columns settings
     * Is use before the column is rendered
     *
     * @param array $col
     *
     * @return array
     */
    private function cpt_update_col_settings(array $col): array
    {
        $settings = $col['settings'] ?? [];

        /**
         * Add the hiddenwrapper setting if it does not exist
         */
        if ( ! array_key_exists('hiddenwrapper', $settings)) {
            $settings['hiddenwrapper'] = 'true';
        }

        if ( ! array_key_exists('name', $settings)) {
            $settings['name'] = 'Column';
        }

        if ( ! array_key_exists('type', $settings)) {
            $settings['type'] = 'col';
        }

        if ( ! array_key_exists('cssclass', $settings)) {
            $settings['cssclass'] = '';
        }

        if ( ! array_key_exists('devices', $settings)) {
            $settings['devices'] = $this->getDefaultStructureSettings()['devices'];
        }

        if ( ! array_key_exists('layout', $settings)) {
            $settings['layout'] = 'None';
        }

        $col['settings'] = $settings;

        return $col;
    }

    /**
     * Function to update the global queries
     *
     * @param mixed $saved_queries
     *
     * @return bool
     */
    private function cpt_update_global_queries($saved_queries = ''): bool
    {
        return $this->cpt_update_option('board_global_queries', $saved_queries);
    }

    /**
     * Use Wordpress global function to update the specified option
     *
     * @use update_option()
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return bool
     */
    private function cpt_update_option($key, $value = ''): bool
    {
        return update_option($key, $value);
    }

    /**
     * Function to update the saved board-templates
     *
     * @param mixed $saved_rows
     *
     * @return bool
     */
    private function cpt_update_saved_rows($saved_rows = ''): bool
    {
        return $this->cpt_update_option('board_saved_rows', $saved_rows);
    }

    private function get_template_tag(array $settings, string $parent_template = 'None'): string
    {
        $template = $settings['layout'] ?? 'None';
        $inherited = false;

        if ($template === 'None' && $parent_template !== 'None') {
            $template = $parent_template;
            $inherited = true;
        }

        $templateName = 'None';

        if ($template !== 'None') {
            $templateName = array_search($template, EveryBoard_Settings::get_templates(), true);
        }

        return sprintf(
            '<p class="article_template_name"><em>%s</em>&nbsp;<strong>%s</strong>%s</p>',
            __('Template:', 'everyboard'),
            $templateName,
            $inherited ? '&nbsp;<em>(' . __('Inherited', 'everyboard') . ')</em>' : ''
        );
    }

    /**
     * @param array $settings
     *
     * @return array
     * @throws NotSelectedException
     * @throws BoardNotFoundException
     */
    private function get_valid_linked_board_settings(array $settings): array
    {
        $sharedBoardId = (int)($settings['network_board_id'] ?? 0);
        $boardId = (int)($settings['board_id'] ?? 0);
        $siteId = null;

        if ($boardId === 0) {
            $boardId = $sharedBoardId;
        }

        if ($sharedBoardId !== 0) {
            $siteId = $settings['network_board_site_id'] ?? null;
        }

        if ($boardId === 0) {
            throw new NotSelectedException(__('No board selected', 'everyboard'));
        }

        try {
            $link_components = $this->get_board_link_components($boardId, $siteId);
        } catch (BoardNotFoundException $e) {
            throw new BoardNotFoundException(__('Board selected cannot be found', 'everyboard'));
        }

        $num_positions = $this->get_highest_position_in_board($boardId, $siteId);

        return array_replace([
            'board_id' => $boardId,
            'site_id' => $siteId,
            'presentation_link' => $this->get_board_link($link_components),
            'positions' => $num_positions
        ], $link_components);
    }

    private function cpt_get_contentcontainer_meta_content_html($settings, array $colsettings): array
    {
        $response = [
            'headline' => $settings['name'] ?? __('Content container', 'everyboard'),
            'content' => $this->get_template_tag($settings, $colsettings['layout'] ?? 'None')
        ];

        $position = (int)$settings['position'];
        $limit = isset($settings['limit']) ? (int)$settings['limit'] : 1;

        $startTag = '<strong class="pos">' . $position . '</strong>';
        $endTag = '<strong class="end">' . (($position + $limit) - 1) . '</strong>';

        $positionInfo = sprintf('<p>Displaying positions %s to %s</p>',
            $startTag,
            $endTag
        );

        $response['content'] .= '<div class="meta_info">' . $positionInfo . '</div>';

        return $response;
    }
}

// Widget sort function
function sort_widget($a, $b)
{
    return strcmp($a['orgin_name'], $b['orgin_name']) + strcmp($a['name'], $b['name']);
}
