<?php
declare(strict_types=1);

namespace Unit\Everyware\Plugin\RedirectOriginalUrls;

use Everyware\Plugin\RedirectOriginalUrls\PluginSettings;
use Everyware\Plugin\RedirectOriginalUrls\PluginSettingsAdmin;
use Everyware\Plugin\RedirectOriginalUrls\PluginSettingsForm;
use Everyware\ProjectPlugin\Helpers\FileReader;
use Everyware\ProjectPlugin\Helpers\MessageBag;
use ValueError;

/**
 * Class PluginSettingsAdminTest
 * @package Unit\Everyware\Plugin\RedirectOriginalUrls
 */
class PluginSettingsAdminTest extends RedirectOriginalUrlsTestCase
{
    public function testFormatDomains(): void
    {
        $domains = [
            'foo.com',
            'bar.io'
        ];
        self::assertEquals('foo.com, bar.io', PluginSettingsAdmin::formatDomains($domains));
    }

    public function testParseDomains(): void
    {
        $domains = [
            'foo.com',
            'bar.io'
        ];
        self::assertEquals($domains, PluginSettingsAdmin::parseDomains('foo.com, bar.io'));
        self::assertEquals($domains, PluginSettingsAdmin::parseDomains(' foo.com , ,, bar.io '));
        self::assertNotEquals($domains, PluginSettingsAdmin::parseDomains('foo.com'));
    }

    public function testValidateWithDefaultSettings(): void
    {
        $this->validate();

        // If the above did not throw an exception, the test passed.
        self::assertTrue(true);
    }

    public function testValidateWithoutTrailingSlashesSettings(): void
    {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('Select whether URLs end with a trailing slash or not.');

        $this->validate([
            PluginSettings::URL_SETTING_TRAILING_SLASHES => [
                PluginSettings::URL_SETTING_TRAILING_SLASHES_WITHOUT => false,
                PluginSettings::URL_SETTING_TRAILING_SLASHES_WITH => false
            ]
        ]);
    }

    public function testValidateWithoutDomains(): void
    {
        list($settingsWithPaths, $settingsWithUrls) = $this->varyExtentInSettings([
            PluginSettings::URL_SETTING_DOMAINS => []
        ]);

        $this->validate($settingsWithPaths);

        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('Enter at least one domain.');

        $this->validate($settingsWithUrls);
    }

    public function testValidateWithInvalidDomain(): void
    {
        list($settingsWithPaths, $settingsWithUrls) = $this->varyExtentInSettings([
            PluginSettings::URL_SETTING_DOMAINS => ['www.example.com', 'www.bad:!domain.7z']
        ]);

        $this->validate($settingsWithPaths);

        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('www.bad:!domain.7z is not a valid domain name.');

        $this->validate($settingsWithUrls);
    }

    public function testValidateWithoutSchemeSettings(): void
    {
        list($settingsWithPaths, $settingsWithUrls) = $this->varyExtentInSettings([
            PluginSettings::URL_SETTING_SCHEMES => [
                PluginSettings::URL_SETTING_SCHEME_HTTPS => false,
                PluginSettings::URL_SETTING_SCHEME_HTTP => false
            ]
        ]);

        $this->validate($settingsWithPaths);

        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('Select at least one scheme.');

        $this->validate($settingsWithUrls);
    }

    public function testValidateWithoutWwwSubdomainSettings(): void
    {
        list($settingsWithPaths, $settingsWithUrls) = $this->varyExtentInSettings([
            PluginSettings::URL_SETTING_WWW_SUBDOMAINS => [
                PluginSettings::URL_SETTING_WWW_SUBDOMAINS_WITH => false,
                PluginSettings::URL_SETTING_WWW_SUBDOMAINS_WITHOUT => false
            ]
        ]);

        $this->validate($settingsWithPaths);

        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('Select whether www. subdomains are used or not.');

        $this->validate($settingsWithUrls);
    }

    public function testUpdateWithInvalidData(): void
    {
        $newData = $this->settingsAdmin->formatData($this->generateSettings([
            PluginSettings::URL_SETTING_DOMAINS => ['www.bad:!domain.7z']
        ]));

        // Keep an eye on MessageBag, since validation errors will be added there.
        $messagesLengthBefore = strlen(MessageBag::messagesToHtml());

        $success = $this->settingsAdmin->update($newData);

        $messagesAdded = substr(MessageBag::messagesToHtml(), $messagesLengthBefore);

        self::assertFalse($success);
        self::assertStringContainsString('<strong>Failed to save:</strong> www.bad:!domain.7z is not a valid domain name.', $messagesAdded);
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->settingsAdmin = new PluginSettingsAdmin(
            new PluginSettingsForm(new FileReader(__FILE__)),
            FakePluginSettings::create()
        );
    }

    /**
     * Several settings are invalid (required) when extent is URLs, but okay when it's paths.
     * So generate two variations of the same settings; one for each scenario.
     *
     * @param array $settings
     *
     * @return array Two arrays of settings; one with extent 'paths', one with extent 'urls'.
     */
    private function varyExtentInSettings(array $settings): array
    {
        return [
            array_merge([
                PluginSettings::URL_SETTING_EXTENT => PluginSettings::URL_SETTING_EXTENT_PATHS
            ], $settings),
            array_merge([
                PluginSettings::URL_SETTING_EXTENT => PluginSettings::URL_SETTING_EXTENT_URLS
            ], $settings)
        ];
    }

    /**
     * Validate the admin with a certain set of settings.
     *
     * @param array $changesFromDefaultSettings
     *                      
     * @throws ValueError  If the validation does not pass.
     */
    private function validate(array $changesFromDefaultSettings = []): void
    {
        $this->settingsAdmin->validate($this->generateSettings($changesFromDefaultSettings));
    }

    private function generateSettings(array $changesFromDefaultSettings = []): array
    {
        $domainKey = PluginSettings::URL_SETTING_DOMAINS;

        $defaults = array_replace(PluginSettings::$fields, [
            // This is the only setting whose default value is not valid, so add a dummy value!
            $domainKey => ['example.com']
        ]);

        $result = array_replace_recursive($defaults, $changesFromDefaultSettings);

        // An empty array should be allowed to override this setting, which array_replace_recursive() does not handle.
        if (isset($changesFromDefaultSettings[$domainKey])) {
            $result[$domainKey] = $changesFromDefaultSettings[$domainKey];
        }

        return $result;
    }

    /**
     * @var PluginSettingsAdmin
     */
    private $settingsAdmin;
}
