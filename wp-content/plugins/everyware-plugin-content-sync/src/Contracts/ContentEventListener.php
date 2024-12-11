<?php declare(strict_types=1);

namespace Everyware\Plugin\ContentSync\Contracts;

interface ContentEventListener
{
    public function handle(ContentEvent $event): void;
}
