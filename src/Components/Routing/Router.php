<?php

namespace FastRaven\Components\Routing;

use FastRaven\Types\EndpointType;

final class Router {
    #----------------------------------------------------------------------
    #\ VARIABLES

    private EndpointType $type;
        public function getType(): EndpointType { return $this->type; }
    
    private array $subrouterList = [];
        public function getSubrouterList(): array { return $this->subrouterList; }
    private array $endpointList = [];
        public function getEndpointList(): array { return $this->endpointList; }

    private int $limitPerMinute = 0;
        public function getLimitPerMinute(): int { return $this->limitPerMinute; }
    
    #/ VARIABLES
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ INIT

    /**
     * Returns a new Router instance.
     *
     * @return Router The Router instance.
     */
    public static function new(EndpointType $type, int $limitPerMinute = -1): Router {
        return new Router($type, $limitPerMinute);
    }

    public function __construct(EndpointType $type, int $limitPerMinute = -1) {
        $this->type = $type;
        $this->limitPerMinute = $limitPerMinute;
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
     * Adds an endpoint to the router.
     *
     * @param Endpoint $endpoint The endpoint to add.
     * @return Router The Router instance.
     */
    public function add(Endpoint $endpoint): Router {
        if($endpoint->getType() !== EndpointType::ROUTER) $this->endpointList[$endpoint->getComplexPath()] = $endpoint;
        else $this->subrouterList[] = $endpoint;

        return $this;
    }

    #/ METHODS
    #----------------------------------------------------------------------
}