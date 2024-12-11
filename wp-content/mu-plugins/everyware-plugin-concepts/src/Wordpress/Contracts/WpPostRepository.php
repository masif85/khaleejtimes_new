<?php declare(strict_types=1);

namespace Everyware\Concepts\Wordpress\Contracts;

use WP_Error;
use WP_Post;

/**
 * Interface WpPostRepository
 * @package Everyware\Concepts\Wordpress\Contracts
 */
interface WpPostRepository
{
    /**
     * @see   https://developer.wordpress.org/reference/functions/add_post_meta/
     *
     * @param int    $postId
     * @param string $key
     * @param mixed  $value
     *
     * @return int|false Meta ID on success, false on failure.
     */
    public function addPostMeta(int $postId, string $key, $value);

    /**
     * @see   https://developer.wordpress.org/reference/functions/wp_count_posts/
     *
     * @param string $type Optional. Post type to retrieve count. Default 'post'.
     * @param string $perm Optional. 'readable' or empty. Default empty.
     *
     * @return object Number of posts for each status.
     */
    public function countPosts($type = 'post', $perm = '');


    /**
     * @see   https://developer.wordpress.org/reference/functions/wp_insert_post/
     *
     * @param array $postData
     * @param bool  $wpError
     *
     * @return int|WP_Error
     */
    public function insertPost($postData, $wpError = false);

    /**
     * @see   https://developer.wordpress.org/reference/functions/is_wp_error/
     *
     * @param mixed $thing Check if unknown variable is a WP_Error object.
     *
     * @return bool True, if WP_Error. False, if not WP_Error.
     */
    public function isError($thing): bool;

    /**
     * @see   https://developer.wordpress.org/reference/functions/wp_delete_post/
     *
     * @param int  $postId
     * @param bool $force_delete
     *
     * @return WP_Post|false|null
     */
    public function deletePost($postId = 0, $force_delete = false);

    /**
     * @see   https://developer.wordpress.org/reference/functions/get_page_by_path/
     *
     * @param string       $pagePath
     * @param string       $output
     * @param string|array $postType
     *
     * @return WP_Post|array|null
     */
    public function getPageByPath($pagePath, $output = OBJECT, $postType = 'page');


    /**
     * @see   https://developer.wordpress.org/reference/functions/get_post/
     *
     * @param int|WP_Post|null $post
     * @param string           $output
     * @param string           $filter
     *
     * @return WP_Post|array|null
     */
    public function getPost($post = null, $output = OBJECT, $filter = 'raw');

    /**
     * @see   https://developer.wordpress.org/reference/classes/wp_query/
     *
     * @param string|array $query URL query string or array of query arguments.
     *
     * @return array List of posts.
     *
     */
    public function query($query): array;

    /**
     * @see   https://developer.wordpress.org/reference/functions/wp_update_post/
     *
     * @param array|object $postData
     * @param bool         $wpError
     *
     * @return int|WP_Error
     */
    public function updatePost($postData = [], $wpError = false);
}
