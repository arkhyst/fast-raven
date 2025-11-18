<?php

namespace SmartGoblin\Helpers;

class Endpoint {
    public static function build(bool $restricted, string $method, string $request, string $file) : array {
        return [
            "restricted" => $restricted,
            "method" => $method,
            "request" => $request,
            "file" => $file
        ];
    }
}