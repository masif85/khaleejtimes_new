<?php

namespace Spec\Everyware\Plugin\Network;

use Everyware\Plugin\Network\Startup;
use PhpSpec\ObjectBehavior;

class StartupSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldHaveType(Startup::class);
    }
}
