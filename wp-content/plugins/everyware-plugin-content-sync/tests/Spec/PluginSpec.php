<?php

namespace Spec\Everyware\Plugin\ContentSync;

use Everyware\Plugin\ContentSync\Plugin;
use Everyware\Plugin\ContentSync\Settings;
use Everyware\Plugin\ContentSync\Wordpress\WpNetworkOptions;
use PhpSpec\ObjectBehavior;

class PluginSpec extends ObjectBehavior
{
    /**
     * @var WpNetworkOptions
     */
    private $options;

    /**
     * @var Settings
     */
    private $settings;

    public function let(WpNetworkOptions $options, Settings $settings)
    {
        $this->options = $options;
        $this->settings = $settings;

        $this->beConstructedWith($options, $settings);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(Plugin::class);
    }

    public function it_should_deploy_configuration(): void
    {
        $this->options->get(CONTENT_SYNC_LAST_DEPLOYED, '')->willReturn('');

        $this->settings->deployConfig()->shouldBeCalled();
        $this->settings->deploySites()->shouldBeCalled();

        $this->options->update(CONTENT_SYNC_LAST_DEPLOYED, CONTENT_SYNC_PLUGIN_VERSION)->shouldBeCalled();

        $this->deploySettings();
    }

    public function it_should_only_deploy_configuration_once_per_plugin_version(): void
    {
        $this->options->get(CONTENT_SYNC_LAST_DEPLOYED, '')->willReturn(CONTENT_SYNC_PLUGIN_VERSION);

        $this->settings->deployConfig()->shouldNotBeCalled();
        $this->settings->deploySites()->shouldNotBeCalled();

        $this->options->update(CONTENT_SYNC_LAST_DEPLOYED, CONTENT_SYNC_PLUGIN_VERSION)->shouldNotBeCalled();

        $this->deploySettings();
    }
}
