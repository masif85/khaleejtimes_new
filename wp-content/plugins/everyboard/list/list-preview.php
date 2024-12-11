<?php
if (isset($_GET['listId'], $_GET['list_preview']) && $_GET['list_preview'] === "true") {

    add_filter('show_admin_bar', '__return_false');

    add_action('wp_enqueue_scripts', function () {

        wp_register_script('jquery', '/wp-includes/js/jquery/jquery.js');
        wp_enqueue_style(
            'everylist_style',
            EVERYBOARD_BASE . 'assets/dist/css/boardlist.min.css',
            [],
            EVERYBOARD_VERSION
        );
        wp_enqueue_script(
            'everylist_preview_script',
            EVERYBOARD_BASE . 'assets/dist/js/listpreview.min.ugly.js',
            ['jquery'],
            EVERYBOARD_VERSION,
            true);
        wp_localize_script('everylist_preview_script', 'preview', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'listId' => $_GET['listId']
        ]);
    }, 200);

    add_filter('wp_head', function () {

        if ( ! is_user_logged_in()) {
            print '<h1>No access</h1>';
            die();
        }

        ?>
        <style>
            .list-preview-header {
                padding: 10px;
                border-bottom: 1px solid #ddd;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                background-color: #fff;
                z-index: 99999;
                box-sizing: border-box;
                height: 50px;
                line-height: 30px;
            }

            .list-preview-header label {
                display: inline;
                margin-right: 5px;
                padding-top: 4px;
                float: left;
            }

            html {
                padding-top: 40px;
            }
        </style>
        <?php
    });

    add_filter('the_content', function () {

        // Fetch article templates.
        $templates = EveryBoard_Settings::get_templates();

        $html = '<div class="list-preview-header">';
        $html .= '<label for="list-template-select">Template:</label>';
        $html .= '<select name="list-template-select" id="list-template-select">';
        foreach ($templates as $template_name => $template_path) {
            $html .= '<option value="' . $template_path . '">' . $template_name . '</option>';
        }
        $html .= '</select>';
        $html .= '</div>';
        $html .= '<div class="list-preview"></div>';

        return $html;
    });
}
