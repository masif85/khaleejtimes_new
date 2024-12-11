<?php

namespace Everyware\Plugin\ContentSync;

use Everyware\Plugin\ContentSync\Wordpress\Contracts\WpSendJson;
use Exception;
use InvalidArgumentException;
use JsonException;

class Router
{
    private WpSendJson $response;

    public function __construct(WpSendJson $response)
    {
        $this->response = $response;
    }

    public function handleEvent(): void
    {
        $this->validateRequest();

        try {
            $event = $this->getEvent();

            if ($event instanceof Event) {
                ignore_user_abort(true);//not required
                ob_start();
                // do initial processing here
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'message' => sprintf('Event: "%s" will be processed.', $event->getId())
                    ]
                ], JSON_THROW_ON_ERROR); // send the response
                status_header(202);
                header('Connection: close');
                header('Content-Length: ' . ob_get_length());
                header('Content-Type: application/json; charset=' . get_option('blog_charset'));
                ob_end_flush();
                flush();
                fastcgi_finish_request();//required for PHP-FPM (PHP > 5.3.3)
                ContentEvents::dispatch($event);
                die();
            }
        } catch (Exception $e) {
            $this->response->sendJsonError(['message' => $e->getMessage()], 400);
        }
    }

    public function validateRequest(): void
    {
        $server = $_SERVER;
        $method = $server['REQUEST_METHOD'] ?? '';
        $contentType = $server['CONTENT_TYPE'] ?? '';

        if ( ! in_array($method, ['GET', 'HEAD', 'POST', 'OPTIONS'])) {
            $this->response->sendJsonError([
                'message' => "Method: `$method` is not supported. Use `POST` instead."
            ], 405);
        }

        if (isset($server['HTTP_CONTENT_TYPE']) && $contentType !== 'application/json') {
            $this->response->sendJsonError([
                'message' => "$contentType is not supported. Use Content-Type: `application/json` instead."
            ], 415);
        }

        if (in_array($method, ['GET', 'HEAD', 'OPTIONS'])) {
            $this->response->sendJsonSuccess();
        }
    }

    /**
     * @throws JsonException|InvalidArgumentException
     */
    private function getValidBody(): array
    {
        $rawBody = file_get_contents("php://input");

        if (empty($rawBody)) {
            throw new InvalidArgumentException('No event data provided');
        }

        return json_decode($rawBody, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @throws JsonException|InvalidArgumentException
     */
    private function getEvent(): Event
    {
        $body = $this->getValidBody();

        if ( ! isset($body['data'])) {
            throw new InvalidArgumentException('No event data provided');
        }

        return new Event(json_encode($body['data'], JSON_THROW_ON_ERROR));
    }
}
