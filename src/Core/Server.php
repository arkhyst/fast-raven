<?php

namespace SmartGoblin\Core;

use SmartGoblin\Http\DataType;
use SmartGoblin\Http\Response;

class Server {
    public static function run(Kernel $kernel): void {
        
        $foundEndpoint = self::routingApi($kernel->getRouteApi());
        if($foundEndpoint["found"]) {
            $worker = require_once $foundEndpoint["file_path"];
            $res = $worker($kernel->getRequest());

            $kernel->packMetadata();
            $res->send();
        }

        $foundView = self::routingView($kernel->getRouteView());
        if($foundView["found"]) {
            $res = new Response(true, 200, DataType::HTML);
            readfile($foundView["file_path"]);

            $kernel->packMetadata();
            $res->send();
        }
    }

    private static function routingApi(array $list) : array {
        $found = false;
        $filePath = "";

        return [
            "found" => $found,
            "file_path" => $filePath
        ];
    }

    private static function routingView(array $list) : array {
        $found = false;
        $filePath = "";

        return [
            "found" => $found,
            "file_path" => $filePath
        ];
    }
}