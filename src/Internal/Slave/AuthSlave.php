<?php

namespace FastRaven\Internal\Slave;

use FastRaven\Workers\AuthWorker;
use FastRaven\Workers\DataWorker;
use FastRaven\Components\Data\Collection;
use FastRaven\Components\Data\Item;

use FastRaven\Workers\Bee;

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

        return null;
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
     * @param bool $globalAuth Whether the authorization should be valid AUTH_DOMAIN.
     */
    public function initializeSessionCookie(string $sessionName, int $lifetime, bool $globalAuth = false): void {
        ini_set("session.use_strict_mode", 1);
        ini_set("session.gc_maxlifetime", $lifetime);
        session_name($sessionName);

        $options = [
            "lifetime" => $lifetime,
            "path" => "/",
            "secure" => true,
            "httponly" => true,
            "samesite" => "Lax"
        ];

        if ($globalAuth) {
            $domain = Bee::env("AUTH_DOMAIN", "localhost");
            if (filter_var(ltrim($domain, "."), FILTER_VALIDATE_IP) === false && $domain !== "localhost") {
                if ($domain[0] !== ".") $domain = "." . $domain;
                $options['domain'] = $domain;
            }
        }

        session_set_cookie_params($options);
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
        if ($sessionToken === null || $requestToken === null) return false;
        return hash_equals($sessionToken, $requestToken);
    }

    /**
     * Regenerates the CSRF token for the authorized user.
     *
     * This function will regenerate the CSRF token for the authorized user if an authorized session exists.
     *
     * @return string The new CSRF token.
     */
    public function regenerateCSRF(): string {
        $csrf = bin2hex(random_bytes(32));
        $_SESSION["sgas_csrf"] = $csrf;
        return $csrf;
    }

    /**
     * Attempts to login a user via their username and password.
     *
     * This function will query the given database table for a user with the given username.
     * If a user is found, it will then verify the given password against the password stored in the database.
     * If the password is valid, it will return the user's ID, otherwise it will return null.
     *
     * @param string $user The username of the user to login.
     * @param string $pass The password of the user to login.
     * @param string $dbTable The name of the database table to query for login data.
     * @param string $dbIdCol The name of the column in the database table that contains the user's ID.
     * @param string $dbNameCol The name of the column in the database table that contains the user's name.
     * @param string $dbPassCol The name of the column in the database table that contains the user's password.
     *
     * @return ?int The user's ID if the login is successful, null otherwise.
     */
    public function loginAttempt(string $user, string $pass, string $dbTable, string $dbIdCol, string $dbNameCol, string $dbPassCol): ?int {
        $data = DataWorker::getOneWhere($dbTable, [$dbIdCol, $dbNameCol, $dbPassCol], Collection::new([
            Item::new($dbNameCol, $user)
        ]));

        if($data && password_verify($pass, $data[$dbPassCol])) {
            return (int)$data[$dbIdCol];
        }

        return null;
    }

    #/ METHODS
    #----------------------------------------------------------------------
}