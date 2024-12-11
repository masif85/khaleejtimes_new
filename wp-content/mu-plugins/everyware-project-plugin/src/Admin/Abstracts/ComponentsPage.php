<?php declare(strict_types=1);

namespace Everyware\ProjectPlugin\Admin\Abstracts;

use Infomaker\Everyware\Support\Arr;
use Infomaker\Everyware\Support\Collection;
use Infomaker\Everyware\Support\Storage\ObjectDB;
use Infomaker\Everyware\Support\Str;
use Everyware\ProjectPlugin\Admin\ProjectPluginPage\ComponentsHandler;
use Everyware\ProjectPlugin\Interfaces\ComponentsPageInterface;


abstract class ComponentsPage implements ComponentsPageInterface
{
    protected static $slug;
    protected static $title;

    protected $textDomain = 'everyware_project_plugin_text_domain';
    protected $componentsHandler;

    public function __construct(ComponentsHandler $componentsHandler)
    {
        $this->componentsHandler = $componentsHandler;
    }

    public function getSlug(): string
    {
        return Str::slug(static::$slug);
    }

    public function addToMenu(string $parentSlug): void
    {
        $title = $this->getTitle();

        // Widgets
        $hook = add_submenu_page($parentSlug, $title, $title, 'manage_options', Str::slug("{$parentSlug}-{$title}"), [
            $this,
            'renderPage',
        ]);

        add_action("load-{$hook}", [$this, 'onPageLoad']);
    }

    /**
     * Retrieve the title of the page to be used in the menu
     *
     * @return string
     */
    public function getTitle(): string
    {
        return __(Str::title(static::$title), $this->textDomain);
    }

    public function activateComponents(): bool
    {
        return $this->componentsHandler->activateComponents();
    }

    /**
     * Deactivate all active components
     *
     * @return void
     */
    public function deactivateComponents(): void
    {
        $this->componentsHandler->deactivateComponents();
    }

    /**
     * Function that fires on page load
     *
     * @return void
     */
    public function onPageLoad(): void
    {
        // Update active plugins with the others
        if($this->componentsUpdated() && $this->updateActiveState()) {
            ObjectDB::update('active_plugins', ComponentsHandler::allPlugins());
        }
    }

    /**
     * Check if components management page have posted changes
     *
     * @return bool
     */
    protected function componentsUpdated(): bool
    {
        return isset($_POST['everyware-component-submit']);
    }

    /**
     * Activate/Deactivate components according to the new list
     *
     * @return bool
     */
    public function updateActiveState(): bool
    {
        $activeComponents = $this->componentsHandler->activeComponents();
        $newComponents    = $this->getSubmittedComponents();

        // Deactivate components that isn't in the new active list
        foreach(array_keys($activeComponents) as $component) {
            if(!array_key_exists($component, $newComponents)) {
                $this->componentsHandler->deactivateComponent($component);
            }
        }

        // Activate components that isn't in the old active list
        foreach(array_keys($newComponents) as $component) {
            // Check if widgets exist if so activate it
            if(!array_key_exists($component, $activeComponents) && !$this->componentsHandler->activateComponent($component)) {
                unset($newComponents[$component]);
            }
        }
        return $this->componentsHandler->storeComponents($newComponents);
    }

    /**
     * Retrieve new list of components posted by the admin page
     *
     * @return array
     */
    protected function getSubmittedComponents(): array
    {
        $components = $_POST['active_components'] ?? [];
        return array_filter((array)$components);
    }

    /**
     * Function for outputting the admin page of the components from the menu
     *
     * @return mixed
     */
    abstract public function renderPage();

    /**
     * Retrieve component page specific data with added data to handle management of component-list
     *
     * @param array $customData
     *
     * @return array
     */
    protected function getComponentsPageData(array $customData = []): array
    {
        $currentListStatusFilter = $this->getCurrentStatusFilter();
        $viewData                  = array_replace_recursive([
            'component_type'    => $this->getTitle(),
            'description'       => __('From here you can manage your Everyware components for the site.', $this->textDomain),
            'updated'           => $this->componentsUpdated(),
            'plugin_status'     => $currentListStatusFilter,
            'plugin_status_url' => [
                'all'      => add_query_arg(['plugin_status' => 'all']),
                'active'   => add_query_arg(['plugin_status' => 'active']),
                'inactive' => add_query_arg(['plugin_status' => 'inactive']),
            ],
            'form_action'       => add_query_arg(['settings-updated' => 'true']),
            'text_domain'       => $this->textDomain,
            'components'        => $this->componentsHandler->getAvailableComponents(),
            'active_components' => [],
        ], $customData);

        $components = new Collection($viewData['components']);

        $viewData['total_count']    = $components->count();
        $viewData['active_count']   = $components->where('Active', true)->count();
        $viewData['inactive_count'] = $components->where('Active', false)->count();

        return $viewData;
    }

    /**
     * Retrieve the current status-filter of the components-list
     *
     * @return string
     */
    protected function getCurrentStatusFilter(): string
    {
        return $_GET['plugin_status'] ?? 'all';
    }
}
