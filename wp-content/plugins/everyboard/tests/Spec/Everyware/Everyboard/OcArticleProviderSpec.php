<?php

/** @noinspection UnknownInspectionInspection */
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection PhpUnused */

namespace Spec\Everyware\Everyboard;

use Everyware\Everyboard\OcApiAdapter;
use Everyware\Everyboard\OcArticleProvider;
use Everyware\Helpers\OcProperties;
use OcArticle;
use OcObject;
use PhpSpec\ObjectBehavior;

class OcArticleProviderSpec extends ObjectBehavior
{
    /**
     * @var OcApiAdapter
     */
    private $ocApi;

    /**
     * @var OcProperties
     */
    private $properties;

    public function let(OcApiAdapter $ocApi, OcProperties $properties)
    {
        $this->ocApi = $ocApi;
        $this->properties = $properties;

        $this->beConstructedWith($ocApi, $properties);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(OcArticleProvider::class);
    }

    public function it_can_fetch_article(OcArticle $article)
    {
        $uuid = 'uuid';

        $this->ocApi->get_single_object($uuid, $this->requiredProperties(), '', true)
            ->shouldBeCalled()
            ->willReturn($article);

        $this->getArticle($uuid)->shouldReturn($article);
    }

    public function it_can_provide_uncached_article(OcArticle $article)
    {
        $uuid = 'uncached article uuid';
        $this->ocApi->get_single_object($uuid, $this->requiredProperties(), '',
            false)->shouldBeCalled()->willReturn($article);

        $this->getArticle($uuid, false)->shouldReturn($article);
    }

    public function it_will_treat_non_articles_as_fail(OcObject $article)
    {
        $uuid = 'no article uuid';
        $this->ocApi->get_single_object($uuid, $this->requiredProperties(), '',
            true)->shouldBeCalled()->willReturn($article);

        $this->getArticle($uuid)->shouldReturn(null);
    }

    public function it_should_serve_article_from_memory_if_found(OcArticle $article)
    {
        $uuid = 'stored article uuid';
        $this->addToMemory($uuid, $article);

        $this->ocApi->get_single_object($uuid, $this->requiredProperties(), '', true)->shouldNotBeCalled();

        $this->getArticle($uuid)->shouldReturn($article);
    }

    public function it_should_store_failed_article_requests_in_memory(OcArticle $article)
    {
        $uuid = 'failed article uuid';
        $this->inMemory($uuid)->shouldEqual(false);

        $this->ocApi->get_single_object($uuid, $this->requiredProperties(), '',
            true)->shouldBeCalled()->willReturn(null);

        $this->getArticle($uuid)->shouldReturn(null);

        $this->inMemory($uuid)->shouldEqual(true);
        $this->getFromMemory($uuid)->shouldReturn(null);
    }

    private function requiredProperties($properties = [])
    {
        $requiredProperties = empty($properties) ? [
            'uuid',
            'contenttype'
        ] : $properties;

        $this->properties->all()->willReturn($requiredProperties);

        return $requiredProperties;
    }
}
