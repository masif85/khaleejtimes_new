<?php declare(strict_types=1);

namespace Everyware\Concepts;

use Everyware\Concepts\Contracts\ConceptRepository;

/**
 * ConceptPostRouter
 *
 * @link    http://infomaker.se
 * @package Everyware\Concepts
 * @since   Everyware\Concepts\ConceptPostRouter 1.0.0
 */
class ConceptPostRouter
{
    /**
     * Contains the current request
     *
     * @var string
     */
    protected $request;

    /**
     * @var ConceptRepository
     */
    private $repository;

    /**
     * ConceptsController constructor.
     *
     * @param ConceptRepository $repository
     */
    public function __construct(ConceptRepository $repository)
    {
        $this->repository = $repository;
        $this->request = $this->extractConceptRoute();
    }

    /**
     * WP_filter: Fires on the "request" filter
     * Route the request to index.php?concept_route=[route/for/concept]
     *
     * @param array $query_vars
     *
     * @return array
     */
    public function routeRequest(array $query_vars = []): array
    {
        // Do nothing if empty, page or an actual Concept requested
        if (empty($query_vars) || isset($query_vars[Concepts::POST_TYPE_ID]) || isset($query_vars['pagename'])) {
            return $query_vars;
        }

        // Redirect to concept if match is found
        if ($this->conceptPathExist()) {
            $conceptType = Concepts::POST_TYPE_ID;
            $conceptUri = $this->getRequestedUri();

            return [
                $conceptType => $conceptUri,
                'post_type' => $conceptType,
                'name' => $conceptUri
            ];
        }

        return $query_vars;
    }

    /**
     * Extract route after home_url and remove query vars.
     *
     * @return string
     */
    public function extractConceptRoute(): string
    {
        $route = str_replace(home_url(), '', $_SERVER['REQUEST_URI']);

        if (($filteredRoute = strtok($route, '?')) !== false) {
            $route = $filteredRoute;
        }

        return trim($route, '/');
    }

    private function conceptPathExist(): bool
    {
        return $this->repository->findByPath($this->getRequestedUri()) instanceof ConceptPost;
    }

    private function getRequestedUri(): string
    {
        return $this->request;
    }

    /**
     * Setup routing for automatic concept-pages
     */
    public static function init(): void
    {
        $router = new static(Concepts::init());
        add_filter('request', [$router, 'routeRequest']);
    }
}
