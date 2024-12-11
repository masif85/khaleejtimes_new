<?php declare(strict_types=1);

namespace Everyware\RssFeeds\Contracts;

/**
 * Interface RssFeedInterface
 * @package Everyware\RssFeeds\Contracts
 */
interface RssFeedInterface
{
    /**
     * @return array
     */
    public function getFeedSettings(): array;

    /**
     * @return array
     */
    public function getItemSettings(): array;

    /**
     * @return string
     */
    public function getTitle(): string;

    /**
     * @return string
     */
    public function getLink(): string;

    /**
     * @return string
     */
    public function getDescription(): string;

    /**
     * Add a channel to the feed.
     *
     * @param RssChannelInterface $channel
     */
    public function addChannel(RssChannelInterface $channel): void;

    /**
     * Load Open Content articles to present in this feed.
     *
     * @return array
     */
    public function getOcArticles(): array;

    /**
     * Export feed as XML.
     *
     * @return string
     */
    public function toXml(): string;
}
