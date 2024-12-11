<?php declare(strict_types=1);

namespace Everyware\Concepts\Contracts;

use Everyware\Concepts\ConceptPost;

/**
 * Interface ConceptEvent
 * @package Everyware\Concepts\Contracts
 */
interface ConceptEvent
{
    public function getUuid(): string;

    public function getPost(): ConceptPost;
}
