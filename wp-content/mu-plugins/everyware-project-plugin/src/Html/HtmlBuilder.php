<?php declare(strict_types=1);

namespace Everyware\ProjectPlugin\Html;

use Exception;
use InvalidArgumentException;
use Tightenco\Collect\Support\Traits\Macroable;
use Infomaker\Everyware\Support\NewRelicLog;

class HtmlBuilder
{
    use Macroable;

    /**
     * Build an HTML attribute string from an array.
     *
     * @param array $attributes
     *
     * @return string
     */
    public function attributes(array $attributes): string
    {
        $html = [];
        foreach ($attributes as $key => $value) {
            $element = $this->attributeElement($key, $value);
            if ($element !== null) {
                $html[] = $element;
            }
        }

        return count($html) > 0 ? ' ' . implode(' ', $html) : '';
    }

    /**
     * Convert entities to HTML characters.
     *
     * @param string $value
     *
     * @return string
     */
    public function decode($value): string
    {
        return html_entity_decode($value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Convert an HTML string to entities.
     *
     * @param string $value
     *
     * @return string
     */
    public function entities($value): string
    {
        return htmlentities($value, ENT_QUOTES, 'UTF-8', false);
    }

    /**
     * Obfuscate an e-mail address to prevent spam-bots from sniffing it.
     *
     * @param string $email
     *
     * @return string
     */
    public function email(string $email): string
    {
        return str_replace('@', '&#64;', $this->obfuscate($email));
    }

    /**
     * Generates non-breaking space entities based on number supplied.
     *
     * @param int $num
     *
     * @return string
     */
    public function nbsp(int $num = 1): string
    {
        return str_repeat('&nbsp;', $num);
    }

    /**
     * Obfuscate a string to prevent spam-bots from sniffing it.
     *
     * @param string $value
     *
     * @return string
     */
    public function obfuscate($value): string
    {
        $safe = '';
        foreach (str_split($value) as $letter) {
            if (ord($letter) > 128) {
                return $letter;
            }
            try {
                // To properly obfuscate the value, we will randomly convert each letter to
                // its entity or hexadecimal representation, keeping a bot from sniffing
                // the randomly obfuscated letters out of the string on the responses.
                switch (random_int(1, 3)) {
                    case 1:
                        $safe .= '&#' . ord($letter) . ';';
                        break;
                    case 2:
                        $safe .= '&#x' . dechex(ord($letter)) . ';';
                        break;
                    case 3:
                        $safe .= $letter;
                }

            } catch (Exception $e) {
                NewRelicLog::error('Failed to Obfuscate a string in: ' . static::class, $e);
            }
        }

        return $safe;
    }

    /**
     * Transform the string to an Html serializable object
     *
     * @param $html
     *
     * @return string
     */
    public function toHtmlString($html): string
    {
        if ( ! is_string($html)) {
            throw new InvalidArgumentException('Could not render none string');
        }

        return $html;
    }

    /**
     * Generate an html tag.
     *
     * @param string $tag
     * @param mixed  $content
     * @param array  $attributes
     *
     * @return string
     */
    public function tag($tag, $content, array $attributes = []): string
    {
        $content = is_array($content) ? implode('', $content) : $content;

        return $this->toHtmlString("<{$tag}" . $this->attributes($attributes) . '>' . $this->toHtmlString($content) . "</{$tag}>");
    }

    /**
     * Generate an html singleton tag.
     *
     * @param string $tag
     * @param array  $attributes
     *
     * @return string
     */
    public function singleTag($tag, array $attributes = []): string
    {
        return "<{$tag}" . $this->attributes($attributes) . '>';
    }

    /**
     * Generate a link to a JavaScript file.
     *
     * @param string $url
     * @param array  $attributes
     *
     * @param string $inlineScript
     *
     * @return string
     */
    public function script($url, array $attributes = [], $inlineScript = ''): string
    {
        if ( ! empty ($url)) {
            $attributes = $this->combineAttributes(['src' => $url], $attributes);
        }

        return $this->tag('script', $inlineScript, $attributes);
    }

    /**
     * Generate a link to a CSS file.
     *
     * @param string $url
     * @param array  $attributes
     *
     * @return string
     */
    public function style($url, array $attributes = []): string
    {
        $defaults = [
            'media' => 'all',
            'type' => 'text/css',
            'rel' => 'stylesheet',
            'href' => $url
        ];

        return $this->singleTag('link', $this->combineAttributes($defaults, $attributes));
    }

    /**
     * Generate an HTML image element.
     *
     * @param string $src
     * @param string $alt
     * @param array  $attributes
     *
     * @return string
     */
    public function image($src, $alt = null, array $attributes = []): string
    {
        return $this->singleTag('img', $this->combineAttributes(compact('src', 'alt'), $attributes));
    }

    /**
     * Generate a meta tag.
     *
     * @param string $name
     * @param string $content
     * @param array  $attributes
     *
     * @return string
     */
    public function meta($name, $content, array $attributes = []): string
    {
        return $this->singleTag('meta', $this->combineAttributes(compact('name', 'content'), $attributes));
    }

    /**
     * Generate a HTML link.
     *
     * @param string $url
     * @param string $title
     * @param array  $attributes
     * @param bool   $escape
     *
     * @return string
     */
    public function link($url, $title = null, array $attributes = [], $escape = true): string
    {
        $title = $title ?: $url;

        if ($escape) {
            $title = $this->entities($title);
        }

        return $this->tag('a', $title, $this->combineAttributes(['href' => $url], $attributes));
    }

    /**
     * Generate a HTML link to an email address.
     *
     * @param string $email
     * @param string $title
     * @param array  $attributes
     * @param bool   $escape
     *
     * @return string
     */
    public function mailto($email, $title = null, array $attributes = [], $escape = true): string
    {
        $email = $this->email($email);
        $title = $title ?: $email;

        if ($escape) {
            $title = $this->entities($title);
        }

        $email = $this->obfuscate('mailto:') . $email;

        return $this->link($email, $title, $attributes, $escape);
    }

    /**
     * Generate an ordered list of items.
     *
     * @param array $list
     * @param array $attributes
     *
     * @return string|string
     */
    public function ol(array $list, array $attributes = []): string
    {
        return $this->listing('ol', $list, $attributes);
    }

    /**
     * Generate an un-ordered list of items.
     *
     * @param array $list
     * @param array $attributes
     *
     * @return string|string
     */
    public function ul(array $list, array $attributes = []): string
    {
        return $this->listing('ul', $list, $attributes);
    }

    /**
     * Generate a description list of items.
     *
     * @param array $list
     * @param array $attributes
     *
     * @return string
     */
    public function dl(array $list, array $attributes = []): string
    {
        $listItems = '';
        foreach ($list as $key => $value) {
            $value = (array)$value;
            $listItems .= "<dt>$key</dt>";
            foreach ($value as $vKey => $vValue) {
                $listItems .= "<dd>$vValue</dd>";
            }
        }

        return $this->tag('dl', $listItems, $attributes);
    }

    /**
     * Create a select box field.
     *
     * @param iterable $list
     * @param bool     $selected
     * @param array    $attributes
     *
     * @return string
     */
    public function select(iterable $list, $selected = false, array $attributes = []): string
    {
        $listItems = '';
        foreach ($list as $option) {
            $option = (array)$option;
            $value = $option['value'] ?? '';
            $label = $option['text'] ?? '';

            $optionAttributes = compact('value');

            if ($this->selected($value, $selected)) {
                $optionAttributes['selected'] = 'selected';
            }

            $listItems .= $this->tag('option', $label ?? '', $optionAttributes);
        }

        return $this->tag('select', $listItems, $attributes);
    }

    /**
     * Build a single attribute element.
     *
     * @param mixed $key
     * @param mixed $value
     *
     * @return string
     */
    protected function attributeElement($key, $value): string
    {
        // For numeric keys we will assume that the value is a boolean attribute
        // where the presence of the attribute represents a true value and the
        // absence represents a false value.
        // This will convert HTML attributes such as "required" to a correct
        // form instead of using incorrect numerics.
        if (is_numeric($key) || $key === $value) {
            return $value;
        }

        // Treat boolean attributes as HTML properties
        if (is_bool($value) && $key !== 'value') {
            return $value ? $key : '';
        }

        if (is_array($value) && $key === 'class') {
            return 'class="' . implode(' ', $value) . '"';
        }

        return $value !== null ? $key . '="' . $value . '"' : '';
    }

    /**
     * Create a listing HTML element.
     *
     * @param string $type
     * @param array  $list
     * @param array  $attributes
     *
     * @return string|string
     */
    public function listing($type, $list, $attributes = []): string
    {
        $html = '';
        if (count($list) === 0) {
            return $html;
        }
        // Essentially we will just spin through the list and build the list of the HTML
        // elements from the array. We will also handled nested lists in case that is
        // present in the array. Then we will build out the final listing elements.
        foreach ($list as $key => $value) {
            $html .= $this->listingElement($key, $type, $value);
        }

        return $this->tag($type, $html, $attributes);
    }

    /**
     * Create the HTML for a listing element.
     *
     * @param mixed  $key
     * @param string $type
     * @param mixed  $value
     *
     * @return string
     */
    protected function listingElement($key, $type, $value): string
    {
        if (is_array($value)) {
            return $this->nestedListing($key, $type, $value);
        }

        return $this->tag('li', $value);
    }

    /**
     * Create the HTML for a nested listing attribute.
     *
     * @param mixed  $key
     * @param string $type
     * @param mixed  $value
     *
     * @return string
     */
    protected function nestedListing($key, $type, $value): string
    {
        if (is_int($key)) {
            return $this->listing($type, $value);
        }

        return $this->tag('li', $key . $this->listing($type, $value));
    }

    /**
     * @param array $array1
     * @param array $array2
     *
     * @return array
     */
    protected function combineAttributes(array $array1, array $array2): array
    {
        return array_filter(array_replace($array1, $array2));
    }

    /**
     * @param mixed $value
     * @param mixed $selected
     *
     * @return bool
     */
    protected function selected($value, $selected = false): bool
    {
        if ( ! $selected) {
            return false;
        }

        return (string)$value === (string)$selected;
    }
}
