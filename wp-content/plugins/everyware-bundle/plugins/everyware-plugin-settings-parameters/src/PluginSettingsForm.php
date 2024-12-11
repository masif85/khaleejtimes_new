<?php declare (strict_types=1);

namespace Everyware\Plugin\SettingsParameters;

use Everyware\ProjectPlugin\Components\ComponentSettingsForm;
use Infomaker\Everyware\Twig\View;
use Everyware\ProjectPlugin\Helpers\JsonImporter;

/**
 * Handles the plugin presentation
 */
class PluginSettingsForm extends ComponentSettingsForm
{
    /** @var string */
    private const SUBMIT_KEY = 'everyware-settings-parameters-submit';

    /** @var string */
    private $identifier = 'everyware-settings-parameter';

    /** @var string */
    protected $textDomain = 'everyware_project_plugin_text_domain';

    /**
     * Generates the body template with the neccessary data
     *
     * @param [mixed] $storedData
     *
     * @return string
     */
    protected function formContent($storedData): string
    {
        $viewData = $this->generateViewData($storedData);
        $parameters = $viewData['parameters']->value();

        return View::generate('@plugins/body', [
            'body' => $this->pageContent($parameters),
        ]);
    }

    /**
     * Generates all the data the view needs
     *
     * @param array $parameters
     *
     * @return string
     */
    private function pageContent($parameters = []): string
    {
        return View::generate('@plugins/settings-parameters', [
            'form_action' => $_SERVER['REQUEST_URI'],
            'submit_key' => static::SUBMIT_KEY,
            'text_domain' => $this->textDomain,
            'template' => [
                'key' => '',
                'value' => '',
                'label_key' => 'Key',
                'label_value' => 'Value',
            ],
            'parameters' => $this->generateParameters($parameters),
            'json_import' => $this->generateJsonImporter($parameters),
        ]);
    }

    /**
     * Generates the json import-export view. Creates an export array from the saved parameters.
     *
     * @param array $export
     *
     * @return string
     */
    private function generateJsonImporter(array $export = []): string
    {
        return View::generate('@plugins/import-export', [
            'field_name' => JsonImporter::POST_KEY,
            'import' => '',
            'export' => $export ? json_encode(array_map(static function ($value, $key) {
                return [ 'key' => $key, 'value' => $value, ];
            }, $export, array_keys($export))) : '',
            'import_form_action' => $_SERVER['REQUEST_URI'],
        ]);
    }

    /**
     * Sets the parameter array structure if there's any saved parameters otherwise returns an empty string
     *
     * @param array $parameters
     *
     * @return array
     */
    private function generateParameters(array $parameters): array
    {
        return $parameters ? array_map(
            function ($value, $key) {
                return [
                    'key' => $key,
                    'id' => $this->identifier . '-' . $key,
                    'value' => $value,
                    'label_key' => 'Key',
                    'label_value' => 'Value',
                ];
            },
            $parameters,
            array_keys($parameters)
        )
            : [];
    }
}
