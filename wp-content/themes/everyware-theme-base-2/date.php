<?php

use Infomaker\Everyware\Twig\View;
use EuKit\Base\ViewModels\ArchivePage;
use Infomaker\Everyware\Support\NewRelicLog;

$archivePage = new ArchivePage(
    get_query_var('year'),
    get_query_var('monthnum'),
    get_query_var('day')
);

try {
    View::render('@base/page/archive-page', $archivePage->getViewData());
} catch (Twig_Error $e) {
    NewRelicLog::error('Error when rendering Archive page', $e);
}
