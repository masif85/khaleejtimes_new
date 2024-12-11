<?php

/** @noinspection UnknownInspectionInspection */
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection PhpUnused */

namespace Spec\Everyware\Everyboard;

use Everyware\Everyboard\OcApiResponse;
use InvalidArgumentException;
use PhpSpec\ObjectBehavior;

/**
 * @method toArray()
 */
class OcApiResponseSpec extends ObjectBehavior
{
    private $validResponseTypes = [
        OcApiResponse::RESPONSE_TYPE_OK => 'Result',
        OcApiResponse::RESPONSE_TYPE_WARNING => 'Warning',
        OcApiResponse::RESPONSE_TYPE_ERROR => 'Error'
    ];

    public function it_is_initializable()
    {
        $this->beConstructedWith(OcApiResponse::RESPONSE_TYPE_OK, '');
        $this->shouldHaveType(OcApiResponse::class);
    }

    public function it_should_throw_exception_on_wrong_response_type()
    {
        $this->beConstructedWith('Bad response type', '');
        $this->shouldThrow(InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_should_return_response_as_array()
    {
        $response = 'Response';

        $this->beConstructedWith(OcApiResponse::RESPONSE_TYPE_OK, $response);

        $this->toArray()->shouldReturn([
            'responseType' => OcApiResponse::RESPONSE_TYPE_OK,
            'responseSubject' => $this->validResponseTypes[OcApiResponse::RESPONSE_TYPE_OK],
            'response' => $response
        ]);
    }
}
