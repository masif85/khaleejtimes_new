<?php declare(strict_types=1);

namespace Everyware\Plugin\ContentSync;

use Everyware\Plugin\ContentSync\Wordpress\Contracts\WpAdminPage;
use Everyware\Plugin\ContentSync\Wordpress\WpAdmin;
use Everyware\Plugin\ContentSync\Wordpress\WpSites;
use WP_Site;

class SettingsPage implements WpAdminPage
{
    private const PAGE_TITLE = 'Content Sync Settings';
    private const MENU_TITLE = 'Content Sync';

    private Settings $settings;
    private WpSites $wpSites;

    public function __construct(Settings $settings, WpSites $wpSites)
    {
        $this->wpSites = $wpSites;
        $this->settings = $settings;
    }

    public function pageTitle(): string
    {
        return static::PAGE_TITLE;
    }

    public function menuTitle(): string
    {
        return static::MENU_TITLE;
    }

    public function getContent(): string
    {
        $bucketUrl = $this->settings->getBucketUrl();

        if ($this->settings->generateSiteMap() !== $this->settings->registeredSites()) {
            $this->settings->deploySites();
        }

        $config = $this->settings->generateConfig();
        if ($config !== $this->settings->currentConfig()) {
            $this->settings->deployConfig();
        }

        $configJson = json_encode($config, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);

        $sites = $this->getSitesSettings($this->settings->registeredSites());
        ?>
        <h1>Content Sync</h1>
        <p class="description">The content sync configuration is stored in sites.json and config.json in <a
                href="<?php print $bucketUrl; ?>"
                target="_blank">S3</a>
        </p>
        <table class="form-table" role="presentation">
            <tbody>
            <tr>
                <th scope="row"><?php _e('Active sites', CONTENT_SYNC_LANG); ?></th>
                <td>
                    <?php foreach ($sites as $site) : ?>
                        <p><label><input
                                    type="checkbox"
                                    <?php disabled(true); ?>
                                    <?php checked($site['active']); ?>
                                    value="<?php print $site['url']; ?>"/><span style="margin: 0 .5rem;"><?php
                                    print $site['name'];
                                    ?></span><code><?php print $site['url']; ?></code>
                            </label></p>
                    <?php endforeach; ?>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Config', CONTENT_SYNC_LANG); ?></th>
                <td>
                    <pre style="margin: 0"><code style="display: inline-block;"><?php print $configJson; ?></code></pre>
                </td>
            </tr>
            </tbody>
        </table>
        <?php
        return '';
    }

    public function getSitesSettings(array $registeredSites): array
    {
        $siteSettings = [];
        foreach ($this->wpSites->subSites() as $site) {
            if ($site instanceof WP_Site) {
                $url = $this->wpSites->getSiteUrl($site);
                $siteSettings[] = $site->to_array() + [
                        'name' => $site->blogname,
                        'url' => $url,
                        'active' => in_array($url, $registeredSites, true)
                    ];
            }
        }

        return $siteSettings;
    }

    public function registerToWordpress(WpAdmin $wpAdmin): void
    {
        $wpAdmin->registerNetworkSettingsPage($this);
    }
}
