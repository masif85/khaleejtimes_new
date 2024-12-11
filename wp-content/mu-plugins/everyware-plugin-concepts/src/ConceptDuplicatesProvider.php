<?php declare(strict_types=1);

namespace Everyware\Concepts;

use Infomaker\Everyware\Support\Storage\CollectionDB;

/**
 * Class ConceptDuplicatesProvider
 * @package Everyware\Concepts
 */
class ConceptDuplicatesProvider
{
    /**
     * @var CollectionDB
     */
    private $storage;

    public function __construct(CollectionDB $storage)
    {
        $this->storage = $storage;
    }

    public function getDuplicates($force = false): array
    {
        if ($force) {
            $this->storage->reset()->save();
        }

        $duplicates = $this->storage->all()->toArray();

        if ( ! empty($duplicates)) {
            return $duplicates;
        }

        $duplicates = $this->extractDuplicates($this->allConceptsUuids());

        $this->store($duplicates);

        return $duplicates;
    }

    private function extractDuplicates(array $concepts): array
    {
        $withoutDuplicates = array_unique($concepts);

        //  If the amount is the same after removing duplicates we dont look any further
        if (count($withoutDuplicates) === count($concepts)) {
            return [];
        }

        // Extract the uuids that have multiple posts
        $duplicatedUuids = array_values(array_unique(array_diff_assoc($concepts, $withoutDuplicates)));

        $duplicates = [];

        // Fetch all post ids that have the same uuids and create ConceptPosts outof them
        foreach ($duplicatedUuids as $uuid) {
            $duplicates[$uuid] = array_map([ConceptPost::class, 'createFromId'], array_keys($concepts, $uuid));
        }

        return $duplicates;
    }

    private function allConceptsUuids(): array
    {
        global $wpdb;
        $key = 'oc_uuid';
        $status = 'publish';

        $r = $wpdb->get_results($wpdb->prepare("
        SELECT pm.meta_value, p.ID FROM {$wpdb->postmeta} pm
        LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
        WHERE pm.meta_key = '%s'
        AND p.post_status = '%s'
        AND p.post_type = '%s'
    ", $key, $status, Concepts::POST_TYPE_ID));

        return array_column($r, 'meta_value', 'ID');
    }

    public function store(array $duplicates): bool
    {
        return $this->storage->setCollection($duplicates)->save();
    }
}
