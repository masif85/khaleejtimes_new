<?php declare(strict_types=1);

namespace Everyware\ProjectPlugin;

use Infomaker\Everyware\Support\Str;
use Everyware\ProjectPlugin\Interfaces\PluginConfigInterface;
use RuntimeException;
use function env;

class Config implements PluginConfigInterface
{
    /** @var string */
    protected $name;

    /** @var string */
    protected $slug;

    /** @var string */
    protected $path;

    public function __construct(string $path)
    {
        if (env('APP_ORGANISATION') === null) {
            throw new RuntimeException('No organisation name provided in .env');
        }

        $this->name = env('APP_ORGANISATION');
        $this->slug = Str::slug($path);
        $this->path = $path;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getPath(string $file = null): string
    {
        return $file ? Str::append($file, $this->path, '/') : $this->path;
    }
}
