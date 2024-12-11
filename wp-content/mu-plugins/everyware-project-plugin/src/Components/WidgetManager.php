<?php declare(strict_types=1);

namespace Everyware\ProjectPlugin\Components;

use Everyware\ProjectPlugin\Components\Adapters\WidgetAdapter;
use Everyware\ProjectPlugin\Wordpress\Contracts\WpWidget;

/**
 * Class WidgetManager
 * @package Everyware\ProjectPlugin\Components
 */
class WidgetManager
{
    protected $widgets = [];
    /**
     * @var bool
     */
    private $widgetsAdded = false;

    /**
     * @var self
     */
    private static $instance;

    private function __construct()
    {
        add_action('widgets_init', [$this, 'registerAll']);
    }

    public function registerAll(): void
    {
        if ( ! $this->widgetsAdded) {
            foreach ($this->widgets as $widgetClass) {
                register_widget($widgetClass);
            }
        }

        $this->widgetsAdded = true;
    }

    public function addWidget($widgetClass): void
    {
        $this->widgets[] = $widgetClass;
    }

    protected static function manager(): WidgetManager
    {
        if( static::$instance === null ) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    public static function register(string $widgetClass): void
    {
        static::manager()->addWidget($widgetClass);
    }
}
