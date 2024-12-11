<?php

namespace Everyware\Helpers;

use Everyware\Contracts\OcApiProvider;
use Everyware\Contracts\OcObject as OcObjectInterface;
use Everyware\Exceptions\InvalidListUuid;
use Everyware\Exceptions\ListNotFoundException;
use Everyware\Storage\Traits\MemoryStorage;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use OcObject;

class OcListProvider
{

    use MemoryStorage;

    /**
     * @var array|string[]
     */
    private array $contentRelations;

    /**
     * @var OcApiProvider
     */
    private $ocApi;

    public function __construct(OcApiProvider $ocApi, array $contentRelations = [])
    {
        $this->ocApi = $ocApi;
        $this->contentRelations = $contentRelations + [
                'articles' => 'Articles',
                'concepts' => 'Concepts',
                'images' => 'Images',
                'lists' => 'Lists'
            ];
    }

    /**
     * @param string $uuid
     * @param string $filterQuery
     *
     * @return array
     * @throws InvalidListUuid|ListNotFoundException
     */
    public function getArticles(string $uuid, string $filterQuery = ''): array
    {
        return $this->getRelatedUuids($uuid, $this->contentRelations['articles'], $filterQuery);
    }

    /**
     * @param string $uuid
     *
     * @return OcObjectInterface|null
     * @throws InvalidListUuid
     * @throws ListNotFoundException
     */
    public function getList(string $uuid): ?OcObjectInterface
    {
        if (empty($uuid)) {
            throw new InvalidListUuid('Trying to fetch List with empty UUID');
        }

        try {
            return $this->ocApi->object_cache()->get($uuid);
        } catch (ClientException $e) {

            if ($e->getCode() === 404) {
                throw new ListNotFoundException("Could not find List with UUID: $uuid");
            }

            if ($e->hasResponse()) {
                trigger_error("OpenContent responded with {$e->getResponse()->getStatusCode()}: {$e->getResponse()->getBody()}",
                    E_USER_WARNING);
            } else {
                trigger_error($e->getMessage());
            }
        } catch (GuzzleException $e) {
            trigger_error($e->getMessage());
        }

        return null;
    }

    /**
     * @param string $uuid
     * @param string $filterQuery
     *
     * @return array
     * @throws InvalidListUuid|ListNotFoundException
     */
    public function getLists(string $uuid, string $filterQuery = ''): array
    {
        return $this->getRelatedUuids($uuid, $this->contentRelations['lists'], $filterQuery);
    }

    /**
     * @throws InvalidListUuid|ListNotFoundException
     */
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
        $properties = ['uuid', 'contenttype', "$relation.uuid"];

        if (empty($uuid)) {
            throw new InvalidListUuid('Trying to fetch List with empty UUID');
        }

        $list = $this->ocApi->get_single_object(
            $uuid,
            $properties,
            $this->createRelationFilter($filterQuery, $relation)
        );

        if ( ! $list instanceof OcObject) {
            throw new ListNotFoundException("Could not find List with UUID: $uuid");
        }

        $uuids = array_column($list->get($relation), 'uuid');

        return array_merge(...$uuids);
    }

    private function createRelationFilter(string $filterQuery, string $relation, array $filters = []): string
    {
        if (empty($filters)) {
            $filters = ['start=0', 'limit=100'];
        }

        if ( ! empty($filterQuery)) {
            $filters[] = "q=$filterQuery";
        }

        return $relation . '(' . implode('|', $filters) . ')';
    }
}
