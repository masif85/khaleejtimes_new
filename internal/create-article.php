<?php
/**
 * This file will be called from command line through a LUA-script and it will create an oc article in WP
 * from the given uuid. It will print the articles url to be used by the script for redirecting.
 */

if (isset($_GET['uuid'])) {
    if (isset($_GET['host'])) {
        $_SERVER['HTTP_HOST'] = $_GET['host'];
    }
    require __DIR__ . '/wp-bootstrap.php';
    header('HTTP/1.1 200 OK');

    $uuid = wp_kses($_GET['uuid'] ?? '', []);
    $oc_api = new OcAPI();
    $result = $oc_api->get_single_article($uuid);
    $article = $result['article'] ?: null;

    if ($article instanceof OcArticle) {
        print($article->get_permalink());
    }
    die();
}
print('');
