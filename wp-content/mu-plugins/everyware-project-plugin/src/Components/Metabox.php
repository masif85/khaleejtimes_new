<?php declare(strict_types=1);
/**
 * Abstract class for handling Metaboxes in Wordpress
 */

namespace Everyware\ProjectPlugin\Components;

use Infomaker\Everyware\Base\Admin\Form;
use Everyware\ProjectPlugin\Html\FormBuilder;
use Everyware\ProjectPlugin\Html\HtmlBuilder;
use Infomaker\Everyware\Base\Models\Page;
use Infomaker\Everyware\Base\Models\Post;
use Infomaker\Everyware\Support\Str;
use \WP_Post;

/**
 * Metabox
 *
 * @link    http://infomaker.se
 * @package Everyware\ProjectPlugin\Components
 * @since   Infomaker\Everyware\Base\Metabox 1.0.0
 */
abstract class Metabox
{
    protected const VERSION_FIELD = 'current_version';

    /**
     * Contains the id/key where the data will be saved in Wordpress database
     *
     * @var string
     */
    public static $postmetaId = 'ew_meta_box';

    /**
     * Contains the context within the screen where the boxes should display.
     * Page edit screen contexts include 'normal', 'side' and 'advanced'. Default 'side'.
     *
     * @var string
     */
    protected static $boxPosition = 'side';

    /**
     * Contains the priority within the context where the boxes
     * should show ('high', 'low', 'core', 'sorted', 'default'). Default 'default'.
     *
     * @var string
     */
    protected static $boxPriority = 'default';

    /**
     * If set, will contain the path to the page-template that will have to be chosen for the metabox to be visible
     *
     * @var string
     */
    public static $requiredTemplate;

    /**
     * Contains the id attribute of the html-metabox and is the default value for "meta_input_prefix"
     *
     * @var string
     */
    protected $metaBoxId = 'ew-meta-box';

    /**
     * Contains the nonce name used to identify meta changes
     *
     * @var string
     */
    protected $metaBoxNonce = 'ew_meta_nonce';

    /**
     * Contains the text domain for the metabox. Defaults to everywares textdomain for metaboxes
     *
     * @var string
     */
    protected $textDomain = 'ew_base_textdomain';

    /**
     * Contains the prefix for name and id attributes on form inputs
     *
     * @var null|string
     */
    protected $metaInputPrefix;

    /**
     * Contains the fields of the metabox form.
     *
     * @var array
     */
    protected $fields = [];

    /**
     * Contains a list of rules for the form fields used for validation
     *
     * @var array
     */
    protected $rules = [];

    /**
     * Contains the auto generated view data
     *
     * @var array
     */
    protected $viewData = [];

    /**
     * Contains the path to where the templates are stored
     *
     * @var string
     */
    protected $templatePath = '@tools/widgets/';

    /**
     * Contains the Metabox untranslated name
     *
     * @var string
     */
    protected $name = 'Metabox Name';

    /**
     * Contains the Metabox untranslated description
     *
     * @var string
     */
    protected $description = 'Metabox Description';

    /**
     * Contains the Metabox version
     *
     * @var string
     */
    protected $version = '1.0.0';

    /**
     * Contains the version a metabox will get if no version exist
     *
     * @var string
     */
    private $defaultVersion = '1.0.0';

    /**
     * Contains the type of post that the metabox will be bound to
     *
     * @var string
     */
    protected $postType = 'page';

    /**
     * Contains the name of a class, extending Post, that handles posts of this type.
     * @see Post
     *
     * @var string
     */
    protected $postClass = Page::class;

    /**
     * @var Form
     */
    protected $formHandler;

    /**
     * @var FormBuilder
     */
    protected $formBuilder;

    /**
     * @var bool
     */
    private $updating = false;

    /**
     * Metabox constructor.
     *
     * @uses  $pagenow
     * @uses  is_admin()
     * @uses  add_action('add_meta_boxes')
     * @uses  add_action('save_post')
     * @uses  Str::snake()
     *
     * @param $headline
     *
     */
    public function __construct($headline = '')
    {
        if (Str::notEmpty($headline)) {
            $this->name = $headline;
        }

        // Default prefix to box id if not set.
        if (null === $this->metaInputPrefix) {
            $this->metaInputPrefix = $this->metaBoxId;
        }

        $this->metaBoxNonce = Str::snake("{$this->metaInputPrefix}-nonce");

        if (is_admin()) {
            add_action('load-post.php', [$this, 'init']);
            add_action('load-post-new.php', [$this, 'init']);
        }
    }

