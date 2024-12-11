<?php
    define( 'WP_USE_THEMES', false );
    $_SERVER[ 'HTTP_HOST' ] = $argv[2];
    require( dirname(__FILE__) . '/../../../../wp-load.php' );

    $queue = get_transient($argv[1]);
    if($queue) {

        $oc_api = new OcAPI();

        foreach($queue as $item) {

            $start = microtime(true);

            $oc_api->update_transient_cache( $item[0], $item[1] );


            global $ocanalyzer_request_id;
            $data = [];
            $data[1]['function'] = 'CLI cache_update';
            $data[1]['file'] = 'cache_update.php';
            $data[1]['args'][0] = $item[0];
            $data[1]['time'] = [0 => microtime(true) - $start];

            do_action('analyzer_add_data', ['name' => 'Cache update ', 'debug' => $data], 'CLI');

        }

        delete_transient($argv[1]);
    }