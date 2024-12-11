<?php declare(strict_types=1);

namespace Spec;

/**
 * Class IterableClass
 * @package Spec
 */
class IteratorClass implements \Iterator
{
    /**
     * @var array
     */
    private $values;

    public function __construct($values = [])
    {
        $this->values = $values;
    }

    public function fill($values)
    {
        $this->values = array_replace($this->values, $values);
    }

    public function current()
    {
        // TODO: Implement current() method.
    }

    public function next()
    {
        // TODO: Implement next() method.
    }

    public function key()
    {
        // TODO: Implement key() method.
    }

    public function valid()
    {
        // TODO: Implement valid() method.
    }

    public function rewind()
    {
        // TODO: Implement rewind() method.
    }
}
