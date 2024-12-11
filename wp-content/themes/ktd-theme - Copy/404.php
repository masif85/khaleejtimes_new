<?php

use Infomaker\Everyware\Twig\View;
use Infomaker\Everyware\Support\GenericPropertyObject;
use KTDTheme\ViewModels\Page;

$page = new Page(new GenericPropertyObject());

View::render('@base/page/page-not-found', $page->getViewData());
