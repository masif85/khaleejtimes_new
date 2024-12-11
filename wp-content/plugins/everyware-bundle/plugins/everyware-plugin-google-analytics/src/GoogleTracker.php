<?php declare(strict_types=1);

namespace Everyware\Plugin\GoogleAnalytics;

use Infomaker\Everyware\Support\Str;
use Infomaker\Everyware\Twig\View;

/**
 * Class GoogleTracker
 * @package Everyware\Plugin\GoogleAnalytics
 */
class GoogleTracker
{
    /**
     * Twig-template to use
     *
     * @var string
     */
    protected $templatePath = '@plugins/analytics';

    /**
     * @var PluginSettings
     */
    private $settings;

    public function __construct(PluginSettings $settings)
    {
        $this->settings = $settings;
        $this->addActions();
    }

    private function addActions(): void
    {
        add_action('wp_head', [$this, 'addTrackingScripts']);
    }

    public function addTrackingScripts(): void
    {
        $measurement_id = $this->settings->getMeasurementId();

        if (Str::notEmpty($measurement_id)) {
            View::render($this->templatePath, ['measurement_id' => $measurement_id]);
        }
    }
}
