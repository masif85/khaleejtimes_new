<?php
/*
 * This class is responsible for storing and retrieving Open Content Credentials
 * Will Validate OC information
 */

use Everyware\OcClient;

class OpenContent
{
    /**
     * @var OpenContent
     */
    private static $instance;

    //user specific settings for OC connection, managed in admin/oc_settings

    /**
     * @var bool
     */
    private $oc_test_con_result;

    /**
     * @var string
     */
    private $oc_username;

    /**
     * @var string
     */
    private $oc_password;

    /**
     * @var string
     */
    private $oc_base_url;

    /**
     * @var int
     */
    private $time_to_cache_json;

    /**
     * @var string
     */
    private $log_cache_events;

    /**
     * @var string
     */
    private $bypass_ob_cache;

    /**
     * @var bool
     */
    private $oc_password_been_encrypted;

    /**
     * @var string
     */
    private $password_placeholder = 'ew_pl_password';

    //User specific settings for OC Notifier

    /**
     * @var string
     */
    private $oc_test_notifier;

    //Notifier 2.0

    /**
     * @var string
     */
    private $oc_notifier_20_registered;

    // URL settings for custom post type

    /**
     * @var string
     */
    private $custom_post_type_slug;

    /**
     * @var string
     */
    private $category_property;

    /**
     * @var array
     */
    private $slug_properties;

    /**
     * @var int
     */
    private $slug_max_length;

    //Analytics

    /**
     * @var string
     */
    private $google_analytics_id;

    /**
     * @var string
     */
    private $google_analytics_placeholder = 'UA-XXXXXXX-X';

    // Amazon S3

    /**
     * @var string
     */
    private $s3_access_key;

    /**
     * @var string
     */
    private $s3_secret_key;

    /**
     * @var string
     */
    private $s3_bucket;

    // Image settings.

    /**
     * @var string
     */
    private $image_service;

    /**
     * @var string
     */
    private $imengine_url;

    /**
     * @var string
     */
    private $imgix_url;

    // Storage path

    /**
     * @var string
     */
    private $storage_path;

    /**
     * @var string
     */
    private $oc_notifier_bindings;

    /**
     * @var OcClient
     */
    private $client;

    /**
     * Public getters for all private members that are exposed
     *
     * @return string
     * @since 1.0.7
     */
    public function getOcUserName(): string
    {
        return $this->oc_username ?? '';
    }

    /**
     * @return string
     * @since 1.0.7
     */
    public function getOcPassword(): string
    {
        $password = $this->oc_password ?? '';
        if ($this->oc_password_been_encrypted === true) {
            $password = $this->decrypt_password($this->oc_password);
        }

        return $password;
    }

    /**
     * @return string
     * @since 1.0.7
     */
    public function getOcBaseUrl(): string
    {
        return $this->oc_base_url ?? '';
    }

    /**
     * @return int
     * @since 1.0.7
     */
    public function getSlugMaxLength()
    {
        return $this->slug_max_length;
    }

    /**
     * @return array
     * @since 1.0.7
     */
    public function getSlugProperties()
    {
        return $this->slug_properties;
    }

    /**
     * @return string
     * @since 1.0.7
     */
    public function getCustomPostTypeSlug()
    {
        return $this->custom_post_type_slug;
    }

    /**
     * @return string
     * @since 1.0.7
     */
    public function getCategoryProperty()
    {
        return $this->category_property;
    }

    /**
     * @return string
     * @since 1.0.7
     */
    public function getOcTestNotifier()
    {
        return $this->oc_test_notifier;
    }

    /**
     * @return int
     * @since 1.0.7
     */
    public function getTimeToCacheJson()
    {
        return $this->time_to_cache_json;
    }

    /**
     * @return string
     * @since 1.0.7
     */
    public function getGoogleAnalyticsId()
    {
        $id = $this->google_analytics_id ?? '';

        if ($id === $this->google_analytics_placeholder) {
            return '';
        }

        return $id;
    }

    /**
     * @return string
     * @since 1.0.7
     */
    public function getLogCacheEvents()
    {
        return $this->log_cache_events;
    }

    /**
     * @return string
     * @since 1.0.7
     */
    public function getBypassObCache()
    {
        return $this->bypass_ob_cache;
    }

    /**
     * @return string
     * @since 1.0.7
     */
    public function getS3AccessKey()
    {
        return $this->s3_access_key;
    }

    /**
     * @return string
     * @since 1.0.7
     */
    public function getS3SecretKey()
    {
        return $this->s3_secret_key;
    }

    /**
     * @return string
     * @since 1.0.7
     */
    public function getS3Bucket()
    {
        return $this->s3_bucket;
    }

    /**
     * @return string
     * @since 1.0.7
     */
    public function getStoragePath()
    {
        return $this->storage_path;
    }

    /**
     * @return string
     * @since 1.0.7
     */
    public function getImageService()
    {
        return $this->image_service;
    }

    /**
     * @return string
     * @since 1.0.7
     */
    public function getImageUrl()
    {
        return $this->getImageService() === 'imgix' ? $this->getImgixUrl() : $this->getImengineUrl();

    }

    /**
     * @return string
     * @since 1.0.7
     */
    public function getImengineUrl()
    {
        return $this->imengine_url;
    }

    /**
     * @return string
     * @since 1.0.7
     */
    public function getImgixUrl()
    {
        return $this->imgix_url;
    }

