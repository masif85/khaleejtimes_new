<?php declare(strict_types=1);

namespace Everyware\Everyboard;

use WP_Error;

/**
 * Class DashboardWidget
 * @package Everyware\Everyboard
 */
class DashboardWidget
{
    private $pluginName = 'infomaker/everyboard';
    /**
     * @var string
     */
    private $versionsUrl = 'https://composer.infomaker.io/versions.json';

    /**
     * @var array
     */
    private $footerLinks = [
        [
            'text' => 'Documentation',
            'href' => 'https://docs.navigaglobal.com/everyware/starter-kit-packages/everyboard'
        ],
        [
            'text' => 'Changelog',
            'href' => 'https://docs.navigaglobal.com/everyware/starter-kit-packages/everyboard/changelog/'
        ]
    ];

    private function getLatestVersion(): string
    {
        $key = 'eb_latest_version';

        if (false !== ($latest_version = get_transient($key))) {
            return $latest_version;
        }

        $versions = $this->getAvailableVersions();

        if (empty($versions)) {
            return '';
        }

        usort($versions, 'version_compare');

        $latest_version = array_pop($versions);

        set_transient($key, $latest_version, HOUR_IN_SECONDS);

        return $latest_version;
    }

    private function getAvailableVersions()
    {
        $response = wp_remote_get($this->versionsUrl, ['timeout' => 3]);

        // Check the response code
        $response_code = (int)wp_remote_retrieve_response_code($response);
        $response_message = wp_remote_retrieve_response_message($response);

        if ($response_code !== 200) {
            return new WP_Error($response_code,
                ! empty($response_message) ? $response_message : 'Could not fetch latest version');
        }

        $plugins = json_decode(wp_remote_retrieve_body($response), false);

        $versions = [];
        foreach ($plugins as $plugin) {
            if ($plugin->name === $this->pluginName) {
                $versions = $plugin->versions;
                break;
            }
        }

        return $versions;
    }

    private function generateContent(): string
    {
        $latest_version = $this->getLatestVersion();

        $message = '';

        if (empty($latest_version)) {
            $message = __('Could not fetch version information, check back later!', 'everyboard');
        } elseif ($latest_version === EVERYBOARD_VERSION) {
            $message = __('Up to date with latest release.', 'everyboard');
        } elseif (version_compare(EVERYBOARD_VERSION, $latest_version, '<')) {
            $message = __('There is a newer version of Everyboard available, update or contact Infomaker for more information.',
                'everyboard');
            $message .= ' <strong>' . __('Latest version', 'everyboard') . ': ' . $latest_version . '</strong>';
        }

        $current_version = EVERYBOARD_VERSION;
        $current_version_text = __('Currently running Everyboard version: ', 'everyboard');

        return <<<EOT
<div class="eb_dashboard_widget__content">
    <p>$current_version_text <strong>$current_version</strong></p>
    <p>{$message}</p>
</div>
EOT;

    }

    private function generateFooter(): string
    {
        $links = implode(' | ', array_map(static function ($link) {
            return sprintf(
                '<a class="eb_dashboard_widget__footer-link" href="%s" style="text-decoration:none;" target="_blank">%s&nbsp;<span class="screen-reader-text">(opens in a new window)</span><span aria-hidden="true" class="dashicons dashicons-external"></span></a>',
                $link['href'],
                $link['text']
            );
        }, $this->footerLinks));

        return <<<EOT
<div class="eb_dashboard_widget__footer">
    $links
</div>
EOT;
    }

    public function getTitle(): string
    {
        return 'Everyboard';
    }

    public function render(): void
    {
        echo $this->generateContent();
        echo $this->generateFooter();
    }

    public static function register(): void
    {
        global $pagenow;

        if ($pagenow === 'index.php') {
            add_action('wp_dashboard_setup', function () {
                $widget = new static();

                wp_add_dashboard_widget(
                    'eb_dashboard_widget',
                    $widget->getTitle(),
                    [$widget, 'render']
                );
            });


            add_action('admin_enqueue_scripts', function () {
                wp_enqueue_style(
                    'everyboard-dashboard',
                    EVERYBOARD_BASE . 'assets/dist/css/dashboardwidget.min.css',
                    [],
                    EVERYBOARD_VERSION
                );
            });
        }
    }
}
