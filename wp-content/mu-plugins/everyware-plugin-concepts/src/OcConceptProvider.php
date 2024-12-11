<?php declare(strict_types=1);

namespace Everyware\Concepts;

use Everyware\Concepts\Contracts\ConceptProvider;
use Everyware\Concepts\Exceptions\InvalidConceptData;
use OcAPI;
use OcObject;

/**
 * Class OcConceptProvider
 * @package Everyware\Concepts
 */
class OcConceptProvider implements ConceptProvider
{
    /**
     * @var OcAPI
     */
    private $ocApi;

    public function __construct(OcAPI $ocApi)
    {
        $this->ocApi = $ocApi;
    }

    /**
     * @param $uuid
     * @param $properties
     *
     * @return OcConcept
     * @throws InvalidConceptData
     */
    public function getSingle($uuid, $properties): ?OcConcept
    {
        $result = $this->ocApi->get_single_object($uuid, $properties, '', false);

        if ( ! $result instanceof OcObject) {
            return null;
        }

        return new OcConcept($result);
    }

    public function search($query, $properties, $params = []): array
    {
        $params['q'] = $query;
        $params['properties'] = $properties;

        return $this->ocApi->search($params, false, false);
    }

    public function getSourceUrl(): string
    {
        return $this->ocApi->getOcBaseUrl();
    }
}
