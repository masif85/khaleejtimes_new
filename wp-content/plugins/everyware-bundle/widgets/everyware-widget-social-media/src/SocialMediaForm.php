<?php declare(strict_types=1);

namespace Everyware\Widget\SocialMedia;

use Everyware\ProjectPlugin\Components\Contracts\InfoManager;
use Everyware\ProjectPlugin\Components\WidgetSettingsForm;
use Infomaker\Everyware\Twig\View;
use Infomaker\Everyware\Support\Collection;

class SocialMediaForm extends WidgetSettingsForm
{
    protected $field;

    public function __construct(InfoManager $infoManager, string $field)
    {
        $this->field = $field;

        parent::__construct($infoManager);
    }

    /**
     * @param $storedData
     *
     * @return string
     */
    protected function formContent($storedData): string
    {
        return View::generate('@social_media_widget/form', $this->transformData($storedData));
    }

    protected function transformData($storedData)
    {
        $fields = [];

        $storedData = (new Collection($storedData))->mapWithKeys(function($value, $key) use (&$fields) {
          if ($key !== $this->field) return [$key => $value];
          $fields = (new Collection($value));

          return $fields->mapWithKeys(function ($url, $provider) {
            return ["$this->field[$provider]" => $url];
          });
        })->toArray();

        $storedData = $this->generateViewData($storedData);

        return array_replace($storedData, [
            'form' => $this->generateFormBuilder(),
            'fields' => $fields->mapWithKeys(function($field, $provider) {
                return [$provider => "$this->field[$provider]"];
            })->toArray()
        ]);
    }
}
