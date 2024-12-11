<?php declare(strict_types=1);


namespace Everyware\RssFeeds\Contracts;


/**
 * Interface RssChannelInterface
 * @package Everyware\RssFeeds\Contracts
 */
interface RssChannelInterface
{
    /**
     * RssChannelInterface constructor.
     *
     * @param string $title
     * @param string $link URL
     * @param string $description
     */
    public function __construct(string $title, string $link, string $description);

    /**
     * Add an item to the channel.
     *
     * @param RssItemInterface $item
     *
     * @see RssItem
     */
    public function addItem(RssItemInterface $item): void;

    /**
     * @return array
     */
    public function getViewItems(): array;
}
