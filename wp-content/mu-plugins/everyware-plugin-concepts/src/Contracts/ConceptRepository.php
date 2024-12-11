<?php declare(strict_types=1);

namespace Everyware\Concepts\Contracts;

use Everyware\Concepts\ConceptPost;
use Everyware\Concepts\Exceptions\ConceptDeleteError;
use Everyware\Concepts\Exceptions\ConceptCreateError;
use Everyware\Concepts\Exceptions\ConceptMetaAddError;
use Everyware\Concepts\Exceptions\ConceptUpdateError;
use Everyware\Concepts\OcConcept;

/**
 * Interface ConceptRepository
 * @package Everyware\Concepts\Contracts
 */
interface ConceptRepository
{
    /**
     * Add Meta data to a given Concept
     * @param ConceptPost $conceptPost
     * @param array       $postMeta
     *
     * @throws ConceptMetaAddError
     */
    public function addMeta(ConceptPost $conceptPost, array $postMeta): void;

    /**
     * Retrieve all Concepts
     *
     * @return array
     */
    public function all(): array;

    /**
     * Count number of Concepts with the given status.
     *
     * @return int Number of published Concepts.
     */
    public function count(): int;

    /**
     * Create new Concept.
     *
     * @param OcConcept $concept An object with data that make up a Concept to insert.
     *
     * @return ConceptPost The new Concept.
     * @throws ConceptCreateError
     */
    public function create(OcConcept $concept): ConceptPost;

    /**
     * Remove or Trash a Concept.
     *
     * @param ConceptPost $concept     Concept to be deleted
     * @param bool        $keepInTrash Optional. Whether to trash instead of delete. Default false.
     *
     * @return ConceptPost data on success, null on failure.
     * @throws ConceptDeleteError
     */
    public function delete(ConceptPost $concept, $keepInTrash = false): ConceptPost;

    /**
     * Remove or Trash a Concept.
     *
     * @param int  $id          ID of Concept to be deleted
     * @param bool $keepInTrash Optional. Whether to trash instead of delete. Default false.
     *
     * @return ConceptPost data on success, null on failure.
     * @throws ConceptDeleteError
     */
    public function deleteById($id, $keepInTrash = false): ConceptPost;

    /**
     * Remove or Trash a Concept.
     *
     * @param OcConcept $concept     Concept to be deleted
     * @param bool      $keepInTrash Optional. Whether to trash instead of delete. Default false.
     *
     * @return ConceptPost data on success, null on failure.
     * @throws ConceptDeleteError
     */
    public function deleteByOcConcept(OcConcept $concept, $keepInTrash = false): ConceptPost;

    /**
     * Retrieves Concept data given its ID.
     *
     * @param int $id
     *
     * @return ConceptPost|null The Concept if found or null.
     */
    public function findById($id): ?ConceptPost;

    /**
     * Get the concept based on the posts name
     *
     * @param $name
     *
     * @return ConceptPost|null
     */
    public function findByName($name): ?ConceptPost;

    /**
     * Retrieves a page given its path.
     *
     * @param string $pagePath Concept path.
     *
     * @return ConceptPost|null The Concept if found or null.
     */
    public function findByPath($pagePath): ?ConceptPost;

    /**
     * Get the Concept based on the uuid stored as metadata on the post
     *
     * @param $uuid
     *
     * @return ConceptPost|null
     */
    public function findByUuid($uuid): ?ConceptPost;

    /**
     * Retrieve all concepts using a parents post id
     *
     * @param int $id
     *
     * @return array
     */
    public function findByParentId($id): array;

    /**
     * Retrieve all concepts based on the uuid stored as metadata on the parent post
     *
     * @param int $uuid
     *
     * @return array
     */
    public function findByParentUuid($uuid): array;

    /**
     * Get post id of concept based on the uuid stored as metadata on the post
     *
     * @param $uuid
     *
     * @return int
     */
    public function findIdByUuid($uuid): int;

    /**
     * Get uuid of concept based on the posts id
     *
     * @param int $id
     *
     * @return string
     */
    public function findUuidById($id): string;

    /**
     * Get first Concept found by comparing meta data
     *
     * @param string $key      Custom field key.
     * @param string $value    Custom field value.
     * @param string $operator Operator to test. Possible values are '=', '!=', '>', '>=', '<', '<='.
     *                         Default value is '='.
     *
     * @return ConceptPost|null
     */
    public function firstWhereMeta($key, $value, $operator = '='): ?ConceptPost;

    /**
     * Update a Concept with new data.
     *
     * @param ConceptPost $concept An object with data that make up the Concept to update.
     *
     * @return ConceptPost The updated Concept.
     * @throws ConceptUpdateError
     */
    public function update(ConceptPost $concept): ConceptPost;

    /**
     * Update a Concept with new data.
     *
     * @param OcConcept $concept An object with data that make up the Concept to update.
     *
     * @return ConceptPost The updated Concept.
     * @throws ConceptUpdateError
     */
    public function updateByOcConcept(OcConcept $concept): ConceptPost;

    /**
     * Get Concepts based on their meta data
     * @param string $key      Custom field key.
     * @param string $value    Custom field value.
     * @param string $operator Operator to test. Possible values are '=', '!=', '>', '>=', '<', '<='.
     *                         Default value is '='.
     *
     * @return array
     */
    public function whereMeta($key, $value, $operator = '='): array;
}
