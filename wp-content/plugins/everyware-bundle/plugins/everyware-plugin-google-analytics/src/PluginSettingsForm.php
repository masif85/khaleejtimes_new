<?php declare(strict_types=1);

namespace Everyware\Plugin\GoogleAnalytics;

use Everyware\ProjectPlugin\Components\ComponentSettingsForm;
use Everyware\ProjectPlugin\Components\Contracts\InfoManager;
use Infomaker\Everyware\Support\Str;
use Infomaker\Everyware\Twig\View;

/**
 * Class SettingsForm
 * @package Everyware\Plugin\GoogleAnalytics
 */
class PluginSettingsForm extends ComponentSettingsForm
{
    public function __construct(InfoManager $infoManager)
    {
        parent::__construct($infoManager);

        add_action(
            'admin_enqueue_scripts',
            function () {
                $assetsPath = plugin_dir_url(__FILE__).'assets';
                $scriptVersion = false;
                wp_enqueue_script(
                    'html-embedded-ads',
                    "{$assetsPath}/js/querytest.js",
                    ['jquery'],
                    $scriptVersion,
                    true
                );
            }
        );
        add_action('wp_ajax_test_ga_query', array($this, 'GaAjaxTest'));
    }
  
    protected function formContent($storedData): string
    {
        $viewData = array_replace($this->generateViewData($storedData), [
            'settings' => $this->getFormSettings(),
            'form' => $this->generateFormBuilder()
        ]);
        return View::generate('@plugins/plugin-settings', $viewData);
    }

    public function GaAjaxTest()
    {
        $settings = PluginSettings::create();

        $client = new GoogleAnalyticsClient($settings);

        echo json_encode([
            'data' => $client->getGaData()
        ]);
    
        exit;
    }
}
