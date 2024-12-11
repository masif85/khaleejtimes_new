<?php

namespace Everyware\Plugin\ContentSync;

use Everyware\Plugin\ContentSync\Wordpress\WpNetworkOptions;
use WP_Site;

class Plugin
{
    private WpNetworkOptions $options;
    private Settings $settings;

    public function __construct(WpNetworkOptions $options, Settings $settings)
    {
        $this->options = $options;
        $this->settings = $settings;
    }

    public function deploySettings(): void
    {
        // If config is not deployed or was deployed by another version
        if ($this->options->get(CONTENT_SYNC_LAST_DEPLOYED, '') !== CONTENT_SYNC_PLUGIN_VERSION) {

            // Generate and deploy Settings
            $this->settings->deployConfig();
            $this->settings->deploySites();

            // Update option
            $this->options->update(CONTENT_SYNC_LAST_DEPLOYED, CONTENT_SYNC_PLUGIN_VERSION);
        }
    }

    public function siteCreated(WP_Site $site): void
    {
        $url = $this->getSiteUrl($site);

        $this->settings->registerSite($url);
        $this->settings->addSiteToStore($url);
    }

    public function siteDeleted(WP_Site $site): void
    {
        $url = $this->getSiteUrl($site);

        $this->settings->unregisterSite($url);
        $this->settings->removeSiteFromStore($url);
    }

    public function siteUpdated(WP_Site $newSite, WP_Site $oldSite): void
    {
        if ($newSite->domain !== $oldSite->domain) {
            $oldUrl = $this->getSiteUrl($oldSite);
            $newUrl = $this->getSiteUrl($newSite);

            $this->settings->replaceSite($oldUrl, $newUrl);
            $this->settings->replaceSiteFromStore($oldUrl, $newUrl);

        } elseif ($this->siteWasDeactivated($newSite, $oldSite)) {
            $this->siteDeleted($newSite);

        } elseif ($this->siteWasActivated($newSite, $oldSite)) {
            $this->siteCreated($newSite);
        }
    }

    private function siteWasDeactivated(WP_Site $newSite, WP_Site $oldSite):bool
    {
        return $this->siteIsActive($oldSite) && ! $this->siteIsActive($newSite);
    }

    private function siteWasActivated(WP_Site $newSite, WP_Site $oldSite):bool
    {
        return ! $this->siteIsActive($oldSite) && $this->siteIsActive($newSite);
    }

    private function siteIsActive(WP_Site $site): bool
    {
        return ! filter_var($site->deleted, FILTER_VALIDATE_BOOLEAN);
    }

    private function getSiteUrl(WP_Site $site): string
    {
        return "https://$site->domain";
    }

    public function initHooks(): void
    {
        add_action('wp_delete_site', [$this, 'siteDeleted'], 10, 1);
        add_action('wp_insert_site', [$this, 'siteCreated'], 10, 1);
        add_action('wp_update_site', [$this, 'siteUpdated'], 10, 2);
    }
}
