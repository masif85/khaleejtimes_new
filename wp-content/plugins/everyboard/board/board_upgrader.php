<?php

class EveryBoard_Upgrader
{
    private $framework;

    /**
     * Settings constructor, adds actions and sets private member.s
     *
     * @param string $file Path of file that called constructor.
     */
    public function __construct()
    {
        if (is_admin()) {
            add_action('admin_menu', [&$this, 'register_board_upgrader_submenu_page']);
        }
    }

    public function register_board_upgrader_submenu_page()
    {
        add_submenu_page('edit.php?post_type=everyboard', 'Board Upgrader', __('Board Upgrader', 'everyboard'),
            'manage_options', 'boardupgrader', [
                &$this,
                'admin_page'
            ]);
    }

    public function admin_page()
    {
        echo "<style>pre {line-height: 1.1em; font-size:12px; }</style>";
        echo $this->html_wrap();
        echo "<h2>" . __('Upgrade Everyboard', 'everyboard') . "</h2>";

        $cpt = new EveryBoard_CustomPostType();

        $boards = $this->get_all_boards_with_content();
        $boardsQty = 0;

        $oldBoards = [];
        $newBoards = [];

        foreach ($boards as $board) {

            // Only use saved boards
            if (empty ($board['json'])) {
                echo "<strong>" . $board['name'] . "</strong><br>";
                echo "Board must be saved before it can be upgraded";
                $this->br(2);
                continue;
            }

            $array = json_decode($board['json'], true);
            // Make sure $array is an array
            $array = is_array($array) ? $array : [];

            echo "<strong>" . $board['name'] . "</strong><br>";

            $board_version = '0.2';

            if (array_key_exists('VERSION', $array)) {
                $board_version = $array['VERSION'];
            } elseif (array_key_exists('version', $array)) {
                $board_version = $array['version'];
            }

            // Todo remove uppercase version statement
            if (EVERYBOARD_JSON_FORMAT_VERSION == $board_version) {
                echo "Version {$board_version} found, no need to update";
                if (isset($_GET['old'])) {

                }

            } elseif ( ! array_key_exists('version', $array)) {
                echo "No version found, assuming 0.2";
                $boardsQty++;

                // Get a new board
                $newBoardObj = $cpt->update_board($array);
                $rows = isset($array['rows']) ? $array['rows'] : [];

                // loop through all rows to convert them and their content
                $rowCounter = 1;
                foreach ($rows as $row_key => $row) {

                    // $rows[$row_key] = $cpt->cpt_update_structure_data_array($row, 'row', $newBoardObj);
                    $newRow = $cpt->cpt_update_structure_data_array($row, 'row', $newBoardObj, $rowCounter);

                    $columns = (isset($row['cols'])) ? $row['cols'] : [];
                    // loop through columns if any
                    foreach ($columns as $col_key => $col) {

                        $column = $cpt->cpt_update_structure_data_array($col, 'col', $newRow, $rowCounter);
                        $content = (isset($col['content'])) ? $col['content'] : [];
                        // loop through content if any

                        $itemCounter = 1;
                        foreach ($content as $item_key => $item) {
                            // Convert every content item in column
                            $newItem = $cpt->cpt_update_structure_data_array($item, 'item', $column, $itemCounter);
                            $itemCounter++;
                            //$content[$item_key] = $cpt->cpt_update_structure_data_array($item, 'item', $column);
                        }
                        // Add new content to column
                        $col['content'] = $content;

                        // Add new column to columns
                        // Convert every column in the row
                        //$columns[$col_key] = $cpt->cpt_update_structure_data_array($col, 'col', $newBoardObj);
                    }

                    // Add new columns to row
                    $row['cols'] = $columns;
                }

                $newBoard['rows'] = $rows;
                $newBoard['VERSION'] = EVERYBOARD_JSON_FORMAT_VERSION;
                $newBoardObj->setVersion(EVERYBOARD_JSON_FORMAT_VERSION);

                if (isset($_POST['upgrade'])) {
                    echo "<h2>Updating " . $board['name'];
                    $old = $_POST['old_json'];
                    $updated = $_POST['new_json'];
                    $updatedResult = $cpt->cpt_update_board_data($board['boardId'], 'everyboard_json',
                        json_encode($newBoardObj));
                    $cpt->cpt_update_board_data($board['boardId'], 'everyboard_json_temp', json_encode($newBoardObj));
                    $cpt->cpt_update_board_data($board['boardId'], 'everyboard_json_version_2_backup', $board['json']);


                    $post_id = isset($_GET['post']) ? $_GET['post'] : '';
                    $page_arr = $cpt->get_board_pages($post_id);
                    if (count($page_arr) > 0) {
                        foreach ($page_arr as $page) {
                            do_action('board_changed_hook', $page->ID);
                        }
                    }


                    if ($updatedResult) {
                        echo "<h2>" . $board['name'] . " updated</h2>";
                    }
                }
                $newJson = json_encode($newBoardObj);
                $v3Json = $boards[0]['json'];
                $newBoards[] = $newBoardObj;
                $oldBoards[] = $board;


                //$this->renderDifferece( $array, json_encode($newBoard) );
                $this->br(4);
            }
            $this->br(2);
        }

        $this->renderUpgradeForm($boardsQty, json_encode($newBoards), json_encode($oldBoards));
        echo $this->html_div_end();
    }

