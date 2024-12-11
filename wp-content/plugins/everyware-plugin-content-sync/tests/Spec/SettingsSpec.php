<?php

namespace Spec\Everyware\Plugin\ContentSync;

use Everyware\Plugin\ContentSync\S3Provider;
use Everyware\Plugin\ContentSync\Settings;
use Everyware\Plugin\ContentSync\Wordpress\WpNetworkOptions;
use Everyware\Plugin\ContentSync\Wordpress\WpSites;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SettingsSpec extends ObjectBehavior
{
    /**
     * @var S3Provider
     */
    private $s3Provider;

    /**
     * @var WpSites
     */
    private $wpSites;

    /**
     * @var WpNetworkOptions
     */
    private $wpNetworkOptions;

    public function let(S3Provider $s3Provider, WpSites $wpSites, WpNetworkOptions $wpNetworkOptions): void
    {
        $this->s3Provider = $s3Provider;
        $this->wpSites = $wpSites;
        $this->wpNetworkOptions = $wpNetworkOptions;

        $this->beConstructedWith($s3Provider, $wpSites, $wpNetworkOptions);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(Settings::class);
    }

    public function it_can_generate_a_default_config(): void
    {
        $this->generateConfig()->shouldReturn($this->simulateDefaultConfig());
    }

    public function it_will_deploy_an_auto_generated_config_to_S3(): void
    {
        $this->s3Provider->storeConfig($this->simulateDefaultConfig())->shouldBeCalled();

        $this->deployConfig();
    }

    public function it_can_fetch_the_current_config_from_s3()
    {
        $this->s3Provider->getConfig()->willReturn(['config']);
        $this->currentConfig()->shouldReturn(['config']);
    }

    public function it_will_deploy_an_auto_generated_list_of_sites_to_S3(): void
    {
        $sites = ['mysite'];
        $this->wpNetworkOptions->get(Settings::CONTENT_SYNC_OPTIONS, [])->willReturn(['sites' => $sites]);
        $this->wpSites->subSiteUrls()->willReturn($sites);
        $this->s3Provider->storeSites($sites)->shouldBeCalled();

        $this->deploySites();
    }

    public function it_will_store_and_deploy_list_of_all_sites_to_S3_if_no_options_exist(): void
    {
        $sites = ['mysite'];
        $this->wpNetworkOptions->get(Settings::CONTENT_SYNC_OPTIONS, [])->willReturn([]);

        $this->wpSites->subSiteUrls()->willReturn($sites);

        $this->wpNetworkOptions->update(Settings::CONTENT_SYNC_OPTIONS, ['sites' => $sites])->willReturn(true);

        $this->s3Provider->storeSites($sites)->shouldBeCalled();

        $this->deploySites();
    }

    public function it_will_fetch_all_registered_sites()
    {
        $result = ['sites'];
        $this->s3Provider->getSites()->willReturn($result);

        $this->registeredSites()->shouldReturn($result);
    }

    public function it_can_register_site(): void
    {
        $this->s3Provider->getSites()->willReturn([
            'site.one',
            'site.two'
        ]);

        $this->s3Provider->storeSites([
            'site.one',
            'site.two',
            'newsite.one'
        ])->shouldBeCalled();

        $this->registerSite('newsite.one');
    }

    public function it_should_not_register_duplicate_sites(): void
    {
        $this->s3Provider->getSites()->willReturn([
            'site.one',
            'site.two'
        ]);

        $this->s3Provider->storeSites([
            'site.one',
            'site.two'
        ])->shouldNotBeCalled();

        $this->registerSite('site.one');
    }

    public function it_can_unregister_site(): void
    {
        $this->s3Provider->getSites()->willReturn([
            'site.one',
            'site.two'
        ]);

        $this->s3Provider->storeSites([
            'site.two'
        ])->shouldBeCalled();

        $this->unregisterSite('site.one');
    }

    public function it_will_not_update_sites_if_none_was_unregistered(): void
    {
        $this->s3Provider->getSites()->willReturn([
            'site.one',
            'site.two'
        ]);

        $this->s3Provider->storeSites()->shouldNotBeCalled();

        $this->unregisterSite('unknown-site.one');
    }

    public function it_can_replace_registered_sites(): void
    {
        $this->s3Provider->getSites()->willReturn([
            'site.one',
            'site.two'
        ]);

        $this->s3Provider->storeSites([
            'newsite.one',
            'site.two'
        ])->shouldBeCalled();

        $this->replaceSite('site.one', 'newsite.one');
    }

    public function it_will_register_new_site_on_replace_even_if_old_url_cant_be_found(): void
    {
        $this->s3Provider->getSites()->willReturn([
            'site.one',
            'site.two'
        ]);

        $this->s3Provider->storeSites([
            'site.one',
            'site.two',
            'newsite.one'
        ])->shouldBeCalled();

        $this->replaceSite('site.three', 'newsite.one');
    }

    public function it_can_fetch_all_sites_from_current_network(): void
    {
        $sites = ['sites'];

        $this->wpNetworkOptions->get(Settings::CONTENT_SYNC_OPTIONS, [])->willReturn(['sites' => $sites]);
        $this->wpSites->subSiteUrls()->willReturn($sites);

        $this->generateSiteMap()->shouldReturn($sites);
    }

    public function it_should_automatically_update_options_with_missing_sites(): void
    {
        $storedSites = ['sites'];
        $sites = ['sites', 'missing.site'];

        $this->wpNetworkOptions->get(Settings::CONTENT_SYNC_OPTIONS, [])->willReturn(['sites' => $storedSites]);
        $this->wpSites->subSiteUrls()->willReturn($sites);
        $this->wpNetworkOptions->update(Settings::CONTENT_SYNC_OPTIONS, ['sites' => $sites])->willReturn(true);

        $this->generateSiteMap()->shouldReturn($sites);
    }

    public function it_provides_a_getter_for_the_bucket_url(): void
    {
        $url = 'bucket_url';

        $this->s3Provider->bucketUrl()->willReturn($url);

        $this->getBucketUrl()->shouldReturn($url);
    }

    public function it_will_add_site_to_options_store()
    {
        $sites = ['sites'];
        $newSite = 'new.site';

        $this->simulateGetSitesFromOptions($sites);

        $this->simulateUpdatingSitesToOptions(array_merge($sites, [$newSite]));

        $this->addSiteToStore($newSite);
    }

    public function it_will_not_add_duplicate_sites_to_options_store()
    {
        $sites = [
            'site.one',
            'site.two'
        ];
        $newSite = 'site.two';

        $this->simulateGetSitesFromOptions($sites);
        $this->wpNetworkOptions->update()->shouldNotBecalled();

        $this->addSiteToStore($newSite);
    }

    private function simulateGetSitesFromOptions(array $sites)
    {
        $this->wpNetworkOptions->get(Settings::CONTENT_SYNC_OPTIONS, [])->willReturn(['sites' => $sites]);
        $this->wpSites->subSiteUrls()->willReturn($sites);
    }

    public function it_will_remove_site_from_options_store()
    {
        $this->simulateGetSitesFromOptions([
            'site.one',
            'site.two'
        ]);

        $this->simulateUpdatingSitesToOptions([
            'site.one'
        ]);

        $this->removeSiteFromStore('site.two');
    }

    public function it_will_not_try_to_remove_non_existing_site_from_options_store()
    {
        $this->simulateGetSitesFromOptions([
            'site.one',
            'site.two'
        ]);

        $this->wpNetworkOptions->update(Argument::type('string'), Argument::type('array'))->shouldNotBecalled();

        $this->removeSiteFromStore('site.three');
    }

    public function it_will_replace_a_site_from_options_store()
    {
        $this->simulateGetSitesFromOptions([
            'site.one',
            'site.two',
            'site.three'
        ]);

        $this->simulateUpdatingSitesToOptions([
            'site.one',
            'new.site',
            'site.three'
        ]);

        $this->replaceSiteFromStore('site.two', 'new.site');
    }

    public function it_will_register_new_site_on_replace_if_old_site_cant_be_found_in_options_store()
    {
        $this->simulateGetSitesFromOptions([
            'site.one',
            'site.two',
            'site.three'
        ]);

        $this->simulateUpdatingSitesToOptions([
            'site.one',
            'site.two',
            'site.three',
            'new.site'
        ]);

        $this->replaceSiteFromStore('missing.site', 'new.site');
    }

    private function simulateUpdatingSitesToOptions(array $sites)
    {
        $this->wpNetworkOptions->update(Settings::CONTENT_SYNC_OPTIONS, ['sites' => $sites])->willReturn(true);
    }

    private function simulateDefaultConfig(): array
    {
        $apiPaths = [];
        $apiPath = Settings::API_PATH;

        $contentTypes = [
            'Article',
            'Concept',
            'List'
        ];

        $ocSettings = [
            'contenttype' => '',
            'sort.indexfield' => 'created',
            'limit' => 500,
            'start' => '0',
            'properties' => 'uuid',
        ];

        foreach ($contentTypes as $contentType) {
            $apiPaths[$contentType] = [
                'ADD' => $apiPath,
                'UPDATE' => $apiPath,
                'DELETE' => $apiPath,
            ];
        }

        return [
            'ocQuery' => $ocSettings,
            'contentTypes' => $contentTypes,
            'apiPaths' => $apiPaths,
            'asynchronous' => true,
            'sqsGroupsEventlog' => 5,
            'sqsGroupsBatch' => 10
        ];
    }
}
