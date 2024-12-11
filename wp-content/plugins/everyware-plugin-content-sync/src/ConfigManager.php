<?php declare(strict_types=1);

namespace Everyware\Plugin\ContentSync;

class ConfigManager
{
    public const API_PATH = 'content-sync/v1/trigger-event';

    private S3Provider $settings;

    public function __construct(S3Provider $settings)
    {
        $this->settings = $settings;
    }

    public function deployConfig(): void
    {
        $this->settings->storeConfig($this->generateConfig());
    }

    public function currentConfig(): array
    {
        return $this->settings->getConfig();
    }

    public function generateConfig(): array
    {
        $apiPaths = [];
        $apiPath = static::API_PATH;

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
