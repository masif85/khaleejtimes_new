<?php

/**
 * PropertyMap
 *
 * @link  http://infomaker.se
 * @since PropertyMap 1.0.7
 */
class PropertyMap {
    
    /**
     * @var OcAPI
     */
    private $oc_api;
    
    /**
     * PropertyMap constructor.
     *
     * @param string $file
     *
     * @since 1.0.7
     */
    public function __construct( $file = '' ) {
        add_action( 'admin_init', [ &$this, 'pm_admin_init' ] );
        add_action( 'admin_menu', [ &$this, 'pm_admin_menu' ] );
        
        add_action( 'admin_enqueue_scripts', [ &$this, 'pm_enqueue_script' ] );
        
        if( $file !== '' ) {
            //Make sure we initialize/sanitize the DB when activating/deactivating
            register_activation_hook( $file, [ &$this, 'pm_add_defaults' ] );
            register_deactivation_hook( $file, [ &$this, 'pm_clean_up' ] );
        }
        
        //$this->pm_add_defaults();
        
        $this->oc_api = new OcAPI();
    }
    
    /**
     * @since 1.0.7
     * @return void
     */
    public function pm_enqueue_script() {
        wp_enqueue_script( 'property_map', plugin_dir_url( __FILE__ ) . '../admin-style/js/property_mapp.js', [ 'jquery' ], '1.0', true );
    }
    
    /**
     * @since 1.0.7
     * @return array|false
     */
    public function get_article_property_map() {
        return get_option( 'prop_map' );
    }
    
    /**
     * @since 1.0.7
     * @return void
     */
    public function pm_admin_init() {
        //Uncomment this line when options default has been altered, reload page once then comment out again
        //$this->pm_add_defaults();
        
        //Params for register settings: optiongroup, option name, sanitize callback
        register_setting( 'prop_map', 'prop_map', [ &$this, 'pm_validate_options' ] );
        
    }
    
    /**
     * @since 1.0.7
     * @return void
     */
    public function pm_admin_menu() {
        //Params: parent slug, Page Title, Menu title, capability, menu slug, function callback
        add_submenu_page( 'oc_connection', 'Property Settings', __( 'Property Settings', 'every' ), 'manage_options', 'ew_properties', [
            &$this,
            'pm_options'
        ] );
    }
    
    /**
     * Public function to Setup default mapping
     * Will update option prop_map
     * When altered, call this function from pm_admin_init once and options will be updated
     *
     * @since 1.0.7
     * @return void
     */
    public function pm_add_defaults() {
        update_option( 'prop_map', [
            'Uuid'                 => 'uuid',
            'Headline'             => 'headline',
            'Headline_sub'         => 'headline_sub',
            'Web_headline'         => 'headline',
            'Tablet_headline'      => 'headline',
            'Mobile_headline'      => 'headline',
            'Leadin'               => 'leadin',
            'Text'                 => 'text',
            'Pubdate'              => 'pubdate',
            'Updated'              => 'updated',
            'Author'               => 'author',
            'Imageuuid'            => 'imageuuid',
            'Summary'              => 'articlesummary',
            'Section'              => 'section',
            'Article_pagedateline' => 'article_pagedateline',
            'Factheadline'         => 'factheadline',
            'Factbody'             => 'factbody',
            'Dateline'             => 'dateline',
            'Category'             => 'section'
        ] );
    }
    
    /**
     * Public function to clean DB on deactivation
     * Will delete option prop_map
     *
     * @since 1.0.7
     * @return void
     */
    public function pm_clean_up() {
        delete_option( 'prop_map' );
    }
    
    /**
     * Function to validate options before update
     *
     * @param array $input
     *
     * @since 1.0.7
     * @return array sanitized input array
     */
    public function pm_validate_options( $input ) {
        $clean_input = [];
        
        foreach ( $input as $key => $val ) {
            $clean_input[ $key ] = strtolower( trim( strip_tags( $val ) ) );
        }
        
        return $clean_input;
    }
    
    /**
     * @since 1.0.7
     * @return void
     */
    public function pm_options() {
        $every_properties = $this->get_article_property_map();
        $oc_properties    = $this->oc_api->get_content_types();
        
        ?>
        <div class="wrap">
            <h2 class="oc_Settings_header"> <?php _e( 'Everyware - Open Content Property map', 'every' ); ?></h2>

            <h3> <?php _e( 'Map the Everyware properties to the corresponding Open Content / Admin Properties', 'every' ); ?></h3>

            <div class="alert-info">
                <h4><?php _e( 'If none of the options are correct, set to / leave as "Unknown"', 'every' ); ?></h4>

                <p><?php _e( 'The list on the left are properties used in the Everyware plugin, make sure they point to the correct properties in the right list.', 'every' ); ?></p>
            </div>

            <div class="alert-info unsaved">
                <h4><?php _e( 'You have unsaved changes!', 'every' ); ?></h4>
            </div>
            
            <?php settings_errors(); ?>
            <form method="post" action="options.php" class="oc_prop_form">
                <?php settings_fields( 'prop_map' ); ?>
                <table class="form-table wide-fat ">

                    <tr valign="top">
                        <th>
                            <h3><?php _e( 'Properties', 'every' ) ?></h3>
                        </th>
                    </tr>

                    <tr valign="">
                        <td>
                            <h4>Wordpress</h4>
                        </td>
                        <td>
                            <h4><?php _e( 'Data Source / OC', 'every' ); ?></h4>
                        </td>
                    </tr>
                    
                    <?php
                    if( $every_properties ) {
                        foreach ( $every_properties as $key => $val ) {
                            ?>
                            <tr valign="top">
                                <th scope="row">
                                    <label for="<?php echo $key ?>"> <?php echo $key ?>
                                    </label>
                                </th>
                                <td>
                                    <select id="<?php echo $key ?>" name="prop_map[<?php echo $key ?>]">
                                        <option><?php _e( 'Unknown', 'every' ); ?></option>
                                        <?php
                                        $match = null;
                                        if( isset( $oc_properties[ 'Article' ] ) ) {
                                            foreach ( $oc_properties[ 'Article' ] as $oc_prop ) {
                                                if( isset( $oc_prop[ 0 ] ) && $oc_prop[ 0 ] !== '' ) {
                                                    if( strtolower( trim( $val ) ) === strtolower( trim( $oc_prop[ 0 ] ) ) ) {
                                                        $selected = 'selected';
                                                        $match    = 'direct hit';
                                                    } else {
                                                        $selected = null;
                                                    }
                                                    print '<option ' . $selected . '>' . ucfirst( $oc_prop[ 0 ] ) . '</option>';
                                                }
                                            }
                                        }
                                        ?>
                                    </select>
                                    <?php
                                    if( $match === null ) {
                                        print '<span class="pm_no_match error">' . __( 'No Auto match found, please select manually', 'every' ) . '</span>';
                                    }
                                    ?>
                                </td>
                            </tr>
                            
                            <?php
                        }
                    }
                    ?>

                    <tr valign="top">
                        <td>
                            <input type="submit" class="button-primary" value=" <?php _e( 'Save changes', 'every' ); ?>"/>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        <?php
    }
}