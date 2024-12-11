<?php

    /*
     * Preview render of board
     */

    if (!function_exists('add_action')) {
        require_once("../../../wp-config.php");
    }

    if( isset( $_GET["preview_data"] ) ) {
        echo get_option("preview_data");
        die();
    }
    else if( isset( $_GET["preview_json"] ) ) {

        if( isset( $_GET["bid"] ) ) {
            $board_id = intval( $_GET["bid"] );
        }
        else {
            $board_id = get_post_custom_values( 'everyboard_id', $_GET["id"] );
            $board_id = intval( $board_id[0] );
        }

        $json = get_post_custom_values( 'everyboard_json_temp', $board_id );
        echo $json[0];
        die();

    }
    elseif ( isset( $_GET["get_board"] ) ) {



    }
    else {

        if( $_GET["mode"] == "mobile" ) {
            $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (iPhone; CPU iPhone OS 5_0 like Mac OS X) AppleWebKit/534.46 (KHTML, like Gecko) Version/5.1 Mobile/9A334 Safari/7534.48.3';
        }
        else if( $_GET["mode"] == "tablet" ) {
            $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.10';
        }

        if( isset( $_GET["bid"] ) ) {
            $board_id = intval( $_GET["bid"] );
        }
        else {
            $board_id = get_post_custom_values( 'everyboard_id', $_GET["id"] );
            $board_id = intval( $board_id[0] );
        }

        require_once dirname( __FILE__ ) . '/everyboard.php';

        $board_json     = get_post_custom_values( 'everyboard_json_temp', $board_id );
        $board_renderer = new EveryBoard_Renderer();
        echo $board_renderer->render( $board_json );
    }