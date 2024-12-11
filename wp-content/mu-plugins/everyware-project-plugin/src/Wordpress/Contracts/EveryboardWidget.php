<?php declare(strict_types=1);


namespace Everyware\ProjectPlugin\Wordpress\Contracts;

/**
 * Interface EveryboardWidget
 *
 * @package Everyware\ProjectPlugin\Wordpress\Contracts
 */
interface EveryboardWidget
{
    /**
     * Function to render widget content in EveryBoard.
     *
     * @param $data
     *
     * @return void
     */
    public function widget_board($data): void;
}
