<?php

namespace Spec\Everyware\Plugin\ContentSync;

use Everyware\Plugin\ContentSync\S3Provider;
use Everyware\Plugin\ContentSync\SitesManager;
use Everyware\Plugin\ContentSync\Wordpress\WpSites;
use PhpSpec\ObjectBehavior;

class SitesManagerSpec extends ObjectBehavior
{
    /**
     * @var WpSites
     */
    private $wpSites;

    /**
     * @var S3Provider
     */
    private $settings;

    public function let(WpSites $wpSites, S3Provider $settings): void
    {
        $this->wpSites = $wpSites;
        $this->settings = $settings;

        $this->beConstructedWith($wpSites, $settings);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(SitesManager::class);
    }
}
