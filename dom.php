<?php
require('simple_html_dom.php');

// Create DOM from URL or file
$html= file_get_html('https://www.coupon.ae/');
$main = $mains->find("coupon-listing-item"); 
$titles = $mains->find("h2.post-card-title"); 
$tags = $mains->find("span.post-card-tags"); 
$authors = $mains->find("a.meta-item"); 
 
// Iterate over the arrays contain the elements 
// and make use of the plaintext property to access only the text values 
foreach($main as $mains)
{
$data = array_map(function ($a1, $a2, $a3) { 
	$new = array( 
		"title" => trim($a1->plaintext), 
		"tag" => trim($a2->plaintext), 
		"author" => trim($a3->plaintext) 
	); 
	return $new; 
}, $titles, $tags, $authors); 
 
 }
print_r($data);