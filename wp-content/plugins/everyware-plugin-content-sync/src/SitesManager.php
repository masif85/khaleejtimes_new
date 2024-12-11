<?php

namespace Everyware\Plugin\ContentSync;

use Everyware\Plugin\ContentSync\Wordpress\WpSites;
use WP_Site;

class SitesManager
{
    private S3Provider $s3Provider;
    private WpSites $sites;

    public function __construct(WpSites $sites, S3Provider $settings)
    {
        $this->sites = $sites;
        $this->s3Provider = $settings;
    }

    public function getSitesSettings(): array
    {
        $registeredSites = $this->s3Provider->getSites();
        $siteSettings = [];
        foreach ($this->sites->subSites() as $site) {
            if ($site instanceof WP_Site) {
                $url = $this->sites->getSiteUrl($site);
                $siteSettings[] = $site->to_array() + [
                    'name' => $site->blogname,
                    'url' => $url,
                    'active' => in_array($url, $registeredSites, true)
                ];
            }
        }

        return $siteSettings;
    }
}
