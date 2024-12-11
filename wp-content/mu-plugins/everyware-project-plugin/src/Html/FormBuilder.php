<?php declare(strict_types=1);

namespace Everyware\ProjectPlugin\Html;

use Tightenco\Collect\Support\Traits\Macroable;
use Everyware\ProjectPlugin\Components\SettingsField;
use Infomaker\Everyware\Support\Date;

class FormBuilder
{
    use Macroable;

    /**
     * Contains the icon element that will present the tooltip
     *
     * @var string
     */
    private static $defaultTooltipIcon = '<i class="fa fa-question"></i>';

    /**
     * @var HtmlBuilder
     */
    private $html;

    public function __construct(HtmlBuilder $html)
    {
        $this->html = $html;
    }

    /**
     * Open up a new HTML form.
     *
     * @param array $options
     *
     * @return string
     */
    public function open(array $options = []): string
    {
        $attributes['method'] = $this->getMethod($options['method'] ?? 'post');
        if (isset($options['action'])) {
            $attributes['action'] = $options['action'];
        }

        return $this->html->singleTag('form', $attributes);
    }

    /**
     * Close the current form.
     *
     * @return string
     */
    public function close(): string
    {
        return $this->html->toHtmlString('</form>');
    }

    /**
     * Create a form label element.
     *
     * @param SettingsField $data
     * @param string        $value
     * @param array         $attributes
     * @param bool          $escape
     *
     * @return string
     */
    public function label(SettingsField $data, $value = '', array $attributes = [], $escape = false): string
    {
        $for = $attributes['for'] ?? $data->id();

        if ($escape) {
            $value = $this->html->entities($value);
        }

        return $this->html->tag('label', $value, $this->combineAttributes(['for' => $for], $attributes));
    }

    /**
     * Create a form input field.
     *
     * @param string        $type
     * @param SettingsField $data
     * @param array         $options
     *
     * @return string
     */
    public function input(string $type, SettingsField $data, array $options = []): string
    {
        $id = $data->id();
        $name = $data->name();
        $value = $data->value(true);

        $defaults = compact('type', 'id', 'name', 'value');

        return $this->html->singleTag('input', $this->combineAttributes($defaults, $options));
    }

    /**
     * Create a textarea input field.
     *
     * @param SettingsField $data
     * @param array         $options
     *
     * @return string
     */
    public function textarea(SettingsField $data, array $options = []): string
    {
        $defaults = [
            'name' => $data->name(),
            'id' => $data->id()
        ];

        $options = $this->setTextAreaSize($options);

        $value = (string)$data->value(true);
        unset($options['size']);

        return $this->html->tag('textarea', $value, $this->combineAttributes($defaults, $options));
    }

    /**
     * Create a button element.
     *
     * @param string|null $value
     * @param array       $options
     *
     * @return string
     */
    public function button(string $value = null, array $options = []): string
    {
        return $this->html->tag('button', $value, $this->combineAttributes(['type' => 'button'], $options));
    }

    /**
     * Create a select box element
     *
     * @param SettingsField $data
     * @param iterable      $list
     * @param array         $options
     *
     * @return string
     */
    public function select(SettingsField $data, iterable $list, array $options = []): string
    {
        if (isset($options['placeholder'])) {
            $list = array_merge([
                ['value' => '', 'text' => $options['placeholder']]
            ], $list);
            unset($options['placeholder']);
        }

        return $this->html->select($list, $data->value(), $this->combineAttributes([
            'id' => $data->id(),
            'name' => $data->name()
        ], $options));
    }

    // Helpers
    // ======================================================

    /**
     * Create a checkbox input field.
     *
     * @param SettingsField $data
     * @param array         $options
     *
     * @return string
     */
    public function checkbox(SettingsField $data, array $options = []): string
    {
        return $this->input('checkbox', $data, $this->combineAttributes([
            'value' => 'on',
            'checked' => filter_var($data->value(), FILTER_VALIDATE_BOOL) ? 'checked' : ''
        ], $options));
    }

    /**
     * Create a date input field.
     *
     * @param SettingsField $data
     * @param array         $options
     *
     * @return string
     */
    public function date(SettingsField $data, array $options = []): string
    {
        $value = Date::parse($data->value());

        if ($value instanceof Date) {
            $options['value'] = $value->format('Y-m-d');
        }

        return $this->input('date', $data, $options);
    }

    /**
     * Create a datetime input field.
     *
     * @param SettingsField $data
     * @param array         $options
     *
     * @return string
     */
    public function datetime(SettingsField $data, array $options = []): string
    {
        $value = Date::parse($data->value());

        if ($value instanceof Date) {
            $options['value'] = $value->format(Date::RFC3339);
        }

        return $this->input('datetime', $data, $options);
    }

    /**
     * Create an e-mail input field.
     *
     * @param SettingsField $data
     * @param array         $options
     *
     * @return string
     */
    public function email(SettingsField $data, array $options = []): string
    {
        return $this->input('email', $data, $options);
    }

    /**
     * Create a hidden input field.
     *
     * @param SettingsField $data
     * @param array         $options
     *
     * @return string
     */
    public function hidden(SettingsField $data, array $options = []): string
    {
        return $this->input('hidden', $data, $options);
    }

