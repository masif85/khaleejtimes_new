<?php

/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */
/** @noinspection PhpUnused */

namespace Spec\Everyware\Storage;

use Everyware\Exceptions\InvalidCacheKey;
use Everyware\Exceptions\NotSupported;
use Everyware\Storage\SimpleCache;
use Everyware\Wordpress\TransientCache;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Spec\IteratorAggregateClass;
use Spec\IteratorClass;

class SimpleCacheSpec extends ObjectBehavior
{
    /**
     * @var TransientCache
     */
    private $wpCache;

    function let(TransientCache $wpCache)
    {
        $this->wpCache = $wpCache;
        $this->beConstructedWith($wpCache);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SimpleCache::class);
    }

    function it_should_get_value_from_cache()
    {
        $key = 'transient_key';
        $value = 'some value';

        $this->wpCache->get($key)->willReturn($value);

        $this->get($key)->shouldReturn($value);
    }

    function it_should_return_default_value_if_value_is_not_found_in_cache()
    {
        $key = 'transient_key';
        $value = 'some value';

        $this->wpCache->get($key)->willReturn(false);

        $this->get($key)->shouldReturn(null);
        $this->get($key, $value)->shouldReturn($value);
    }

    function it_should_store_value_with_ttl_in_cache()
    {
        $key = 'transient_key';
        $value = 'some value';
        $ttl = 10;

        $this->wpCache->set($key, $value, 0)->shouldBeCalled();
        $this->set($key, $value);

        $this->wpCache->set($key, $value, $ttl)->shouldBeCalled();
        $this->set($key, $value, $ttl);
    }

    function it_should_respond_correctly_if_value_was_added_to_cache()
    {
        $key = 'transient_key';
        $value = 'some value';
        $ttl = 10;

        $this->wpCache->set($key, $value, $ttl)->willReturn(true);
        $this->set($key, $value, $ttl)->shouldReturn(true);

        $this->wpCache->set($key, $value, $ttl)->willReturn('true');
        $this->set($key, $value, $ttl)->shouldReturn(true);
    }

    function it_should_respond_correctly_if_value_was_not_added_to_cache()
    {
        $key = 'transient_key';
        $value = 'some value';
        $ttl = 10;

        $this->wpCache->set($key, $value, $ttl)->willReturn(false);
        $this->set($key, $value, $ttl)->shouldReturn(false);

        $this->wpCache->set($key, $value, $ttl)->willReturn('false');
        $this->set($key, $value, $ttl)->shouldReturn(false);

        $this->wpCache->set($key, $value, $ttl)->willReturn(null);
        $this->set($key, $value, $ttl)->shouldReturn(false);
    }

    function it_should_remove_value_from_cache()
    {
        $key = 'transient_key';

        $this->wpCache->delete($key)->shouldBeCalled();
        $this->delete($key);
    }

    function it_should_respond_correctly_if_value_was_removed_from_cache()
    {
        $key = 'transient_key';

        $this->wpCache->delete($key)->willReturn(true);
        $this->delete($key)->shouldReturn(true);

        $this->wpCache->delete($key)->willReturn('true');
        $this->delete($key)->shouldReturn(true);
    }

    function it_should_respond_correctly_if_value_was_not_removed_from_cache()
    {
        $key = 'transient_key';
        $this->wpCache->delete($key)->willReturn(false);
        $this->delete($key)->shouldReturn(false);

        $this->wpCache->delete($key)->willReturn('false');

        $this->delete($key)->shouldReturn(false);
    }

    function it_should_not_support_clearing_the_hole_system_for_content()
    {
        $this->shouldThrow(NotSupported::class)->duringClear();
    }

    function it_should_support_recieving_values_from_multiple_keys()
    {
        $keys = [
            'key_1',
            'key_2',
            'key_3'
        ];
        $values = [];

        foreach ($keys as $i => $key) {
            $this->wpCache->get($key)->willReturn('value_' . $i);
            $values[$key] = 'value_' . $i;
        }

        $this->getMultiple($keys)->shouldReturn($values);
    }

    function it_should_support_recieving_values_from_multiple_keys_with_defaults()
    {
        $keys = [
            'key_1',
            'key_2',
            'key_3'
        ];

        $default = 'defaultValue';

        $this->wpCache->get('key_1')->willReturn('value_1');
        $this->wpCache->get('key_2')->willReturn(false);
        $this->wpCache->get('key_3')->willReturn('value_3');

        $this->getMultiple($keys, $default)->shouldReturn([
            'key_1' => 'value_1',
            'key_2' => $default,
            'key_3' => 'value_3'
        ]);
    }

    function it_should_support_multiple_insertions()
    {
        $values = [
            'key_1' => 'value_1',
            'key_2' => ['value_2'],
            'key_3' => (object)['value_3']
        ];

        foreach ($values as $key => $value) {
            $this->wpCache->set($key, $value, 0)->willReturn(true);
        }

        $this->setMultiple($values)->shouldReturn(true);
    }

    function it_should_support_ttl_on_multiple_insertions()
    {
        $values = [
            'key_1' => 'value_1',
            'key_2' => ['value_2'],
            'key_3' => (object)['value_3']
        ];
        $ttl = 10;

        foreach ($values as $key => $value) {
            $this->wpCache->set($key, $value, $ttl)->willReturn(true);
        }

        $this->setMultiple($values, $ttl)->shouldReturn(true);
    }

    function it_should_responde_with_false_if_one_or_more_multiple_insertions_fail()
    {
        $values = [
            'key_1' => 'value_1',
            'key_2' => ['value_2'],
            'key_3' => (object)['value_3']
        ];

        foreach ($values as $key => $value) {

            if ($key === 'key_2') {
                $this->wpCache->set($key, $value, 0)->willReturn(false);
            } else {
                $this->wpCache->set($key, $value, 0)->willReturn(true);
            }
        }

        $this->setMultiple($values)->shouldReturn(false);
    }

    function it_should_support_multiple_deletions()
    {
        $keys = [
            'key_1',
            'key_2',
            'key_3'
        ];

        foreach ($keys as $i => $key) {
            $this->wpCache->delete($key)->willReturn(true);
        }

        $this->deleteMultiple($keys)->shouldReturn(true);
    }

    function it_should_responde_with_false_if_one_or_more_deletions_failed()
    {
        $keys = [
            'key_1',
            'key_2',
            'key_3'
        ];

        foreach ($keys as $i => $key) {
            if ($key === 'key_2') {
                $this->wpCache->delete($key)->willReturn(false);
            } else {
                $this->wpCache->delete($key)->willReturn(true);
            }
        }

        $this->deleteMultiple($keys)->shouldReturn(false);
    }

    function it_can_determines_whether_an_item_is_present_in_the_cache()
    {
        $key = 'key_1';

        $this->wpCache->get($key)->willReturn(false);
        $this->has($key)->shouldReturn(false);

        $this->wpCache->get($key)->willReturn(true);
        $this->has($key)->shouldReturn(true);
    }

    function it_should_be_able_to_remember_values_in_cache()
    {
        $key = 'key_1';
        $value = 'remember this value';
        $ttl = 10;

        $this->wpCache->get($key)->willReturn(false);
        $this->wpCache->set($key, $value, $ttl)->willReturn(true);

        $this->remember($key, $ttl, static function () use ($value) {
            return $value;
        })->shouldReturn($value);
    }

    function it_should_not_remember_values_in_cache_if_found()
    {
        $key = 'key_1';
        $value = 'remember this value';
        $ttl = 10;

        $this->wpCache->get($key)->willReturn($value);
        $this->wpCache->set($key, $value, $ttl)->shouldNotBeCalled();

        $this->remember($key, $ttl, static function () use ($value) {
            return $value;
        })->shouldReturn($value);
    }

    function it_should_not_remember_values_in_cache_if_callback_returns_null()
    {
        $key = 'key_1';
        $value = 'remember this value';
        $ttl = 10;

        $this->wpCache->get($key)->willReturn(false);
        $this->wpCache->set($key, $value, $ttl)->shouldNotBeCalled();

        $this->remember($key, $ttl, static function () use ($value) {
            return null;
        })->shouldReturn(null);
    }

    function it_should_be_able_to_pull_values_from_cache()
    {
        $key = 'key_1';
        $value = 'Pulled value';

        $this->wpCache->get($key)->willReturn($value);
        $this->wpCache->delete($key)->willReturn(true);

        $this->pull($key)->shouldReturn($value);
    }

    function it_should_not_try_to_remove_nonexistent_values_from_cache_if_pulled()
    {
        $key = 'key_1';

        $this->wpCache->get($key)->willReturn(false);
        $this->wpCache->delete($key)->shouldNotBeCalled();

        $this->pull($key)->shouldReturn(null);
    }

    function its_multiple_methodeds_should_support_IteratorAggregate_iterables(IteratorAggregateClass $iterator)
    {
        $this->shouldNotThrow(InvalidCacheKey::class)->during('getMultiple', [$iterator]);
        $this->shouldNotThrow(InvalidCacheKey::class)->during('deleteMultiple', [$iterator]);
        $this->shouldNotThrow(InvalidCacheKey::class)->during('setMultiple', [$iterator]);
    }

    function its_multiple_methodeds_should_support_Iterator_iterables(IteratorClass $iterator)
    {
        $this->shouldNotThrow(InvalidCacheKey::class)->during('getMultiple', [$iterator]);
        $this->shouldNotThrow(InvalidCacheKey::class)->during('deleteMultiple', [$iterator]);
        $this->shouldNotThrow(InvalidCacheKey::class)->during('setMultiple', [$iterator]);
    }

    function it_should_validate_keys()
    {
        $this->shouldThrow(InvalidCacheKey::class)->during('get', ['']);
        $this->shouldThrow(InvalidCacheKey::class)->during('set', [false, 'value']);
        $this->shouldThrow(InvalidCacheKey::class)->during('delete', [null]);
        $this->shouldThrow(InvalidCacheKey::class)->during('deleteMultiple', ['not_iterable']);
        $this->shouldThrow(InvalidCacheKey::class)->during('remember', [false, 10, null]);
    }

    function it_will_validate_keys_properly_before_usage()
    {
        $this->shouldThrow(InvalidCacheKey::class)->during('get', [false]);
        $this->shouldThrow(InvalidCacheKey::class)->during('get', ['']);
        $this->shouldThrow(InvalidCacheKey::class)->during('get', ['key with space']);
        $this->shouldThrow(InvalidCacheKey::class)->during('get', ["key\nwith\nnewline"]);
        $this->shouldThrow(InvalidCacheKey::class)->during('get', ["key\rwith\rnewline"]);
        $this->shouldThrow(InvalidCacheKey::class)->during('get', ["key\twith\ttabs"]);
    }
}
