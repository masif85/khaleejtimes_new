<?php

use Infomaker\Everyware\Base\OpenContent\OpenContentProvider;
use EuKit\Base\PageTemplate;
use Infomaker\Everyware\Base\Models\Page;
use Infomaker\Everyware\Twig\View;

$current_page = Page::current();

if ($current_page instanceof Page) {

    // get start param
    if (isset($_GET['start'])) {
        $start = $_GET['start'];
    } else {
        $start = 0;
    }

    // get id param
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
    } else {
        $id = "";
    }

    $page         = new PageTemplate($current_page);
    $getSolrQuery = get_post_meta($id, 'ew_solr_query', true);

    $provider = OpenContentProvider::setup( [
        'ConceptChannelsUuids'   => '704085f6-29d1-4c24-b09e-23b8e42bac34',
        'q'                      => $getSolrQuery,
        'contenttypes'           => [ 'Article' ],
        'sort.indexfield'        => 'Pubdate',
        'sort.Pubdate.ascending' => 'false',
        'start'                  => $start,
        'limit'                  => 10
    ] );

    $articles = $provider->queryWithRequirements();
    if( is_array( $articles ) ) {
        add_filter( 'ew_content_container_fill', function ( $arr ) use ( $articles ) {
            $arr = array_merge( $arr, $articles );

            return $arr;
        } );
    }

    View::render('@base/page/page-load-more', $page->getViewData());
}
