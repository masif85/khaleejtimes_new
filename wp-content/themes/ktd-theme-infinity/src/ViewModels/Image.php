<?php

namespace KTDTheme\ViewModels;

class Image extends \EuKit\Base\Parts\Image
{
  public function getViewData(): array
  {
    return array_merge(parent::getViewData(), [
      'optimizedSrcset' => $this->createSrcset([105, 235, 335, 500, 660, 775, 950, 1005]),
    ]);
  }

  private function createSrcset(array $sizes, bool $crop = true): string
  {
    return implode(',', array_map(function($size) use ($crop) {
      if ($crop) {
        return $this->getRatioUrl($size) . " {$size}w";
      }

      return $this->getFitURL($size) . " {$size}w";
    }, $sizes));
  } 
}
