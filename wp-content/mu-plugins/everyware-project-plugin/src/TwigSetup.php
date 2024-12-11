<?php

namespace Everyware\ProjectPlugin;

use Infomaker\Everyware\Twig\ViewSetup;
use Infomaker\Everyware\Twig\FilesystemLoader;
use Infomaker\Everyware\Twig\EveryTwig;
use Twig\Extension\ExtensionInterface;

class TwigSetup
{
    public static function addExtension(ExtensionInterface $extension): void
    {
        ViewSetup::getInstance()->addExtension($extension);
    }

    public static function addFilter(string $name, $callable = null, array $options = []): void
    {
        ViewSetup::getInstance()->addFilter($name, $callable, $options);
    }

    public static function addFunction(string $name, $callable = null, array $options = []): void
    {
        ViewSetup::getInstance()->addFunction($name, $callable, $options);
    }

    public static function addGlobal(string $key, $value): void
    {
        ViewSetup::getInstance()->addGlobal($key, $value);
    }

    public static function addWpFunction(string $functionName, string $name = null): void
    {
        ViewSetup::getInstance()->addWpFunction($functionName, $name);
    }

    public static function registerTwigFolder(string $namespace, string $path): void
    {
        ViewSetup::getInstance()->registerTwigFolder($namespace, $path);
    }
}
