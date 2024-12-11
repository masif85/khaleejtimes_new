<?php

namespace Spec\Everyware\Plugin\ContentSync;

use Everyware\Plugin\ContentSync\ConfigManager;
use Everyware\Plugin\ContentSync\S3Provider;
use PhpSpec\ObjectBehavior;

class ConfigManagerSpec extends ObjectBehavior
{
    /**
     * @var S3Provider
     */
    private $settings;

    public function let(S3Provider $settings): void
    {
        $this->settings = $settings;

        $this->beConstructedWith($settings);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ConfigManager::class);
    }

    public function it_can_generate_a_default_config()
    {
        $this->generateConfig()->shouldReturn($this->simulateDefaultConfig());
    }

    public function it_will_deploy_an_auto_generated_config_to_S3()
    {
        $this->settings->storeConfig($this->simulateDefaultConfig())->shouldBeCalled();

        $this->deployConfig();
    }

    public function simulateDefaultConfig()
    {
        $apiPaths = [];
        $apiPath = ConfigManager::API_PATH;

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
