<?php declare(strict_types=1);

namespace Everyware\Plugin\ContentSync\Contracts;

/**
 * Class ContentEvent
 */
interface ContentEvent
{
    public function getContent(): array;

    public function getContentType(): string;

    public function getContentUuid(): string;

    public function getContentVersion(): int;

    public function getId(): int;

    public function getType(): string;

    public function isAdd(): bool;

    public function isDelete(): bool;

    public function isUpdate(): bool;

    public function raw(): string;
}
