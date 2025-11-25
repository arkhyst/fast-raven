<?php

namespace SmartGoblin\Internal\Slave;

use SmartGoblin\Worker\AuthWorker;

final class AuthSlave {
    #----------------------------------------------------------------------
    #\ VARIABLES

    private static bool $busy = false;

    #/ VARIABLES
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ INIT

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

    public function createAuthorizedSession(int $id, array $customData, string $csrf): void {
        session_regenerate_id(true);
        $_SESSION["sgas_uid"] = $id;
        $_SESSION["sgas_custom"] = $customData;
        $_SESSION["sgas_csrf"] = $csrf;
    }

    public function destroyAuthorizedSession(): void {
        session_regenerate_id(true);
        $_SESSION = [];
    }

    public function validateSession(): bool {
        return isset($_SESSION["sgas_uid"]) && isset($_SESSION["sgas_custom"]) && isset($_SESSION["sgas_csrf"]);
    }

    public function validateCSRF(?string $sessionToken, ?string $requestToken) {
        return $sessionToken === $requestToken;
    }

    #/ METHODS
    #----------------------------------------------------------------------
}