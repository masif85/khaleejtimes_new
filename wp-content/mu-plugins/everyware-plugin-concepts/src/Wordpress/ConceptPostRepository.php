<?php declare(strict_types=1);

namespace Everyware\Concepts\Wordpress;

use Everyware\Concepts\ConceptPost;
use Everyware\Concepts\Exceptions\ConceptCreateError;
use Everyware\Concepts\Exceptions\ConceptDeleteError;
use Everyware\Concepts\Exceptions\ConceptMetaAddError;
use Everyware\Concepts\Exceptions\ConceptUpdateError;
use Everyware\Concepts\Wordpress\Contracts\WpConceptPostRepository;
use Everyware\Concepts\Wordpress\Contracts\WpPostRepository;
use WP_Post;

/**
 * Class ConceptPostRepository
 * @package Everyware\Concepts\Wordpress
 */
class ConceptPostRepository implements WpConceptPostRepository
{
    public const POST_TYPE_ID = 'concept';

    /**
     * @var WpPostRepository
     */
    private $repository;

    public function __construct(WpPostRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param int   $postId
     * @param array $postMeta
     *
     * @return void
     * @throws ConceptMetaAddError
     */
    public function addPostMeta(int $postId, array $postMeta): void
    {
        $failed = [];

        foreach ($postMeta as $key => $value) {
            if ($this->repository->addPostMeta($postId, $key, $value) === false) {
                $failed[] = $key;
            }
        }

        if ( ! empty($failed)) {
            $failedKeys = implode(', ', $failed);
            throw new ConceptMetaAddError("Failed to add meta keys: ({$failedKeys}) to Concept with ID: {$postId}");
        }
    }

    /**
     * Count number of published Concepts.
     *
     * @return int Number of published Concepts found.
     */
    public function countPosts(): int
    {
        $postsCountObject = $this->repository->countPosts(static::POST_TYPE_ID);

        if (isset($postsCountObject->publish)) {
            return (int)$postsCountObject->publish;
        }

        return 0;
    }

    /**
     * Create new Concept post.
     *
     * @param array $postData
     *
     * @return int Id of created Concept
     * @throws ConceptCreateError
     */
    public function insertPost(array $postData): int
    {
        $postId = $this->repository->insertPost(array_replace([
            'post_type' => static::POST_TYPE_ID,
            'post_status' => 'publish'
        ], $postData));

        if ($this->repository->isError($postId)) {
            throw new ConceptCreateError($postId->get_error_message());
        }

        return $postId;
    }

    /**
     * Remove or Trash a Concept.
     *
     * @param int  $postId      ID of Concept to be deleted
     * @param bool $keepInTrash Optional. Whether to trash instead of delete. Default false.
     *
     * @return ConceptPost data on success, null on failure.
     * @throws ConceptDeleteError
     */
    public function deletePost(int $postId, bool $keepInTrash = false): ConceptPost
    {
        $removedConcept = static::convertPost($this->repository->deletePost($postId, $keepInTrash ? false : true));

        if ( ! $removedConcept instanceof ConceptPost) {
            throw new ConceptDeleteError("Failed to delete concept with id: {$postId} from database.");
        }

        return $removedConcept;
    }

    /**
     * Retrieves a page given its path.
     *
     * @param string $pagePath Concept path.
     *
     * @return ConceptPost|null The Concept if found or null.
     */
    public function getPageByPath(string $pagePath): ?ConceptPost
    {
        $post = static::convertPost($this->repository->getPageByPath($pagePath, 'OBJECT', static::POST_TYPE_ID));

        return $post instanceof ConceptPost ? $post : null;
    }

    /**
     * Retrieves Concept data given its ID.
     *
     * @param int $postId
     *
     * @return ConceptPost|null The Concept if found or null.
     */
    public function getPost(int $postId): ?ConceptPost
    {
        $post = static::convertPost($this->repository->getPost($postId));

        return $post instanceof ConceptPost ? $post : null;
    }

    /**
     * Fetch concepts with given parent
     *
     * @param int $postId
     *
     * @return array
     */
    public function getPostsByParent(int $postId): array
    {
        return $this->makeWpQuery(['post_parent' => $postId]);
    }

    /**
     * Use Wordpress query to fetch concept posts
     *
     * @param array $args
     *
     * @return array
     */
    public function query(array $args = []): array
    {
        return $this->makeWpQuery($args);
    }

    /**
     * Fetch the first concept found based on the arguments of your search
     *
     * @param array $args
     *
     * @return ConceptPost|null
     */
    public function queryFirst(array $args = []): ?ConceptPost
    {
        $args['posts_per_page'] = 1;

        $items = $this->makeWpQuery($args);

        if (empty($items)) {
            return null;
        }

        $item = array_shift($items);

        return $item instanceof ConceptPost ? $item : null;
    }

    /**
     * Update a Concept with new data.
     *
     * @param int   $postId Id of concept post
     * @param array $postData
     *
     * @return int Id of updated Concept.
     * @throws ConceptUpdateError
     */
    public function updatePost(int $postId, array $postData): int
    {
        $postData['ID'] = $postId;

        $result = $this->repository->updatePost($postData);

        if ($this->repository->isError($result)) {
            throw new ConceptUpdateError($result->get_error_message());
        }

        return $postId;
    }

    private function makeWpQuery(array $args = []): array
    {
        $query = array_replace([
            'post_type' => static::POST_TYPE_ID,
            'posts_per_page' => -1
        ], $args);

        return array_map([static::class, 'convertPost'], $this->repository->query($query));
    }

    /**
     * Converts WP_Post to Concept
     * else do nothing.
     *
     * @param $item
     *
     * @return ConceptPost|mixed
     */
    public static function convertPost($item)
    {
        if ($item instanceof WP_Post) {
            return new ConceptPost($item);
        }

        return $item;
    }
}
