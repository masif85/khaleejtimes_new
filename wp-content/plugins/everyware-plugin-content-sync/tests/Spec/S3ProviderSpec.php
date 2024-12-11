<?php

namespace Spec\Everyware\Plugin\ContentSync;

use Aws\Result;
use Aws\S3\S3Client;
use Everyware\Plugin\ContentSync\S3Provider;
use PhpSpec\ObjectBehavior;

class S3ProviderSpec extends ObjectBehavior
{
    /**
     * @var Result
     */
    private $awsResult;

    private $bucket = 'bucket';
    private $configFile = 'config.json';
    private $sitesFile = 'sites.json';
    private $eventlogFile = 'eventlog-id.txt';

    private $defaultConfig = <<<JSON
{
    "ocQuery": {
        "contenttype": "",
        "sort.indexfield": "created",
        "limit": 500,
        "start": 0,
        "properties": "uuid"
    },
    "contentTypes": [
        "Article",
        "Concept",
        "List"
    ],
    "apiPaths": {
        "Article": {
            "ADD": "content-sync/v1/trigger-event/",
            "UPDATE": "content-sync/v1/trigger-event/",
            "DELETE": "content-sync/v1/trigger-event/"
        },
        "Concept": {
            "ADD": "content-sync/v1/trigger-event/",
            "UPDATE": "content-sync/v1/trigger-event/",
            "DELETE": "content-sync/v1/trigger-event/"
        },
        "List": {
            "ADD": "content-sync/v1/trigger-event/",
            "UPDATE": "content-sync/v1/trigger-event/",
            "DELETE": "content-sync/v1/trigger-event/"
        }
    },
    "asynchronous": true,
    "sqsGroupsEventlog": 5,
    "sqsGroupsBatch": 10
}
JSON;


    /**
     * @var S3Client
     */
    private $client;

    public function let(S3Client $client, Result $awsResult): void
    {
        $this->client = $client;

        $this->awsResult = $awsResult;

        $this->beConstructedWith($client, $this->bucket);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(S3Provider::class);
    }

    public function it_will_fetch_content_from_a_choosen_S3_file()
    {
        $this->simulateGetFileContent($this->configFile, $this->defaultConfig);

        $this->getFileContent($this->configFile)->shouldReturn($this->defaultConfig);
    }

    public function it_can_determine_if_a_file_exists_in_S3()
    {
        $this->simulateFileExist('file.txt', true);

        $this->fileExist('file.txt')->shouldReturn(true);
    }

    public function it_will_fetch_config_from_S3()
    {
        $config = json_decode($this->defaultConfig, true, 512, JSON_THROW_ON_ERROR);

        $this->simulateGetFileContent($this->configFile, $this->defaultConfig);

        $this->getConfig()->shouldReturn($config);
    }

    public function it_will_return_empty_config_if_none_exists_on_S3()
    {
        $this->simulateGetFileContent($this->configFile, '');

        $this->getConfig()->shouldReturn([]);
    }

    public function it_should_add_content_to_file_on_S3()
    {
        $this->simulatePutFileContent($this->configFile, 'content');

        $this->putFileContent($this->configFile, 'content')->shouldReturn('content');
    }

    public function it_can_determine_if_a_config_file_exists_in_S3()
    {
        $this->simulateFileExist($this->configFile, true);

        $this->configExists()->shouldReturn(true);
    }

    public function it_should_update_config_file_in_S3()
    {
        $this->simulatePutFileContent($this->configFile, '{"myConfig":true}');

        $this->storeConfig([
            'myConfig'=> true
        ]);
    }

    public function it_should_update_config_with_empty_object_by_default()
    {
        $this->simulatePutFileContent($this->configFile, '{}');

        $this->storeConfig([]);
    }

    public function it_will_fetch_registered_sites_from_S3()
    {
        $this->simulateGetFileContent($this->sitesFile, <<<JSON
{
  "sites": [
    "site.one"
  ]
}
JSON
        );

        $this->getSites()->shouldReturn([
            'site.one'
        ]);
    }

    public function it_will_return_empty_array_of_sites_if_none_exists_on_S3()
    {
        $this->simulateGetFileContent($this->sitesFile, '');

        $this->getSites()->shouldReturn([]);
    }

    public function it_should_update_sites_file_in_S3()
    {
        $this->simulatePutFileContent($this->sitesFile, '{"sites":["site.one"]}');

        $this->storeSites([
            'site.one'
        ]);
    }

    public function it_will_fetch_last_handled_event_id_from_S3()
    {
        $this->simulateGetFileContent($this->eventlogFile, 0);

        $this->getLastEventId()->shouldReturn(0);
    }

    public function it_has_a_getter_for_the_bucket_name_in_S3()
    {
        $this->bucketName()->shouldReturn($this->bucket);
    }

    public function it_has_a_getter_for_the_bucket_url_in_S3()
    {
        $this->bucketUrl()->shouldReturn("https://s3.console.aws.amazon.com/s3/buckets/$this->bucket");
    }

    private function simulateGetFileContent(string $filename, string $content)
    {
        $this->client->getObject([
            'Bucket' => $this->bucket,
            'Key' => $filename
        ])
            ->shouldBeCalled()
            ->willReturn($this->awsResult);

        $this->awsResult->get('Body')->willReturn($content);
    }

    private function simulateFileExist(string $filename, bool $exist)
    {
        $this->client->doesObjectExist($this->bucket, $filename)
            ->shouldBeCalled()
            ->willReturn($exist);
    }

    private function simulatePutFileContent(string $filename, string $content, string $contentType='application/json')
    {
        $this->client->putObject([
            'Bucket' => $this->bucket,
            'Key' => $filename,
            'Body' => $content,
            'ContentType'=> $contentType
        ])
            ->shouldBeCalled()
            ->willReturn($this->awsResult);

        $this->awsResult->get('Body')->willReturn($content);
    }
}
