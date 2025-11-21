<?php

namespace SmartGoblin\Components\Core;

use SmartGoblin\Components\Router\Endpoint;

final class Config {
    private string $sitePath;
    private string $siteName;
    private bool $restricted;

    private array $allowedHosts = ["*"];
    
    private string $defaultPathRedirect = "/login";
    private string $defaultSubdomainRedirect = "";

    private array $apiRoutes = [];
    private array $viewRoutes = [];

    public static function new(string $sitePath, string $siteName, bool $restricted): Config {
        return new Config($sitePath, $siteName, $restricted);
    }

    private function  __construct(string $sitePath, string $siteName, bool $restricted) {
        $this->sitePath = $sitePath;
        $this->siteName = $siteName;
        $this->restricted = $restricted;
    }

    public function configureAllowedHosts(array $allowedHosts): void {
        $this->allowedHosts = $allowedHosts;
    }

    public function configureUnauthorizedRedirects(string $path, string $subdomain): void {
        $this->defaultSubdomainRedirect = $path;
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
    
    public function getSitePath(): string { return $this->sitePath; }
    public function getSiteName(): string { return $this->siteName; }
    public function isRestricted(): bool { return $this->restricted; }
    public function getAllowedHosts(): array { return $this->allowedHosts; }
    public function getDefaultPathRedirect(): string { return $this->defaultPathRedirect; }
    public function getDefaultSubdomainRedirect(): string { return $this->defaultSubdomainRedirect; }

    public function getApiRoutes(): array { return $this->apiRoutes; }
    public function getViewRoutes(): array { return $this->viewRoutes; }
}