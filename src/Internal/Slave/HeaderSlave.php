<?php

namespace SmartGoblin\Internal\Slave;

use SmartGoblin\Workers\HeaderWorker;

final class HeaderSlave {
    #----------------------------------------------------------------------
    #\ VARIABLES

    private static bool $busy = false;

    #/ VARIABLES
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ INIT

    /**
     * Initializes the HeaderSlave if it is not already busy.
     * 
     * This function will create a new HeaderSlave if it is not already busy.
     * It will then call HeaderWorker::__getToWork() and pass the new HeaderSlave object.
     * The new HeaderSlave object will be returned.
     * 
     * @return ?HeaderSlave The HeaderSlave object if it was successfully created, null otherwise.
     */
    public static function zap(): ?HeaderSlave {
        if(!self::$busy) {
            self::$busy = true;
            $inst = new HeaderSlave();
            HeaderWorker::__getToWork($inst);

            return $inst;
        }
    }

    private function __construct() {

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
     * Adds a header to the response.
     * 
     * @param string $key The key of the header.
     * @param string $value The value of the header.
     */
    public function addHeader(string $key, string $value): void {
        header("$key: $value");
    }

    /**
     * Removes a header from the response.
     * 
     * @param string $key The key of the header to remove.
     */
    public function removeHeader(string $key): void {
        header_remove($key);
    }

    
    /**
     * Writes security headers to the response.
     * 
     * @param array $allowedHosts An array of allowed hosts.
     * @param string $https The value of the HTTPS header.
     * @param string $origin The value of the Origin header.
     */
    public function writeSecurityHeaders(array $allowedHosts, string $https, string $origin): void {
        // TODO: Enable wildcard for allowedHosts
        HeaderWorker::removeHeader("X-Powered-By");

        HeaderWorker::addHeader("X-Content-Type-Options", "nosniff");
        HeaderWorker::addHeader("Referrer-Policy", "strict-origin-when-cross-origin");
        HeaderWorker::addHeader("Cross-Origin-Resource-Policy", "same-origin");
        HeaderWorker::addHeader("Content-Security-Policy", "frame-ancestors 'none';");
        HeaderWorker::addHeader("X-Frame-Options", "DENY");
        HeaderWorker::addHeader("Access-Control-Allow-Methods", "GET, POST, OPTIONS");
        HeaderWorker::addHeader("Access-Control-Allow-Headers", "Content-Type");

        if (!empty($https) && $https !== 'off') {
            HeaderWorker::addHeader("Strict-Transport-Security", "max-age=31536000; includeSubDomains; preload");
        }
        
        $origin = $origin ?? ""; // TODO: Do more research about HTTP_ORIGIN
        if (in_array($origin, $allowedHosts, true)) {
            HeaderWorker::addHeader("Access-Control-Allow-Origin", "https://$origin");
            HeaderWorker::addHeader("Access-Control-Allow-Credentials", "true");
            HeaderWorker::addHeader("Vary", "Origin");
        }
    }

    /**
     * Writes utility headers to the response.
     *
     * @param bool $isApi Whether the request is an API request.
     */
    public function writeUtilityHeaders(bool $isApi): void {
        // TODO: Add complexity for better cache control
        if($isApi) HeaderWorker::addHeader("Cache-Control", "private, no-store, must-revalidate");
        else HeaderWorker::addHeader("Cache-Control", "private, max-age=0, no-cache, must-revalidate");
    }

    #/ METHODS
    #----------------------------------------------------------------------
}