<?php

use Infomaker\Imengine\Imengine;
use Infomaker\Everyware\Twig\View;
use Infomaker\Everyware\Base\FeedRouter;
use Infomaker\Everyware\Base\OpenContent\QueryBuilder;
use Infomaker\Everyware\Base\OpenContent\OpenContentProvider;

FeedRouter::register('/routerss',  function() {
    $provider = OpenContentProvider::setup( [
        'contenttypes'           => [ 'Article' ],
        'sort.indexfield'        => 'Pubdate',
        'sort.Pubdate.ascending' => 'false',
        'limit'                  => 20,
        'start'                  => 0,
    ] );
    $provider->setPropertyMap( 'Article' );
    $data = $provider->queryWithRequirements();
    $url = Imengine::original();

    $articles = array_map( function ($article) use ($url) {
        return View::generate( '@base/feeds/feed-rss', [
            'title' => $article->headline,
            'accessRights' => '',
            'publisher' => '',
            'permalink' => $article->permalink,
            'uuid' => $article->uuid,
            'pubdate' => $article->updated ?: $article->pubdate,
            'images' => array_map( function( $uuid ) use ($url) {
                return $url->fromUuid($uuid);
            }, is_array($article->image_uuids) ? $article->image_uuids : []),
            'authors' => array_map( function( $author ) {
                return $author['name'];
            }, is_array($article->authors) ? $article->authors : [])
        ]);
    }, $data );

    View::render('@base/feeds/feed', [
        'articles' => $articles,
        'title' => get_bloginfo('name'),
        'description' => get_bloginfo('description'),
        'link' => get_bloginfo('url')
    ]);
});
