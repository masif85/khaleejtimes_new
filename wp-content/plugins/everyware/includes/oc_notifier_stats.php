<?php

class OcNotifierStats {

	private $FLUSH_OPTION = 'EV_FLUSH_STAT';
	private $oc;

	public function __construct() {
//		$this->oc = new OpenContent();
		$this->oc = OpenContent::getInstance();

		$this->FLUSH_OPTION .= get_option( 'blogname' );

		if ( isset( $_POST['empty_event_logs'] ) ) {
			delete_transient( $this->FLUSH_OPTION );
		}

		add_action( 'oc_get_remote_content', array( &$this, 'register_oc_get_remote_content' ), 10, 2 );
		add_action( 'oc_get_json_hook', array( &$this, 'register_oc_get_json_hook' ), 10, 2 );

		add_action( 'every_frontcache_flush_hook', array( &$this, 'register_every_frontcache_flush_hook' ), 10, 2 );

		add_action( 'oc_event_listener_pre_purge', array( &$this, 'register_notifier_post_flush_event' ), 10, 2 );
		add_action( 'clear_widget_cache_hook', array( &$this, 'register_clear_widget_cache_hook' ), 10, 2 );
		add_action( 'update_widget_cache_hook', array( &$this, 'register_updated_widget_cache_hook' ), 10, 2 );

		add_action( 'admin_menu', array( &$this, 'flush_stats_menu' ) );
		add_action( 'admin_init', array( &$this, 'ons_init' ) );
	}

	public function ons_init() {
		if ( ! is_array( get_transient( $this->FLUSH_OPTION ) ) ) {
			set_transient( $this->FLUSH_OPTION, array(), 2 * DAY_IN_SECONDS );
		}
	}

	public function flush_stats_menu() {
		//Params: parent slug, Page Title, Menu title, capability, menu slug, function callback
		add_submenu_page( 'oc_connection', 'Notifier Stats', __('Notifier Stats', 'every'), 'manage_options', 'not_stats', array( &$this, 'onf_options' ) );
	}

	public function register_clear_widget_cache_hook( $result, $key ) {
		$event_data = array(
			'page'   => null,
			'result' => $result,
			'key'    => $key,
			'date'   => date( 'Y-m-d H:i:s:u' ),
			'origin' => 'OcCon Flush cache'
		);

		$this->add_flush_data( $event_data );
	}

	public function register_updated_widget_cache_hook( $query, $key ) {
		$event_data = array(
			'page'   => null,
			'result' => $query,
			'key'    => $key,
			'date'   => date( 'Y-m-d H:i:s:u' ),
			'origin' => 'OcCon Update cache'
		);

		$this->add_flush_data( $event_data );
	}

	public function register_notifier_post_flush_event( $page_id, $oc_query ) {
		$event_data = array(
			'page'   => $page_id,
			'result' => 1,
			'key'    => md5( $this->oc->getOcBaseUrl() . 'search?' . $oc_query ),
			'date'   => date( 'Y-m-d H:i:s:u' ),
			'origin' => 'Oc Notifier'
		);

		$this->add_flush_data( $event_data );
	}

	public function register_oc_get_remote_content( $result, $key ) {
		$event_data = array(
			'page'   => null,
			'result' => $result,
			'key'    => $key,
			'date'   => date( 'Y-m-d H:i:s:u' ),
			'origin' => 'Oc Get Remote Content'
		);

		$this->add_flush_data( $event_data );
	}

	public function register_oc_get_json_hook( $result, $key ) {
		$event_data = array(
			'page'   => null,
			'result' => $result,
			'key'    => $key,
			'date'   => date( 'Y-m-d H:i:s:u' ),
			'origin' => 'Oc Get Cache JSON'
		);

		$this->add_flush_data( $event_data );
	}

	public function register_every_frontcache_flush_hook( $post_id, $front_id ) {
		$event_data = array(
			'page'   => null,
			'result' => ( $post_id === $front_id ),
			'key'    => $front_id . ' ' . $post_id,
			'date'   => date( 'Y-m-d H:i:s:u' ),
			'origin' => 'Front cache flush hook'
		);

		$this->add_flush_data( $event_data );
	}

	private function add_flush_data( $data_array ) {
		$data = get_transient( $this->FLUSH_OPTION );
		if ( ! is_array( $data ) ) {
			$data = array();
		}
		array_push( $data, $data_array );

		set_transient( $this->FLUSH_OPTION, $data, 2 * DAY_IN_SECONDS );
	}

	private function read_flush_data() {
		return get_transient( $this->FLUSH_OPTION );
	}

	public function onf_options() {
		$stats = $this->read_flush_data();
		?>

		<div class="wrap">
			<h2 class="oc_Settings_header">Cache flush Stats</h2>
			<br />

			<form action="" method="POST">
				<input id="empty_event_logs_button" type="submit" class="button-primary" value="Empty Log" />
				<input type="hidden" value="empty_event_logs" id="empty_event_logs" name="empty_event_logs" />
			</form>

			<br>

			<?php settings_errors(); ?>

			<table id="exceptions_table" class="sortable error_log_display_table wp-list-table widefat fixed posts">
				<thead>
				<tr>
					<th>Date:</th>
					<th>Origin:</th>
					<th>Result:</th>
					<th>Key:</th>
					<th>Page:</th>
				</tr>
				</thead>
				<tbody>
				<?php

				if ( is_array( $stats ) ) {
					foreach ( $stats as $stat ) {
						if ( ! empty( $stat ) ) {
							echo '<tr>';
							echo '<td class="error_display_date">' . $stat['date'] . '</td>';
							echo '<td>' . $stat['origin'] . '</td>';
							echo '<td>' . $stat['result'] . '</td>';
							echo '<td>' . $stat['key'] . '</td>';
							echo '<td>' . $stat['page'] . '</td>';
							echo '</tr>';
						}
					}

				} else {
					echo '<h3>' . __("Nothing logged", "every"). '</h3>';
				}
				?>
				</tbody>
			</table>

		</div>

	<?php
	}

}