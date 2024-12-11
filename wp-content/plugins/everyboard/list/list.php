<?php

require_once __DIR__ . '/list-cache.php';
require_once __DIR__ . '/list-preview.php';

class EveryList
{
    private $oc_api;

    public static $OPTIONS_NAME = 'EVERYLIST_OPTIONS';

    private static $SLUG = 'boardlist';

    private static $SLUG_SETTINGS = 'boardlist_settings';

    private $options;

    private $OC_HEALTH_CACHE = 'oc_health_cache';

    private $list_cache;

    private static $published_network_lists = null;

    private static $approved_list_cache = [];

    public function __construct()
    {
        if (isset($_POST['list_settings_form_posted'])) {
            $this->save_list_settings();
        }

        $this->oc_api = new OcAPI();
        $this->list_cache = new ListCache();
        $this->options = get_option(self::$OPTIONS_NAME);

        add_action('admin_init', [&$this, 'admin_init']);
        add_action('admin_menu', [&$this, 'admin_menu']);
        add_action('admin_enqueue_scripts', [&$this, 'admin_style']);

        add_action('wp_ajax_create_board_list', [&$this, 'create_board_list']);
        add_action('wp_ajax_save_board_list', [&$this, 'save_board_list']);
        add_action('wp_ajax_get_board_list', [&$this, 'get_board_list']);
        add_action('wp_ajax_get_board_list_preview', [&$this, 'get_board_list_preview']);
        add_action('wp_ajax_remove_board_list', [&$this, 'remove_board_list']);
        add_action('wp_ajax_check_oc_health', [&$this, 'check_oc_health']);
        add_action('wp_ajax_fetch_articles', [&$this, 'fetch_articles']);
        add_action('wp_ajax_list_get_article', [&$this, 'list_get_article']);

        add_filter('ew_apply_required_search_properties', function ($properties) {
            return array_unique(array_merge($properties, $this->get_required_properties()));
        });
    }

    /**
     * Fetch the properties required from Open Content for EveryList to function properly
     *
     * @return array
     * @since 0.1
     */
    private function get_required_properties()
    {
        $properties = ['Pubdate'];
        if ($this->options !== false) {
            $options = json_decode($this->options, true);
            $properties[] = $options['property'] ?: '';
        }

        return $properties;
    }

    /**
     * Fires on add_action( 'admin_init' )
     *
     * @return void
     */
    public function admin_init()
    {
        if (null === get_role('list_user')) {
            add_role('list_user', __('List User', 'everyboard'), [
                'use_list' => true,
                'publish_posts' => true
            ]);
        } else {
            $list_role = get_role('list_user');
            $capabilities = $list_role->capabilities;

            if ( ! isset($capabilities['use_list'])) {
                $list_role->add_cap('use_list');
            }

            if ( ! isset($capabilities['publish_posts'])) {
                $list_role->add_cap('publish_posts');
            }
        }

        if (($admin = get_role('administrator')) !== null) {
            $admin->add_cap('use_list');
        }

        if (static::on_boardlist_page() && ! current_user_can('use_list')) {
            wp_die(__('You do not have access to this page', 'everyboard'));
        }
    }

    public function admin_menu()
    {
        add_submenu_page('edit.php?post_type=everyboard', 'List', __('List', 'everyboard'), 'publish_posts',
            self::$SLUG, [
                &$this,
                'admin_page'
            ]);

        add_submenu_page('edit.php?post_type=everyboard', 'List Settings', __('List Settings', 'everyboard'),
            'manage_options', self::$SLUG_SETTINGS, [
                &$this,
                'settings_page'
            ]);
    }

