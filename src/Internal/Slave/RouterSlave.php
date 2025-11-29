<?php

namespace SmartGoblin\Internal\Slave;

use SmartGoblin\Components\Http\Request;
use SmartGoblin\Components\Routing\Router;
use SmartGoblin\Components\Routing\Endpoint;
use SmartGoblin\Worker\LogWorker;

use SmartGoblin\Worker\Bee;

final class RouterSlave {
    #----------------------------------------------------------------------
    #\ VARIABLES

    private static bool $busy = false;

    #/ VARIABLES
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ INIT

    public static function zap(): ?RouterSlave {
        if(!self::$busy) {
            self::$busy = true;
            return new RouterSlave();
        }
    }

    private function __construct() {

    }

    #/ INIT
    #----------------------------------------------------------------------
    
    #----------------------------------------------------------------------
    #\ PRIVATE FUNCTIONS

    private function matchAssocFileList(Request $request, array $assocFileList): string|null {
        $requestPath = dirname($request->getPath());
        $filePath = null;

        foreach($assocFileList as $path => $file) {
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

    public function route(Request $request, Router $router): ?Endpoint {
        $endpointList = $router->getEndpointList();
        $endpoint = null;

        if(!$router->getEndpointsLoaded()) {
            $filePath = $this->matchAssocFileList($request, $router->getAssocFileList());
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