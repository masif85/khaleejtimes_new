<?php

namespace Spec\Everyware\Concepts;

use Everyware\Concepts\Exceptions\InvalidConceptData;
use Everyware\Concepts\OcConcept;
use OcObject;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class OcConceptSpec extends ObjectBehavior
{
    /**
     * @var array
     */
    protected static $requiredProperties = [
        'Name',
        'Type',
        'uuid',
        'contenttype',
        'ParentUuid'
    ];

    public function let(): void
    {
        $this->beConstructedWith($this->generateValidConceptObject());
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(OcConcept::class);
    }

    public function it_should_throw_error_if_required_properties_is_missing()
    {
        $concept = $this->generateValidConceptObject();
        $concept->offsetUnset('type');
        $this->beConstructedWith($concept);
        $this->shouldThrow(InvalidConceptData::class)->duringInstantiation();
    }

    public function it_can_generate_a_list_of_properties()
    {
        $this->requiredProperties()->shouldReturn(static::$requiredProperties);
    }

    public function it_can_generate_a_tree_of_properties_with_multiple_parents()
    {
        $this->requiredProperties(1)->shouldReturn([
            'Name',
            'Type',
            'uuid',
            'contenttype',
            'ParentUuid',
            'Parent.Name',
            'Parent.Type',
            'Parent.uuid',
            'Parent.contenttype',
            'Parent.ParentUuid'
        ]);

        $this->requiredProperties(2)->shouldReturn([
            'Name',
            'Type',
            'uuid',
            'contenttype',
            'ParentUuid',
            'Parent.Name',
            'Parent.Type',
            'Parent.uuid',
            'Parent.contenttype',
            'Parent.ParentUuid',
            'Parent.Parent.Name',
            'Parent.Parent.Type',
            'Parent.Parent.uuid',
            'Parent.Parent.contenttype',
            'Parent.Parent.ParentUuid'
        ]);
    }

    public function it_can_tell_if_object_has_no_parents()
    {
        $this->hasParent()->shouldReturn(false);
        $this->getParent()->shouldReturn(null);
    }

    public function it_can_handle_if_an_object_has_a_parent()
    {
        $this->beConstructedWith($this->generateValidConceptObjectWithParent());
        $this->hasParent()->shouldReturn(true);
        $this->getParent()->shouldReturnAnInstanceOf(OcConcept::class);
        $this->offsetUnset('Parent');
        $this->hasParent()->shouldReturn(false);
        $this->getParent()->shouldReturn(null);

    }

    public function it_should_trow_error_if_an_object_has_an_invalid_parent()
    {
        $concept = $this->generateValidConceptObjectWithParent();
        foreach ($concept->parent as $parent) {
            $parent->offsetUnset('type');
        }

        $this->beConstructedWith($concept);
        $this->shouldThrow(InvalidConceptData::class)->duringInstantiation();
    }

    public function it_can_provide_the_necessary_post_meta()
    {
        $this->getPostMeta()->shouldReturn([
            'oc_uuid' => 'uuid'
        ]);
    }

    public function it_should_provide_empy_string_as_uuid_if_parent_is_not_set()
    {
        $this->getParentUuid()->shouldBe('');
    }

    // Helper functions
    // ======================================================


    /**
     * Generate Object with correct setup
     * @return OcObject
     */
    private function generateValidConceptObject(): OcObject
    {
        $concept = new OcObject();
        $concept->fill([
            'name' => ['Title'],
            'type' => ['type'],
            'uuid' => ['uuid'],
            'contenttype' => ['Concept'],
            'parentuuid' => []
        ]);

        return $concept;
    }

    private function generateValidConceptObjectWithParent(): OcObject
    {
        $concept = $this->generateValidConceptObject();
        $concept->set('parentuuid', ['uuid']);
        $concept->set('parent', [
            $this->generateValidConceptObject()
        ]);

        return $concept;

    }
}
