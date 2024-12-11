<?php declare(strict_types=1);

namespace Everyware\Concepts;

use Everyware\Concepts\Admin\OpenContentClient;
use Everyware\Concepts\Contracts\ConceptProvider;
use Infomaker\Everyware\Support\Date;
use Infomaker\Everyware\Support\Storage\CollectionDB;
use OcObject;

/**
 * Class ConceptDiffProvider
 * @package Everyware\Concepts
 */
class ConceptDiffProvider
{
    /**
     * @var ConceptProvider
     */
    private $ocClient;

    /**
     * @var CollectionDB
     */
    private $diffHandler;

    public function __construct(CollectionDB $diffHandler, OpenContentClient $ocClient)
    {
        $this->ocClient = $ocClient;
        $this->diffHandler = $diffHandler;
    }

    public function getDiff(bool $force = false): array
    {
        if ($force) {
            $this->clearDiffStore();
        }

        $diff = $this->getStoredDiff();

        if ( ! empty($diff)) {
            return $diff;
        }

        $diff = $this->createDiff();

        $this->storeDiff($diff);

        return $diff;
    }

    public function createDiff(): array
    {
        $wpConcepts = $this->getWpConcepts();
        $ocConcepts = $this->getOcConcepts();

        $wpCount = count($wpConcepts);
        $ocCount = count($ocConcepts);
        $itemsHandled = 0;
        $conceptDiff = [];

        // Match for Concepts in Wordpress that might need to be removed or updated
        foreach ($wpConcepts as $uuid => $title) {
            ++$itemsHandled;
            $inOc = array_key_exists($uuid, $ocConcepts);

            // Ignore Concept that can be found in Oc and that are up-to-date
            // Prepare title to match how Wordpress handle titles
            if ($inOc && trim(htmlspecialchars($ocConcepts[$uuid])) === $title) {
                unset($ocConcepts[$uuid]);
                continue;
            }

            $conceptDiff[$uuid] = [
                'uuid' => $uuid,
                'title' => $title,
                'inOc' => $inOc,
                'inWp' => true,
            ];

            if ($inOc) {
                unset($ocConcepts[$uuid]);
            }
        }

        // All Concepts left from Open Content needs to be added
        foreach ($ocConcepts as $uuid => $title) {
            ++$itemsHandled;
            $conceptDiff[$uuid] = [
                'uuid' => $uuid,
                'title' => $title,
                'inOc' => true,
                'inWp' => false,
            ];
        }

        return [
            'source' => [
                'url' => $this->ocClient->getBaseUri(),
                'user' => $this->getConnectedOcUser()
            ],
            'timestamp' => time(),
            'itemsHandled' => $itemsHandled,
            'ocCount' => $ocCount,
            'wpCount' => $wpCount,
            'diff' => $conceptDiff
        ];
    }

    public function getStoredDiff(): array
    {
        return $this->diffHandler->all()->toArray();
    }

    public function storeDiff(array $diff): bool
    {
        return $this->diffHandler->setCollection($diff)->save();
    }

    public function clearDiffStore(): bool
    {
        return $this->diffHandler->reset()->save();
    }

    public function hasDiff(): bool
    {
        return ! empty($this->getStoredDiff());
    }

    public function getWpConcepts(): array
    {
        return $this->allMetaValues('oc_uuid');
    }

    public function getOcConcepts(): array
    {
        $ocConcepts = $this->allOcConcepts(['Headline', 'uuid']);
        $concepts = [];

        foreach ($ocConcepts as $concept) {
            if ( ! empty($concept)) {
                $headline = is_array($concept['Headline']) ? implode(' ', $concept['Headline']) : '';
                $uuid = implode(' ', $concept['uuid']);
                $concepts[$uuid] = $headline;
            }
        }

        return $concepts;
    }

    public function getConnectedOc(): string
    {
        $urlParts = parse_url($this->ocClient->getBaseUri());

        return $urlParts['host'] ?? '';
    }

    public function getConnectedOcUser(): string
    {
        $credentials = $this->ocClient->getCredentials();

        return $credentials['user'] ?? '';
    }

    private function allOcConcepts(array $properties = []): array
    {
        $lastFetch = 0;
        $limit = 1000;
        $concepts = [[]];

        do {
            $response = $this->ocClient->search([
                'contenttype' => 'Concept',
                'start' => $lastFetch,
                'sort.indexfield' => 'created',
                'limit' => $limit,
                'properties' => implode(',', $properties),
            ]);

            if ( ! isset($response['hits'])) {
                break;
            }

            $fetchedConcepts = [];

            if (isset($response['hits'])) {
                $fetchedConcepts = array_map([$this->ocClient, 'extractProperties'], $response['hits']);
            }

            $totalFound = $response['totalHits'] ?? 0;

            $lastFetch += count($fetchedConcepts);
            $concepts[] = $fetchedConcepts;

        } while ($lastFetch < $totalFound);

        return array_merge(...$concepts);
    }

    private function allMetaValues($key = '', $status = 'publish'): array
    {
        if (empty($key)) {
            return [];
        }

        global $wpdb;

        $r = $wpdb->get_results($wpdb->prepare("
        SELECT pm.meta_value, p.post_title FROM {$wpdb->postmeta} pm
        LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
        WHERE pm.meta_key = '%s'
        AND p.post_status = '%s'
        AND p.post_type = '%s'
    ", $key, $status, Concepts::POST_TYPE_ID));

        return array_column($r, 'post_title', 'meta_value');
    }

    public function getLastEvent(): array
    {
        return $this->ocClient->lastEvent();
    }

    public function checkEventHealth(array $event): bool
    {
        // Fetch last updated object
        $fetchedObject = $this->ocClient->lastOcUpdate();

        if (empty($fetchedObject)) {
            return false;
        }

        if ($event['eventType'] !== 'DELETE') {
            return $fetchedObject['uuid'] === $event['uuid'];
        }

        // For delete events we match the dates to make sure that the event occured after the update
        $lastOcUpdate = Date::createFromOcString($fetchedObject['updated']);

        return $lastOcUpdate->lessThan(Date::createFromOcString($event['created']));
    }
}
