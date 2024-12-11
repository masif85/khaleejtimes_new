<?php declare(strict_types=1);

namespace Everyware\Contracts;

use Everyware\Storage\OcObjectCache;

interface OcApiProvider
{
    public function get_single_object($uuid, array $prop_arr = [], $filter = '', $use_cache = true);

    public function object_cache(): OcObjectCache;

    public function search(array $params = [], $use_cache = true, $create_article = true, $cache_ttl = null): array;

    public function get_default_properties(): array;

    public function get_oc_sort_options(): ?object;
}
