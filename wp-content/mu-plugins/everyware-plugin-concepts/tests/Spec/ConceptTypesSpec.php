<?php

namespace Spec\Everyware\Concepts;

use Everyware\Concepts\ConceptType;
use Everyware\Concepts\ConceptTypes;
use Everyware\Concepts\Contracts\SimpleCacheHandler;
use Everyware\Concepts\Exceptions\ConceptDeleteError;
use Everyware\Concepts\Exceptions\ConceptTypeCountError;
use Everyware\Concepts\Exceptions\ConceptTypeCreateError;
use Everyware\Concepts\Exceptions\ConceptTypeUpdateError;
use Everyware\Concepts\Wordpress\Contracts\WpTermRepository;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use WP_Error;

/**
 * @method count()
 * @method create(string $name, string $description)
 * @method delete(int $id)
 * @method update(int $id, array $data)
 */
class ConceptTypesSpec extends ObjectBehavior
{
    /**
     * @var WpTermRepository
     */

    private $repository;

    public function let(WpTermRepository $repository, SimpleCacheHandler $cache): void
    {
        $this->beConstructedWith($repository);
        $this->repository = $repository;
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ConceptTypes::class);
    }

    public function it_can_count_all_concept_types_in_the_database(): void
    {
        $OK = 5;

        $this->repository->countTerms(ConceptTypes::TAXONOMY_ID)->willReturn($OK);

        $this->willNotGenerateError($OK);

        $this->count()->shouldReturn($OK);
    }

    public function it_will_throw_exception_if_error_on_type_count(WP_Error $error): void
    {
        $result = null;

        $this->repository->countTerms(ConceptTypes::TAXONOMY_ID)->willReturn($error);

        $this->willGenerateError($error);

        $error->get_error_message()->willReturn('error');

        $this->shouldThrow(ConceptTypeCountError::class)->duringCount();
    }

    public function it_can_create_a_new_term_from_specified_name(ConceptType $type): void
    {
        $name = 'Type';
        $description = 'Description';
        $result = ['term_id' => 1];

        $this->repository
            ->insertTerm($name, ConceptTypes::TAXONOMY_ID, [
                'description' => $description
            ])
            ->willReturn($result);

        $this->willNotGenerateError($result);

        $this->willFindTermById($result['term_id'], $type);

        $this->create($name, $description)->shouldReturn($type);
    }

    public function it_will_throw_exception_if_error_on_type_creation(WP_Error $error)
    {
        $name = 'Type';
        $description = 'Description';

        $this->repository
            ->insertTerm($name, ConceptTypes::TAXONOMY_ID, [
                'description' => $description
            ])
            ->willReturn($error);

        $this->willGenerateError($error);

        $error->get_error_message()->willReturn('error');

        $this->shouldThrow(ConceptTypeCreateError::class)->duringCreate($name, $description);
    }

    public function it_will_throw_exception_if_id_is_missing_after_type_creation(): void
    {
        $name = 'Type';
        $description = 'Description';

        $result = [];

        $this->repository
            ->insertTerm($name, ConceptTypes::TAXONOMY_ID, [
                'description' => $description
            ])
            ->willReturn($result);

        $this->willNotGenerateError($result);

        $this->shouldThrow(ConceptTypeCreateError::class)->duringCreate($name, $description);
    }

    public function it_can_delete_term_by_specified_id(ConceptType $type): void
    {
        $id = 5;

        $this->willFindTermById($id, $type);

        $type->get('id')->willReturn($id);

        $this->repository->deleteTerm($id, ConceptTypes::TAXONOMY_ID)->willReturn($type);

        $this->willNotGenerateError($type);

        $this->delete($id)->shouldReturn($type);
    }

    public function it_will_throw_exception_if_type_could_not_be_found_on_delete()
    {
        $id = 5;

        $this->willFindTermById($id);

        $this->shouldThrow(ConceptDeleteError::class)->duringDelete($id);
    }

    public function it_will_throw_exception_if_error_on_type_deletion(ConceptType $type, WP_Error $error)
    {
        $id = 5;

        $this->willFindTermById($id, $type);

        $type->get('id')->willReturn($id);

        $this->repository->deleteTerm($id, ConceptTypes::TAXONOMY_ID)->willReturn($error);

        $this->willGenerateError($error);

        $error->get_error_message()->willReturn('error');

        $this->shouldThrow(ConceptDeleteError::class)->duringDelete($id);
    }

    public function it_can_update_a_term_by_specified_id_and_data(ConceptType $type): void
    {
        $id = 5;
        $data = [
            'description' => 'description'
        ];

        $result = [
            'term_id' => $id
        ];

        // Fetch type to update
        $this->willFindTermById($id, $type);

        $this->willUpdateTermWithData($type, $id, $data, $result);

        $this->willNotGenerateError($result);

        $this->update($id, $data)->shouldReturn($type);
    }

    public function it_will_throw_exception_if_error_on_type_update(ConceptType $type, WP_Error $error)
    {
        $id = 5;
        $data = [
            'description' => 'description'
        ];

        // Fetch type to update
        $this->willFindTermById($id, $type);

        $this->willUpdateTermWithData($type, $id, $data, $error);

        $this->willGenerateError($error);

        $error->get_error_message()->willReturn('error');

        $this->shouldThrow(ConceptTypeUpdateError::class)->duringUpdate($id, $data);
    }

    public function it_will_throw_exception_if_id_is_missing_after_type_update(ConceptType $type): void
    {
        $id = 5;
        $data = [
            'description' => 'description'
        ];

        $result = [];

        // Fetch type to update
        $this->willFindTermById($id, $type);

        $this->willUpdateTermWithData($type, $id, $data, $result);

        $this->willNotGenerateError($result);

        $this->shouldThrow(ConceptTypeUpdateError::class)->duringUpdate($id, $data);
    }

    // Helper functions
    // ======================================================


    private function willFindTermById($id, $result = null)
    {
        $this->repository->getTerm($id, ConceptTypes::TAXONOMY_ID)->willReturn($result);

        return $result;
    }

    private function willUpdateTermWithData(ConceptType $type, $id, $data, $result = null)
    {
        $description = 'stored description';
        $parent = 0;

        if (isset($data['description'])) {
            $description = $data['description'];
            $type->set('description', $description)->shouldBeCalled();
        }

        if (isset($data['parent'])) {
            $parent = $data['parent'];
            $type->set('parent', $parent)->shouldBeCalled();
        }

        $type->get('id')->willReturn($id);

        $type->get('description')->willReturn($description);

        $type->get('parent')->willReturn($parent);

        $this->repository
            ->updateTerm($id, ConceptTypes::TAXONOMY_ID, [
                'description' => $description,
                'parent' => $parent,
            ])
            ->willReturn($result);

        return $result;
    }

    private function willGenerateError($thing)
    {
        return $this->isError($thing, true);
    }

    private function willNotGenerateError($thing)
    {
        return $this->isError($thing, false);
    }

    private function isError($thing, $isError)
    {
        $this->repository->isError($thing)->willReturn($isError);

        return $isError;
    }
}
