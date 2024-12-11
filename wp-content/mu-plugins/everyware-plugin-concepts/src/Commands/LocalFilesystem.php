<?php declare(strict_types=1);

namespace Everyware\Concepts\Commands;

use Symfony\Component\Filesystem\Filesystem;

/**
 * Class LocalFilesystem
 * @package Everyware\Concepts\Commands
 */
class LocalFilesystem extends Filesystem
{
    public function getFileContent(string $filename)
    {
        return file_get_contents($filename);
    }
}
