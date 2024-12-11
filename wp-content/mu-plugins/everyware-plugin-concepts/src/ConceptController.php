<?php declare(strict_types=1);

namespace Everyware\Concepts;

use Everyware\Concepts\Contracts\ConceptProvider;
use Everyware\Concepts\Contracts\ConceptRepository;
use Everyware\Concepts\Events\ConceptApiEvent;
use Everyware\Concepts\Exceptions\ConceptDeleteError;
use Everyware\Concepts\Exceptions\ConceptCreateError;
use Everyware\Concepts\Exceptions\ConceptUpdateError;
use Everyware\Concepts\Exceptions\InvalidConceptData;
use Everyware\Concepts\Wordpress\Contracts\WpAction;
use Exception;
use Infomaker\Everyware\Support\NewRelicLog;

/**
 * Class AjaxController
 * @package Everyware\Concepts
 */
class ConceptController
{
    /**
     * @var ConceptRepository
     */
    private $repository;

    /**
     * @var ConceptProvider
     */
    private $provider;

    /**
     * @var array
     */
    private $responseCodes = [];

    /**
     * @var WpAction
     */
    private $wpAction;

    /**
     * @var bool
     */
    private $logErrors;

    public function __construct(
        ConceptRepository $repository,
        ConceptProvider $provider,
        WpAction $wpAction,
        bool $logErrors = true
    ) {
        $this->repository = $repository;
        $this->provider = $provider;
        $this->wpAction = $wpAction;
        $this->logErrors = $logErrors;
    }

    public function create($uuid): ConceptApiResponse
    {
        if ($this->conceptExists($uuid)) {
            return ConceptApiResponse::alreadyExists();
        }

        $concept = null;

        try {
            $concept = $this->fetchConcept($uuid);
        } catch (InvalidConceptData $e) {
            $this->log('InvalidConceptData in concept CREATE: ', $e);

            return ConceptApiResponse::internalError($e);
        }

        if ( ! $concept instanceof OcConcept) {
            return ConceptApiResponse::notFoundInOC();
        }

        return $this->createPost($uuid, $concept);
    }

    /**
     * @param $uuid
     *
     * @return ConceptApiResponse
     */
    public function show($uuid): ConceptApiResponse
    {
        $post = $this->repository->findByUuid($uuid);

        if ( ! $post instanceof ConceptPost) {
            return ConceptApiResponse::error([], 404)
                ->addResponseCode(ConceptApiResponse::NOT_FOUND_IN_WP);
        }

        return ConceptApiResponse::success($this->extractPostData($post, $uuid));
    }

    /**
     * @param $uuid
     *
     * @return ConceptApiResponse
     */
    public function update($uuid): ConceptApiResponse
    {
        $post = $this->repository->findByUuid($uuid);

        if ( ! $post instanceof ConceptPost) {
            return ConceptApiResponse::notFoundInDb();
        }

        $concept = null;

        try {
            $concept = $this->fetchConcept($uuid);
        } catch (InvalidConceptData $e) {
            $this->log('InvalidConceptData in concept UPDATE: ', $e);

            return ConceptApiResponse::internalError($e);
        }

        if ( ! $concept instanceof OcConcept) {
            return ConceptApiResponse::notFoundInOC();
        }

        return $this->updatePost($uuid, $post, $concept);
    }

    public function remove($uuid): ConceptApiResponse
    {
        $post = $this->repository->findByUuid($uuid);

        if ( ! $post instanceof ConceptPost) {
            return ConceptApiResponse::notFoundInDb();
        }

        return $this->removePost($uuid, $post);
    }

    public function synchronize($uuid): ConceptApiResponse
    {
        $post = $this->repository->findByUuid($uuid);
        $concept = null;

        try {
            $concept = $this->fetchConcept($uuid);
        } catch (InvalidConceptData $e) {
            $this->log('InvalidConceptData in concept UPDATE: ', $e);

            return ConceptApiResponse::internalError($e);
        }

        if ( ! $concept instanceof OcConcept) {
            // Remove concept that cant't be retrieved from Open Content but was found in Wordpress
            return $post instanceof ConceptPost ? $this->removePost($uuid, $post) : ConceptApiResponse::notFoundInOC();
        }

        // Create concepts that can't be found in Wordpress
        if ( ! $post instanceof ConceptPost) {
            return $this->createPost($uuid, $concept);
        }

        return $this->updatePost($uuid, $post, $concept);
    }

