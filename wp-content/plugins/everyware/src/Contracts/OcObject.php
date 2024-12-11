<?php declare(strict_types=1);

namespace Everyware\Contracts;

interface OcObject
{
    public function getCacheTTL(): int;

    public function getUuid(): string;

    public function getProperties(): array;

    public function toJson(): string;

    public function raw(): string;

    public function toArray($depth = 512, $options = 0): array;
}
