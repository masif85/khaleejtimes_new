<?php declare(strict_types=1);

namespace Spec;

use Everyware\ProjectPlugin\Components\Contracts\Admin;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

abstract class ComponentAdapterSpec extends ObjectBehavior
{
    protected $success = 'ok';
}