    public function admin_style()
    {
        global $pagenow;

        $page = '';
        if (isset($_GET['page']) && ($_GET['page'] === 'boardlist' || $_GET['page'] === 'boardlist_settings')) {
            $page = $_GET['page'];
        }

        if ($pagenow === 'edit.php' && ! empty($page) && is_admin()) {
            wp_enqueue_script('jquery-ui-dialog');
            wp_enqueue_style('wp-jquery-ui-dialog');
            wp_enqueue_script(
                'everylist_script',
                EVERYBOARD_BASE . 'assets/dist/js/boardlist.min.ugly.js',
                ['jquery'],
                EVERYBOARD_VERSION,
                true
            );
            wp_enqueue_style(
                'everylist_style',
                EVERYBOARD_BASE . 'assets/dist/css/boardlist.min.css',
                [],
                EVERYBOARD_VERSION
            );

            wp_localize_script('everylist_script', 'everylist_translate', [
                'modal_active_list_message' => __('Doing this will cause all changes not saved to be lost, are you sure you want to proceed?'),
                'modal_proceed' => __('Proceed', 'everyboard'),
                'modal_cancel' => __('Cancel', 'everyboard'),
                'validate_no_list_name' => __('Please fill in a list name', 'everyboard'),
                'validate_duplicated_list_name' => __('The current list name already exists, please choose another one',
                    'everyboard')
            ]);

            $iframe_buttons = apply_filters('everylist_iframe_buttons', [
                [
                    'title' => 'Preview',
                    'src' => get_site_url() . '?listId=_LISTID_&list_preview=true'
                ]
            ]);

            wp_localize_script('everylist_script', 'eb_settings', [
                'image_base' => OpenContent::getInstance()->getImageUrl(),
                'image_service' => OpenContent::getInstance()->getImageService(),
                'list_settings' => $this->options !== false ? $this->options : json_encode([
                    'property' => '',
                    'value' => '',
                    'number_of_items_to_save'
                ]),
                'list_url_base' => plugin_dir_url(__FILE__),
                'iframe_buttons' => $iframe_buttons
            ]);
        }
    }

    /**
     * AJAX
     */
    public function list_get_article()
    {
        $uuid = $_POST['uuid'];

        $oc_api = new OcAPI();
        $result = $oc_api->search([
            'q' => 'uuid:' . $uuid,
            'contenttypes' => ['Article'],
            'start' => 0,
            'limit' => 1
        ], false);

        if ( ! empty($result) && isset($result[0])) {
            wp_send_json($result[0]->get_all_properties());
        }

        wp_send_json(null);
    }

    /**
     * AJAX
     */
    public function create_board_list()
    {
        $list_name = isset($_POST['listname']) ? $_POST['listname'] : '';

        $args = [
            'post_type' => 'boardlist',
            'post_title' => $list_name,
            'post_name' => '',
            'ping_status' => 'closed',
            'post_status' => 'publish',
            'comment_status' => 'closed'
        ];

        $id = wp_insert_post($args);

        $error = null;
        if ($id === 0) {
            //failure
            wp_send_json(['status' => 0, 'error' => __('Could not create board list', 'everyboard')]);
        } else {
            $timestamp = time();
            $success = add_post_meta($id, 'list_data', json_encode([
                'listname' => $list_name,
                'timestamp' => $timestamp,
                'articles' => []
            ], JSON_UNESCAPED_UNICODE));
            if ($success) {
                wp_send_json([
                    'status' => 200,
                    'list' => ['id' => $id, 'name' => $list_name, 'timestamp' => $timestamp]
                ]);
            } else {
                wp_delete_post($id);
                wp_send_json(['status' => 0, 'error' => __('Could not create board list', 'everyboard')]);
            }
        }
        die(1);
    }

    public function get_list_articles_by_id($id)
    {
        $data = $this->get_list_data($id);

        return $this->prefetch_list_articles($data);
    }

    /**
     * @param $id
     *
     * @return mixed
     * Helper function to fetch cached list data
     */
    private function get_list_data($id)
    {
        if (false === ($data = $this->list_cache->get_list_cache($id))) {
            $data = get_post_meta($id, 'list_data', true);
            $this->list_cache->save_list_cache($id, $data);
        }

        return $data;
    }

    private function prefetch_list_articles($data = '')
    {
        $articles = [];

        $decoded_data = json_decode($data, true);

        if (isset($decoded_data['articles'])) {

            $uuids = self::extract_uuid_from_data($decoded_data);

            if (null !== EveryBoard_Article_Helper::prefetch_board_list_articles($uuids)) {
                foreach ($uuids as $uuid) {
                    $article = EveryBoard_Article_Helper::get_oc_article($uuid, false);
                    $articles[] = $article;
                }
            }
        }

        return $articles;
    }

