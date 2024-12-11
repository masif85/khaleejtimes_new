<?php

/** @noinspection PhpVoidFunctionResultUsedInspection */
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */
/** @noinspection PhpUnused */

namespace Spec\Everyware\Storage;

use Everyware\OcClient;
use Everyware\Storage\Contracts\SimpleCacheInterface;
use Everyware\Storage\OcResponseCache;
use GuzzleHttp\Exception\ClientException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use RuntimeException;

class OcResponseCacheSpec extends ObjectBehavior
{
    /**
     * @var OcClient
     */
    private $client;

    /**
     * @var SimpleCacheInterface
     */
    private $cache;

    private $ttl = 60;

    function let(OcClient $client, SimpleCacheInterface $cache)
    {
        $this->client = $client;
        $this->cache = $cache;

        $this->beConstructedWith($client, $cache, $this->ttl);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(OcResponseCache::class);
    }

    function it_should_get_the_result_of_the_request_from_the_cache()
    {
        $response = 'Response';
        $uri = 'uri';
        $parameters = [];

        $key = $this->generateKey($uri, $parameters);

        $this->cache->get($key)->willReturn($response);

        $this->getContent($uri, $parameters)->shouldReturn($response);
    }

    function it_should_store_the_result_of_the_request_using_a_hash_from_the_url()
    {
        $response = 'Response';
        $uri = 'uri';
        $parameters = [];

        $key = $this->generateKey($uri, $parameters);

        $this->cache->get($key)->shouldBeCalled();

        $this->client->getContent($uri, $parameters)->willReturn($response);

        $this->cache->set($key, $response, $this->ttl)->willReturn(true);

        $this->getContent($uri, $parameters)->shouldReturn($response);
    }

    function it_should_throw_error_on_client_error_while_fetching_content()
    {
        $response = 'Response';
        $uri = 'uri';
        $parameters = [];

        $key = $this->generateKey($uri, $parameters);

        $this->cache->get($key)->shouldBeCalled();

        $this->client->getContent($uri, $parameters)->willThrow(ClientException::class);

        $this->cache->set($key, $response, $this->ttl)->shouldNotBeCalled();

        $this->shouldThrow(ClientException::class)->duringGetContent($uri, $parameters);
    }

    function it_should_throw_error_if_response_could_not_be_stored_in_cache()
    {
        $response = 'Response';
        $uri = 'uri';
        $parameters = [];

        $key = $this->generateKey($uri, $parameters);

        $this->cache->get($key)->shouldBeCalled();

        $this->client->getContent($uri, $parameters)->willReturn($response);

        $this->cache->set($key, $response, $this->ttl)->willReturn(false);

        $this->shouldThrow(RuntimeException::class)->duringGetContent($uri, $parameters);
    }

    function it_should_combine_uri_and_parameters_to_generate_the_cache_key()
    {
        $uri = 'uri/to/request';

        $params = [
            'q' => 'query',
            'param' => 1
        ];

        $key = OcResponseCache::KEY_PREFIX . md5($uri . http_build_query($params));

        $this->generateKey($uri, $params)->shouldReturn($key);
    }

    function it_should_not_include_empty_parameter_array_when_generating_key()
    {
        $uri = 'uri/to/request';
        $key = OcResponseCache::KEY_PREFIX . md5($uri);

        $this->generateKey($uri)->shouldReturn($key);
        $this->generateKey($uri, [])->shouldReturn($key);
        $this->generateKey($uri, [])->shouldReturn($key);
    }
}