    private function createPost($uuid, OcConcept $concept): ConceptApiResponse
    {
        try {
            $this->verifyParentExists($concept);

            $newPost = $this->repository->create($concept);

            $this->triggerEvent('created', new ConceptApiEvent($uuid, $newPost));

        } catch (ConceptCreateError $e) {
            $this->log('ConceptCreateError in concept CREATE: ', $e);

            return ConceptApiResponse::internalError($e);
        }

        return ConceptApiResponse::creationSuccess($this->getResponseCodes());
    }

    private function removePost(string $uuid, ConceptPost $post): ConceptApiResponse
    {
        try {
            $this->repository->delete($post);

            $this->triggerEvent('deleted', new ConceptApiEvent($uuid, $post));

        } catch (ConceptDeleteError $e) {
            $this->log('ConceptDeleteError in concept DELETE: ', $e);

            return ConceptApiResponse::internalError($e);
        }

        return ConceptApiResponse::deletionSuccess($this->getResponseCodes());
    }

    private function updatePost($uuid, ConceptPost $post, OcConcept $concept): ConceptApiResponse
    {
        try {
            $this->verifyParentExists($concept);

            $newPost = $this->repository->updateByOcConcept($concept);

            if ($post->post_parent !== $newPost->post_parent) {
                $this->addResponseCode(ConceptApiResponse::MOVED);
            }

            $this->triggerEvent('updated', new ConceptApiEvent($uuid, $newPost));

        } catch (ConceptUpdateError $e) {
            $this->log('ConceptUpdateError in concept UPDATE: ', $e);

            return ConceptApiResponse::internalError($e);
        }

        return ConceptApiResponse::updateSuccess($this->getResponseCodes());
    }

    /**
     * @param string $uuid
     *
     * @return OcConcept
     * @throws InvalidConceptData
     */
    private function fetchConcept($uuid): ?OcConcept
    {
        return $this->provider->getSingle($uuid, OcConcept::requiredProperties(1));
    }

    /**
     * @param ConceptPost $post
     * @param string|null $uuid
     *
     * @return array
     */
    private function extractPostData(ConceptPost $post, $uuid = null): array
    {
        $parent = $post->parent();
        $postId = $post->id;

        return [
            'uuid' => $uuid ?? $this->repository->findUuidById($postId),
            'postId' => $postId,
            'permalink' => $post->permalink(),
            'parent' => $parent instanceof ConceptPost ? $this->extractPostData($parent) : null
        ];
    }

    /**
     * Determine if a concept already exists in Wordpress
     *
     * @param $uuid
     *
     * @return bool
     */
    private function conceptExists($uuid): bool
    {
        return $this->repository->findByUuid($uuid) instanceof ConceptPost;
    }

    private function triggerEvent($eventName, ConceptApiEvent $event): void
    {
        $this->wpAction->doAction("ew_concept_{$eventName}", $event);
    }

    private function verifyParentExists(OcConcept $concept): bool
    {
        // Do nothing if no parent uuid can be found on concept or if parent already exists in database
        if ( ! $concept->getParentUuid() || $this->conceptExists($concept->getParentUuid())) {
            return true;
        }

        $this->addResponseCode(ConceptApiResponse::PARENT_NOT_FOUND_IN_WP);

        $parent = $this->fetchParent($concept);

        // If none is retrieved we set the response code for it
        if ( ! $parent instanceof OcConcept) {
            $this->addResponseCode(ConceptApiResponse::PARENT_NOT_FOUND_IN_SOURCE);

            return false;
        }

        $this->verifyParentExists($parent);

        try {
            $this->repository->create($parent);
        } catch (ConceptCreateError $e) {
            $this->addResponseCode(ConceptApiResponse::PARENT_NOT_CREATED);

            return false;
        }

        $this->addResponseCode(ConceptApiResponse::PARENT_CREATED);

        return true;
    }

    private function fetchParent(OcConcept $concept): ?OcConcept
    {
        // Has the parent been fetched as a relation? or request OC for the parent
        try {
            return $concept->getParent() ?? $this->fetchConcept($concept->getParentUuid());
        } catch (InvalidConceptData $e) {
            $this->addResponseCode(ConceptApiResponse::INTERNAL_ERROR);
        }

        return null;

    }

    private function addResponseCode(string $code): void
    {
        $this->responseCodes[] = $code;
    }

    /**
     * @return array
     */
    private function getResponseCodes(): array
    {
        return $this->responseCodes;
    }

    private function log(string $message, Exception $e): void
    {
        if ($this->logErrors) {
            NewRelicLog::error($message, $e);
        }
    }
}
