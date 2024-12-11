<?php
/*
  Plugin Name: Quick 301 Redirects
  Contributors: galdub, tomeraharon, premio
  Description:The fastest and easiest way to do 301 redirects.
  Author: Premio
  Author URI: https://premio.io/
  Version: 1.1.6
  License: GPL2
 */
function quick_301_redirects_load_plugin_textdomain() {
	load_plugin_textdomain( 'quick-301-redirects', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'quick_301_redirects_load_plugin_textdomain' );


/**
 * Redirect to plugin setting page after activate the plugin.
 *
 * @since 1.0.0
 */
function quick_301_redirects_activation_redirect( $plugin ) {
    if( $plugin == plugin_basename( __FILE__ ) ) {
        wp_redirect( admin_url( 'options-general.php?page=quick-301-redirects' ) ) ;
		exit;
    }
}
add_action( 'activated_plugin', 'quick_301_redirects_activation_redirect' );

/**
 * Plug-in activation - settings link.
 * Settings link in plugin page.
 * @param array $links
 *
 * @since 1.0.0
 */
function quick_301_redirects_action_links( $links ){

	$plugin_links = array( '<a href="' . admin_url( 'options-general.php?page=quick-301-redirects' ) . '">' . __( 'Settings', 'quick-301-redirects' ) . '</a>' );

	return array_merge( $plugin_links, $links );
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'quick_301_redirects_action_links' );

/**
 * Enqueue admin scripts and styles.
 *
 * @Since 1.0
 */
function quick_301_redirects_admin_scripts(){
	wp_enqueue_style( 'wp-jquery-ui-dialog' );
	wp_enqueue_script( 'jquery-ui-dialog' );

	wp_enqueue_script( 'jquery-session', plugins_url('/', __FILE__). 'js/jquery.session.js', array('jquery') );
}
add_action( 'admin_enqueue_scripts', 'quick_301_redirects_admin_scripts' );
/**
 * quick_301_redirects_menu function
 * generate the link to the options page under settings
 *
 * @Since 1.0
 */
function quick_301_redirects_menu(){
	add_options_page( __( 'Quick 301 Redirects', 'quick-301-redirects' ), __( 'Quick 301 Redirects', 'quick-301-redirects' ), 'manage_options', 'quick-301-redirects', 'quick_301_redirects_options_page' );
}
add_action('admin_menu', 'quick_301_redirects_menu');

/**
 * quick_301_redirects_options_page function
 * generate the options page in the wordpress admin inside settings menu
 *
 * @Since 1.0
 */
function quick_301_redirects_options_page(){

	if (isset($_POST['quick_301_redirects_']) && !empty($_POST['quick_301_redirects_'])) {
		quick_301_redirects_save_data();
		?>
		<div id="message" class="updated"><p><?php _e( 'Settings saved', 'quick-301-redirects' );?></p></div>
		<?php
	}
	?>
	<div class="wrap quick-301-redirects-wrap" dir="ltr">
		<h1 class="wp-heading-inline"><?php _e( 'Quick 301 Redirects', 'quick-301-redirects' );?></h1>		
		<?php
		if ( isset($_GET['bulk_upload_successfull']) && $_GET['bulk_upload_successfull'] == true) {

			echo "<div class='updated inline'><p>";
			_e( 'Upload Bluk csv file successfully.', 'woocommerce' ) ;
			echo "</p></div>";
		}
		?>
		<div class="stuffbox" style="padding: 20px 20px 0; margin-bottom:50px; margin-top:20px">
			<form method="post" id="quick_301_redirects_form" action="options-general.php?page=quick-301-redirects">

				<table id="quick-301-redirects-tbl" class="widefat quick-301-redirects">
					<thead>
						<tr>
							<th colspan="2" style="text-align:left;"><strong><?php _e( 'Request', 'quick-301-redirects' );?></strong></th>
							<th colspan="2" style="text-align:left;"><strong><?php _e( 'Destination', 'quick-301-redirects' );?></strong></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td colspan="2"><small><?php _e( 'Example: /page.html', 'quick-301-redirects' );?></small></td>
							<td colspan="2"><small><?php printf(__( 'Example:  %s/page/', 'quick-301-redirects'), get_option('home') );?></small></td>
						</tr>
						<?php quick_301_redirects_data_lists(); ?>
						<tr id="quick-301-redirects-main">
							<td style="width:45%;">
								<input type="text" name="quick_301_redirects_[request][]" value="" class="regular-text" style="width:95%;" />
								<p class="description"><?php esc_html_e( 'if / isn\'t added in the request part, add it yourself in the beginning (otherwise it won\'t work)', 'quick-301-redirects');?></p>
							</td>
							<td style="width:1%;">&rarr;</td>
							<td style="width:45%;">
								<input type="text" name="quick_301_redirects_[destination][]" value="" class="regular-text" style="width:95%;" />
							</td>
							<td>
								<span class="quick-301-redirects-add"><a href="#" id="quick-301-redirects-add" class="dashicons dashicons-plus"><?php __( 'Add', 'quick-301-redirects' );?></a></span>
							</td>
						</tr>
					</tbody>
				</table>
				<div class="submit quick-301-redirects-submit">
					<input type="submit" name="submit_quick_301_redirects_" class="button-primary" value="<?php _e('Save Changes', 'quick-301-redirects' ) ?>" />
				</div>

				<?php wp_nonce_field( 'save_quick_301_redirects_', '_quick_301_redirects_nonce' ); ?>
			</form>
		</div>
		<div id="quick-301-redirects-delete-confirm" style="display:none;" title="<?php esc_attr_e( 'Delete Redirect option', 'the-ansh' ); ?>">
			<p><?php _e('Delete This Redirect 301 link?', 'quick-301-redirects' ); ?></p>
			<label><input type="checkbox" id="quick_301_redirects_again" name="quick_301_redirects_again" value="1" /> &nbsp;<?php _e( "don't ask me again", "quick-301-redirects" );?></label>
		</div>
		<script>
		( function( $ ) {
			"use strict";

			$(document).ready(function(){
				/* Checkbox checked delete confirm box when checked save on session */
				if ( $.session.get("quick_301_redirects_again") === 'true' ) {
					$('#quick_301_redirects_again').prop('checked', true);
					$('#quick_301_redirects_again').is(':checked');
				}

				$('a#quick-301-redirects-add').on( 'click', function(event) {
					event.preventDefault();
					$('#quick-301-redirects-tbl tbody tr#quick-301-redirects-main').before('<tr><td style="width:45%;"><input type="text" name="quick_301_redirects_[request][]" value="" class="regular-text" style="width:100%;" /></td><td style="width:2%;">&rarr;</td><td style="width:45%;"><input type="text" name="quick_301_redirects_[destination][]" value="" class="regular-text" style="width:100%;" /></td><td><span class="quick-301-redirects-delete"><a href="#" class="quick-301-redirects-delete delete-empty-field dashicons dashicons-trash"><?php __('Delete', 'quick-301-redirects' );?></a></span></td></tr>');
				});
				
				$(document).on( "click", 'a.quick-301-redirects-delete' , function(e){		
					event.preventDefault();
					var $delete_tr = $(this);
					
					if ( $(this).hasClass('delete-empty-field')) {
						$(this).parent().parent().parent().remove();
						return;
					}
					
					if ( $('#quick_301_redirects_again').is(':checked') !== true ) {
						jQuery( "#quick-301-redirects-delete-confirm" ).dialog({
								resizable: false,
								modal: true,
								draggable: false,
								height: 'auto',
								width: 400,
								buttons: {
									"OK": function () {
										$delete_tr.parent().parent().parent().remove();

										if ( $('#quick_301_redirects_again').is(':checked') === true ) {
											$.session.set("quick_301_redirects_again", true);
										}
										if ( !$delete_tr.hasClass('delete-empty-field')) {
											jQuery('form#quick_301_redirects_form').submit();
										}
										$(this).dialog('close');
									},
									"Cancel": function () {
										$(this).dialog('close');
									}
								}
							});

					} else {
						$(this).parent().parent().parent().remove();
						if ( !$delete_tr.hasClass('delete-empty-field')) {
							jQuery('form#quick_301_redirects_form').submit();
						}
					}

					/*var delete_confirm = confirm('<?php ?>');
					if ( delete_confirm ) {
						$(this).parent().parent().parent().remove();

						if ( !$(this).hasClass('delete-empty-field')) {
							jQuery('form#quick_301_redirects_form').submit();
						}
					}*/

				});
			});
		})( jQuery );
		</script>
		<style>
		#quick-301-redirects-tbl tr td{
			vertical-align: top;
		}
		#quick-301-redirects-tbl tr td input[type="text"]{
			border-radius: 12px;
		}
		.quick-301-redirects-submit .button-primary{
			background: #F51366;
			color: #fff;
			border: solid 1px #F51366;
			box-shadow: 0 3px 5px -3px #333333;
			text-shadow: none;
		}
		.quick-301-redirects-submit .button-primary:hover, .quick-301-redirects-submit .button-primary:focus {
			background: #bc0f50;
			color: #ffffff;
			border: solid 1px #bc0f50;
		}
		.quick-301-redirects-wrap .button.button-primary,
		#quick-301-redirects-bulk-upload .button.button-primary{
			background: #06CB53;
			color: #fff;
			border: solid 1px #06CB53;
			box-shadow: 0 3px 5px -3px #333333;
			text-shadow: none;
			vertical-align: unset;
		}
		.quick-301-redirects-wrap .button.button-primary:hover,
		#quick-301-redirects-bulk-upload .button.button-primary:hover{
			background: #039b4c;
			color: #ffffff;
			border: solid 1px #039b4c;
		}
		</style>
		<?php quick_301_redirects_bulk_upload();?>
	</div>
	<?php
}

