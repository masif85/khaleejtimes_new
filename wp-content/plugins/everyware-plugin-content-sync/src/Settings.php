<?php

namespace Everyware\Plugin\ContentSync;

use Everyware\Plugin\ContentSync\Wordpress\WpNetworkOptions;
use Everyware\Plugin\ContentSync\Wordpress\WpSites;

class Settings
{
    public const API_PATH = 'content-sync/v1/trigger-event';

    public const CONTENT_SYNC_OPTIONS = 'content_sync_options';

    private S3Provider $s3Provider;

    private WpNetworkOptions $wpNetworkOptions;

    private WpSites $wpSites;

    public function __construct(
        S3Provider $s3Provider,
        WpSites $wpSites,
        WpNetworkOptions $networkOptions
    ) {
        $this->s3Provider = $s3Provider;
        $this->wpSites = $wpSites;
        $this->wpNetworkOptions = $networkOptions;
    }

    public function currentConfig(): array
    {
        return $this->s3Provider->getConfig();
    }

    public function deployConfig(): void
    {
        $this->s3Provider->storeConfig($this->generateConfig());
    }

    public function deploySites(): void
    {
        $this->s3Provider->storeSites($this->generateSiteMap());
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

    public function generateSiteMap(): array
    {
        $options = $this->getOptions();

        return $options['sites'];
    }

    public function getBucketUrl(): string
    {
        return $this->s3Provider->bucketUrl();
    }

    public function registerSite(string $url): void
    {
        $registeredSites = $this->registeredSites();

        if ( ! in_array($url, $registeredSites, true)) {
            $registeredSites[] = $url;

            $this->s3Provider->storeSites($registeredSites);
        }
    }

    public function registeredSites(): array
    {
        return $this->s3Provider->getSites();
    }

    public function replaceSite(string $oldUrl, string $newUrl): void
    {
        $registeredSites = array_values($this->s3Provider->getSites());

        if (($key = array_search($oldUrl, $registeredSites, true)) !== false) {
            $registeredSites[$key] = $newUrl;

            $this->s3Provider->storeSites($registeredSites);

        } elseif ( ! in_array($newUrl, $registeredSites, true)) {
            $registeredSites[] = $newUrl;

            $this->s3Provider->storeSites($registeredSites);
        }
    }

    public function unregisterSite(string $url): void
    {
        $registeredSites = $this->s3Provider->getSites();

        if (($key = array_search($url, $registeredSites, true)) !== false) {
            unset($registeredSites[$key]);

            $this->s3Provider->storeSites(array_values($registeredSites));
        }
    }

    public function removeSiteFromStore(string $url): void
    {
        $options = $this->getOptions();
        $storedSites = $options['sites'];

        if (($key = array_search($url, $storedSites, true)) !== false) {
            unset($storedSites[$key]);

            $options['sites'] = $storedSites;

            $this->updateOptions($options);
        }
    }

    public function replaceSiteFromStore(string $oldUrl, string $newUrl): void
    {
        $options = $this->getOptions();
        $storedSites = $options['sites'];

        if (($key = array_search($oldUrl, $storedSites, true)) !== false) {
            $storedSites[$key] = $newUrl;

            $options['sites'] = $storedSites;

            $this->updateOptions($options);

        } elseif ( ! in_array($newUrl, $storedSites, true)) {
            $storedSites[] = $newUrl;

            $options['sites'] = $storedSites;

            $this->updateOptions($options);
        }
    }

    public function addSiteToStore(string $url): void
    {
        $options = $this->getOptions();
        $storedSites = $options['sites'];

        if ( ! in_array($url, $storedSites, true)) {
            $storedSites[] = $url;

            $options['sites'] = $storedSites;

            $this->updateOptions($options);
        }
    }

    private function getOptions(): array
    {
        $options = $this->wpNetworkOptions->get(static::CONTENT_SYNC_OPTIONS, []);
        $sites = $this->wpSites->subSiteUrls();

        if (empty($options) || $options['sites'] !== $sites) {
            $options['sites'] = $sites;

            $this->updateOptions($options);
        }

        return $options;
    }

    private function updateOptions($options): void
    {
        $this->wpNetworkOptions->update(static::CONTENT_SYNC_OPTIONS, $options);
    }
}
