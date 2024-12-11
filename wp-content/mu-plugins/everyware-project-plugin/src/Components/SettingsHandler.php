<?php declare(strict_types=1);

namespace Everyware\ProjectPlugin\Components;

class SettingsHandler
{
    /**
     * @var string
     */
    private $formPrefix;

    /**
     * @var string
     */
    protected $currentGroup;

    public function __construct($formPrefix)
    {
        $this->formPrefix = $formPrefix;
    }

    public function generateFormData(array $storedData = []): array
    {
        return $this->setupFields($storedData);
    }

    public function generateGroupedFormData($group, $storedData = [])
    {
        $this->currentGroup = "[$group]";

        $formData = $this->generateFormData($storedData);

        $this->currentGroup = null;

        return $formData;
    }

    public function getFormPrefix(): string
    {
        return $this->formPrefix;
    }

    // Private Functions
    // ======================================================

    private function getFieldMultiValues(string $field, array $instance = []): array
    {
        $returnVal = [];
        foreach ($instance as $i => $value) {
            if (\is_array($value)) {
                foreach ((array)$value as $settingsName => $settingsValue) {
                    $returnVal[$i][$settingsName] = $this->getFieldValues("{$field}[{$i}][{$settingsName}]",
                        $instance[$i][$settingsName]);
                }
            }
        }

        return $returnVal;
    }

    private function getFieldValues(string $field, $value): SettingsField
    {
        $fieldObj = new SettingsField($field, $value, $this->formPrefix . $this->currentGroup ?? '');

        return $fieldObj;
//        return $fieldObj->toArray();
    }

    private function setupFields(array $fields = []): array
    {
        $fieldsValues = [];

        foreach ($fields as $field => $value) {
            if( \is_array($value) ) {
                $fieldValue = $this->getFieldMultiValues($field, $value);

                $fieldsValues[$field] = ! empty($fieldValue) ? $fieldValue : $this->getFieldValues("{$field}[]", $value);
                continue;
            }
            if( !\is_string($field)) {
                continue;
            }

            $fieldsValues[$field] = $this->getFieldValues($field, $value);
        }

        return $fieldsValues;
    }
}
