<?php declare(strict_types=1);

namespace Everyware\ProjectPlugin\Admin\Abstracts;

use Everyware\ProjectPlugin\Helpers\MessageBag;
use Infomaker\Everyware\Support\Collection;
use Infomaker\Everyware\Support\Str;
use Infomaker\Everyware\Twig\View;
use Everyware\ProjectPlugin\Interfaces\PluginPageInterface;
use Everyware\ProjectPlugin\Interfaces\SettingsPageInterface;

abstract class TabsPage implements PluginPageInterface
{

    /**
     * Path to the twig template base
     */
    protected static $template_path = '@projectPlugin/admin/';

    /**
     * @var string
     */
    protected static $text_domain = 'everyware_project_plugin_text_domain';

    /**
     * Contains messages from the tab-pages
     *
     * @var array
     */
    protected $messages;

    /**
     * Optional description of tabs page
     *
     * @var string
     */
    private $description;

    /**
     * Optional headline of tabs page
     *
     * @var
     */
    private $headline;

    /**
     * Contains the pages added as tab to the plugins settings-page
     *
     * @var SettingsPageInterface[]
     */
    private $settings_pages = [];

    /**
     * Contains the settings-page currently active
     *
     * @var SettingsPageInterface
     */
    private $current_page;

    /**
     * Add a page to be displayed as a tab on the settings page
     *
     * @param SettingsPageInterface $page
     *
     * @return $this
     */
    public function addSettingsTab(SettingsPageInterface $page): self
    {
        $this->settings_pages[$page->getTabSlug()] = $page;

        return $this;
    }

    /**
     * Use {add_submenu_page} to add the page to the plugin menu.
     *
     * @param string $parentSlug
     *
     * @return void
     */
    public function addToMenu(string $parentSlug): void
    {
        $title = $this->getTitle();
        $hook  = add_submenu_page($parentSlug, $title, $title, 'manage_options', $this->getPageSlug($parentSlug), [
            $this,
            'renderSettingsPage',
        ]);

        add_action("load-{$hook}", [$this, 'onSettingsPageLoad']);
    }

    /**
     * Build page slug "admin.php/?page=[PageSlug]"
     *
     * @param $parentSlug
     *
     * @return string
     */
    protected function getPageSlug(string $parentSlug): string
    {
        return Str::slug("{$parentSlug}-" . $this->getTitle());
    }

    /**
     * Fires whenever the settings page is loaded
     */
    public function onSettingsPageLoad(): void
    {
        $page = $this->getCurrentPage();
        if ($page instanceof SettingsPageInterface) {
            $page->onPageLoad();
            $this->current_page = $page;
        }
    }

    /**
     * Determine and fetch the current page based on the active tab
     *
     * @return SettingsPageInterface
     */
    protected function getCurrentPage():? SettingsPageInterface
    {
        return $this->getSettingsTabs()->get($this->getActiveTab()) ?? null;
    }

    /**
     * Retrieve a collection of the pages displayed ass tabs on the settings page
     *
     * @return Collection
     */
    public function getSettingsTabs(): Collection
    {
        return new Collection($this->settings_pages);
    }

    /**
     * Retrieve the tab-slug currently active on the settings page
     *
     * @return string
     */
    public function getActiveTab(): string
    {
        if (isset($_GET['tab'])) {
            return $_GET['tab'];
        }

        $settings_pages = $this->getSettingsTabs();

        if ( ! $settings_pages->isEmpty()) {
            return $settings_pages->first()->getTabSlug();
        }

        return $this->getSlug();
    }

    /**
     * Display settings page
     */
    public function renderSettingsPage(): void
    {

        View::render($this->getTemplate('tabs-page'), [
            'tabs'         => $this->getTabs(),
            'headline'     => $this->headline ?: $this->getTitle(),
            'description'  => $this->description ?: '',
            'text_domain'  => static::$text_domain,
            'message'      => $this->current_page ? $this->getPageMessage($this->current_page) : '',
            'page_content' => $this->current_page ? $this->getPageContent($this->current_page) : '',
        ]);
    }

    /**
     * Retrieve a template from configured path
     *
     * @param $template
     *
     * @return string
     */
    protected function getTemplate(string $template): string
    {
        return Str::finish(static::$template_path, "/{$template}");
    }

    /**
     * Collect data from each registered tab-page
     *
     * @return array
     */
    protected function getTabs(): array
    {
        $active_tab = $this->getActiveTab();

        return array_map(function (SettingsPageInterface $page) use ($active_tab) {
            return [
                'slug'   => $page->getTabSlug(),
                'label'  => $page->getTabTitle(),
                'active' => $page->getTabSlug() === $active_tab,
                'url'    => add_query_arg('tab', $page->getTabSlug()),
            ];
        }, $this->settings_pages);
    }

    /**
     * Retrieve any messages from the provided page
     *
     * @param SettingsPageInterface $page
     *
     * @return string
     */
    protected function getPageMessage(SettingsPageInterface $page): string
    {
        $message = $page->getStatusMessage();

        return Str::notEmpty($message) ? $message : MessageBag::messagesToHtml();
    }

    /**
     * Retrieve content from the provided page
     *
     * @param SettingsPageInterface $page
     *
     * @return string
     */
    public function getPageContent(SettingsPageInterface $page): string
    {
        return $page->pageContent();
    }

    /**
     * Function to set a description for the tabs page. Will be rendered above tabs
     *
     * @param string $description
     *
     * @return self
     */
    protected function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Function to set a description for the tabs page. Will be rendered above tabs
     *
     * @param string $headline
     *
     * @return self
     */
    protected function setHeadline(string $headline): self
    {
        $this->headline = $headline;

        return $this;
    }
}