    /**
     * @return OpenContent
     * @since 1.0.7
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new OpenContent();
        }

        return self::$instance;
    }

    /**
     * @param string $param_notifier
     * @param string $type
     *
     * @return bool|null|string
     * @since 1.0.7
     */
    public function getNotifierRegistered($param_notifier = null, $type = 'default')
    {

        $ret = false;
        $options = get_option('oc_options');
        $notifier = $param_notifier ?? $options['oc_notifier20_registered'];

        if (isset($options['oc_notifier20_registered'])) {

            if ($type === 'default') {
                $has_listener = $this->oc_notifier_contains_listener($notifier,
                    sanitize_title(get_bloginfo('name')) . '-ALL-' . md5(strtolower(plugin_dir_url(__FILE__) . "oc_push.php")));
                $ret = $has_listener === true ? $notifier : "";
            }
        }

        if ($type !== 'default') {
            $ret = $this->oc_notifier_contains_listener($notifier,
                sanitize_title(get_bloginfo('name')) . '-' . $type . '-ALL-' . md5(strtolower(plugin_dir_url(__FILE__) . "oc_push.php")));
        }

        return $ret;
    }

    /*
     * Public construct
     * will initialise plugin and setup DB - default/customized settings etc
     */
    private function __construct()
    {

        //Initialize private members
        $options = get_option('oc_options');

        $this->oc_username = $options['oc_username'] ?? null;
        $this->oc_password = $options['oc_password'] ?? null;
        $this->oc_base_url = $options['oc_base_url'] ?? null;
        $this->oc_password_been_encrypted = isset($options['oc_password_been_encrypted']);

        $this->s3_access_key = $options['s3_access_key'] ?? null;
        $this->s3_secret_key = $options['s3_secret_key'] ?? null;
        $this->s3_bucket = $options['s3_bucket'] ?? null;
        $this->storage_path = $options['storage_path'] ?? null;

        $this->image_service = $options['image_service'] ?? 'imengine';
        $this->imengine_url = $options['imengine_url'] ?? 'http://example.imengine.com/image.php';
        $this->imgix_url = $options['imgix_url'] ?? 'https://xample.imgix.com';

        $this->time_to_cache_json = $options['time_to_cache'] ?? 10;
        $this->log_cache_events = $options['log_cache_events'] ?? 'false';
        $this->bypass_ob_cache = $options['bypass_ob_cache'] ?? 'false';

        $url_options = get_option('oc_article_url_options');
        $this->custom_post_type_slug = $url_options['custom_post_type_slug'] ?? null;
        $this->category_property = $url_options['category_property'] ?? null;
        $this->slug_max_length = $url_options['slug_max_length'] ?? null;
        $this->slug_properties = $url_options['slug_properties'] ?? null;

        //OC Notifier options
        $this->oc_test_notifier = $options['oc_notifier_test'] ?? null;

        //OC Bindings
        $this->oc_notifier_bindings = $options['oc_notifier_bindings'] ?? null;

        $this->google_analytics_id = $options['google_analytics_id'] ?? null;

        add_action('wp_ajax_oc_ajax_test_connection', [&$this, 'oc_ajax_test_connection']);
        add_action('wp_ajax_oc_ajax_test_s3_connection', [&$this, 'oc_ajax_test_s3_connection']);
        add_action('wp_ajax_oc_ajax_register_notifier', [&$this, 'oc_ajax_register_notifier']);
        add_action('wp_ajax_oc_ajax_unregister_notifier', [&$this, 'oc_ajax_unregister_notifier']);
        add_action('wp_ajax_oc_ajax_get_select_binding', [&$this, 'oc_ajax_get_select_binding']);

        $this->oc_test_con_result = $options['oc_test_con'] ?? false;

        //Hook into WP init and menu with our functions to render our stuff
        add_action('admin_init', [&$this, 'oc_connection_init']);
        add_action('admin_menu', [&$this, 'oc_connection_menu']);

        if ( ! is_admin() && ! is_user_logged_in() && $this->getGoogleAnalyticsId() !== '') {
            add_action('wp_footer', [&$this, 'every_google_analytics']);
        }

        //Display message when plugin is activated
        if ( ! $this->oc_test_con_result && ! $this->on_settings_page()) {

            //Display admin notice when activated
            add_action('admin_notices', [&$this, 'oc_admin_notice']);
        }

        if (isset($options['oc_notifier20_registered']) && $this->on_settings_page()) {
            $has_listener = $this->oc_notifier_contains_listener(
                $options['oc_notifier20_registered'],
                sanitize_title(get_bloginfo('name')) . '-ALL-' . md5(strtolower(plugin_dir_url(__FILE__) . "oc_push.php"))
            );

            $this->oc_notifier_20_registered = $has_listener === true ? $options['oc_notifier20_registered'] : "";
        }
    }

    private function on_settings_page()
    {
        if ( ! is_admin()) {
            return false;
        }

        global $pagenow;

        if ($pagenow !== 'admin.php') {
            return false;
        }

        return ($_GET['page'] ?? '') === 'oc_connection';
    }

    private function get_oc_options(): array
    {
        return array_replace([
            'oc_username' => null,
            'oc_password' => null,
            'oc_base_url' => null,
            's3_access_key' => null,
            's3_secret_key' => null,
            's3_bucket' => null,
            'storage_path' => null,
            'image_service' => 'imengine',
            'imengine_url' => 'http://example.imengine.com/image.php',
            'imgix_url' => 'https://xample.imgix.com',
            'time_to_cache' => 10,
            'log_cache_events' => 'false',
            'bypass_ob_cache' => 'false',
            'oc_notifier_test' => null,
            'oc_notifier_bindings' => null,
            'google_analytics_id' => null,
        ], (array)get_option('oc_options'));
    }

