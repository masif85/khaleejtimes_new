<?php
/*
 * This file is used as a healthcheck for AWS loadbalancers, it checks that
 * PHP is working on the server and has the required extensions installed.
 */


$healthy = true;

// Check that we have redis support.
if(!class_exists('Redis')) {
    $healthy = false;
}

// Check that we have mysql support.
if(!function_exists('mysqli_connect') && !function_exists('mysql_connect')) {
    $healthy = false;
}

if($healthy) {
    http_response_code(200);
    print 'Healthy!';
}
else {
    http_response_code(500);
    print 'Not looking good.';
}
