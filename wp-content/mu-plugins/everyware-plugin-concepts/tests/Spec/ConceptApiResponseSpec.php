<?php

namespace Spec\Everyware\Concepts;

use Everyware\Concepts\ConceptApiResponse;
use InvalidArgumentException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ConceptApiResponseSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->beConstructedWith([], 200);
        $this->shouldHaveType(ConceptApiResponse::class);
    }

    public function it_can_respond_with_correct_response_code()
    {
        $this->beConstructedThrough('createWithResponseCode', ['ALREADY_EXISTS', 409]);

        $this->respondWithStatus(409);
        $this->respondWith('ALREADY_EXISTS');
    }

    public function it_can_return_multiple_response_codes()
    {
        $this->beConstructedThrough('createWithResponseCode', ['ALREADY_EXISTS', 409]);
        $this->addResponseCode('MOVED');
        $this->respondWithStatus(409);
        $this->respondWith(['ALREADY_EXISTS', 'MOVED']);
    }

    public function it_should_validate_response_codes()
    {
        $this->beConstructedWith([], 200);
        $this->shouldThrow(new InvalidArgumentException('Invalid responseCode:"NOT_VALID_CODE"'))->during('addResponseCode', ['NOT_VALID_CODE']);
    }

    public function it_should_validate_response_codes_when_construction_with()
    {
        $this->beConstructedThrough('createWithResponseCode', ['NOT_VALID_CODE', 500]);
        $this->shouldThrow(new InvalidArgumentException('Invalid responseCode:"NOT_VALID_CODE"'))->duringInstantiation();
    }

    public function it_can_add_multiple_response_codes()
    {
        $this->beConstructedThrough('createWithResponseCode', ['ALREADY_EXISTS', 409]);
        $this->addResponseCodes(['MOVED', 'ALREADY_EXISTS', 'NOT_FOUND_IN_WP']);
        $this->respondWithStatus(409);
        $this->respondWith(['ALREADY_EXISTS', 'MOVED', 'NOT_FOUND_IN_WP']);
    }

    public function it_will_not_return_multiple_response_codes_of_same_value()
    {
        $this->beConstructedThrough('createWithResponseCode', ['ALREADY_EXISTS', 409]);
        $this->addResponseCode('MOVED');
        $this->addResponseCode('MOVED');
        $this->addResponseCode('NOT_FOUND_IN_WP');
        $this->addResponseCode('NOT_FOUND_IN_WP');
        $this->respondWithStatus(409);
        $this->respondWith(['ALREADY_EXISTS', 'MOVED', 'NOT_FOUND_IN_WP']);
    }

    public function it_can_use_predefined_constructors()
    {
        $this->beConstructedThrough('alreadyExists');
        $this->respondWithStatus(409);
        $this->respondWith('ALREADY_EXISTS');
    }

    public function it_can_reset_its_response()
    {
        $this->beConstructedWith([], 200);
        $this->setResponse([]);
        $this->respondWithStatus(200);
        $this->getResponse()->shouldReturn([]);
    }

    public function it_can_send_back_data_with_success()
    {
        $this->beConstructedThrough('success', [['data']]);

        $this->respondWithStatus(200);
        $this->getResponse()->shouldReturn([
            'responseCodes' => [],
            'data' => ['data']
        ]);
    }

    public function it_can_send_back_data_with_error()
    {
        $this->beConstructedThrough('error', [['data']]);

        $this->respondWithStatus(400);
        $this->getResponse()->shouldReturn([
            'responseCodes' => [],
            'data' => ['data']
        ]);
    }

    // Help functions
    // ======================================================


    private function respondWithStatus($statusCode)
    {
        $this->getStatusCode()->shouldReturn($statusCode);
    }

    private function respondWith($codes)
    {
        $this->getResponse()->shouldReturn(['responseCodes' => (array)$codes]);
    }
}
