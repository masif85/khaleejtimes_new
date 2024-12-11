<?php declare(strict_types=1);


namespace Everyware\Plugin\RedirectOriginalUrls;


use BadFunctionCallException;

/**
 * Class originalUrlsAnalysisItem
 *
 * Each instance is typically related to a setting in {@see PluginSettings} and a corresponding input in the settings form.
 *
 * @package Everyware\Plugin\RedirectOriginalUrls
 */
class OriginalUrlsAnalysisItem
{
    /**
     * Status used when a count is good.
     */
    public const STATUS_GOOD = 'good';

    /**
     * Status used when a count is zero, or not applicable.
     */
    public const STATUS_NEUTRAL = 'neutral';

    /**
     * Status used when a count is very low (but not zero), compared to a related count.
     * This indicates that it would be a good idea to correct the underlying data so it becomes zero.
     */
    public const STATUS_LOW = 'low';

    /**
     * Status used when a count is low (but not zero), compared to a related count, and only one non-zero count is allowed.
     */
    public const STATUS_INCOMPATIBLE = 'incompatible';

    /**
     * Status used when a count is lower than it should be.
     */
    public const STATUS_INSUFFICIENT = 'insufficient';

    /**
     * @var string Key in name of form input related to this setting.
     */
    private $key;

    /**
     * @var string|null HTML ID, when $key is not enough to identify the form input.
     */
    private $id;

    /**
     * @var int Number of hits that match this setting.
     */
    private $resultCount;

    /**
     * @var string|null URL to find a listing of the hits in $resultCount.
     */
    private $resultUrl;

    /**
     * @var string|null
     */
    private $resultText;

    /**
     * @var string|null One of the STATUS_* constants in this class, indicating if anything ought to be done about this result.
     */
    private $resultStatus;

    /**
     * @var mixed|null Recommended value to set to the form input.
     */
    private $recommendedValue;

    /**
     * OriginalUrlsAnalysisItem constructor.
     *
     * @param string      $key
     * @param string|null $id
     */
    public function __construct(string $key, string $id = null)
    {
        $this->key = $key;
        $this->id = $id;
    }

    public function setResultCount(int $count): void
    {
        $this->resultCount = $count;
    }

    public function getResultCount(): ?int
    {
        return $this->resultCount;
    }

    public function setResultUrl(string $url): void
    {
        $this->resultUrl = $url;
    }

    public function setResultStatus(string $status): void
    {
        $this->resultStatus = $status;
    }

    public function setResultText(string $text): void
    {
        $this->resultText = $text;
    }

    /**
     * @param mixed $value
     */
    public function setRecommendedValue($value): void
    {
        $this->recommendedValue = $value;
    }

    /**
     * @param $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        if (isset($this->{$name})) {
            return $this->{$name};
        }

        throw new BadFunctionCallException('Unknown property `' . $name . '`');
    }

    public function toArray(): array
    {
        return [
            'key'              => $this->key,
            'id'               => $this->id,
            'resultText'       => $this->getResultText(),
            'resultStatus'     => $this->resultStatus,
            'recommendedValue' => $this->recommendedValue
        ];
    }

    public static function resultLink(int $count, string $resultUrl = null): string
    {
        $result = '<strong>' . sprintf(__('%d articles', REDIRECT_ORIGINAL_URLS_TEXT_DOMAIN), $count) . '</strong>';

        if ($resultUrl !== null) {
            $result .= ' <a href="' . htmlentities($resultUrl) . '" target="_blank">' . __('View data', REDIRECT_ORIGINAL_URLS_TEXT_DOMAIN) . '</a>';
        }

        return $result;
    }

    private function getResultText(): string
    {
        if ($this->resultText !== null) {
            return $this->resultText;
        }
        if ($this->resultCount !== null) {
            return self::resultLink($this->resultCount, $this->resultUrl);
        }

        // This should never happen.
        return __('No result.', REDIRECT_ORIGINAL_URLS_TEXT_DOMAIN);
    }
}
