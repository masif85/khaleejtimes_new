<?php

use Infomaker\Everyware\Twig\View;
use EuKit\Base\ViewModels\BasePage;
use Infomaker\Everyware\Base\Models\Page;
use Infomaker\Everyware\Base\OpenContent\OpenContentProvider;

$current_page = Page::current();

if ($current_page instanceof Page) {

    $page = new BasePage($current_page);

    $provider = OpenContentProvider::setup( [
        'contenttypes'           => [ 'Article' ],
        'sort.indexfield'        => 'Pubdate',
        'sort.Pubdate.ascending' => 'false',
        'limit'                  => 30
    ] );

    $articles = $provider->queryWithRequirements();

    if( is_array( $articles ) ) {
        add_filter( 'ew_content_container_fill', function ( $arr ) use ( $articles ) {
            $arr = array_merge( $arr, $articles );

            return $arr;
        } );
    }

    View::render('@base/main', $page->getViewData());
}
