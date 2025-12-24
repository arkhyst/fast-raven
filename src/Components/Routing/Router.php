<?php

namespace FastRaven\Components\Routing;

use FastRaven\Components\Types\MiddlewareType;

final class Router {
    #----------------------------------------------------------------------
    #\ VARIABLES

    private MiddlewareType $type;
        public function getType(): MiddlewareType { return $this->type; }
    private array $endpointList = [];
        public function getEndpointList(): array { return $this->endpointList; }
    
    #/ VARIABLES
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ INIT

    /**
     * Returns a new Views Router instance with the given list of endpoints.
     *
     * The list of endpoints should contain Endpoint instances.
     *
     * @param array $endpointList The list of Endpoint instances.
     * 
     * @return Router The Router instance.
     */
    public static function views(array $endpointList): Router {
        return new Router(MiddlewareType::VIEW, $endpointList);
    }

    /**
     * Returns a new API Router instance with the given list of endpoints.
     *
     * The list of endpoints should contain Endpoint instances.
     *
     * @param array $endpointList The list of Endpoint instances.
     * 
     * @return Router The Router instance.
     */
    public static function api(array $endpointList): Router {
        return new Router(MiddlewareType::API, $endpointList);
    }

    /**
     * Returns a new CDN Router instance with the given list of endpoints.
     *
     * The list of endpoints should contain Endpoint instances.
     *
     * @param array $endpointList The list of Endpoint instances.
     * 
     * @return Router The Router instance.
     */
    public static function cdn(array $endpointList): Router {
        return new Router(MiddlewareType::CDN, $endpointList);
    }

    private function __construct(MiddlewareType $type, array $endpointList = []) {
        $this->type = $type;
        $this->endpointList = $endpointList;
    }

    #/ INIT
    #----------------------------------------------------------------------
    
    #----------------------------------------------------------------------
    #\ PRIVATE FUNCTIONS



    #/ PRIVATE FUNCTIONS
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ METHODS



    #/ METHODS
    #----------------------------------------------------------------------
}