/**
 * quick_301_redirects_data_lists function
 * Display the current list of redirects as form fields
 * @return string <html>
 *
 * @Since 1.0
 */
function quick_301_redirects_data_lists(){
	$quick_301_redirects_ = get_option('quick_301_redirects_');
	/* Upload CSV file in View Process */
	if ( isset($_POST['quick_301_redirects_bulk_upload']) &&  !empty($_FILES['import_bulk_301_csv'])  ) {

		$quick_301_redirects_ = get_option('quick_301_redirects_');
		$filename = $_FILES["import_bulk_301_csv"]["tmp_name"];
		if ( $_FILES["import_bulk_301_csv"]["size"] > 0 ) {
			$file = fopen($filename, "r");
			$i=0;
			while (($getData = fgetcsv($file, 10000, ",")) !== FALSE) {
				if ( $i != 0 ) {
					$quick_301_redirects_[$getData[0]] = $getData[1];
				}
				$i++;
			}
			fclose($file);
		}

	}

	if (!empty($quick_301_redirects_)) {
		foreach ($quick_301_redirects_ as $request => $destination) { 
			$len = strlen($request);
			$request_link = ( substr($request, 0, $len) === '/') ? site_url() . $request : $request;
			$url = parse_url($request);
			if( isset($url['scheme']) && ( $url['scheme'] == 'https' || $url['scheme'] == 'http' ) ){
			   $request_link = $request;
			} else {				
				$request_link = ( substr($request, 0, 1) == '/' ) ? site_url() . $request : site_url() . '/' . $request;
			}
			
		?>
			<tr>
				<td>
					<input type="text" name="quick_301_redirects_[request][]" value="<?php echo $request;?>" class="regular-text" style="width:95%;" />					
					<a href="<?php echo $request_link;?>" target="_balnk" class="" style="vertical-align:middle;"><img src="<?php echo plugins_url('/', __FILE__) . '/images/external-link-16.png';?>" /></a>
				</td>
				<td>&rarr;</td>
				<td>
					<input type="text" name="quick_301_redirects_[destination][]" value="<?php echo $destination;?>" class="regular-text" style="width:95%;" />
				</td>
				<td>
					<span class="quick-301-redirects-delete"><a href="#" class="quick-301-redirects-delete dashicons dashicons-trash"><?php __( 'Delete', 'quick-301-redirects' );?></a></span>
				</td>
			</tr>
		<?php

		}
	} // end if

}

