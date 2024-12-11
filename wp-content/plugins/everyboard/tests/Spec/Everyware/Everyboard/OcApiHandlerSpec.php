<?php

namespace Spec\Everyware\Everyboard;

use Everyware\Everyboard\OcApiHandler;
use PhpSpec\ObjectBehavior;

class OcApiHandlerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(OcApiHandler::class);
    }
}
