<?php

namespace FastRaven\Components\Routing;

use FastRaven\Types\EndpointType;

final class Middleware {
    #----------------------------------------------------------------------
    #\ VARIABLES

    private array $list = [];
    
    #/ VARIABLES
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ INIT

    /**
     * Returns a new Middleware instance.
     *
     * @return Middleware The Middleware instance.
     */
    public static function new(): Middleware {
        return new Middleware();
    }

    public function __construct() {
    }

    #/ INIT
    #----------------------------------------------------------------------
    
    #----------------------------------------------------------------------
    #\ PRIVATE FUNCTIONS



    #/ PRIVATE FUNCTIONS
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ METHODS

    /**
     * Adds a middleware method to the router.
     * 
     * @param string $id The ID of the middleware.
     * @param callable $callback The callback to add.
     * @return Middleware The Middleware instance.
     */
    public function add(string $id, callable $callback): Middleware {
        $this->list[$id] = $callback;
        return $this;
    }

    /**
     * Gets a middleware method.
     * 
     * @param string $id The ID of the middleware.
     * @return callable|null The middleware method or null if not found.
     */
    public function get(string $id): callable|null {
        return $this->list[$id] ?? null;
    }

    #/ METHODS
    #----------------------------------------------------------------------
}