/**
 * quick_301_redirects_save_data function
 * save the redirects datas into options table
 * @return array
 *
 * @Since 1.0
 */
function quick_301_redirects_save_data(){

	if ( !current_user_can('manage_options') )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.', 'quick-301-redirects' ) );
	}

	check_admin_referer( 'save_quick_301_redirects_', '_quick_301_redirects_nonce' );

	$quick_301_redirects_ = array();


	for($i = 0; $i < sizeof($_POST['quick_301_redirects_']['request']); ++$i) {

		$request = sanitize_text_field( trim($_POST['quick_301_redirects_']['request'][$i]) );
		$destination = sanitize_text_field( trim($_POST['quick_301_redirects_']['destination'][$i] ) );

		if ($request != '' && $destination != '') {
			$url = parse_url($request);
			if( isset($url['scheme']) && ( $url['scheme'] == 'https' || $url['scheme'] == 'http' ) ){
			   $request = $request;
			} else {				
				$request = ( substr($request, 0, 1) == '/' ) ? $request : '/' . $request;
			}			
			$quick_301_redirects_[$request] = $destination;
		}
	}

	update_option('quick_301_redirects_', $quick_301_redirects_);

}

/**
 * quick_301_redirects_bulk_upload function
 * Upload Bulk CSV file upload section
 * @return string <html>
 *
 * @Since 1.0
 */
