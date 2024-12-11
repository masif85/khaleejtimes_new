<?php declare(strict_types=1);

namespace Everyware\Concepts\Wordpress\Contracts;

use Everyware\Concepts\Exceptions\ConceptCreateError;
use Everyware\Concepts\Exceptions\ConceptDeleteError;
use Everyware\Concepts\Exceptions\ConceptMetaAddError;
use Everyware\Concepts\Exceptions\ConceptUpdateError;
use Everyware\Concepts\ConceptPost;

/**
 * Interface WpConceptPostRepository
 * @package Everyware\Concepts\Wordpress\Contracts
 */
interface WpConceptPostRepository
{
    /**
     * @param int    $postId
     * @param array  $postMeta
     *
     * @return void
     * @throws ConceptMetaAddError
     */
    public function addPostMeta(int $postId, array $postMeta): void;

    /**
     * Count number of published Concepts.
     *
     * @return int Number of published Concepts found.
     */
    public function countPosts(): int;

    /**
     * Create new Concept post.
     *
     * @param array $postData
     *
     * @return int Id of created Concept
     * @throws ConceptCreateError
     */
    public function insertPost(array $postData): int;

    /**
     * Remove or Trash a Concept.
     *
     * @param int  $postId          ID of Concept to be deleted
     * @param bool $keepInTrash Optional. Whether to trash instead of delete. Default false.
     *
     * @return ConceptPost data on success, null on failure.
     * @throws ConceptDeleteError
     */
    public function deletePost(int $postId, bool $keepInTrash = false): ConceptPost;

    /**
     * Retrieves a page given its path.
     *
     * @param string $pagePath Concept path.
     *
     * @return ConceptPost|null The Concept if found or null.
     */
    public function getPageByPath(string $pagePath): ?ConceptPost;


    /**
     * Retrieves Concept data given its ID.
     *
     * @param int $postId
     *
     * @return ConceptPost|null The Concept if found or null.
     */
    public function getPost(int $postId): ?ConceptPost;

    /**
     * Fetch concepts with given parent
     *
     * @param int $postId
     *
     * @return array
     */
    public function getPostsByParent(int $postId):array;

    /**
     * Use Wordpress query to fetch concept posts
     *
     * @param array $args
     *
     * @return array
     */
    public function query(array $args = []): array;

    /**
     * Fetch the first concept found based on the arguments of your search
     *
     * @param array $args
     *
     * @return ConceptPost|null
     */
    public function queryFirst(array $args = []): ?ConceptPost;

    /**
     * Update a Concept with new data.
     *
     * @param int $postId Id of concept post
     * @param array $postData post data to be updated
     *
     * @return int Id of updated Concept.
     * @throws ConceptUpdateError
     */
    public function updatePost(int $postId, array $postData): int;
}
