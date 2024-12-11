<?php declare(strict_types=1);

namespace Everyware\Exceptions;

use InvalidArgumentException;
use Psr\SimpleCache\InvalidArgumentException as InvalidArgumentInterface;

/**
 * Class InvalidCacheKey
 * @package Everyware\Concepts\Exceptions
 */
class InvalidCacheKey extends InvalidArgumentException implements InvalidArgumentInterface
{

}