function quick_301_redirects_bulk_upload() {

	if( isset($_POST['quick_301_redirects_bulk_upload']) ) {
		$is_import=true;
		$filetype = wp_check_filetype(  wp_unslash( $_FILES['import_bulk_301_csv']['name'] ) , array(
					'csv' => 'text/csv',
				) );
		if ( ! in_array( $filetype['type'], array('csv' => 'text/csv'), true ) ) {
			echo "<div class='error inline'><p>";
			_e( 'Invalid file type. The importer supports CSV file formats.', 'woocommerce' ) ;
			echo "</p></div>";
			$is_import=false;
		}

		if ($is_import == true) {
			//quick_301_redirects_bulk_import_csv_file();
		}
	}

	$bytes      = apply_filters( 'import_upload_size_limit', wp_max_upload_size() );
	$size       = size_format( $bytes );
	?>
	<div id="quick-301-redirects-bulk-upload" class="stuffbox quick-301-redirects-bulk-upload" style="padding:0 20px 20px 20px;">
		<div class="wrap" dir="ltr">
			<form enctype="multipart/form-data" method="post">
			<h2><?php _e( 'Import 301 Redirects from a CSV file', 'quick-301-redirects' );?></h2>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row" style="text-align:left;">
							<label for="upload"><?php _e( 'Choose a CSV file from your computer:', 'quick-301-redirects' ); ?></label>
						</th>
						<td>
							<input type="file" id="upload" name="import_bulk_301_csv" size="25">
							<input type="hidden" name="action" value="save">
							<input type="hidden" name="max_file_size" value="<?php echo esc_attr( $bytes ); ?>">
							<br>
							<small>
							<?php
								/* translators: %s: maximum upload size */
								printf( __( 'Maximum size: %s', 'quick-301-redirects' ), esc_html( $size ));
								?>
							</small>
						</td>
					</tr>
				</tbody>
			</table>
			<div class="bulk-actions">
					<button type="submit" class="button button-primary button-next" value="<?php esc_attr_e( 'Import CSV File', 'quick-301-redirects' ); ?>" name="quick_301_redirects_bulk_upload"><?php _e( 'Import CSV File', 'quick-301-redirects' ); ?></button>
				</div>
			</form>
		</div>
	</div>
	
	<div class="stuffbox quick-301-redirects-export" style="padding:0 20px 20px 20px;">
		<div class="wrap" dir="ltr">			
			<h3><?php esc_html_e('After saving your changes, you can export all your redirects to a CSV file', 'quick-301-redirects');?></h3>
			<a href="<?php echo plugins_url('export-quick-301-redirects.php?download_file=quick-301-redirects.csv',__FILE__); ?>" class="page-title-action button button-primary button-next"><?php esc_html_e('Export  CSV', 'quick-301-redirects');?></a>			
		</div>
	</div>
	<?php
}

