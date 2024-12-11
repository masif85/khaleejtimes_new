<?php

/** @noinspection PhpVoidFunctionResultUsedInspection */
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */
/** @noinspection PhpUnused */

namespace Spec\Everyware\Storage;

use Everyware\Contracts\OcObject;
use Everyware\Exceptions\ObjectNotFoundException;
use Everyware\OcClient;
use Everyware\OcObjects\EmptyOcObject;
use Everyware\Storage\Contracts\SimpleCacheInterface;
use Everyware\Storage\OcObjectCache;
use GuzzleHttp\Exception\ClientException;
use InvalidArgumentException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use RuntimeException;

class OcObjectCacheSpec extends ObjectBehavior
{
    /**
     * @var string
     */
    private $objectUuid = '6ff3bdaa-b381-5861-9b45-57ec0cbf6760';

    /**
     * @var OcClient
     */
    private $client;

    /**
     * @var SimpleCacheInterface
     */
    private $cache;

    /**
     * @var OcObject
     */
    private $ocObject;

    private $ttl = 60;

    function let(OcClient $client, SimpleCacheInterface $cache, OcObject $ocObject)
    {
        $this->client = $client;
        $this->cache = $cache;
        $this->ocObject = $ocObject;

        $this->beConstructedWith($client, $cache, $this->ttl);
        $this->clearMemory();
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(OcObjectCache::class);
    }

    function it_should_add_objects_to_the_store()
    {
        $this->simulateObjectAdded($this->objectUuid, $this->ocObject);

        $this->add($this->objectUuid);
    }

    function it_should_use_object_ttl_if_provided_when_adding_objects_to_the_cache()
    {
        $this->simulateObjectAdded($this->objectUuid, $this->ocObject, 100);

        $this->add($this->objectUuid);
    }

    function it_should_store_empty_object_in_cache_if_client_cant_find_object_during_add()
    {
        $this->simulateObjectNotFound($this->objectUuid);

        $this->add($this->objectUuid);
    }

    function it_should_throw_exception_if_object_was_not_added_to_the_cache()
    {
        $this->simulateObjectAddFailed($this->objectUuid, $this->ocObject);

        $this->shouldThrow(RuntimeException::class)->duringAdd($this->objectUuid);
    }

    function it_should_get_object_from_cache()
    {
        $uuid = $this->objectUuid;

        $this->simulateCacheGet($uuid, $this->ocObject);

        $this->get($uuid)->shouldReturn($this->ocObject);
    }

    function it_should_fetch_and_cache_objects_from_source_if_not_found_in_store()
    {
        $uuid = $this->objectUuid;

        $this->simulateCacheGet($uuid, null);

        $this->simulateObjectAdded($uuid, $this->ocObject);

        $this->get($uuid)->shouldReturn($this->ocObject);
    }

    function it_should_throw_exception_if_object_was_not_added_during_get()
    {
        $uuid = $this->objectUuid;

        $this->simulateCacheGet($uuid, null);

        $this->simulateObjectAddFailed($uuid, $this->ocObject);

        $this->shouldThrow(RuntimeException::class)->duringGet($uuid);
    }

    function it_should_throw_clientException_if_client_fails_during_get()
    {
        $uuid = $this->objectUuid;

        $this->simulateCacheGet($uuid, null);

        $this->client->getObject($uuid)->willThrow(ClientException::class);

        $this->shouldThrow(ClientException::class)->duringGet($uuid);
    }

    function it_should_store_empty_object_in_cache_if_client_cant_find_object_during_get()
    {
        $uuid = $this->objectUuid;

        $this->simulateCacheGet($uuid, null);

        $this->simulateObjectNotFound($this->objectUuid);

        $this->get($uuid)->shouldReturn(null);
    }

    function it_should_return_null_if_empty_object_is_found_in_cache_during_get(EmptyOcObject $emptyObject)
    {
        $uuid = $this->objectUuid;

        $this->simulateCacheGet($uuid, $emptyObject);

        $this->get($uuid)->shouldReturn(null);
    }

    function it_should_throw_clientException_if_client_fails_during_add()
    {
        $uuid = $this->objectUuid;

        $this->client->getObject($uuid)->willThrow(ClientException::class);

        $this->shouldThrow(ClientException::class)->duringAdd($uuid);
    }

