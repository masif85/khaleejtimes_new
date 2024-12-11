<?php

class EnvSettings {
    
    /**
     * @var string
     */
    private $use_auth;
    
    /**
     * @var string
     */
	private $use_nginx;
    
    /**
     * @var string
     */
	private $use_debug;
    
    /**
     * @var string
     */
	private $env_query;
    
    /**
     * @var string
     */
    private $use_everystats;
    
    /**
     * @var bool
     */
	private $cache_flushed;
    
    /**
     * @var string
     */
    private $category_property;
    
    /**
     * @var string
     */
	private $pubdate_future_hide;
    
    /**
     * @var string
     */
	private $use_trashed;
    
    /**
     * @var string
     */
	private $use_hierarchy_categories;
    
    /**
     * Public function to get "Use Auth" setting
     *
     * @return boolean
     */
	public function get_use_auth() {
		return $this->use_auth === 'true';
	}
    
    /**
     * Public function to get "Use nginx" setting
     *
     * @return boolean
     */
	public function get_use_nginx(){
		return $this->use_nginx === 'true';
	}
    
    /**
     * Public function to get "Use Debug" setting
     *
     * @return boolean
     */
	public function get_use_debug(){
		return $this->use_debug === 'true';
	}
    
    /**
     * Public function to get "Environment query" setting
     *
     * @return boolean
     */
	public function get_use_everystats(){
		return $this->use_everystats === 'true';
	}
    
    /**
     * Public function to get "Environment query" setting
     *
     * @return boolean
     */
	public function get_env_query(){
		return $this->env_query;
	}
    
    /**
     * Public function to get the category property setting
     *
     * @return string
     */
    public function get_category_property(){
        return strtolower( $this->category_property );
    }
    
    /**
     * Public function to get the pubdate future hide setting
     *
     * @return string
     */
	public function get_pubdate_hide_property(){
		return $this->pubdate_future_hide === 'true';
	}
    
    /**
     * Public function to get "Use Trashed" setting
     *
     * @return bool
     */
	public function get_use_trashed() {
		return $this->use_trashed === 'true';
	}
    
    /**
     * Public function to get "Use hierarchy categories" setting
     *
     * @return bool
     */
	public function get_use_hierarchy_categories() {
		return $this->use_hierarchy_categories === 'true';
	}

	/**
	 * Public Constructor
	 *
	 * @param string $file
	 */
	public function __construct( $file = '' ) {
        
        $options                        = get_option( 'ev_env_settings' );
        $this->use_auth                 = isset( $options[ 'use_auth' ] ) ? $options[ 'use_auth' ] : 'false';
        $this->use_nginx                = isset( $options[ 'use_nginx' ] ) ? $options[ 'use_nginx' ] : 'false';
        $this->use_debug                = isset( $options[ 'use_debug' ] ) ? $options[ 'use_debug' ] : 'false';
        $this->use_everystats           = isset( $options[ 'use_everystats' ] ) ? $options[ 'use_everystats' ] : 'false';
        $this->env_query                = isset( $options[ 'env_query' ] ) ? $options[ 'env_query' ] : '';
        $this->category_property        = isset( $options[ 'category_prop' ] ) ? $options[ 'category_prop' ] : 'category';
        $this->pubdate_future_hide      = isset( $options[ 'pubdate_hide' ] ) ? $options[ 'pubdate_hide' ] : 'true';
        $this->use_trashed              = isset( $options[ 'use_trashed' ] ) ? $options[ 'use_trashed' ] : 'false';
        $this->use_hierarchy_categories = isset( $options[ 'use_hierarchy_categories' ] ) ? $options[ 'use_hierarchy_categories' ] : 'false';
        $this->cache_flushed            = false;

		if ( $this->use_nginx ) {
			add_filter( 'got_rewrite', '__return_true' );
		}

		if ( null !== $file && $file !== '' ) {
			register_activation_hook( $file, array( &$this, 'ev_env_add_defaults' ) );
			add_action( 'admin_init', array( &$this, 'ev_env_init' ) );
			add_action( 'admin_menu', array( &$this, 'ev_env_menu' ) );
		}
	}
    
