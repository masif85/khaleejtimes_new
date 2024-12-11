<?php
/**
 * Widget base class
 *
 * Class to assists with widget communication with Open Content
 *
 * Also provides utility functions to assists widget rendering
 *
 * @author Infomaker Scandinavie AB
 *
 */

class OcWidgetBase extends WP_Widget {

	public $oc_api;

	const ARTICLES 		= "Article";
	const IMAGES   		= "Image";
	const ADS      		= "Ad";
	const FACETING 		= "faceting";
	const AD_TEXT 		= "Ad Text";
	const SECTION      	= "Section Name";
	const SUB_HEADLINE 	= "Subheading";
	const STARTPAGE 	= "Start page";
	const DATE      	= "Date";
	const TEXT      	= "Text";
	const PAGE_DATELINE = "Page Dateline";
	const LEADIN 		= "Leadin";
	const AUTHOR 		= "Author";
	const SUMMARY 		= "Summary";
	const FACT_BOX      = "Fact box";
    const MADMANSROW    = "Madmansrow";

	/**
	 * Constructor, will call the WP widget-construct
	 */
	function __construct() {
		parent::__construct( false, $this->oc_widget_name() );
		$this->oc_api = new OcAPI();

		//When a widget is removed, make sure we cancel the subscription
		add_action( 'sidebar_admin_setup', array( &$this, 'remove_widget_subscription' ) );
	}

	/**
	 * Function to render widget name
	 *
	 * @return string
	 */
	function oc_widget_name() {
		return 'Every Base Widget';
	}

	/**
	 * Function to get available sort options
	 *
	 * @return array|mixed
	 */
	private function get_sort_options() {
		return $this->oc_api->get_oc_sort_options();
	}

	/**
	 * Function to render widget on Every Board
	 *
	 * @param $instance
	 */
	public function widget_board( $instance ) {

		echo "<p>";
		foreach ( $instance as $key => $value ) {
			if ( strlen( $value ) > 0 ) {
				echo esc_html( $key ) . " => <strong>" . esc_html( $value ) . "</strong><br />";
			}
		}
		echo "</p>";
	}

	/**
	 * Function to enable wp-storing of widget settings
	 *
	 * @param $new_instance
	 * @param $old_instance
	 *
	 * @return mixed
	 */
	function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		//Make sure query-start -limit and text length are Integers, if not set to 0 (allow empty string)
		if ( ! is_int( $new_instance['oc_query_start'] ) && $new_instance['oc_query_start'] !== "" ) {
			$new_instance['oc_query_start'] = intval( $new_instance['oc_query_start'] );
		}

		if ( ! is_int( $new_instance['oc_query_limit'] ) && $new_instance['oc_query_limit'] !== "" ) {
			$new_instance['oc_query_limit'] = intval( $new_instance['oc_query_limit'] );
		}

		if( isset( $new_instance['text_length'] ) ) {
			if ( ! is_int( $new_instance['text_length'] ) ) {
				$new_instance['text_length'] = intval( $new_instance['text_length'] );
			}
		}

		if ( isset( $new_instance['no_image_text_length'] ) && ! is_int( $new_instance['no_image_text_length'] ) ) {
			$new_instance['no_image_text_length'] = intval( $new_instance['no_image_text_length'] );
		}

		//Loop through all properties of new instance and validate/trim and sanitize content
		foreach ( $new_instance as $key => $value ) {

			if ( $key !== "oc_query" ) {
				$instance[$key] = wp_filter_nohtml_kses( trim( $value ) ); // Sanitize text input (strip html tags, and escape characters)
			} else {
				$instance[$key] = strip_tags( trim( $value ) ); //This is the oc_query being stripped of tags and trimmed, not url encoded or escaped
			}
		}

		//Set/unset checkboxes for opencontent search, ads include text and OC Event subscription
		if ( ! array_key_exists( OcWidgetBase::ARTICLES, $new_instance ) ) {
			$instance[OcWidgetBase::ARTICLES] = false;
		}

