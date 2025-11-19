<?php

namespace SmartGoblin\Components\Router;

class Endpoint {
    private bool $restricted;
    private string $complexPath;
    private string $file;

    public static function new(bool $restricted, string $method, string $path, string $fileName): Endpoint {
        return new Endpoint($restricted, $method, $path, $fileName);
    }

    public function __construct(bool $restricted, string $method, string $path, string $fileName) {
        $this->restricted = $restricted;
        $this->complexPath = $path."#".$method;

        $fileName = rtrim($fileName, ".php");
        $fileName = rtrim($fileName,".html");
        $this->file = ltrim($fileName, DIRECTORY_SEPARATOR);
    }

    public function getRestricted(): bool { return $this->restricted; }
    public function getComplexPath(): string { return $this->complexPath; }
    public function getFile(): string { return $this->file; }
}