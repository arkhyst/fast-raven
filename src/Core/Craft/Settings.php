<?php

namespace SmartGoblin\Core\Craft;

class Settings {
    private string $sitePath;
    private string $siteName;
    private bool $restricted;
    private string $defaultPathRedirect;
    private string $defaultSubdomainRedirect;

    public function  __construct(string $sitePath, string $siteName, bool $restricted = true, string $defaultPathRedirect = "/login", string $defaultSubdomainRedirect = "") {
        $this->sitePath = $sitePath;
        $this->siteName = $siteName;
        $this->restricted = $restricted;
        $this->defaultPathRedirect = $defaultPathRedirect;
        $this->defaultSubdomainRedirect = $defaultSubdomainRedirect;
    }

    public function getSitePath(): string { return $this->sitePath; }
    public function getSiteName(): string { return $this->siteName; }
    public function isRestricted(): bool { return $this->restricted; }
    public function getDefaultPathRedirect(): string { return $this->defaultPathRedirect; }
    public function getDefaultSubdomainRedirect(): string { return $this->defaultSubdomainRedirect; }

}