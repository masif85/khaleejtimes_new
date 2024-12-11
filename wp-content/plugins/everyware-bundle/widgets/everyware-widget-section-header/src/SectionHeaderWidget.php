<?php declare(strict_types=1);

namespace Everyware\Widget\SectionHeader;

use Everyware\ProjectPlugin\Helpers\FileReader;
use Everyware\ProjectPlugin\Components\WidgetAdmin;
use Everyware\ProjectPlugin\Components\Contracts\Admin;
use Everyware\ProjectPlugin\Components\Contracts\InfoManager;
use Everyware\ProjectPlugin\Components\Adapters\WidgetAdapter;
use Everyware\ProjectPlugin\Components\ComponentSettingsRepository;
use Everyware\ProjectPlugin\Components\SettingsProviders\SimpleSettingsProvider;
use Infomaker\Everyware\Twig\View;

class SectionHeaderWidget extends WidgetAdapter
{

    protected static $fields = [
        'title' => '',
        'link' => '',
    ];

    protected static $infoManager;

    /**
     * @param InfoManager $infoManager
     */
    public static function setInfoManager(InfoManager $infoManager): void
    {
        static::$infoManager = $infoManager;
    }

    /**
     * @return Admin
     */
    protected function widgetSetup(): Admin
    {
        return new WidgetAdmin(
            new SectionHeaderForm(static::$infoManager ?? new FileReader(__FILE__)),
            new ComponentSettingsRepository(new SimpleSettingsProvider(self::$fields))
        );
    }

    /**
     * @param array $viewData
     * @param array $args
     *
     * @return string
     */
    protected function generateWidget(array $viewData, array $args): string
    {
        return View::generate('@section-header/widget', array_replace($viewData, [
            'args' => $args,
            'settings' => $this->getWidgetSettings()
        ]));
    }

    /**
     * @param array $data
     *
     * @return string
     */
    protected function generateBoardContent(array $data): string
    {
        $string = '<h2 class="section-headline">';

        // if a link is set, create an a-tag
        $string .= !empty($data['link']) ? '<a href="' . $data['link'] . '">' . $data['title'] . '</a>' : $data['title'];

        $string .= '</h2>';

        return $string;
    }
}
