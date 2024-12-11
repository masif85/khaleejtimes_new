<?php declare(strict_types=1);

namespace Everyware\Concepts;

/**
 * Class AjaxRouter
 * @package Everyware\Concepts
 */
class AjaxAPI
{
    /**
     * @var string
     */
    private const AJAX_ENDPOINT = 'concepts';

    /**
     * @var array
     */
    private static $registeredRoutes = [];

    /**
     * Method of current request
     *
     * @var string
     */
    private $method;

    public function __construct()
    {
        add_action('wp_ajax_' . self::AJAX_ENDPOINT, [$this, 'route']);
        add_action('wp_ajax_nopriv_' . self::AJAX_ENDPOINT, [$this, 'route']);
    }

    public function route(): void
    {
        $this->method = $_SERVER['REQUEST_METHOD'];

        $requestData = $_REQUEST;

        $route = urldecode($requestData['route']);

        unset($requestData['route'], $requestData['action']);

        if ( ! $this->routeIsRegistered($route)) {
            ConceptApiResponse::routeNotFound($route)->send();
        }

        $response = $this->handleRequest($route, $requestData);

        if ( ! $response instanceof ConceptApiResponse) {
            $response = ConceptApiResponse::error([
                'message' => "Error occurred when handling route: {$route}. The request may not have been handled properly",
            ], 500);
        }

        $response->send();
    }

    private function handleRequest($route, $requestData = []): ConceptApiResponse
    {
        $availableRoutes = $this->getAvailableRoutes();

        return call_user_func_array($availableRoutes[$route], $requestData);
    }

    private function routeIsRegistered($route): bool
    {
        return array_key_exists($route, $this->getAvailableRoutes());
    }

    public static function post($route, callable $callable): void
    {
        static::$registeredRoutes['POST'][$route] = $callable;
    }

    public static function get($route, callable $callable): void
    {
        static::$registeredRoutes['GET'][$route] = $callable;
    }

    public static function init()
    {
        return new static;
    }

    private function getAvailableRoutes()
    {
        return static::$registeredRoutes[$this->method];
    }
}
