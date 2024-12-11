<?php declare(strict_types=1);

namespace Everyware\Concepts\Events;

use Everyware\Concepts\ConceptPost;
use Everyware\Concepts\Contracts\ConceptEvent;

/**
 * Class ConceptApiEvent
 * @package Everyware\Concepts\Events
 */
class ConceptApiEvent implements ConceptEvent
{
    /**
     * @var string
     */
    private $uuid;

    /**
     * @var ConceptPost
     */
    private $post;

    public function __construct(string $uuid, ConceptPost $post)
    {
        $this->uuid = $uuid;
        $this->post = $post;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getPost(): ConceptPost
    {
        return $this->post;
    }
}
