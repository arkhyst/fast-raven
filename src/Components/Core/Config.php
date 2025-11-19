<?php

namespace SmartGoblin\Components\Core;

use SmartGoblin\Components\Router\Endpoint;

final class Config {
    private string $sitePath;
    private string $siteName;
    private bool $restricted;
    private string $defaultPathRedirect;
    private string $defaultSubdomainRedirect;

    private array $allowedHosts = ["*"];
    private array $apiRoutes = [];
    private array $viewRoutes = [];

    public static function new(string $sitePath, string $siteName, bool $restricted = true, string $defaultPathRedirect = "/login", string $defaultSubdomainRedirect = ""): Config {
        return new Config($sitePath, $siteName, $restricted, $defaultPathRedirect, $defaultSubdomainRedirect);
    }

    public function  __construct(string $sitePath, string $siteName, bool $restricted = true, string $defaultPathRedirect = "/login", string $defaultSubdomainRedirect = "") {
        $this->sitePath = $sitePath;
        $this->siteName = $siteName;
        $this->restricted = $restricted;
        $this->defaultPathRedirect = $defaultPathRedirect;
        $this->defaultSubdomainRedirect = $defaultSubdomainRedirect;
    }

    public function configureAllowedHosts(array $allowedHosts): void {
        $this->allowedHosts = $allowedHosts;
    }

    public function configureApi(array $list): void { 
        foreach ($list as $e) {
            if($e instanceof Endpoint) {
                $this->apiRoutes[$e->getComplexPath()] = $e;
            }
        }
    }

    public function configureViews(array $list): void { 
        foreach ($list as $e) {
            if($e instanceof Endpoint) {
                $this->viewRoutes[$e->getComplexPath()] = $e;
            }
        }
    }
    
    public function getSitePath(): string { return $this->sitePath; }
    public function getSiteName(): string { return $this->siteName; }
    public function isRestricted(): bool { return $this->restricted; }
    public function getDefaultPathRedirect(): string { return $this->defaultPathRedirect; }
    public function getDefaultSubdomainRedirect(): string { return $this->defaultSubdomainRedirect; }

}