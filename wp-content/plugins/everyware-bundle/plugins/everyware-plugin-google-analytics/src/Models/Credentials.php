<?php namespace Everyware\Plugin\GoogleAnalytics\Models;

use Infomaker\Everyware\Support\GenericPropertyObject;

/**
 * Credentials
 *
 * @property string project_id
 * @property array  scopes
 * @property string client_id
 * @property string client_email
 * @link    http://infomaker.se
 * @package Everyware\Plugin\GoogleAnalytics\Models
 * @since   Everyware\Plugin\GoogleAnalytics\Models\Credentials 1.0.0
 */
class Credentials extends GenericPropertyObject {
    
    protected static $defaultCredentials = [
        'type'                        => '',
        'project_id'                  => '',
        'private_key_id'              => '',
        'private_key'                 => '',
        'client_email'                => '',
        'client_id'                   => '',
        'client_secret'               => '',
        'auth_uri'                    => '',
        'token_uri'                   => '',
        'auth_provider_x509_cert_url' => '',
        'client_x509_cert_url'        => '',
        'scopes'                      => [
            'https://www.googleapis.com/auth/analytics.readonly'
        ]
    ];
    
    public function __construct( $credentials = [] ) {
        $this->fill( array_replace( static::$defaultCredentials, (array)$credentials ) );
    }
}
