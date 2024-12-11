<?php

class BoardRenderWidget extends OcWidgetBase {

    function oc_widget_name() {
        return 'Boardrender Widget';
    }

    /*
     * Function to display widget
     */
    function widget( $args, $instance ) {

        extract( $args );

        $json = get_post_meta((int)$instance['board'], 'everyboard_json');

        echo $before_widget;

        $renderer = new EveryBoard_Renderer();
        echo $renderer->render( $json );

        echo $after_widget;
    }

    function widget_board( $instance ) {

        print 'BoardRenderWidget.';
    }

    function form( $instance ) {

        $args     = array( 'post_type' => 'everyboard', 'posts_per_page' => -1 );
        $loop     = new WP_Query( $args );

        $curr_board_id = isset( $instance['board'] ) ? $instance['board'] : "";

        print '<p><strong>Choose an EveryBoard</strong></p>';
        print '<select name="'. $this->get_field_name( 'board' ) .'" id="'. $this->get_field_id( 'board' ) . '">';

        print '<option value="">(No board)</option>';
        foreach ($loop->posts as $board_post ) {
            print '<option value="' . $board_post->ID . ' " '.  ($board_post->ID == $curr_board_id ? "selected=\"selected\"" : "") .'>' . $board_post->post_title . '</option>';
        }

        print '</select>';
    }

    function update( $new_instance, $old_instance ) {
        $instance = $old_instance;

        $instance['board'] = $new_instance['board'];

        return $instance;
    }
}

//add_action( 'widgets_init', function() {
//    register_widget( 'BoardRenderWidget' );
//});