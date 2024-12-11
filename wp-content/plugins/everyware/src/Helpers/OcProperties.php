<?php declare(strict_types=1);

namespace Everyware\Helpers;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

class OcProperties implements ArrayAccess, Countable, IteratorAggregate
{
    protected $properties = [];

    public function __construct($properties = [])
    {
        $this->properties = $this->getArrayableItems($properties);
    }

    public function getIterator()
    {
        return new ArrayIterator($this->properties);
    }

    public function count(): int
    {
        return count($this->properties);
    }

    public function add(string $property): self
    {
        if ( ! $this->has($property)) {
            $this->properties[] = $property;
        }

        return $this;
    }

    public function all(): array
    {
        return $this->properties;
    }

    public function empty(): bool
    {
        return $this->count() === 0;
    }

    public function has(string $property): bool
    {
        return \in_array($property, $this->properties, true);
    }

    public function remove($property): self
    {
        if (($key = array_search($property, $this->properties, true)) !== false) {
            unset($this->properties[$key]);
        }

        return $this;
    }

    public function hasRelations(): bool
    {
        return ! empty($this->getRelations());
    }

    public function getRelationHierarchy(): array
    {
        $properties = $this->all();
        $hierarchy = [];

        foreach ($properties as $property) {
            $hierarchy = $this->buildHierarchy(explode('.', $property), $hierarchy);
        }

        return $hierarchy;
    }

    public function getRelations(): array
    {
        $relations = [];

        foreach ($this->all() as $property) {

            if (strpos($property, '.') !== false) {
                $relations[] = $property;
            }
        }

        return $relations;
    }

    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->properties);
    }

    public function offsetGet($offset)
    {
        return $this->properties[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->properties[] = $value;
        } else {
            $this->properties[$offset] = $value;
        }
    }

    public function offsetUnset($offset): void
    {
        unset($this->properties[$offset]);
    }

    public function toQueryString(): string
    {
        return $this->propertiesToQuerystring($this->getRelationHierarchy());
    }

    public function __toString(): string
    {
        return $this->toQueryString();
    }

    protected function buildHierarchy(array $relations, array $parent = []): array
    {
        if (empty($relations)) {
            return $parent;
        }

        $propertyName = array_shift($relations);
        $parent[$propertyName] = $this->buildHierarchy($relations, $parent[$propertyName] ?? []);

        return $parent;
    }

    protected function getArrayableItems($items)
    {
        if (is_array($items)) {
            return $items;
        }

        if ($items instanceof self) {
            return $items->all();
        }

        if ($items instanceof Traversable) {
            return iterator_to_array($items);
        }

        return (array)$items;
    }

    protected function propertiesToQuerystring(array $properties = []): string
    {
        $queryStrings = [];
        foreach ($properties as $property => $relatedProperties) {
            $queryStrings[] = $this->propertyToQuerystring($property, $relatedProperties);
        }

        return implode(',', $queryStrings);
    }

    protected function propertyToQuerystring(string $property, array $relatedProperties = []): string
    {
        if ( ! empty($relatedProperties)) {
            return sprintf("%s[%s]", $property, $this->propertiesToQuerystring($relatedProperties));
        }

        return $property;
    }
}
