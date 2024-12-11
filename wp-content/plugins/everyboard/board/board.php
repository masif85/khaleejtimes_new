<?php

// Require needed files and classes
require_once dirname(__FILE__) . '/board_settings.php';
require_once dirname(__FILE__) . '/board_article_helper.php';
require_once dirname(__FILE__) . '/board_custom_post_type.php';
require_once dirname(__FILE__) . '/board_page.php';
require_once dirname(__FILE__) . '/board_framework_helper.php';
require_once dirname(__FILE__) . '/board_renderer.php';
require_once dirname(__FILE__) . '/board_upgrader.php';

/**
 * Class EveryBoard_Board
 */
class EveryBoard_Board
{
    /**
     * Initialize EveryBoard_Board class
     */
    public function __construct()
    {
        // Creates a new custom post type board instance.
        $cpt_board = new EveryBoard_CustomPostType();

        // Creates a new page handling instance.
        $page_board = new EveryBoard_Page();

        $page_upgrader = new EveryBoard_Upgrader();

        add_action('every_widget_saved_flush', [&$this, 'clean_cache_board'], 10, 2);
    }

    /**
     *
     * @param $percent
     * @param $cols_num
     *
     * @return int
     */
    public static function procent_to_columns($percent, $cols_num)
    {
        $one_col = (1 / $cols_num) * 100;

        $ld = 1000;
        $i = 1;
        for ($c = $one_col; $c <= 100; $c += $one_col) {
            $dist = abs($percent - $c);

            if ($dist > $ld) {
                break;
            }

            $ld = $dist;
            $i++;
        }

        $i = $i - 1;

        return $i;
    }

    /**
     *
     *
     * @param $dummy
     * @param $widgetid
     */
    public function clean_cache_board($dummy, $widgetid)
    {
        $board_args = [
            'post_type' => 'everyboard',
            'post_status' => 'any'
        ];

        $boards = get_posts($board_args);
        $found_boards = [];

        foreach ($boards as $board) {
            $custom_fields = get_post_custom($board->ID);
            $json = $custom_fields["everyboard_json"][0];
            $json = json_decode($json);

            if (isset($json->rows)) {
                foreach ($json->rows as $row) {
                    if (isset($row->cols)) {
                        foreach ($row->cols as $col) {
                            if (isset($col->content)) {
                                foreach ($col->content as $content) {
                                    if ($content->type == "widget") {
                                        if ($widgetid == $content->widget_id) {
                                            if ( ! array_key_exists($board->ID, $found_boards)) {
                                                $found_boards[$board->ID] = 1;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }

                }
            }

        }

        foreach ($found_boards as $id => $none) {
            $args = [
                'post_type' => 'page',
                'meta_query' => [
                    [
                        'key' => 'everyboard_id',
                        'value' => $id,
                        'compare' => '='
                    ]
                ]
            ];

            $cat_ttl = 3600;
            $cache_key = md5("clean_cache_board_" . $id);

            if (false === ($posts = get_transient($cache_key))) {
                $posts = get_posts($args);
                set_transient($cache_key, $posts, $cat_ttl);
            }

            #$posts = get_posts( $args );
            foreach ($posts as $page) {
                do_action('board_changed_hook', $page->ID);
            }
        }

    }
}

$everyboard_board = new EveryBoard_Board();
$eb_framework_helper = new EveryBoard_Framework_Helper();