    /**
     * @param string $file
     *
     * @return void
     * @since 1.0.7
     */
    public function oc_single_hook_init($file)
    {
        register_activation_hook($file, [&$this, 'oc_connection_add_defaults']);
        register_deactivation_hook($file, [&$this, 'oc_connection_delete_plugin_options']);
    }

    /**
     * Display message on activation
     *
     * @return void
     * @since 1.0.7
     */
    public function oc_admin_notice()
    {
        $url = admin_url('admin.php?page=oc_connection');
        echo '<div class="updated"><p><strong>' . __('Open Content plugin is now activated',
                'every') . '</strong>, ' . __('next step is to enter your credentials under',
                'every') . ' <a href="' . $url . '"> ' . __('Open Content Settings', 'every') . '</a></p></div>';
    }

    /**
     * Delete options-table entries ONLY when plugin deactivated AND/OR deleted
     *
     * @return void
     * @since 1.0.7
     */
    public function oc_connection_delete_plugin_options()
    {
        delete_option('oc_options');
    }

    /**
     * Init plugin options to white list our options
     *
     * @return void
     * @since 1.0.7
     */
    public function oc_connection_init()
    {
        //Params for register settings: optiongroup- a settings group name, option name, sanitize callback
        register_setting('oc_connection_optiongroup', 'oc_options', [&$this, 'oc_connection_validate_options']);
        register_setting('oc_article_url_optiongroup', 'oc_article_url_options',
            [&$this, 'oc_article_url_validate_options']);
    }

    /**
     * Function to populate the DB with OC defaults
     *
     * @return void
     * @since 1.0.7
     */
    public function oc_connection_add_defaults()
    {
        update_option('oc_options', [
            'oc_username' => '',
            'oc_password' => '',
            'oc_base_url' => '',
            'imengine' => 'true',
            'time_to_cache' => 10,
            'log_cache_events' => 'false',
            'bypass_ob_cache' => 'false',
            'google_analytics_id' => $this->google_analytics_placeholder,
            'imengine_url' => 'http://example.imengine.com/image.php',
            'imgix_url' => 'https://example.imgix.com',
            'image_service' => 'imengine'
        ]);

        update_option('oc_article_url_options', [
            'custom_post_type_slug' => 'article',
            'category_property' => '',
            'slug_properties' => ['Headline', 'Leadin', 'Text'],
            'slug_max_length' => '100'
        ]);
    }

    /**
     * Function to render Google analytics on Site
     *
     * @deprecated as of 1.8.0
     */
    public function every_google_analytics()
    {
        $analytics_id = $this->getGoogleAnalyticsId();
        ?>
        <script type="text/javascript">
            var _gaq = _gaq || [];
            _gaq.push(['_setAccount', '<?php echo $analytics_id ?>']);
            _gaq.push(['_trackPageview']);
            (function () {
                var ga = document.createElement('script');
                ga.type = 'text/javascript';
                ga.async = true;
                ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
                var s = document.getElementsByTagName('script')[0];
                s.parentNode.insertBefore(ga, s);
            })();
        </script>
        <?php
    }

    /*
     * Adding a Admin menu with Admin menu page details
     */
    public function oc_connection_menu()
    {
        // Menu page
        //Params page_title, menu_title, capability, menu_slug, callback function name, icon url, position
        add_menu_page('Open Content Connection Settings', 'Open Content', 'manage_options', 'oc_connection');
        add_submenu_page('oc_connection', 'OC Settings', __('OC Settings', 'every'), 'manage_options', 'oc_connection',
            [&$this, 'oc_connection_options']);
        add_submenu_page('oc_connection', 'URL Settings', __('URL Settings', 'every'), 'manage_options',
            'oc_article_url', [&$this, 'oc_article_url_options']);
    }

