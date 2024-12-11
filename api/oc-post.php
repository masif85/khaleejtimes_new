<?php
require('../wp-blog-header.php');

$data = [];
$status = 400;
if (isset($_GET['url'])) {
    $url = urldecode($_GET['url']);
    $postId = url_to_postid($url);
    $data['uuid'] = OcUtilities::get_uuid_by_post_id($postId);
    $data['url'] = $url;
    $status = $data['uuid'] ? 200 : 404;
} elseif (isset($_GET['uuid'])) {
    $uuid = urldecode($_GET['uuid']);
    $postId = OcUtilities::get_article_post_id_by_uuid($uuid);
    $data['uuid'] = $uuid;
    $data['url'] = get_permalink($postId);

    if (!$data['url']) {
        $ocApi = new OcAPI();
        $object = $ocApi->get_single_object($uuid);

        if ($object instanceof OcArticle) {
            $data['url'] = $object->get_permalink();
        }
    }
    $status = $data['url'] ? 200 : 404;
}

wp_send_json($data, $status);