    function it_can_determine_if_a_object_exist_in_store()
    {
        $uuid = $this->objectUuid;

        $this->simulateCacheHas($uuid, true);

        $this->exists($uuid)->shouldReturn(true);
    }

    function it_can_remove_objects_from_cache()
    {
        $uuid = $this->objectUuid;

        $this->simulateCacheDelete($uuid, true);

        $this->delete($uuid);
    }

    function it_should_throw_exception_if_object_was_not_deleted_from_cache()
    {
        $uuid = $this->objectUuid;

        $this->simulateCacheDelete($uuid, false);

        $this->shouldThrow(RuntimeException::class)->duringDelete($uuid);
    }

    function it_should_only_throw_exception_if_object_exists_and_was_not_deleted_from_cache()
    {
        $uuid = $this->objectUuid;

        $this->simulateCacheDelete($uuid, false, false);

        $this->shouldNotThrow(RuntimeException::class)->duringDelete($uuid);
    }

    function it_should_update_existing_objects_in_store()
    {
        $uuid = $this->objectUuid;

        $this->simulateCacheHas($uuid, true);

        $this->simulateObjectAdded($uuid, $this->ocObject, $this->ttl);

        $this->update($uuid);
    }

    function it_should_only_update_existing_objects_in_cache()
    {
        $uuid = $this->objectUuid;
        $key = $this->generateKey($uuid);

        $this->simulateCacheHas($uuid, false);

        $this->client->getObject($uuid)->shouldNotBeCalled();

        $this->cache->set($key, $this->ocObject)->shouldNotBeCalled();

        $this->update($uuid);
    }

    function it_should_remove_objects_from_cache_if_they_cant_be_found_while_updating()
    {
        $uuid = $this->objectUuid;

        $this->simulateCacheHas($uuid, true);

        $this->client->getObject($uuid)->willThrow(ObjectNotFoundException::class);

        $this->simulateCacheDelete($uuid, true);

        $this->update($uuid);
    }

    function it_should_not_remove_objects_from_cache_if_client_fails_to_fetch_object_while_updating()
    {
        $uuid = $this->objectUuid;
        $key = $this->generateKey($uuid);

        $this->simulateCacheHas($uuid, true);

        $this->client->getObject($uuid)->willThrow(ClientException::class);

        $this->cache->delete($key)->shouldNotBeCalled();

        $this->shouldThrow(ClientException::class)->duringUpdate($uuid);
    }

    function it_should_get_multiple_objects_from_cache()
    {
        $uuid = $this->objectUuid;

        $this->simulateCacheGet($uuid, $this->ocObject);

        $this->getMultiple([$uuid])->shouldReturn([$uuid => $this->ocObject]);
    }

    function it_should_only_multiple_get_objects_that_can_be_found()
    {
        $baseUuid = $this->objectUuid;

        $objectsToGet = [
            "{$baseUuid}-1" => $this->ocObject,
            "{$baseUuid}-2" => null,
            "{$baseUuid}-3" => $this->ocObject
        ];

        foreach ($objectsToGet as $uuid => $object) {
            $this->simulateCacheGet($uuid, $object);

            if ( ! $object instanceof OcObject) {
                $this->simulateObjectNotFound($uuid);
            }
        }

        $this->getMultiple(array_keys($objectsToGet))->shouldReturn([
            "{$baseUuid}-1" => $this->ocObject,
            "{$baseUuid}-3" => $this->ocObject
        ]);
    }

    function it_should_add_multiple_objects_to_the_cache()
    {
        $uuid = $this->objectUuid;
        $this->simulateObjectAdded($uuid, $this->ocObject);

        $this->addMultiple([$uuid]);
    }

    function it_should_throw_exception_if_one_or_more_objects_failed_to_be_added_to_the_cache()
    {
        $this->simulateObjectAdded('uuid', $this->ocObject);
        $this->simulateObjectAddFailed('failed', $this->ocObject);

        $this->shouldThrow(RuntimeException::class)->duringAddMultiple([
            'uuid',
            'failed'
        ]);
    }

