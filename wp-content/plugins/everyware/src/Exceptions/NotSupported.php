<?php declare(strict_types=1);

namespace Everyware\Exceptions;

use RuntimeException;

/**
 * Class NotSupported
 * @package Everyware\Concepts\Exceptions
 */
class NotSupported extends RuntimeException
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct( 'Not supported' );
    }
}
