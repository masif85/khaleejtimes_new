<?php

namespace Spec\Everyware\Concepts\Wordpress;

use Everyware\Concepts\ConceptPost;
use Everyware\Concepts\Concepts;
use Everyware\Concepts\Exceptions\ConceptCreateError;
use Everyware\Concepts\Exceptions\ConceptDeleteError;
use Everyware\Concepts\Exceptions\ConceptMetaAddError;
use Everyware\Concepts\Exceptions\ConceptUpdateError;
use Everyware\Concepts\OcConcept;
use Everyware\Concepts\Wordpress\ConceptPostRepository;
use Everyware\Concepts\Wordpress\Contracts\WpPostRepository;
use Infomaker\Everyware\Support\GenericPropertyObject;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ConceptPostRepositorySpec extends ObjectBehavior
{
    /**
     * @var WpPostRepository
     */
    private $repository;

    public function let(WpPostRepository $repository)
    {
        $this->beConstructedWith($repository);
        $this->repository = $repository;
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ConceptPostRepository::class);
    }

    public function it_can_add_meta_to_existing_concept()
    {
        $id = 5;
        $postMeta = [
            'oc_uuid' => 'uuid'
        ];

        foreach ($postMeta as $key => $value) {
            $this->repository->addPostMeta($id, $key, $value)
                ->shouldBeCalled()
                ->willReturn(2);
        }

        $this->addPostMeta($id, $postMeta);
    }

    public function it_should_throw_error_if_meta_failed_to_be_added_to_a_concept()
    {
        $id = 5;
        $postMeta = [
            'oc_uuid' => 'uuid'
        ];

        foreach ($postMeta as $key => $value) {
            $this->repository->addPostMeta($id, $key, $value)
                ->shouldBeCalled()
                ->willReturn(false);
        }

        $this->shouldThrow(ConceptMetaAddError::class)->duringAddPostMeta($id, $postMeta);
    }

    public function it_can_count_all_concepts_in_the_database(): void
    {
        $OK = 5;

        $postCountObject = new GenericPropertyObject();
        $postCountObject->publish = $OK;

        $this->repository
            ->countPosts(ConceptPostRepository::POST_TYPE_ID)
            ->shouldBeCalled()
            ->willReturn($postCountObject);

        $this->countPosts()->shouldReturn($OK);
    }

    public function it_can_will_return_zero_if_published_concepts_can_be_found_on_count_object(): void
    {
        $postCountObject = new GenericPropertyObject();

        $this->repository
            ->countPosts(ConceptPostRepository::POST_TYPE_ID)
            ->shouldBeCalled()
            ->willReturn($postCountObject);

        $this->countPosts()->shouldReturn(0);
    }

    public function it_can_insert_concepts_using_data_array(): void
    {
        $id = 5;

        $postData = [
            'post_title' => 'Parent Title',
            'post_parent' => 0,
            'post_type' => ConceptPostRepository::POST_TYPE_ID,
            'post_status' => 'publish'
        ];

        $this->repository->insertPost($postData)->shouldBeCalled()->willReturn($id);

        $this->repository->isError($id)->shouldBeCalled()->willReturn(false);

        $this->insertPost($postData)->shouldReturn($id);
    }

    public function it_should_throw_exception_if_insert_error(\WP_Error $error): void
    {
        $postData = [
            'post_title' => 'Parent Title',
            'post_parent' => 0,
            'post_type' => ConceptPostRepository::POST_TYPE_ID,
            'post_status' => 'publish'
        ];

        $this->repository->insertPost($postData)->shouldBeCalled()->willReturn($error);

        $this->repository->isError($error)->shouldBeCalled()->willReturn(true);

        $error->get_error_message()->shouldBeCalled()->willReturn('error');

        $this->shouldThrow(ConceptCreateError::class)->duringInsertPost($postData);
    }

    public function it_can_delete_concepts(ConceptPost $concept): void
    {
        $id = 5;

        $this->repository
            ->deletePost($id, true)
            ->shouldBeCalled()
            ->willReturn($concept);

        $this->deletePost($id)->shouldReturn($concept);
    }

    public function it_can_save_concept_in_trash_on_delete(ConceptPost $post): void
    {
        $id = 5;
        $keepInTrash = true;

        $this->repository
            ->deletePost($id, false)
            ->shouldBeCalled()
            ->willReturn($post);

        $this->deletePost($id, $keepInTrash)->shouldReturn($post);
    }

    public function it_should_throw_exception_if_delete_error(): void
    {
        $id = 5;
        $this->repository
            ->deletePost($id, true)
            ->shouldBeCalled()
            ->willReturn(null);

        $this->shouldThrow(ConceptDeleteError::class)->duringDeletePost($id);
    }

    public function it_can_find_concepts_by_path(ConceptPost $post): void
    {
        $path = 'path/to/concept';
        $this->repository
            ->getPageByPath($path, 'OBJECT', ConceptPostRepository::POST_TYPE_ID)
            ->shouldBeCalled()
            ->willReturn($post);

        $this->getPageByPath($path)->shouldReturn($post);
    }

    public function it_can_fetch_concepts_by_id(ConceptPost $post): void
    {
        $id = 5;
        $this->repository
            ->getPost($id)
            ->shouldBeCalled()
            ->willReturn($post);

        $this->getPost($id)->shouldReturn($post);
    }

    public function it_can_fetch_concepts_by_given_parent_id(ConceptPost $post): void
    {
        $id = 5;
        $this->repository
            ->query([
                'post_type' => ConceptPostRepository::POST_TYPE_ID,
                'posts_per_page' => -1,
                'post_parent' => $id
            ])
            ->shouldBeCalled()
            ->willReturn([$post]);

        $this->getPostsByParent($id)->shouldReturn([$post]);
    }

    public function it_will_use_wp_query_syntax_to_query_for_concept_posts(ConceptPost $post): void
    {
        $args = [];
        $this->repository
            ->query(array_replace([
                'post_type' => ConceptPostRepository::POST_TYPE_ID,
                'posts_per_page' => -1
            ], $args))
            ->shouldBeCalled()
            ->willReturn([$post]);

        $this->query($args)->shouldReturn([$post]);
    }

    public function it_can_update_concepts_using_data_array(): void
    {
        $id = 5;

        $postData = [
            'post_title' => 'Title',
            'post_parent' => 4
        ];

        $this->repository
            ->updatePost(array_replace([
                'ID' => $id
            ], $postData))
            ->shouldBeCalled()
            ->willReturn($id);

        $this->repository->isError($id)->shouldBeCalled()->willReturn(false);

        $this->updatePost($id, $postData)->shouldReturn($id);
    }

    public function it_should_throw_exception_if_update_error(\WP_Error $error): void
    {
        $id = 5;

        $postData = [
            'post_title' => 'Title',
            'post_parent' => 4
        ];

        $this->repository
            ->updatePost(array_replace([
                'ID' => $id
            ], $postData))
            ->shouldBeCalled()
            ->willReturn($error);

        $this->repository->isError($error)->shouldBeCalled()->willReturn(true);

        $error->get_error_message()->shouldBeCalled()->willReturn('error');

        $this->shouldThrow(ConceptUpdateError::class)->duringUpdatePost($id, $postData);
    }
}