    function it_should_delete_multiple_objects_from_cache()
    {
        $uuidBase = $this->objectUuid;

        $uuids = [
            "{$uuidBase}-1" => true,
            "{$uuidBase}-2" => true
        ];

        foreach ($uuids as $uuid => $success) {
            $this->simulateCacheDelete($uuid, $success);
        }

        $this->deleteMultiple(array_keys($uuids));
    }

    function it_should_throw_exception_if_one_or_more_objects_failed_to_be_deleted_from_the_cache()
    {
        $uuidBase = $this->objectUuid;

        $uuids = [
            "{$uuidBase}-1" => true,
            "{$uuidBase}-2" => false
        ];

        foreach ($uuids as $uuid => $success) {
            $this->simulateCacheDelete($uuid, $success);
        }

        $this->shouldThrow(RuntimeException::class)->duringDeleteMultiple(array_keys($uuids));
    }

    function it_can_pull_object_from_cache()
    {
        $uuid = $this->objectUuid;

        $this->simulateCachePull($uuid, $this->ocObject);

        $this->pull($uuid)->shouldReturn($this->ocObject);
    }

    function it_can_fetch_related_objects_from_provided_object_multi_valued_property()
    {
        $uuid = $this->objectUuid;
        $property = 'ArticleUuids';
        $relatedObjectUuids = ['uuid1', 'uuid2'];
        $relatedObjects = [];

        $this->simulateCacheGet($uuid, $this->ocObject);

        $this->ocObject->getProperties()->willReturn([$property => $relatedObjectUuids]);

        foreach ($relatedObjectUuids as $objectUuid) {
            $this->simulateCacheGet($objectUuid, $this->ocObject);
            $relatedObjects[$objectUuid] = $this->ocObject;
        }

        $this->getRelatedObjectsFromProperty($uuid, $property)->shouldReturn($relatedObjects);
    }

    function it_can_fetch_related_objects_from_provided_object_single_valued_property(OcObject $relatedObject)
    {
        $uuid = $this->objectUuid;

        $property = 'ParentUuid';
        $relatedObjectUuid = 'uuid';

        $this->simulateCacheGet($uuid, $this->ocObject);

        $this->ocObject->getProperties()->willReturn([$property => $relatedObjectUuid]);

        $this->simulateCacheGet($relatedObjectUuid, $relatedObject);

        $this->getRelatedObjectsFromProperty($uuid, $property)->shouldReturn([
            $relatedObjectUuid => $relatedObject
        ]);
    }

    function it_should_throw_exception_if_the_object_cant_be_found_when_fetching_related_objects()
    {
        $uuid = $this->objectUuid;
        $this->simulateCacheGet($uuid, null);

        $this->simulateObjectNotFound($uuid);

        $this
            ->shouldThrow(InvalidArgumentException::class)
            ->duringGetRelatedObjectsFromProperty($uuid, 'property');
    }

    function it_should_throw_exception_if_property_cant_be_found_when_fetching_related_objects()
    {
        $uuid = $this->objectUuid;
        $relatedObjectUuids = ['uuid1', 'uuid2'];

        $this->simulateCacheGet($uuid, $this->ocObject);

        $this->ocObject->getProperties()->willReturn([
            'ArticleUuids' => $relatedObjectUuids
        ]);

        $this
            ->shouldThrow(InvalidArgumentException::class)
            ->duringGetRelatedObjectsFromProperty($uuid, 'property');
    }

    function it_should_store_added_objects_in_memory()
    {
        $uuid = $this->objectUuid;

        $this->simulateObjectAdded($uuid, $this->ocObject);

        $this->inMemory($uuid)->shouldEqual(false);
        $this->add($uuid);
        $this->inMemory($uuid)->shouldEqual(true);
        $this->getFromMemory($uuid)->shouldBe($this->ocObject);
    }

    function it_should_store_objects_fetched_from_cache_in_memory()
    {
        $uuid = $this->objectUuid;
        $this->simulateCacheGet($uuid, $this->ocObject);

        $this->inMemory($uuid)->shouldEqual(false);

        $this->get($uuid)->shouldReturn($this->ocObject);

        $this->inMemory($uuid)->shouldEqual(true);
        $this->getFromMemory($uuid)->shouldBe($this->ocObject);
    }

