<?php declare(strict_types=1);

namespace Everyware\Storage;

use Everyware\Contracts\OcObject;
use Everyware\Exceptions\ObjectNotFoundException;
use Everyware\OcClient;
use Everyware\OcObjects\EmptyOcObject;
use Everyware\Storage\Contracts\SimpleCacheInterface;
use Everyware\Storage\Traits\MemoryStorage;
use GuzzleHttp\Exception\GuzzleException;
use Psr\SimpleCache\InvalidArgumentException;
use RuntimeException;

class OcObjectCache
{
    use MemoryStorage;

    /**
     * @var string
     */
    public const KEY_PREFIX = 'oc_d_';

    /**
     * @var OcClient
     */
    private $client;

    /**
     * @var SimpleCacheInterface
     */
    private $cache;

    /**
     * @var int|null
     */
    private $ttl;

    public function __construct(OcClient $client, SimpleCacheInterface $cache, int $ttl = 0)
    {
        $this->client = $client;
        $this->cache = $cache;
        $this->ttl = abs($ttl);
    }

    /**
     * @param string $uuid
     *
     * @throws GuzzleException
     */
    public function add(string $uuid): void
    {
        try {
            $this->storeObject($this->client->getObject($uuid));
        } catch (ObjectNotFoundException $e) {
            $this->storeObject(new EmptyOcObject($uuid));
        }
    }

    /**
     * @param string $uuid
     *
     * @return OcObject
     * @throws GuzzleException
     */
    public function get(string $uuid): ?OcObject
    {
        try {
            $object = $this->getStoredObject($uuid);

            if ($object instanceof EmptyOcObject) {
                return null;
            }

            if ($object instanceof OcObject) {
                return $object;
            }

            $object = $this->client->getObject($uuid);

            $this->storeObject($object);

            return $object;

        } catch (ObjectNotFoundException $e) {
            $this->storeObject(new EmptyOcObject($uuid));
        }

        return null;
    }

    /**
     * @param string $uuid
     *
     * @throws GuzzleException
     */
    public function update(string $uuid): void
    {
        try {
            if ($this->exists($uuid)) {
                $this->storeObject($this->client->getObject($uuid));
            }
        } catch (ObjectNotFoundException $e) {
            $this->delete($uuid);
        }
    }

    public function delete(string $uuid): void
    {
        try {
            $this->removeFromMemory($uuid);

            $key = $this->generateKey($uuid);

            $success = ! $this->cache->has($key) || $this->cache->delete($key);

            if ( ! $success) {
                throw new RuntimeException(sprintf('Could not delete object with uuid "%s" from cache', $uuid));
            }

        } catch (InvalidArgumentException $e) {
            @trigger_error($e->getMessage());
        }
    }

    public function exists(string $uuid): bool
    {
        try {
            $key = $this->generateKey($uuid);

            return $this->inMemory($uuid) || $this->cache->has($key);
        } catch (InvalidArgumentException $e) {
            @trigger_error($e->getMessage());
        }

        return false;
    }

    public function getMultiple(iterable $uuids): iterable
    {
        $objects = [];

        foreach ($uuids as $uuid) {
            $object = $this->get($uuid);

            if ($object instanceof OcObject) {
                $objects[$uuid] = $object;
            }
        }

        return $objects;
    }

    public function addMultiple(iterable $uuids): void
    {
        foreach ($uuids as $uuid) {
            $this->add($uuid);
        }
    }

    public function deleteMultiple(iterable $uuids): void
    {
        foreach ($uuids as $uuid) {
            $this->delete($uuid);
        }
    }

    public function pull($uuid): ?OcObject
    {
        $this->removeFromMemory($uuid);

        $key = $this->generateKey($uuid);

        return $this->cache->pull($key);
    }

    public function getRelatedObjectsFromProperty($uuid, $propertyName): iterable
    {
        $object = $this->get($uuid);

        if ( ! $object instanceof OcObject) {
            throw new \InvalidArgumentException(
                sprintf('Could not find Object with uuid "%s" to find relations to', $uuid)
            );
        }

        $properties = $object->getProperties();

        if ( ! array_key_exists($propertyName, $properties)) {
            throw new \InvalidArgumentException(
                sprintf('Cant find property "%s" on object with uuid "%s"', $propertyName, $uuid)
            );
        }

        return $this->getMultiple((array)$properties[$propertyName]);
    }

    private function getTTL(OcObject $object): int
    {
        $objectTTL = $object->getCacheTTL() ?? $this->ttl;

        if ($objectTTL > 0) {
            return $objectTTL;
        }

        return $this->ttl;
    }

    private function getStoredObject($uuid): ?OcObject
    {
        try {
            $key = $this->generateKey($uuid);

            $object = $this->getFromMemory($uuid);

            if ($object instanceof OcObject) {
                return $object;
            }

            $object = $this->cache->get($key);

            if ($object instanceof OcObject) {
                $this->addToMemory($uuid, $object);

                return $object;
            }
        } catch (InvalidArgumentException $e) {
            @trigger_error($e->getMessage());
        }

        return null;
    }

    private function storeObject(OcObject $object): void
    {
        try {
            $uuid = $object->getUuid();
            $key = $this->generateKey($uuid);

            $success = $this->cache->set($key, $object, $this->getTTL($object));

            if ( ! $success) {
                throw new RuntimeException(sprintf('Could not add object with uuid: "%s" in object cache', $uuid));
            }

            $this->addToMemory($uuid, $object);

        } catch (InvalidArgumentException $e) {
            @trigger_error($e->getMessage());
        }
    }

    public function generateKey($uuid): string
    {
        return self::KEY_PREFIX . $uuid;
    }
}
