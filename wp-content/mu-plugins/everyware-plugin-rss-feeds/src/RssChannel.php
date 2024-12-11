<?php declare(strict_types=1);


namespace Everyware\RssFeeds;


use Everyware\RssFeeds\Contracts\RssChannelInterface;
use Everyware\RssFeeds\Contracts\RssItemInterface;

/**
 * Class RssChannel
 * @package Everyware\RssFeeds
 */
class RssChannel implements RssChannelInterface
{
    /**
     * @var string
     */
    private $title;

    /**
     * @var string URL
     */
    private $link;

    /**
     * @var string
     */
    private $description;

    /**
     * @var array[]  View Items
     */
    private $items;

    /**
     * RssChannelInterface constructor.
     *
     * @param string $title
     * @param string $link URL
     * @param string $description
     */
    public function __construct(string $title, string $link, string $description)
    {
        $this->title = $title;
        $this->link = $link;
        $this->description = $description;
        $this->items = [];
    }

    /**
     * Add an item to the channel.
     *
     * @param RssItemInterface $item
     *
     * @see RssItem
     */
    public function addItem(RssItemInterface $item): void
    {
        $this->items[] = $item->getViewItems();
    }

    /**
     * @return array
     */
    public function getViewItems(): array
    {
        return [
            'title'       => $this->title,
            'link'        => $this->link,
            'description' => $this->description,
            'items'       => $this->items
        ];
    }
}
