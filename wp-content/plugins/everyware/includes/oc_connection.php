<?php

use Everyware\Contracts\OcObject as OcObjectInterface;
use Everyware\Helpers\OcProperties;
use Everyware\OcClient;
use Everyware\Storage\Contracts\SimpleCacheInterface;
use Everyware\Storage\OcObjectCache;
use Everyware\Storage\OcResponseCache;
use Everyware\Storage\SimpleCache;
use Everyware\Wordpress\TransientCache;
use GuzzleHttp\Exception\ClientException;

/**
 * Class OcConnection
 *
 * Class to enable communication with Open Content
 *
 * Use wrapping OcAPI class to interact with OC
 *
 * @author Infomaker Scandinavie AB
 *
 */
class OcConnection
{
    const TRANSIENT_KEY = 'ew_t';
    const TRANSIENT_EXPIRE_TIME = PHP_OB_CACHE_TTL;
    const DEFAULT_SORT_CONFIG_FILE_NAME = 'default_sort_options_config.json';
    const SORT_OPTIONS_CACHE_KEY = 'ew_sort_options';

    /**
     * @var int
     */
    protected $latestQueryTotalHits;

    /**
     * @var array
     */
    private $remote_args;

    /**
     * @var string
     */
    protected $oc_url;

    /**
     * @var string
     */
    private $oc_base_url;

    /**
     * @var array
     */
    private $return_array = [];

    /**
     * @var OpenContent
     */
    private $open_content;

    /**
     * @var SimpleCacheInterface
     */
    private $cache;

    /**
     * @var OcClient
     */
    private $client;

    /**
     * @var OcResponseCache
     */
    private $response_cache;

    /**
     * @var OcObjectCache
     */
    private $oc_object_cache;

    /**
     * @var array
     */
    private static $oc_sort_options;

    /**
     * Public getter for the OC Base URL
     *
     * @scope public
     *
     * @return null|string
     */
    public function getOcBaseUrl()
    {
        return $this->oc_base_url ?? null;
    }

    /**
     * Public getter for the full OC URL
     *
     * @scope public
     *
     * @return null|string
     */
    public function getOcUrl()
    {
        return $this->oc_url ?? null;
    }

    /**
     * Public setter for the OC URL
     *
     * @scope public
     *
     * @param string $url
     */
    public function setOcUrl($url)
    {
        $this->oc_url = $url;
    }

    /**
     * Public getter for the OC Auth
     *
     * @scope public
     *
     * @return null
     */
    public function getOcAuth()
    {
        return $this->oc_auth ?? null;
    }

    /**
     * Public getter for cache TTL
     *
     * @scope public
     *
     * @return int
     */
    public function getCacheTTL()
    {
        return $this->open_content->getTimeToCacheJson();
    }

    /**
     * Public getter to get the latest queries total hits
     *
     * @return int
     */
    public function getLatestQueryTotalHits()
    {
        return $this->latestQueryTotalHits;
    }

    /**
     * Public getter for remote args.
     *
     * @return array
     */
    public function getRemoteArgs()
    {
        return $this->remote_args;
    }

    /**
     * Public constructor
     *
     * @scope public
     *
     * @param OpenContent $oc
     * @param string      $query
     * @param string      $start
     * @param string      $limit
     * @param string      $sort
     */
    public function __construct(OpenContent $oc, $query = '', $start = '', $limit = '', $sort = '')
    {
        if ( ! empty($query)) {
            trigger_error('Oc_Connection is being used in a deprecated way, use OcApi instead', E_STRICT);
        }
        $this->open_content = $oc;
        $this->oc_base_url = $oc->getOcBaseUrl();
        $this->remote_args = [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($oc->getOcUserName() . ':' . $oc->getOcPassword())
            ],
            'timeout' => 20
        ];

        $this->client = $oc->get_client();
        $this->setup_cache();

