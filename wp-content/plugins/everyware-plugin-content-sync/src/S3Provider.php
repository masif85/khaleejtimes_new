<?php

namespace Everyware\Plugin\ContentSync;

use Aws\S3\S3Client;
use Exception;
use Infomaker\Everyware\Support\NewRelicLog;

class S3Provider
{
    private const BASE_BUCKET_URL = 'https://s3.console.aws.amazon.com/s3/buckets';

    private static array $files = [
        'config' => 'config.json',
        'sites' => 'sites.json',
        'last_event' => 'eventlog-id.txt',
    ];

    /** @var string Name of the dedicated Content Sync bucket */
    private string $bucket;

    private S3Client $client;

    public function __construct(S3Client $client, string $bucket)
    {
        $this->client = $client;
        $this->bucket = $bucket;
    }

    public function bucketName(): string
    {
        return $this->bucket;
    }

    public function bucketUrl(): string
    {
        return static::BASE_BUCKET_URL . "/$this->bucket";
    }

    public function configExists(): bool
    {
        return $this->fileExist(static::$files['config']);
    }

    public function getConfig(): array
    {
        try {
            return json_decode($this->getFileContent(static::$files['config']), true, 512, JSON_THROW_ON_ERROR);
        } catch (Exception $e) {
            NewRelicLog::error($e->getMessage());
        }

        return [];
    }

    public function getFileContent(string $filename): string
    {
        $result = $this->client->getObject([
            'Bucket' => $this->bucket,
            'Key' => $filename
        ]);

        return (string)$result->get('Body');
    }

    public function putFileContent(string $filename, string $content, string $contentType='application/json'): string
    {
        $result = $this->client->putObject([
            'Bucket' => $this->bucket,
            'Key' => $filename,
            'Body' => $content,
            'ContentType'=> $contentType
        ]);

        return (string)$result->get('Body');
    }

    public function fileExist(string $filename): bool
    {
        return $this->client->doesObjectExist($this->bucket, $filename);
    }

    public function getSites(): array
    {
        try {
            $content = json_decode($this->getFileContent(static::$files['sites']), true, 512, JSON_THROW_ON_ERROR);

            return $content['sites'] ?? [];
        } catch (Exception $e) {
            NewRelicLog::error($e->getMessage());
        }

        return [];
    }

    public function getLastEventId(): int
    {
        return (int)$this->getFileContent(static::$files['last_event']);
    }

    public function storeConfig(array $config): void
    {
        try {
            $content = ! empty($config) ? $this->serializeArray($config) : '{}';
            $this->putFileContent(static::$files['config'], $content);
        } catch (Exception $e) {
            NewRelicLog::error($e->getMessage());
        }
    }

    public function storeSites(array $sites): void
    {
        try {
            $content = $this->serializeArray(['sites' => $sites]);
            $this->putFileContent(static::$files['sites'], $content);
        } catch (Exception $e) {
            NewRelicLog::error($e->getMessage());
        }
    }

    /**
     * @throws \JsonException
     */
    private function serializeArray(array $content): string
    {
        return json_encode($content, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }
}
