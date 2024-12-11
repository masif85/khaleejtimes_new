<?php declare(strict_types=1);

namespace Everyware\OcObjects;

use Everyware\Contracts\OcObject;

class EmptyOcObject implements OcObject
{
    private $objectJson;
    private $objectUuid;

    public function __construct($objectUuid, $objectJson='')
    {
        $this->objectUuid = $objectUuid;
        $this->objectJson = $objectJson;
    }

    public function getCacheTTL(): int
    {
        return 30;
    }

    public function getUuid(): string
    {
        return $this->objectUuid;
    }

    public function toJson(): string
    {
        return '';
    }

    public function raw(): string
    {
        return $this->objectJson;
    }

    public function toArray($depth = 512, $options = 0): array
    {
        return [];
    }

    public function getProperties(): array
    {
        return [];
    }
}
