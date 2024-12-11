<?php

namespace Spec\Everyware\ProjectPlugin\Helpers;

use Everyware\ProjectPlugin\Helpers\Message;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class MessageSpec extends ObjectBehavior
{

    public function let()
    {
        $this->beConstructedWith(Message::ERROR, 'Error message');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Message::class);
    }

    public function it_can_be_staticly_created()
    {
        self::create(Message::ERROR, 'Error message')->shouldReturnAnInstanceOf(Message::class);
    }

    public function it_offers_a_getter_for_the_message()
    {
        $this->getMessage()->shouldReturn('Error message');
    }

    public function it_offers_a_getter_for_the_type()
    {
        $this->getType()->shouldReturn(Message::ERROR);
    }

    public function it_can_be_displayed_as_html()
    {
        $this->toHtml()->shouldReturn('<div class="notice notice-error"><p>Error message</p></div>');
    }

    public function it_handles_invalid_message_types()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('__construct', ['invalid_type', 'Error message']);
    }
}
