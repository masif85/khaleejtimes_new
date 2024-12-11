<?php

/**
 * Class EveryArticleEvents
 */
class EveryArticleEvents {

    const OC_WRITE_TRANSIENT = 'eae_oc_write_data';
    const OC_NEXT_UPDATE_TIME_TRANSIENT = 'eae_oc_next_update_time';
    const OC_WRITE_MAX_RETRIES = 3;

    function __construct($file) {

        // Add custom CRON interval.
        add_filter('cron_schedules', function () {
            $schedules['every_minute'] = array(
                'interval' => 60,
                'display' => __('Every minute'),
            );

            return $schedules;
        });

        add_action('oc_push_update', array(&$this, 'eae_oc_push_update_post_date')); // Article update from OC, update post date.
        add_action('oc_push_update', array(&$this, 'eae_oc_push_update_tags_and_categories'));

        if( defined('OC_EDIT_USER') && defined('OC_EDIT_PASSWORD') ) {
            add_action('trash_article', array(&$this, 'eae_article_trashed'), 10, 2); // Article trashed.
            add_action('trash_to_publish', array(&$this, 'eae_article_published_from_trash'), 10, 1); // Article restored from trash.

            if (defined('EVERY_URL_WRITEBACK') && EVERY_URL_WRITEBACK ) {
                add_action('oc_push_update', array(&$this, 'eae_oc_push_update')); // Article update from OC, call update on meta writeback
                add_action('publish_article', array(&$this, 'eae_article_published'), 10, 1); // Publish article, construct transient data for use when writing data to OC.
                add_action('every_article_created', array(&$this, 'eae_article_published'), 10, 1); // Publish article, construct transient data for use when writing data to OC.

                // Schedule OC write queue cron job.
                $schedule_event = 'oc_write_queue_event';
                if (!wp_next_scheduled($schedule_event)) {
                    wp_schedule_event(time(), 'every_minute', $schedule_event);
                }

                // Check write to OC queue and write any queued objects to OC.
                add_action('oc_write_queue_event', function () {
                    $next_update = get_site_transient(static::OC_NEXT_UPDATE_TIME_TRANSIENT);

                    if (false === $next_update) {
                        $next_update = time();
                    }

                    if (time() >= $next_update) {
                        set_site_transient(static::OC_NEXT_UPDATE_TIME_TRANSIENT, time() + 60);
                        $this->eae_scheduled_oc_update();
                    }
                });
            }
        }
    }

    /**
     * Function that runs on article thrown in trash.
     * @param $ID
     * @param $post
     */
    function eae_article_trashed($post_id, $post) {
        $this->update_article_post($post);
    }

    /**
     * Function that runs when a post is restored from trash.
     * @param $post
     */
    function eae_article_published_from_trash($post) {

        // Check that the post type is article
        if ($post->post_type === 'article') {
            $this->update_article_post($post);
        }
    }

    /**
     * Empties article caches and writes status to OC.
     * @param $post
     */
    private function update_article_post($post) {

        $uuid = OcUtilities::get_uuid_by_post_id($post->ID);
        if ($uuid !== '') {

            // Write post status to Open Content.
            $oc_writer = new OcMetaWriter();
            $result = $oc_writer->update_metadata_xml($post);
            $this->check_update_status($result, $uuid);

            // Remove article from cache.
            OcUtilities::delete_article_from_cache($uuid);
        }
    }

    function eae_oc_push_update($data) {
        if (isset($data['uuid'], $data['response'])) {
            $article_data = json_decode($data['response']);

            $oc_writer = new OcMetaWriter();
            $result = $oc_writer->update_metadata_xml_by_uuid( $data['uuid'], null, $article_data );
            $this->check_update_status($result, $data['uuid']);
        }
    }

    /**
     * On OC push update, update post object date if new.
     *
     * @param $data
     */
    function eae_oc_push_update_post_date($data) {

        // If we have an article update it's pubdate.
        if (isset($data['uuid'], $data['response'])) {

            $article_data = json_decode($data['response']);
            $pubdate = null;

            foreach ($article_data->properties as $prop) {
                $name = strtolower($prop->name);
                if ($name === 'pubdate' && isset($prop->values[0])) {
                    $pubdate = $prop->values[0];
                    break;
                }
            }

            // If we have an article with pubdate
            if (isset($pubdate)) {

                // Get WP post of article.
                $article_post = \OcUtilities::get_article_post_by_uuid($data['uuid']);
                if ($article_post !== null) {
                    // Get time for both post object and oc object.
                    $article_time = strtotime($pubdate);
                    $post_time = strtotime($article_post->post_date);

                    // Check time difference, if bigger than one minute
                    $time_diff = $post_time - $article_time;
                    if ($time_diff > 60 || $time_diff < -60) {
                        // Update post with new time and remove cached data.
                        $update_post = array(
                            'ID' => $article_post->ID,
                            'post_date' => date('Y-m-d H:i:s', $article_time),
                            'post_date_gmt' => gmdate('Y-m-d H:i:s', $article_time)
                        );

                        wp_update_post($update_post);
                        delete_transient('the_post_' . $data['uuid']);
                    }
                }
            }
        }
    }

