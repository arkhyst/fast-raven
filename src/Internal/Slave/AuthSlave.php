<?php

namespace SmartGoblin\Internal\Slave;

use SmartGoblin\Workers\AuthWorker;

final class AuthSlave {
    #----------------------------------------------------------------------
    #\ VARIABLES

    private static bool $busy = false;

    #/ VARIABLES
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ INIT

    /**
     * Initialize the AuthSlave.
     *
     * This function will create a new AuthSlave if it is not already busy.
     * It will then call AuthWorker::__getToWork() and pass the new AuthSlave object.
     * The new AuthSlave object will be returned.
     *
     * @return ?AuthSlave The AuthSlave object if it was successfully created, null otherwise.
     */
    public static function zap(): ?AuthSlave {
        if(!self::$busy) {
            self::$busy = true;
            $inst = new AuthSlave();
            AuthWorker::__getToWork($inst);

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
     * Initialize the session cookie for the given session name, lifetime, and domain.
     *
     * This function will set the session.use_strict_mode and session.gc_maxlifetime PHP
     * settings. It will then set the session name and cookie parameters. Finally, it
     * will start the session.
     *
     * @param string $sessionName The session name to use.
     * @param int $lifetime The lifetime of the session cookie in seconds.
     * @param string $domain The domain to use for the session cookie.
     */
    public function initializeSessionCookie(string $sessionName, int $lifetime, string $domain): void {
        ini_set("session.use_strict_mode", 1);
        ini_set("session.gc_maxlifetime", $lifetime);
        session_name($sessionName);
        session_set_cookie_params([
            "lifetime" => $lifetime,
            "path" => "/",
            "domain" => $domain,
            "secure" => true,
            "httponly" => true,
            "samesite" => "Lax"
        ]);

        session_start();
    }

    /**
     * Creates an authorized session.
     *
     * This function will regenerate the session ID and set the following session variables:
     * $_SESSION["sgas_uid"] = $id
     * $_SESSION["sgas_custom"] = $customData
     * $_SESSION["sgas_csrf"] = $csrf
     *
     * @param int $id The ID of the user to authorize.
     * @param array $customData Custom data to store in the session.
     * @param string $csrf The CSRF token to store in the session.
     */
    public function createAuthorizedSession(int $id, array $customData, string $csrf): void {
        session_regenerate_id(true);
        $_SESSION["sgas_uid"] = $id;
        $_SESSION["sgas_custom"] = $customData;
        $_SESSION["sgas_csrf"] = $csrf;
    }

    /**
     * Destroys the authorized session.
     *
     * This function will regenerate the session ID and clear the session variables.
     */
    public function destroyAuthorizedSession(): void {
        session_regenerate_id(true);
        $_SESSION = [];
    }

    /**
     * Validates the authorized session.
     *
     * This function will return true if the following session variables are set:
     * $_SESSION["sgas_uid"]
     * $_SESSION["sgas_custom"]
     * $_SESSION["sgas_csrf"]
     *
     * @return bool True if the session is valid, false otherwise.
     */
    public function validateSession(): bool {
        return isset($_SESSION["sgas_uid"]) && isset($_SESSION["sgas_custom"]) && isset($_SESSION["sgas_csrf"]);
    }

    /**
     * Validates the CSRF token of the session against the CSRF token of the request.
     *
     * @param string|null $sessionToken The CSRF token stored in the session.
     * @param string|null $requestToken The CSRF token sent in the request.
     *
     * @return bool True if the CSRF tokens match, false otherwise.
     */
    public function validateCSRF(?string $sessionToken, ?string $requestToken): bool {
        return $sessionToken === $requestToken;
    }

    #/ METHODS
    #----------------------------------------------------------------------
}