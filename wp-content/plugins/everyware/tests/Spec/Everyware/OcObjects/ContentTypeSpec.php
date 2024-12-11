<?php

/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */
/** @noinspection PhpUnused */

namespace Spec\Everyware\OcObjects;

use Everyware\OcObjects\ContentType;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Spec\DocumentFileLoader;

class ContentTypeSpec extends ObjectBehavior
{
    /**
     * @var string
     */
    private $documentUuid = '6ff3bdaa-b381-5861-9b45-57ec0cbf6760';
    private $articleDocument;

    function let()
    {
        $articleDocument = DocumentFileLoader::loadDocumentJson($this->documentUuid . '.json');

        $this->articleDocument = $articleDocument;

        $this->beConstructedWith($articleDocument);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ContentType::class);
    }

    function it_can_present_the_document_as_an_array()
    {
        $this->toArray()->shouldReturn(json_decode($this->articleDocument, true));
    }

    function it_can_present_the_document_as_json()
    {
        $this->toJson()->shouldReturn($this->articleDocument);
    }

    function it_can_present_the_document_as_its_raw_format()
    {
        $this->toJson()->shouldReturn($this->articleDocument);
    }

    function it_stores_its_own_cache_ttl()
    {
        $ttl = ContentType::OC_OBJECT_DEFAULT_TTL;

        if (defined('PHP_OB_CACHE_TTL')) {
            $ttl = PHP_OB_CACHE_TTL;
        }

        $this->getCacheTTL()->shouldReturn($ttl);
    }

    function it_offers_a_getter_for_the_contenttype_uuid()
    {
        $this->getUuid()->shouldReturn($this->documentUuid);
    }
}
