<?php declare(strict_types=1);

namespace Everyware\ProjectPlugin\Components;

use Infomaker\Everyware\Support\Interfaces\Arrayable;

class SettingsField implements Arrayable
{
    /**
     * @var string
     */
    private $field;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @var string
     */
    private $fieldPrefix;

    /**
     * SettingsField constructor.
     *
     * @param string $field
     * @param string $value
     * @param string $prefix
     */
    public function __construct(string $field, $value = '', $prefix = '')
    {
        $this->field = $field;
        $this->value = $value;
        $this->fieldPrefix = $prefix;
    }

    public function name(): string
    {
        if (false === $pos = strpos($this->field, '[')) {
            return $this->fieldPrefix . "[{$this->field}]";
        }

        return $this->fieldPrefix . '[' . substr_replace($this->field, '][', $pos, \strlen('['));
    }

    public function value($escape = false)
    {
        return $escape ? $this->escape((string)$this->value) : $this->value;
    }

    public function id(): string
    {
        return trim(str_replace(['[]', '[', ']'], ['', '-', ''], $this->name()), '-');
    }

    private function escape($value, $doubleEncode = true)
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8', $doubleEncode);
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id(),
            'name' => $this->name(),
            'value' => $this->value()
        ];
    }
}
