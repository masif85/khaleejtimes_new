<?php declare(strict_types=1);


namespace Everyware\RssFeeds\Contracts;


/**
 * Interface RssItemInterface
 * @package Everyware\RssFeeds\Contracts
 */
interface RssItemInterface
{
    /**
     * @return array
     */
    public function getViewItems(): array;
}
