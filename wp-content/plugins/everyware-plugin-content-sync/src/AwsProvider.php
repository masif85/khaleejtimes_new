<?php

namespace Everyware\Plugin\ContentSync;

use Aws\Sdk;
use Everyware\Plugin\ContentSync\Exceptions\MissingAwsCredentialsException;
use Everyware\Plugin\ContentSync\Exceptions\MissingAwsRegionException;
use Infomaker\Everyware\Support\Environment;
use Infomaker\Everyware\Support\Str;

class AwsProvider
{
    protected static array $regions = [
        'us-east-1' => 'US East (N. Virginia)',
        'us-east-2' => 'US East (Ohio)',
        'us-west-1' => 'US West (N. California)',
        'us-west-2' => 'US West (Oregon)',
        'ca-central-1' => 'Canada (Central)',
        'af-south-1' => 'Africa (Cape Town)',
        'ap-east-1' => 'Asia Pacific (Hong Kong)',
        'ap-south-1' => 'Asia Pacific (Mumbai)',
        'ap-northeast-2' => 'Asia Pacific (Seoul)',
        'ap-northeast-3' => 'Asia Pacific (Osaka-Local)',
        'ap-southeast-1' => 'Asia Pacific (Singapore)',
        'ap-southeast-2' => 'Asia Pacific (Sydney)',
        'ap-northeast-1' => 'Asia Pacific (Tokyo)',
        'cn-north-1' => 'China (Beijing)',
        'cn-northwest-1' => 'China (Ningxia)',
        'eu-central-1' => 'EU (Frankfurt)',
        'eu-west-1' => 'EU (Ireland)',
        'eu-west-2' => 'EU (London)',
        'eu-south-1' => 'EU (Milan)',
        'eu-west-3' => 'EU (Paris)',
        'eu-north-1' => 'EU (Stockholm)',
        'me-south-1' => 'Middle East (Bahrain)',
        'sa-east-1' => 'South America (Sao Paulo)'
    ];
    /**
     * @var Sdk
     */
    private static $sdk;

    public static function createSDK(array $args = []): Sdk
    {
        if( static::$sdk === null ) {
            if ( ! isset($args['version'])) {
                $args['version'] = 'latest';
            }

            if ( ! isset($args['region'])) {
                $args['region'] = static::getAwsRegion();
            }

            if ( ! isset($args['credentials'])) {
                $credentials = static::getIAMCredentials();
                if ($credentials) {
                    $args['credentials'] = $credentials;
                }
            }

            static::$sdk = new Sdk($args);
        }

        return static::$sdk;
    }

    public static function createS3Provider(): S3Provider
    {
        return new S3Provider(static::createSDK()->createS3(), static::generateS3BucketName());
    }

    public static function generateS3BucketName(): string
    {
        return implode('-', [
            env('APP_ORGANISATION', ''),
            'i',
            'web-content-sync',
            Environment::current(),
            'config'
        ]);
    }

    public static function getAwsRegion(string $url = ''): string
    {
        if (empty($url)) {
            $url = (string)env('AWS_REGION', '');
        }

        if (empty($url) && defined('DB_HOST')) {
            $url = DB_HOST;
        }

        $awsRegion = '';

        foreach (array_keys(static::$regions) as $region) {
            if (Str::contains($url, $region)) {
                $awsRegion = $region;
            }
        }

        return $awsRegion;
    }

    public static function getIAMCredentials(): array
    {
        if ( ! defined('AWS_ACCESS_KEY_ID') || ! defined('AWS_SECRET_ACCESS_KEY')) {
            return [];
        }

        return [
            'key' => AWS_ACCESS_KEY_ID,
            'secret' => AWS_SECRET_ACCESS_KEY,
        ];
    }

    /**
     * @throws MissingAwsCredentialsException
     * @throws MissingAwsRegionException
     */
    public static function validateEnvironment(): void
    {
        if ( ! defined('AWS_ACCESS_KEY_ID') || ! defined('AWS_SECRET_ACCESS_KEY')) {
            throw new MissingAwsCredentialsException('Missing required IAM credentials.');
        }

        if (empty(static::getAwsRegion())) {
            throw new MissingAwsRegionException('Missing required AWS region.');
        }
    }
}
