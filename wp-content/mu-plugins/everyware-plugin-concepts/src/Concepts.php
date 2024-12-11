<?php declare(strict_types=1);

namespace Everyware\Concepts;

use Everyware\Concepts\Contracts\ConceptRepository;
use Everyware\Concepts\Exceptions\ConceptCreateError;
use Everyware\Concepts\Exceptions\ConceptDeleteError;
use Everyware\Concepts\Exceptions\ConceptMetaAddError;
use Everyware\Concepts\Exceptions\ConceptUpdateError;
use Everyware\Concepts\Wordpress\ConceptPostRepository;
use Everyware\Concepts\Wordpress\Contracts\WpConceptPostRepository;
use Everyware\Concepts\Wordpress\PostRepository;
use WP_Query;

/**
 * Class Concepts
 * @package Everyware\Concepts
 */
class Concepts implements ConceptRepository
{
    public const POST_TYPE_ID = 'concept';

    public const POST_UUID_FIELD = 'oc_uuid';

    /**
     * @var WpConceptPostRepository
     */
    private $repository;

    public function __construct(WpConceptPostRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param ConceptPost $conceptPost
     * @param array       $postMeta
     *
     * @throws ConceptMetaAddError
     */
    public function addMeta(ConceptPost $conceptPost, array $postMeta): void
    {
        $this->repository->addPostMeta($conceptPost->id, $postMeta);
    }

    /**
     * Retrieve all Concepts
     *
     * @return array
     */
    public function all(): array
    {
        return $this->repository->query();
    }

    /**
     * Count number of Concepts with the given status.
     *
     * @return int Number of published Concepts.
     */
    public function count(): int
    {
        return $this->repository->countPosts();
    }

    /**
     * Create new Concept.
     *
     * @param OcConcept $concept An object with data that make up a Concept to insert.
     *
     * @return ConceptPost The new Concept.
     * @throws ConceptCreateError
     * @throws ConceptMetaAddError
     */
    public function create(OcConcept $concept): ConceptPost
    {
        $postId = $this->repository->insertPost([
            'post_title' => $concept->getPostTitle(),
            'post_parent' => $concept->hasParent() ? $this->findIdByUuid($concept->getParentUuid()) : 0
        ]);

        $this->repository->addPostMeta($postId, $concept->getPostMeta());

        return $this->findById($postId);
    }

    /**
     * Remove or Trash a Concept.
     *
     * @param ConceptPost $concept     Concept to be deleted
     * @param bool        $keepInTrash Optional. Whether to trash instead of delete. Default false.
     *
     * @return ConceptPost data on success, null on failure.
     * @throws ConceptDeleteError
     */
    public function delete(ConceptPost $concept, $keepInTrash = false): ConceptPost
    {
        return $this->deleteById($concept->id, $keepInTrash);
    }

    /**
     * Remove or Trash a Concept.
     *
     * @param int  $id          ID of Concept to be deleted
     * @param bool $keepInTrash Optional. Whether to trash instead of delete. Default false.
     *
     * @return ConceptPost data on success, null on failure.
     * @throws ConceptDeleteError
     */
    public function deleteById($id, $keepInTrash = false): ConceptPost
    {
        return $this->repository->deletePost($id, $keepInTrash);
    }

    /**
     * Remove or Trash a Concept.
     *
     * @param OcConcept $concept     Concept to be deleted
     * @param bool      $keepInTrash Optional. Whether to trash instead of delete. Default false.
     *
     * @return ConceptPost data on success, null on failure.
     * @throws ConceptDeleteError
     */
    public function deleteByOcConcept(OcConcept $concept, $keepInTrash = false): ConceptPost
    {
        $conceptPost = $this->findByUuid($concept->uuid);

        if ( ! $conceptPost instanceof ConceptPost) {
            throw new ConceptDeleteError("Could not find Concept with uuid {$concept->uuid} for update");
        }

        return $this->delete($conceptPost, $keepInTrash);
    }

    /**
     * Retrieves Concept data given its ID.
     *
     * @param int $id
     *
     * @return ConceptPost|null The Concept if found or null.
     */
    public function findById($id): ?ConceptPost
    {
        if ((int)$id === 0) {
            return null;
        }

        return $this->repository->getPost($id);
    }

    /**
     * Get the concept based on the posts name
     *
     * @param $name
     *
     * @return ConceptPost|null
     */
    public function findByName($name): ?ConceptPost
    {
        return $this->repository->queryFirst(['title' => $name]);
    }

    /**
     * Retrieves a page given its path.
     *
     * @param string $pagePath Concept path.
     *
     * @return ConceptPost|null The Concept if found or null.
     */
    public function findByPath($pagePath): ?ConceptPost
    {
        return $this->repository->getPageByPath($pagePath);
    }

    /**
     * Get the Concept based on the uuid stored as metadata on the post
     *
     * @param $uuid
     *
     * @return ConceptPost|null
     */
    public function findByUuid($uuid): ?ConceptPost
    {
        $concept = $this->firstWhereMeta(self::POST_UUID_FIELD, $uuid);

        return $concept instanceof ConceptPost ? $concept : null;
    }

    /**
     * Retrieve all concepts using a parents post id
     *
     * @param int $id
     *
     * @return array
     */
    public function findByParentId($id): array
    {
        return $this->repository->getPostsByParent($id);
    }

    /**
     * Retrieve all concepts based on the uuid stored as metadata on the parent post
     *
     * @param int $uuid
     *
     * @return array
     */
    public function findByParentUuid($uuid): array
    {
        return $this->findByParentId($this->findIdByUuid($uuid));
    }

    /**
     * Get post id of concept based on the uuid stored as metadata on the post
     *
     * @param $uuid
     *
     * @return int
     */
    public function findIdByUuid($uuid): int
    {
        $concept = $this->findByUuid($uuid);

        return $concept instanceof ConceptPost ? $concept->id : 0;
    }

    /**
     * Get uuid of concept based on the posts id
     *
     * @param int $id
     *
     * @return string
     */
    public function findUuidById($id): string
    {
        $concept = $this->findById($id);

        return $concept instanceof ConceptPost ? (string)$concept->getMeta(self::POST_UUID_FIELD, '') : '';
    }

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
    public function firstWhereMeta($key, $value, $operator = '='): ?ConceptPost
    {
        return $this->getFirstWhereMeta([
            'key' => $key,
            'value' => $value,
            'compare' => $operator
        ]);
    }

    /**
     * Update a Concept with new data.
     *
     * @param ConceptPost $concept An object with data that make up the Concept to update.
     *
     * @return ConceptPost The updated Concept.
     * @throws ConceptUpdateError
     */
    public function update(ConceptPost $concept): ConceptPost
    {
        $postId = $this->repository->updatePost($concept->id, [
            'post_title' => $concept->post_title,
            'post_parent' => $concept->post_parent
        ]);

        return $this->findById($postId);
    }

    /**
     * Update a Concept with new data.
     *
     * @param OcConcept $concept An object with data that make up the Concept to update.
     *
     * @return ConceptPost The updated Concept.
     * @throws ConceptUpdateError
     */
    public function updateByOcConcept(OcConcept $concept): ConceptPost
    {
        $conceptPost = $this->findByUuid($concept->uuid);

        if ( ! $conceptPost instanceof ConceptPost) {
            throw new ConceptUpdateError("Could not find Concept with uuid {$concept->uuid} for update");
        }

        $conceptPost->set('post_title', $concept->getPostTitle());
        $conceptPost->set('post_parent', $concept->hasParent() ? $this->findIdByUuid($concept->getParentUuid()) : 0);

        return $this->update($conceptPost);
    }

    /**
     * @param string $key      Custom field key.
     * @param string $value    Custom field value.
     * @param string $operator Operator to test. Possible values are '=', '!=', '>', '>=', '<', '<='.
     *                         Default value is '='.
     *
     * @return array
     */
    public function whereMeta($key, $value, $operator = '='): array
    {
        $metaQuery = [
            'key' => $key,
            'value' => $value,
            'compare' => $operator
        ];

        return $this->repository->query([
            'meta_query' => [$metaQuery]
        ]);
    }

    public static function init()
    {
        return new static(new ConceptPostRepository(new PostRepository(new WP_Query())));
    }

    /**
     * @param array $metaQuery
     *
     * @return ConceptPost|null
     */
    private function getFirstWhereMeta(array $metaQuery = []): ?ConceptPost
    {
        return $this->repository->queryFirst([
            'meta_query' => [$metaQuery]
        ]);
    }
}
