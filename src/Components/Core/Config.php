<?php

namespace FastRaven\Components\Core;


final class Config {
    #----------------------------------------------------------------------
    #\ VARIABLES

    private string $siteName;
        public function getSiteName(): string { return $this->siteName; }
    private bool $restricted;
        public function isRestricted(): bool { return $this->restricted; }

    private array $allowedHosts = ["*"];
        public function getAllowedHosts(): array { return $this->allowedHosts; }
    
    private string $authSessionName = "PHPSESSID";
        public function getAuthSessionName(): string { return $this->authSessionName; }
    private int $authExpiryDays = 7;
        public function getAuthLifetime(): int { return $this->authExpiryDays * 24 * 60 * 60; }
    private bool $authGlobal = false;
        public function isAuthGlobal(): bool { return $this->authGlobal; }

    private string $defaultNotFoundPathRedirect = "/";
        public function getDefaultNotFoundPathRedirect(): string { return $this->defaultNotFoundPathRedirect; }
    private string $defaultUnauthorizedPathRedirect = "/login";
        public function getDefaultUnauthorizedPathRedirect(): string { return $this->defaultUnauthorizedPathRedirect; }
    private string $defaultUnauthorizedSubdomainRedirect = "";
        public function getDefaultUnauthorizedSubdomainRedirect(): string { return $this->defaultUnauthorizedSubdomainRedirect; }

    private bool $privacyRegisterLogs = true;
        public function isPrivacyRegisterLogs(): bool { return $this->privacyRegisterLogs; }
    private bool $privacyRegisterOrigin = true;
        public function isPrivacyRegisterOrigin(): bool { return $this->privacyRegisterOrigin; }

    private int $securityRateLimit = 100;
        public function getSecurityRateLimit(): int { return $this->securityRateLimit; }
    private int $securityInputLengthLimit = 256 * 1024;
        public function getSecurityInputLengthLimit(): int { return $this->securityInputLengthLimit; }

    #/ VARIABLES
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ INIT

    /**
     * Create a new Config instance.
     *
     * @param string $siteName   The name of the site (e.g., "main", "admin").
     * @param bool $restricted   Whether the site requires authorization or not.
     *
     * @return Config
     */
    public static function new(string $siteName, bool $restricted): Config {
        return new Config($siteName, $restricted);
    }

    private function  __construct(string $siteName, bool $restricted) {
        $this->siteName = $siteName;
        $this->restricted = $restricted;
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
     * Configure the authorization settings.
     *
     * @param string $sessionName   The name of the session to use for authorization.
     * @param int $expiryDays       The number of days the authorization session should last.
     * @param bool $globalAuth      Whether the authorization should be valid across the parent domain and all subdomains.
     *                              (e.g. "example.com" becomes ".example.com", "lin.sub.example.com" becomes ".sub.example.com")
     */
    public function configureAuthorization(string $sessionName, int $expiryDays, bool $globalAuth = false): void {
        $this->authSessionName = $sessionName;
        $this->authExpiryDays = $expiryDays;
        $this->authGlobal = $globalAuth;
    }

    /**
     * Configure the default not found redirect settings.
     *
     * @param string $path      The path to redirect not found requests to.
     */
    public function configureNotFoundRedirects(string $path): void {
        $this->defaultNotFoundPathRedirect = $path;
    }

    /**
     * Configure the default unauthorized redirect settings.
     *
     * @param string $path      The path to redirect unauthorized requests to.
     * @param string $subdomain The subdomain to redirect unauthorized requests to. Leave empty to use the main domain.
     */
    public function configureUnauthorizedRedirects(string $path, string $subdomain = ""): void {
        $this->defaultUnauthorizedPathRedirect = $path;
        $this->defaultUnauthorizedSubdomainRedirect = $subdomain;
    }

    /**
     * Configure the privacy settings.
     *
     * @param bool $registerLogs   Define whether to register logs or not.
     * @param bool $registerOrigin Define whether to register origin data or not.
     */
    public function configurePrivacy(bool $registerLogs = true, bool $registerOrigin = true): void {
        $this->privacyRegisterLogs = $registerLogs;
        $this->privacyRegisterOrigin = $registerOrigin;
    }

    /**
     * Configure the security settings.
     *
     * @param int $rateLimit       The number of requests allowed per minute.
     * @param int $inputLengthLimit The maximum length of input data allowed in bytes.
     */
    public function configureSecurity(int $rateLimit = 100, int $inputLengthLimit = 256 * 1024): void {
        $this->securityRateLimit = $rateLimit;
        $this->securityInputLengthLimit = $inputLengthLimit;
    }

    #/ METHODS
    #----------------------------------------------------------------------
}