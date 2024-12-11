<?php declare(strict_types=1);

namespace Everyware\ProjectPlugin\Components\Contracts;

/**
 * Interface InfoManager
 * @package Everyware\ProjectPlugin\Components\Contracts
 */
interface InfoManager
{
    /**
     * Retrieve metadata from a file.
     *
     * Searches for metadata in the first 8kiB of a file, such as a plugin, theme, Metabox or widget.
     * Each piece of metadata must be on its own line. Fields can not span multiple
     * lines, the value will get cut at the end of the first line.
     *
     * @param array $headers List of headers, in the format array('HeaderKey' => 'Header Name').
     *
     * @return array Array of file headers in `HeaderKey => Header Value` format.
     */
    public function extractHeaders(array $headers = []): array;

    /**
     * Retrieve one specific value
     *
     * @param $field
     *
     * @return string
     */
    public function getHeader($field): string;
}
