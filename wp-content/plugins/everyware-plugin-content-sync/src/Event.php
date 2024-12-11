<?php declare(strict_types=1);

namespace Everyware\Plugin\ContentSync;

use Everyware\Plugin\ContentSync\Contracts\ContentEvent;
use Everyware\Plugin\ContentSync\Exceptions\InvalidEventException;
use JsonException;

/**
 * Interface OpenContentEvent
 * @package Everyware\Plugin\ContentSync
 */
class Event implements ContentEvent
{
    public const ADD = 'ADD';
    public const DELETE = 'DELETE';
    public const UPDATE = 'UPDATE';

    private static array $requiredProperties = [
        'id',
        'eventType',
        'content'
    ];

    private static array $requiredContentProperties = [
        'uuid',
        'contentType',
        'version'
    ];

    private array $event;

    private string $eventRaw;

    public function __construct(string $eventJson)
    {
        try {
            $event = json_decode($eventJson, true, 512, JSON_THROW_ON_ERROR);

            $this->validateEvent($event);
            $this->validateEventContent($event['content']);

            $this->event = $event;
        } catch (JsonException $e) {
            throw new InvalidEventException($e->getMessage());
        }

        $this->eventRaw = $eventJson;
    }

    public function getContent(): array
    {
        return $this->event['content'] ?? [];
    }

    public function getContentType(): string
    {
        return $this->getContent()['contentType'] ?? '';
    }

    public function getContentUuid(): string
    {
        return $this->getContent()['uuid'] ?? '';
    }

    public function getContentVersion(): int
    {
        return (int)($this->getContent()['version'] ?? 0);
    }

    public function getId(): int
    {
        return (int)($this->event['id'] ?? 0);
    }

    public function getType(): string
    {
        return $this->event['eventType'] ?? '';
    }

    public function isAdd(): bool
    {
        return $this->getType() === static::ADD;
    }

    public function isDelete(): bool
    {
        return $this->getType() === static::DELETE;
    }

    public function isUpdate(): bool
    {
        return $this->getType() === static::UPDATE;
    }

    public function raw(): string
    {
        return $this->eventRaw;
    }

    private function validateEvent(array $event): void
    {
        if (empty($event)) {
            throw new InvalidEventException('Can not handle empty events.');
        }

        $missingProperties = [];
        foreach (static::$requiredProperties as $property) {
            if ( ! array_key_exists($property, $event)) {
                $missingProperties[] = $property;
            }
        }

        if ( ! empty($missingProperties)) {
            throw new InvalidEventException(
                sprintf('The event is missing the required properties: "%s"', implode(', ', $missingProperties))
            );
        }
    }

    private function validateEventContent(array $content): void
    {
        if (empty($content)) {
            throw new InvalidEventException('Can not handle empty event content.');
        }

        $missingProperties = [];
        foreach (static::$requiredContentProperties as $property) {
            if ( ! array_key_exists($property, $content)) {
                $missingProperties[] = $property;
            }
        }

        if ( ! empty($missingProperties)) {
            throw new InvalidEventException(
                sprintf('The event content is missing the required properties: "%s"', implode(', ', $missingProperties)
                )
            );
        }
    }
}
