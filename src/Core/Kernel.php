<?php

namespace SmartGoblin\Core;

declare(strict_types=1);
require __DIR__."/../vendor/autoload.php";

use SmartGoblin\Http\Request;
use SmartGoblin\Core\Craft\Settings;
use SmartGoblin\Core\Craft\Meta;
use SmartGoblin\Helpers\Bee;

use Dotenv\Dotenv;

class Kernel {
    private Meta $meta;
    private Settings $config;
    private Request $request;

    private array $apiRoutes;
    private array $viewRoutes;
    
    public function  __construct() {
        $this->meta = new Meta();
        Dotenv::createImmutable(__DIR__)->load();
    }

    /**
     * Basic configuration for server
     * @param Settings $config
     * @return void
     */
    public function setup(Settings $config) : void {
        $this->config = $config;
        $this->request = new Request();
    }

    /**
     * Configure API endpoints
     * @param array<array> $list Use request Endpoint::api as value.
     * @return void
     */
    public function configureApi(array $list) : void {
        foreach ($list as $e) {
            $requestPath = $e["request"]."#".$e["method"];
            $this->apiRoutes[$requestPath] = [
                $e["restricted"],
                $e["file"]
            ];
        }
    }

    /**
     * Configure Views endpoints
     * @param array<string, array> $list Use Endpoint::view as value.
     * @return void
     */
    public function configureViews(array $list) : void {
        foreach ($list as $e) {
            $requestPath = $e["request"];
            $this->viewRoutes[$requestPath] = [
                $e["restricted"],
                $e["file"]
            ];
        }
    }

    public function packMetadata() : void {
        if (Bee::isDev()) {
            Header("X-Meta-Request-Time: " . microtime(true) - $this->meta->getStartRequestTime());
        }
    }

    public function getRequest() : Request { return $this->request; }
    public function getRouteApi() : array { return $this->apiRoutes; }
    public function getRouteView() : array { return $this->viewRoutes; }
}