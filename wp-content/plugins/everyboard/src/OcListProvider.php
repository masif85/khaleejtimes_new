<?php

namespace Everyware\Everyboard;

use Everyware\Contracts\OcObject as OcObjectInterface;
use Everyware\Everyboard\Exceptions\InvalidListUuid;
use Everyware\Everyboard\Exceptions\ListNotFoundException;
use Everyware\Storage\Traits\MemoryStorage;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use OcObject;

class OcListProvider
{

    use MemoryStorage;

    /**
     * @var OcApiAdapter
     */
    private $ocApi;

    public function __construct(OcApiAdapter $ocApi)
    {
        $this->ocApi = $ocApi;
    }

    /**
     * @param string $uuid
     *
     * @return OcObjectInterface|null
     * @throws InvalidListUuid
     */
    public function getList(string $uuid): ?OcObjectInterface
    {
        if (empty($uuid)) {
            throw new InvalidListUuid('Trying to fetch List with empty UUID');
        }

        try {
            return $this->ocApi->object_cache()->get($uuid);
        } catch (ClientException $e) {
            if ($e->getCode() !== 404) {
                if ($e->hasResponse()) {
                    trigger_error("OpenContent responded with {$e->getResponse()->getStatusCode()}: {$e->getResponse()->getBody()}",
                        E_USER_WARNING);
                } else {
                    trigger_error($e->getMessage());
                }
            }
        }  catch (GuzzleException $e) {
            trigger_error($e->getMessage());
        }

        return null;
    }

    public function getListProperties(string $uuid, array $properties = []): array
    {
        $list = $this->getList($uuid);

        if ( ! $list instanceof OcObjectInterface) {
            return [];
        }

        if (empty($properties)) {
            return $list->getProperties();
        }

        return array_filter($list->getProperties(), static function ($key) use ($properties) {
            return in_array($key, $properties, true);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * @param string $uuid
     * @param string $relation
     * @param string $filterQuery
     *
     * @return array
     * @throws InvalidListUuid|ListNotFoundException
     */
    public function getRelatedUuids(string $uuid, string $relation, string $filterQuery = ''): array
    {
        $properties = ['uuid', 'contenttype', "{$relation}.uuid"];

        if (empty($uuid)) {
            throw new InvalidListUuid('Trying to fetch List with empty UUID');
        }

        $list = $this->ocApi->get_single_object(
            $uuid,
            $properties,
            $this->createRelationFilter($filterQuery, $relation)
        );

        if ( ! $list instanceof OcObject) {
            throw new ListNotFoundException(__("Could not find List with UUID: {$uuid}", 'everyboard'));
        }

        $uuids = array_column($list->get($relation), 'uuid');

        return array_merge(...$uuids);
    }

    private function createRelationFilter(string $filterQuery, string $relation): string
    {
        $filters = ['start=0', 'limit=100'];

        if ( ! empty($filterQuery)) {
            $filters[] = "q={$filterQuery}";
        }

        return $relation . '(' . implode('|', $filters) . ')';
    }
}
