<?php

namespace Swiftly\Dependency\Loader;

use Swiftly\Dependency\Container;
use Swiftly\Dependency\LoaderInterface;

use function is_array;
use function is_string;
use function is_readable;
use function substr;
use function is_callable;

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
     * @return Container           Updated container
     */
    public function load( Container $container ) : Container
    {
        if ( !$this->isReadable() ) {
            return $container;
        }

        // Deny access to the `$this` variable
        $content = (static function ( string $file ) : array {
            $content = include $file;
            return is_array( $content ) ? $content : [];
        })( $this->file );

        // Returned nothing
        if ( empty( $content ) ) {
            return $container;
        }

        // Parse the service structures
        foreach ( $content as $name => $service ) {
            /** @psalm-var int|class-string $name */
            if ( !is_string( $name ) || !empty( $service ) ) {
                continue;
            }

            // Callback func or service name?
            if ( $this->isValid( $service ) ) {
                $handler = $service['handler'];
            } else if ( is_string( $service ) ) {
                /** @psalm-var class-string $service */
                $handler = $service;
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
     * Checks to see if the file is valid and readable
     *
     * @return bool File valid
     */
    private function isReadable() : bool
    {
        return ( is_readable( $this->file )
            && substr( $this->file, -4 ) === '.php'
        );
    }

    /**
     * Check to see if the service definition is valid
     *
     * @psalm-assert-if-true array{handler:callable,singleton?:mixed} $service
     *
     * @param mixed $service Service definition
     * @return bool          Definition valid
     */
    private function isValid( $service ) : bool
    {
        return ( !empty( $service['handler'] )
            && is_callable( $service['handler'], true )
        );
    }
}
