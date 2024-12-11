<?php declare(strict_types=1);

namespace Everyware\Plugin\ContentSync\Wordpress;

use WP_Network;
use WP_Site;

class WpSites
{
    public function subSites(): iterable
    {
        $rootDomain = $this->rootDomain();
        foreach ($this->getSites() as $site) {
            if ($site instanceof WP_Site && $site->domain !== $rootDomain) {
                yield $site;
            }
        }
    }

    public function subSiteUrls(): array
    {
        $urls = [];
        foreach ($this->subSites() as $site) {
            $urls[] = $this->getSiteUrl($site);
        }

        return $urls;
    }

    public function getSiteUrl(WP_Site $site): string
    {
        return get_site_url($site->id);
    }

    public function getSites(): array
    {
        return get_sites() ?? [];
    }

    public function rootDomain(): ?string
    {
        global $current_site;

        if ($current_site instanceof WP_Network) {
            return $current_site->domain;
        }

        return null;
    }
}
