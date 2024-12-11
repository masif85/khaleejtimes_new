<?php declare(strict_types=1);

namespace Everyware\OcObjects;

use Everyware\Contracts\OcObject;
use Everyware\Exceptions\InvalidOcObjectException;

class ContentType implements OcObject
{
    /**
     * TTL in seconds
     *
     * @var int
     */
    public const OC_OBJECT_DEFAULT_TTL = 3600;

    /**
     * @var string
     */
    protected $objectJson;

    /**
     * @var array
     */
    protected $properties;

    /**
     * @var string
     */
    protected $uuid;

    public function __construct($documentJson)
    {
        $this->objectJson = $documentJson;
    }

    public function getCacheTTL(): int
    {
        if (defined('PHP_OB_CACHE_TTL')) {
            return PHP_OB_CACHE_TTL;
        }

        return static::OC_OBJECT_DEFAULT_TTL;
    }

    public function getUuid(): string
    {
        if ($this->uuid === null) {
            $properties = $this->getProperties();

            if ( ! array_key_exists('uuid', $properties) || empty($properties['uuid'])) {
                throw new InvalidOcObjectException('Invalid uuid in document json.');
            }

            $this->uuid = $properties['uuid'];
        }

        return $this->uuid;
    }

    public function raw(): string
    {
        return $this->objectJson;
    }

    public function toArray($depth = 512, $options = 0): array
    {
        return json_decode($this->objectJson, true, $depth, $options);
    }

    public function toJson(): string
    {
        return $this->objectJson;
    }

    public function getProperties(): array
    {
        if ( ! $this->properties) {
            $this->properties = $this->getParsedProperties();
        }

        return $this->properties;
    }

    protected function getParsedProperties(): array
    {
        $content = $this->toArray();
        $contentProperties = $content['properties'] ?? [];
        $properties = [];

        foreach ($contentProperties as $property) {
            $properties[$property['name']] = $this->parseProperty($property);
        }

        return $properties;
    }

    protected function parseProperty(array $property)
    {
        $type = $property['type'];
        $values = $property['values'];

        if ($type === 'BOOLEAN') {
            $values = array_map(static function ($value) {
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            }, $values);
        }

        if ($type === 'INTEGER') {
            $values = array_map(static function ($value) {
                return (int)$value;
            }, $values);
        }

        if ($property['multiValued']) {
            return $values;
        }

        return array_shift($values) ?? '';
    }
}
