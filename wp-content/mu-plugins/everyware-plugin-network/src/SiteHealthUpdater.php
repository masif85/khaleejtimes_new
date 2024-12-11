<?php

declare(strict_types=1);

namespace Everyware\Plugin\Network;

class SiteHealthUpdater
{

    function removeHealthTests() {

//        //Loop through both arrays and unset
//        add_filter( 'site_status_tests', 'removeHealthTests' );
//        add_filter( 'site_status_tests', 'removeHealthTests' );
//        unset( $tests['async']['background_updates'] );
//        unset( $tests['direct']['php_extensions'] );
//        unset( $tests['aaaaa']['wooooooo'] );
//        return $tests;

        add_filter( 'site_status_tests', function ($tests) {
            unset( $tests['async']['background_updates'] );
            unset( $tests['direct']['php_extensions'] );
            unset( $tests['direct']['plugin_version'] );
            unset( $tests['direct']['wordpress_version'] );
            return $tests;
        } );
    }

}
