<?php

namespace SmartGoblin\Worker;

use SmartGoblin\Internal\Slave\AuthSlave;

use SmartGoblin\Components\Http\Request;

class AuthWorker {
    #----------------------------------------------------------------------
    #\ VARIABLES

    private static bool $busy = false;
    private static AuthSlave $slave;

    #/ VARIABLES
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ INIT

    public static function __getToWork(AuthSlave &$slave): void {
        if(!self::$busy) {
            self::$busy = true;
            self::$slave = $slave;
        }
    }

    #/ INIT
    #----------------------------------------------------------------------
    
    #----------------------------------------------------------------------
    #\ PRIVATE FUNCTIONS



    #/ PRIVATE FUNCTIONS
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ METHODS

    public static function createAuthorization(int $id, array $customData = []): void {
        if(self::$busy) {
            if (session_status() === PHP_SESSION_ACTIVE) {
                self::$slave->createAuthorizedSession($id, $customData, bin2hex(random_bytes(32)));
                LogWorker::log("-SG- Authorized session created for user {$id}.");
            }
        }
    }

    public static function destroyAuthorization(): void {
        if(self::$busy) {
            if (session_status() === PHP_SESSION_ACTIVE) {
                self::$slave->destroyAuthorizedSession();
                LogWorker::log("-SG- Authorized session destroyed.");
            }
        }
    }

    public static function isAuthorized(?Request $request): bool {
        if(self::$busy) {
            if (session_status() === PHP_SESSION_ACTIVE) {
                if(self::$slave->validateSession()) {
                    if($request && in_array($request->getMethod(), ["POST", "PUT", "DELETE", "PATCH"], true)) {
                        if(!self::$slave->validateCSRF($_SESSION["sgas_csrf"], $request->getDataItem("csrf_token"))) {
                            LogWorker::warning("-SG- Restricted action for authenticated user was called without a valid csrf_token.");
                            return false;
                        }
                    }
                    LogWorker::log("-SG- Verified authorization for user {$_SESSION["sgas_uid"]}."); // Are we logging user data now??? Anyway...
                    return true;
                } 
            }
        }

        return false;
    }

    #/ METHODS
    #----------------------------------------------------------------------
}