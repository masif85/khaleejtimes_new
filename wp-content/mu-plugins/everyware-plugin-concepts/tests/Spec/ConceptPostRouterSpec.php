<?php /** @noinspection ALL */

namespace Spec\Everyware\Concepts;

use Everyware\Concepts\ConceptPost;
use Everyware\Concepts\ConceptPostRouter;
use Everyware\Concepts\Contracts\ConceptRepository;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @method extractConceptRoute()
 * @method routeRequest()
 */
class ConceptPostRouterSpec extends ObjectBehavior
{
    /**
     * @var ConceptRepository
     */
    private $repository;

    public function let(ConceptRepository $repository): void
    {
        $_SERVER['REQUEST_URI'] = '/current/route/';
        $this->repository = $repository;
        $this->beConstructedWith($repository);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ConceptPostRouter::class);
    }

    public function it_should_extract_the_current_route(): void
    {
        $_SERVER['REQUEST_URI'] = '/current/route/';
        $this->extractConceptRoute()->shouldReturn('current/route');
    }

    public function it_should_exclude_query_parameters_when_extracting_route(): void
    {
        $_SERVER['REQUEST_URI'] = '/current/route/?q=random_string';
        $this->extractConceptRoute()->shouldReturn('current/route');
    }

    public function it_should_return_empty_string_if_empty_route(): void
    {
        $_SERVER['REQUEST_URI'] = '/';
        $this->extractConceptRoute()->shouldReturn('');
    }

    public function it_will_ignore_empty_routes(): void
    {
        $this->repository
            ->findByPath('current/route')
            ->shouldNotBeCalled();

        $this->routeRequest([])->shouldReturn([]);
    }

    public function it_will_ignore_concept_routes(): void
    {
        $queryVars = [
            'concept' => 'current/route',
            'post_type' => 'concept',
            'name' => 'current/route'
        ];

        $this->repository
            ->findByPath('current/route')
            ->shouldNotBeCalled();

        $this->routeRequest($queryVars)->shouldReturn($queryVars);
    }

    public function it_will_ignore_page_routes(): void
    {
        $queryVars = [
            'page' => '',
            'pagename' => 'teaser-gallery'
        ];
        $this->repository
            ->findByPath('current/route')
            ->shouldNotBeCalled();

        $this->routeRequest($queryVars)->shouldReturn($queryVars);
    }

    public function it_will_check_article_routs(): void
    {
        $queryVars = [
            'article' => 'route',
            'post_type' => 'article',
            'name' => 'route',
        ];

        $this->repository->findByPath('current/route')->willReturn(null);

        $this->routeRequest($queryVars)->shouldReturn($queryVars);
    }

    public function it_will_redirect_to_concept_routes_if_found(ConceptPost $post): void
    {

        $queryVars = [
            'article' => 'route',
            'post_type' => 'article',
            'name' => 'route',
        ];

        $conceptVars = [
            'concept' => 'current/route',
            'post_type' => 'concept',
            'name' => 'current/route'
        ];

        $this->repository->findByPath('current/route')->willReturn($post);

        $this->routeRequest($queryVars)->shouldReturn($conceptVars);
    }
}
