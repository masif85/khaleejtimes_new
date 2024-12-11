<?php declare(strict_types=1);

namespace Everyware\ProjectPlugin\Helpers;

use Infomaker\Everyware\Support\Str;

/**
 * Message
 *
 * @link    http://infomaker.se
 * @package Everyware\ProjectPlugin\Helpers
 * @since   Everyware\ProjectPlugin\Helpers\Message 1.0.0
 */
class Message
{

    public const ERROR = 'error';
    public const WARNING = 'warning';
    public const SUCCESS = 'success';
    public const INFO = 'info';

    /**
     * Available types of the message
     *
     * @var array
     */
    private static $types = [
        self::ERROR,
        self::WARNING,
        self::INFO,
        self::SUCCESS
    ];

    /**
     * A dismissible message will automatically trigger a closing icon to be added to your message via JavaScript
     *
     * @var bool
     */
    private $dismissible;

    /**
     * Message text
     *
     * @var string
     */
    private $message;

    /**
     * Message type
     *
     * @var string
     */
    private $type;

    /**
     * Message constructor.
     *
     * @param string $type One of valid types
     * @param string $message
     * @param bool   $dismissible
     */
    public function __construct(string $type, string $message, bool $dismissible = false)
    {

        // If type isn't valid
        if ( ! \in_array(strtolower($type), static::$types, true)) {
            $this->invalidTypeError($type, $message);
        } else {
            $this->message = $message;
            $this->type = strtolower($type);
            $this->dismissible = $dismissible;
        }
    }

    /**
     * Static function for creating messages and add it to the message bag
     *
     * @param string $type
     * @param string $message
     * @param bool   $dismissible
     *
     * @return self
     */
    public static function create(string $type, string $message = '', bool $dismissible = false): self
    {
        $instance = new static($type, $message, $dismissible);
        MessageBag::add($instance);

        return $instance;
    }

    /**
     * Generate html out of all messages
     *
     * @return string
     */
    public static function generateAll(): string
    {
        return MessageBag::messagesToHtml();
    }

    /**
     * Generate HTML out of the message
     *
     * @return string
     */
    public function toHtml(): string
    {
        return sprintf('<div class="%s"><p>%s</p></div>', $this->getMessageClasses(), $this->getMessage());
    }

    /**
     * Retrieve the message
     *
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Retrieve the message
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Generate a classes for
     *
     * @return string
     */
    private function getMessageClasses(): string
    {
        return implode(' ', array_filter([
            'notice',
            Str::append($this->type, 'notice', '-'),
            $this->dismissible ? 'is-dismissible' : ''
        ]));
    }

    /**
     * Create a fallback error message do explain
     *
     * @param string $type
     * @param string $message
     *
     * @return void
     */
    private function invalidTypeError(string $type, string $message): void
    {
        throw new \InvalidArgumentException(sprintf('Failed to create message: "%1$s" with type: "%2$s"', $message, $type));
    }
}
