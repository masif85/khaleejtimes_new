<?php declare(strict_types=1);

namespace Everyware\ProjectPlugin\Helpers;

use Everyware\ProjectPlugin\Components\Contracts\InfoManager;

class FileReader implements InfoManager
{
    /**
     * @var array
     */
    private static $fileData = [];

    /**
     * @var string
     */
    private $file;

    public function __construct(string $file)
    {
        if ( ! file_exists($file)) {
            throw new \InvalidArgumentException("File {$file} dose not exist.");
        }

        $this->file = $file;
    }

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
    public function extractHeaders(array $headers = []): array
    {
        $fileData = $this->extractHeaderData($this->file);

        foreach ($headers as $field => $regex) {
            $headers[$field] = $this->findHeaderByName($regex, $fileData);
        }

        return $headers;
    }

    public function getHeader($field): string
    {
        $headers = $this->extractHeaders(['header' => $field]);

        return $headers['header'] ?? '';
    }

    protected function findHeaderByName(string $name, string $fileData): string
    {
        if (preg_match('/^[ \t\/*#@]*' . preg_quote($name, '/') . ':(.*)$/mi', $fileData, $match) && $match[1]) {
            return trim(preg_replace("/\s*(?:\*\/|\?>).*/", '', $match[1]));
        }

        return '';
    }

    protected function extractHeaderData(string $file): string
    {
        if (isset(static::$fileData[$file])) {
            return static::$fileData[$file];
        }

        // We don't need to write to the file, so just open for reading.
        $fp = fopen($file, 'rb');

        // Pull only the first 8kiB of the file in.
        $fileData = fread($fp, 8192);

        // PHP will close file handle, but we are good citizens.
        fclose($fp);

        // Make sure we catch CR-only line endings.
        return static::$fileData[$file] = str_replace("\r", "\n", $fileData);
    }
}