/**
 * quick_301_redirects_bulk_import_csv_file function
 * Save Upload Bulk CSV file data
 * @return array
 *
 * @Since 1.0
 */
function quick_301_redirects_bulk_import_csv_file() {

	if ( isset($_POST['quick_301_redirects_bulk_upload']) &&  !empty($_FILES['import_bulk_301_csv'])  ) {
		
		$quick_301_redirects_ = get_option('quick_301_redirects_');
		$filename = $_FILES["import_bulk_301_csv"]["tmp_name"];

		if ( $_FILES["import_bulk_301_csv"]["size"] > 0 ) {
			$file = fopen($filename, "r");
			$i=0;
			while (($getData = fgetcsv($file, 10000, ",")) !== FALSE) {
				if ( $i != 0 ) {
					$quick_301_redirects_[$getData[0]] = $getData[1];
				}
				$i++;
			}
			fclose($file);
			update_option( 'quick_301_redirects_', $quick_301_redirects_ );
		}
		?>
		<script>
		window.location.href = "<?php echo admin_url('options-general.php?page=quick-301-redirects&bulk_upload_successfull=true');?>";
		</script>
		<?php
	}
}


/**
 * quick_301_redirects_template_redirect function
 * Redirect 301 to Destination URL if found.
 * @return URL
 *
 * @Since 1.0
 */
function quick_301_redirects_template_redirect(){

	$protocol = ( is_ssl()) ? 'https' : 'http';
	$request_url = $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	$user_request = quick_301_redirects_replace( get_option( 'home' ), '', $request_url );
	$quick_301_redirects_ = get_option('quick_301_redirects_');

	if (!empty($quick_301_redirects_)) {
		$redirect = '';
		/* compare user request to each 301 stored in the table */
		foreach ($quick_301_redirects_ as $request => $destination) {
			
			if( urldecode($user_request) == rtrim($request,'/') || rtrim($request_url,'/') == rtrim($request,'/') ) {
				/* comparison redirect */
				$redirect = $destination;
			}

			if ($redirect !== '' && trim($redirect,'/') !== trim($user_request,'/')) {
				/* check if destination needs the domain prepended */
				if ( strpos($redirect,'/') === 0 ){
					$redirect = home_url() . $redirect;
				}
				header ('HTTP/1.1 301 Moved Permanently');
				wp_redirect ( $redirect, 301 );
				exit();
			}
		}
	}
}
add_action( 'template_redirect', 'quick_301_redirects_template_redirect', 0 );

/**
 * quick_301_redirects_replace function
 * replace Request URL to subject slug.
 * @return URL
 *
 * @Since 1.0
 */
function quick_301_redirects_replace( $url_search, $url_replace, $request_url ) {

	$token 		= chr(1);	
	if ( ( $pos = strpos( strtolower($request_url), strtolower($url_search) ) ) !== FALSE ){
		$request_url = substr_replace( $request_url, $token, $pos, strlen($url_search) );		
	}
	return rtrim( str_replace( $token, $url_replace, $request_url ), '/' );
}