    // Public Functions
    // ======================================================

    public function init(): void
    {
        add_action('add_meta_boxes', [&$this, 'setupMetabox']);
        add_action('save_post', [&$this, 'saveForm'], 10, 3);
    }

    /**
     * Initialize the metabox form
     *
     * @param WP_Post $wpPost
     *
     * @return void
     */
    public function initializeForm(WP_Post $wpPost): void
    {
        // Add default version field on new instances
        if ( ! array_key_exists(static::VERSION_FIELD, $this->fields)) {
            $this->fields[static::VERSION_FIELD] = $this->defaultVersion;
        }

        $this->formHandler = new Form($this->metaInputPrefix, $this->fields);
        $this->formBuilder = new FormBuilder(new HtmlBuilder());

        $postMeta = (new $this->postClass($wpPost))->getMeta(static::$postmetaId);

        // Make sure that new fields are added to the instance with default values and override stored values
        $this->form(array_replace_recursive($this->fields, $postMeta));
    }

    /**
     * Fires on "save_post"
     *
     * @param int     $postId The post ID.
     * @param WP_Post $wpPost The Wordpress post object.
     * @param bool    $update Whether this is an existing post being updated or not.
     *
     * @return int|null
     */
    public function saveForm(int $postId, WP_Post $wpPost, bool $update): ?int
    {
        $this->updating = $update;

        // check if there was a multi-site switch before
        if (is_multisite() && ms_is_switched()) {
            return $postId;
        }

        $post = new $this->postClass($wpPost);
        if ($this->shouldUpdate($postId) && $post->updateAllowed()) {
            $meta_data = $this->update($_POST[$this->metaInputPrefix] ?: [], $post->getMeta(static::$postmetaId));
            if ($meta_data) {
                $post->updateMeta(static::$postmetaId, $meta_data);
            }
        }

        return $postId;
    }

    /**
     * Function for registering the metabox in Wordpress
     *
     * @uses  add_meta_box()
     *
     * @return void
     */
    public function setupMetabox(): void
    {
        if ($this->hasRequiredTemplate()) {
            add_meta_box($this->metaBoxId, $this->translate($this->name), [
                &$this,
                'initializeForm'
            ], $this->postType, static::$boxPosition, static::$boxPriority);
        }
    }

