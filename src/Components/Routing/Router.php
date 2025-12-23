<?php

namespace FastRaven\Components\Routing;

use FastRaven\Components\Data\Collection;

final class Router {
    #----------------------------------------------------------------------
    #\ VARIABLES

    private array $endpointList = [];
        public function getEndpointList(): array { return $this->endpointList; }
    
    #/ VARIABLES
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ INIT

    /**
     * Returns a new Router instance with the given list of endpoints.
     *
     * The list of endpoints should contain Endpoint instances.
     *
     * @param array $endpointList The list of Endpoint instances.
     * 
     * @return Router The new Router instance.
     */
    public static function endpoints(array $endpointList): Router {
        return new Router($endpointList);
    }

    private function __construct(array $endpointList = []) {
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