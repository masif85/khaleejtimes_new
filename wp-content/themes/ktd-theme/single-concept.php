<?php

use Infomaker\Everyware\Base\Utilities;
use Infomaker\Everyware\Base\Models\Page;
use KTDTheme\ViewModels\ConceptPage;

$currentPage = Page::current();

if (!($currentPage instanceof Page)) {
  Utilities::trigger404();
}

$conceptPage = new ConceptPage($currentPage);

$conceptPage->render();