    /**
     * Callback when Admin menu is triggered, Add actual menu page
     *
     * @return void
     * @since 1.0.7
     */
    public function oc_connection_options()
    {
        ?>
        <div class="wrap">
            <h2 class="oc_Settings_header"><?php _e('Open Content Settings', 'every'); ?></h2>

            <?php settings_errors(); ?>
            <form method="post" action="options.php" class="oc_settings_form">
                <?php settings_fields('oc_connection_optiongroup'); ?>
                <?php wp_nonce_field('update', 'oc_settings_nonce'); ?>

                <?php
                $placeholder_password = '';
                $password_disabled = '';
                $password = $this->getOcPassword();
                if (isset($password) && $password !== '') {
                    $placeholder_password = $this->password_placeholder;
                    $password_disabled = 'disabled="disabled"';
                }
                ?>

                <table class="form-table wide-fat ">
                    <tbody>
                    <tr valign="top">
                        <th scope="row"><label for="oc_username"><?php _e('Username', 'every'); ?>:</label></th>
                        <td>
                            <input type="text" id="oc_username" name="oc_options[oc_username]"
                                   value="<?php echo esc_attr($this->getOcUserName()); ?>" onclick="select()"/>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="oc_password"><?php _e('Password', 'every'); ?>:</label></th>
                        <td>
                            <input type="password" id="oc_password" name="oc_options[oc_password]"
                                   value="<?php echo $placeholder_password; ?>"
                                   onclick="select()" <?php echo $password_disabled; ?> />
                            <img src="<?php echo EVERY_BASE . 'assets/images/lock.png'; ?>" alt="Lock icon" title="Lock"
                                 class="password-lock"/>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th><label for="oc_base"><?php _e('OC Base URL', 'every'); ?>:</label></th>
                        <td>
                            <input type="text" id="oc_base" name="oc_options[oc_base_url]" size="50"
                                   value="<?php echo esc_url($this->getOcBaseUrl()); ?>"/>
                        </td>
                    </tr>
                    <tr valign="top">
                        <td>
                            <input type="submit" class="button" value="<?php _e("Test OC connection", "every") ?>"
                                   id="test_oc_con_button"/>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">
                            <label for="time_to_cache"><?php _e('Time to cache OC response to minimize traffic',
                                    'every'); ?>:
                            </label>
                        </th>
                        <td>
                            <input type="text" name="oc_options[time_to_cache]"
                                   value="<?php echo esc_attr($this->getTimeToCacheJson()); ?>" id="time_to_cache"
                                   size="8"/>
                            <br>

                            <p>
                                <em><?php _e('Enter time in seconds, default 10 will cache all data for 10 seconds<br />its recommended to increase this number in production',
                                        'every'); ?></em></p>

                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">
                            <label for="log_cache_events"> <?php _e('Log Cache events (DEV)', 'every'); ?>:
                                <br/>

                                <p class="modal_imengine_info_link"><?php _e('Used in development or debugging',
                                        'every'); ?></p>
                            </label>
                        </th>
                        <td>
                            <input type="radio" name="oc_options[log_cache_events]" value="true"
                                   id="log_cache_events" <?php checked($this->getLogCacheEvents() === 'true'); ?>/> <?php _e('Yes',
                                'every') ?><br>
                            <input type="radio" name="oc_options[log_cache_events]"
                                   value="false" <?php checked($this->getLogCacheEvents() === 'false'); ?>/> <?php _e('No',
                                'every'); ?>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">
                            <label for="bypass_ob_cache"> <?php _e('Bypass object cache', 'every'); ?>:
                                <br/>

                                <p class="modal_imengine_info_link"><?php _e('Used in development or debugging',
                                        'every'); ?></p>
                            </label>
                        </th>
                        <td>
                            <input type="radio" name="oc_options[bypass_ob_cache]" value="true"
                                   id="bypass_ob_cache" <?php checked($this->getBypassObCache() === 'true'); ?>/> <?php _e('Yes',
                                'every') ?><br>
                            <input type="radio" name="oc_options[bypass_ob_cache]"
                                   value="false" <?php checked($this->getBypassObCache() === 'false'); ?>/> <?php _e('No',
                                'every'); ?>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th>
                            <h3><?php _e('Image service settings', 'every'); ?></h3>
                        </th>
                    </tr>

                    <tr valign="top">
                        <th scope="row">
                            <label for="image_service"> <?php _e('Choose image service', 'every'); ?>:
                                <p class="">
                                    <em><?php _e('Choose which image service to use for displaying images in admin.',
                                            'every'); ?></em></p>
                            </label>
                        </th>
                        <td>
                            <input type="radio" name="oc_options[image_service]" value="imengine" id="image_service"
                                   class="image-service-option" <?php checked($this->getImageService() === 'imengine'); ?>/> <?php _e('imengine',
                                'every') ?><br>
                            <input type="radio" name="oc_options[image_service]" value="imgix"
                                   class="image-service-option" <?php checked($this->getImageService() === 'imgix'); ?>/> <?php _e('imgix',
                                'every'); ?>
                        </td>
                    </tr>

                    <tr valign="top" class="imengine-settings ew-hidden">
                        <th scope="row">
                            <label for="imengine_url"><?php _e('Imengine server:', 'every'); ?>
                            </label>
                        </th>
                        <td>
                            <input type="text" id="imengine_url" name="oc_options[imengine_url]" size="50"
                                   value="<?php echo esc_url($this->getImengineUrl()); ?>"/>
                            <br>
                            <p>
                                <em><?php _e('Enter the address of the Imengine server you want to use in the Board admin.',
                                        'every'); ?></em></p>
                        </td>
                    </tr>

                    <tr valign="top" class="imgix-settings ew-hidden">
                        <th scope="row">
                            <label for="imgix_url"><?php _e('Imgix source:', 'every'); ?>
                            </label>
                        </th>
                        <td>
                            <input type="text" id="imgix_url" name="oc_options[imgix_url]" size="50"
                                   value="<?php echo $this->getImgixUrl(); ?>"/>
                            <br>
                            <p>
                                <em><?php _e('Enter the address of the Imgix source you want to use for displaying picture in admin interfaces.',
                                        'every'); ?></em></p>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th>
                            <h3>Google Analytics</h3>
                        </th>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label for="analytics">Google analytics ID:
                                <br/><a href="http://www.google.com/analytics/" target="_blank"
                                        class="analytics_info_link"><?php _e('Read more about Google Analytics',
                                        'every'); ?></a>
                            </label>
                        </th>
                        <td>
                            <input type="text" name="oc_options[google_analytics_id]"
                                   value="<?php
                                   echo ! empty($this->getGoogleAnalyticsId()) ?
                                       esc_html($this->getGoogleAnalyticsId()) :
                                       $this->google_analytics_placeholder;
                                   ?>" id="analytics"/>
                            <br>
                        </td>
                    </tr>

                    <tr>
                        <th>
                            <h3><?php _e('Notifier 2.0', 'every'); ?></h3>
                        </th>
                    </tr>

                    <tr>
                        <th>
                            <label>Notifier URL:</label>
                        </th>
                        <td>
                            <input type="text" id="oc_notifier20_url" size="35" placeholder="http://url"
                                   value="<?php echo $this->oc_notifier_20_registered ?? '' ?>"/>
                            <input type="hidden" id="oc_notifier20_registered"
                                   name="oc_options[oc_notifier20_registered]"
                                   value="<?php echo $this->oc_notifier_20_registered ?? '' ?>"/>
                        </td>
                    </tr>
                    <tr>
                        <?php
                        $oc_api = new OcAPI();
                        $oc_properties = $oc_api->get_content_types();
                        ?>

                        <th></th>
                        <td class="notifier20_binding">
                            <?php if (isset($this->oc_notifier_bindings)): ?>
                                <?php foreach ($this->oc_notifier_bindings as $binding => $value): ?>
                                    <div>
                                        <select <?php echo $this->oc_notifier_20_registered ? 'disabled' : ''; ?>
                                            class="bindings_selector">
                                            <?php foreach ($oc_properties['Article'] as $property):
                                                if (isset($property[0])): ?>
                                                    <option <?php if ($property[0] === $binding) {
                                                        echo 'selected';
                                                    } ?>
                                                        value="<?php echo $property[0]; ?>"><?php echo $property[0]; ?></option>
                                                <?php endif;
                                            endforeach; ?>
                                        </select>

                                        <input <?php echo $this->oc_notifier_20_registered ? 'disabled' : ''; ?>
                                            type="text" class="binding_input" value="<?php echo $value; ?>"
                                            name="oc_options[oc_notifier_bindings][<?php echo $binding; ?>]"/>
                                        <?php if ( ! $this->oc_notifier_20_registered): ?>
                                            <input type="button" class="delete_binding" value="Remove"/>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <?php if ( ! $this->oc_notifier_20_registered): ?>
                                <input type="button" id="oc_notifier20_add_binding" class="button" value="add binding"/>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th></th>
                        <td>
                            <input type="button" id="oc_notifier20_button" class="button"
                                   value="<?php echo empty($this->oc_notifier_20_registered) ? __('Register',
                                       'every') : __('Deregister', 'every'); ?>"/>
                        </td>
                    </tr>