    /**
     * Function to render menu item
     *
     * @return void
     */
	public function ev_env_menu() {
		add_submenu_page( 'oc_connection', 'Environment', __('Environment', 'every'), 'manage_options', 'ev_env_settings', array( &$this, 'ev_env_options' ) );
	}
    
    /**
     * Function to add default values when plugin is activated
     *
     * @return void
     */
	public function ev_env_add_defaults() {
		update_option( 'ev_env_settings', array(
			'use_auth'          => 'false',
			'use_nginx'         => 'false',
			'use_debug'			=> 'false',
            'category_property' => 'category',
			'env_query'         => '',
			'pubdate_hide'      => 'true',
			'use_trashed'       => 'false'
		) );
	}
    
    /**
     * Function to add options group and option name
     *
     * @return void
     */
	public function ev_env_init() {
		register_setting( 'ev_env_settings', 'ev_env_settings', array( &$this, 'ev_env_validate_options' ) );
	}
    
    /**
     * Function to Sanitize input before Options are saved
     *
     * @param $input
     *
     * @return array
     */
	public function ev_env_validate_options( $input ) {
		return $input;
	}
    
    /**
     * Function to render Options page
     *
     * @return void
     */
	public function ev_env_options() {

        $oc_api         = new OcAPI();
        $article_props  = $oc_api->get_contenttype_properties( 'Article' );
		?>
		<div class="wrap">
			<h2 class="oc_Settings_header"><?php _e('Environment Options', 'every');?></h2>

			<?php settings_errors(); ?>
			<form method="post" action="options.php" class="ev_env_settings_form">
				<?php settings_fields( 'ev_env_settings' ); ?>

				<?php
					if ( $this->cache_flushed ) {
						echo '<div class="updated cache_flushed"><p>';
							echo __('JSON Cache Flushed', 'every');
						echo '</p></div>';
					}
				?>
				<table class="form-table wide-fat ">

					<tr valign="top">
						<th>
							<label for="use_auth"><?php _e('Use Auth:', 'every');?></label>

							<p class="description"><?php _e('Default set to false', 'every');?></p>
						</th>
						<td>
                            <label><input type="radio" name="ev_env_settings[use_auth]" value="true" id="use_auth" <?php checked( $this->use_auth === 'true' ); ?>/> <?php _e('Yes', 'every');?></label><br>
                            <label><input type="radio" name="ev_env_settings[use_auth]" value="false" <?php checked( $this->use_auth === 'false' ); ?>/> <?php _e('No', 'every'); ?></label>
						</td>
					</tr>

					<tr valign="top">
						<th>
							<label for="use_auth"><?php _e('Use Trashed:', 'every');?></label>

							<p class="description"><?php _e('If true, board searches and manual articles will look at Open Content property "EveryTrashed" to see if an article is in the trash or not. Default set to false.', 'every');?></p>
						</th>
						<td>
							<label><input type="radio" name="ev_env_settings[use_trashed]" value="true" id="use_trashed" <?php checked( $this->use_trashed === 'true' ); ?>/> <?php _e('Yes', 'every');?></label><br>
							<label><input type="radio" name="ev_env_settings[use_trashed]" value="false" <?php checked( $this->use_trashed === 'false' ); ?>/> <?php _e('No', 'every'); ?></label>
						</td>
					</tr>

					<tr valign="top">
						<th>
							<label for="use_nginx"><?php _e('Use Nginx:', 'every');?></label>

							<p class="description"><?php _e('This option is used to add rewrite rules to remove index.php', 'every');?></p>
						</th>
						<td>
							<label><input type="radio" name="ev_env_settings[use_nginx]" value="true" id="use_nginx" <?php checked( $this->use_nginx === 'true' ); ?>/> <?php _e('Yes', 'every');?></label><br>
							<label><input type="radio" name="ev_env_settings[use_nginx]" value="false" <?php checked( $this->use_nginx === 'false' ); ?>/> <?php _e('No', 'every');?></label>
						</td>
					</tr>

					<tr valign="top">
						<th>
							<label for="use_debug"><?php _e('Use Debug Mode:', 'every');?></label>

							<p class="description"><?php _e('If this option is active, the plugins will activate more debug functionality for troubleshooting. Default: No', 'every');?></p>
						</th>
						<td>
							<label><input type="radio" name="ev_env_settings[use_debug]" value="true" id="use_debug" <?php checked( $this->use_debug === 'true' ); ?>/> <?php _e('Yes', 'every');?></label><br>
							<label><input type="radio" name="ev_env_settings[use_debug]" value="false" <?php checked( $this->use_debug === 'false' ); ?>/> <?php _e('No', 'every');?></label>
						</td>
					</tr>

					<tr valign="top">
						<th>
							<label><?php _e('Use EveryStats:', 'every');?></label>

							<p class="description"><?php _e('If this is enabled, all articles will have a custom field with the view count of the article.', 'every');?></p>
						</th>
						<td>
							<label><input type="radio" name="ev_env_settings[use_everystats]" value="true" <?php checked( $this->use_everystats === 'true' ); ?>/> <?php _e('Yes', 'every');?></label><br>
							<label><input type="radio" name="ev_env_settings[use_everystats]" value="false" <?php checked( $this->use_everystats === 'false' ); ?>/> <?php _e('No', 'every');?></label>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row">
							<label for="env_query"><?php _e('Environment Query:', 'every');?></label>

							<p class="description"><?php _e('Add a query-rule that will be applied to all queries before they are sent to Open Content', 'every');?></p>
						</th>

						<td>
							<textarea rows="5" cols="80" id="env_query" name="ev_env_settings[env_query]"><?php echo esc_attr( $this->env_query ); ?></textarea>
							<p class="description">'Ex: AND Product:"xxx"  <br> OR Pubdate:"xxx"  <br> AND Status NOT "xxx"'</p>
						</td>
					</tr>

                    <tr valign="top">
                        <th scope="row">
                            <label for="category_prop"><?php _e('Category property:', 'every'); ?></label>
                        </th>

                        <td>
                            <select id="category_prop" name="ev_env_settings[category_prop]]">
                                <option>None</option>
                                <?php foreach ( $article_props as $prop ) : ?>
                                    <option<?php selected( strtolower( trim( $this->category_property ) ), strtolower( trim( $prop ) ) ); ?>><?php print ucfirst( $prop ); ?></option>
                                <?php endforeach; ?>
                            </select>

                            <p class="description" style="max-width: 500px;">
                                <?php _e('Choose an Open Content property to be used as the category for articles. This data will be added to the category taxonomy for the articles created in Wordpress and can for example be used in the URL for the article.', 'every' ); ?>
                            </p>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">
                            <label><?php _e('Use hierarchy categories:', 'every'); ?></label>
                            <p class="description">
                                <?php _e('If this option is active, Everyware will create categories hierarchical. Default: No', 'every');?>
                            </p>
                        </th>
                        <td>
                            <label><input type="radio" name="ev_env_settings[use_hierarchy_categories]" value="true" <?php checked( $this->use_hierarchy_categories === 'true' ); ?>/> <?php _e('Yes', 'every');?></label><br>
                            <label><input type="radio" name="ev_env_settings[use_hierarchy_categories]" value="false" <?php checked( $this->use_hierarchy_categories === 'false' ); ?>/> <?php _e('No', 'every');?></label>
                        </td>
                    </tr>

					<tr valign="top">
						<th>
							<label><?php _e('Hide future articles:', 'every');?></label>

							<p class="description"><?php _e('Default set to true, if it is true articles with pubdates in the future will not be rendered.', 'every');?></p>
						</th>
						<td>
							<label><input type="radio" name="ev_env_settings[pubdate_hide]" value="true" <?php checked( $this->pubdate_future_hide === 'true' ); ?>/> <?php _e('Yes', 'every');?></label><br>
							<label><input type="radio" name="ev_env_settings[pubdate_hide]" value="false" <?php checked( $this->pubdate_future_hide === 'false' ); ?>/> <?php _e('No', 'every'); ?></label>
						</td>
					</tr>

					<tr valign="top">
						<td>
							<input type="submit" class="button-primary" value="<?php _e('Save changes', 'every');?>" />
						</td>
					</tr>
				</table>
			</form>
		</div>
	<?php
	}
}
