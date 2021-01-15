<?php

namespace Swiftly\Dependency\Loader;

use Swiftly\Dependency\{
    Container,
    LoaderInterface
};

use function is_numeric;
use function is_string;
use function is_callable;
use function is_readable;
use function is_array;
use function file_get_contents;
use function json_decode;
use function json_last_error;

use const JSON_ERROR_NONE;

/**
 * Class responsible for loading services from JSON files
 *
 * @author clvarley
 */
Class JsonLoader Implements LoaderInterface
{

    /**
     * Path to the JSON services file
     *
     * @var string $file File path
     */
    protected $file;

    /**
     * Create loader for the given JSON service file
     *
     * @param string $file File json
     */
    public function __construct( string $file )
    {
        $this->file = $file;
    }

    /**
     * Load services into this dependency container
     *
     * @param Container $container Dependency container
     * @return void                N/a
     */
    public function load( Container $container ) : Container
    {
        $json = $this->json();

        // Nothing to do, pass through
        if ( empty( $json ) ) {
            return $container;
        }

        foreach ( $service as $name => $service ) {
            if ( empty( $service ) ) {
                continue;
            }

            // Just a service name
            if ( is_numeric( $name ) && is_string( $service ) ) {
                $name = $service;
            } elseif ( is_numeric( $name ) ) {
                continue; // Cannot have an anonymous definition
            }

            // Figure out the callback type
            if ( is_string( $service ) ) {
                $handler = $service;
            } elseif ( !empty( $service['handler'] ) && is_callable( $service['handler'] ) ) {
                $handler = $service['handler'];
            } else {
                $handler = $name; // Fallback
            }

            $registered = $container->bind( $name, $handler );

            // Marked as non-singleton?
            if ( isset( $service['singleton'] ) && empty( $service['singleton'] ) ) {
                $registered->singleton( false );
            }
        }

        return $container;
    }

    /**
     * Loads the JSON file and returns it's decoded content
     *
     * @return array JSON content
     */
    private function json() : array
    {
        if ( is_readable( $this->file ) ) {
            return [];
        }

        $content = (string)file_get_contents( $this->file );
        $content = json_decode( $content, true );

        if ( !is_array( $content ) || json_last_error() !== JSON_ERROR_NONE ) {
            return [];
        }

        return $content;
    }
}
