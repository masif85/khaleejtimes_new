<?php

use EuKit\Base\ViewModels\Page;
use Infomaker\Everyware\Twig\View;
use Infomaker\Everyware\Support\GenericPropertyObject;

$page = new Page(new GenericPropertyObject());
View::render('@base/page/page-not-found', $page->getViewData());
