<?php declare (strict_types = 1);

namespace KTDTheme\ViewModels;

use Infomaker\Everyware\Support\GenericPropertyObject;
use EuKit\Base\ViewModels\Page as BasePage;

class Page extends BasePage
{
  public function __construct(GenericPropertyObject $object)
  {
    $object->fill([
      'meganav' => $this->getMenuItems('meganav'),
      'footernav' => $this->getMenuItems('footernav'),
      'every_device' => constant("EVERY_DEVICE")
    ]);

    parent::__construct($object);
  }
}