    /**
     * Create a number input field.
     *
     * @param SettingsField $data
     * @param array         $options
     *
     * @return string
     */
    public function number(SettingsField $data, array $options = []): string
    {
        return $this->input('number', $data, $options);
    }

    /**
     * Create a password input field.
     *
     * @param SettingsField $data
     * @param array         $options
     *
     * @return string
     */
    public function password(SettingsField $data, array $options = []): string
    {
        return $this->input('password', $data, $options);
    }

    /**
     * Create a radio input field.
     *
     * @param SettingsField $data
     * @param string        $value
     * @param array         $options
     *
     * @return string
     */
    public function radio(SettingsField $data, string $value, array $options = []): string
    {
        return $this->html->singleTag('input', $this->combineAttributes([
            'type' => 'radio',
            'value' => $value,
            'name' => $data->name(),
            'checked' => $this->isSelected($data->value(), $value) ? 'checked' : ''
        ], $options));
    }

    /**
     * Create a range input field.
     *
     * @param SettingsField $data
     * @param array         $options
     *
     * @return string
     */
    public function range(SettingsField $data, array $options = []): string
    {
        return $this->input('range', $data, $options);
    }

    /**
     * Create a search input field.
     *
     * @param SettingsField $data
     * @param array         $options
     *
     * @return string
     */
    public function search(SettingsField $data, array $options = []): string
    {
        return $this->input('search', $data, $options);
    }

    /**
     * Create a tel input field.
     *
     * @param SettingsField $data
     * @param array         $options
     *
     * @return string
     */
    public function tel(SettingsField $data, array $options = []): string
    {
        return $this->input('tel', $data, $options);
    }

    /**
     * Create a text input field.
     *
     * @param SettingsField $data
     * @param array         $options
     *
     * @return string
     */
    public function text(SettingsField $data, array $options = []): string
    {
        return $this->input('text', $data, $options);
    }

    /**
     * Create a url input field.
     *
     * @param SettingsField $data
     * @param array         $options
     *
     * @return string
     */
    public function url(SettingsField $data, array $options = []): string
    {
        return $this->input('url', $data, $options);
    }

    // Special Elements
    // ======================================================
    /**
     * @param SettingsField $data
     * @param string        $onLabel
     * @param string        $offLabel
     * @param array         $options
     *
     * @return string
     */
    public function toggleButton(SettingsField $data, $onLabel = 'on', $offLabel = 'off', array $options = []): string
    {
        // Add off label
        $toggleOutput = $this->label($data, $offLabel, ['class' => 'checkbox-toggle-not-checked']);

        // Add toggle wit label and checkbox
        $toggleOutput .= '<div class="checkbox-toggle">' . $this->checkbox($data,
                ['class' => 'checkbox']) . $this->label($data) . '</div>';

        // Add on label
        $toggleOutput .= $this->label($data, $onLabel, ['class' => 'checkbox-toggle-checked']);

        return $this->html->tag('div', $toggleOutput, $this->combineAttributes([
            'class' => 'checkbox-toggle-wrapper'
        ], $options));
    }

    /**
     * Create a tooltip button based on the Everyware's front-end component library
     *
     * @param string      $text
     * @param string      $position
     *
     * @param string|null $icon
     *
     * @return string
     */
    public function tooltip(string $text, $position = 'left', string $icon = null): string
    {
        $attributes = [
            'class' => 'ew-tooltip',
            'data-placement' => $position,
            'data-toggle' => 'tooltip',
            'title' => $text
        ];

        // Check if text contains HTML
        if ($text !== strip_tags($text)) {
            $attributes['data-html'] = 'true';
        }

        return $this->button($icon ?? static::$defaultTooltipIcon, $attributes);
    }

    /**
     * @param mixed $value
     * @param mixed $selected
     *
     * @return bool
     */
    public function isSelected($value, $selected = false): bool
    {
        if ( ! $selected) {
            return false;
        }

        return (string)$value === (string)$selected;
    }

    /**
     * Combine two sets of attributes and remove empty attributes with values
     *
     * @param array $array1
     * @param array $array2
     *
     * @return array
     */
    protected function combineAttributes(array $array1, array $array2): array
    {
        return array_filter(array_replace($array1, $array2), static function ($attribute) {
            return is_array($attribute) ? ! empty($attribute) : strlen((string)$attribute);
        });
    }

    /**
     * Parse the form action method.
     *
     * @param string $method
     *
     * @return string
     */
    protected function getMethod(string $method): string
    {
        $method = strtoupper($method);

        return $method !== 'GET' ? 'POST' : $method;
    }

    /**
     * Set the text area size on the attributes.
     *
     * @param array $options
     *
     * @return array
     */
    protected function setTextAreaSize(array $options): array
    {
        $cols = $options['cols'] ?? 40;
        $rows = $options['rows'] ?? 3;

        if (isset($options['size'])) {
            [$rows, $cols] = explode('x', $options['size']);
        }

        return array_merge($options, compact('cols', 'rows'));
    }
}
