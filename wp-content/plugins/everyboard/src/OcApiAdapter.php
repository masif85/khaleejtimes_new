<?php declare(strict_types=1);

namespace Everyware\Everyboard;

use Everyware\Storage\OcObjectCache;
use OcAPI;

/**
 * Class OcApiAdapter
 * @package Everyware\Everyboard
 */
class OcApiAdapter
{
    /**
     * @var OcAPI
     */
    private $ocApi;

    public function __construct(OcAPI $ocApi)
    {
        $this->ocApi = $ocApi;
    }

    public function get_single_object($uuid, array $prop_arr = [], $filter = '', $use_cache = true)
    {
        return $this->ocApi->get_single_object($uuid, $prop_arr, $filter, $use_cache);
    }

    public function object_cache(): OcObjectCache
    {
        return $this->ocApi->object_cache();
    }

    public function search(array $params = [], $use_cache = true, $create_article = true, $cache_ttl = null): array
    {
        return $this->ocApi->search($params, $use_cache, $create_article, $cache_ttl);
    }

    public function get_default_properties(): array
    {
        return $this->ocApi->get_default_properties();
    }

    public function get_oc_sort_options(): object
    {
        return $this->ocApi->get_oc_sort_options();
    }

    public static function create(): OcApiAdapter
    {
        return new static(new OcAPI());
    }
}
