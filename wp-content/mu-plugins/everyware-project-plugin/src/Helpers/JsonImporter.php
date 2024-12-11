<?php declare(strict_types=1);

namespace Everyware\ProjectPlugin\Helpers;

use Infomaker\Everyware\Support\Str;

/**
 * JsonImporter
 *
 * @link    http://infomaker.se
 * @package Infomaker\Everyware\Handler
 * @since   Everyware\ProjectPlugin\Helpers\JsonImporter 1.0.0
 */
class JsonImporter
{
    public const POST_KEY = 'imported-data';

    /**
     * @var string
     */
    protected $submitName;

    public function __construct(string $submitName = '')
    {
        $this->submitName = Str::notEmpty($submitName) ? Str::slug($submitName) : self::POST_KEY;
    }

    public function isImported(): bool
    {
        return isset($_POST[$this->submitName]);
    }

    public function getJson(): array
    {
        if ($this->isImported()) {
            $data = json_decode($this->getRawJson(), true);

            return $data !== null ? array_filter($data) : [];
        }

        return [];
    }

    public function getRawJson(): string
    {
        if ($this->isImported()) {
            return stripslashes($_POST[$this->submitName]);
        }

        return '';
    }
}
