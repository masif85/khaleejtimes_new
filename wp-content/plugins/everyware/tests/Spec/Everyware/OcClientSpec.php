<?php
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */
/** @noinspection PhpUnused */

namespace Spec\Everyware;

use Everyware\Contracts\OcObject;
use Everyware\Exceptions\ObjectNotFoundException;
use Everyware\OcClient;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PhpSpec\ObjectBehavior;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class OcClientSpec extends ObjectBehavior
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * @var StreamInterface
     */
    private $stream;

    function let(Client $client, ResponseInterface $response, StreamInterface $stream)
    {
        $this->client = $client;
        $this->response = $response;
        $this->stream = $stream;

        $this->beConstructedWith($client);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(OcClient::class);
    }

    function it_should_support_general_get_requests()
    {
        $uri = 'test';

        $this->client->get($uri, [])->willReturn($this->response);

        $this->get($uri)->shouldReturn($this->response);
    }

    function it_should_support_general_get_requests_with_content_as_response()
    {
        $uri = 'test';
        $content = json_encode(['testing']);
        $this->client->get($uri, [])->willReturn($this->response);

        $this->simulateResponseContent($content);

        $this->getContent($uri)->shouldReturn($content);
    }

    function it_should_support_search_requests()
    {
        $query = 'test';
        $params = ['q' => $query];

        $content = ['content'];

        $this->client->get('search', ['query' => $params])->willReturn($this->response);
        $this->simulateResponseJson(['hits' => $content]);

        $this->search($query, $params)->shouldReturn($content);
    }

    function it_should_support_search_requests_with_additional_parameters()
    {
        $query = 'test';
        $addedParams = [
            'contenttype' => 'Article',
            'properties' => 'uuid'
        ];

        $params = array_replace($addedParams, [
            'q' => $query
        ]);

        $content = ['content'];

        $this->client->get('search', ['query' => $params])->willReturn($this->response);
        $this->simulateResponseJson(['hits' => $content]);

        $this->search($query, $addedParams)->shouldReturn($content);
    }

    function it_should_support_requests_for_events()
    {
        $id = 1;

        $content = ['content'];

        $this->client->get('eventlog', ['query' => ['event' => $id]])->willReturn($this->response);

        $this->simulateResponseJson(['events' => $content]);

        $this->events($id)->shouldReturn($content);
    }

    function it_should_not_send_event_id_unless_given_while_requesting_events()
    {
        $content = ['content'];

        $this->client->get('eventlog', [])->willReturn($this->response);

        $this->simulateResponseJson(['events' => $content]);

        $this->events()->shouldReturn($content);
    }

    function it_should_be_able_to_retrieve_document_by_id()
    {
        $content = json_encode(['content']);
        $uuid = '000000-0000-0000-0000';

        $this->client->get("objects/{$uuid}/properties", [])->willReturn($this->response);

        $this->simulateResponseJson($content);

        $this->getObject($uuid)->shouldBeAnInstanceOf(OcObject::class);
    }

    function it_can_handle_404_status_code()
    {
        $uuid = '000000-0000-0000-0000';
        $path = "objects/{$uuid}/properties";

        $exception = new ClientException(
            '',
            new Request('get', $path),
            new Response(404)
        );
        $this->client->get($path, [])->willThrow($exception);

        $this->shouldThrow(ObjectNotFoundException::class)->during('getObject', [$uuid]);
    }

    function it_can_determine_if_path_will_return_200_status_code()
    {
        $path = 'path';
        $code = 200;
        $this->client->request('head', $path, ['http_errors' => false])->willReturn($this->response);

        $this->response->getStatusCode()->willReturn($code);

        $this->testPath($path, $code)->shouldReturn(true);
        $this->testPath($path, 500)->shouldReturn(false);
    }

    function it_can_determine_if_options_to_path_will_return_error_codes()
    {
        $path = 'path';
        $code = 400;
        $options = ['query' => ['foo' => 'bar']];
        $this->client->request('head', $path, array_replace($options, ['http_errors' => false]))
            ->willReturn($this->response);

        $this->response->getStatusCode()->willReturn($code);

        $this->testPath($path, $code, $options)->shouldReturn(true);
        $this->testPath($path, 200, $options)->shouldReturn(false);
    }

    function it_can_determine_the_open_content_version()
    {
        $version = '2.4.0';
        $this->client->get('infoandstats/version', [])->willReturn($this->response);

        $this->simulateResponseContent($version);

        $this->getOcVersion()->shouldReturn($version);
    }

    function it_can_test_the_connection_to_open_content()
    {
        $this->client->request('head', 'infoandstats/version', ['http_errors' => false])->willReturn($this->response);
        $this->response->getStatusCode()->willReturn(200);

        $this->testConnection()->shouldReturn(true);

        $this->response->getStatusCode()->willReturn(500);

        $this->testConnection()->shouldReturn(false);
    }

    function it_can_test_credentials_against_an_open_content()
    {
        $user = 'user';
        $password = 'pass';
        $this->client->request('head', 'infoandstats/version', [
            'auth' => [$user, $password],
            'http_errors' => false
        ])->willReturn($this->response);

        $this->response->getStatusCode()->willReturn(200);

        $this->testCredentials($user, $password)->shouldReturn(true);

        $this->response->getStatusCode()->willReturn(500);

        $this->testCredentials($user, $password)->shouldReturn(false);
    }

    private function simulateResponseJson($content)
    {
        $this->simulateResponseContent(json_encode($content));
    }

    private function simulateResponseContent($content)
    {
        $this->response->getBody()->willReturn($this->stream);

        $this->stream->getContents()->willReturn($content);
    }
}
