<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */

class AdSpots extends WP_Widget {

  public function __construct() {
    $widget_options = array( 
        'classname' => 'adspots',
        'description' => 'This is an Ad Spots Widget.',
      );
      parent::__construct( 'adspots', 'Ad Spots', $widget_options );
  }

  public function widget( $args, $instance ) {
    $adSpotID = $instance[ 'adSpotID' ];
    $adSpotSize = $instance[ 'adSpotSize' ];
    echo '<div id="' . $adSpotID. '" class="' . $adSpotID . ' advertisement">';
    echo '<script type="text/javascript">';
    echo 'if (googletag !== undefined) {googletag.cmd.push(function() { googletag.display("' . $adSpotID. '"); })}';
    echo '</script>';
    echo '</div>';
  }

  public function form( $instance ) {
    $adSpotID = ! empty( $instance['adSpotID'] ) ? $instance['adSpotID'] : '';
    $adSpotSize = ! empty( $instance['adSpotSize'] ) ? $instance['adSpotSize'] : ''; ?>
    <p>
      <label for="<?php echo $this->get_field_id( 'adSpotID' ); ?>">Ad Spot ID:</label>
      <input type="text" id="<?php echo $this->get_field_id( 'adSpotID' ); ?>" name="<?php echo $this->get_field_name( 'adSpotID' ); ?>" value="<?php echo esc_attr( $adSpotID ); ?>" />
    </p>
    <p>
      <label for="<?php echo $this->get_field_id( 'adSpotSize' ); ?>">Ad Spot Size:</label>
      <input type="text" id="<?php echo $this->get_field_id( 'adSpotSize' ); ?>" name="<?php echo $this->get_field_name( 'adSpotSize' ); ?>" value="<?php echo esc_attr( $adSpotSize ); ?>" />
    </p><?php 
  }

  public function update( $new_instance, $old_instance ) {
    $instance = $old_instance;
    $instance[ 'adSpotID' ] = strip_tags( $new_instance[ 'adSpotID' ] );
    $instance[ 'adSpotSize' ] = strip_tags( $new_instance[ 'adSpotSize' ] );
    return $instance;
  }
}

function adspots_register_widget() { 
    register_widget( 'AdSpots' );
}
add_action( 'widgets_init', 'adspots_register_widget' );

?>