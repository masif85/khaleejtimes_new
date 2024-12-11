<?php

namespace Spec\Everyware\Plugin\ContentSync;

use Everyware\Plugin\ContentSync\Contracts\ContentEvent;
use Everyware\Plugin\ContentSync\Exceptions\InvalidEventException;
use Everyware\Plugin\ContentSync\Event;
use PhpSpec\ObjectBehavior;

class EventSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith('');
        $this->shouldHaveType(Event::class);
        $this->shouldImplement(ContentEvent::class);
    }

    public function it_should_parse_events_from_OpenContent_event_log()
    {
        $json = <<<JSON
{
  "id": 7107,
  "uuid": "c12bb71b-11a5-5081-8bbd-3d32537eac82",
  "eventType": "ADD",
  "created": "2021-05-24T06:08:23.000Z",
  "content": {
    "uuid": "c12bb71b-11a5-5081-8bbd-3d32537eac82",
    "version": 1,
    "created": "2021-05-24T06:07:21.000Z",
    "source": "cca",
    "contentType": "Image",
    "batch": false
  }
}
JSON;

        $this->beConstructedWith($json);

        $this->getContent()->shouldReturn([
            'uuid' => 'c12bb71b-11a5-5081-8bbd-3d32537eac82',
            'version' => 1,
            'created' => '2021-05-24T06:07:21.000Z',
            'source' => 'cca',
            'contentType' => 'Image',
            'batch' => false
        ]);
        $this->getContentType()->shouldReturn('Image');
        $this->getContentUuid()->shouldReturn('c12bb71b-11a5-5081-8bbd-3d32537eac82');
        $this->getContentVersion()->shouldReturn(1);
        $this->getId()->shouldReturn(7107);
        $this->getType()->shouldReturn('ADD');

        $this->isAdd()->shouldBe(true);
        $this->isdelete()->shouldBe(false);
        $this->isupdate()->shouldBe(false);
        $this->raw()->shouldBe($json);
    }

    public function it_should_validate_events()
    {
        $jsonWithSyntaxError = <<<JSON
{
  "id": 7107,
  "uuid": "c12bb71b-11a5-5081-8bbd-3d32537eac82",
  "eventType": "ADD",
  "created": "2021-05-24T06:08:23.000Z",
  "content": {
    "uuid": "c12bb71b-11a5-5081-8bbd-3d32537eac82",
    "version": 1,
    "created": "2021-05-24T06:07:21.000Z",
    "source": "cca",
    "contentType": "Image",
    "batch": false
  }
JSON;
        $this->shouldThrow(InvalidEventException::class)->during('__construct', [$jsonWithSyntaxError]);
        $this->shouldThrow(InvalidEventException::class)->during('__construct', ['{}']);
    }

    public function it_should_validate_events_with_missing_properties()
    {

        $jsonWithMissingContent = <<<JSON
{
  "id": 7107,
  "uuid": "c12bb71b-11a5-5081-8bbd-3d32537eac82",
  "eventType": "ADD",
  "created": "2021-05-24T06:08:23.000Z",
  "content": {
    "created": "2021-05-24T06:07:21.000Z",
    "source": "cca",
    "batch": false
  }
JSON;
        $this->shouldThrow(InvalidEventException::class)->during('__construct', ['{"someProperty":true}']);
        $this->shouldThrow(InvalidEventException::class)->during('__construct', [$jsonWithMissingContent]);
    }
}
