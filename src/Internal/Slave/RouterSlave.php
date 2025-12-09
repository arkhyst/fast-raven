<?php

namespace FastRaven\Internal\Slave;

use FastRaven\Components\Http\Request;
use FastRaven\Components\Routing\Router;
use FastRaven\Components\Routing\Endpoint;
use FastRaven\Workers\LogWorker;

use FastRaven\Workers\Bee;
use FastRaven\Components\Data\Collection;

final class RouterSlave {
    #----------------------------------------------------------------------
    #\ VARIABLES

    private static bool $busy = false;

    #/ VARIABLES
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ INIT

    /**
     * Initializes the RouterSlave if it is not already busy.
     * 
     * This function will create a new RouterSlave if it is not already busy.
     * It will then set the busy flag to true and return the new RouterSlave object.
     * If the RouterSlave is already busy, it will return null.
     * 
     * @return ?RouterSlave The RouterSlave object if it was successfully created, null otherwise.
     */
    public static function zap(): ?RouterSlave {
        if(!self::$busy) {
            self::$busy = true;
            return new RouterSlave();
        }

        return null;
    }

    private function __construct() {

    }

    #/ INIT
    #----------------------------------------------------------------------
    
    #----------------------------------------------------------------------
    #\ PRIVATE FUNCTIONS

    /**
     * Attempts to match the request path to a path in the file collection.
     * 
     * It will loop through the file collection and check if the request path starts with the path in the file collection.
     * If it does, it will check if the file exists in the router directory. If it does, it will return the file path.
     * If it does not, it will log an error.
     * 
     * @param Request $request The request object.
     * @param Collection $fileCollection The collection of file endpoints files relative to /config/router/ directory.
     * 
     * @return string|null The file path if the request path matches a path in the file collection, null otherwise.
     */
    private function matchFileCollection(Request $request, Collection $fileCollection): string|null {
        $requestPath = dirname($request->getPath());
        $filePath = null;

        foreach($fileCollection->getRawData() as $path => $file) {
            if($request->isApi()) $path = "/api/" . Bee::normalizePath($path . "/");
            if(str_starts_with($path, $requestPath)) {
                $tmp = SITE_PATH . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR . "router" . DIRECTORY_SEPARATOR . $file;
                if(file_exists($tmp)) $filePath = $tmp;
                else LogWorker::error("-SG- Router file does not exist: " . $filePath);
                break;
            }
        }

        return $filePath;
    }

    #/ PRIVATE FUNCTIONS
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ METHODS

    /**
     * Attempts to match the request path to an endpoint in the router.
     * If the router endpoints are not loaded, it will attempt to load them from the file collection.
     * If the router endpoints are loaded, it will loop through the endpoint list and check if the request path matches the complex path of an endpoint.
     * If a match is found, it will return the matched endpoint.
     * If no match is found, it will log an error and return null.
     * 
     * @param Request $request The request object.
     * @param Router $router The router to use.
     * 
     * @return Endpoint|null The matched endpoint, or null if no match is found.
     */
    public function route(Request $request, Router $router): ?Endpoint {
        $endpointList = $router->getEndpointList();
        $endpoint = null;

        if(!$router->isEndpointsLoaded()) {
            $filePath = $this->matchFileCollection($request, $router->getFileCollection());
            if($filePath) $endpointList = require_once $filePath;
        }

        if(!empty($endpointList) && $endpointList[0] instanceof Endpoint) {
            foreach($endpointList as $ep) {
                if($ep instanceof Endpoint && $ep->getComplexPath() === $request->getComplexPath()) {
                    $endpoint = $ep;
                    break;
                }
            }
        } else {
            LogWorker::error("-SG- Router endpoint list is empty or does not contain endpoints.");
        }

        return $endpoint;
    }

    #/ METHODS
    #----------------------------------------------------------------------
}