<?php declare(strict_types=1);

namespace Everyware\Concepts;

use Everyware\Concepts\Contracts\ConceptTypeRepository;
use Everyware\Concepts\Exceptions\ConceptDeleteError;
use Everyware\Concepts\Exceptions\ConceptTypeCountError;
use Everyware\Concepts\Exceptions\ConceptTypeCreateError;
use Everyware\Concepts\Exceptions\ConceptTypeUpdateError;
use Everyware\Concepts\Wordpress\Contracts\WpTermRepository;
use Infomaker\Everyware\Support\NewRelicLog;
use WP_Term;

/**
 * Class ConceptTypes
 * @package Everyware\Concepts
 */
class ConceptTypes implements ConceptTypeRepository
{
    public const TAXONOMY_ID = 'concept-type';

    /**
     * @var WpTermRepository
     */
    private $repository;

    public function __construct(WpTermRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Count number of Concept types.
     *
     * @return int
     * @throws ConceptTypeCountError
     */
    public function count(): int
    {
        $result = $this->repository->countTerms(self::TAXONOMY_ID);

        if ($this->repository->isError($result)) {
            throw new ConceptTypeCountError($result->get_error_message());
        }

        return $result;
    }

    /**
     * Create new Concept.
     *
     * @param string $name
     * @param string $description Optional.
     *
     * @return ConceptType The new Type.
     * @throws ConceptTypeCreateError
     */
    public function create($name, $description = ''): ConceptType
    {
        $result = $this->repository->insertTerm($name, self::TAXONOMY_ID, [
            'description' => $description
        ]);

        if ($this->repository->isError($result)) {
            throw new ConceptTypeCreateError($result->get_error_message());
        }

        if ( ! isset($result['term_id'])) {
            throw new ConceptTypeCreateError("Failed to create Concept type with name: {$name}.");
        }

        return $this->findById($result['term_id']);
    }

    /**
     * Remove Type by given ID.
     *
     * @param int $id Type ID.
     *
     * @return ConceptType data on success, null on failure.
     * @throws ConceptDeleteError
     */
    public function delete($id): ConceptType
    {
        $type = $this->findById($id);

        if ( ! $type instanceof ConceptType) {
            throw new ConceptDeleteError("Could not find concept type with id: {$id} to delete.");
        }

        $result = $this->repository->deleteTerm($type->id, self::TAXONOMY_ID);

        if ($this->repository->isError($result)) {
            throw new ConceptDeleteError($result->get_error_message());
        }

        return $type;
    }

    /**
     * Retrieves Concept Type data given its ID.
     *
     * @param int $id The Type ID.
     *
     * @return ConceptType|null
     */
    public function findById($id): ?ConceptType
    {
        return static::convertTerm($this->repository->getTerm($id, self::TAXONOMY_ID));
    }

    /**
     * Update a Concept with new data.
     *
     * @param string $id
     * @param array data Optional.
     *
     * @return ConceptType The updated Type.
     * @throws ConceptTypeUpdateError
     */
    public function update($id, $data = []): ConceptType
    {
        $type = $this->findById($id);

        if ( ! $type instanceof ConceptType) {
            throw new ConceptTypeUpdateError("Could not find concept type with id: {$id} to update.");
        }

        if (isset($data['description'])) {
            $type->description = $data['description'];
        }

        if (isset($data['parent'])) {
            $type->parent = $data['parent'];
        }

        $result = $this->repository->updateTerm($type->id, self::TAXONOMY_ID, [
            'description' => $type->description,
            'parent' => $type->parent,
        ]);

        if ($this->repository->isError($result)) {
            throw new ConceptTypeUpdateError($result->get_error_message());
        }

        if ( ! isset($result['term_id'])) {
            throw new ConceptTypeUpdateError("Failed to update Concept type with id: {$id}.");
        }

        return $type;
    }

    /**
     * Converts WP_Term to ConceptType
     * else do nothing.
     *
     * @param $item
     *
     * @return ConceptPost|mixed
     */
    public static function convertTerm($item)
    {
        if ($item instanceof WP_Term) {
            return new ConceptType($item);
        }

        return $item;
    }
}
