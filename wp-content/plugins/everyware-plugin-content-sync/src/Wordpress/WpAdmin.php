<?php declare(strict_types=1);

namespace Everyware\Plugin\ContentSync\Wordpress;

use Everyware\Plugin\ContentSync\Wordpress\Contracts\WpAdminPage;
use Infomaker\Everyware\Support\Str;

class WpAdmin
{
    private WpAdminPage $settingsPage;

    public function isAdmin(): bool
    {
        return is_admin();
    }

    public function isNetworkAdmin(): bool
    {
        return $this->isAdmin() && is_network_admin();
    }

    public function addNetworkSettingsPage(): void
    {
        if ( ! $this->settingsPage instanceof WpAdminPage) {
            return;
        }

        add_submenu_page(
            'settings.php',
            $this->settingsPage->pageTitle(),
            $this->settingsPage->menuTitle(),
            'manage_options',
            Str::kebab($this->settingsPage->pageTitle()),
            function () {
                echo $this->settingsPage->getContent();
            });
    }

    public function registerNetworkSettingsPage(WpAdminPage $page): void
    {
        $this->settingsPage = $page;

        if( $this->isNetworkAdmin() ) {
            add_action('network_admin_menu', [$this, 'addNetworkSettingsPage']);
        }
    }
}