    public function renderDifferece($old, $new)
    {
        $new = json_decode($new);

        foreach ($old['rows'] as $key => $oldBoard) {
            if (count($oldBoard['cols']) < 1) {
                return;
            }

            echo "<div style='border-bottom: 2px solid #565656; padding-top:30px;'>";
            echo "<h2>Compare row " . $key . "</h2>";

            echo "<div style='float:left; width: 45%; height: 320px;'>";
            echo "<h3>Settings</h3>";
            $this->br();
            $this->loopData($oldBoard['settings']);
            echo "</div>";

            echo "<div style='float:left; width: 45%;  height: 320px;'>";
            echo "<h3>New Settings</h3>";
            $this->br();
            $this->loopData($new->rows[$key]);
            echo "<div style='clear:both'></div>";

            echo "</div>";


            echo "<div class='row' style='padding-left: 30px;'>";

            echo "<div style='float:left; width: 45%; height:360px;'>";
            foreach ($oldBoard['cols'] as $k => $col) {
                echo "<h3>Column {$k}</h3>";
                echo $this->br();
                $this->loopData($col['settings']);

                echo $this->br();
                echo $this->br();
                echo "<div style='float:left;padding-left:30px; width: 100%; height: 320px;'>";
                foreach ($col['content'] as $k => $col) {
                    echo "<h3>Content {$k}</h3>";
                    echo $this->br();
                    $this->loopData($col['settings']);
                }
                echo "</div>";
            }
            echo "</div>";

            echo "<div style='float:left; width: 45%'>";
            foreach ($new['rows'][$key]['cols'] as $k => $col) {
                echo "<h3>New Column {$k}</h3>";
                echo $this->br();

                $this->loopData($col['settings']);

                echo $this->br();
                echo $this->br();

                echo "<div style='float:left; padding-left:30px; width: 100%; height: 320px;' >";
                foreach ($col['content'] as $k => $col) {
                    echo "<h3>New Content {$k}</h3>";
                    $this->br();
                    $this->loopData($col['settings']);
                }
                echo "</div>";

            }
            echo "<div style='clear:both'></div>";
            echo "</div>";

            echo "<div style='clear:both'></div>";
            echo "</div>";
        }

    }

    public function loopData($data)
    {
        foreach ($data as $key => $d) {
            if (is_array($d)) {
                echo "<a title='" . print_r($d, true) . "'>$key Array</a>";
                echo "<hr>";
            } else {
                echo $key . " - " . $d;
                echo "<hr>";
            }
        }
    }