                    <?php if ($this->oc_notifier_20_registered): ?>
                        <tr>
                            <th>
                                <label>Notifer Debug:</label>
                            </th>
                            <td>
                                <?php

                                if (($debug = get_transient('OC_PUSH_DEBUG')) === false) {
                                    echo "No messages received";
                                } else {
                                    $debug = json_decode($debug, true);

                                    echo '<table class="notifier-debug">';
                                    echo '<th>' . __('Received', 'every') . '</th>';
                                    echo '<th>Content</th>';
                                    echo '<th>Uuid</th>';
                                    echo '<th>Event</th>';
                                    foreach ($debug as $item) {
                                        echo '<tr>';
                                        echo '<td class="notifier20 not-received">' . date('Y-m-d H:i:s',
                                                $item['time']) . '</td>';
                                        echo '<td class="notifier20 not-content">' . $item['content'], '</td>';
                                        echo '<td class="notifier20 not-uuid">' . $item['uuid'] . '</td>';
                                        echo '<td class="notifier20 not-event">' . $item['event'] . '</td>';
                                        echo '</tr>';
                                    }
                                    echo '</table>';
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <tr valign="top">
                        <td>
                            <input id="options-save" type="submit" class="button-primary"
                                   value="<?php _e('Save changes', 'every'); ?>"/>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </form>
        </div>
        <div class="modal_imengine_info dialog_window">
            <h1>Infomaker ImEngine</h1>

            <p><?php _e('Imengine is an optional module that can be used to safely cache images that are fetched from Open Content',
                    'every'); ?>.</p>

            <p><?php _e('Imengine will speed up image load time and also enable Crop and Resize without effect on the image source file in Open Content.',
                    'every'); ?></p>
        </div>
        <div class="modal_notifier_info dialog_window">
            <h1>Infomaker Notifier</h1>

            <p><?php _e('Notifier is an optional module that can be used between Everyware and Open Content.',
                    'every'); ?></p>

            <p><?php _e('Notifier will then listen for changes in Open Content and if the changes affect any of the content currently used in Everyware it will send a notification to let Everyware know about the change.',
                    'every'); ?></p>

            <p><?php _e('This enables fast cache "flush and refill" by Everyware. All cached data will be dropped and refilled automatically without a user request to the public site.',
                    'every'); ?></p>
        </div>
        <?php
    }

