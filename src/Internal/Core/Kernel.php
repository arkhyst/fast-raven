<?php

namespace SmartGoblin\Internal\Core;

use SmartGoblin\Internal\Core\Craft\Meta;
use SmartGoblin\Components\Core\Config;
use SmartGoblin\Components\Http\Response;
use SmartGoblin\Components\Http\Request;

use SmartGoblin\Exceptions\BadImplementationException;
use SmartGoblin\Helpers\Bee;

use Dotenv\Dotenv;

final class Kernel {
    private Meta $meta;
    private Config $config;
    private Request $request;

    private array $apiRoutes;
    private array $viewRoutes;
    
    public function  __construct() {
        $this->meta = new Meta();
    }

    public function setup(Config $config): void {
        $this->config = $config;
        Dotenv::createImmutable($config->getSitePath() . DIRECTORY_SEPARATOR . "config")->load();

        $this->request = new Request($_SERVER["REQUEST_URI"], $_SERVER["REQUEST_METHOD"], file_get_contents("php://input"));
    }

    public function processApi(&$response): void {
        $foundEndpoint = $this->apiRoutes[$this->request->getComplexPath()];

        if ($foundEndpoint) {
            $fn = require_once $this->config->getSitePath() . DIRECTORY_SEPARATOR . $foundEndpoint->getFile() . ".php";
            if (is_callable($fn)) $response = $fn($this->request);
            if (!$response instanceof Response) { 
                $response = null;
                throw new BadImplementationException("API file {$foundEndpoint->getFile()} expected to return Response object.");
            }
        }
    }

    public function processView(&$response): void {
        $foundEndpoint = $this->viewRoutes[$this->request->getComplexPath()];

        if ($foundEndpoint) {
            readFile($this->config->getSitePath() . DIRECTORY_SEPARATOR . $foundEndpoint['file_path'] . ".html");
            $response = Response::new(true, 200);
        }
    }

    public function packMetadata(): void {
        if (Bee::isDev()) {
            Header("X-Meta-Request-Time: " . microtime(true) - $this->meta->getStartRequestTime());
        }
    }

    public function getConfig(): Config { return $this->config; }
    public function getRequest(): Request { return $this->request; }
}