    /**
     * @param array $data
     *
     * @return array
     * Function to help extract the uuids out of the decoded data sent in.
     * Checks whether the data has the old structure or the new.
     */
    private static function extract_uuid_from_data(array $data = [])
    {
        $uuids = [];

        #Check whether we're using the old list data structure or not
        if (count(array_column((array)$data['articles'], 'pubdate')) > 0) {
            foreach ((array)$data['articles'] as $uuid => $pubdate) {
                $uuids[] = $uuid;
            }
        } else {
            $uuids = array_values($data['articles']);
        }

        return $uuids;
    }

    private function map_list_article_data($list_data)
    {
        $mapped_articles = [];

        $decoded_data = json_decode($list_data, true);
        $uuids = self::extract_uuid_from_data($decoded_data);

        foreach ($uuids as $uuid) {
            $article = EveryBoard_Article_Helper::get_oc_article($uuid, false);
            $mapped_articles[$uuid] = null !== $article ? $article->get_all_properties() : ['uuid' => [$uuid]];
        }

        return $mapped_articles;
    }

    /**
     * AJAX
     */
    public function get_board_list_preview()
    {
        $id = isset($_POST['listId']) ? $_POST['listId'] : null;

        if (false === ($data = $this->list_cache->get_list_cache($id))) {
            $data = get_post_meta($id, 'list_data', true);
            $this->list_cache->save_list_cache($id, $data);
        }

        if ( ! empty($data)) {
            $decoded_data = json_decode($data, true);
            $uuids = self::extract_uuid_from_data($decoded_data);

            if (null !== EveryBoard_Article_Helper::prefetch_board_list_articles($uuids)) {
                $articles = [];

                $uuids = self::extract_uuid_from_data($decoded_data);

                foreach ($uuids as $uuid) {
                    $article = EveryBoard_Article_Helper::get_oc_article($uuid);
                    $articles[$uuid] = null !== $article ? $article->get_all_properties() : ['uuid' => [$uuid]];
                }

                $article_html = '';
                foreach ($articles as $json_article) {

                    $oc_api = new \OcAPI();
                    $article = new \OcArticle();

                    // Create WP article.
                    foreach ((array)$json_article as $key => $value) {
                        $name = strtolower($key);
                        $val = $value;
                        $article->$name = $val;
                    }

                    $oc_api->add_mapped_properties($article);
                    new \OcArticleCustomPostFactory($article);

                    $template = isset($_POST['template']) ? $_POST['template'] : 'None';

                    // Get HTML for list preview and return it via ajax.
                    $article_html .= '<div class="rendered_board_article">';
                    if (isset($template) && $template !== "None" && file_exists(EveryBoard_Settings::get_template_path($template))) {
                        ob_start();
                        include EveryBoard_Settings::get_template_path($template);
                        $article_html .= ob_get_contents();
                        ob_end_clean();
                    } else {
                        if (file_exists(EveryBoard_Settings::get_template_path("templates/ocarticle-default.php"))) {
                            ob_start();
                            include EveryBoard_Settings::get_template_path("templates/ocarticle-default.php");
                            $article_html .= ob_get_contents();
                            ob_end_clean();
                        }
                    }

                    $article_html .= '</div>';
                }

                wp_send_json(['status' => 200, 'data' => $data, 'article_html' => $article_html]);
            } else {
                //we've got problem fetching articles from OC
                wp_send_json(['status' => 400, 'data' => $data]);
            }

        } else {
            wp_send_json(['status' => 0, 'error' => __('Could not fetch board list', 'everyboard')]);
        }
        die(1);
    }

    /**
     * AJAX
     */
    public function get_board_list()
    {
        $id = isset($_POST['listId']) ? $_POST['listId'] : null;

        $data = $this->get_list_data($id);

        if (empty($data)) {
            wp_send_json(['status' => 0, 'error' => __('Could not fetch board list', 'everyboard')]);
        }

        $decoded_data = json_decode($data, true);

        if (isset($decoded_data['articles']) && empty($decoded_data['articles'])) {
            wp_send_json(['status' => 200, 'data' => $data, $articles = []]);
        }

        $articles = $this->prefetch_list_articles($data);

        if (empty($articles)) {
            //we've got problem fetching articles from OC
            wp_send_json(['status' => 400, 'data' => $data]);
        } else {
            $mapped_articles = $this->map_list_article_data($data);
            wp_send_json(['status' => 200, 'data' => $data, 'articles' => $mapped_articles]);
        }
    }

