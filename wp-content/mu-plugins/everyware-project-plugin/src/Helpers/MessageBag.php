<?php declare(strict_types=1);

namespace Everyware\ProjectPlugin\Helpers;

use Infomaker\Everyware\Support\Collection;

/**
 * MessageBag
 *
 * @link    http://infomaker.se
 * @package Everyware\ProjectPlugin\Helpers
 * @since   Everyware\ProjectPlugin\Helpers\MessageBag 1.0.0
 */
class MessageBag
{

    /**
     * Contains all added messages
     *
     * @var Collection
     */
    private $messages;

    /**
     * Instance of self
     *
     * @var self
     */
    private static $instance;

    private function __construct()
    {
        $this->messages = new Collection();
    }

    private function addMessage(Message $message): void
    {
        $this->messages->push($message);
    }

    public function getMessages(): Collection
    {
        return $this->messages;
    }

    private static function getInstance(): MessageBag
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * @param Message $message
     *
     * @return void
     */
    public static function add(Message $message): void
    {
        static::getInstance()->addMessage($message);
    }

    public static function messagesToHtml()
    {
        return implode('', static::getInstance()->getMessages()->map(function (Message $message) {
            return $message->toHtml();
        })->toArray());
    }

    public static function renderMessages(): void
    {
        echo static::messagesToHtml();
    }
}
