<?php
define('WP_USE_THEMES', false);
require_once(dirname(__FILE__) . '/../../../../wp-blog-header.php');

$usr = html_entity_decode(isset($_GET['username']) ? $_GET['username'] : '');
$pwd = html_entity_decode(isset($_GET['password']) ? $_GET['password'] : '');
$listid = isset($_GET['listid']) ? $_GET['listid'] : '';

$pwd = str_replace(' ', '+', $pwd);

$credentials = [
    'user_login' => $usr,
    'user_password' => $pwd,
    'remember' => false
];

$list = ! empty($listid) ? '&listid=' . $listid : '';

if (is_wp_error(wp_signon($credentials))) {
    $login_url = wp_login_url(site_url('wp-admin/edit.php?post_type=everyboard&page=boardlist&source=np' . $list,
        true));
    wp_redirect($login_url);
} else {
    wp_redirect(site_url('wp-admin/edit.php?post_type=everyboard&page=boardlist&source=np' . $list, 302));
}
