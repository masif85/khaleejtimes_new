<?php declare(strict_types=1);

namespace Everyware\ProjectPlugin\Components\Adapters;

use Everyware\ProjectPlugin\Wordpress\Contracts\EveryboardWidget;
use Everyware\ProjectPlugin\Wordpress\Contracts\WpWidget;
use Everyware\ProjectPlugin\Components\Contracts\Admin;
use Everyware\ProjectPlugin\Components\Contracts\WidgetComponent;
use Infomaker\Everyware\Support\Str;
use WP_Widget;

abstract class WidgetAdapter extends WP_Widget implements WpWidget, EveryboardWidget
{
    /**
     * Optional. Widget options.
     * @see wp_register_sidebar_widget() for information on accepted arguments.
     * @var array
     */
    protected $widgetOptions = [];

    /**
     * Optional. Widget control options.
     * @see wp_register_widget_control() for information on accepted arguments.
     * @var array
     */
    protected $controlOptions = [];

    /**
     * @var Admin
     */
    private $component;

    public function __construct()
    {
        $this->component = $this->widgetSetup();

        $widgetId = $this->getWidgetId();

        $widgetOptions = array_replace_recursive([
            'classname' => $widgetId,
            'description' => $this->component->getDescription()
        ], $this->widgetOptions);

        parent::__construct($widgetId, $this->component->getName(), $widgetOptions, $this->controlOptions);
    }

    /**
     * @param array $instance
     *
     * @return null|string
     */
    public function form($instance): ?string
    {
        $this->renderForm($this->updateData($instance));

        return null;
    }

    /**
     * @param array $new_instance
     * @param array $old_instance
     *
     * @return array|bool|mixed
     */
    public function update($new_instance, $old_instance)
    {
        if ($this->saveSettings($new_instance, $old_instance)) {
            return $this->component->getSettings();
        }

        return false;
    }

    /**
     * @param array $args
     * @param array $instance
     */
    public function widget($args, $instance): void
    {
        echo $this->generateWidget(array_replace($this->component->getSettings(), $instance), $args);
    }

    /**
     * @return string
     */
    public function getWidgetId(): string
    {
        return Str::replaceFirst('widget-', '', $this->component->getInputPrefix());
    }

    /**
     * Function to render widget content in EveryBoard.
     *
     * @param $data
     *
     * @return void
     */
    public function widget_board($data): void
    {
        echo $this->generateBoardContent($data);
    }

    /**
     * @param array $data
     *
     * @return string
     */
    protected function generateBoardContent(array $data): string
    {
        return '<p>' . $this->component->getDescription() . '</p>';
    }

    /**
     * @param $settings
     *
     * @return array
     */
    protected function getWidgetSettings( array $settings = []): array
    {
        return array_replace([
            'name' => $this->component->getName(),
            'description' => $this->component->getDescription(),
            'class_prefix' => $this->getWidgetId(),
            'id_prefix' => $this->getWidgetId()
        ], $settings);
    }

    /**
     * @param $storedData
     */
    private function renderForm($storedData): void
    {
        echo empty($storedData) ? $this->component->create($storedData) : $this->component->edit($storedData);
    }

    /**
     * @param $newData
     * @param $storedData
     *
     * @return bool
     */
    private function saveSettings($newData, $storedData): bool
    {
        if (empty($storedData)) {
            return $this->component->store($newData, $storedData);
        }

        return $this->component->update($newData, $storedData);
    }

    /**
     * @param $storedData
     *
     * @return array
     */
    private function updateData($storedData): array
    {
        if ($this->component instanceof WidgetComponent) {
            $this->component->setWidgetInstanceNumber($this->number);
        }

        $this->saveSettings($storedData, []);

        return $this->component->getSettings();
    }

    /**
     * @return Admin
     */
    abstract protected function widgetSetup(): Admin;

    /**
     * @param array $viewData
     * @param array $args
     *
     * @return string
     */
    abstract protected function generateWidget(array $viewData, array $args): string;
}
