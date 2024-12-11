<?php declare(strict_types=1);

namespace Everyware\Concepts\Contracts;

use Everyware\Concepts\Exceptions\InvalidConceptData;
use Everyware\Concepts\OcConcept;
use Everyware\Concepts\OcConceptProvider;

/**
 * Interface ConceptProvider
 * @package Everyware\Concepts\Contracts
 */
interface ConceptProvider
{
    /**
     * @param $uuid
     * @param $properties
     *
     * @return OcConcept
     * @throws InvalidConceptData
     */
    public function getSingle($uuid, $properties): ?OcConcept;

    /**
     * @param $query
     * @param $properties
     * @param $params
     *
     * @return array
     */
    public function search($query, $properties, $params): array;

    /**
     * @return string
     */
    public function getSourceUrl(): string;
}