    /**
     * Function that adds tags and categories to WP if new ones is present
     */
    function eae_oc_push_update_tags_and_categories($data) {

        if( defined( 'EVERY_TAXONOMY_PUSH_SYNC' ) && EVERY_TAXONOMY_PUSH_SYNC === false) {
            return false;
        }

        $article_data = json_decode($data['response']);

        $article = new OcArticle();
        foreach ( $article_data->properties as $prop ) {
            $name = strtolower( $prop->name );
            $val = $prop->values;
            $article->$name = $val;
        }

        $ocApi = new OcApi();
        $ocApi->add_mapped_properties( $article );

        $article_id = OcUtilities::get_article_post_id_by_uuid($article->uuid[0]);

        // Tags
        $wp_tags = $article->tags;

        if( is_array( $wp_tags ) ) {
            $wp_tags = implode( ",", array_map(
                    function ($tag) {
                        //Only add strings, ignore concept objects
                        if(is_string($tag)){
                            return $tag;
                        }
                    },
                    $article->tags)
            );
        }

        $added_categories = CustomOcPostType::add_sanitized_article_categories( $article );

        if( !is_null( $article_id ) ) {
            wp_update_post([
                'ID'            => $article_id,
                'tags_input'    => $wp_tags,
                'post_category' => $added_categories

            ]);
        }
    }

    /**
     * On article published event, construct transient data for use in cron job that writes to OC.
     *
     * @param $post_id
     */
    function eae_article_published($post_id) {

        // Get wp-post and the url
        $url = get_permalink($post_id);
        $uuid = OcUtilities::get_uuid_by_post_id($post_id);

        if( isset( $uuid ) && $uuid !== '' ) {
            $this->update_transient_data($uuid, $url);
        }
    }

    /**
     * Handles updating of transient data.
     *
     * @param $uuid
     * @param $url
     * @param int $retries
     */
    function update_transient_data($uuid, $url, $retries = 0) {

        $transient_key = self::OC_WRITE_TRANSIENT;
        $transient_ttl = 60 * 60 * 24;

        // Check if transient exists, otherwise create empty set.
        $current_links = get_site_transient($transient_key);

        if ($current_links === false) {
            $current_links = [];
        }

        if (!isset($current_links[$uuid])) {
            $current_links[$uuid] = [];
        }

        if (!isset($current_links[$uuid]['links'])) {
            $current_links[$uuid]['links'] = [];
        }

        // If url is not already in array, add it.
        if (!in_array($url, $current_links[$uuid]['links'])) {
            array_push($current_links[$uuid]['links'], $url);
        }

        if (!isset($current_links[$uuid]['retries'])) {
            $current_links[$uuid]['retries'] = 0;
        }

        $current_links[$uuid]['retries'] = $retries;

        // Set site transient.
        set_site_transient($transient_key, $current_links, $transient_ttl);
    }

    /**
     * Executed on CRON, checks if any queued articles exists writes them to OC if so.
     *
     */
    function eae_scheduled_oc_update() {
        $transient_key = self::OC_WRITE_TRANSIENT;
        $write_data = get_site_transient($transient_key);

        // Transient data will be recreated when articles are published.
        // Remove it as soon at is retrieved so we dont run it multiple times.
        delete_site_transient($transient_key);

        // If no transient data, do nothing.
        if ($write_data === false) {
            return;
        }

        $oc_writer = new OcMetaWriter();
        foreach ($write_data as $uuid => $data) {
            $result = $oc_writer->update_metadata_xml_by_uuid($uuid, $data);
            $this->check_update_status($result, $uuid);
        }
    }

    /**
     * Checks if update to OC was successful, otherwise add object for retry.
     *
     * @param $result
     * @param $uuid
     * @param int $retries
     */
    function check_update_status($result, $uuid, $retries = 0) {

        if(isset($result['status']) && $result['status'] === 409) {

            if($retries <= self::OC_WRITE_MAX_RETRIES) {

                $post_id = OcUtilities::get_article_post_id_by_uuid($uuid);
                if(isset($post_id)) {

                    $url = get_permalink($post_id);
                    $retries = $retries + 1;
                    $this->update_transient_data($uuid, $url, $retries);
                }
            }
        }
    }
}