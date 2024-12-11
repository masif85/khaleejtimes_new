<?php declare(strict_types=1);

namespace Everyware\Concepts;

use Everyware\Plugin\ContentSync\Contracts\ContentEvent;
use Everyware\Plugin\ContentSync\Contracts\ContentEventListener;

/**
 * Class ConceptEventListener
 * @package Everyware\Concepts
 */
class ConceptEventListener implements ContentEventListener
{
    private ConceptController $controller;

    public function __construct(ConceptController $controller)
    {
        $this->controller = $controller;
    }

    public function onAdd(ContentEvent $event): void
    {
        $this->handleResponse($event, $this->controller->synchronize($event->getContentUuid()));
    }

    public function onDelete(ContentEvent $event): void
    {
        $this->handleResponse($event, $this->controller->remove($event->getContentUuid()));
    }

    public function onUpdate(ContentEvent $event): void
    {
        $this->handleResponse($event, $this->controller->synchronize($event->getContentUuid()));
    }

    private function handleResponse(ContentEvent $event, ConceptApiResponse $response): void
    {
        if ( ! in_array($response->getStatusCode(), [200, 201])) {
            $errorMessage = sprintf(
                'ConceptPlugin triggered on Concept Event "%s" for Object: %s Resulted in Status: %s, "%s"',
                $event->getType(),
                $event->getContentUuid(),
                $response->getStatusCode(),
                implode(', ', $response->getResponse()['responseCodes'] ?? ['No response'])
            );

            error_log($errorMessage);
        }
    }

    public function handle(ContentEvent $event): void
    {
        if ($event->isAdd()) {
            $this->onAdd($event);
        } elseif ($event->isDelete()) {
            $this->onDelete($event);
        } elseif ($event->isUpdate()) {
            $this->onUpdate($event);
        }
    }
}
