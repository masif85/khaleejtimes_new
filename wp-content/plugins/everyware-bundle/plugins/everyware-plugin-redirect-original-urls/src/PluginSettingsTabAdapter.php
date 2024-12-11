<?php declare(strict_types=1);

namespace Everyware\Plugin\RedirectOriginalUrls;


use Infomaker\Everyware\Support\Environment;

/**
 * Class PluginSettingsTabAdapter
 * @package Everyware\Plugin\RedirectOriginalUrls
 */
class PluginSettingsTabAdapter extends \Everyware\ProjectPlugin\Components\Adapters\PluginSettingsTabAdapter
{
    public function onPageLoad(): void
    {
        parent::onPageLoad();

        add_action('admin_enqueue_scripts', static function () {

            $scriptVersion = Environment::isDev() ? false : GIT_COMMIT;
            $pluginDirUrl = plugin_dir_url( dirname(__DIR__) . '/index.php' );

            wp_enqueue_script(
                'redirect-original-urls-admin-js',
                $pluginDirUrl . 'dist/js/admin' . (is_dev() ? '.js' : '.min.js'),
                null,
                $scriptVersion
            );
            wp_enqueue_style(
                'redirect-original-urls-admin-css',
                $pluginDirUrl . 'dist/css/admin' . (is_dev() ? '.css' : '.min.css'),
                null,
                $scriptVersion
            );
        });
    }
}
