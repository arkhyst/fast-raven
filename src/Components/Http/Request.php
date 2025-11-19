<?php

namespace SmartGoblin\Components\Http;

final class Request {
    private array $data;
    private string $complexPath;

    public function __construct(string $uri, string $method, string $dataStream) {
        $this->data = json_decode($dataStream, true) ?? [];

        $requestPath = parse_url($uri ?? "/", PHP_URL_PATH) ?: "/";
        $requestPath = ($requestPath !== "/") ? rtrim($requestPath, "/"): "/";
        $this->complexPath = $requestPath . "#" . $method;
    }

    public function getDataItem(string $key): string|int|bool|null {
        if(isset($this->data[$key])) {
            return trim(strip_tags($this->data[$key]));
        }

        return null;
    }
    public function getComplexPath(): string { return $this->complexPath; }
}

?>