<?php declare(strict_types=1);

namespace Everyware\Storage;

use Everyware\OcClient;
use Everyware\Storage\Contracts\SimpleCacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use RuntimeException;

class OcResponseCache
{
    /**
     * @var string
     */
    public const KEY_PREFIX = 'oc_r_';

    /**
     * @var OcClient
     */
    private $client;

    /**
     * @var SimpleCacheInterface
     */
    private $cache;

    /**
     * @var int
     */
    private $ttl;

    public function __construct(OcClient $client, SimpleCacheInterface $cache, $ttl = 0)
    {
        $this->client = $client;
        $this->cache = $cache;
        $this->ttl = $ttl;
    }

    public function getContent(string $uri, array $params = []): string
    {
        try {
            $key = $this->generateKey($uri, $params);
            $response = $this->cache->get($key);

            if ($response !== null) {
                return $response;
            }

            $response = $this->client->getContent($uri, $params);

            $success = $this->cache->set($key, $response, $this->ttl);

            if ( ! $success) {
                throw new RuntimeException(sprintf('Could not add response of request against "%s" to cache', $uri));
            }

            return $response;

        } catch (InvalidArgumentException $e) {
            throw new RuntimeException(sprintf('Failed to create proper cache key out of uri: "%s"', $uri));
        }
    }

    public function generateKey(string $uri, array $params = []): string
    {
        return self::KEY_PREFIX . md5($uri . http_build_query($params));
    }
}
