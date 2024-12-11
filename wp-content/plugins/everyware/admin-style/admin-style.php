<?php
/*
* Function to add admin page styles
*/
function every_admin_head() {
	global $post_type;

	if ( isset( $_GET['post_type'] ) && ( $_GET['post_type'] == 'article' ) || ( $post_type == 'article' ) ) {
		print '<style>';
		print '#icon-edit { background:transparent url(\'' . plugins_url( 'Every/admin-style/images/article_big.png' ) . '\') no-repeat; }';
		print '</style>';
	}

	echo '<link rel="stylesheet" type="text/css" href="' . plugins_url( '/css/every-admin.css', __FILE__ ) . '">';

	wp_enqueue_style( 'wp-jquery-ui-dialog' );
}

add_action( 'admin_head', 'every_admin_head' );

/*
 * Function to add script files to admin
 */
function every_admin_scripts() {
	wp_enqueue_script( 'ever-admin', plugin_dir_url( __FILE__ ) . '/js/every-admin.js', array( 'jquery', 'jquery-ui-draggable', 'jquery-ui-droppable', 'jquery-ui-dialog', 'jquery-ui-tabs', 'jquery-ui-sortable' ), '1.1', true );

	wp_localize_script( 'ever-admin', 'oc_ajax', array( 'oc_ajax_url' => admin_url( 'admin-ajax.php' ) ) );
    wp_localize_script( 'ever-admin', 'translation', array(
        'oc_success'            => __( 'A test connection to your Open Content was successfully established and closed!', 'every' ),
        'oc_failure'            => __( 'Could not establish a connection to your Open Content!', 'every' ),
        's3_success'            => __( 'A test connection to your S3 Bucket was successfully established and closed!', 'every' ),
        's3_failure'            => __( 'Could not establish a connection to your S3 Bucket!', 'every' ),
        'oc_qb_header'          => __( 'Open Content Query Builder', 'every' ),
        'oc_qb_generated_query' => __( 'Generated Query:', 'every' ),
        'oc_qb_add_rule'        => __( 'Add', 'every' ),
        'oc_qb_active_rules'    => __( 'Active rules:', 'every' ),
        'oc_qb_query_button'    => __( 'Set query to widget', 'every' ),
        'oc_query_result'       => __( 'Query results:', 'every' ),
        'article_slug_property' => __( 'Add article slug property', 'every' ),
        'article_slug_add'      => __( 'Add' ),
        'button_register'       => __( 'Register', 'every' ),
        'button_unregister'     => __( 'Unregister', 'every' ),
        'notifier_reg_success'  => __( 'Successfully registered as listener', 'every' ),
        'notifier_reg_failure'  => __( 'An error has occurred during registration of notifier, please contact an administrator', 'every' ),
        'notifier_unreg_success'=> __( 'Successfully unregistered listener', 'every' ),
        'notifier_unreg_failure'=> __( 'An error has occurred during removal of notifier, please contact an administrator', 'every' ),
        'notifier_no_input'     => __( 'Please input a valid URL for notifier! ', 'every'),
        'purge_cache_success'   => __( 'Successfully purged the article from cache', 'every' ),
        'purge_cache_failure'   => __( 'Error: Could not purge article from cache', 'every' ),
        'get_property_error'    => __( 'Could not fetch properties from OC', 'every' ),
        'remove_button'         => __( 'Remove', 'every' ),
        'add_binding'           => __( 'Add Binding', 'every' ),
        'choose_binding'        => __( 'Choose binding', 'every' )
    ));
}
add_action( 'admin_print_scripts', 'every_admin_scripts' );

/*
* Function to add custom style to login page
*/
function my_login_css() {
	echo '<link rel="stylesheet" type="text/css" href="' . plugins_url( '/css/every-login.css  ', __FILE__ ) . '">';
}
add_action( 'login_head', 'my_login_css' );


