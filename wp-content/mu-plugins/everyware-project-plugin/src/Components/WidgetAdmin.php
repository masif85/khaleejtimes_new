<?php declare(strict_types=1);

namespace Everyware\ProjectPlugin\Components;

use Everyware\ProjectPlugin\Components\Adapters\WidgetAdapter;
use Everyware\ProjectPlugin\Components\Contracts\WidgetComponent;

class WidgetAdmin extends ComponentAdmin implements WidgetComponent
{
    /**
     * @param array $storedData
     *
     * @return string The settings form.
     */
    public function edit(array $storedData = []): string
    {
        return parent::edit($this->addRequiredFields($storedData));
    }

    /**
     * @param array $storedData
     *
     * @return string The settings form.
     */
    public function create(array $storedData = []): string
    {
        return parent::create($this->addRequiredFields($storedData));
    }

    /**
     * @param int|string $number
     */
    public function setWidgetInstanceNumber($number): void
    {
        $this->repository->addRequiredField('instance_id', $number);
    }

    private function addRequiredFields($storedData)
    {
        // Add required fields from Everyboard
        $this->repository->addRequiredField('board_widget_name', '');
        $this->repository->addRequiredField('board_widget_tags', '');

        // Add title unless it's being used as a field from the widget
        $this->repository->addRequiredField('title', $storedData['board_widget_name'] ?? '');

        return $storedData;
    }
}
