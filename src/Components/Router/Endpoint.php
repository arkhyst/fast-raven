<?php

namespace SmartGoblin\Components\Router;

use SmartGoblin\Helpers\Bee;

final class Endpoint {
    private bool $restricted;
    private string $complexPath;
    private string $file;

    public static function api(bool $restricted, string $httpMethod, string $uri, string $fileName): Endpoint {
        return new Endpoint($restricted, $httpMethod, $uri, $fileName);
    }

    public static function view(bool $restricted, string $uri, string $fileName): Endpoint {
        return new Endpoint($restricted, "GET", $uri, $fileName);
    }

    private function __construct(bool $restricted, string $method, string $uri, string $fileName) {
        $this->restricted = $restricted;
        $this->complexPath = "/".Bee::normalizePath($uri)."#".$method;
        $this->file = Bee::normalizePath($fileName, true);
    }

    public function getRestricted(): bool { return $this->restricted; }
    public function getComplexPath(): string { return $this->complexPath; }
    public function getFile(): string { return $this->file; }
}