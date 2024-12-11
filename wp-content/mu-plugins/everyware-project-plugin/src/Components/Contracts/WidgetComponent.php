<?php declare(strict_types=1);

namespace Everyware\ProjectPlugin\Components\Contracts;

interface WidgetComponent
{
    /**
     * @param int|string $number
     */
    public function setWidgetInstanceNumber($number): void;
}