    /**
     * Callback when submenu item "URL Settings" is triggered, option page
     *
     * @return void
     * @since 1.0.7
     */
    public function oc_article_url_options()
    {
        ?>
        <div class="wrap">
            <h2 class="oc_Settings_header"><?php _e('Article URL Settings', 'every'); ?></h2>
            <?php settings_errors(); ?>
            <form method="post" action="options.php" class="oc_settings_form">
                <?php settings_fields('oc_article_url_optiongroup'); ?>
                <table class="form-table wide-fat ">
                    <tbody>
                    <tr class="<?php echo $this->oc_test_con_result ? '' : 'slug_hidden' ?>">
                        <th colspan="2" style="font-weight: normal;">
                            <?php _e(
                                'Set the Custom Post Type Slug, the default value is "article". This value will be the first part of the url of every article.<br/><strong>IMPORTANT: If you change this value after articles has been created all old article URLs will be changed</strong><br/>so this setting should only be changed before starting loading articles.<br/><br/><strong>You have to set "Permalink Settings" to "Post name" for this to work.</strong>',
                                'every'); ?>

                        </th>
                    </tr>
                    <tr class="<?php echo $this->oc_test_con_result ? '' : 'slug_hidden' ?> custom_post_type_slug">
                        <td><label for="custom_post_type_slug"><?php _e('Custom Post Type Slug:', 'every'); ?></label>
                        </td>
                        <td>
                            <input type="text" id="custom_post_type_slug"
                                   name="oc_article_url_options[custom_post_type_slug]" size="50"
                                   value="<?php echo esc_attr($this->custom_post_type_slug); ?>"/><span
                                style="color:#999">/%postname%/</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-top:0"></td>
                        <td style="padding-top:0"><p>Supported slugs: <code>%categoryname%</code>, <code>%year%</code>,
                                <code>%monthnum%</code>, <code>%day%</code><br/>(<code>%category%</code> and <code>_category_</code>
                                are deprecated.)</p></td>
                    </tr>

                    <tr class="<?php echo $this->oc_test_con_result ? '' : 'slug_hidden' ?>">
                        <th colspan="2">
                            <?php _e('Set max length for the article objects URL-slug and which properties should be used to generate the slug in order of preference.<br/>NOTE: The changes here will only apply to new articles that have not yet been created.',
                                'every'); ?>
                        </th>
                    </tr>
                    <tr class="<?php echo $this->oc_test_con_result ? '' : 'slug_hidden' ?>">
                        <td valign="top"><?php _e('Article Slug properties:', 'every'); ?></td>
                        <td class="slug_properties">
                            <ul id="slug_sortable">
                                <?php
                                if (is_array($this->slug_properties) && count($this->slug_properties) > 0) {
                                    foreach ($this->slug_properties as $property) {
                                        ?>
                                        <li class="slug_property">
                                            <span class="slug_property_name"><?php echo esc_attr($property); ?></span>
                                            <a href="#" class="slug_property_delete_link"></a>
                                            <input type="hidden" name="oc_article_url_options[slug_properties][]"
                                                   value="<?php echo esc_attr($property); ?>"/>
                                        </li>

                                        <?php
                                    }
                                } ?>
                            </ul>
                        </td>
                    </tr>
                    <tr class="<?php echo $this->oc_test_con_result ? '' : 'slug_hidden' ?>">
                        <td><label for="slug_max_length"><?php _e('Max length:', 'every'); ?></label></td>
                        <td>
                            <input type="text" id="slug_max_length" name="oc_article_url_options[slug_max_length]"
                                   size="50" value="<?php echo esc_attr($this->slug_max_length); ?>"/>
                        </td>
                    </tr>

                    <tr valign="top">
                        <td>
                            <input type="submit" class="button-primary" value="<?php _e('Save changes', 'every'); ?>"/>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <input type="hidden" name="oc_article_url_options[category_property_hidden]"
                       value="<?php echo esc_attr($this->category_property); ?>" id="category_property_hidden_value"/>
            </form>
        </div>
        <?php
    }

    /**
     * Function to Ajax test OC connection options
     *
     * @return void
     * @since 1.0.7
     */
    public function oc_ajax_test_connection(): void
    {
        $oc_user = isset($_POST['oc_user']) ? wp_filter_nohtml_kses(trim($_POST['oc_user'])) : '';
        $oc_pass = isset($_POST['oc_pass']) ? wp_filter_nohtml_kses(trim($_POST['oc_pass'])) : '';
        $oc_url = isset($_POST['oc_url']) ? wp_filter_nohtml_kses(trim($_POST['oc_url'])) : '';
        $oc_url = $this->oc_connection_validate_url($oc_url);

        // If no password was entered, use the existing one.
        if ($oc_pass === '' || $oc_pass === 'ew_pl_password') {
            $oc_pass = $this->getOcPassword();
        }

        print $this->oc_test_connection($oc_user, $oc_pass, $oc_url);

        die(1);
    }

    /**
     *
     * @return void
     * @since      1.0.7
     * @deprecated 1.7.0 to be removed 1.9.0
     */
    public function oc_ajax_test_s3_connection(): void
    {
        $s3_key = isset($_POST['s3_key']) ? wp_filter_nohtml_kses(trim($_POST['s3_key'])) : '';
        $s3_sec = isset($_POST['s3_sec']) ? wp_filter_nohtml_kses(trim($_POST['s3_sec'])) : '';
        $s3_buck = isset($_POST['s3_buck']) ? wp_filter_nohtml_kses(trim($_POST['s3_buck'])) : '';
        $result = false;

        if ($s3_key !== '' && $s3_sec !== '' && $s3_buck !== '') {
            require_once __DIR__ . '/Imengine/adapters/S3_Adapter.php';
            $s3_adapter = new S3_Adapter($s3_key, $s3_sec, $s3_buck);
            $available_buckets = $s3_adapter->listBuckets();
            if (is_array($available_buckets) && in_array($s3_buck, $available_buckets, true)) {
                $result = true;
            }

        }

        print $result;
        die(1);

    }

