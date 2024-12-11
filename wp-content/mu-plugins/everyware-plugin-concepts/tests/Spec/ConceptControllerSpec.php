<?php

namespace Spec\Everyware\Concepts;

use Everyware\Concepts\ConceptApiResponse;
use Everyware\Concepts\ConceptController;
use Everyware\Concepts\ConceptPost;
use Everyware\Concepts\Contracts\ConceptProvider;
use Everyware\Concepts\Contracts\ConceptRepository;
use Everyware\Concepts\Events\ConceptApiEvent;
use Everyware\Concepts\Exceptions\ConceptCreateError;
use Everyware\Concepts\Exceptions\ConceptDeleteError;
use Everyware\Concepts\Exceptions\ConceptUpdateError;
use Everyware\Concepts\OcConcept;
use Everyware\Concepts\Wordpress\Contracts\WpAction;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @method create(string $uuid)
 * @method show(string $uuid)
 * @method update(string $uuid)
 * @method remove(string $uuid)
 * @method synchronize(string $uuid)
 */
class ConceptControllerSpec extends ObjectBehavior
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
     * @var WpAction
     */
    private $wpAction;

    /**
     * @param ConceptRepository $repository
     * @param ConceptProvider   $provider
     * @param WpAction          $wpAction
     */
    public function let(ConceptRepository $repository, ConceptProvider $provider, WpAction $wpAction): void
    {
        $this->beConstructedWith($repository, $provider, $wpAction, false);

        $this->provider = $provider;

        $this->repository = $repository;
        $this->wpAction = $wpAction;
    }

    // Tests for: Create
    // ======================================================

    public function it_respond_with_CREATE_success_if_concept_was_created(OcConcept $concept): void
    {
        $uuid = 'uuid';

        $this->willTryToFetchPost($uuid, null);

        $this->willTryToFetchConceptFromOC($uuid, $concept);

        // Simulate no parent
        $this->simulateParentExistCheck($concept);

        $this->repository->create($concept)->shouldBeCalled();

        $this->assertResponse($this->create($uuid), 201);
    }

    public function it_respond_with_CREATE_error_if_concept_failed_to_be_created(OcConcept $concept): void
    {
        $uuid = 'uuid';

        $this->willTryToFetchPost($uuid, null);

        $this->willTryToFetchConceptFromOC($uuid, $concept);

        // Simulate no parent
        $this->simulateParentExistCheck($concept);

        $this->repository->create($concept)->willThrow(ConceptCreateError::class);

        $this->assertResponse($this->create($uuid), 500, [
            'responseCodes' => [ConceptApiResponse::INTERNAL_ERROR]
        ]);
    }

    public function it_respond_with_CREATE_error_if_concept_cant_be_found_in_source(): void
    {
        $uuid = 'uuid';

        $this->willTryToFetchPost($uuid, null);

        $this->willTryToFetchConceptFromOC($uuid, null);

        $this->assertResponse($this->create($uuid), 424, [
            'responseCodes' => [ConceptApiResponse::NOT_FOUND_IN_SOURCE]
        ]);
    }

    public function it_respond_with_CREATE_error_if_concept_already_exists(ConceptPost $post): void
    {
        $uuid = 'uuid';

        $this->willTryToFetchPost($uuid, $post);

        $this->assertResponse($this->create($uuid), 409, [
            'responseCodes' => [ConceptApiResponse::ALREADY_EXISTS]
        ]);
    }

    public function it_should_trigger_event_when_post_has_been_created(OcConcept $concept, ConceptPost $post): void
    {
        $uuid = 'uuid';

        $this->willTryToFetchPost($uuid, null);

        $this->willTryToFetchConceptFromOC($uuid, $concept);

        // Simulate no parent
        $this->simulateParentExistCheck($concept);

        $post->beADoubleOf(ConceptPost::class);
        $this->repository->create($concept)->willReturn($post);

        $this->assertEventIsTriggered('created', $uuid, $post);

        $this->create($uuid);
    }

    // Tests for: Show
    // ======================================================

    public function it_respond_with_post_data_if_requested(ConceptPost $post): void
    {
        $uuid = 'uuid';

        $data = [
            'uuid' => $uuid,
            'postId' => 'id',
            'permalink' => 'url',
            'parent' => null
        ];

        $this->willTryToFetchPost($uuid, $post);

        $post->parent()->willReturn(null);

        $this->willExtractPostData($post, $data, true);

        $this->assertResponse($this->show($uuid), 200, [
            'responseCodes' => [],
            'data' => $data
        ]);
    }

    public function it_respond_with_post_data_for_parent_if_nested_object(
        ConceptPost $post,
        ConceptPost $parent
    ): void {
        $uuid = 'uuid';

        $parentData = [
            'uuid' => 'parentUuid',
            'postId' => 'parentId',
            'permalink' => 'parentUrl',
            'parent' => null
        ];

        $data = [
            'uuid' => $uuid,
            'postId' => 'id',
            'permalink' => 'url',
            'parent' => $parentData
        ];

        $this->willTryToFetchPost($uuid, $post);

        $post->parent()->willReturn($parent);

        $this->willExtractPostData($post, $data, true);

        $parent->parent()->willReturn(null);

        $this->willExtractPostData($parent, $parentData);

        $this->assertResponse($this->show($uuid), 200, [
            'responseCodes' => [],
            'data' => $data
        ]);
    }

    public function it_respond_with_SHOW_error_if_concept_cant_be_found_in_db(): void
    {
        $uuid = 'uuid';

        $this->willTryToFetchPost($uuid, null);

        $this->assertResponse($this->show($uuid), 404, [
            'responseCodes' => [ConceptApiResponse::NOT_FOUND_IN_WP],
            'data' => []
        ]);
    }

    // Tests for: Update
    // ======================================================

    public function it_respond_with_UPDATE_success_if_concept_is_updated(ConceptPost $post, OcConcept $concept): void
    {
        $uuid = 'uuid';

        $this->willTryToFetchPost($uuid, $post);

        $this->willTryToFetchConceptFromOC($uuid, $concept);

        // Simulate no parent
        $this->simulateParentExistCheck($concept);

        $this->repository->updateByOcConcept($concept)->shouldBeCalled();

        $this->assertResponse($this->update($uuid), 200);
    }

    public function it_respond_with_UPDATE_success_and_responseCode_if_concept_switched_parent_on_UPDATE(
        ConceptPost $post,
        OcConcept $concept,
        ConceptPost $newPost
    ): void {
        $uuid = 'uuid';

        $this->willTryToFetchPost($uuid, $post);

        $this->willTryToFetchConceptFromOC($uuid, $concept);

        // Simulate no parent
        $this->simulateParentExistCheck($concept);

        $post->get('post_parent')->willReturn('oldParent');
        $newPost->get('post_parent')->willReturn('newParent');

        $this->repository->updateByOcConcept($concept)->willReturn($newPost);

        $this->assertResponse($this->update($uuid), 200, [
            'responseCodes' => [ConceptApiResponse::MOVED]
        ]);
    }

    public function it_respond_with_UPDATE_error_if_concept_failed_to_update(
        ConceptPost $post,
        OcConcept $concept
    ): void {
        $uuid = 'uuid';

        $this->willTryToFetchPost($uuid, $post);

        $this->willTryToFetchConceptFromOC($uuid, $concept);

        // Simulate no parent
        $this->simulateParentExistCheck($concept);

        $this->repository->updateByOcConcept($concept)->willThrow(ConceptUpdateError::class);

        $this->assertResponse($this->update($uuid), 500, [
            'responseCodes' => [ConceptApiResponse::INTERNAL_ERROR]
        ]);
    }

    public function it_respond_with_UPDATE_error_if_concept_cant_be_found_in_db(): void
    {
        $uuid = 'uuid';

        $this->willTryToFetchPost($uuid, null);

        $this->assertResponse($this->update($uuid), 404, [
            'responseCodes' => [ConceptApiResponse::NOT_FOUND_IN_WP]
        ]);
    }

    public function it_respond_with_UPDATE_error_if_concept_cant_be_found_in_source(ConceptPost $post): void
    {
        $uuid = 'uuid';

        $this->willTryToFetchPost($uuid, $post);
        $this->willTryToFetchConceptFromOC($uuid, null);

        $this->assertResponse($this->update($uuid), 424, [
            'responseCodes' => [ConceptApiResponse::NOT_FOUND_IN_SOURCE]
        ]);
    }

    public function it_should_trigger_event_when_post_has_been_updated(OcConcept $concept, ConceptPost $post): void
    {
        $uuid = 'uuid';

        $this->willTryToFetchPost($uuid, $post);

        $this->willTryToFetchConceptFromOC($uuid, $concept);

        // Simulate no parent
        $this->simulateParentExistCheck($concept);

        $this->repository->updateByOcConcept($concept)->willReturn($post);

        $this->assertEventIsTriggered('updated', $uuid, $post);

        $this->update($uuid);

    }

    // Tests for: Remove
    // ======================================================

    public function it_respond_with_DELETE_success_if_concept_was_remove(ConceptPost $post): void
    {
        $uuid = 'uuid';

        $this->willTryToFetchPost($uuid, $post);

        $this->repository->delete($post)->shouldBeCalled();

        $this->assertResponse($this->remove($uuid), 200);
    }

    public function it_respond_with_DELETE_error_if_concept_failed_to_be_removed(ConceptPost $post): void
    {
        $uuid = 'uuid';

        $this->willTryToFetchPost($uuid, $post);

        $this->repository->delete($post)->willThrow(ConceptDeleteError::class);

        $this->assertResponse($this->remove($uuid), 500, [
            'responseCodes' => [ConceptApiResponse::INTERNAL_ERROR]
        ]);
    }

    public function it_respond_with_DELETE_error_if_no_concept_can_be_found_from_db(): void
    {
        $uuid = 'uuid';

        $this->willTryToFetchPost($uuid, null);

        $this->assertResponse($this->remove($uuid), 404, [
            'responseCodes' => [ConceptApiResponse::NOT_FOUND_IN_WP]
        ]);
    }

    public function it_should_trigger_event_when_post_has_been_deleted(ConceptPost $post): void
    {
        $uuid = 'uuid';

        $this->willTryToFetchPost($uuid, $post);

        $this->repository->delete($post)->shouldBeCalled();

        $this->assertEventIsTriggered('deleted', $uuid, $post);

        $this->remove($uuid);

    }

    // Tests for Synchronize
    // ======================================================

    public function it_should_create_non_existing_concepts_on_SYNC(OcConcept $concept): void
    {
        $uuid = 'uuid';

        $this->willTryToFetchPost($uuid, null);

        $this->willTryToFetchConceptFromOC($uuid, $concept);

        // Simulate no parent
        $this->simulateParentExistCheck($concept);

        $this->repository->create($concept)->shouldBeCalled();

        $this->assertResponse($this->synchronize($uuid), 201);
    }

    public function it_should_update_existing_concepts_on_SYNC(ConceptPost $post, OcConcept $concept): void
    {
        $uuid = 'uuid';

        $this->willTryToFetchPost($uuid, $post);

        $this->willTryToFetchConceptFromOC($uuid, $concept);

        // Simulate no parent
        $this->simulateParentExistCheck($concept);

        $this->repository->updateByOcConcept($concept)->shouldBeCalled();

        $this->assertResponse($this->synchronize($uuid), 200);
    }

    public function it_should_remove_existing_concepts_that_cant_be_fetched_from_OC_on_SYNC(
        ConceptPost $post
    ): void {
        $uuid = 'uuid';

        $this->willTryToFetchPost($uuid, $post);

        $this->willTryToFetchConceptFromOC($uuid, null);

        $this->repository->delete($post)->shouldBeCalled();

        $this->assertResponse($this->synchronize($uuid), 200);
    }

    public function it_respond_with_SYNC_error_if_concept_cant_be_found_in_source(): void
    {
        $uuid = 'uuid';

        $this->willTryToFetchPost($uuid, null);
        $this->willTryToFetchConceptFromOC($uuid, null);

        $this->assertResponse($this->synchronize($uuid), 424, [
            'responseCodes' => [ConceptApiResponse::NOT_FOUND_IN_SOURCE]
        ]);
    }

    // More General tests
    // ======================================================

    public function it_should_verify_that_parents_exists_in_db_on_CREATE(
        OcConcept $concept,
        OcConcept $parent,
        ConceptPost $parentPost
    ): void {
        $uuid = 'uuid';
        $parentUuid = 'parent_uuid';

        $this->willTryToFetchPost($uuid, null);
        $this->willTryToFetchConceptFromOC($uuid, $concept);

        // Simulate that parent is found in DB
        $this->simulateParentExistCheck($concept, $parentUuid, $parentPost);

        // Should NOT try to create Parent
        $this->repository->create($parent)->shouldNotBeCalled();

        $this->repository->create($concept)->shouldBeCalled();

        $this->assertResponse($this->create($uuid), 201);
    }

    public function it_should_verify_that_parents_exists_in_db_on_UPDATE(
        ConceptPost $post,
        OcConcept $concept,
        ConceptPost $parentPost,
        OcConcept $parent
    ): void {
        $uuid = 'uuid';
        $parentUuid = 'parent_uuid';

        $this->willTryToFetchPost($uuid, $post);
        $this->willTryToFetchConceptFromOC($uuid, $concept);

        // Simulate that parent is found in DB
        $this->simulateParentExistCheck($concept, $parentUuid, $parentPost);

        // Should NOT try to create Parent
        $this->repository->create($parent)->shouldNotBeCalled();

        $this->repository->updateByOcConcept($concept)->shouldBeCalled();

        $this->assertResponse($this->update($uuid), 200);
    }

    public function it_should_create_parents_that_cant_be_found_in_db_on_CREATE(
        OcConcept $concept,
        OcConcept $parent
    ): void {
        $uuid = 'uuid';
        $parentUuid = 'parent_uuid';

        $this->willTryToFetchPost($uuid, null);
        $this->willTryToFetchConceptFromOC($uuid, $concept);

        // Simulate that parent not found in DB
        $this->simulateParentExistCheck($concept, $parentUuid);

        $this->simulateFetchingParent($concept, $parent);

        // Verify that parent has no parent in turn before creating it
        $this->simulateParentExistCheck($parent);
        $this->repository->create($parent)->shouldBeCalled();

        $this->repository->create($concept)->shouldBeCalled();

        $this->assertResponse($this->create($uuid), 201, [
            'responseCodes' => [ConceptApiResponse::PARENT_NOT_FOUND_IN_WP, ConceptApiResponse::PARENT_CREATED]
        ]);
    }

    public function it_should_create_parents_that_cant_be_found_in_db_on_UPDATE(
        ConceptPost $post,
        OcConcept $concept,
        OcConcept $parent
    ): void {
        $uuid = 'uuid';
        $parentUuid = 'parent_uuid';

        $this->willTryToFetchPost($uuid, $post);
        $this->willTryToFetchConceptFromOC($uuid, $concept);

        // Simulate that parent not found in DB
        $this->simulateParentExistCheck($concept, $parentUuid);

        $this->simulateFetchingParent($concept, $parent);

        // Verify that parent has no parent in turn before creating it
        $this->simulateParentExistCheck($parent);

        $this->repository->create($parent)->shouldBeCalled();

        $this->repository->updateByOcConcept($concept)->shouldBeCalled();

        $this->assertResponse($this->update($uuid), 200, [
            'responseCodes' => [ConceptApiResponse::PARENT_NOT_FOUND_IN_WP, ConceptApiResponse::PARENT_CREATED]
        ]);
    }

    public function it_respond_if_parent_cant_be_fetched_from_source_on_CREATE(
        OcConcept $concept,
        OcConcept $parent
    ): void {
        $uuid = 'uuid';
        $parentUuid = 'parent_uuid';

        $this->willTryToFetchPost($uuid, null);
        $this->willTryToFetchConceptFromOC($uuid, $concept);

        // Simulate that parent not found in DB
        $this->simulateParentExistCheck($concept, $parentUuid);

        $this->simulateFetchingParent($concept, null, $parentUuid);
        $this->repository->create($parent)->shouldNotBeCalled();

        $this->repository->create($concept)->shouldBeCalled();

        $this->assertResponse($this->create($uuid), 201, [
            'responseCodes' => [
                ConceptApiResponse::PARENT_NOT_FOUND_IN_WP,
                ConceptApiResponse::PARENT_NOT_FOUND_IN_SOURCE
            ]
        ]);
    }

    public function it_respond_if_parent_cant_be_fetched_from_source_on_UPDATE(
        ConceptPost $post,
        OcConcept $concept,
        OcConcept $parent
    ): void {
        $uuid = 'uuid';
        $parentUuid = 'parent_uuid';

        $this->willTryToFetchPost($uuid, $post);
        $this->willTryToFetchConceptFromOC($uuid, $concept);

        // Simulate that parent not found in DB
        $this->simulateParentExistCheck($concept, $parentUuid);

        $this->simulateFetchingParent($concept, null, $parentUuid);
        $this->repository->create($parent)->shouldNotBeCalled();

        $this->repository->updateByOcConcept($concept)->shouldBeCalled();

        $this->assertResponse($this->update($uuid), 200, [
            'responseCodes' => [
                ConceptApiResponse::PARENT_NOT_FOUND_IN_WP,
                ConceptApiResponse::PARENT_NOT_FOUND_IN_SOURCE
            ]
        ]);
    }

    public function it_respond_if_parent_failed_to_be_created_on_CREATE(OcConcept $concept, OcConcept $parent): void
    {
        $uuid = 'uuid';
        $parentUuid = 'parent_uuid';

        $this->willTryToFetchPost($uuid, null);
        $this->willTryToFetchConceptFromOC($uuid, $concept);

        // Simulate that parent not found in DB
        $this->simulateParentExistCheck($concept, $parentUuid);

        $this->simulateFetchingParent($concept, $parent, $parentUuid);

        $this->repository->create($parent)->willThrow(ConceptCreateError::class);

        $this->repository->create($concept)->shouldBeCalled();

        $this->assertResponse($this->create($uuid), 201, [
            'responseCodes' => [ConceptApiResponse::PARENT_NOT_FOUND_IN_WP, ConceptApiResponse::PARENT_NOT_CREATED]
        ]);
    }

    public function it_respond_if_parent_failed_to_be_created_on_UPDATE(
        ConceptPost $post,
        OcConcept $concept,
        OcConcept $parent
    ): void {
        $uuid = 'uuid';
        $parentUuid = 'parent_uuid';

        $this->willTryToFetchPost($uuid, $post);
        $this->willTryToFetchConceptFromOC($uuid, $concept);

        // Simulate that parent not found in DB
        $this->simulateParentExistCheck($concept, $parentUuid);

        $this->simulateFetchingParent($concept, $parent, $parentUuid);

        $this->repository->create($parent)->willThrow(ConceptCreateError::class);

        $this->repository->updateByOcConcept($concept)->shouldBeCalled();

        $this->assertResponse($this->update($uuid), 200, [
            'responseCodes' => [ConceptApiResponse::PARENT_NOT_FOUND_IN_WP, ConceptApiResponse::PARENT_NOT_CREATED]
        ]);
    }

    // Helper functions
    // ======================================================

    private function willTryToFetchPost($uuid, $result)
    {
        $this->repository->findByUuid($uuid)->willReturn($result);

        return $result;
    }

    private function willTryToFetchConceptFromOC($uuid, $result)
    {
        $this->provider->getSingle($uuid, OcConcept::requiredProperties(1))->willReturn($result);

        return $result;
    }

    private function willExtractPostData(ConceptPost $post, $returnData, $hasUuid = false): void
    {
        $post->get('id')->willReturn($returnData['postId']);

        $post->permalink()->willReturn($returnData['permalink']);

        if ( ! $hasUuid) {
            $this->repository->findUuidById($returnData['postId'])->willReturn($returnData['uuid']);
        }
    }

    private function simulateParentExistCheck(OcConcept $concept, $uuid = null, ConceptPost $post = null): void
    {
        $concept->getParentUuid()->willReturn($uuid);

        if ($uuid) {
            $this->repository->findByUuid($uuid)->willReturn($post);
        }
    }

    private function simulateFetchingParent(
        OcConcept $concept,
        OcConcept $parent,
        string $uuid = null,
        $fromOC = false
    ): void {
        $relationWillReturn = $fromOC ? null : $parent;

        $concept->getParent()->willReturn($relationWillReturn);

        if ($relationWillReturn === null) {
            $this->willTryToFetchConceptFromOC($uuid, $parent);
        }
    }

    private function assertResponse($response, int $statusCode, array $responseData = []): void
    {
        $response->shouldHaveType(ConceptApiResponse::class);
        $response->getStatusCode()->shouldBe($statusCode);
        $response->getResponse()->shouldBe($responseData);
    }

    private function assertEventIsTriggered($eventName, $uuid, ConceptPost $post)
    {
        $this->wpAction->doAction("ew_concept_{$eventName}",
            new ConceptApiEvent($uuid, $post->getWrappedObject()))->shouldBeCalled();
    }
}
