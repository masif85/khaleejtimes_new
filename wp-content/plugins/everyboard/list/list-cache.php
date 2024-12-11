<?php

class ListCache
{
    public static $CACHE_KEY = 'cache_boardlist_';
    private static $CACHE_TIME = 3600;

    public function get_list_cache($id)
    {
        return get_transient(self::$CACHE_KEY . $id);
    }

    public function delete_list_cache($id)
    {
        return delete_transient($id);
    }

    public function save_list_cache($id, $data)
    {
        return set_transient(self::$CACHE_KEY . $id, $data, self::$CACHE_TIME);
    }
}