    /**
     * @return void
     * @since 1.0.7
     */
    public function oc_ajax_register_notifier(): void
    {
        $all_queue = false;
        $remove_queue = false;
        $notifier_url = isset($_POST['url']) ? wp_filter_nohtml_kses(trim($_POST['url'])) : "";
        $type = $_POST['type'] ?? 'default';
        $token = md5(strtolower(plugin_dir_url(__FILE__) . 'oc_push.php'));

        if ($type === 'default') {
            $all_name = sanitize_title(get_bloginfo('name')) . '-ALL-' . $token;
            $remove_name = sanitize_title(get_bloginfo('name')) . '-REMOVE-' . $token;
        } else {
            $all_name = sanitize_title(get_bloginfo('name')) . '-' . $type . '-ALL-' . $token;
            $remove_name = sanitize_title(get_bloginfo('name')) . '-' . $type . '-REMOVE-' . $token;
        }

        $properties = '';
        if (isset($_POST['properties'])) {
            $properties = stripslashes_deep($_POST['properties']);
        }

        //ordinary queue
        $data = [
            'name' => $all_name,
            'url' => wp_filter_nohtml_kses(plugin_dir_url(__FILE__) . "oc_push.php?token={$token}"),
            'properties' => $properties,
            'description' => plugin_dir_url(__FILE__)
        ];
        $json = json_encode($data);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $notifier_url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($json)
        ]);

        curl_setopt($ch, CURLINFO_HTTP_CODE, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_exec($ch);

        $all_http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($all_http_status === 200) {
            $all_queue = true;
        }

        //remove queue
        if ($type !== 'oclist') {

            $data = [
                'name' => $remove_name,
                'url' => wp_filter_nohtml_kses(plugin_dir_url(__FILE__) . "oc_push.php?token={$token}"),
                'properties' => ['eventtype' => 'DELETE'],
                'description' => ''
            ];
            $json = json_encode($data);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $notifier_url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($json)
            ]);

            curl_setopt($ch, CURLINFO_HTTP_CODE, 1);
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_exec($ch);

            $remove_http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($remove_http_status === 200) {
                $remove_queue = true;
            }
        } else {
            $remove_queue = true;
        }

        if ($all_queue && $remove_queue) {

            if ($type === 'default') {
                $opt = get_option('oc_options');
                $opt['oc_notifier20_registered'] = $notifier_url;
                $opt['oc_password'] = $this->decrypt_password($opt['oc_password']);
                update_option('oc_options', $opt);
            }

            do_action('ew_notifier_' . $type . '_added', $properties);

            print 200;
        } else {
            //rollback
            if ( ! $all_queue) {
                //remove all queue
                $name = $all_name;
                $this->ajax_remove_queue($name);
            }

            if ( ! $remove_queue) {
                //remove removequeue
                $name = $remove_name;
                $this->ajax_remove_queue($name);
            }
            print 'Error: ALL = ' . $all_http_status . ' | REMOVE = ' . $remove_http_status;
        }

        die(1);
    }

    /**
     * @param $name
     *
     * @return bool
     * @since 1.0.7
     */
    function ajax_remove_queue($name)
    {
        $notifier_url = isset($_POST['url']) ? wp_filter_nohtml_kses(trim($_POST['url'])) : '';
        $url = $this->oc_connection_validate_url($notifier_url);
        $ret = false;

        if ( ! empty($notifier_url)) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url . $name);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            curl_setopt($ch, CURLINFO_HTTP_CODE, 1);
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_exec($ch);

            $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ((int)$http_status === 200) {
                $ret = true;
            }
        }

        return $ret;
    }

    public function oc_ajax_unregister_notifier()
    {
        $notifier_url = isset($_POST['url']) ? wp_filter_nohtml_kses(trim($_POST['url'])) : '';
        $type = isset($_POST['type']) ? wp_filter_nohtml_kses(trim($_POST['type'])) : 'default';
        $ret = '';

        if ( ! empty($notifier_url)) {

            if ($type === 'default') {
                $all = $this->ajax_remove_queue(sanitize_title(get_bloginfo('name')) . '-ALL-' . md5(strtolower(plugin_dir_url(__FILE__) . 'oc_push.php')));
                $remove = $this->ajax_remove_queue(sanitize_title(get_bloginfo('name')) . '-REMOVE-' . md5(strtolower(plugin_dir_url(__FILE__) . 'oc_push.php')));

                if ($all && $remove) {
                    $ret = 200;
                }
            } else {
                $all = $this->ajax_remove_queue(sanitize_title(get_bloginfo('name')) . '-' . $type . '-ALL-' . md5(strtolower(plugin_dir_url(__FILE__) . 'oc_push.php')));
                if ($all) {
                    $ret = 200;
                }
            }

            print $ret;
        }

        die(1);
    }

    public function oc_ajax_get_select_binding()
    {
        $oc_api = new OcAPI();

        $contenttype = 'Article';
        if (isset($_POST['contenttype']) && $_POST['contenttype'] !== '') {
            $contenttype = $_POST['contenttype'];
        }

        print json_encode($oc_api->ajax_get_content_types()[$contenttype]);
        die(1);
    }

    public function oc_notifier_contains_listener($notifier, $listener)
    {
        if (($notifier ?? '') === '') {
            return false;
        }

        $api_response = wp_remote_get($notifier, [
            'timeout' => 10
        ]);

        $response = wp_remote_retrieve_body($api_response);

        if (empty($response)) {
            return false;
        }

        $json = json_decode($response, true) ?? [];

        return in_array($listener, array_column($json, 'name'), true);
    }

    /*
     * Function to test given user/pass and baseURL
     * @param string - username, string - password, string  - baseurl
     * @returns Boolean, true if test connection was successfully established, else false
     */
    private function oc_test_connection($username, $password, $base_url)
    {
        try {
            return OcClient::create($base_url, $username, $password)->testConnection();
        } catch (Exception $e) {
            trigger_error("Failed to test connection: " . $e->getMessage());
        }

        return false;
    }

    /*
     * Sanitize and validate input. Accepts an array, returns a sanitized array.
     * @param Array with submitted username, password and oc-url etc
     * @returns Sanitized Array
     */
    public function oc_connection_validate_options($input)
    {
        // if this fails, check_admin_referer() will automatically print a "failed" page and die.
        if ( ! empty($_POST) && check_admin_referer('update', 'oc_settings_nonce')) {


            // Replace password_placeholder if it hasn't been changed
            if ($this->oc_password !== '' && $input['oc_password'] === $this->password_placeholder) {
                $input['oc_password'] = $this->getOcPassword();
            }

            foreach ($input as $key => &$option) {
                if ($key === 'oc_base_url' || $key === 'oc_notifier') {
                    $option = $this->oc_connection_validate_url($option);
                }

                if (in_array($key, ['oc_username', 'oc_password', 'google_analytics_id'], true)) {
                    $option = wp_filter_nohtml_kses(trim($option)); // Sanitize and trim text input (remove white space, strip html tags, and escape characters)
                }
            }

            //Make sure the OC URL is ok
            $isValidInformation = $this->oc_test_connection(
                $input['oc_username'],
                $input['oc_password'],
                $input['oc_base_url']
            );

            //If not, trigger error
            if ( ! $isValidInformation) {
                add_settings_error(
                    'oc_test_con',
                    'oc_test_con',
                    __('Incorrect Open Content URL, username or password entered', 'every'),
                    'error'
                );
            }

            $input['oc_test_con'] = $isValidInformation;

            // Encrypt password before insert, also set bool to show password has been encrypted (legacy).
            $input['oc_password'] = $this->encrypt_password($input['oc_password']);
            $input['oc_password_been_encrypted'] = true;

            //Make sure TTL is entered as a positive int
            $input['time_to_cache'] = (int)$input['time_to_cache'];

            //Else trigger error
            if ($input['time_to_cache'] <= 0) {
                $input['time_to_cache'] = 30;
                add_settings_error(
                    'time_to_cache',
                    'time_to_cache',
                    __('Incorrect Time To Cache value entered, default value restored', 'every'),
                    'error'
                );
            }

            return $input;
        }
    }

    /*
     * Sanitize and validate input. Accepts an array, returns a sanitized array.
     * @param Array with form data
     * @returns Sanitized Array
     */
    public function oc_article_url_validate_options($input)
    {

        return $input;
    }

    /*
     * Make sure url always ends with a / (after its been trimmed and stripped of all tags etc)
     * @param string with url
     * @return sanitized and prepped string with url
     */
    public function oc_connection_validate_url($url)
    {
        $url = wp_filter_nohtml_kses(trim($url)); // Sanitize text input (strip html tags, and escape characters)

        return rtrim($url, '/') . '/';
    }

    /*
     * Public function to validate Notifier URL
     * If valid, we will subscribe!
     * @param notifier url
     * @return boolean success
     */
    public function test_notifier_url_and_subscribe($notifier_url)
    {
        //Make sure the URL is ok
        $notifier = new OcNotifier($notifier_url);
        $is_valid = $notifier->check_if_valid_url();

        if ($is_valid) {
            //notifier knows if we are already subscribing
            $notifier->register_site_as_listener();

            return true;
        }

        return false;
    }

    public function get_client(): OcClient
    {
        if ( ! $this->client instanceof OcClient) {
            $this->client = OcClient::create(
                $this->getOcBaseUrl(),
                $this->getOcUserName(),
                $this->getOcPassword()
            );
        }

        return $this->client;
    }

    /*
     * Public function to unsubscribe to Notifier events
     * This will trigger all widgets to loose their subscriptions
     */
    public function unsubscribe_to_notifier()
    {
        $notifier = new OcNotifier();
        //Unsubscribe
        $notifier->un_register_site_as_listener();

        return true;
    }


    private function encrypt_password($data)
    {
        $password = 'secretOcStuff';
        $salt = substr(md5(mt_rand(), true), 8);

        $key = md5($password . $salt, true);
        $iv = md5($key . $password . $salt, true);

        if (strlen($data) % 16 !== 0) {
            $data = str_pad($data, strlen($data) + 16 - strlen($data) % 16, "\0");
        }

        $ct = openssl_encrypt($data, 'AES-128-CBC', $key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);

        return base64_encode('Salted__' . $salt . $ct);
    }

    private function decrypt_password($data)
    {
        $password = 'secretOcStuff';
        $data = base64_decode($data);
        $salt = substr($data, 8, 8);
        $ct = substr($data, 16);

        $key = md5($password . $salt, true);
        $iv = md5($key . $password . $salt, true);

        $pt = openssl_decrypt($ct, 'AES-128-CBC', $key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);

        return rtrim($pt, "\0");
    }
}
