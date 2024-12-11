<?php declare(strict_types=1);

namespace Everyware\ProjectPlugin\Components\Adapters;

use Everyware\ProjectPlugin\Components\Contracts\Admin;

/**
 * Class MetaboxAdapter
 *
 * @package Everyware\ProjectPlugin\Wordpress
 */
class MetaboxAdapter
{
    public const POST_TYPE = 'Page';

    /**
     * @var Admin
     */
    private $component;

    /**
     * @var string
     */
    private $boxPosition;

    /**
     * @var string
     */
    private $boxPriority;

    /**
     * @var string
     */
    private $metaBoxId;

    /**
     * @var string
     */
    private $metaBoxNonce;

    /**
     * @var array
     */
    private $storedSettings;

    public function __construct(Admin $component, $boxPosition = 'side', $boxPriority = 'default')
    {
        $this->metaBoxId = $this->component->getInputPrefix();
        $this->metaBoxNonce = $this->component->getInputPrefix() . '-nonce';
        $this->component = $component;
        $this->boxPosition = $boxPosition;
        $this->boxPriority = $boxPriority;
    }

    public function onPageLoad(): void
    {
        $this->addMetabox();
    }

    public function form(): void
    {
        $storedData = $this->getStoredSettings();

        echo $this->formController($storedData);

        wp_nonce_field($this->metaBoxId, $this->metaBoxNonce);
    }

    /**
     * Fires on "save_post"
     *
     * @param int     $postId The post ID.
     *
     * @return int|null
     */
    public function saveForm($postId): ?int
    {
        // check if there was a multi-site switch before
        if (is_multisite() && ms_is_switched()) {
            return $postId;
        }

        if ( $this->shouldUpdate($postId) ) {

            $newData = $this->postedSettings();
            $storedData = $this->getStoredSettings();

            $success = empty($storedData) ?
                $this->component->store($newData, $storedData) :
                $this->component->update($newData, $storedData);

            if ($success) {
                $this->storedSettings = $this->component->getSettings();
            }
        }

        return $postId;
    }

    /**
     * Function to verify this came from our screen and with proper authorization,
     * because the save_post action can be triggered at other times.
     *
     * @param string $postId
     *
     * @return bool
     */
    protected function shouldUpdate($postId): bool
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

    private function formController($storedData): string
    {
        if (empty($storedData)) {
            return $this->component->create($storedData);
        }

        return $this->component->edit($storedData);
    }

    /**
     * Function for registering the metabox in Wordpress
     *
     * @uses add_meta_box()
     *
     * @return void
     */
    private function addMetabox(): void
    {
        add_meta_box(
            $this->metaBoxId,
            $this->component->getName(),
            [&$this, 'form'],
            static::POST_TYPE,
            $this->boxPosition,
            $this->boxPriority
        );
    }

    private function getStoredSettings(): array
    {
        if ($this->storedSettings === null) {
            $this->storedSettings = $this->component->getSettings();
        }

        return $this->storedSettings;
    }

    private function postedSettings(): array
    {
        return $_POST[$this->component->getInputPrefix()] ?? [];
    }

    public static function init(Admin $component, $boxPosition = 'side', $boxPriority = 'default'): MetaboxAdapter
    {
        $adapter = new static($component, $boxPosition, $boxPriority);

        add_action('add_meta_boxes', [$adapter, 'addMetabox']);
        add_action('save_post', [$adapter, 'saveForm'], 10, 3);

        return $adapter;
    }

    public static function setupSideBox(Admin $component, $boxPriority = 'default'): MetaboxAdapter
    {
        return static::init($component, 'side', $boxPriority);
    }
}
