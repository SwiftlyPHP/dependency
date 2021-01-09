<?php

namespace Swiftly\Dependency\Loader;

use Swiftly\Dependency\{
    Container,
    LoaderInterface
};

use function is_array;
use function is_string;
use function is_callable;
use function is_readable;
use function substr;

/**
 * Class responsible for loading services from PHP files
 *
 * @author clvarley
 */
Class PhpLoader Implements LoaderInterface
{

    /**
     * Path to the PHP services file
     *
     * @var string $file File path
     */
    protected $file;

    /**
     * Create loader for the given PHP service file
     *
     * @param string $file File path
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
        if ( !$this->valid() ) {
            return $container;
        }

        // TODO: Sandbox this in future!
        $services = include ($this->file);

        if ( empty( $services ) || !is_array( $services ) ) {
            return $container;
        }

        // Parse the service structures
        foreach ( $services as $name => $service ) {
            if ( !is_string( $name ) || empty( $service ) ) {
                continue;
            }

            // Callback func or service name?
            if ( is_string( $service ) ) {
                $handler = $service;
            } elseif ( !empty( $service['handler'] ) && is_callable( $service['handler'] ) ) {
                $handler = $service['handler'];
            } else {
                $handler = $name;
            }

            // Explicitly not a singleton?
            $single = ( isset( $service['singleton'] )
                && empty( $service['singleton'] )
            );

            $registered = $container->bind( $name, $handler );
            $registered->singleton( $single );
        }

        return $container;
    }

    /**
     * Checks to see if the file is valid
     *
     * @return bool File valid
     */
    private function valid() : bool
    {
        return ( is_readable( $this->file )
            && substr( $this->file, -4 ) === '.php'
        );
    }
}
