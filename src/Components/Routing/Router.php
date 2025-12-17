<?php

namespace FastRaven\Components\Routing;

use FastRaven\Components\Data\Collection;

final class Router {
    #----------------------------------------------------------------------
    #\ VARIABLES

    private bool $endpointsLoaded = false;
        public function isEndpointsLoaded(): bool { return $this->endpointsLoaded; }
    private array $endpointList = [];
        public function getEndpointList(): array { return $this->endpointList; }
    private Collection $fileCollection;
        public function getFileCollection(): Collection { return $this->fileCollection; }
    
    #/ VARIABLES
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ INIT

    /**
     * Returns a new Router instance with the given Collection of endpoints files.
     *
     * The Collection should have the following format:
     *
     * Collection::new([
     *     Item::new("/v1", "v1/main.php"),
     *     ...
     * ])
     *
     * @param Collection $fileCollection The Collection of file endpoints files relative to /config/router/ directory.
     * 
     * @return Router The new Router instance.
     */
    public static function files(Collection $fileCollection): Router {
        return new Router([], $fileCollection);
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
        return new Router($endpointList);
    }

    private function __construct(array $endpointList = [], ?Collection $fileCollection = null) {
        $this->endpointsLoaded = !empty($endpointList);
        $this->endpointList = $endpointList;
        $this->fileCollection = $fileCollection ?? Collection::new();
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