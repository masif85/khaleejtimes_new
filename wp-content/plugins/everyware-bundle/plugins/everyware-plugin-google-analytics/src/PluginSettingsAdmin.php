<?php declare(strict_types=1);

namespace Everyware\Plugin\GoogleAnalytics;

use Everyware\Plugin\GoogleAnalytics\Models\Credentials;
use Everyware\ProjectPlugin\Components\ComponentAdmin;
use Everyware\ProjectPlugin\Components\Contracts\SettingsRepository;
use Everyware\ProjectPlugin\Helpers\JsonImporter;
use Infomaker\Everyware\Twig\View;

/**
 * Class PluginSettingsAdmin
 * @package Everyware\Plugin\GoogleAnalytics
 */
class PluginSettingsAdmin extends ComponentAdmin
{
    private $credentialStub = '{imported}';

    /**
     * @var JsonImporter
     */
    private $jsonImporter;

    public function __construct(
        PluginSettingsForm $form,
        SettingsRepository $repository,
        JsonImporter $jsonImporter
    ) {
        $this->jsonImporter = $jsonImporter;
        parent::__construct($form, $repository);
    }

    public function create(array $storedData): string
    {
        return parent::edit($storedData);
    }

    public function edit(array $storedData): string
    {
        $storedData = $this->addImportedCredentials($storedData);

        $credentials = new Credentials($storedData['credentials']);
        $storedData['credentials'] = ! empty($storedData['credentials']) ? $this->credentialStub : '';

        return View::generate('@plugins/admin', [
            'credential_preview' => $this->generateCredentialPreview($credentials),
            'settings_form' => parent::edit($storedData)
        ]);
    }

    public function update(array $newData, array $oldData = []): bool
    {
        if ($newData['credentials'] ?? '' === $this->credentialStub) {
            $newData['credentials'] = $oldData['credentials'] ?? [];
        }

        return parent::update($newData, $oldData);
    }

    public function store(array $newData, array $oldData = []): bool
    {
        return parent::update($newData, $oldData);
    }

    private function addImportedCredentials($settings)
    {
        if ($this->jsonImporter->isImported()) {
            $settings['credentials'] = (new Credentials($this->jsonImporter->getJson()))->toArray();
            $this->repository->save($settings);
        }

        return $settings;
    }

    private function generateJsonImporter(Credentials $credentials): string
    {
        return View::generate('@projectPlugin/utilities/import-export', [
            'field_name' => JsonImporter::POST_KEY,
            'export' => $credentials->toJson(JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT),
            'import' => ''
        ]);
    }

    private function generateCredentialPreview(Credentials $credentials): string
    {
        return View::generate('@plugins/credential-preview', [
            'json_import' => $this->generateJsonImporter($credentials),
            'credentials' => $credentials->toArray()
        ]);
    }
}