    /**
     * AJAX
     */
    public function fetch_articles()
    {
        $uuids = isset($_POST['uuids']) ? $_POST['uuids'] : [];

        if ( ! empty($uuids)) {
            if (null !== EveryBoard_Article_Helper::prefetch_board_list_articles($uuids)) {
                $articles = [];

                foreach ($uuids as $uuid) {
                    $article = EveryBoard_Article_Helper::get_oc_article($uuid);

                    if (null !== $article) {
                        $articles[$uuid] = $article->get_all_properties();
                    }
                }

                wp_send_json(['status' => 200, 'articles' => $articles]);
            }
        }

        wp_send_json(['status' => 400]);
    }

    /**
     * AJAX
     */
    public function save_board_list()
    {
        $id = isset($_POST['listId']) ? $_POST['listId'] : null;
        $listtitle = isset($_POST['listTitle']) ? $_POST['listTitle'] : null;
        $listdata = isset($_POST['listData']) ? stripslashes($_POST['listData']) : null;
        $shared = isset($_POST['shared']) ? $_POST['shared'] : null;
        $timestamp = isset($_POST['timestamp']) ? (int)$_POST['timestamp'] : null;

        $should_update = false;
        $old_list = false;

//        if( null !== $id && null !== $listtitle && null !== $listdata && null !== $shared && null !== $timestamp ) {
        if (isset($id, $listtitle, $listdata, $shared, $timestamp)) {
            $post = get_post($id);
            $post->post_title = $listtitle;
            wp_update_post($post);

            $post_meta = get_post_meta($id, 'list_data', true);
            $json = json_decode($post_meta, true, JSON_UNESCAPED_UNICODE);
            $lastsavetime = isset($json['timestamp']) ? $json['timestamp'] : 0;

            if ($lastsavetime <= (int)$timestamp) {
                $should_update = true;

                $listdata = json_decode($listdata, true, JSON_UNESCAPED_UNICODE);
                $listdata['timestamp'] = time();
                $listdata = json_encode($listdata, JSON_UNESCAPED_UNICODE);
            } else {
                $old_list = true;
            }
        }

        if ($should_update) {
            if (empty($listdata)) {
                wp_send_json(['status' => 0, 'error' => __('List data is empty', 'everyboard')]);
            } else {
                $success = update_post_meta($id, 'list_data', $listdata);
                update_post_meta($id, 'list_share', $shared);

                if ($success === true) {
                    $this->list_cache->save_list_cache($id, $listdata);
                    wp_send_json([
                        'status' => 200,
                        'list' => ['id' => $id, 'name' => $listtitle, 'data' => $listdata]
                    ]);
                } else {
                    wp_send_json([
                        'status' => 0,
                        'error' => __('Could not save list', 'everyboard'),
                        'id' => $id,
                        'listdata' => $listdata
                    ]);
                }
            }
        } else {
            if ($old_list) {
                wp_send_json([
                    'status' => 1,
                    'error' => __('There is a newer version of this list available, please reload to get the latest changes.',
                        'everyboard')
                ]);
            } else {
                $list_data_validation = null === $listdata ? 'Complete' : 'Incomplete';

                wp_send_json([
                    'status' => 0,
                    'error' => __("An error has occurred while saving the list.
                    \nID: {$id}
                    \nTitel: {$listtitle}
                    \nShared: {$shared}
                    \nListdata: {$list_data_validation}
                    \nTimestamp: {$timestamp}", 'everyboard')
                ]);
            }

        }
        die(1);
    }

    /**
     * AJAX
     */
    public function remove_board_list()
    {
        $id = isset($_POST['listId']) ? $_POST['listId'] : null;

        if (null !== $id) {
            $post_success = false;
            $post_meta_success = delete_post_meta($id, 'list_data');
            $this->list_cache->delete_list_cache($id);

            $post = get_post($id);

            if (null !== $post && $post->post_type === 'boardlist') {
                $post_success = wp_delete_post($id);
            }

            if ($post_meta_success && $post_success) {
                wp_send_json(['status' => 200]);
            } else {
                wp_send_json([
                    'status' => 0,
                    'error' => __('Something went wrong while trying to remove the list', 'everyboard')
                ]);
            }
        }

        die(1);
    }

    /**
     * AJAX
     */
    public function check_oc_health()
    {
        if (false === ($status = get_site_transient($this->OC_HEALTH_CACHE))) {
            $response = $this->oc_api->get_oc_health();
            $status = $response['http_code'];

            set_site_transient($this->OC_HEALTH_CACHE, $status, 30);
        }

        wp_send_json(['status' => (int)$status]);
        die(1);
    }

    public function admin_page()
    {
        $boardlist = self::get_all_lists();
        $lists = [];

        foreach ($boardlist as $key => $list) {
            if (false === ($data = $this->list_cache->get_list_cache($key))) {
                $data = get_post_meta($key, 'list_data', true);
                $this->list_cache->save_list_cache($key, $data);
            }

            $lists[$key] = json_decode($data);
        }

        include __DIR__ . '/views/admin.php';
    }

    public function settings_page()
    {
        $oc_api = new OcAPI();
        $metafield = $oc_api->get_content_types()['Article'];
        $options = $this->options !== false ? json_decode($this->options, true) : [
            'property' => '',
            'value' => '',
            'number_of_items_to_save' => ''
        ];

        include __DIR__ . '/views/settings.php';
    }

    public function save_list_settings()
    {
        $property = $_POST['select_publish_property'];
        $value = $_POST['publish_property'];
        $number_of_items_to_save = $_POST['number_of_items_to_save'];

        $settings = [
            'property' => $property,
            'value' => $value,
            'number_of_items_to_save' => $number_of_items_to_save
        ];
        update_option(self::$OPTIONS_NAME, json_encode($settings));
    }

    public static function get_all_lists()
    {
        $args = ['numberposts' => -1, 'post_type' => 'boardlist'];

        $posts = get_posts($args);

        $lists = [];
        foreach ($posts as $list) {
            $lists[$list->ID] = $list->post_title;
        }

        return $lists;
    }

    public static function get_networked_lists()
    {
        if ( ! is_multisite()) {
            return [];
        }
        if (null !== self::$published_network_lists) {
            return self::$published_network_lists;
        }

        $ret_lists = [];
        $original_blog_id = get_current_blog_id();
        $sites = get_sites();

        foreach ($sites as $site) {

            if (isset($site->blog_id)) {

                $site_id = (int)$site->blog_id;
                if ($original_blog_id !== $site_id) {
                    switch_to_blog($site_id);

                    $args = [
                        'post_type' => 'boardlist',
                        'posts_per_page' => -1,
                        'orderby' => 'title',
                        'order' => 'ASC',
                        'meta_key' => 'list_share',
                        'meta_value' => true
                    ];

                    $posts = get_posts($args);

                    $site_lists = ['name' => get_bloginfo('name'), 'siteId' => $site_id, 'lists' => []];

                    foreach ($posts as $post) {
                        $site_lists['lists'][] = ['name' => $post->post_title, 'listId' => $post->ID];
                    }

                    if (count($site_lists['lists']) > 0) {
                        $ret_lists[] = $site_lists;
                    }

                    restore_current_blog();
                }
            }
        }

        self::$published_network_lists = $ret_lists;

        return $ret_lists;
    }

    /**
     * Helper function to get cached approved list data
     *
     * @param $listId
     *
     * @return array
     */
    private static function get_approved_cache($listId)
    {
        $blog_id = get_current_blog_id();
        $approved_articles = [];

        if (isset(self::$approved_list_cache[$blog_id][$listId])) {
            $approved_articles = self::$approved_list_cache[$blog_id][$listId];
        }

        return $approved_articles;
    }

    /**
     * Helper function to set approved list data to cache so that its only
     * fetched once per request
     *
     * @param $listId
     * @param $data
     */
    private static function set_approved_cache($listId, $data)
    {
        $blog_id = get_current_blog_id();

        if ( ! isset(self::$approved_list_cache[$blog_id])) {
            self::$approved_list_cache[$blog_id] = [];
        }

        self::$approved_list_cache[$blog_id][$listId] = $data;
    }

    /**
     * Function for fetching an article from a specific position from a specific list
     *
     * @param     $listId
     * @param int $positionId
     *
     * @return null
     */
    public static function get_article_from_list($listId, $positionId = 0)
    {
        if ($positionId === 0) {
            return null;
        }

        $cache = self::get_approved_cache($listId);

        if ( ! empty($cache)) {
            $available_articles = $cache;
        } else {
            $available_articles = self::get_approved_list_articles($listId);
            self::set_approved_cache($listId, $available_articles);
        }

        $ret = null;
        if (isset($available_articles[$positionId - 1])) {
            $ret = $available_articles[$positionId - 1];
        }

        return $ret;
    }

    /**
     * Function for fetching one or more articles from a list, begins fetcing at $positionId
     *
     * @param     $listId
     * @param int $positionId
     * @param int $limit
     *
     * @return null|array
     */
    public static function get_articles_from_list($listId, $positionId = 0, $limit = 1)
    {
        if ($positionId === 0) {
            return null;
        }

        $cache = self::get_approved_cache($listId);

        if ( ! empty($cache)) {
            $available_articles = $cache;
        } else {
            $available_articles = self::get_approved_list_articles($listId);
            self::set_approved_cache($listId, $available_articles);
        }

        $ret = [];
        if ($limit > 1) {

            for ($i = 0; $i < $limit; $i++) {
                if (isset($available_articles[($positionId - 1) + $i])) {
                    $ret[] = $available_articles[($positionId - 1) + $i];
                }
            }
        } else {
            if (isset($available_articles[$positionId - 1])) {
                $ret[] = $available_articles[$positionId - 1];
            }
        }

        return count($ret) > 0 ? $ret : null;
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
        $article_list_json = get_post_meta($listId, 'list_data', true);

        if (empty($article_list_json)) {
            return null;
        }
        $decoded = json_decode($article_list_json, true);
        $list_options = get_option(self::$OPTIONS_NAME);
        $list_options_decoded = $list_options !== false ? json_decode($list_options, true) : [
            'property' => 'Status',
            'value' => '',
            'number_of_items_to_save' => '20'
        ];
        $s_prop = isset($list_options_decoded['property']) ? strtolower($list_options_decoded['property']) : 'Status';

        $available_articles = [];

        $options = get_option(self::$OPTIONS_NAME);
        $published_indicator = $options !== false ? strtolower(json_decode($options, true)['value']) : 'publicerad';

        $article_uuids = self::extract_uuid_from_data($decoded);

        //Prefetch the articles so we can iterate over fresh data
        EveryBoard_Article_Helper::prefetch_board_list_articles($article_uuids);

        foreach ($article_uuids as $uuid) {
            $now = time();
            $article = EveryBoard_Article_Helper::get_oc_article($uuid, false);

            if (null !== $article) {
                $article_s_prop = isset($article->$s_prop) ? $article->$s_prop : null;
                $s_prop_value = null !== $article_s_prop ? array_shift($article_s_prop) : '';

                #saftey check to determine if we have status and pubdate fields
                if (isset($s_prop, $article->pubdate[0])) {

                    if (strtolower($s_prop_value) === $published_indicator && strtotime($article->pubdate[0]) <= $now) {
                        $available_articles[] = $article;
                    }
                }
            }
        }

        return $available_articles;
    }

    public static function get_list_name_by_id($listId)
    {
        $list_cache = new ListCache();
        if (false === ($article_list_json = $list_cache->get_list_cache($listId))) {
            $article_list_json = get_post_meta($listId, 'list_data', true);
        }

        if ( ! empty($article_list_json)) {
            $list_data = json_decode($article_list_json, true);

            return isset($list_data['listname']) ? $list_data['listname'] : '( Cant find list name )';
        }
    }

    /**
     * Determine if we are on the boardlist page
     *
     * @return bool
     * @since 0.1
     */
    public static function on_boardlist_page()
    {
        global $pagenow, $typenow;

        return $pagenow === 'edit.php' && $typenow === 'everyboard' && (isset($_GET['page']) && $_GET['page'] === 'boardlist');
    }
}

add_action('init', function () {
    new EveryList();
});
