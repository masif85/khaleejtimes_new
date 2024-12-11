<?php declare(strict_types=1);

namespace Everyware\Exceptions;

use GuzzleHttp\Exception\ClientException;

class ObjectNotFoundException extends ClientException
{
    public function __construct(ClientException $e)
    {
        parent::__construct(
            $e->getMessage(),
            $e->getRequest(),
            $e->getResponse(),
            $e,
            $e->getHandlerContext()
        );
    }
}