    /**
     * @param array $instance
     *
     * @return array
     */
    protected function getFormSettings(array $instance): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'class_prefix' => "{$this->metaBoxId}__form",
            'id_prefix' => $this->metaBoxId,
            'version' => $this->version,
            'up_to_date' => $this->isUpToDate($instance),
            'text_domain' => $this->textDomain
        ];
    }

    /**
     * Render the form wrapper. This is the for that calls the form-method
     *
     * @param array $instance
     *
     * @return void
     */
    public function form(array $instance = []): void
    {
        //update version on instance to be updated on save
        $instance[static::VERSION_FIELD] = $this->version;

        echo $this->formContent($this->getFormSettings($instance), $instance);

        wp_nonce_field($this->metaBoxId, $this->metaBoxNonce);
    }

    /**
     * When the post is saved, saves our custom data.
     *
     * @param array $newInstance Values just sent to be saved.
     * @param array $oldInstance Previously saved values from database.
     *
     * @return array
     */
    public function update(array $newInstance, array $oldInstance = []): array
    {
        // Update the form if the new data is valid
        if ($this->validate($newInstance, $this->rules)) {

            $newInstance = array_replace_recursive($this->fields, $newInstance);

            // Store if new form or change existing form
            if (empty($oldInstance)) {
                return $this->beforeSave($this->onStore($newInstance));
            }

            return $this->beforeSave($this->onChange($newInstance, $oldInstance));
        }

        return $oldInstance;
    }

    // Protected functions
    // ======================================================

    /**
     * Retrieve the name of the required template
     *
     * @return string
     */
    protected function getTemplateName(): string
    {
        if (static::$requiredTemplate) {
            $template_file = Str::startsWith(static::$requiredTemplate,
                '/') ? static::$requiredTemplate : '/' . static::$requiredTemplate;
            $located_file = locate_template($template_file);

            if ( ! empty($located_file)) {
                $file_data = array_replace(['name' => $located_file],
                    get_file_data($located_file, ['name' => 'Template Name']));

                return $file_data['name'];
            }
        }

        return '';
    }

    /**
     * Check if the required page-template is set to the page
     *
     * @return bool
     */
    protected function hasRequiredTemplate(): bool
    {
        if (static::$requiredTemplate) {
            return static::$requiredTemplate === get_page_template_slug(get_queried_object_id());
        }

        return true;
    }

    /**
     * Fires on saving updated metabox
     *
     * @param array $newInstance
     * @param array $oldInstance
     *
     * @return array $new_instance The form data to be saved
     */
    protected function onChange(array $newInstance, array $oldInstance = []): array
    {
        $this->updating = ! empty($oldInstance);

        return $newInstance;
    }

    /**
     * Fires on saving new metabox
     *
     * @param array $newInstance
     *
     * @return array $new_instance The form data to be saved
     */
    protected function onStore(array $newInstance): array
    {
        return $newInstance;
    }

    /**
     * Function to verify this came from our screen and with proper authorization,
     * because the save_post action can be triggered at other times.
     *
     * @param int $postId
     *
     * @return bool
     */
    protected function shouldUpdate(int $postId): bool
    {

        // Check if not an autosave.
        if (wp_is_post_autosave($postId)) {
            return false;
        }

        // Check if not a revision.
        if (wp_is_post_revision($postId)) {
            return false;
        }

        // Verify that the nonce is valid.
        return wp_verify_nonce($_POST[$this->metaBoxNonce] ?? '', $this->metaBoxId) !== false;
    }

    /**
     * Function for validating the form
     *
     * @param array $newInstance
     * @param array $rules
     *
     * @return bool
     */
    protected function validate(array $newInstance, array $rules): bool
    {
        return ! empty($newInstance) && is_iterable($rules);
    }

    /**
     * Extract the data from the Metabox dock-header
     * and save into properties to be used when instantiating the widget
     *
     * @uses  get_file_data()
     *
     * @param string $file The metabox file with the docBlock
     *
     * @return array the extracted data
     */
    protected function setMetaboxData(string $file): array
    {
        $widgetData = get_file_data($file, [
            'name' => 'Plugin Name',
            'description' => 'Description',
            'version' => 'Version'
        ]);

        $this->name = $this->translate($widgetData['name']);
        $this->description = $this->translate($widgetData['description']);
        $this->version = $this->translate($widgetData['version']);

        return $widgetData;
    }

    /**
     * Function to translate text using the widgets text-domain
     *
     * @uses  __()
     *
     * @param $text
     *
     * @return string
     */
    protected function translate(string $text): string
    {
        return __($text, $this->textDomain);
    }

    /**
     * Function required by the metabox-child. Returns the string-representation of the metabox-form
     *
     * @param array $formSettings
     * @param array $storedData
     *
     * @return string
     */
    abstract protected function formContent(array $formSettings, array $storedData): string;

    /**
     * Function to compare the version of the widget and the saved instance
     *
     * @param array $instance
     *
     * @return bool
     */
    private function isUpToDate(array $instance): bool
    {
        return version_compare($instance[static::VERSION_FIELD], $this->version) >= 0;
    }

    /**
     * Function for setting up the metabox
     *
     * @param string $headline
     *
     * @return self
     */
    public static function setup(string $headline = ''): self
    {
        return new static($headline);
    }

    /**
     * Retrieve the meta-data used by the metabox.
     *
     * @param Post        $post      The post from witch to fetch the meta data
     * @param string|null $fieldName Retrieve all data or use the optional fieldName to specify a field
     *
     * @return array
     */
    public static function getMetaData(Post $post, string $fieldName = null): array
    {
        $meta = $post->getMeta(static::$postmetaId);

        // Return the field if specified
        if ($fieldName && array_key_exists($fieldName, $meta)) {
            return $meta[$fieldName];
        }

        return $meta;
    }

    /**
     * Fires before data is saved
     *
     *
     * @param array $instance
     *
     * @return array
     */
    protected function beforeSave(array $instance): array
    {
        return $instance;
    }
}
