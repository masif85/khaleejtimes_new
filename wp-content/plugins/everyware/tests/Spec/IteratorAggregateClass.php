<?php declare(strict_types=1);

namespace Spec;

use Exception;
use Traversable;

/**
 * Class IterableClass
 * @package Spec
 */
class IteratorAggregateClass implements \IteratorAggregate
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

    public function getIterator()
    {
        return new \ArrayIterator($this->values);
    }
}
