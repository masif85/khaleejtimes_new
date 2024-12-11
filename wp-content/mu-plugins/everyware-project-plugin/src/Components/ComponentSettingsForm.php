<?php declare(strict_types=1);

namespace Everyware\ProjectPlugin\Components;

use Everyware\ProjectPlugin\Components\Contracts\InfoManager;
use Everyware\ProjectPlugin\Components\Contracts\SettingsForm;
use Everyware\ProjectPlugin\Html\FormBuilder;
use Everyware\ProjectPlugin\Html\HtmlBuilder;
use Infomaker\Everyware\Support\Str;

/**
 * Class SettingsForm
 * @package Everyware\ProjectPlugin\Components
 */
abstract class ComponentSettingsForm implements SettingsForm
{
    public static $infoMap = [
        'description' => 'Description',
        'name' => 'Plugin Name',
        'version' => 'Version',
    ];

    /**
     * @var InfoManager
     */
    protected $infoManager;

    /**
     * @var string
     */
    private $settingsPrefix;

    public function __construct(InfoManager $infoManager)
    {
        $this->infoManager = $infoManager;
        $this->settingsPrefix = Str::slug('ew-' . $infoManager->getHeader('Plugin Name'));
    }

    public function currentVersion(): string
    {
        return $this->infoManager->getHeader(static::$infoMap['version']);
    }

    public function getComponentInfo(array $componentInfoMap): array
    {
        return $this->infoManager->extractHeaders($componentInfoMap);
    }

    public function getDescription(): string
    {
        return $this->infoManager->getHeader(static::$infoMap['description']);
    }

    public function getName(): string
    {
        return $this->infoManager->getHeader(static::$infoMap['name']);
    }

    public function create(array $storedData): string
    {
        return $this->formContent($storedData);
    }

    public function edit(array $storedData): string
    {
        return $this->formContent($storedData);
    }

    public function getFormPrefix(): string
    {
        return $this->settingsPrefix;
    }

    /**
     * @return FormBuilder
     */
    protected function generateFormBuilder(): FormBuilder
    {
        return new FormBuilder(new HtmlBuilder());
    }

    /**
     * Use the stored data to create view-friendly data
     *
     * @param $storedData
     *
     * @return array
     */
    protected function generateViewData(array $storedData): array
    {
        $settingsHandler =  new SettingsHandler($this->getFormPrefix());

        return $settingsHandler->generateFormData($storedData);
    }

    protected function getFormSettings(array $settings = []): array
    {
        return array_replace([
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'version' => $this->currentVersion(),
            'class_prefix' => $this->getFormPrefix() . '__form',
            'id_prefix' => $this->getFormPrefix()
        ], $settings);
    }

    abstract protected function formContent($storedData): string;
}
