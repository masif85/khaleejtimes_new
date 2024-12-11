<?php

namespace KTDTheme\ViewModels;

use Infomaker\Everyware\Base\Models\Page as PageModel;
use Infomaker\Everyware\Support\GenericPropertyObject;

class BasePage extends Page
{
  public function __construct(PageModel $page)
  {
    $propertyObject = new GenericPropertyObject();
    $propertyObject->fill([
      'page' => $page,
      'paramQ' => $this->getQuery(),
      'getPageID' => get_the_ID(),
      'getMetaDescription' => $page->getMeta('meta_description', ''),
      'getMetaTitle' => $page->getMeta('meta_title', ''),
      'getMetaKeywords' => $page->getMeta('meta_keywords', ''),
    ]);

    parent::__construct($propertyObject);
  }

  /**
   * @return string
   */
  public function getQuery(): string
  {
    return empty($_GET['q']) ? '' : $_GET['q'];
  }
}
