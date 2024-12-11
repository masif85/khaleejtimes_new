<?php declare(strict_types=1);

namespace Everyware\Exceptions;

use InvalidArgumentException;
use Psr\SimpleCache\InvalidArgumentException as InvalidArgumentInterface;

/**
 * Class InvalidCacheValue
 * @package Everyware\Concepts\Exceptions
 */
class InvalidCacheValue  extends InvalidArgumentException implements InvalidArgumentInterface
{

}
