<?php declare(strict_types=1);

namespace Everyware\ProjectPlugin\Components\Adapters;

use Everyware\ProjectPlugin\Helpers\MessageBag;
use Infomaker\Everyware\Support\Str;
use Everyware\ProjectPlugin\Components\Contracts\Admin;
use Everyware\ProjectPlugin\Interfaces\SettingsPageInterface;

class PluginSettingsTabAdapter implements SettingsPageInterface
{
    /**
     * @var Admin
     */
    private $component;

    /**
     * @var array
     */
    private $storedSettings;

    public function __construct(Admin $component)
    {
        $this->component = $component;
    }

    public function getStatusMessage(): string
    {
        return MessageBag::messagesToHtml();
    }

    public function getTabSlug(): string
    {
        return Str::slug($this->getTabTitle());
    }

    public function getTabTitle(): string
    {
        return $this->component->getName();
    }

    public function onPageLoad(): void
    {
        $storedData = $this->getStoredSettings();

        if ($this->onSave()) {

            $newData = $this->postedSettings();

            $saved = empty($storedData) ?
                $this->component->store($newData, $storedData) :
                $this->component->update($newData, $storedData);

            if ($saved) {
                $this->updateStoredSettings();
            }
        }
    }

    public function pageContent(): string
    {
        $storedData = $this->getStoredSettings();

        return empty($storedData) ? $this->component->create($storedData) : $this->component->edit($storedData);
    }

    private function getStoredSettings(): array
    {
        if ($this->storedSettings === null) {
            $this->storedSettings = $this->component->getSettings();
        }

        return $this->storedSettings;
    }

    private function updateStoredSettings(): void
    {
        $this->storedSettings = $this->component->getSettings();
    }

    private function postedSettings(): array
    {
        return $_POST[$this->component->getInputPrefix()] ?? [];
    }

    private function onSave()
    {
        return array_key_exists($this->component->getInputPrefix(), $_POST);
    }
}
