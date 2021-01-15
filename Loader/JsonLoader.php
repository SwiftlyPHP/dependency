<?php

namespace Swiftly\Dependency\Loader;

use Swiftly\Dependency\{
    Container,
    LoaderInterface
};

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
            // TODO
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
        if ( \is_readable( $this->file ) ) {
            return [];
        }

        $content = (string)\file_get_contents( $this->file );
        $content = \json_decode( $content, true );

        if ( !\is_array( $content ) || \json_last_error() !== \JSON_ERROR_NONE ) {
            return [];
        }

        return $content;
    }
}
