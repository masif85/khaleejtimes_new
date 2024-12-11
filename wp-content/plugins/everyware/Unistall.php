<?php

//This file is responsible for cleaning up the WP-DB when the Everyware plugin is being Uninstalled

//If Uninstall is not called by WP, exit!
//Else clean up Options-table in WP-DB, for now we leave all the custom posts
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
} else {
	delete_option( 'oc_options' );
}