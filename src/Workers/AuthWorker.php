<?php

namespace SmartGoblin\Workers;

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

    /**
     * Creates an authorized session for the given user ID.
     *
     * This function will create an authorized session for the given user ID.
     * It will then call AuthSlave::createAuthorizedSession() and pass the user ID, custom data, and a randomly generated CSRF token.
     * It will then log a message indicating that an authorized session was created for the user.
     *
     * @param int $id The ID of the user to authorize.
     * @param array $customData Custom data to store in the session.
     */
    public static function createAuthorization(int $id, array $customData = []): void {
        if(self::$busy) {
            if (session_status() === PHP_SESSION_ACTIVE) {
                self::$slave->createAuthorizedSession($id, $customData, bin2hex(random_bytes(32)));
                LogWorker::log("-SG- Authorized session created for user {$id}.");
            }
        }
    }

    /**
     * Destroys the authorized session.
     *
     * This function will destroy the authorized session if it exists.
     * It will then log a message indicating that the authorized session was destroyed.
     */
    public static function destroyAuthorization(): void {
        if(self::$busy) {
            if (session_status() === PHP_SESSION_ACTIVE) {
                self::$slave->destroyAuthorizedSession();
                LogWorker::log("-SG- Authorized session destroyed.");
            }
        }
    }

    /**
     * Verifies if the user is authorized and has a valid csrf_token.
     *
     * This function will check if the user is authorized and has a valid csrf_token.
     * If the user is authorized and has a valid csrf_token, it will log a message indicating that the authorization was verified.
     * If the user is authorized but does not have a valid csrf_token, it will log a warning message indicating that the csrf_token was invalid.
     * If the user is not authorized, it will return false.
     *
     * @param ?Request $request The request object.
     *
     * @return bool True if the user is authorized and has a valid csrf_token, false otherwise.
     */
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