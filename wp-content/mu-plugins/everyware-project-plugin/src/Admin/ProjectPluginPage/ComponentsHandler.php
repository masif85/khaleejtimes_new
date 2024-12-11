<?php declare(strict_types=1);

namespace Everyware\ProjectPlugin\Admin\ProjectPluginPage;

use Infomaker\Everyware\Support\Arr;
use Infomaker\Everyware\Support\Collection;
use Infomaker\Everyware\Support\Storage\ObjectDB;
use Infomaker\Everyware\Support\Str;
use Everyware\ProjectPlugin\Helpers\WordPressPageHelper;

class ComponentsHandler
{
    protected static $wpActivePlugins;

    protected $optionName;
    protected $componentsPath;
    protected $availableComponents;

    public function __construct(string $componentsPath, string $optionName)
    {
        $this->optionName = $optionName;
        $this->componentsPath = $componentsPath;

        /**
         * Hiding our own plugins for WordPress so they don't deactivate them.
         */
        ObjectDB::onGet('active_plugins', [$this, 'hideComponents']);

        /**
         * We are hooking in and adding our own plugins before saving the original plugins
         * A must do to first hide our plugins and the hook this on otherwise
         * WordPress will say our plugins is invalid and deactivate them.
         */
        ObjectDB::beforeUpdate('active_plugins', [$this, 'forceSaveComponents']);
    }

    /**
     * To prepend our plugins to WordPress original update plugins
     *
     * @param array $plugins
     *
     * @return array
     */
    public function forceSaveComponents(array $plugins = []): array
    {
        static::$wpActivePlugins = $this->getCombinedPlugins($plugins);

        return static::$wpActivePlugins;
    }

    /**
     * Hide plugins if we are on plugins.php so WordPress don't deactivate them.
     */
    public function hideComponents(array $plugins = []): array
    {
        static::$wpActivePlugins = $plugins;

        return WordPressPageHelper::onPluginsPage() ? $this->getFilteredPlugins($plugins) : $plugins;
    }

    /**
     * Retrieve activated components from its options table
     *
     * @return array
     */
    public function activeComponents(): array
    {
        $activeComponents = ObjectDB::get($this->optionName, []);

        return Arr::hasStringKeys($activeComponents) ? array_keys($activeComponents) : $activeComponents;
    }

    /**
     * Retrieve all "Plugins" activated in Wordpress from the options table
     *
     * @return mixed
     */
    public static function allPlugins()
    {
        if (static::$wpActivePlugins === null) {
            static::$wpActivePlugins = ObjectDB::get('active_plugins', []);
        }

        return static::$wpActivePlugins;
    }

    /**
     * Retrieve a merged list of active plugins and Everyware components
     *
     * @param array $plugins
     *
     * @return array
     */
    public function getCombinedPlugins(array $plugins = []): array
    {
        return array_unique(array_merge($this->getFilteredPlugins($plugins), $this->activeComponents()));
    }

    public function getFilteredPlugins(array $plugins = []): array
    {
        return array_filter(! empty($plugins) ? $plugins : static::allPlugins(), [$this, 'isNotComponent']);
    }

    /**
     * Check if a value is a Component added by Everyware
     *
     * @param string $component
     *
     * @return bool
     */
    public function isNotComponent(string $component): bool
    {
        return ! $this->isComponent($component);
    }

    /**
     * Check if a value is a Component added by Everyware
     *
     * @param string $component
     *
     * @return bool
     */
    public function isComponent(string $component): bool
    {
        return Str::startsWith($component, $this->componentsPath);
    }

    /**
     * Retrieve stored components from the options table and try to activate them.
     * Restores list after activation
     *
     * @return bool
     */
    public function activateComponents(): bool
    {
        return $this->storeComponents(array_filter($this->activeComponents(), [$this, 'activateComponent']));
    }

    /**
     * Activate component as a Wordpress plugin
     *
     * @param $component
     *
     * @return bool
     */
    public function activateComponent(string $component): bool
    {
        $file = WP_PLUGIN_DIR . '/' . $component;

        // Check so the plugin is available
        if ( ! is_readable($file)) {
            return false;
        }

        // Include file so activate can be triggered.
        include_once $file;

        do_action('activate_plugin', $component);
        do_action("activate_{$component}");
        do_action('activated_plugin', $component);

        return true;
    }

    /**
     * Deactivate all active components
     *
     * @return void
     */
    public function deactivateComponents(): void
    {
        Collection::make($this->activeComponents())->each([$this, 'deactivateComponent']);
    }

    /**
     * Deactivate component from wordpress plugins
     *
     * @param $component
     *
     * @return void
     */
    public function deactivateComponent(int $component): void
    {
        do_action('deactivate_plugin', $component);
        do_action("deactivate_{$component}");
        do_action('deactivated_plugin', $component);
    }

    /**
     * Update specific Components in options table
     *
     * @param array $components
     *
     * @return bool
     */
    public function storeComponents(array $components = []): bool
    {
        return ObjectDB::update($this->optionName, $components);
    }

    /**
     * Retrieve a list of components from the specified folder
     * Used for fetching "Widgets" and "Plugins"
     *
     * @return array
     */
    public function getAvailableComponents(): array
    {
        if ($this->availableComponents !== null) {
            return $this->availableComponents;
        }

        $availableComponents = get_plugins("/{$this->componentsPath }");

        foreach ($availableComponents as $file => $component) {
            $this->availableComponents[$file] = $this->createComponentObject($component,
                "{$this->componentsPath}/{$file}");
        }

        return $this->availableComponents ?? [];
    }

    /**
     * Create an object that can bu used in the admin list
     *
     * @param $component
     * @param $path
     *
     * @return array
     */
    protected function createComponentObject(array $component, string $path): array
    {
        return array_replace($component, [
            'Active' => \in_array($path, static::allPlugins(), true),
            'Path' => $path,
        ]);
    }
}