    public function renderUpgradeForm($boardsQty = 0, $new_json, $old_json)
    {
        if ($boardsQty > 0) :
            echo "<form method='post'>";
            echo "<input type='hidden' name='old_json' value='{$old_json}'>";
            echo "<input type='hidden' name='new_json' value='{$new_json}'>";
            echo "<input type='submit' name='upgrade' value='Upgrade {$boardsQty} boards' class='button button-primary'>";
            echo "</form>";
        endif;
    }

    public function get_all_boards_with_content()
    {
        $args = [
            'post_type' => 'everyboard',
            'posts_per_page' => -1,
            'post_status' => ''
        ];
        $wpBoards = get_posts($args);

        $boards = [];
        foreach ($wpBoards as $board) {
            $content = $this->get_board_content('everyboard_json', $board->ID);
            array_push($boards, [
                'name' => $board->post_title,
                'boardId' => $board->ID,
                'json' => $content
            ]);
        }

        return $boards;
    }

    public function get_board_content($key, $post_id)
    {
        $json = get_post_custom_values($key, $post_id);
        if ( ! $json) {
            $option = [];
            add_option($key, $option);
        }
        $json = (isset($json)) ? $json[0] : '';

        return $json;
    }

    private function html_wrap()
    {
        return '<div class="wrap">';
    }

    private function html_div_end()
    {
        return '</div">';
    }

    private function br($qty = 1)
    {
        for ($i = 0; $i < $qty; $i++) {
            echo '<br />';
        }
    }
}

class BoardBase
{
    public $data = [];
    public $settings = [];

    public function __set($key, $value)
    {
        if ( ! array_key_exists($key, $this->data)) {
            $this->data[$key] = $value;
        }
    }

    public function addSettings(array $settings)
    {
        $this->settings = $settings;
    }
}

class BoardBoard extends BoardBase
{
    public $name;
    public $date;
    public $rows = [];
    public $version;
    public $index;

    public function getVersion()
    {
        return $this->version;
    }

    public function setVersion($version)
    {
        $this->version = $version;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function setDate($date)
    {
        $this->date = $date;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getRows()
    {
        return $this->rows;
    }

    public function setRows($rows)
    {
        $this->rows = $rows;
    }

    public function addRow(BoardRow $row)
    {
        $this->rows[] = $row;
    }

    public function getIndex()
    {
        return $this->index;
    }

    public function setIndex($index)
    {
        $this->index = $index;
    }

}

class BoardRow extends BoardBase
{
    public $index;
    public $cols = [];
    public $type;

    public function getCols()
    {
        return $this->cols;
    }

    public function setCols($cols)
    {
        $this->cols = $cols;
    }

    public function addColumn(BoardColumn $column)
    {
        $this->cols[] = $column;
    }

    /**
     * @return mixed
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * @param mixed $index
     */
    public function setIndex($index)
    {
        $this->index = $index;
    }


    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }
}

class BoardColumn extends BoardBase
{
    public $content = [];
    public $index;
    public $width;
    public $type;

    public function getContents()
    {
        return $this->content;
    }

    public function setContents($contents)
    {
        $this->content = $contents;
    }

    public function addContent(BoardItem $content)
    {
        $this->content[] = $content;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function setWidth($width)
    {
        $this->width = $width;
    }

    public function getIndex()
    {
        return $this->index;
    }

    public function setIndex($index)
    {
        $this->index = $index;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }
}

class BoardContent extends BoardBase
{

}

class BoardItem extends BoardBase
{
    public $type;
    public $widget_id;
    public $oc_uuid;
    public $index;

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getOcUuid()
    {
        return $this->oc_uuid;
    }

    public function setOcUuid($uuid)
    {
        $this->oc_uuid = $uuid;
    }

    public function getWidgetId()
    {
        return $this->widget_id;
    }

    public function setWidgetId($widget_id)
    {
        $this->widget_id = $widget_id;
    }

    /**
     * @return mixed
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * @param mixed $index
     */
    public function setIndex($index)
    {
        $this->index = $index;
    }

}

class BoardSettings extends BoardBase
{

}
