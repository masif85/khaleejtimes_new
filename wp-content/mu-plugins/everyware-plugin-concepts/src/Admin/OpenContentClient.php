<?php declare(strict_types=1);

namespace Everyware\Concepts\Admin;

use GuzzleHttp\Client;
use OpenContent;

/**
 * Class OpenContentClient
 * @package Everyware\Concepts\Commands
 */
class OpenContentClient
{
    /**
     * @var string
     */
    private $baseUri;
    /**
     * @var array
     */
    private $credentials = [
        'user' => '',
        'password' => ''
    ];

    private $headers = [
        'Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8',
        'Accept' => 'application/json;'
    ];

    public function getBaseUri(): string
    {
        return $this->baseUri;
    }

    public function getCredentials(): array
    {
        return $this->credentials;
    }

    public function setBaseUri(string $targetOc, string $scheme = 'https'): OpenContentClient
    {
        $urlParts = parse_url($targetOc);

        // Change scheme if set in OC url
        if (isset($urlParts['scheme'])) {
            $scheme = $urlParts['scheme'];

            // Or match it if to port if set in OC url
        } elseif (isset($urlParts['port'])) {
            $scheme = $urlParts['port'] === 8443 ? 'https' : 'http';
        }

        $host = $urlParts['host'] ?? $targetOc;

        // Always match port with scheme
        $port = $scheme === 'https' ? 8443 : 8080;

        $this->baseUri = "{$scheme}://{$host}:{$port}/opencontent/";
        return $this;
    }

    public function setCredentials($user, $password): void
    {
        $this->credentials['user'] = $user;
        $this->credentials['password'] = $password;
    }

    /**
     * Maximum of 100 events are returned.
     * If following events are wanted next request must use the latest event id in the previous response.
     * Event id = -1 will return the current latest event.
     * Event id = -12 will return the current latest 12 events and so on.
     *
     * @param int|null $id
     *
     * @return array
     */
    public function events(int $id = null): array
    {
        $params = ['event' => $id];
        $response = $this->client()->get('eventlog', ['query' => $params]);
        $body = json_decode($response->getBody()->getContents(), true);

        return $body['events'] ?? [];
    }

    /**
     * OpenContent search
     *
     * @param array $params
     *
     * @return array
     */
    public function search(array $params = []): array
    {
        $response = $this->client()->get('search', ['query' => $params]);
        $body = json_decode($response->getBody()->getContents(), true);

        return $body['hits'] ?? [];
    }

    /**
     * limit = 1 will return the current latest event.
     * limit = 12 will return the current latest 12 events and so on.
     *
     * @param int $limit
     *
     * @return array
     */
    public function latestEvents(int $limit = 1): array
    {
        return $this->events(0 - max(abs($limit), 1));
    }

    /**
     * Will return the first event found.
     *
     * @return array Will return empty array if no event was found
     */
    public function firstEvent(): array
    {
        $events = $this->events(0);

        return (array)array_pop($events);
    }

    /**
     * Will return the Id of the first event found.
     * @return int|null
     */
    public function firstEventId(): ?int
    {
        $event = $this->firstEvent();

        return $event['id'] ?? null;
    }

    /**
     * Will return the last event found.
     * @return array Will return empty array if no event was found
     */
    public function lastEvent(): array
    {
        $events = $this->latestEvents(1);

        return (array)array_pop($events);
    }

    /**
     * Will return the Id of the last event found.
     * @return int|null
     */
    public function lastEventId(): ?int
    {
        $event = $this->lastEvent();

        return $event['id'] ?? null;
    }

    public function fetchAll(): array
    {
        $lastFetch = 0;
        $uuids = [[]];

        do {
            $response = $this->search([
                'contenttype' => 'Concept',
                'sort.indexfield' => 'created',
                'limit' => 300,
                'start' => $lastFetch,
                'properties' => 'uuid'
            ]);

            if ( ! isset($response['hits'])) {
                break;
            }

            $fetchedUuids = $this->extractUuids($response);
            $totalFound = $response['totalHits'] ?? 0;

            $lastFetch += count($fetchedUuids);
            $uuids[] = $fetchedUuids;

        } while ($lastFetch < $totalFound);

        return array_merge(...$uuids);
    }

    public function lastOcUpdate()
    {
// Fetch last updated object
        $response = $this->search([
            'start' => 0,
            'sort.indexfield' => 'updated',
            'sort.updated.ascending' => 'false',
            'limit' => 1,
            'properties' => implode(',', ['uuid', 'updated']),
        ]);

        if ( ! isset($response['hits'])) {
            return [];
        }
        $fetchedObject = $this->extractProperties(array_pop($response['hits']));

        return [
            'uuid' => array_pop($fetchedObject['uuid']),
            'updated'=> array_pop($fetchedObject['updated'])
        ];
    }

    private function client(array $options = []): Client
    {
        return new Client(array_replace([
            'base_uri' => $this->baseUri,
            'auth' => [
                $this->credentials['user'],
                $this->credentials['password']
            ],
            'headers' => $this->headers
        ], $options));
    }

    private function extractUuids($response): array
    {
        if ( ! isset($response['hits'])) {
            return [];
        }

        return array_column($response['hits'], 'id');
    }

    public function isValidEvent($eventId): bool
    {
        if ( ! $eventId) {
            return false;
        }

        $events = $this->events($eventId);

        // Invalid ff the event has not yet been created
        if (empty($events)) {
            return false;
        }

        // Valid if it can be found among the fetched events. (Else it's most likely old)
        return in_array($eventId, array_column($events, 'id'), true);
    }

    public static function createFromWpSettings(OpenContent $ocInstance): OpenContentClient
    {
        $ocClient = new static();
        $ocClient->setBaseUri($ocInstance->getOcBaseUrl() ?? '');

        $ocClient->setCredentials(
            $ocInstance->getOcUserName(),
            $ocInstance->getOcPassword()
        );

        return $ocClient;

    }

    public function extractProperties(array $item): array
    {
        $versions = $item['versions'] ?? [];

        if ( ! empty($versions)) {
            $version = array_pop($versions);

            return $version['properties'] ?? [];
        }

        return [];
    }
}