		if ( ! array_key_exists( OcWidgetBase::IMAGES, $new_instance ) ) {
			$instance[OcWidgetBase::IMAGES] = false;
		}

		if ( ! array_key_exists( OcWidgetBase::ADS, $new_instance ) ) {
			$instance[OcWidgetBase::ADS] = false;
		}

		if ( ! array_key_exists( OcWidgetBase::FACETING, $new_instance ) ) {
			$instance[OcWidgetBase::FACETING] = false;
		}

		if ( ! array_key_exists( OcWidgetBase::AD_TEXT, $new_instance ) ) {
			$instance[OcWidgetBase::AD_TEXT] = false;
		}

		if ( ! array_key_exists( OcWidgetBase::SECTION, $new_instance ) ) {
			$instance[OcWidgetBase::SECTION] = false;
		}

		if ( ! array_key_exists( OcWidgetBase::SUB_HEADLINE, $new_instance ) ) {
			$instance[OcWidgetBase::SUB_HEADLINE] = false;
		}

		if ( ! array_key_exists( OcWidgetBase::STARTPAGE, $new_instance ) ) {
			$instance[OcWidgetBase::STARTPAGE] = false;
		}

		if ( ! array_key_exists( OcWidgetBase::DATE, $new_instance ) ) {
			$instance[OcWidgetBase::DATE] = false;
		}

		if ( ! array_key_exists( OcWidgetBase::TEXT, $new_instance ) ) {
			$instance[OcWidgetBase::TEXT] = false;
		}

		if ( ! array_key_exists( OcWidgetBase::PAGE_DATELINE, $new_instance ) ) {
			$instance[OcWidgetBase::PAGE_DATELINE] = false;
		}

		if ( ! array_key_exists( OcWidgetBase::LEADIN, $new_instance ) ) {
			$instance[OcWidgetBase::LEADIN] = false;
		}

		if ( ! array_key_exists( OcWidgetBase::AUTHOR, $new_instance ) ) {
			$instance[OcWidgetBase::AUTHOR] = false;
		}

		if ( ! array_key_exists( OcWidgetBase::SUMMARY, $new_instance ) ) {
			$instance[OcWidgetBase::SUMMARY] = false;
		}

		if ( ! array_key_exists( OcWidgetBase::FACT_BOX, $new_instance ) ) {
			$instance[OcWidgetBase::FACT_BOX] = false;
		}

		if ( ! array_key_exists( OcWidgetBase::MADMANSROW, $new_instance ) ) {
			$instance[OcWidgetBase::MADMANSROW] = false;
		}

		if ( ! array_key_exists( 'oc_event_subscribe', $new_instance ) ) {
			$instance['oc_event_subscribe'] = false;
			$this->deactivate_widget_subscription( $this->id );
		} else {
			$notifier_query = $this->oc_api->prepare_notifier_query( $instance );
			$this->activate_widget_subscription( $this->id, $notifier_query, $instance['oc_post_id'] );
		}

		//Make sure we flush the cached result for this widget
		$suggest = false;
		if ( isset( $instance['oc_suggest_field'] ) && $instance['oc_suggest_field'] !== '' ) {
			$suggest = $instance['oc_suggest_field'];
		}

		if ( $suggest === false ) {
			$this->oc_api->prepare_widget_query( $instance );
		} else {
			$this->oc_api->prepare_suggest_query( $instance );
		}
		$this->oc_api->update_widget_cache();

		//Fire of all functions hooked into "widget saved" to flush cache
		do_action( 'every_widget_saved_flush', $instance['oc_post_id'], $this->id );

