<?php declare(strict_types=1);

namespace Everyware;

use Everyware\Contracts\OcObject;
use Everyware\Exceptions\ObjectNotFoundException;
use Everyware\OcObjects\ContentType;
use Everyware\OcObjects\EmptyOcObject;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use OpenContent;
use Psr\Http\Message\ResponseInterface;

class OcClient
{
    private const OC_VERSION_PATH = 'infoandstats/version';

    /**
     * @var Client
     */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param string $uri
     * @param array  $options
     *
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function get(string $uri, array $options = []): ResponseInterface
    {
        return $this->client->get($uri, $options);
    }

    /**
     * @param int|null $id
     * @param array    $params
     *
     * @return array
     * @throws GuzzleException
     */
    public function events(int $id = null, array $params = []): array
    {
        // Make sure to add the event as parameter
        if (is_int($id)) {
            $params['event'] = $id;
        }

        $response = $this->getContent('eventlog', $params);
        $body = json_decode($response, true);

        return $body['events'] ?? [];
    }

    /**
     * @param string $uri
     * @param array  $params
     *
     * @return string|null
     * @throws GuzzleException
     */
    public function getContent(string $uri, array $params = []): string
    {
        $options = [];

        if( !empty($params) ) {
            $options['query'] = $params;
        }

        $response = $this->get($uri, $options);

        return $response->getBody()->getContents() ?? '';
    }

    /**
     * @param string $uuid
     * @param array  $params
     *
     * @return OcObject
     * @throws GuzzleException
     */
    public function getObject(string $uuid, array $params = []): OcObject
    {
        try {
            $content = $this->getContent("objects/{$uuid}/properties", $params);

            return $this->createOcObject($uuid, $content);
        } catch (GuzzleException $e) {
            $this->reValidateGuzzleException($e);
        }
    }

    /**
     * @return string|null
     * @throws GuzzleException
     */
    public function getOcVersion(): ?string
    {
        return $this->getContent(static::OC_VERSION_PATH);
    }

    /**
     * @param string $query
     * @param array  $params
     *
     * @return array
     * @throws GuzzleException
     */
    public function search(string $query, array $params = []): array
    {
        // Make sure to add the query as parameter
        if ( ! empty($query)) {
            $params['q'] = $query;
        }

        $response = $this->getContent('search', $params);
        $body = json_decode($response, true);

        return $body['hits'] ?? [];
    }

    /**
     * @return bool
     * @throws GuzzleException
     */
    public function testConnection(): bool
    {
        return $this->testPath(static::OC_VERSION_PATH, 200);
    }

    /**
     * @param string $user
     * @param string $password
     *
     * @return bool
     * @throws GuzzleException
     */
    public function testCredentials(string $user, string $password): bool
    {
        return $this->testPath(static::OC_VERSION_PATH, 200, [
            'auth' => [$user, $password]
        ]);
    }

    /**
     * @param string $path
     * @param int    $code
     * @param array  $options
     *
     * @return bool
     * @throws GuzzleException
     */
    public function testPath(string $path, int $code, array $options = []): bool
    {
        $options['http_errors'] = false;

        $response = $this->client->request('head', $path, $options);

        return $response->getStatusCode() === $code;
    }

    /**
     * @param OpenContent $ocInstance
     *
     * @return OcClient
     */
    public static function createFromWpSettings(OpenContent $ocInstance): OcClient
    {
        return static::create(
            $ocInstance->getOcBaseUrl() ?? '',
            $ocInstance->getOcUserName(),
            $ocInstance->getOcPassword()
        );
    }

    /**
     * @param $uri
     * @param $user
     * @param $password
     *
     * @return OcClient
     */
    public static function create($uri, $user, $password): OcClient
    {
        $clientOptions = [
            'base_uri' => $uri,
            'auth' => [$user, $password],
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8',
                'Accept' => 'text/plain, application/json;'
            ]
        ];

        return new static(new Client($clientOptions));
    }

    /**
     * @param string $uuid
     * @param string $content
     *
     * @return OcObject
     */
    private function createOcObject(string $uuid, string $content): OcObject
    {
        return ! empty($content) ? new ContentType($content) : new EmptyOcObject($uuid, $content);
    }

    /**
     * @param GuzzleException $exception
     *
     * @throws GuzzleException
     */
    private function reValidateGuzzleException(GuzzleException $exception): void
    {
        if ( ! $exception instanceof ClientException) {
            throw $exception;
        }

        if ($exception->getCode() === 404) {
            throw new ObjectNotFoundException($exception);
        }

        throw $exception;
    }
}
