<?php declare(strict_types=1);

namespace Spec;

class DocumentFileLoader
{
    private static $loadedFiles = [];

    public static function loadDocumentJson($filename)
    {
        if ( ! array_key_exists($filename, static::$loadedFiles)) {
            $documentPath = __DIR__ . '/documents/';
            static::$loadedFiles[$filename] = file_get_contents("{$documentPath}/{$filename}");
        }

        return static::$loadedFiles[$filename];
    }
}
