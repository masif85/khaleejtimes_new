<?php

abstract class AbstractOcObject implements ArrayAccess, JsonSerializable {
    
    /**
     * All of the properties set on the container.
     *
     * @since 1.4.2
     * @var array
     */
    protected $prop_data = [];
    
    /**
     * @since 1.7.1
     * @return array
     */
    public function all_to_array() {
        return array_map( [ $this, 'property_to_array' ], $this->to_array() );
    }
    
    /**
     * Get a property from the container.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @since 1.4.2
     * @return mixed
     */
    public function get( $key, $default = [] ) {
        $key = strtolower( $key );
        if( array_key_exists( $key, $this->prop_data ) ) {
            return $this->prop_data[ $key ];
        }
        
        return $default instanceof Closure ? $default() : $default;
    }
    
    /**
     * Returns all current properties on object
     *
     * @since 1.4.2
     * @return array
     */
    public function get_all_properties() {
        return $this->prop_data;
    }
    
    /**
     * Specify data which should be serialized to JSON
     *
     * @link  http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @since 1.4.2
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    public function jsonSerialize() {
        return $this->to_array();
    }
    
    /**
     * Set a property on the container.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @since 1.4.2
     * @return void
     */
    public function set( $key, $value ) {
        $key = strtolower( $key );
        if( $key === 'url' ) {
            $value = $this->get_url_value( $value );
        }
        $this->prop_data[ $key ] = $value;
    }
    
    /**
     * Set a list of properties to the container
     *
     * @param array $properties
     *
     * @since 1.4.2
     * @return void
     */
    public function set_properties( array $properties = [] ) {
        $this->fill( $properties );
    }
    
    /**
     * Convert the object to an array.
     *
     * @since 1.4.2
     * @return array
     */
    public function to_array() {
        return $this->prop_data;
    }
    
    /**
     * Convert the object to JSON.
     *
     * @param int $options
     *
     * @since 1.4.2
     * @return string
     */
    public function to_json( $options = 0 ) {
        return json_encode( $this->jsonSerialize(), $options );
    }
    
    /**
     * Whether a offset exists
     *
     * @link  http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset An offset to check for.
     *
     * @since 1.4.2
     * @return boolean true on success or false on failure.
     */
    public function offsetExists( $offset ) {
        return isset( $this->{$offset} );
    }
    
    /**
     * Offset to retrieve
     *
     * @link  http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset The offset to retrieve.
     *
     * @since 1.4.2
     * @return mixed Can return all value types.
     */
    public function offsetGet( $offset ) {
        return $this->{$offset};
    }
    
    /**
     * Offset to set
     *
     * @link  http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset The offset to assign the value to.
     * @param mixed $value  The value to set.
     *
     * @since 1.4.2
     * @return void
     */
    public function offsetSet( $offset, $value ) {
        $this->{$offset} = $value;
    }
    
    /**
     * Offset to unset
     *
     * @link  http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset The offset to unset.
     *
     * @since 1.4.2
     * @return void
     */
    public function offsetUnset( $offset ) {
        unset( $this->{$offset} );
    }
    
    // Dynamic Methods:
    // ======================================================
    
    /**
     * Handle dynamic calls to the container to set attributes.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @since 1.4.2
     * @return $this
     */
    public function __call( $method, $parameters ) {
        $this->prop_data[ $method ] = count( $parameters ) > 0 ? $parameters[ 0 ] : true;
        
        return $this;
    }
    
    /**
     * Dynamic getter
     *
     * @param string $name
     *
     * @since 1.4.2
     * @return array
     */
    public function __get( $name ) {
        return $this->get( $name );
    }
    
    /**
     * Dynamically check if an property is set.
     *
     * @param string $name
     *
     * @since 1.4.2
     * @return bool
     */
    public function __isset( $name ) {
        return isset( $this->prop_data[ $name ] );
    }
    
    /**
     * Dynamic setter, will set value on none existing property
     *
     * @param string $name
     * @param mixed  $value
     *
     * @since 1.4.2
     * @return void
     */
    public function __set( $name, $value ) {
        $this->set( $name, $value );
    }
    
    /**
     * Dynamically unset an property.
     *
     * @param string $name
     *
     * @since 1.4.2
     * @return void
     */
    public function __unset( $name ) {
        unset( $this->prop_data[ $name ] );
    }
    
    /**
     * Walk through all values of a property and convert OcObjects into array
     *
     * @param $property_values
     *
     * @since 1.7.1
     * @return array
     */
    protected function property_to_array( $property_values ) {
        return array_map( function ( $value ) {
            if( $value instanceof AbstractOcObject ) {
                return $value->all_to_array();
            }
            
            return $value;
        }, (array)$property_values );
    }
    
    /**
     * Fill up properties dynamically on the container
     *
     * @param array $properties
     *
     * @since 1.4.2
     * @return void
     */
    public function fill( array $properties = [] ) {
        foreach ( $properties as $key => $value ) {
            $this->set( $key, $value );
        }
    }
    
    /**
     * Special function to set url that makes sure all url-values starts with scheme
     *
     * @param $value
     *
     * @since 0.1
     * @return array
     */
    private function get_url_value( $value ) {
        return array_map( function ( $value_item ) {
            return ! $this->starts_with( $value_item, [
                'http://',
                'https://'
            ] ) ? "http://{$value_item}" : $value_item;
        }, (array)$value );
    }
    
    /**
     * Check if a value starts with a given string
     *
     * @param string       $haystack
     * @param string|array $needles
     *
     * @since 1.4.2
     * @return bool
     */
    private function starts_with( $haystack, $needles ) {
        foreach ( (array)$needles as $needle ) {
            if( $needle !== '' && 0 === strpos( $haystack, (string)$needle ) ) {
                return true;
            }
        }
        
        return false;
    }
}