<?php declare(strict_types=1);

namespace Everyware\Concepts;

use Everyware\Concepts\Contracts\Concept;
use Infomaker\Everyware\Base\Models\Post;

/**
 * Class ConceptPost
 * @property string post_type
 * @package Everyware\Concepts
 */
class ConceptPost extends Post
{
    /**
     * @var string
     */
    protected static $type = 'concept';

}