        $this->oc_url = $this->prep_oc_query($query, $start, $limit, $sort);
    }

    /**
     * Private function called to sanitize and prepare the given query
     *
     * @scope private
     *
     * @param string $query
     * @param string $start
     * @param string $limit
     * @param string $sort
     *
     * @return null|string
     */
    protected function prep_oc_query($query = '', $start = '', $limit = '', $sort = '')
    {
        $prepared_query = $this->_build_query_string($query, $start, $limit, $sort, $url_encoded = true);
        $prepared_query = $this->append_search_properties($prepared_query);
        if ($prepared_query !== '') {
            return $this->open_content->getOcBaseUrl() . "search?{$prepared_query}";
        }

        return $this->open_content->getOcBaseUrl();
    }

    /**
     * Function to prepare a Suggest query
     *
     * @scope public
     *
     * @param string $selector
     * @param string $query
     * @param string $start
     * @param int    $limit
     *
     * @return void
     */
    public function prepare_oc_suggest_query($selector, $query, $start = '', $limit = 25)
    {
        if (null !== $selector) {
            $this->oc_url = $this->open_content->getOcBaseUrl() . "suggest?field={$selector}";
        }

        $prepared_query = $this->_build_query_string($query, $start, $limit, $sort = '', $url_encode = true);

        if ($prepared_query !== '') {
            $this->oc_url .= "&{$prepared_query}";
        }
    }

    /**
     * Function to build query string
     *
     * @scope    protected
     *
     * @param        $query
     * @param string $start
     * @param string $limit
     * @param string $sort
     * @param bool   $url_encoded
     *
     * @return string
     */
    protected function _build_query_string($query, $start, $limit, $sort, $url_encoded = false)
    {
    	// Temporarily switch to local timezone.
        if (get_option('timezone_string')) {
            $original_date_default_timezone = date_default_timezone_get();
            date_default_timezone_set(get_option('timezone_string'));
        }

        $prepared_query = '';

        if ($query !== '') {

            if (class_exists('EnvSettings')) {
                $env_settings = new EnvSettings();
                $env_query = $env_settings->get_env_query();

                if ($env_query !== '') {
                    $query .= ' ' . trim($env_query);
                }
            }

            $first_char = $query[0];

            if ($first_char === '/') {
                $query = substr($query, 1);
            }

            $url_start = '';
            if (null !== $start && $start !== '') {
                $url_start = "&start={$start}";
            }

            $url_limit = '';
            if (null !== $limit && $limit !== '') {
                $url_limit = "&limit={$limit}";
            }

            $url_sort = '';
            if ( null !== $sort && $sort !== '' ) {
                $url_sort = $this->get_oc_sort_query($sort);
            }

            /*
             * Shortcode Param replace segment
             */
            if (isset($_GET['date'])) {
                $date = wp_filter_nohtml_kses($_GET['date']);
            } else {
                $date = gmdate('Y-m-d');
            }
            $gm_start_date = gmdate("Y-m-d\TH:i:s\Z", strtotime($date . '00:00:00'));
            $gm_end__date = gmdate("Y-m-d\TH:i:s\Z", strtotime($date . '23:59:59'));

            $section = '* OR (*:* -ShopSection:*)';
            if (isset($_GET['section'])) {
                $section = '"' . wp_filter_nohtml_kses($_GET['section']) . '"';
            }

            $tags = '* OR (*:* -Tags:*)';
            if (isset($_GET['tags'])) {
                $tags = '"' . wp_filter_nohtml_kses($_GET['tags']) . '"';
            }

            $author = '* OR (*:* -Author:*)';
            if (isset($_GET['author'])) {
                $author = '"' . wp_filter_nohtml_kses($_GET['author']) . '"';
            }

            $text = '*';
            if (isset($_GET['q'])) {
                $text = '' . wp_filter_nohtml_kses($_GET['q']) . '';
            } else {
                if (isset($_GET['text'])) {
                    $text = '' . wp_filter_nohtml_kses($_GET['text']) . '';
                }
            }

            $date_from = '';
            $date_to = '';
            if (isset($_GET['date_from'], $_GET['date_to'])) {
                $date_from = wp_filter_nohtml_kses($_GET['date_from']);
                $date_to = wp_filter_nohtml_kses($_GET['date_to']);
            } else {
//                $field_pos = strpos( $query, '[param_date_interval]' );
                // TODO: Fix field mapping here so that updated is not hardcoded
                $query = preg_replace("/\"?\[param_date_interval\]\"?/i", '* OR (*:* -Pubdate:*)', $query);
            }

            global $oc_widget_inject_query;
            $inject_query = '';
            if (null !== $oc_widget_inject_query) {
                $inject_query = $oc_widget_inject_query;
            }

            global $oc_api_base_query;
            $inject_base_query = '';
            if (null !== $oc_api_base_query) {
                $inject_base_query = $oc_api_base_query;
            }

            // If a global parameter for start is set, use it instead.
            global $oc_widget_inject_start;
            if (null !== $oc_widget_inject_start) {
                $url_start = "&start={$oc_widget_inject_start}";
            }

            //Shortcode replace
            $query = preg_replace("/\"?\[param_date\]\"?/i", "[{$gm_start_date} TO {$gm_end__date}]", $query);
            $query = preg_replace("/\"?\[param_date_interval\]\"?/i",
                "[{$date_from}T00:00:00Z TO {$date_to}T23:59:59Z]", $query);
            $query = preg_replace("/\"?\[param_section\]\"?/i", $section, $query);
            $query = preg_replace("/\"?\[param_tags\]\"?/i", $tags, $query);
            $query = preg_replace("/\"?\[param_author\]\"?/i", $author, $query);
            $query = preg_replace("/\"?\[param_text\]\"?/i", $text, $query);
            $query = preg_replace("/\"?\[param_inject_query\]\"?/i", $inject_query, $query);
            $query = preg_replace("/\"?\[oc_api_base_query\]\"?/i", $inject_base_query, $query);

            if ($url_encoded) {
                $query = urlencode($query);
            }

            $prepared_query = "q={$query}" . $url_start . $url_limit . $url_sort;
        }

        /** @todo Uncomment this block once Twig (and other components that calculate dates) get their timezone
         *        from Wordpress instead of PHP.
         */
//        // Restore timezone if we changed it.
//        if (isset($original_date_default_timezone)) {
//            date_default_timezone_set($original_date_default_timezone);
//        }

        return $prepared_query;
    }

    /**
     * Function to get remote content, using build in WP remote_get functions
     *
     * @scope protected
     *
     * @param string $url
     *
     * @return null|string
     * @deprecated as of v1.8.0
     */
    protected function _get_remote_content($url)
    {
        $start = microtime(true);

        $api_response = wp_remote_get($url, $this->remote_args);

        if (is_wp_error($api_response)) {
            trigger_error($api_response->get_error_message());

            return null;
        }

        $http_status = (int)wp_remote_retrieve_response_code($api_response);
        $json_content = wp_remote_retrieve_body($api_response);

        if ($http_status === 200) {

            $debug = $this->get_debug_data_for_analyzer($start);

            do_action('analyzer_add_data', [
                'name' => 'Call from Oc_Connection',
                'debug' => $debug
            ], 'OC');

            return $json_content;
        }

        if ($http_status === 401) {

            $debug = $this->get_debug_data_for_analyzer($start);

            do_action('analyzer_add_data', [
                'name' => 'Call from Oc_Connection with 401 response code',
                'debug' => $debug
            ], 'OC_ERROR');

            return '401';
        }

        if ($http_status >= 400) {
            trigger_error(sprintf('Open Content API responded with Error: [Status: %s] - %s', $http_status,
                $json_content));
        }

        return null;
    }

    /**
     * @param int $start
     * @param int $backtrace
     *
     * @return array
     */
    protected function get_debug_data_for_analyzer($start = null, $backtrace = 20)
    {
        if (null === $start) {
            $start = microtime(true);
        }

        $data = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, $backtrace);
        $data[1]['time'] = [0 => microtime(true) - $start];

        return $data;

    }

    /**
     * function to get remote data, using build in WP remote_get functions
     *
     * @scope protected
     *
     * @param string $url
     *
     * @return null|string
     */
    protected function _get_remote_data($url)
    {
        $start = microtime(true);

        $api_response = wp_remote_get($url, $this->remote_args);

        if ( ! is_wp_error($api_response)) {
            $http_status = (int)wp_remote_retrieve_response_code($api_response);

            if ($http_status === 200) {
                $debug = $this->get_debug_data_for_analyzer($start);
                do_action('analyzer_add_data', [
                    'name' => 'Call from Oc_Connection',
                    'debug' => $debug
                ], 'OC');

                return $api_response;
            }
            if ($http_status === 401) {
                $debug = $this->get_debug_data_for_analyzer($start);
                do_action('analyzer_add_data', [
                    'name' => 'Call from Oc_Connection 401 data',
                    'debug' => $debug
                ], 'OC_ERROR');

                return '401';
            }
        }

        return null;
    }

    /**
     * Function to get json results from cache, if exists, else from Open Content and then store in cache
     *
     * @scope protected
     *
     * @param string $url
     * @param int    $cache_ttl
     *
     * @return null|string
     */
    protected function get_json_result($url = null, $cache_ttl = null)
    {
        try {
            $response_cache = $this->response_cache;

            if (is_int($cache_ttl) && $cache_ttl > 0) {
                $response_cache = new OcResponseCache($this->client, $this->cache, $cache_ttl);
            }

            return $response_cache->getContent($url ?? $this->oc_url);
        } catch (Exception $e) {
            @trigger_error($e->getMessage());
        }

        return null;
    }

    /**
     * Function to update a transients cache with content from Open Content.
     *
     * @param string   $url
     * @param string   $key
     * @param null|int $cache_ttl
     *
     * @return null|string
     *
     * @deprecated as of 1.8.0
     */
    public function update_transient_cache($url, $key, $cache_ttl = null)
    {
        $this->internal_log('Trying to update cache for url: ' . $url);

        return $this->get_json_result($url);
    }

    /**
     * Helper function to set transient
     *
     * @param string   $to_cache
     * @param string   $key
     * @param null|int $cache_ttl
     *
     * @return void
     *
     * @deprecated as of 1.8.0
     */
    private function set_transient_cache($to_cache, $key, $cache_ttl = null)
    {
        $this->internal_log('Trying to set cache for content: ' . $to_cache);
    }

    /**
     * @param array $response
     *
     * @return void
     *
     * @deprecated as of 1.8.0
     */
    public function update_transient_notifier_push($response)
    {
        $response_uuid = $response['uuid'];

        //check all keys that the uuid resides
        $transient_keys = OcCacheHelper::getInstance()->get_transient_keys_by_uuid($response_uuid);

        if (empty($transient_keys)) {
            return; // No Cache exists to update
        }
        $response_object = $this->format_single_properties(json_decode($response['response']));

        foreach ($transient_keys as $transient_key => $expire_time) {
            $json = json_decode(get_transient(self::TRANSIENT_KEY . '_' . $transient_key), true);

            if ( ! isset($json['contentType']) && ! isset($json['hits'])) {
                continue;
            }

            // If response for properties endpoint
            if (isset($json['contentType'])) {
                $this->set_transient_cache(json_encode($response_object, JSON_UNESCAPED_UNICODE), $transient_key);
                OcCacheHelper::getInstance()->update_cache_map($response_uuid, $transient_key,
                    self::TRANSIENT_EXPIRE_TIME);
                continue;
            }

            $object_found = false;

            foreach ((array)$json['hits']['hits'] as $hit_key => $hit) {
                if ($hit['id'] === $response_uuid) {
                    $object_found = true;
                    $json['hits']['hits'][$hit_key]['versions'][0]['properties'] = $this->get_oc_property_array($response_object);
                }
            }

            if ($object_found) {
                OcCacheHelper::getInstance()->update_cache_map($response_uuid, $transient_key,
                    self::TRANSIENT_EXPIRE_TIME);
            }

            $this->set_transient_cache(json_encode($json, JSON_UNESCAPED_UNICODE), $transient_key);
        }
    }

    /**
     * @param array $response
     *
     * @return void
     *
     * @deprecated as of 1.8.0
     */
    public function add_transient_notifier_push($response)
    {
        $key = md5($this->oc_base_url . "objects/{$response['uuid']}/properties");
        $response_object = $this->format_single_properties(json_decode($response['response']));

        $this->set_transient_cache(json_encode($response_object, JSON_UNESCAPED_UNICODE), $key);
        OcCacheHelper::getInstance()->add_to_cache_map($response['uuid'], $key, self::TRANSIENT_EXPIRE_TIME);
    }

    /**
     * @param string $uuid
     *
     * @return bool
     *
     * @deprecated as of 1.8.0
     */
    public function delete_transient_notifier_push($uuid)
    {
        $ret = false;
        $transient_keys = OcCacheHelper::getInstance()->get_transient_keys_by_uuid($uuid);

        if (count($transient_keys) > 0) {
            $ret = true;
            foreach ($transient_keys as $transient_key => $expire_time) {
                $json = json_decode(get_transient(self::TRANSIENT_KEY . '_' . $transient_key), true);

                if (isset($json['contentType'])) {

                    delete_transient(self::TRANSIENT_KEY . '_' . $transient_key);
                    delete_transient(self::TRANSIENT_KEY . '_' . $transient_key . '_expire');
                    OcCacheHelper::getInstance()->remove_from_cache_map($uuid, $transient_key);
                    continue;
                }

                if (isset($json['hits'])) {
                    foreach ((array)$json['hits']['hits'] as $hit_key => $hit) {

                        if ($hit['id'] === $uuid) {
                            unset($json['hits']['hits'][$hit_key]);
                            OcCacheHelper::getInstance()->remove_from_cache_map($uuid, $transient_key);
                        }
                    }

                    $this->set_transient_cache(json_encode($json, JSON_UNESCAPED_UNICODE), $transient_key);
                }
            }
        }

        return $ret;
    }

    /**
     * @param $property_object
     *
     * @return array
     */
    private function format_single_properties($property_object)
    {
        if (isset($property_object->properties)) {
            $property_object->properties = array_map(function ($prop) {
                return (object)[
                    'name' => $prop->name,
                    'values' => array_map(function ($value) {
                        return $this->format_single_properties($value);
                    }, $prop->values)
                ];
            }, $property_object->properties);
        }

        return $property_object;
    }

    /**
     * Function to clear cache on Widget
     *
     * @param $md5_key
     *
     * @return void
     *
     * @deprecated as of 1.8.0
     */
    public static function clear_widget_cache($md5_key = null)
    {
        if (null !== $md5_key) {
            $result = delete_transient(self::TRANSIENT_KEY . '_' . $md5_key);
            do_action('clear_widget_cache_hook', $result, $md5_key);
        }
    }

    /**
     * Function to update Widget cache
     * This function is called when OC Notifier tells us there has
     * been a CRUD event on an Widget query result
     * OR when a widget is saved
     * This function will flush existing cache and get new JSON from OC and store it in transient
     *
     * @param bool $oc_query
     *
     * @return void
     *
     * @deprecated as of 1.8.0
     */
    public function update_widget_cache($oc_query = false)
    {
        if ($oc_query === false) {
            //Widget updated, take action...
            $url = $this->oc_url;

        } else {
            //Notifier calling, take action...
            $url = $this->oc_base_url . 'search?' . $oc_query;
        }

        $key = md5($url);
        $this->update_transient_cache($url, $key);
    }

    /**
     * Function to get an array with articles from Open Content
     *
     * @param bool $create_cpt
     *
     * @return array
     *
     * @deprecated as of 1.8.0
     */
    public function get_oc_articles($create_cpt = true)
    {
        $result = $this->get_json_result();

        $this->return_array = [];

        if ($result !== null && $result !== '401') {
            $json_result = json_decode($result);
            $this->latestQueryTotalHits = isset($json_result->hits, $json_result->hits->totalHits) ? $json_result->hits->totalHits : 0;
            try {
                foreach ((array)$json_result->hits->hits as $object) {
                    $this->return_array[] = $this->create_oc_search_object($object->versions[0]->properties,
                        $create_cpt);
                }
            } catch (Exception $e) {
                trigger_error('Error in get_oc_articles - Foreach json->hits->hits, Exception: ' . $e->getTraceAsString(),
                    E_USER_ERROR);
            }
        }

        return $this->return_array;
    }

    /**
     * Function to get an array with ads from Open Content
     *
     * @return array
     *
     * @deprecated as of 1.8.0
     */
    public function get_oc_ads()
    {
        $result = $this->get_json_result();
        $this->return_array = [];

        if ($result !== null && $result !== '401') {
            $json_result = json_decode($result);
            try {
                foreach ((array)$json_result->hits->hits as $object) {
                    $this->return_array[] = $this->create_oc_search_object($object->versions[0]->properties);
                }
            } catch (Exception $e) {
                trigger_error('Error in get_oc_ads - Foreach json->hits->hits, Exception: ' . $e->getTraceAsString(),
                    E_USER_ERROR);
            }

        }

        return $this->return_array;
    }

    /**
     * Function to get an array with Images from Open Content
     *
     * @return array
     * @deprecated as of 1.8.0
     */
    public function get_oc_images()
    {
        $result = $this->get_json_result();
        $this->return_array = [];

        if ($result !== null && $result !== '401') {
            $json_result = json_decode($result);
            $this->latestQueryTotalHits = (isset($json_result->hits) && isset($json_result->hits->totalHits)) ? $json_result->hits->totalHits : 0;

            try {
                foreach ((array)$json_result->hits->hits as $object) {
                    $this->return_array[] = $this->create_oc_search_object($object->versions[0]->properties);
                }
            } catch (Exception $e) {
                trigger_error('Error in get_oc_images - Foreach json->hits->hits, Exception: ' . $e->getTraceAsString(),
                    E_USER_ERROR);
            }

        }

        return $this->return_array;
    }

    /**
     * Function to get full article with related images.
     *
     * @param string $article_id
     * @param array  $prop_arr
     *
     * @return array with an OcArticle and an Array with images
     */
    public function get_single_article($article_id, $prop_arr = null)
    {
        global $use_oc_cache;
        $this->return_array = [];

        $article = $this->get_single_object($article_id, $prop_arr ?? [], '', $use_oc_cache);

        if ($article instanceof OcArticle) {
            $this->return_array['article'] = $article;
            $image_uuids = $article->imageuuids ?? [];

            // Check old values for backwards compatibility
            if (empty($image_uuids)) {
                $image_uuids = array_filter((array)$article->get_value('imageuuid'));
            }

            $this->return_array['article_images'] = $image_uuids;
        }

        return $this->return_array;
    }

    /**
     * Create a nested array of formatted properties and values
     *
     * @param $object
     *
     * @return array
     * @since 1.7.0
     */
    protected function get_oc_property_array($object)
    {
        $property_object = $this->get_oc_property_object($object);

        if ( ! $property_object instanceof AbstractOcObject) {
            throw new RuntimeException('Failed to converting properties of object');
        }

        return $property_object->all_to_array();
    }

    /**
     * Factory-function for creating OcObjects from data fetched from Open Content properties
     *
     * @param $object
     *
     * @return AbstractOcObject
     * @since 1.5.0
     */
    public function get_oc_property_object($object)
    {
        $oc_object = $this->get_oc_object_by_type(isset($object->contentType) ? $object->contentType : '');

        foreach ((array)$object->properties as $prop) {
            if (is_array($prop)) {
                $prop = (object)$prop;
            }

            if (is_object($prop) && isset($prop->name)) {
                $oc_object->set($prop->name, $this->get_oc_property_object_values($prop->values));
            }
        }

        if ($oc_object instanceof OcArticle) {
            $this->create_article_post_type($oc_object);
        }

        return $oc_object;
    }

    /**
     * Extract property values from Object fetched from a property search
     *
     * @param array $prop_values
     *
     * @return array
     * @since 1.5.0
     */
    protected function get_oc_property_object_values(array $prop_values = [])
    {
        return array_map(function ($prop_value) {
            return is_object($prop_value) ? $this->get_oc_property_object($prop_value) : $prop_value;
        }, $prop_values);
    }

    /**
     * Function to instantiate a new OcObject depending on the contenttype
     *
     * @param string $type
     *
     * @return AbstractOcObject
     * @since 1.5.0
     */
    protected function get_oc_object_by_type($type = '')
    {
        $article_types = array_map('strtolower', $this->get_contenttypes_considered_articles());
        $lower_contenttype = strtolower(implode('', (array)$type));

        if (in_array($lower_contenttype, $article_types, true)) {
            return new OcArticle();
        }

        if ($lower_contenttype === 'ad') {
            return new OcAd();
        }

        if ($lower_contenttype === 'image') {
            return new OcImage();
        }

        return new \OcObject();
    }

    /**
     * Get object data from Open Content.
     *
     * @param string $uuid
     * @param array  $prop_arr
     * @param bool   $use_cache
     * @param string $filter
     *
     * @return null|string
     * @since 1.5.0
     */
    public function get_single_object_data($uuid, array $prop_arr = [], $use_cache = true, $filter = ''): ?string
    {
        try {
            $ocProperties = new OcProperties($prop_arr);

            if ($use_cache && ! $ocProperties->hasRelations()) {
                $object = $this->object_cache()->get($uuid);

                if( $object instanceof OcObjectInterface ) {
                    return $object->toJson();
                }
            }

            $path = "objects/{$uuid}/properties";
            $params = array_filter([
                'properties' => $ocProperties->toQueryString(),
                'filters' => $filter
            ]);

            $result = $use_cache ? $this->response_cache->getContent($path, $params) : $this->request_api($path, $params);

            if( ! empty($result) ) {
                return $result;
            }

        } catch (ClientException $e) {
            if ($e->getCode() !== 404) {
                if ($e->hasResponse()) {
                    trigger_error("OpenContent responded with {$e->getResponse()->getStatusCode()}: {$e->getResponse()->getBody()}", E_USER_WARNING);
                } else {
                    trigger_error($e->getMessage());
                }
            }
        }

        return null;
    }

    /**
     * @param string $uuid
     * @param array  $prop_arr
     * @param string $filter
     * @param bool   $use_cache
     *
     * @return \AbstractOcObject
     * @since 1.0.0
     */
    public function get_single_object($uuid, array $prop_arr = [], $filter = '', $use_cache = true)
    {
        $result = $this->get_single_object_data($uuid, $prop_arr, $use_cache, $filter);

        if ($result !== null && (int)$result !== 401) {
            $json_result = json_decode($result);

            return $this->get_oc_property_object($json_result);
        }

        return null;
    }

    /**
     * Function to set properties from OC property map
     *
     * @param $object
     *
     * @return void
     */
    public function add_mapped_properties($object)
    {
        $prop_map = OcUtilities::get_property_map();

        foreach ($prop_map as $key => $value) {
            $key = strtolower($key);
            $temp = $object->$value;
            $object->$key = $temp;
        }
    }

    /**
     * Function to get all image metadata
     *
     * @scope      public
     *
     * @param string $image_id
     * @param array  $prop_arr
     *
     * @return OcImage
     *
     * @deprecated as of 1.8.0
     */
    public function get_single_image_metadata($image_id, array $prop_arr = [])
    {
        $query = $this->oc_base_url . "objects/$image_id/properties";

        // If property array is set, add it's values to the query.
        if ( ! empty($prop_arr)) {
            $c = 0;
            foreach ($prop_arr as $prop) {

                if ($c === 0) {
                    $query .= '?name=' . $prop;
                } else {
                    $query .= '&name=' . $prop;
                }

                $c++;
            }
        }

        $result = $this->get_json_result($query);
        $image = new OcImage();

        if ($result !== null && $result !== '401') {
            $json_result = json_decode($result);

            if (isset($json_result->properties)) {
                foreach ((array)$json_result->properties as $prop) {
                    $name = strtolower($prop->name);
                    $val = $prop->values;
                    $image->$name = $val;
                }
            }
        }

        return $image;
    }

    /**
     * Function to be called by get_single_article to get related images
     *
     * @param string $article_id
     *
     * @return array
     *
     * @deprecated as of 1.8.0
     */
    private function _get_single_article_images($article_id)
    {
        $query = $this->oc_base_url . "objects/$article_id/relations";

        $result = $this->get_json_result($query);
        $uuid_arr = [];

        if ($result !== null && $result !== '401') {

            $json_result = json_decode($result);
            if (count($json_result->relations) > 0) {

                foreach ((array)$json_result->relations as $key => $value) {

                    if ($value->targetContentType === 'Image' && isset($value->uuids) && count($value->uuids) > 0) {

                        foreach ((array)$value->uuids as $uuid) {
                            $uuid_arr[] = $uuid;
                        }
                    }
                }
            }
        }

        return $uuid_arr;
    }

    /**
     * Function called by partial view to render image-src
     *
     * @scope public
     *
     * @param int    $image_uuid
     * @param string $image_size
     * @param bool   $source_file
     *
     * @return null|string
     */
    public function get_image($image_uuid = 0, $image_size = 'thumbnail', $source_file = false)
    {
        if ($image_uuid === 0) {
            return 'fail';
        }

        $path = "objects/{$image_uuid}";

        if ($source_file) {
            $path .= "/files/{$image_size}";
        }

        return $this->response_cache->getContent($path);
    }

    /**
     * Function called by article view to display image-ul
     *
     * @scope public
     *
     * @param int    $image_uuid
     * @param string $image_size
     * @param bool   $source_file
     *
     * @return string
     */
    public function get_image_url($image_uuid = 0, $image_size = 'thumbnail', $source_file = false)
    {
        if ($image_uuid === 0) {
            return 'fail';
        }

        if ( ! $source_file) {

            $query = $this->oc_base_url . "objects/$image_uuid/files/$image_size";
        } else {

            $query = $this->oc_base_url . "objects/$image_uuid";
        }

        return $query;
    }

    /**
     * @param array $params
     *
     * @return string
     */
    private function build_search_query(array $params)
    {
        $params = array_replace_recursive([
            'q' => '',
            'start' => 0,
            'limit' => 15,
            'contenttypes' => [],
            'properties' => [],
            'sort' => [],
            'sort.name' => null,
            'sort.indexfield' => null,
            'facet' => null,
            'facet.limit' => null,
            'facet.mincount' => null,
            'facet.date.mincount' => null,
        ], $params);

        // We create our own parameters from these arrays
        $properties = new OcProperties($this->get_search_properties($params['properties']));
        $contenttypes = (array)$params['contenttypes'];
        $sort = (array)$params['sort'];
        $sort_name = $params['sort.name'];unset($params['properties'], $params['contenttypes'], $params['sort'], $params['sort.name']);

        if ( ! $properties->empty()) {

            // Add "contenttype" to the properties as it is needed by the function.
            $properties->add('contenttype');

            // Convert to new OpenContent property syntax
            $params['properties'] = $properties->toQueryString();
        }
        $env_settings = new EnvSettings();
        if ($env_settings->get_use_trashed()) {
            $params['q'] .= ' AND NOT EveryTrashed:"' . OcUtilities::get_site_shortname() . '"';
        }

        // Remove q-parameter if no query has been made
        $params['q'] = $params['q'] !== '' ? $params['q'] : null;

        // Convert facet-parameter if set
        if (isset($params['facet'])) {
            $params['facet'] = $params['facet'] ? 'true' : 'false';
        }

        $query_string = http_build_query($params);

        // Add contenttypes if specified
        foreach ($contenttypes as $contenttype) {
            $query_string .= "&contenttype={$contenttype}";
        }

        if($sort_name !== null){
            $query_string .= $this->get_oc_sort_query($sort_name);
        }

        // Add multiple sort orders if specified
        if (count($sort) > 0) {
            foreach ($sort as $field) {
                if ( ! array_key_exists('indexfield', $field)) {
                    continue;
                }
                $indexfield = $field['indexfield'];
                $query_string .= "&sort.indexfield={$indexfield}";
                if (isset($field['ascending'])) {
                    $query_string .= "&sort.{$indexfield}.ascending={$field['ascending']}";
                }
            }
        }

        return "{$this->oc_base_url}search?{$query_string}";
    }

    /**
     * Function to get free text search from OC
     *
     * @scope      public
     *
     * @param string     $search_text
     * @param array      $include_array
     * @param string     $sort
     * @param int        $facet_limit
     * @param int        $facet_mincount
     * @param int|string $start
     * @param int|string $limit
     * @param bool       $create_article
     * @param array      $prop_arr
     * @param bool       $use_cache
     * @param null       $use_facet
     *
     * @return array
     *
     * @deprecated as of 1.8.0
     */
    public function text_search(
        $search_text,
        $include_array,
        $sort = '',
        $facet_limit = null,
        $facet_mincount = null,
        $start = '',
        $limit = '',
        $create_article = true,
        $prop_arr = [],
        $use_cache = false,
        $use_facet = null
    ) {

        // Redirect to new search function
        return $this->search([
            'q' => $search_text,
            'contenttypes' => $include_array,
            'sort.name' => $sort,
            'facet' => $use_facet,
            'facet.limit' => $facet_limit,
            'facet.mincount' => $facet_mincount,
            'facet.date.mincount' => $facet_mincount,
            'start' => $start,
            'limit' => $limit,
            'properties' => $prop_arr
        ], $use_cache, $create_article);
    }

    /**
     * New function for using search against Open Content
     *
     * @param array $params
     * @param bool  $use_cache
     * @param bool  $create_article
     * @param int   $cache_ttl
     *
     * @return array|string
     */
    public function search(array $params = [], $use_cache = true, $create_article = true, $cache_ttl = null)
    {
        $this->return_array = [];

        $query = $this->_encode_query($this->build_search_query($params));

        return $this->get_search_result($query, $use_cache, $create_article, $cache_ttl);
    }

    /**
     * @param string $query
     * @param bool   $use_cache
     * @param bool   $create_article
     * @param int    $cache_ttl
     *
     * @return array
     * @since 1.5.0
     */
    private function get_search_result($query, $use_cache, $create_article, $cache_ttl = null)
    {
        $result = $this->get_json_result($query, $cache_ttl);

        if ($result !== null && $result !== '401') {
            $json_result = json_decode($result);
            $this->return_array['duration'] = $json_result->stats->duration;
            $this->return_array['hits'] = $json_result->hits->totalHits;
            $this->return_array['facet'] = $json_result->facet;

            foreach ((array)$json_result->hits->hits as $object) {
                $this->return_array[] = $this->create_oc_search_object($object->versions[0]->properties,
                    $create_article);
            }
        }

        return $this->return_array;
    }

    /**
     * Function to get Sections from Open Content
     *
     * @return array
     */
    public function get_oc_suggest()
    {
        $result = $this->get_json_result();
        $return_array = [];

        if ($result !== null && $result !== '401') {
            $json_result = json_decode($result);
            try {
                foreach ((array)$json_result->facetFields[0]->terms as $section) {
                    $return_array[] = $section;
                }
            } catch (Exception $e) {
                trigger_error('Error in get_oc_suggest - Foreach json_result->facetFields->terms, Exception: ' . $e->getTraceAsString(),
                    E_USER_ERROR);
            }

        }

        return $return_array;
    }

    /**
     * @param array $prop_values
     *
     * @return array
     * @since 1.5.0
     */
    protected function get_oc_search_object_values(array $prop_values = [])
    {
        return array_map(function ($prop_value) {
            return is_object($prop_value) ? $this->create_oc_search_object($prop_value) : $prop_value;
        }, $prop_values);
    }

    /**
     * Function to create OC-Objects from the search-response out of data from Open Content
     *
     * @param      $object
     * @param bool $create_cpt
     *
     * @return AbstractOcObject
     * @since 1.5.0
     */
    protected function create_oc_search_object($object, $create_cpt = true)
    {
        $oc_object = $this->get_oc_object_by_type($object->contenttype ?? '');
        foreach ((array)$object as $name => $val) {
            $oc_object->set($name, $this->get_oc_search_object_values($val ?? []));
        }

        if ($oc_object instanceof OcArticle) {
            $this->create_article_post_type($oc_object, $create_cpt);
        }

        return $oc_object;
    }

    /**
     * Function to create articles in Wordpress if they meet the requirements
     *
     * @param OcArticle $article
     * @param bool      $create_cpt
     *
     * @return void
     * @since 1.5.0
     */
    protected function create_article_post_type(OcArticle $article, $create_cpt = true)
    {
        $article->set_mapped_properties();
        if ($create_cpt && $this->article_can_be_created($article)) {
            // Create a new Custom post type from the article object
            new OcArticleCustomPostFactory($article);
        }
    }

    /**
     * Function to get OC Sort options.
     *
     * Will first try to get it from any registered override, then from OC and if that fails, it will get it from the default config in this package.
     *
     * @scope public
     *
     * @return array
     */
    public function get_oc_sort_options() {
        if(self::$oc_sort_options){
            return self::$oc_sort_options;
        }

        $sorting_options = $this->get_oc_sort_options_from_override();
        if(!$sorting_options){
            $sorting_options = $this->get_oc_sort_options_from_oc();
        }
        if(!$sorting_options) {
            $sorting_options = $this->get_oc_sort_options_from_default();
        }

        self::$oc_sort_options = $sorting_options;
        return $sorting_options;
    }

    /**
     * Function that will try to get sort config using the path supplied in the ocSortConfigPath filter.
     *
     * @return array | null
     */
    private function get_oc_sort_options_from_override(){
        $config_override_path = apply_filters('ew_oc_sort_config_path', '');
        $sort_data = null;
        if(!file_exists($config_override_path)){
            return $sort_data;
        }
        try{
            $sort_data = json_decode(file_get_contents($config_override_path), false);
        }catch (Exception $exception){
            if (\extension_loaded('newrelic')) {
                newrelic_notice_error(null, $exception);
            }
           error_log((string)$exception);
        }
        return $sort_data;
    }

    /**
     * Function that will try to get sort config from Open Content.
     *
     * @return array | null
     */
    private function get_oc_sort_options_from_oc(){
        $sort_data = null;
        $cached_sort_data = get_transient( self::SORT_OPTIONS_CACHE_KEY );
        if($cached_sort_data === false){
            $query = $this->oc_base_url . 'sortings';
            $result = $this->_get_remote_content( $query );
            if ( $result !== null && $result !== '401' ) {
                $sort_data = json_decode($result, false);
            }

            set_transient( self::SORT_OPTIONS_CACHE_KEY, $sort_data, 300 );
        } else {
            $sort_data = $cached_sort_data;
        }
        return $sort_data;
    }

    /**
     * Function that will get the default sort config.
     *
     * @return array
     */
    private function get_oc_sort_options_from_default(){
        $config_path = str_replace('includes/', self::DEFAULT_SORT_CONFIG_FILE_NAME, plugin_dir_path(__FILE__));
        return json_decode(file_get_contents($config_path), false);
    }

    /**
     * Returns the sort option data for the given sort option name.
     *
     * @param string $sort_option_name
     * @return stdClass Object | null
     */
    public function get_oc_sort_option( $sort_option_name ) {
        $sortings = $this->get_oc_sort_options()->sortings;
        foreach($sortings as $sorting){
            if($sorting->name === $sort_option_name){
                return $sorting;
            }
        }
        return null;
    }

    /**
     * Returns the default sorting option.
     *
     * Will first try to get it from any registered override. If there is no override, it will take the first sorting option from OC.
     * If it's unable to get any sorting options from OC, it will use the default found in the sorting config of this package.
     *
     * @return string
     */
    public function get_default_oc_sort_option() {
        $availableSortOptions = $this->get_oc_sort_options();
        $defaultSortOption = $availableSortOptions->defaultSorting ?? null;

        if(!$defaultSortOption){
            $defaultSortOption = $availableSortOptions->sortings[0]->name ?? '';
        }

        return $defaultSortOption;
    }

    /**
     * Will return the given argument if it's a sort option that's available in the current sort config. If not, it will return the default.
     *
     * @param string $sortOption
     * @return string
     */
    protected function validateSortOption($sortOption){
        $availableSortOptions = $this->get_oc_sort_options();
        foreach ($availableSortOptions->sortings as $availableSortOption){
            if($availableSortOption->name === $sortOption){
                return $sortOption;
            }
        }
        return $this->get_default_oc_sort_option();
    }

    /**
     * Returns a sort query for adding to oc-queries.
     *
     * @param string $sort_option_name
     * @return string
     */
    public function get_oc_sort_query( $sort_option_name ){
        $sort_data = $this->get_oc_sort_option($sort_option_name);
        if($sort_data === null){
            return '';
        }

        $sort_query = '';
        foreach ($sort_data->sortIndexFields as $sortIndexField){
            if($sortIndexField->indexField !== null & $sortIndexField->ascending !== null){
                $sort_query .= "&sort.indexfield={$sortIndexField->indexField}&sort.{$sortIndexField->indexField}.ascending={$sortIndexField->ascending}";
            }
        }

        return $sort_query;
    }

    /**
     * Function to test oc query with Ajax call
     * TODO: Use new filter when adding articles?
     * @scope public
     *
     * @return array
     */
    public function ajax_test_query()
    {
        $result = $this->get_json_result($this->oc_url);
        $this->return_array = [];

        if ($result !== null) {

            $json_result = json_decode($result);
            $hits = $json_result->hits;

            //If we get a response from OC but no hits
            if ($hits->totalHits === 0) {
                $this->return_array['No Matches'] = __('Given query returns no matching objects from Open Content');
            } else {
                foreach ((array)$hits->hits as $object) {
                    switch (strtolower($object->versions[0]->properties->contenttype[0])) {
                        case 'ad':
                            ! isset($this->return_array['Ads']) ? $this->return_array['Ads'] = 0 : null;
                            $this->return_array['Ads']++;
                            break;
                        case 'article':
                            ! isset($this->return_array['Articles']) ? $this->return_array['Articles'] = 0 : null;
                            $this->return_array['Articles']++;
                            break;
                        case 'image':
                            ! isset($this->return_array['Images']) ? $this->return_array['Images'] = 0 : null;
                            $this->return_array['Images']++;
                            break;
                        case 'page':
                            ! isset($this->return_array['Pages']) ? $this->return_array['Pages'] = 0 : null;
                            $this->return_array['Pages']++;
                            break;
                        case 'video':
                            ! isset($this->return_array['Video']) ? $this->return_array['Video'] = 0 : null;
                            $this->return_array['Video']++;
                            break;
                        default:
                            ! isset($this->return_array['Others']) ? $this->return_array['Others'] = 0 : null;
                            $this->return_array['Others']++;
                            break;
                    }
                }
            }

        } elseif ($result === '401') //Not authorized
        {
            $this->return_array['Authentication'] = __('Invalid Open Content credentials. Check Open Content Settings');
        } else //No response from OC or Null object is returned
        {
            $this->return_array['No Matches'] = __('No Response');
        }

        return $this->return_array;
    }

    /**
     * Function to get Content types with Ajax (uses indexfield as value).
     *
     * @scope public
     *
     * @return array
     */
    public function ajax_get_content_types()
    {
        $this->return_array = [];
        $contenttypes = [];

        try {
            $contenttypes = $this->get_contenttypes();
        } catch (RuntimeException $e) {
            $this->return_array['Authentication'] = __('Invalid Open Content credentials. Check "Settings - Open Content"');
        }

        if (empty($contenttypes)) {
            $this->return_array['No Matches'] = __('No Content types found in Open Content');
            return $this->return_array;
        }

        foreach ($contenttypes as $content_type) {
            $properties = [];

            foreach ((array)$content_type->properties as $property) {
                $properties[] = $property->indexFields;
            }

            $this->return_array[$content_type->name] = $properties;
        }

        return $this->return_array;
    }

    /**
     * Function to get Content types and use property name as value.
     *
     * @param array $types
     *
     * @return array
     * @since 1.5.0
     */
    public function get_content_types_properties(array $types = [])
    {
        $this->return_array = [];
        $contenttypes = [];

        try {
            $contenttypes = $this->get_contenttypes();
        } catch (RuntimeException $e) {
            $this->return_array['Authentication'] = __('Invalid Open Content credentials. Check "Settings - Open Content"');
        }

        if (empty($contenttypes)) {
            $this->return_array['No Matches'] = __('No Content types found in Open Content');
            return $this->return_array;
        }

        $filter_properties = ! empty($types);

        foreach ($contenttypes as $content_type) {
            $properties = [];

            foreach ((array)$content_type->properties as $property) {
                if ( ! $filter_properties || in_array(strtolower($property->type), $types, true)) {
                    $properties[] = $property->name;
                }
            }

            $this->return_array[$content_type->name] = $properties;
        }

        return $this->return_array;
    }

    /**
     * Function to get Suggest results with AJAX
     *
     * @scope public
     *
     * @param string $field
     * @param string $q
     * @param string $incompleteWord
     * @param string $incompleteWordInText
     *
     * @return null|string
     */
    public function ajax_get_suggest($field, $q, $incompleteWord, $incompleteWordInText)
    {
        $query_str = 'suggest/';
        if ($field !== null) {
            $query_str = add_query_arg('field', urlencode($field), $query_str);
        }

        // TODO: This one is causing all kind of problems. Check if it is needed.
        /*if ( $q !== null && $field !== null ) {
            $query_str = add_query_arg( 'q', urlencode( $field . ':' . $q ), $query_str );
        } else*/
        if ($q !== null) {
            $query_str = add_query_arg('q', urlencode($q), $query_str);
        }

        if ($incompleteWord !== null) {
            $query_str = add_query_arg('incompleteWord', urlencode($incompleteWord), $query_str);
        }

        if ($incompleteWordInText !== null) {
            $query_str = add_query_arg('incompleteWordInText', urlencode($incompleteWordInText), $query_str);
        }

        $this->oc_url = $this->open_content->getOcBaseUrl() . $query_str;

        $result = $this->get_json_result($this->oc_url);

        if ($result !== null && $result !== '401') {
            return $result;
        }

        die();
    }

    /**
     * Function to url encode parameter values in a URL string
     * TODO Can we make this function more efficient?
     *
     * @scope private
     *
     * @param string $query
     *
     * @return string
     */
    private function _encode_query($query)
    {
        $startpos = stripos($query, '?');
        $base = substr($query, 0, $startpos + 1);

        $query_str = substr($query, $startpos + 1);
        $query_str = explode('&', $query_str);

        //TODO What is happening here?
        foreach ($query_str as &$value) {
            $tmp = explode('=', $value);

            if (isset($tmp[1])) {
                $tmp[1] = urlencode(urldecode($tmp[1]));
            }
            $value = implode('=', $tmp);
        }

        $encoded_url = implode('&', $query_str);
        $encoded_url = $base . $encoded_url;

        return $encoded_url;
    }

    /**
     * Get search query for given parameters.
     *
     * @param array $params
     *
     * @return string
     */
    public function get_search_query(array $params = [])
    {
        return $this->_encode_query($this->build_search_query($params));
    }

    /**
     * Function to get the health status of the bound OpenContent
     * @return mixed
     */
    public function get_oc_health()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->oc_base_url . 'health');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_exec($ch);
        $response = curl_getinfo($ch);
        curl_close($ch);

        return $response;
    }

    /**
     * Determine if the value can be considered an article
     *
     * @param $article
     *
     * @return bool
     */
    protected function is_article($article)
    {
        if ($article instanceof OcArticle) {
            $article_types = array_map('strtolower', $this->get_contenttypes_considered_articles());

            return in_array(strtolower($article->get_value('contenttype')), $article_types, true);
        }

        return false;
    }

    /**
     * Add requested properties in the form of query-parameters
     *
     * @param string $query
     *
     * @return string
     * @since 1.5.0
     */
    protected function append_search_properties($query)
    {
        $properties = $this->get_search_properties();

        if ( ! empty($properties)) {
            foreach ($properties as $property) {
                $query .= "&property={$property}";
            }
        }

        return $query;
    }

    /**
     * Retrieve properties to request from Open Content.
     *
     * @param array $properties
     *
     * @return array
     * @since 1.5.0
     */
    protected function get_search_properties(array $properties = [])
    {
        // Add default properties if no properties has been specified
        if (empty($properties)) {
            $properties = array_filter((array)apply_filters('ew_apply_default_search_properties', []));
        }

        // Make sure required properties are applied if properties has been requested.
        if ( ! empty($properties)) {
            $properties = $this->append_required_properties($properties);
        }

        return array_unique($properties);
    }

    /**
     * Add required properties if properties has been specified
     *
     * @param array $properties
     *
     * @return array
     * @since 1.5.0
     */
    protected function append_required_properties(array $properties = [])
    {
        if (empty($properties)) {
            return [];
        }

        $required_oc_properties = array_filter([
            'contenttype',
            'uuid',
            $this->get_required_permalink_property()
        ]);

        // Merge and remove duplicates
        $properties = array_unique(array_merge($properties, $required_oc_properties));

        $required_properties = array_filter((array)apply_filters('ew_apply_required_search_properties', $properties));

        return $required_properties;
    }

    /**
     * Fetch and map the property required for creating an articles permalink
     *
     * @return string
     * @since 1.5.0
     */
    protected function get_required_permalink_property()
    {
        static $required_property;

        if ($required_property !== null) {
            return $required_property;
        }

        $cache_key = $this->generate_cache_key('required_permalink_property');

        // Fetch from cache
        if (($required_property = get_transient($cache_key)) !== false) {
            return $required_property;
        }

        $required_property = OpenContent::getInstance()->getSlugProperties()[0];
        $article_properties = $this->get_contenttype_properties('Article');
        $property_exists = in_array($required_property, $article_properties, true);

        // If no property was found and a property map exists
        if ( ! $property_exists && (($property_map = get_option('prop_map')) !== false)) {
            $mapped_property = isset($property_map[$required_property]) ? $property_map[$required_property] : 'unknown';

            // Make sure that a property is mapped and not the property we already have
            if ($mapped_property !== 'unknown' && strtolower($mapped_property) !== strtolower($required_property)) {

                foreach ($article_properties as $prop) {

                    // Get the real name of the property and require it
                    if (strtolower($mapped_property) === strtolower($prop)) {
                        $required_property = $prop;
                    }
                }
            }
        }

        // Save property and return it
        set_transient($cache_key, $required_property, self::TRANSIENT_EXPIRE_TIME);

        return $required_property;
    }

    /**
     * Get the contenttypes that should be considered articles.
     *
     * @return array
     */
    protected function get_contenttypes_considered_articles()
    {
        static $article_contenttypes;

        if ($article_contenttypes !== null) {
            return $article_contenttypes;
        }

        $article_contenttypes = array_map('strtolower',
            apply_filters('ew_contenttypes_considered_article', ['article']));

        return $article_contenttypes;
    }

    /**
     * Check if an OcArticle has what it takes for an article post type to be created in Wordpress
     *
     * @param OcArticle $article
     *
     * @return bool
     * @since 1.5.0
     */
    protected function article_can_be_created(OcArticle $article)
    {
        $properties = $this->append_required_properties(['contenttype', 'uuid']);
        $article_properties = $article->get_all_properties();

        // Walk through all required properties to see if we have what it takes
        $missing_values = array_filter($properties, function ($property) use ($article_properties) {
            return ! array_key_exists(strtolower($property), $article_properties);
        });

        return empty($missing_values);
    }

    /**
     * Helper function fetch all properties on given contenttype
     *
     * @param string $contenttype
     * @param array  $types
     *
     * @return array
     * @since 1.5.0
     */
    public function get_contenttype_properties($contenttype, array $types = [])
    {
        $oc_properties = $this->get_content_types_properties($types);

        if (isset($oc_properties[$contenttype])) {
            return array_filter($oc_properties[$contenttype]);
        }

        return [];
    }

    public function object_cache(): OcObjectCache
    {
        return $this->oc_object_cache;
    }

    /**
     * Function to generate a transient key
     *
     * @param string $suffix
     *
     * @return string
     *
     * @deprecated as of 1.8.0
     */
    public function generate_cache_key($suffix = '')
    {
        return self::TRANSIENT_KEY . "_{$suffix}";
    }

    public function request_api($path, $params): string
    {
        return $this->client->getContent($path, $params) ?? '';
    }

    public function get_api_response_ttl(): int
    {
        return $this->open_content->getTimeToCacheJson() ?? 30;
    }

    public function get_object_cache_ttl()
    {
        if (defined('PHP_OB_CACHE_TTL')) {
            return PHP_OB_CACHE_TTL;
        }

        return 60 * 60; // 1 hour
    }

    private function internal_log($log): void
    {
        trigger_error($log);
    }

    /**
     * Gets the json response from the Open Content contenttype endpoint, using a cached response if it's available.
     *
     * @return array
     * @throws RuntimeException
     *
     */
    protected function get_contenttypes(): array
    {
        $result = $this->get_json_result($this->oc_base_url . 'contenttypes');

        if ($result === null || empty($result) ) {
            return [];
        }

        $result = json_decode($result, false);

        return $result->contentTypes ?? [];
    }

    private function setup_cache(): void
    {
        $simpleCache = new SimpleCache(new TransientCache());

        $this->oc_object_cache = new OcObjectCache($this->client, $simpleCache, $this->get_object_cache_ttl());
        $this->response_cache = new OcResponseCache($this->client, $simpleCache, $this->get_api_response_ttl());
        $this->cache = $simpleCache;
    }
}