		return $instance;
	}

	/**
	 * Function to subscribe to OC Events
	 * Notifier holds all knowledge about how and when
	 *
	 * @scope public
	 *
	 * @param $id
	 * @param $query_string
	 * @param $page_id
	 */
	public function activate_widget_subscription( $id, $query_string, $page_id ) {

		$notifier = new OcNotifier();

		if ( $notifier->check_if_valid_url() ) {
			$notifier->subscribe_widget( $id, $query_string, $page_id );
		}
	}

	/**
	 * Function to un-subscribe to OC Events when checkbox is un-checked on widget
	 * Notifier holds all knowledge about how and when
	 *
	 * @scope public
	 *
	 * @param $widget_id
	 */
	function deactivate_widget_subscription( $widget_id ) {

		$notifier = new OcNotifier();

		if ( $notifier->check_if_valid_url() ) {
			$notifier->unsubscribe_widget( $widget_id );
		}
	}

	/**
	 * Function to unsubscribe to OC Events when a widget is deleted/removed
	 * This function is activated with add_action in construct
	 *
	 * @scope public
	 */
	function remove_widget_subscription() {
		if ( 'post' == strtolower( $_SERVER['REQUEST_METHOD'] ) ) {

			if ( isset( $_POST['delete_widget'] ) ) {
				if ( 1 === (int) $_POST['delete_widget'] ) {

					$widget_id = $_POST['widget-id'];
					$this->deactivate_widget_subscription( $widget_id ); //This wont have any effect anymore, we dont use widget ID as key in cache
					OcConnection::clear_widget_cache( $widget_id );
				}
			}

		}
	}

	/*
	|-----------------------------------------------------------------------------------
	| Utility Functions for rendering of editable widget settings fields on admin page
	|-----------------------------------------------------------------------------------
	*/

	/**
	 * Function to render Widget Title
	 *
	 * @param $instance
	 */
	public function title_field( $instance ) {
		echo '<p>';
		echo '<label for="' . $this->get_field_id( 'title' ) . '">';
		echo __('Title:', 'every') . '<input class="widefat" id="' . $this->get_field_id( 'title' ) . '" name="' . $this->get_field_name( 'title' ) . '" type="text" value="' . esc_attr( ( isset( $instance['title'] ) ? $instance['title'] : "" ) ) . '" />';
		echo '</label>';
		echo '</p>';
	}

	/**
	 * Function to render Oc Query Field
	 * Optional fields are start, limit and sort
	 * Function will include Query builder and functionality to test a query
	 *
	 * @param      $instance
	 * @param bool $use_start_limit
	 * @param bool $use_sort
	 */
	public function oc_query_field( $instance, $use_start_limit = true, $use_sort = true ) {

		echo '<fieldset class="oc_fieldset">';
		echo '<legend><strong>' . _e("Open Content Query", "every") . '</strong></legend>';
		echo '<input type="hidden" name="widget-width" class="widget-width" value="350">';
		echo '<p>';
		echo '<label for="' . $this->get_field_id( 'oc_query' ) . '">';
		echo __('Query:', 'every') .  '<textarea rows="5" class="widefat oc_query_text_area" id="' . $this->get_field_id( 'oc_query' ) . '" name="' . $this->get_field_name( 'oc_query' ) . '" >' . esc_attr( ( isset( $instance['oc_query'] ) ? $instance['oc_query'] : "" ) ) . '</textarea>';
		echo '</label>';
		echo '</p>';

		//This is the Start-Limit part of the query box
		if ( $use_start_limit === true ) {
			echo '<div class="start_limit_div">';
			echo '<p class="left start_p">';
			echo '<label for="' . $this->get_field_id( 'oc_query_start' ) . '">';
			echo 'Start: <input class="oc_query_start" type="text" value="' . esc_attr( ( isset( $instance['oc_query_start'] ) ? $instance['oc_query_start'] : 0 ) ) . '" name="' . $this->get_field_name( 'oc_query_start' ) . '" id="' . $this->get_field_id( 'oc_query_start' ) . '" maxlength="4" size="4" />';
			echo '</label>';
			echo '</p>';

			echo '<p class="left">';
			echo '<label for="' . $this->get_field_id( 'oc_query_limit' ) . '">';
			echo __('Limit:', 'every') . '<input class="oc_query_limit" type="text" value="' . esc_attr( ( isset( $instance['oc_query_limit'] ) ? $instance['oc_query_limit'] : 10 ) ) . '" name="' . $this->get_field_name( 'oc_query_limit' ) . '" id="' . $this->get_field_id( 'oc_query_limit' ) . '" maxlength="4" size="4" />';
			echo '</label>';
			echo '</p>';
			echo '</div>';
		}
		//End start-Limit

		echo '<div class="clearfix"></div>';

		//This is the Sort part of the query box
		if ( $use_sort === true ) {
			$this->sort_field($instance);
		}
		//End sort

		echo '<p>';
		echo '<div class="ajax_result_div"></div>';
		echo '<div id="modal_query_builder" class="dialog_window"></div>';

		echo '<a href="#"	class="button-primary query_builder_link" style="margin: 0 10px 0 0 !important;">' . __('Query Builder', 'every') . '</a>';
		echo '<a href="' . EVERY_BASE . 'admin-style/images/ajax-loader.gif" class="button-primary ajax_test_query_link" style="margin-top: 0px !important;">' . __('Test query', 'every') . '</a>';
		echo '</label>';
		echo '</p>';

		echo '</fieldset>';

		//Post ID is used by Notifier to generate a unique ID for the page
		if ( isset( $_GET['post'] ) ) {
			echo '<input type="hidden" id="' . $this->get_field_id( 'oc_post_id' ) . '" name="' . $this->get_field_name( 'oc_post_id' ) . '" value="' . $_GET['post'] . '" />';
		}
	}

	/**
	 * Function to render text length field
	 * This will set text length on article with Images
	 *
	 * @param $instance
	 */
	public function text_length_field( $instance ) {
		echo '<p>';
		echo '<label for="' . $this->get_field_id( 'text_length' ) . '">';
		echo __('Text Max Length:', 'every') . '<input class="widefat" id="' . $this->get_field_id( 'text_length' ) . '" name="' . $this->get_field_name( 'text_length' ) . '" type="text" value="' . esc_attr( ( isset( $instance['text_length'] ) ? $instance['text_length'] : 0 ) ) . '" />';
		echo '</label>';
		echo '</p>';
	}

	/**
	 * Function to render No image text field
	 * This will set text length on article without Images
	 *
	 * @param $instance
	 */
	public function no_image_text_length_field( $instance ) {
		echo '<p>';
		echo '<label for="' . $this->get_field_id( 'no_image_text_length' ) . '">';
		echo __('NO Image Text Max Length:', 'every') . '<input class="widefat" id="' . $this->get_field_id( 'no_image_text_length' ) . '" name="' . $this->get_field_name( 'no_image_text_length' ) . '" type="text" value="' . esc_attr( ( isset( $instance['no_image_text_length'] ) ? $instance['no_image_text_length'] : 0 ) ) . '" />';
		echo '</label>';
		echo '</p>';
	}

	/**
	 * Function to render leadin length field
	 *
	 * @param $instance
	 */
	public function leadin_length_field( $instance ) {
		echo '<p>';
		echo '<label for="' . $this->get_field_id( 'leadin_length' ) . '">';
		echo __('Leadin Max Length:', 'every') . '<input class="widefat" id="' . $this->get_field_id( 'leadin_length' ) . '" name="' . $this->get_field_name( 'leadin_length' ) . '" type="text" value="' . esc_attr( ( isset( $instance['leadin_length'] ) ? $instance['leadin_length'] : 0 ) ) . '" />';
		echo '</label>';
		echo '</p>';
	}

	/**
	 * Function to render "Image Type" select box
	 *
	 * @param $instance
	 */
	public function image_type_field( $instance ) {
		$selected = 'selected="selected"';
		echo '<p>';
		echo '<label for="' . $this->get_field_id( 'image_type' ) . '">';
		echo __('Image Type:', 'every') . ' ';

		echo '<select id="' . $this->get_field_id( 'image_type' ) . '" name="' . $this->get_field_name( 'image_type' ) . '"><br />';
		echo '<option value="none" ' . ( esc_attr( ( isset( $instance['image_type'] ) ? $instance['image_type'] : "" ) ) == "none" ? $selected : null ) . '>' . __('None', 'every') . '</option>';
		echo '<option value="thumbnail" ' . ( esc_attr( ( isset( $instance['image_type'] ) ? $instance['image_type'] : "" ) ) == "thumbnail" ? $selected : null ) . '>' . __('Thumbnail', 'every') . '</option>';
		echo '<option value="preview" ' . ( esc_attr( ( isset( $instance['image_type'] ) ? $instance['image_type'] : "" ) ) == "preview" ? $selected : null ) . '>' . __('Preview', 'every') . '</option>';
		echo '<option value="source" ' . ( esc_attr( ( isset( $instance['image_type'] ) ? $instance['image_type'] : "" ) ) == "source" ? $selected : null ) . '>' . __('Source', 'every') . '</option>';
		echo '</select>';

		echo '</label>';
		echo '</p>';
	}

	/**
	 * Function to render "display order" field
	 * Used to determine if article headline or Image should be rendered first in HTML output
	 *
	 * @param $instance
	 */
	public function display_order_field( $instance ) {
		$selected = 'selected="selected"';
		echo '<p>';
		echo '<label for="' . $this->get_field_id( 'display_order' ) . '">';
		echo 'Display Order: ';

		echo '<select id="' . $this->get_field_id( 'display_order' ) . '" name="' . $this->get_field_name( 'display_order' ) . '"><br />';
		echo '<option value="headline" ' . ( esc_attr( ( isset( $instance['display_order'] ) ? $instance['display_order'] : "" ) ) == "headline" ? $selected : null ) . '>' . __('Headline first', 'every') . '</option>';
		echo '<option value="image" ' . ( esc_attr( ( isset( $instance['display_order'] ) ? $instance['display_order'] : "" ) ) == "image" ? $selected : null ) . '>' . __('Image First', 'every') . '</option>';
		echo '</select>';

		echo '</label>';
		echo '</p>';
	}

	/**
	 * Function to read and render all available templates as select
	 *
	 * @param $instance
	 */
	public function article_template( $instance ) {
		$selected = 'selected="selected"';

		$available_templates = EveryBoard_Settings::get_templates();

		echo '<p>';
		echo '<label for="' . $this->get_field_id( 'article_template' ) . '">';
		echo __('Article template:', 'every');

		echo '<select id="' . $this->get_field_id( 'article_template' ) . '" name="' . $this->get_field_name( 'article_template' ) . '"><br />';

		foreach ( $available_templates as $key => $value ) {
			echo '<option value="' . $value . '" ' . ( esc_attr( ( isset( $instance['article_template'] ) ? $instance['article_template'] : "" ) ) == $value ? $selected : null ) . '>' . $key . '</option>';
		}


		echo '</select>';

		echo '</label>';
		echo '</p>';
	}

    public function image_template( $instance ) {
            $selected = 'selected="selected"';
            $available_templates = EveryBoard_Settings::get_image_templates();
        ?>

        <p>
        <label for="<?php echo $this->get_field_id( 'image_template' ); ?>">
        <?php echo __('Image template:', 'every'); ?>

        <select id="<?php echo $this->get_field_id( 'image_template' );?>" name="<?php echo $this->get_field_name( 'image_template' );?>"><br />
            <?php foreach ( $available_templates as $key => $value ) :?>
                <option value="<?php echo $value;?>" <?php echo ( esc_attr( ( isset( $instance['image_template'] ) ? $instance['image_template'] : "" ) ) == $value ? $selected : null );?>>
                    <?php echo $key;?>
                </option>
            <?php endforeach; ?>
        </select>

        </label>
        </p>
        <?php
    }

    public function template_usage_option( $instance ) {
        $selected = 'selected="selected"';
        ?>
            <p>
                <label for="<?php $this->get_field_id('template_data_input');?>">
                    <?php _e('Template data input:', 'every'); ?>
                    <select id="<?php echo $this->get_field_id('template_data_input');?>" name="<?php echo $this->get_field_name('template_data_input'); ?>">
                        <option value="single" <?php echo isset($instance['template_data_input']) && $instance['template_data_input'] === 'single' ? $selected : ''; ?>><?php _e('Single', 'every');?></option>
                        <option value="all" <?php echo isset($instance['template_data_input']) && $instance['template_data_input'] === 'all' ? $selected : ''; ?>><?php _e('All', 'every');?></option>
                    </select>
                </label>
            </p>
        <?php
    }

	/**
	 * Function to render Sort field
	 *
	 * @param $instance
	 */
	public function sort_field( $instance ) {
		$selected = 'selected="selected"';

		$sorting_options = $this->get_sort_options();
		$saved_sorting_option = esc_attr($instance['oc_query_sort']  ?? '');
		$saved_option_available = false;
		foreach($sorting_options->sortings as $sorting){
			if($sorting === $saved_sorting_option){
				$saved_option_available = true;
				break;
			}
		}

		echo '<div class="sort_by_div">';
		echo '<p>';
		echo '<label for="' . $this->get_field_id( 'oc_query_sort' ) . '">';
		echo __('Sorting:', 'every') . '<br />';
		echo '<select class="sort_select" id="' . $this->get_field_id( 'oc_query_sort' ) . '" name="' . $this->get_field_name( 'oc_query_sort' ) . '"><br />';

		if(!$saved_option_available){
			echo '<option value="" ></option>';
		}

		foreach ( $sorting_options->sortings as $sorting ) {
			if ( $sorting->contentType == null ) {
				echo '<option value="' . $sorting->name . '" ' . ( $saved_sorting_option === $sorting->name ? $selected : null ) . '>' . $sorting->name . '</option>';
			}
		}

		echo '</select>';
		echo '</label>';
		echo '</p>';
		echo '</div>';
	}

	/**
	 * Function to render css class field
	 *
	 * @param $instance
	 */
	public function css_classes_field( $instance ) {
		echo '<p>';
		echo '<label for="' . $this->get_field_id( 'css_classes' ) . '">';
		echo __('CSS classes (for each item):', 'every') . '<input class="widefat" id="' . $this->get_field_id( 'css_classes' ) . '" name="' . $this->get_field_name( 'css_classes' ) . '" type="text" value="' . esc_attr( ( isset( $instance['css_classes'] ) ? $instance['css_classes'] : "" ) ) . '" />';
		echo '</label>';
		echo '</p>';
	}

	/**
	 * Function to render a text field
	 *
	 * @param $instance
	 * @param $option_name
	 * @param $label
	 */
	public function text_field( $instance, $option_name, $label ) {
		echo '<p>';
		echo '<label for="' . $this->get_field_id( $option_name ) . '">';
		echo $label . ': <input class="widefat" id="' . $this->get_field_id( $option_name ) . '" name="' . $this->get_field_name( $option_name ) . '" type="text" value="' . esc_attr( ( isset( $instance[$option_name] ) ? $instance[$option_name] : "" ) ) . '" />';
		echo '</label>';
		echo '</p>';
	}

	/**
	 * Function to render checkboxes
	 *
	 * @scope public
	 *
	 * @param $instance
	 * @param $options_array
	 */
	public function check_box_field( $instance, $options_array ) {
		$checked = "checked='checked'";

		echo '<p>';
		echo '<strong>' . __('Include / Use:', 'every') . '</strong> <br />';
		foreach ( $options_array as $option ) {
			echo '<input type="checkbox" id="' . $this->get_field_id( $option ) . '" name="' . $this->get_field_name( $option ) . '" value="true" ' . ( isset( $instance[$option] ) && $instance[$option] == 'true' ? $checked : null ) . ' /> <label for="' . $this->get_field_id( $option ) . '">' . $option . '</label><br />';
		}
		echo '</p>';
	}

	/**
	 * Function to render choice of faceting or not
	 *
	 * @scope public
	 *
	 * @param $instance
	 */
	public function facet_field( $instance ) {
		$checked = "checked='checked'";
		echo '<p>';
		echo __('Enable faceting:', 'every');
		echo '<input type="checkbox" id="' . $this->get_field_id( 'faceting' ) . '" name="' . $this->get_field_name( 'faceting' ) . '" value="true" ' . ( isset( $instance['faceting'] ) && $instance['faceting'] == 'true' ? $checked : null ) . ' />';
		echo '</p>';
	}
}
