<?php declare(strict_types=1);

namespace Everyware\Plugin\ContentSync\Wordpress;

class WpNetworkOptions
{
    public function get(string $key, $default = null)
    {
        return get_network_option(null, $key, $default);
    }

    public function delete(string $key): bool
    {
        return delete_network_option(null, $key);
    }

    public function update(string $key, $value): bool
    {
        return update_network_option(null, $key, $value);
    }
}
