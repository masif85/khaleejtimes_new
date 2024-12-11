<?php

/***
 * Class OcCacheHelper
 *
 * Helper class for accessing the cache holding the information about witch queries a article is originated in.
 * This to be able to know what cache keys to look in when a update or delete event is pushed from OC notifier.
 *
 * The cache follows a structure such as 'uuid' => array( transientkey => expire time );
 */
class OcCacheHelper {
    
    /**
     * @var string
     */
    const OPTION_KEY = 'oc_cache_map';
    
    /**
     * @var string
     */
    const WIDGET_OPTION_KEY = 'oc_widget_cache_map';
    
    /**
     * @var string
     */
    const MAX_EXPIRE = 1209600;
    
    /**
     * @var OcCacheHelper
     */
    private static $instance;
    
    /**
     * @since 1.0.7
     * @return OcCacheHelper
     */
    public static function getInstance() {
        if( self::$instance === null ) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    /**
     * @param string $prefix
     * @param string $uuid
     *
     * @since 1.0.0
     * @return string
     */
    private function build_key( $prefix, $uuid ) {
        return implode( '_', [ $prefix, $uuid ] );
    }
    
    /**
     * @param string $uuid
     * @param string $key
     * @param int    $expire_time
     *
     * @since 1.0.7
     * @return void
     */
    public function add_to_cache_map( $uuid, $key, $expire_time ) {
        $this->internal_log("Will not add uuid: \"{$uuid}\" to cache map.");
        $uuid='';
        if( empty( $uuid ) || empty( $key ) ) {
            return;
        }
        
        $cache  = get_transient( $this->build_key( self::OPTION_KEY, $uuid ) );
        $expire = time() + $expire_time;
        
        if( ! is_array( $cache ) ) {
            $cache = [];
        }
        
        $cache[ $key ] = $expire;
        set_transient( $this->build_key( self::OPTION_KEY, $uuid ), $cache, $expire_time );
    }
    
    /**
     * @param string $uuid
     * @param string $key
     *
     * @since 1.0.7
     * @return void
     */
    public function remove_from_cache_map( $uuid, $key ) {
        $this->internal_log("Remove uuid: \"{$uuid}\" from cache map.");
        $cache = get_transient( $this->build_key( self::OPTION_KEY, $uuid ) );
        
        if( isset( $cache[ $key ] ) ) {
            unset( $cache[ $key ] );
            
            if( empty( $cache ) ) {
                delete_transient( $this->build_key( self::OPTION_KEY, $uuid ) );
                
                return;
            }
        }
        
        set_transient( $this->build_key( self::OPTION_KEY, $uuid ), $cache, self::MAX_EXPIRE );
    }
    
    /**
     * @param string $uuid
     *
     * @since 1.0.7
     * @return array
     */
    public function get_transient_keys_by_uuid( $uuid ) {
        $this->internal_log('Get transient keys for uuid: ' . $uuid);
        $ret = [];
        
        if( false !== ( $cache = get_transient( $this->build_key( self::OPTION_KEY, $uuid ) ) ) ) {
            $ret = $cache;
        }
        
        return $ret;
    }
    
    /**
     * @param string $uuid
     * @param string $key
     * @param int    $expire_time
     *
     * @since 1.0.7
     * @return void
     */
    public function update_cache_map( $uuid, $key, $expire_time ) {
        $this->internal_log('Will not update cache map for uuid: ' . $uuid);
        $uuid='';
        if( empty( $uuid ) || empty( $key ) ) {
            return;
        }
        
        $cache  = get_transient( $this->build_key( self::OPTION_KEY, $uuid ) );
        $expire = time() + $expire_time;
        
        if( isset( $cache[ $key ] ) ) {
            $cache[ $key ] = $expire;
        }
        
        set_transient( $this->build_key( self::OPTION_KEY, $uuid ), $cache, $expire_time );
        $this->clean_up_cache_map( $uuid );
    }
    
    /**
     * @param string $uuid
     *
     * @since 1.0.7
     * @return void
     */
    public function clean_up_cache_map( $uuid ) {
        $time_now = time();
        $this->internal_log('Clean up cache map for uuid: ' . $uuid);
        $cache = get_transient( $this->build_key( self::OPTION_KEY, $uuid ) );
        if( $cache ) {
            foreach ( $cache as $key => $expire ) {
                if( $time_now >= $expire ) {
                    $this->remove_from_cache_map( $uuid, $key );
                }
            }
        }
    }
    
    /**
     * @param string $key
     *
     * @since 1.0.7
     * @return void
     */
    public function create_widget_cache_buster( $key ) {
        $this->internal_log('Create widget cache_buster for: ' . $key);
        set_transient( $this->build_key( self::WIDGET_OPTION_KEY, $key ), true, 4 * WEEK_IN_SECONDS );
    }
    
    /**
     * @param string $uuid
     *
     * @since 1.0.7
     * @return void
     */
    public function clear_widget_cache_by_uuid( $uuid ) {
        $this->internal_log('Clear widget cache for uuid: ' . $uuid);
        $queries = $this->get_transient_keys_by_uuid( $uuid );
        
        foreach ( $queries as $cache_key => $expire ) {
            $widget_cache = get_transient( $this->build_key( self::WIDGET_OPTION_KEY, $cache_key ) );
            if( $widget_cache ) {
                delete_transient( OcAPI::TRANSIENT_KEY . '_' . $cache_key );
            }
        }
    }
    
    /**
     * @param string $key
     *
     * @since 1.0.7
     * @return void
     */
    public function remove_cache_buster_key( $key ) {
        $this->internal_log('Remove cache buster key: ' . $key);
        delete_transient( $this->build_key( self::WIDGET_OPTION_KEY, $key ) );
    }

    private function internal_log($log)
    {
        trigger_error($log);
    }
}
