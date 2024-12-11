<?php

/**
 * Class OcMetaWriter
 */
class OcMetaWriter {

    // Class properties.
    private $metadata_filename  = 'everyware-wp-meta-0.1.xml';
    private $metadata_mimetype  = 'everyware/wp-meta';
    private $edit_user_exists   = false;

    function __construct() {

        if( defined( 'OC_EDIT_USER' ) && defined( 'OC_EDIT_PASSWORD' ) ) {
            $this->edit_user_exists = true;
        }
    }

    /**
     * Sends the XML to Open Content for indexing.
     * @param $post
     * @return bool
     */
    public function update_metadata_xml( $post ) {

        // Check that user with edit priveliges exists otherwise do nothing.
        if( $this->edit_user_exists ) {

            $uuid   = OcUtilities::get_uuid_by_post_id( $post->ID );
            $xml    = $this->generate_metadata_xml_for_post( $post, $uuid );
            $result = $this->put_metadata_xml( $uuid, $xml );

            return $result;
        }
        else {

            return false;
        }
    }

    public function update_metadata_xml_by_uuid( $uuid, $write_data, $article_data = null ) {
        if( $this->edit_user_exists ) {
            $xml = $this->generate_metadata_xml_for_uuid( $uuid, $write_data, $article_data );

            if( $xml !== false ) {
                $result = $this->put_metadata_xml( $uuid, $xml );
                return $result;
            }
        }

        return false;
    }

    /**
     * Looks at the given WP Article and generates an XML for it.
     * @param $post
     * @param $uuid
     * @return string
     */
    private function generate_metadata_xml_for_post( $post, $uuid ) {

        $site_shortname = OcUtilities::get_site_shortname();
        $post_status    = $post->post_status;
        $link           = get_permalink( $post->ID );
        $current_xml    = $this->get_current_metadata_xml( $uuid );
        $trashed        = $post_status === 'trash' ? $site_shortname : 'false';

        if( isset( $current_xml['body'] ) ) { // If metadata file already exists in OC.

            $xml = simplexml_load_string( $current_xml['body'] );
            foreach( $xml->site as $site ) {

                // Check if current site already exists in the XML.
                if( isset( $site['shortname'] ) && (string)$site['shortname'] === $site_shortname ) {

                    // Delete it if it exists.
                    $dom = dom_import_simplexml( $site );
                    $dom->parentNode->removeChild( $dom );
                    break;
                }
            }
        }
        else { // If metadata does not exist, create a new one.
            $xml = new SimpleXMLElement( '<sites/>' );
        }

        $site = $xml->addChild( 'site' );
        $site->addAttribute( 'shortname', $site_shortname );
        $site->addChild( 'trashed', $trashed );
        $site->addChild( 'url', $link );
        return $xml->asXML();
    }

    /**
     * Takes write data from parameter and adds data to uuid XML in OC.
     * @param $uuid
     * @param $write_data
     * @return string
     */
    private function generate_metadata_xml_for_uuid( $uuid, $write_data, $article_data = null ) {

        $site_shortname = '';
        $link           = '';
        $current_xml    = $this->get_current_metadata_xml( $uuid );
        $trashed        = 'false';

        if( isset( $current_xml['body'] ) ) { // If metadata file already exists in OC.
            $xml = simplexml_load_string( $current_xml['body'] );
        } else { // If metadata does not exist, create a new one.
            $xml = new SimpleXMLElement( '<sites/>' );
        }

        if( isset( $write_data['links'] ) ) {

            if( $xml->urls[0] !== null ) {
                $links = $xml->urls;
                $links_to_remove = [];
                foreach( $links->url as $link ) {

                    $xml_link = (string)$link;
                    $url_data = parse_url( $xml_link );

                    if( $url_data !== false && isset( $url_data['host'] ) ) {

                        $temp_links = $write_data['links'];
                        $indexes = $this->substr_arr_get_keys( $url_data['host'], $temp_links );
                        if( count( $indexes ) > 0 ) {

                            foreach ($indexes as $index) {
                                unset( $temp_links[$index] );
                            }

                            $links_to_remove[] = $link;
                        }
                    }
                }

                foreach($links_to_remove as $item) {
                    unset($item[0]);
                }
            }
            else {
                $links = $xml->addChild( 'urls' );
            }

            foreach( $write_data['links'] as $link ) {
                $links->addChild( 'url', htmlspecialchars( $link ) );
            }
        }

        $xml_stack = [];
        #$xml_stack = apply_filters('ew_writeback_custom_data', $xml_stack, $uuid, $article_data);

        // If we have custom data
        if( count($xml_stack) > 0 ) {
            $dom = dom_import_simplexml($xml);
            $obj_node = $dom->getElementsByTagName('object')->item(0);

            if( ! is_null($obj_node) ) {
                $dom->removeChild( $obj_node );
            }

            $obj = $xml->addChild('object');
            $data = $obj->addChild("data");

            $to_node = dom_import_simplexml($data);

            foreach( $xml_stack as $custom_xml ) {
                if( $custom_xml instanceof SimpleXMLElement ) {
                    $custom_node = dom_import_simplexml($custom_xml);
                    $to_node->appendChild($to_node->ownerDocument->importNode($custom_node, true));
                }
            }
        }

        return md5($current_xml['body']) === md5($xml->asXML()) ? false : $xml->asXML();
    }

    /**
     * Get the current metadata file for the given object.
     * @param $uuid
     * @return array
     */
    private function get_current_metadata_xml( $uuid ) {

        $path = 'objects/' . $uuid . '/files/' . $this->metadata_filename;
        return $this->perform_oc_request( $path );
    }

    /**
     * Update metadata file in OC.
     * @param $uuid
     * @param $xml
     * @return mixed
     */
    private function put_metadata_xml( $uuid, $xml ) {

        $path = 'objects/' . $uuid . '/files/metadata/' . $this->metadata_filename;
        $args = array(
            'method'    => 'PUT',
            'headers'   => array(
                'Content-Type'  => $this->metadata_mimetype
            ),
            'body'      => $xml
        );

        $result = $this->perform_oc_request( $path, $args );

        return $result;
    }

    /**
     * Performs a HTTP request to Open Content.
     * @param $path
     * @param array $http_args
     * @return array
     */
    private function perform_oc_request( $path, $http_args = array() ) {

        $oc_api = new OcAPI();
        $oc_base_url = defined( 'OC_EDIT_URL' ) ? OC_EDIT_URL : $oc_api->getOcBaseUrl();

        $return_data = [
            'status' => 200,
            'body' => null
        ];

        $req_url        = $oc_base_url . $path;
        $args           = array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode( OC_EDIT_USER . ':' . OC_EDIT_PASSWORD )
            ),
            'timeout' => 20
        );

        $args           = array_merge_recursive( $args, $http_args );
        $api_response   = wp_remote_request( $req_url, $args );

        $http_status = wp_remote_retrieve_response_code( $api_response );
        $return_data['status'] = $http_status;

        if( !is_wp_error( $api_response ) ) {

            if( $http_status === 200 || $http_status === 201 ) {
                $return_data['body'] = wp_remote_retrieve_body( $api_response );
            }
        }

        return $return_data;
    }


    function substr_arr_get_keys($needle, array $haystack) {
        $filtered = array_filter($haystack, function ($item) use ($needle) {
            return false !== strpos($item, $needle);
        });

        $return_keys = [];
        foreach( $filtered as $url ) {

            foreach( $haystack as $key => $value ) {
                if( $url === $value ) {
                    $return_keys[] = $key;
                    break;
                }
            }
        }

        return $return_keys;
    }
}