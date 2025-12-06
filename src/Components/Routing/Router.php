<?php

namespace FastRaven\Components\Routing;

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

    /**
     * Returns a new Router instance with the given associative array of endpoints files.
     *
     * The associative array should have the following format:
     *
     * [
     *     "/v1/path" => "v1/main.php",
     *     ...
     * ]
     *
     * @param array $assocFileList The associative array of file endpoints files relative to /config/router/ directory.
     * 
     * @return Router The new Router instance.
     */
    public static function files(array $assocFileList): Router {
        return new Router([], $assocFileList);
    }

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