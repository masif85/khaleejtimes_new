<?php
echo parse_url();
exit;
$urls = "https://navstage.khaleejtimes.com/hindi/";	
		//$urls = str_replace("/", "-/", $urls);	
		$url=explode("/",strtolower($urls));
		print_r($url);
?>