    function it_should_also_store_empty_objects_fetched_from_cache_in_memory(EmptyOcObject $emptyObject)
    {
        $uuid = $this->objectUuid;

        $this->simulateCacheGet($uuid, $emptyObject);

        $this->inMemory($uuid)->shouldEqual(false);

        $this->get($uuid)->shouldReturn(null);

        $this->inMemory($uuid)->shouldEqual(true);
        $this->getFromMemory($uuid)->shouldBe($emptyObject);
    }

    function it_should_store_objects_fetched_from_OpenContent_in_memory()
    {
        $uuid = $this->objectUuid;

        $this->simulateCacheGet($uuid, null);
        $this->simulateObjectAdded($uuid, $this->ocObject);

        $this->inMemory($uuid)->shouldEqual(false);

        $this->get($uuid)->shouldReturn($this->ocObject);

        $this->inMemory($uuid)->shouldEqual(true);
        $this->getFromMemory($uuid)->shouldBe($this->ocObject);
    }

    function it_should_also_store_empty_objects_fetched_from_OpenContent_in_memory()
    {
        $uuid = $this->objectUuid;

        $this->simulateCacheGet($uuid, null);
        $this->simulateObjectNotFound($uuid);

        $this->inMemory($uuid)->shouldBe(false);

        $this->get($uuid)->shouldReturn(null);

        $this->inMemory($uuid)->shouldBe(true);
        $this->getFromMemory($uuid)->shouldBeAnInstanceOf(EmptyOcObject::class);
    }

    function it_should_remove_pulled_objects_from_memory()
    {
        $uuid = $this->objectUuid;

        $this->addToMemory($uuid, $this->ocObject);
        $this->simulateCachePull($uuid, $this->ocObject);

        $this->inMemory($uuid)->shouldEqual(true);

        $this->pull($uuid)->shouldReturn($this->ocObject);

        $this->inMemory($uuid)->shouldEqual(false);
        $this->getFromMemory($uuid)->shouldBe(null);
    }

    function it_should_add_updated_objects_to_memory()
    {
        $uuid = $this->objectUuid;

        $this->simulateCacheHas($uuid, true);
        $this->simulateObjectAdded($uuid, $this->ocObject);

        $this->inMemory($uuid)->shouldBe(false);

        $this->update($uuid);

        $this->inMemory($uuid)->shouldBe(true);
        $this->getFromMemory($uuid)->shouldBe($this->ocObject);
    }

    function it_should_remove_object_from_memory_if_update_cant_find_it_in_OpenContent()
    {
        $uuid = $this->objectUuid;

        $this->simulateCacheHas($uuid, true);
        $this->simulateObjectNotFound($uuid);
        $this->simulateCacheDelete($uuid, true);

        $this->addToMemory($uuid, $this->ocObject);
        $this->inMemory($uuid)->shouldBe(true);

        $this->update($uuid);

        $this->inMemory($uuid)->shouldBe(false);
        $this->getFromMemory($uuid)->shouldBe(null);
    }

    function it_should_remove_object_from_memory_if_they_are_removed_from_cache()
    {
        $uuid = $this->objectUuid;

        $this->simulateCacheDelete($uuid, true);

        $this->addToMemory($uuid, $this->ocObject);
        $this->inMemory($uuid)->shouldEqual(true);

        $this->delete($uuid);

        $this->inMemory($uuid)->shouldEqual(false);
        $this->getFromMemory($uuid)->shouldBe(null);
    }

    function it_should_first_check_in_memory_if_asked_if_objects_exist_in_cache()
    {
        $uuid = $this->objectUuid;
        $key = $this->generateKey($uuid);

        $this->cache->has($key)->shouldBeCalledTimes(1)->willReturn(false);

        $this->exists($uuid)->shouldBe(false);

        $this->addToMemory($uuid, $this->ocObject);
        $this->inMemory($uuid)->shouldBe(true);

        $this->exists($uuid)->shouldReturn(true);
    }

