<?php

class OcImageWidget extends OcWidgetBase {

    private $default_fields = [
        'template_data_input' => 'single'
    ];

    function oc_widget_name() {
        return 'OC Image Display';
    }

    /*
     * Function to render widget content to site
     */
    function widget( $args, $instance ) {

        extract( $args );

        if(isset($_GET['start']) && is_numeric($_GET['start'])) {
            $instance['oc_query_start'] = intval($_GET['start']);
        }

        $this->oc_api->prepare_widget_query( $instance );
        $object_array = $this->oc_api->get_oc_images();

        print $before_widget;

        $template = null;
        if( isset($instance["image_template"] ) && file_exists( EveryBoard_Settings::get_template_path( $instance["image_template"]) ) ) {
            $template = EveryBoard_Settings::get_template_path($instance["image_template"]);
        }

        $widget_title = isset( $instance['title'] ) ? $instance['title'] : '';

        if( isset( $widget_title ) && $widget_title !== '' ){
            //This will echo widget title, not article title
            print '<div class="widget_title_div">';
            print '<h3 class="widget_title">' . esc_attr( $widget_title ) . '</h3>';
            print '</div>';
        }

        $instance = array_replace_recursive($this->default_fields, $instance);

        if( $instance['template_data_input'] === 'single' ) {
            try {
                foreach ( $object_array as $key => $object ) {
                    $this->render_template($object, $template);
                }

            } catch(Exception $e) {}
        } else {

            if($template !== null) {

                $object_array['pagination'] = [
                    'limit' => $instance['oc_query_limit'],
                    'start' => $instance['oc_query_start'],
                    'total' => $this->oc_api->getLatestQueryTotalHits()
                ];
            }

            $this->render_template($object_array, $template);
        }

        print $after_widget;
    }

    private function render_template( $object, $template = null ) {
        if( !is_array($object) )
            $article = $object;

        if($template !== null) {
            include $template;
        }
        else if ( file_exists( EveryBoard_Settings::get_template_path( "templates/ocimage-default.php" ) ) ) {
            if( is_array($object) ) {
                array_walk($object, function($object) {
                    $article = $object;
                    include EveryBoard_Settings::get_template_path( "templates/ocimage-default.php" );
                });
            } else {
                include EveryBoard_Settings::get_template_path( "templates/ocimage-default.php" );
            }
        }
    }

    /*
     * Function to declare admin UI for widget
     */
    function form( $instance ) {
        $this->title_field( $instance );
        $this->oc_query_field( $instance );
        $this->image_template($instance);
        $this->template_usage_option($instance);
    }
}

//add_action( 'widgets_init', function() {
//    register_widget( 'OcImageWidget' );
//});