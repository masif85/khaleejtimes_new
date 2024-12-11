<?php declare(strict_types=1);

namespace Everyware\Concepts\Admin;

use Infomaker\Everyware\Support\Str;

/**
 * Class SubPage
 * @package Everyware\Concepts\Admin
 */
abstract class SubPage
{
    public function addSubPage($parentSlug): void
    {
        $pageSlug = "{$parentSlug}_" . Str::slug($this->pageTitle(), '_');
        $screenBase = "{$parentSlug}_page_{$pageSlug}";

        add_action('admin_init', function () use ($pageSlug) {
            # Check current admin page.
            if ( $this->onSubPage($pageSlug)) {

                if ( ! $this->validatePage()) {
                    wp_redirect(admin_url('/edit.php?' . build_query($_GET)));
                    exit;
                }

                $this->preFormRender();
            }

        });

        add_action('admin_notices', function () use ($pageSlug) {
            if ($this->onSubPage($pageSlug)) {
                $this->dashboardNotices();
            }
        });

        add_action('admin_enqueue_scripts', static function ($currentScreenBase) use ($screenBase, $pageSlug) {

            if ($currentScreenBase === $screenBase) {
                wp_dequeue_script('autosave');
            }

            if ($currentScreenBase === $screenBase && $_GET['page'] === $pageSlug) {

                $pluginUrl = CONCEPTS_PLUGIN_URL;

                wp_enqueue_style(
                    $pageSlug,
                    "{$pluginUrl}dist/css/admin" . (is_dev() ? '.css' : '.min.css'),
                    ['everyware-project-plugin-style'],
                    GIT_COMMIT
                );

                wp_enqueue_script(
                    $pageSlug,
                    "{$pluginUrl}dist/js/admin" . (is_dev() ? '.js' : '.min.js'),
                    ['everyware-project-plugin-js'],
                    GIT_COMMIT,
                    true
                );
            }

        });

        add_submenu_page(
            "edit.php?post_type={$parentSlug}",
            $this->pageTitle(),
            $this->menuTitle(),
            'manage_options',
            $pageSlug,
            [&$this, 'renderForm']
        );

    }

    public function renderForm(): void
    {
        $this->formContent([
            'currentSite' => get_bloginfo('name'),
            'currentQueryParams' => $_GET,
            'pageUrl' => $this->getPageUrl(),
            'hasAccess' => $this->hasAccess(),
            'title' => $this->pageTitle(),
            'translations' => $this->getTranslations()
        ]);
    }

    public function menuTitle(): string
    {
        return $this->pageTitle();
    }

    protected function onSubPage($pageSlug): bool
    {
        global $pagenow;

        if ($pagenow !== 'edit.php') {
            return false;
        }

        if ( ! isset($_GET['page'])) {
            return false;
        }

        # Check current admin page.
        return $_GET['page'] === $pageSlug;
    }

    protected function hasAccess(): bool
    {
        return current_user_can($this->getUserAccess());
    }

    protected function validatePage(): bool
    {
        // Override to add page validation
        return true;
    }

    /**
     * Show relevant notices for the plugin
     */
    protected function dashboardNotices(): void
    {
        // Override to add dashboard notice
    }

    protected function preFormRender(): void
    {
        // Override to form preparations
    }

    private function getTranslations(): array
    {
        return array_replace([
            'noPageAccess' => __('Sorry, you are not allowed to access this page.', CONCEPTS_TEXT_DOMAIN),
        ], $this->getLocalTranslations());
    }

    private function getPageUrl(): string
    {
        $queryString = http_build_query([
            'page' => $_GET['page'],
            'post_type' => $_GET['post_type']
        ]);

        return "{$_SERVER['PHP_SELF']}?{$queryString}";
    }

    abstract public function formContent(array $viewData): void;

    abstract public function pageTitle(): string;

    abstract public function getUserAccess(): string;

    abstract public function getLocalTranslations(): array;
}
