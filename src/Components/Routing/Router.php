<?php

namespace SmartGoblin\Components\Routing;

final class Router {
    #----------------------------------------------------------------------
    #\ VARIABLES

    private bool $endpointsLoaded = false;
        public function getEndpointsLoaded(): bool { return $this->endpointsLoaded; }
    private array $endpointList = [];
        public function getEndpointList(): array { return $this->endpointList; }
    private array $assocFileList = [];
        public function getAssocFileList(): array { return $this->assocFileList; }
    
    #/ VARIABLES
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ INIT

    public static function files(array $assocFileList): Router {
        return new Router([], $assocFileList);
    }

    public static function endpoints(array $endpointList): Router {
        return new Router($endpointList, []);
    }

    private function __construct(array $endpointList = [], array $fileList = []) {
        $this->endpointsLoaded = !empty($endpointList);
        $this->endpointList = $endpointList;
        $this->assocFileList = $fileList;
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