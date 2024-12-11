<?php

/**
 * This is basically just one 404 page.
 */

global $wp_query;
$wp_query->set_404();

status_header( 404 );
nocache_headers();
