<?php declare(strict_types=1);

namespace Everyware\Plugin\ContentSync\Wordpress\Contracts;

interface WpAdminPage
{
    public function pageTitle(): string;

    public function menuTitle(): string;

    public function getContent(): string;
}
