<?php

namespace Swiftly\Dependency;

/**
 *
 */
Class Service
{

    /**
     * 
     */
    protected $classname;

    /**
     *
     */
    protected $method;

    /**
     *
     */
    protected $parameters = [];

    /**
     *
     */
    protected $singleton = true;

    /**
     *
     */
    public function alias( string $name ) : self
    {
        // TODO

        return $this;
    }

    /**
     *
     */
    public function singleton( bool $singleton ) : self
    {
        $this->singleton = $singleton;

        return $this;
    }

    /**
     *
     */
    public function parameters( array $parameters ) : self
    {
        $this->parameters = \array_merge( $this->parameters, $parameters );

        return $this;
    }
}
