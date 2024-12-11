<?php

namespace Spec\Everyware\Helpers;

use Everyware\Helpers\OcProperties;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class OcPropertiesSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(OcProperties::class);
    }

    function it_can_count_properties()
    {
        $this->beConstructedWith([
            'uuid',
            'Type',
            'version'
        ]);

        $this->count()->shouldReturn(3);
    }

    function it_can_determine_if_the_list_is_empty()
    {
        $this->empty()->shouldReturn(true);
    }

    function it_can_add_properties_to_the_list()
    {
        $this->count()->shouldReturn(0);
        $this->empty()->shouldReturn(true);
        $this->add('uuid');

        $this->count()->shouldReturn(1);
        $this->empty()->shouldReturn(false);
    }

    function it_should_not_add_properties_twice()
    {
        $this->count()->shouldReturn(0);
        $this->empty()->shouldReturn(true);
        $this->add('uuid');
        $this->add('uuid');

        $this->count()->shouldReturn(1);
        $this->empty()->shouldReturn(false);
    }

    function it_can_get_relational_properties()
    {
        $this->beConstructedWith([
            'uuid',
            'Type',
            'version',
            'Articles.Images.uuid'
        ]);

        $this->getRelations()->shouldReturn(['Articles.Images.uuid']);
    }

    function it_can_determine_if_there_are_relational_properties()
    {
        $this->beConstructedWith([
            'uuid',
            'Type',
            'version'
        ]);

        $this->hasRelations()->shouldReturn(false);

        $this->add('Articles.Images.uuid');
        $this->hasRelations()->shouldReturn(true);
    }

    function it_can_build_an_hiarchical_tree_out_of_relational_properties()
    {
        $this->beConstructedWith([
            'contenttype',
            'Headline',
            'Pubdate',
            'Section',
            'BodyRaw',
            'uuid',
            'Articles.contenttype',
            'Articles.Headline',
            'Articles.Pubdate',
            'Articles.Section',
            'Articles.BodyRaw',
            'Articles.uuid',
            'Articles.Images.contenttype',
            'Articles.Images.uuid',
            'Articles.Images.Description',
            'Articles.Images.version',
            'Categories.contenttype',
            'Categories.ImageUuids',
            'Categories.ConceptMetaData',
            'Categories.Description',
            'Categories.DescriptionShort',
            'Categories.Name',
            'Categories.Type',
            'Categories.uuid',
            'Categories.ParentUuid',
            'Categories.Status'
        ]);

        $this->getRelationHierarchy()->shouldReturn([
            'contenttype' => [],
            'Headline' => [],
            'Pubdate' => [],
            'Section' => [],
            'BodyRaw' => [],
            'uuid' => [],
            'Articles' => [
                'contenttype' => [],
                'Headline' => [],
                'Pubdate' => [],
                'Section' => [],
                'BodyRaw' => [],
                'uuid' => [],
                'Images' => [
                    'contenttype' => [],
                    'uuid' => [],
                    'Description' => [],
                    'version' => []
                ]
            ],
            'Categories' => [
                'contenttype' => [],
                'ImageUuids' => [],
                'ConceptMetaData' => [],
                'Description' => [],
                'DescriptionShort' => [],
                'Name' => [],
                'Type' => [],
                'uuid' => [],
                'ParentUuid' => [],
                'Status' => []
            ]
        ]);
    }

    function it_can_build_an_hiarchical_query_string_out_of_relational_properties()
    {
        $this->beConstructedWith([
            'contenttype',
            'Headline',
            'Pubdate',
            'Section',
            'BodyRaw',
            'uuid',
            'Articles.contenttype',
            'Articles.Headline',
            'Articles.Pubdate',
            'Articles.Section',
            'Articles.BodyRaw',
            'Articles.uuid',
            'Articles.Images.contenttype',
            'Articles.Images.uuid',
            'Articles.Images.Description',
            'Articles.Images.version',
            'Categories.contenttype',
            'Categories.ImageUuids',
            'Categories.ConceptMetaData',
            'Categories.Description',
            'Categories.DescriptionShort',
            'Categories.Name',
            'Categories.Type',
            'Categories.uuid',
            'Categories.ParentUuid',
            'Categories.Status'
        ]);

        $string = '
        contenttype,
            Headline,
            Pubdate,
            Section,
            BodyRaw,
            uuid,
            Articles[
                contenttype,
                Headline,
                Pubdate,
                Section,
                BodyRaw,
                uuid,
                Images[
                    contenttype,
                    uuid,
                    Description,
                    version
                ]
            ],
            Categories[
                contenttype,
                ImageUuids,
                ConceptMetaData,
                Description,
                DescriptionShort,
                Name,
                Type,
                uuid,
                ParentUuid,
                Status
            ]
        ';

        $this->toQueryString()->shouldReturn(preg_replace('/\s+/', '', $string));
    }
}
