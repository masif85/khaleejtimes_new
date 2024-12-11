<?php declare(strict_types=1);

namespace Everyware\Widget\SocialMedia;

use Everyware\ProjectPlugin\Components\ComponentSettingsRepository;
use Everyware\ProjectPlugin\Components\Contracts\Admin;
use Everyware\ProjectPlugin\Components\Contracts\InfoManager;
use Everyware\ProjectPlugin\Components\SettingsProviders\SimpleSettingsProvider;
use Everyware\ProjectPlugin\Components\WidgetAdmin;
use Everyware\ProjectPlugin\Helpers\FileReader;
use Everyware\ProjectPlugin\Components\Adapters\WidgetAdapter;
use Infomaker\Everyware\Twig\View;
use Infomaker\Everyware\Support\Collection;

/**
 * Class ArticleListWidget
 */
class SocialMediaWidget extends WidgetAdapter
{
    protected static $icons = [
      'facebook' => 'fab fa-facebook-f',
      'twitter' => 'fab fa-twitter',
      'linkedin' => 'fab fa-linkedin-in',
      'youtube' => 'fab fa-youtube',
      'instagram' => 'fab fa-instagram',
      'snapchat' => 'fab fa-snapchat'
    ];

    protected static $field_name = 'social_media_urls';

    /**
     * @var InfoManager
     */
    protected static $infoManager;

    /**
     * @param array $viewData
     * @param array $args
     *
     * @return string
     */
    protected function generateWidget(array $viewData, array $args): string
    {
        return View::generate('@social_media_widget/widget', array_replace($viewData, [
            'icons' => self::$icons
        ]));
    }

    protected function widgetSetup(): Admin
    {
        $fileReader = static::$infoManager ?? new FileReader(__FILE__);

        $fields = [
          'social_media_urls' => (new Collection(self::$icons))->mapWithKeys(function($value, $key) {
            return [$key => get_option('social_' . $key. '_url')];
          })->toArray()
        ];

        return new WidgetAdmin(
            new SocialMediaForm($fileReader, self::$field_name),
            new ComponentSettingsRepository(new SimpleSettingsProvider($fields))
        );
    }

    public static function setInfoManager(InfoManager $infoManager): void
    {
        static::$infoManager = $infoManager;
    }
}
