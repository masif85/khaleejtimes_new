<?php declare(strict_types=1);

namespace Everyware\Storage\Traits;

/**
 * Trait MemoryStorage
 * @package Everyware\Storage\Traits
 */
trait MemoryStorage
{
    protected static $memory = [];

    public function inMemory(string $key): bool
    {
        return array_key_exists($key, static::$memory);
    }

    public function getFromMemory(string $key)
    {
        return static::$memory[$key] ?? null;
    }

    public function addToMemory(string $key, $value): void
    {
        static::$memory[$key] = $value;
    }

    public function updateMemory(string $key, $value): void
    {
        $this->addToMemory($key, $value);
    }

    public function removeFromMemory(string $key): void
    {
        unset(static::$memory[$key]);
    }

    public function pullFromMemory(string $key)
    {
        if ( ! $this->inMemory($key)) {
            return null;
        }

        $value = $this->getFromMemory($key);
        $this->removeFromMemory($key);

        return $value;
    }

    public function clearMemory()
    {
        static::$memory = [];
    }
}
