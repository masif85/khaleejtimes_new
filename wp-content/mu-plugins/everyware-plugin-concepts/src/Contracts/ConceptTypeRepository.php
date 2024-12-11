<?php declare(strict_types=1);

namespace Everyware\Concepts\Contracts;

use Everyware\Concepts\ConceptType;
use Everyware\Concepts\Exceptions\ConceptDeleteError;
use Everyware\Concepts\Exceptions\ConceptTypeCreateError;
use Everyware\Concepts\Exceptions\ConceptTypeUpdateError;
use Infomaker\Everyware\Support\GenericPropertyObject;

/**
 * Interface ConceptTypeRepository
 * @package Everyware\Concepts\Contracts
 */
interface ConceptTypeRepository
{

    /**
     * Count number of Concept types.
     *
     * @return int
     */
    public function count(): int;

    /**
     * Create new Concept.
     *
     * @param string $name
     * @param string $description Optional.
     *
     * @return ConceptType The new Type.
     * @throws ConceptTypeCreateError
     */
    public function create($name, $description = ''): ConceptType;

    /**
     * Remove Type by given ID.
     *
     * @param int $id Type ID.
     *
     * @return ConceptType on success.
     * @throws ConceptDeleteError
     */
    public function delete($id): ConceptType;

    /**
     * Retrieves Concept Type data given its ID.
     *
     * @param int $id The Type ID.
     *
     * @return ConceptType|null
     */
    public function findById($id): ?ConceptType;

    /**
     * Update a Concept with new data.
     *
     * @param string $id
     * @param array data Optional.
     *
     * @return ConceptType The updated Type.
     * @throws ConceptTypeUpdateError
     */
    public function update($id, $data = []): ConceptType;
}
