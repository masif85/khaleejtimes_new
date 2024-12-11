<?php /** @noinspection ALL */

namespace Spec\Everyware\Concepts;

use Everyware\Concepts\ConceptPost;
use Everyware\Concepts\Concepts;
use Everyware\Concepts\Contracts\ConceptRepository;
use Everyware\Concepts\Contracts\SimpleCacheHandler;
use Everyware\Concepts\Exceptions\ConceptCreateError;
use Everyware\Concepts\Exceptions\ConceptDeleteError;
use Everyware\Concepts\Exceptions\ConceptMetaAddError;
use Everyware\Concepts\Exceptions\ConceptUpdateError;
use Everyware\Concepts\OcConcept;
use Everyware\Concepts\Wordpress\Contracts\PostRepository;
use Everyware\Concepts\Wordpress\Contracts\WpConceptPostRepository;
use Everyware\Concepts\Wordpress\Contracts\WpPostRepository;
use Infomaker\Everyware\Base\Models\Post;
use Infomaker\Everyware\Support\GenericPropertyObject;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ConceptsSpec extends ObjectBehavior
{
    /**
     * @var WpConceptPostRepository
     */
    private $repository;

    public function let(WpConceptPostRepository $repository, SimpleCacheHandler $cache): void
    {
        $this->beConstructedWith($repository);
        $this->repository = $repository;
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ConceptRepository::class);
    }

    public function it_can_add_meta_to_existing_concept(ConceptPost $post)
    {
        $id = 5;
        $postMeta = [
            'oc_uuid' => 'uuid'
        ];

        $post->get('id')->willReturn($id);

        $this->repository->addPostMeta($id, $postMeta)->shouldBeCalled();

        $this->addMeta($post, $postMeta);
    }

    public function it_can_fetch_all_concepts(): void
    {
        $OK = ['success'];

        $this->repository->query()->willReturn($OK);

        $this->all()->shouldReturn($OK);
    }

    public function it_can_count_all_concepts_in_the_database(): void
    {
        $OK = 5;

        $this->repository->countPosts()->willReturn($OK);

        $this->count()->shouldReturn($OK);
    }

    public function it_can_create_concepts_using_an_OcConcept(OcConcept $concept, ConceptPost $post): void
    {
        $id = 5;
        $title = 'Title';
        $postMeta = [
            'oc_uuid' => 'uuid'
        ];

        // Post values should be extracted
        $concept->getPostTitle()->willReturn($title);
        $concept->hasParent()->willReturn(false);
        $concept->getPostMeta()->willReturn($postMeta);

        // Extracted post values should be inserted
        $this->repository
            ->insertPost([
                'post_title' => $title,
                'post_parent' => 0
            ])
            ->willReturn($id);

        // Post meta should be added
        $this->repository->addPostMeta($id, $postMeta)->shouldBeCalled();

        // The ID will be used to fetch the post
        $this->willTryFindById($id, $post);

        $this->create($concept)->shouldReturn($post);
    }

    public function it_can_delete_concepts(ConceptPost $post): void
    {
        $id = 5;

        $post->get('id')->willreturn($id);

        $this->repository->deletePost($id, false)->willReturn($post);

        $this->delete($post)->shouldReturn($post);
    }

    public function it_can_delete_concepts_from_post_id(ConceptPost $post): void
    {
        $id = 5;
        $this->repository->deletePost($id, false)->willReturn($post);

        $this->deleteById($id)->shouldReturn($post);
    }

    public function it_can_save_concept_in_trash_on_delete(ConceptPost $concept): void
    {
        $id = 5;
        $keepInTrash = true;

        $this->repository->deletePost($id, $keepInTrash)->willReturn($concept);

        $this->deleteById($id, $keepInTrash)->shouldReturn($concept);
    }

    public function it_can_delete_concepts_from_OcConcept(ConceptPost $post, OcConcept $ocConcept): void
    {
        $id = 5;
        $uuid = 'uuid';

        // Simulate Using OcConcept to find by uuid
        //------------------------------------------------------
        $ocConcept->get('uuid')->willReturn($uuid);

        $this->simulateFindByUuid($uuid, $post);
        //------------------------------------------------------

        $post->get('id')->willReturn($id);

        $this->repository->deletePost($id, false)->willReturn($post);

        $this->deleteByOcConcept($ocConcept)->shouldReturn($post);
    }

    public function it_should_throw_exception_if_concept_cant_be_found_from_uuid_during_delete(OcConcept $ocConcept
    ): void {
        $uuid = 'uuid';

        // Simulate Using OcConcept to find by uuid
        //------------------------------------------------------
        $ocConcept->get('uuid')->willReturn($uuid);

        $this->simulateFindByUuid($uuid, null);
        //------------------------------------------------------

        $this->shouldThrow(ConceptDeleteError::class)->duringDeleteByOcConcept($ocConcept);
    }

    public function it_can_find_concepts_by_their_post_id(ConceptPost $post): void
    {
        $id = 5;
        $this->repository->getPost($id)->willReturn($post);

        $this->findById($id)->shouldReturn($post);
    }

    public function it_should_not_retrieve_concept_for_post_id_0(ConceptPost $post): void
    {
        $this->findById(0)->shouldReturn(null);
        $this->findById('0')->shouldReturn(null);
    }

    public function it_can_find_concepts_by_name(ConceptPost $post): void
    {
        $name = 'name';

        $this->repository->queryFirst(['title' => $name])->willReturn($post);

        $this->findByName($name)->shouldReturn($post);
    }

    public function it_can_find_concepts_by_path(ConceptPost $post): void
    {
        $path = 'path/to/concept';

        $this->repository->getPageByPath($path)->willReturn($post);

        $this->findByPath($path)->shouldReturn($post);
    }

    public function it_can_find_concepts_by_uuid(ConceptPost $post): void
    {
        $uuid = 'uuid';

        $this->simulateFindByUuid($uuid, $post);

        $this->findByUuid($uuid)->shouldReturn($post);
    }

    public function it_can_find_concepts_by_parent_id(ConceptPost $post): void
    {
        $id = 2;

        $this->repository->getPostsByParent($id)->willReturn([$post]);

        $this->findByParentId($id)->shouldReturn([$post]);
    }

    public function it_can_find_concepts_by_parent_uuid(ConceptPost $post): void
    {
        $uuid = 'uuid';
        $id = 5;

        $this->simulateFindByUuid($uuid, $post);

        // Should fetch by parent ID
        $post->get('id')->willReturn($id);

        $this->repository->getPostsByParent($id)->willReturn([$post]);

        $this->findByParentUuid($uuid)->shouldReturn([$post]);
    }

    public function it_can_find_concept_id_by_its_uuid(ConceptPost $post): void
    {
        $uuid = 'uuid';
        $id = 5;

        $this->simulateFindByUuid($uuid, $post);

        // Should have its ID fetched
        $post->get('id')->willReturn($id);

        $this->findIdByUuid($uuid)->shouldReturn($id);
    }

    public function it_can_find_concept_uuid_by_its_id(ConceptPost $post): void
    {
        $uuid = 'uuid';
        $id = 5;
        $this->willTryFindById($id, $post);

        $post->getMeta(Concepts::POST_UUID_FIELD, '')->willReturn($uuid);

        $this->findUuidById($id)->shouldReturn($uuid);
    }

    public function it_can_find_concepts_by_matching_metadata(ConceptPost $post): void
    {
        $uuid = 'uuid';
        $metaQuery = [
            'key' => Concepts::POST_UUID_FIELD,
            'value' => $uuid,
            'compare' => '='
        ];

        $this->repository
            ->queryFirst([
                'meta_query' => [$metaQuery]
            ])
            ->willReturn($post);

        $this->firstWhereMeta(Concepts::POST_UUID_FIELD, $uuid)->shouldReturn($post);
    }

    public function it_can_update_concepts(ConceptPost $post): void
    {
        $id = 5;
        $title = 'title';
        $parentId = 0;

        // Simulate Extraction of data for the update
        $post->get('id')->willReturn($id);
        $post->get('post_title')->willReturn($title);
        $post->get('post_parent')->willReturn($parentId);

        $this->repository
            ->updatePost($id, [
                'post_title' => $title,
                'post_parent' => $parentId
            ])
            ->willReturn($id);

        $this->willTryFindById($id, $post);

        $this->update($post)->shouldReturn($post);
    }

    public function it_can_update_concepts_from_OcConcept(ConceptPost $post, OcConcept $concept): void
    {
        $uuid = 'uuid';
        $id = 5;
        $title = 'title';
        $parentId = 0;

        // Simulate Using OcConcept to find by uuid
        //------------------------------------------------------
        $concept->get('uuid')->willReturn($uuid);

        $this->simulateFindByUuid($uuid, $post);
        //------------------------------------------------------

        // Simulate Using OcConcept to update post
        //------------------------------------------------------
        $concept->getPostTitle()->willReturn($title);

        $concept->hasParent()->willReturn(false);
        $post->set('post_title', $title)->shouldBeCalled();
        $post->set('post_parent', 0)->shouldBeCalled();
        //------------------------------------------------------

        // Simulate update method
        $post->get('id')->willReturn($id);
        $post->get('post_title')->willReturn($title);
        $post->get('post_parent')->willReturn($parentId);

        $this->repository
            ->updatePost($id, [
                'post_title' => $title,
                'post_parent' => $parentId
            ])
            ->willReturn($id);

        $this->willTryFindById($id, $post);

        $this->updateByOcConcept($concept)->shouldReturn($post);
    }

    public function it_should_throw_error_if_concept_cant_be_found_on_OcConcept_update(OcConcept $concept): void
    {
        $uuid = 'uuid';

        // Simulate Using OcConcept to find by uuid
        //------------------------------------------------------
        $concept->get('uuid')->willReturn($uuid);

        $this->simulateFindByUuid($uuid, null);
        //------------------------------------------------------

        $this->shouldThrow(ConceptUpdateError::class)->duringUpdateByOcConcept($concept);
    }

    public function it_can_fetch_concepts_based_on_its_metadata(ConceptPost $post): void
    {
        $metaQuery = [
            'key' => Concepts::POST_UUID_FIELD,
            'value' => 'uuid',
            'compare' => '='
        ];

        // Should fetch Concept by uuid
        $this->simulateWpQuery([
            'meta_query' => [$metaQuery]
        ], [$post]);

        $this->whereMeta(Concepts::POST_UUID_FIELD, 'uuid')->shouldReturn([$post]);

    }

    // Helper functions:
    // ======================================================

    private function defaultQuery(array $args = []): array
    {
        return array_replace([
            'post_type' => Concepts::POST_TYPE_ID,
            'posts_per_page' => -1
        ], $args);
    }

    private function simulateFindByUuid($uuid, $return)
    {
        $metaQuery = [
            'key' => Concepts::POST_UUID_FIELD,
            'value' => $uuid,
            'compare' => '='
        ];

        // Should fetch Concept by uuid
        $this->repository
            ->queryFirst([
                'meta_query' => [$metaQuery]
            ])
            ->willReturn($return);
    }

    private function willTryFindById($id, $return)
    {
        $this->repository->getPost($id)->willReturn($return);
    }

    private function simulateWpQuery($args, $return)
    {
        $this->repository->query($args)->willReturn($return);
    }
}
