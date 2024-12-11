<?php

/**
 * Class EveryBoard_Page
 * TODO: Refactor and move into namespace
 */
class EveryBoard_Page
{
    /**
     * Initialize EveryBoard_Page class
     */
    public function __construct()
    {
        add_action('add_meta_boxes', [&$this, 'board_page_add_metaboxes']); // Adds custom meta boxes to edit page.
        add_action('save_post', [&$this, 'board_page_update_page']); // On page update execute action.
        add_filter('the_content',
            [&$this, 'board_page_content_override']); // Filter for overriding the_content if a board is chosen.

        if (isset($_GET['board_preview']) && $_GET["board_preview"] === 'true') {
            add_action('wp_enqueue_scripts', [&$this, 'eb_page_enqueue']);
            add_filter('show_admin_bar', '__return_false');
            add_action('template_redirect', [&$this, 'cache_page']);
        }
    }

    /**
     * @return void
     * @since 1.5.13
     */
    public function eb_page_enqueue()
    {
        wp_enqueue_script(
            'everyboard',
            EVERYBOARD_BASE . 'assets/dist/js/boardpreview.min.ugly.js',
            ['jquery'],
            EVERYBOARD_VERSION
        );

        $preview_id = isset($_GET['preview_id']) ? $_GET['preview_id'] : '';

        wp_localize_script('everyboard', 'board', [
            'json' => EVERYBOARD_BASE . 'preview.php?preview=true',
            'json_data' => EVERYBOARD_BASE . 'preview.php?preview_json=true',
            'board_check' => EVERYBOARD_BASE . 'preview.php?get_board=true&id=' . $preview_id,
            'page_id' => $preview_id,
            'board_id' => $this->get_board_id($preview_id)
        ]);
    }

    /**
     * @return void
     */
    public function cache_page()
    {
        ob_start([&$this, 'ob_is_cached']);
    }

    /**
     * @param string $content
     *
     * @return string
     */
    public function ob_is_cached($content)
    {
        //TODO: Check if user has permission to view this
        if ( ! is_user_logged_in()) {
            wp_redirect(wp_login_url('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']));
            die();
        }

        if (get_option('preview_data') !== null) {
            update_option('preview_data', $content);
        } else {
            add_option('preview_data', $content);
        }

        $html = '<!DOCTYPE html><html><head><title>EveryBoard Preview</title>';
        $html .= '<meta name="viewport" content="width=device-width, initial-scale=1">';
        $html .= '<style>.rotate {  }</style>';
        $html .= '</head><body>';
        $html .= '<div id="wrapper"><iframe id="preview" src="' . EVERYBOARD_BASE . 'preview.php?preview_data' . '" width="100%" height="100%" frameborder="0"></iframe></div>';
        $html .= '<script type="text/javascript">var board_url = "";</script>';
        $html .= '<script src="http://code.jquery.com/jquery-1.9.1.min.js"></script><script type="text/javascript" src="' . EVERYBOARD_BASE . 'assets/dist/js/boardpreview_action.min.ugly.js' . '"></script>';
        $html .= '</body></html>';

        return $html;
    }

    /**
     * Adds custom meta boxes to edit board page in admin.
     *
     * @return void
     */
    public function board_page_add_metaboxes()
    {
        add_meta_box('page_board_side_meta', 'EveryBoard', [&$this, 'board_page_side_meta'], 'Page', 'side', 'core',
            null);
    }

    /**
     * Side meta box renders on edit page and displays options for connecting page to boards.
     *
     * @return void
     */
    public function board_page_side_meta()
    {
        // Get the active board id.
        $active_board_id = $this->get_active_board_id(get_the_ID());

        // Set up loop for boards cpt.
        $args = ['post_type' => 'everyboard', 'posts_per_page' => -1];
        $loop = new WP_Query($args);
        $selected = 'selected="selected"';

        // Print select board item.
        print '<p><strong>' . __('Choose an EveryBoard', 'everyboard') . '</strong></p>';
        print '<select name="everyboard_id" id="everyboard_id">';

        $board_title = '';
        print '<option value="">' . __('(No board)', 'everyboard') . '</option>';

        foreach ($loop->posts as $board_post) {
            print '<option value="' . $board_post->ID . '" ' . ($active_board_id === $board_post->ID ? $selected : '') . '>' . $board_post->post_title . '</option>';
            if ($active_board_id === $board_post->ID) {
                $board_title = $board_post->post_title;
            }
        }

        print '</select>';

        $link = get_edit_post_link($active_board_id);
        print " - <a href='" . $link . "' target='_blank'>Edit " . $board_title . '</a>';
    }

    /**
     * Executes on page update and saves custom field data.
     *
     * @return void
     * @since 1.5.13
     */
    public function board_page_update_page()
    {
        if (isset($_REQUEST['post_ID'], $_REQUEST['post_type'], $_REQUEST['everyboard_id']) && $_REQUEST['post_type'] === 'page') {
            update_post_meta($_REQUEST['post_ID'], 'everyboard_id', $_REQUEST['everyboard_id']);
        }
    }

    /**
     * Override the_content and return Board Data if a board is active.
     *
     * @param string $content
     *
     * @return string
     * @since 1.5.13
     */
    public function board_page_content_override($content)
    {
        // If this is not a single page, return the_content as is.
        if ( ! is_singular('page')) {
            return $content;
        }

        $board = $this->get_page_board();

        // If board has been removed or trashed
        if (null === $board || $board->post_status === 'trash') {
            return $content;
        }

        $board_renderer = new EveryBoard_Renderer();

        return $board_renderer->render($this->get_board_json($board->ID));
    }

    /**
     * Retrieve board json from id
     *
     * @param int $board_id
     *
     * @return array|null
     * @since 1.5.13
     */
    protected function get_board_json($board_id)
    {
        if (isset($_GET['board_preview']) && $_GET['board_preview'] === 'true') {
            return get_post_meta($board_id, 'everyboard_json_temp');
        }

        return get_post_meta($board_id, 'everyboard_json');
    }

    /**
     * Retrieve id of board connected to current page
     *
     * @param int|false $post_id
     *
     * @return int
     * @since 1.5.13
     */
    protected function get_board_id($post_id = false)
    {
        if (isset($_GET['board_id'])) {
            return (int)$_GET['board_id'];
        }

        if ($post_id === false || empty($post_id)) {
            $post_id = get_the_ID();
        }

        $board_id = get_post_meta($post_id, 'everyboard_id');

        return count($board_id) > 0 ? (int)$board_id[0] : 0;
    }

    /**
     * Retrieve board connected to current page
     *
     * @return null|WP_Post
     * @since 1.5.13
     */
    protected function get_page_board()
    {
        $board_id = $this->get_board_id();
        // If board is chosen
        if ($board_id !== null && $board_id !== 0) {
            return get_post($board_id);
        }

        return null;
    }

    protected function get_active_board_id(int $page_id): int
    {
        $active_board_settings = (array)get_post_custom_values('everyboard_id', $page_id);

        return !empty($active_board_settings) ? (int)$active_board_settings[0] : 0;
    }
}
