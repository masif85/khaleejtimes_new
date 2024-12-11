<?php declare (strict_types=1);

namespace Everyware\Plugin\SettingsParameters;

use Everyware\ProjectPlugin\Components\ComponentSettingsRepository;
use Everyware\ProjectPlugin\Components\SettingsProviders\CollectionDbProvider;
use Infomaker\Everyware\Support\Storage\CollectionDB;


use Infomaker\Everyware\Support\Environment;

/**
 * Handles all ajax request for Inserting, deleting and importing settings parameters
 *
 */
class PluginSettingsParameters extends ComponentSettingsRepository
{
    private static $fields = [
        'parameters' => [],
    ];
    public const OPTION_NAME = 'everyware_settings_parameters';

    /**
     * Instantiates parent constructor with CollectionDbProvider
     * and CollectionDB passing Option_name and parameters field
     */
    public function __construct()
    {
        parent::__construct(
            new CollectionDbProvider(
                new CollectionDB(static::OPTION_NAME),
                static::$fields
            )
        );
    }
    /**
     * Enqueues the admin.js script and add ajax actions for
     * deleting, adding and Importing parameters.
     *
     * @return void
     */
    public function init(): void
    {
        add_action('admin_enqueue_scripts', static function () {
            if (is_admin()) {
                $assetsPath = plugin_dir_url(__FILE__) . '../dist';
                $scriptVersion = Environment::isDev() ? false : GIT_COMMIT;
                wp_enqueue_script(
                    'everyware-settings-parameters',
                    "{$assetsPath}/js/admin.js",
                    ['jquery'],
                    $scriptVersion,
                    true
                );
                wp_localize_script(
                    'everyware-settings-parameters',
                    'settings_parameters_ajax',
                    [
                        'ajax_url' => admin_url(
                            'admin-ajax.php'
                        ),
                    ]
                );
            }
        });
        add_action('wp_ajax_delete_parameter', [$this, 'ajaxDeleteParameter']);
        add_action('wp_ajax_add_parameters', [$this, 'ajaxAddParameters']);
        add_action('wp_ajax_import_json', [$this, 'ajaxJsonImport']);
    }

    /**
     * Ajax for merging jsonData into current settings parameters.
     *
     * @return void
     */
    public function ajaxJsonImport(): void
    {
        if (isset($_POST['data'])) {
            $importedParametersJson = stripslashes(html_entity_decode($_POST['data']));

            $importedParameters = json_decode($importedParametersJson, true);

            wp_send_json($this->mergeParameters($importedParameters));
        }
    }

    /**
     * Ajax for deleteing a parameter. Requires id to be present in data variable.
     *
     * @return void
     */
    public function ajaxDeleteParameter()
    {
        if (isset($_POST['data'])) {
            $savedParameters = $this->getSavedParameters();
            $newParameters = $this->deleteParameter($savedParameters, $_POST['data']);

            wp_send_json($this->updateParameters($newParameters));
        }
    }

    /**
     * Ajax for adding parameters
     *
     * @return void
     */
    public function ajaxAddParameters()
    {
        if (isset($_POST['form'])) {
            parse_str($_POST['form'], $searcharray);
            wp_send_json($this->updateParameters($searcharray['fields']));
        }
    }

    /**
     * Retrives all saved parameters
     *
     * @return array
     */
    private function getSavedParameters(): array
    {
        $savedParameters = $this->getValue('parameters');
        $keys = array_keys($savedParameters);
        $values = array_values($savedParameters);

        $combinedParameters['key'] = $keys;
        $combinedParameters['value'] = $values;

        return $combinedParameters;
    }

    /**
     * Merges saved and imported parameters.
     *
     * @param array $importedParameters The imported settings parameters
     *
     * @return bool
     */
    private function mergeParameters(array $importedParameters)
    {
        $savedParameters = $this->getSavedParameters();

        foreach ($importedParameters as $parameter) {
            array_push($savedParameters['key'], $parameter['key']);
            array_push($savedParameters['value'], $parameter['value']);
        }

        return $this->updateParameters($savedParameters);
    }

    /**
     * Deletes a item from the collection by getting its index from the passed key
     *
     * @param string $parameters The key to the parameter that should be deleted
     * @param array  $key        The full collection of parameters
     *
     * @return array
     */
    private function deleteParameter(array $parameters, string $key): array
    {
        $id = array_search($key, array_values($parameters['key']));
        unset($parameters['key'][$id]);
        unset($parameters['value'][$id]);

        return $parameters;
    }

    /**
     * Iterates over all parameters and removes parameters with duplicate keys.
     *
     * @param array $parameters The array which will be checked for duplicate keys.
     *
     * @return array
     */
    private function deleteDuplicates(array $parameters): array
    {
        $duplicates = [];
        foreach (array_count_values($parameters['key']) as $value => $parameterKey) {
            if ($parameterKey > 1) {
                $duplicates[] = $value;
            }
        }
        foreach ($duplicates as $duplicate) {
            $parameters = $this->deleteParameter($parameters, $duplicate);
        }

        return $parameters;
    }

    /**
     * Updates the parameters settingsField.
     * Removes duplicate parameters before saving.
     *
     * @param array $submittedParameters Contains all parameters that should be saved
     *
     * @return bool
     */
    private function updateParameters(array $submittedParameters): bool
    {
        $parameters = $this->deleteDuplicates($submittedParameters);

        $validParameters = array_combine($parameters['key'], $parameters['value']);

        $settings = $this->get();

        $settings['parameters'] = $validParameters;

        return $this->save($settings);
    }
}
