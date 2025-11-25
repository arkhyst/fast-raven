<?php

namespace SmartGoblin\Components\Core;

use SmartGoblin\Components\Router\Endpoint;

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

    private array $apiRoutes = [];
        public function getApiRoutes(): array { return $this->apiRoutes; }
    private array $viewRoutes = [];
        public function getViewRoutes(): array { return $this->viewRoutes; }

    #/ VARIABLES
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ INIT

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

    public function configureAllowedHosts(array $allowedHosts): void {
        $this->allowedHosts = $allowedHosts;
    }

    public function configureAuthorization(string $sessionName, int $expiryDays, string $domain): void {
        $this->authSessionName = $sessionName;
        $this->authExpiryDays = $expiryDays;
        $this->authDomain = $domain;
    }

    public function configureUnauthorizedRedirects(string $path, string $subdomain): void {
        $this->defaultPathRedirect = $path;
        $this->defaultSubdomainRedirect = $subdomain;
    }

    public function configureApi(array $list): void { 
        foreach ($list as $e) {
            if($e instanceof Endpoint) {
                $this->apiRoutes["/api".$e->getComplexPath()] = $e;
            }
        }
    }

    public function configureView(array $list): void { 
        foreach ($list as $e) {
            if($e instanceof Endpoint) {
                $this->viewRoutes[$e->getComplexPath()] = $e;
            }
        }
    }

    #/ METHODS
    #----------------------------------------------------------------------
}