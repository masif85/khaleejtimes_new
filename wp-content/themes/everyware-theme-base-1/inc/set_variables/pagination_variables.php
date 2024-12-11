<?php 
	
/**
 * This file is where we are setting all of our variables used for the pagination.
 */

if (isset($_GET['start'])) {
	$start_value = (int)$_GET['start'];
	$start_set = isset($start_value);
	$page_number = $start_value / 10 + 1;
	$previous_link = $page_number - 1;
	$previous_start = $start_value - 10;
	$next_link = $page_number + 1;
	$next_start = $start_value + 10;
} else {
	$start_value = false;
	$start_set = false;
	$page_number = false;
	$previous_link = false;
	$previous_start = false;
	$next_link = false;
	$next_start = false;
}

$page_request = $_SERVER['REQUEST_URI'];
$page_request_noquery = strtok($_SERVER["REQUEST_URI"],'?');

?>