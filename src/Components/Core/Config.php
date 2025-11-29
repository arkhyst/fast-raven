<?php

namespace SmartGoblin\Components\Core;


final class Config {
    #----------------------------------------------------------------------
    #\ VARIABLES

    private string $sitePath;
        public function getSitePath(): string { return $this->sitePath; }
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

    private string $defaultPathRedirect = "/login";
        public function getDefaultPathRedirect(): string { return $this->defaultPathRedirect; }
    private string $defaultSubdomainRedirect = "";
        public function getDefaultSubdomainRedirect(): string { return $this->defaultSubdomainRedirect; }

    #/ VARIABLES
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ INIT

    /**
     * Create a new Config instance.
     *
     * @param string $sitePath   The local path of the site. Use __DIR__ unless you know what you are doing.
     * @param string $siteName   The name of the site.
     * @param bool $restricted   Whether the site requires authorization or not.
     *
     * @return Config
     */
    public static function new(string $sitePath, string $siteName, bool $restricted): Config {
        return new Config($sitePath, $siteName, $restricted);
    }

    private function  __construct(string $sitePath, string $siteName, bool $restricted) {
        $this->sitePath = $sitePath;
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
     * Configure the default unauthorized redirect settings.
     *
     * @param string $path      The path to redirect unauthorized requests to.
     * @param string $subdomain The subdomain to redirect unauthorized requests to. Leave empty to use the main domain.
     */
    public function configureUnauthorizedRedirects(string $path, string $subdomain = ""): void {
        $this->defaultPathRedirect = $path;
        $this->defaultSubdomainRedirect = $subdomain;
    }

    #/ METHODS
    #----------------------------------------------------------------------
}