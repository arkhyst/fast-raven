<?php

namespace FastRaven\Components\Routing;

use FastRaven\Components\Types\MiddlewareType;

final class Router {
    #----------------------------------------------------------------------
    #\ VARIABLES

    private MiddlewareType $type;
        public function getType(): MiddlewareType { return $this->type; }
    
    private array $subrouterList = [];
        public function getSubrouterList(): array { return $this->subrouterList; }
    private array $endpointList = [];
        public function getEndpointList(): array { return $this->endpointList; }
    
    #/ VARIABLES
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ INIT

    /**
     * Returns a new Router instance.
     *
     * @return Router The Router instance.
     */
    public static function new(MiddlewareType $type): Router {
        return new Router($type);
    }

    public function __construct(MiddlewareType $type) {
        $this->type = $type;
    }

    #/ INIT
    #----------------------------------------------------------------------
    
    #----------------------------------------------------------------------
    #\ PRIVATE FUNCTIONS



    #/ PRIVATE FUNCTIONS
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ METHODS

    public function add(Endpoint $endpoint): Router {
        if($endpoint->getType() !== MiddlewareType::ROUTER) $this->endpointList[$endpoint->getComplexPath()] = $endpoint;
        else $this->subrouterList[] = $endpoint;

        return $this;
    }

    #/ METHODS
    #----------------------------------------------------------------------
}