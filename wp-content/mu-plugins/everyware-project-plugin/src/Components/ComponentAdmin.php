<?php declare(strict_types=1);

namespace Everyware\ProjectPlugin\Components;

use Everyware\ProjectPlugin\Components\Contracts\Admin;
use Everyware\ProjectPlugin\Components\Contracts\SettingsRepository;
use Everyware\ProjectPlugin\Components\Contracts\SettingsForm;
use Infomaker\Everyware\Support\Str;

class ComponentAdmin implements Admin
{
    public const VERSION_FIELD = 'current_version';

    /**
     * Contains a list of rules for the form fields used for validation
     *
     * @var array
     */
    protected $rules;

    /**
     * Contains the settings from the plugin
     * @var SettingsRepository
     */
    protected $repository;

    /**
     * @var ComponentSettingsForm
     */
    protected $form;

    /**
     * @var string
     */
    private $currentVersion;

    public function __construct(
        SettingsForm $form,
        SettingsRepository $repository
    ) {
        $this->form = $form;
        $this->repository = $repository;

        $this->setCurrentVersion();

        // Add default version field on new instances
        $this->repository->addRequiredField(static::VERSION_FIELD, $this->currentVersion());
    }

    public function currentVersion(): string
    {
        return $this->currentVersion;
    }

    public function getComponentInfo(array $componentInfoMap): array
    {
        return $this->form->getComponentInfo($componentInfoMap);
    }

    public function getDescription(): string
    {
        return $this->form->getDescription();
    }

    public function getId(): string
    {
        return Str::slug($this->getName());
    }

    public function getInputPrefix(): string
    {
        return $this->form->getFormPrefix();
    }

    public function getName(): string
    {
        return $this->form->getName();
    }

    public function getSettings(): array
    {
        return $this->repository->get();
    }

    /**
     * Fires on creation
     *
     * @param array $storedData
     *
     * @return string The settings form.
     */
    public function create(array $storedData): string
    {
        return $this->form->create($storedData);
    }

    /**
     * Fires on edit
     *
     * @param array $storedData
     *
     * @return string The settings form.
     */
    public function edit(array $storedData): string
    {
        return $this->form->edit($storedData);
    }

    /**
     * Fires before the data will be saved
     *
     * @param array $newData
     * @param array $oldData
     *
     * @return bool Data to save or null to cancel saving
     */
    public function store(array $newData, array $oldData = []): bool
    {
        return $this->repository->store($newData);
    }

    /**
     * Fires before the data will be updated
     *
     * @param array $newData
     * @param array $oldData
     *
     * @return bool
     */
    public function update(array $newData, array $oldData = []): bool
    {
        return $this->repository->update($newData, $oldData);
    }

    protected function setCurrentVersion(): void
    {
        $this->currentVersion = $this->form->currentVersion();
    }
}
