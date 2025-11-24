<?php

namespace SmartGoblin\Internal\Core;

use SmartGoblin\Components\Core\Config;
use SmartGoblin\Components\Http\Response;
use SmartGoblin\Components\Http\Request;
use SmartGoblin\Components\Http\DataType;

use SmartGoblin\Internal\Stash\LoggingStash;
use SmartGoblin\Internal\Stash\HeaderStash;
use SmartGoblin\Internal\Stash\AuthorizationStash;

use SmartGoblin\Internal\Worker\HeaderWorker;
use SmartGoblin\Internal\Worker\LoggingWorker;

use SmartGoblin\Exceptions\BadImplementationException;
use SmartGoblin\Exceptions\EndpointFileDoesNotExist;

use Dotenv\Dotenv;

final class Kernel {

    private Config $config;
    private Request $request;

    private LoggingStash $loggingStash;
    private HeaderStash $headerStash;
    private AuthorizationStash $authorizationStash;

    private float $startRequestTime;
    
    public function  __construct() {
        
    }

    public function open(): void {
        $this->startRequestTime = microtime(true);

        define("SITE_PATH", $this->config->getSitePath());

        Dotenv::createImmutable($this->config->getSitePath() . DIRECTORY_SEPARATOR . "config")->load();

        // Add security for remote address
        $this->request = new Request($_SERVER["REQUEST_URI"], $_SERVER["REQUEST_METHOD"], file_get_contents("php://input"), $_SERVER["REMOTE_ADDR"]);
        
        $this->loggingStash = LoggingStash::pack();
        LoggingWorker::addOpenLogs($this->loggingStash, $this->request);

        $this->headerStash = HeaderStash::pack($this->request->isApi(), $this->config->getAllowedHosts(), $_SERVER["HTTPS"], $_SERVER["HTTP_ORIGIN"] ?? "");
        HeaderWorker::dump($this->headerStash);
    }

    public function close(Response $response): void {
        session_write_close();

        if(!$response) {
            $response = Response::new(false, 301);
            HeaderWorker::addAndDump($this->headerStash, "Location", "/".$this->config->getDefaultPathRedirect());
        }

        http_response_code($response->getCode());

        $type = $this->request->isApi() ? DataType::JSON : DataType::HTML;
        HeaderWorker::addAndDump($this->headerStash, "Content-Type", "{$type->value}; charset=utf-8");

        if ($type == DataType::JSON) {
            echo json_encode([
                "status" => $response->getStatus(),
                "msg" => $response->getMessage(),
                "data" => $response->getData()
            ]);
        }

        if (function_exists("fastcgi_finish_request")) fastcgi_finish_request();

        $diff = microtime(true) - $this->startRequestTime;
        $elapsedTime = round(($diff - floor($diff)) * 1000);

        LoggingWorker::addCloseLogs($this->loggingStash, $this->request, $elapsedTime);
        LoggingWorker::dump($this->loggingStash);
    }

    public function processApi(): ?Response {
        $foundEndpoint = $this->config->getApiRoutes()[$this->request->getComplexPath()];

        if ($foundEndpoint) {
            $filePath = $this->config->getSitePath() . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "api" . DIRECTORY_SEPARATOR . $foundEndpoint->getFile() . ".php";
            if(!file_exists($filePath)) {
                throw new EndpointFileDoesNotExist("API file could not be loaded, it does not exist. (Payload: $filePath)");
            }
            
            $fn = require_once $filePath;
            $response = null;
            if (is_callable($fn)) $response = $fn($this->request);

            if ($response instanceof Response) return $response;
            else { 
                throw new BadImplementationException("API file {$foundEndpoint->getFile()} expected to return Response object.");
            }
        }

        return null;
    }

    public function processView(): ?Response {
        $foundEndpoint = $this->config->getViewRoutes()[$this->request->getComplexPath()];

        if ($foundEndpoint) {
            $filePath = $this->config->getSitePath() . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "views" . DIRECTORY_SEPARATOR  . $foundEndpoint->getFile() . ".html";
            if(!file_exists($filePath)) {
                throw new EndpointFileDoesNotExist("View file could not be rendered, it does not exist. (Payload: $filePath)");
            }

            readFile($filePath);
            return Response::new(true, 200);
        }

        return null;
    }


    public function setConfig(Config $config): void { $this->config = $config; }
    public function isApiRequest(): bool { return $this->request->isApi(); }
}