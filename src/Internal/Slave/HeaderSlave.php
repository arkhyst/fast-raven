<?php

namespace SmartGoblin\Internal\Slave;

use SmartGoblin\Worker\HeaderWorker;

final class HeaderSlave {
    #----------------------------------------------------------------------
    #\ VARIABLES

    private static bool $busy = false;

    #/ VARIABLES
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ INIT

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

    public function addHeader(string $key, string $value): void { header("$key: $value"); }
    public function removeHeader(string $key): void { header_remove($key); }

    // TODO: Enable wildcard for allowedHosts
    public function writeSecurityHeaders(array $allowedHosts, string $https, string $origin): void {
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

    public function writeUtilityHeaders(bool $isApi): void {
        // TODO: Add complexity for better cache control
        if($isApi) HeaderWorker::addHeader("Cache-Control", "private, no-store, must-revalidate");
        else HeaderWorker::addHeader("Cache-Control", "private, max-age=0, no-cache, must-revalidate");
    }

    #/ METHODS
    #----------------------------------------------------------------------
}