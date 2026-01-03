<?php

namespace FastRaven\Workers;

use FastRaven\Internal\Slave\AuthSlave;
use FastRaven\Components\Http\Request;

final class AuthWorker {
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

    private static function ensureSession(): void {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

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
     * @param int $id The ID of the user to authorize or any other unique identifier.
     * @param array $customData Custom data to store in the session.
     */
    public static function createAuthorization(int $id, array $customData = []): void {
        if(self::$busy) {
            self::ensureSession();
            self::$slave->createAuthorizedSession($id, $customData, bin2hex(random_bytes(32)));
            LogWorker::debug("Authorized session created for user {$id}.");
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
            self::ensureSession();
            self::$slave->destroyAuthorizedSession();
            LogWorker::debug("Authorized session destroyed.");
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
    public static function isAuthorized(?Request $request = null): bool {
        if(self::$busy) {
            self::ensureSession();
            if(self::$slave->validateSession()) {
                if($request && in_array($request->getMethod(), ["POST", "PUT", "DELETE", "PATCH"], true)) {
                    if(!self::$slave->validateCSRF($_SESSION["sgas_csrf"], $request->post("csrf_token"))) {
                        LogWorker::warning("Restricted action for authenticated user was called without a valid csrf_token.");
                        return false;
                    }
                }
                LogWorker::debug("Verified authorization for user " . self::getAuthorizedUserId() . ".");
                return true;
            }
        }

        return false;
    }

    /**
     * Retrieves the ID of the authorized user if an authorized session exists.
     *
     * This function will return the ID of the authorized user if an authorized session exists.
     * If no authorized session exists, it will return null.
     *
     * @return ?int The ID of the authorized user if an authorized session exists, null otherwise.
     */
    public static function getAuthorizedUserId(): ?int {
        if(self::$busy) {
            self::ensureSession();
            if(self::$slave->validateSession()) {
                return $_SESSION["sgas_uid"];
            }
        }

        return null;
    }

    /**
     * Automatically logs in a user via their username and password, and creates an authorized session if the login is successful.
     *
     * @param ?string $user The username of the user to login.
     * @param ?string $pass The password of the user to login.
     * @param string $dbTable The name of the database table to query for login data.
     * @param string $dbIdCol The name of the column in the database table that contains the user's ID.
     * @param string $dbNameCol The name of the column in the database table that contains the user's name.
     * @param string $dbPassCol The name of the column in the database table that contains the user's password.
     *
     * @return bool True if the login is successful, false otherwise.
     */
    public static function autologin(?string $user, ?string $pass, string $dbTable = "users", string $dbIdCol = "id", string $dbNameCol = "name", string $dbPassCol = "password"): bool {
        if(self::$busy) {
            self::ensureSession();
            if(!$user || !$pass) return false;

            $id = self::$slave->checkCredentials($user, $pass, $dbTable, $dbIdCol, $dbNameCol, $dbPassCol);
            
            if($id) {
                LogWorker::debug("User {$user} logged in via autologin.");
                AuthWorker::createAuthorization($id);
                return true;
            } else {
                LogWorker::warning("Failed autologin attempt for user {$user}.");
                AuthWorker::destroyAuthorization();
                return false;
            }
        }

        return false;
    }

    /**
     * Regenerates the CSRF token for the current authorized session.
     *
     * This function will regenerate the CSRF token for the current authorized session if an authorized session exists.
     * If no authorized session exists, it will return null.
     * Note: This will invalidate CSRF tokens in other open tabs.
     *
     * @return ?string The new CSRF token if the authorized session exists, null otherwise.
     */
    public static function regenerateCSRF(): ?string {
        if(self::$busy) {
            self::ensureSession();
            if(self::$slave->validateSession()) {
                LogWorker::debug("CSRF token has been regenerated for user " . self::getAuthorizedUserId());
                return self::$slave->regenerateCSRF();
            }
        }

        return null;
    }

    #/ METHODS
    #----------------------------------------------------------------------
}