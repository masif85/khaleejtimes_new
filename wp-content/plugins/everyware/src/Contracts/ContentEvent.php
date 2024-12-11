<?php declare(strict_types=1);

namespace Everyware\Contracts;

interface ContentEvent
{
    public function getEventId(): string;

    public function getEventType(): string;

    public function getDocumentType(): string;

    public function getDocumentId(): string;
}
