<?php declare(strict_types=1);


namespace Unit\Everyware\RssFeeds;

use OcArticle;
use OcObject;

/**
 * Class FakeOcArticle
 *
 * Imitates parts of {@see OcArticle}, but can be populated with data without loading it from an OC source.
 *
 * @package Unit\Everyware\RssFeeds
 */
class FakeOcArticle extends OcObject
{
    /**
     * FakeOcArticle constructor.
     *
     * @param array $props
     */
    public function __construct(array $props)
    {
        $this->props = $props;
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function get_value(string $key)
    {
        return $this->props[$key] ?? '';
    }

    /**
     * @return string
     */
    public function get_permalink(): string
    {
        return 'https://example.com/article/'.$this->get_value('uuid');
    }

    /**
     * @var array
     */
    private $props;
}
