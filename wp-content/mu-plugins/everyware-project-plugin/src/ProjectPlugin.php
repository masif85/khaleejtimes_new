<?php declare(strict_types=1);

namespace Everyware\ProjectPlugin;

use Infomaker\Everyware\Support\Environment;
use Infomaker\Everyware\Support\Storage\ObjectDB;
use Infomaker\Everyware\Support\Str;
use Everyware\ProjectPlugin\Admin\ProjectPluginPage\ComponentsHandler;
use Everyware\ProjectPlugin\Admin\ProjectPluginPage\MetaboxesPage;
use Everyware\ProjectPlugin\Admin\ProjectPluginPage\PluginsPage;
use Everyware\ProjectPlugin\Admin\ProjectPluginPage\SettingsPage;
use Everyware\ProjectPlugin\Admin\ProjectPluginPage\WidgetsPage;
use Everyware\ProjectPlugin\Interfaces\ComponentsPageInterface;
use Everyware\ProjectPlugin\Interfaces\PluginConfigInterface;
use Everyware\ProjectPlugin\Interfaces\SettingsPageInterface;

class ProjectPlugin
{
    public const BUNDLE_PATH = 'everyware-bundle';
    public const COMPONENT_OPTION_PREFIX = 'everyware_project_plugin';

    protected static $instance;
    protected static $version = '0.8.3';

    /** @var SettingsPage */
    protected $settingsPage;

    protected $componentsPages;

    /** @var PluginConfigInterface */
    protected $config;

    protected $pages = [];

    private $textDomain = 'everyware_project_plugin_text_domain';

    private $actionLinks = [];

    public static function bootstrap(PluginConfigInterface $config): void
    {
        /** @var self $plugin */
        $plugin = static::getInstance();

        $plugin->init($config);

        /** @noinspection PhpUndefinedFunctionInspection */
        if (is_admin()) {
            /** @noinspection PhpUndefinedFunctionInspection */
            add_action('plugins_loaded', [$plugin, 'getInstance']);
        }
    }

    public static function getInstance(): self
    {
        if (static::$instance === null) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    public function init(PluginConfigInterface $config): void
    {
        $this->config = $config;

        $this->setupScripts()
            ->setupActionLinks()
            ->setupAdminMenu();
    }

    private function setupActionLinks(): self
    {
        $bootstrapFilePath = $this->config->getPath('bootstrap.php');

        // Add settings link for this plugin in WordPress plugin
        add_filter("plugin_action_links_{$bootstrapFilePath}", function ($links) {
            return $this->applyActionLinks($links);
        });

        return $this;
    }

    protected function setupScripts(): self
    {
        add_action('admin_init', function () {
            wp_enqueue_media();

            $assetsPath = $this->getPluginAssetsPath();

            wp_admin_css_color('naviga-theme', __('Naviga Theme'),
                "{$assetsPath}/css/admin-themes/naviga.min.css",
                [
                    '#1f0744',
                    '#402c60',
                    '#1473e6',
                    '#14B0Bc'
                ]
            );
        });

        // Make sure that new users are set with the Naviga Theme activated.
        add_action('user_register', function ($user_id) {
            $args = array(
                'ID' => $user_id,
                'admin_color' => 'naviga-theme'
            );
            wp_update_user( $args );
        });

        add_action('admin_enqueue_scripts', function () {

            if (is_admin()) {
                $assetsPath = $this->getPluginAssetsPath();
                $scriptVersion = Environment::isDev() ? false : GIT_COMMIT;

                wp_enqueue_style(
                    'everyware-project-plugin-style',
                    "{$assetsPath}/css/admin.min.css",
                    [],
                    $scriptVersion
                );

                wp_enqueue_script(
                    'everyware-project-plugin-popper-js',
                    "{$assetsPath}/js/popper.min.js",
                    [],
                    $scriptVersion,
                    true
                );

                wp_enqueue_script(
                    'everyware-project-plugin-bootstrap-js',
                    "{$assetsPath}/js/bootstrap.min.js",
                    ['jquery', 'everyware-project-plugin-popper-js'],
                    $scriptVersion,
                    true
                );

                wp_enqueue_script(
                    'everyware-project-plugin-js',
                    "{$assetsPath}/js/admin.min.js",
                    ['ever-admin', 'everyware-project-plugin-bootstrap-js'],
                    $scriptVersion,
                    true
                );
            }
        });

        return $this;
    }

    public function addPage(ComponentsPageInterface $page): self
    {
        if ($page instanceof ComponentsPageInterface) {
            $this->addComponentsPage($page);
        }

        $this->pages[] = $page;

        return $this;
    }

    protected function addComponentsPage(ComponentsPageInterface $page): self
    {
        $this->componentsPages[] = $page;

        return $this;
    }

    public function addSettingsTab(SettingsPageInterface $page): self
    {
        $this->settingsPage->addSettingsTab($page);

        return $this;
    }


    public function addActionLink(string $linkTitle, string $linkUrl, string $linkSlug = null): self
    {
        $linkSlug = $linkSlug ?? Str::slug($linkTitle);

        $this->actionLinks[$linkSlug] = "<a href=\"{$linkUrl}\">{$linkTitle}</a>";

        return $this;
    }

    protected function setupAdminMenu(): self
    {
        $this->settingsPage = new SettingsPage();

        $this
            ->addPage(new MetaboxesPage($this->getComponentsHandler('metaboxes')))
            ->addPage(new PluginsPage($this->getComponentsHandler('plugins')))
            ->addPage(new WidgetsPage($this->getComponentsHandler('widgets')));

        // Add menu items
        add_action('admin_menu', function () {

            $menuSlug = $this->config->getSlug();

            // Add head menu item
            add_menu_page($this->config->getName(), $this->config->getName(), 'manage_options', $menuSlug);

            // Add Settings menu item
            $this->settingsPage->addToMenu($menuSlug);

            foreach ($this->pages as $page) {
                $page->addToMenu($menuSlug);
            }

        }, 10);

        return $this;
    }

    protected function applyActionLinks(array $links): array
    {
        return array_merge($this->actionLinks, $links);
    }

    public static function getAllPlugins()
    {
        return ObjectDB::get('active_plugins', []);
    }

    protected function getComponentsHandler($componentDir): ComponentsHandler
    {
        $componentsOptionName = static::COMPONENT_OPTION_PREFIX . '_active_' . strtolower($componentDir);

        return new ComponentsHandler(static::BUNDLE_PATH . "/{$componentDir}", $componentsOptionName);
    }

    public function getPageUrl(ComponentsPageInterface $page): string
    {
        return admin_url('admin.php?page=' . $page->getSlug());
    }

    private function getPluginAssetsPath(): string
    {
        $pluginDir = $this->config->getPath();
        $pluginPath = substr(__DIR__, 0, strpos(__DIR__, $pluginDir));

        return plugin_dir_url($pluginPath) . "{$pluginDir}/dist";
    }
}