    function it_should_store_objects_from_getMultiple_in_memory(OcObject $first, OcObject $second)
    {
        $uuidBase = $this->objectUuid;

        $objects = [
            "{$uuidBase}-1" => $first,
            "{$uuidBase}-2" => $second
        ];

        foreach ($objects as $uuid => $object) {
            $this->simulateCacheGet($uuid, $object);
        }

        foreach (array_keys($objects) as $uuid) {
            $this->inMemory($uuid)->shouldBe(false);
            $this->getFromMemory($uuid)->shouldReturn(null);
        }

        $this->getMultiple(array_keys($objects))->shouldReturn($objects);

        foreach ($objects as $uuid => $object) {
            $this->inMemory($uuid)->shouldBe(true);
            $this->getFromMemory($uuid)->shouldReturn($object);
        }
    }

    function it_should_store_objects_from_addMultiple_in_memory(OcObject $first, OcObject $second)
    {
        $uuidBase = $this->objectUuid;

        $objects = [
            "{$uuidBase}-1" => $first,
            "{$uuidBase}-2" => $second
        ];

        $uuids = array_keys($objects);

        foreach ($uuids as $uuid) {
            $this->inMemory($uuid)->shouldBe(false);
            $this->getFromMemory($uuid)->shouldReturn(null);
        }

        foreach ($objects as $uuid => $object) {
            $this->simulateObjectAdded($uuid, $object);
        }

        $this->addMultiple($uuids);

        foreach ($objects as $uuid => $object) {
            $this->inMemory($uuid)->shouldBe(true);
            $this->getFromMemory($uuid)->shouldReturn($object);
        }
    }

    function it_should_remove_all_objects_from_memory_that_was_multi_deleted_from_cache(
        OcObject $first,
        OcObject $second
    ) {
        $uuidBase = $this->objectUuid;

        $objects = [
            "{$uuidBase}-1" => $first,
            "{$uuidBase}-2" => $second
        ];

        foreach ($objects as $uuid => $object) {
            $this->addToMemory($uuid, $object);
            $this->inMemory($uuid)->shouldBe(true);

            $this->simulateCacheDelete($uuid, true);
        }

        $this->deleteMultiple(array_keys($objects));

        foreach (array_keys($objects) as $uuid) {
            $this->inMemory($uuid)->shouldBe(false);
        }
    }

    function it_should_add_a_prefix_while_generating_a_cache_key()
    {
        $uuid = 'uuid';

        $this->generateKey($uuid)->shouldReturn(OcObjectCache::KEY_PREFIX . $uuid);
    }

    private function simulateCacheGet($uuid, $return)
    {
        $this->cache->get($this->generateKey($uuid))->willReturn($return);
    }

    private function simulateCacheDelete($uuid, $return, $cacheExist=true)
    {
        $this->cache->has($this->generateKey($uuid))->shouldBeCalled()->willReturn($cacheExist);
        $this->cache->delete($this->generateKey($uuid))->willReturn($return);
    }

    private function simulateCacheHas($uuid, $return)
    {
        $this->cache->has($this->generateKey($uuid))->willReturn($return);
    }

    private function simulateCachePull($uuid, $return)
    {
        $this->cache->pull($this->generateKey($uuid))->willReturn($return);
    }

    private function simulateCacheSet($uuid, $object, $ttl, $return)
    {
        $this->cache->set($this->generateKey($uuid), $object, $ttl)->willReturn($return);
    }

    private function simulateObjectAdded($uuid, OcObject $object, $ttl = null)
    {
        $this->simulateObjectAdd($uuid, $object, $ttl, true);
    }

    private function simulateObjectAddFailed($uuid, OcObject $object, $ttl = null)
    {
        $this->simulateObjectAdd($uuid, $object, $ttl, false);
    }

    private function simulateObjectAdd($uuid, $object, $ttl = null, $success = true)
    {
        $this->client->getObject($uuid)->willReturn($object);

        $object->getUuid()->willReturn($uuid);

        if ( ! $ttl) {
            $ttl = $this->ttl;
            $object->getCacheTTL()->shouldBeCalled();
        } else {
            $object->getCacheTTL()->willReturn($ttl);
        }

        $this->simulateCacheSet($uuid, $object, $ttl, $success);
    }

    private function simulateObjectNotFound($uuid)
    {
        $this->client->getObject($uuid)->willThrow(ObjectNotFoundException::class);

        $emptyObject = new EmptyOcObject($uuid);

        $this->simulateCacheSet($uuid, $emptyObject, $emptyObject->getCacheTTL(), true);
    }
}
