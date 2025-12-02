<?php

namespace SmartGoblin\Components\Core;


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
    private string $authDomain = "localhost";
         public function getAuthDomain(): string { return $this->authDomain; }

    private string $defaultNotFoundPathRedirect = "/";
        public function getDefaultNotFoundPathRedirect(): string { return $this->defaultNotFoundPathRedirect; }
    private string $defaultUnauthorizedPathRedirect = "/login";
        public function getDefaultUnauthorizedPathRedirect(): string { return $this->defaultUnauthorizedPathRedirect; }
    private string $defaultUnauthorizedSubdomainRedirect = "";
        public function getDefaultUnauthorizedSubdomainRedirect(): string { return $this->defaultUnauthorizedSubdomainRedirect; }

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
     * @deprecated This setting is not used and will be removed in the next version
     * Configure the allowed hosts for this site.
     *
     * @param array $allowedHosts   An array of allowed hosts. Use "*" to allow all hosts.
     */
    public function configureAllowedHosts(array $allowedHosts): void {
        $this->allowedHosts = $allowedHosts;
    }

    /**
     * Configure the authorization settings.
     *
     * @param string $sessionName   The name of the session to use for authorization.
     * @param int $expiryDays       The number of days the authorization session should last.
     * @param string $domain        The domain to use for the authorization session.
     */
    public function configureAuthorization(string $sessionName, int $expiryDays, string $domain): void {
        $this->authSessionName = $sessionName;
        $this->authExpiryDays = $expiryDays;
        $this->authDomain = $domain;
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

    #/ METHODS
    #----------------------------------------------------------------------
}