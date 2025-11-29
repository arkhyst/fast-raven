<?php

namespace SmartGoblin\Internal\Core;

use SmartGoblin\Components\Core\Config;
use SmartGoblin\Components\Http\Response;
use SmartGoblin\Components\Http\Request;
use SmartGoblin\Components\Http\DataType;
use SmartGoblin\Components\Core\Template;
use SmartGoblin\Components\Routing\Router;

use SmartGoblin\Worker\AuthWorker;
use SmartGoblin\Worker\HeaderWorker;

use SmartGoblin\Internal\Slave\LogSlave;
use SmartGoblin\Internal\Slave\HeaderSlave;
use SmartGoblin\Internal\Slave\AuthSlave;
use SmartGoblin\Internal\Slave\DataSlave;
use SmartGoblin\Internal\Slave\RouterSlave;

use SmartGoblin\Exceptions\BadImplementationException;
use SmartGoblin\Exceptions\EndpointFileDoesNotExist;
use SmartGoblin\Exceptions\NotAuthorizedException;

use SmartGoblin\Worker\Bee;

use Dotenv\Dotenv;

final class Kernel {
    #----------------------------------------------------------------------
    #\ VARIABLES

    private Config $config;
        public function setConfig(Config $config): void { $this->config = $config; }
    private Template $template;
        public function setTemplate(Template $template): void { $this->template = $template; }
    private Router $viewRouter;
        public function setViewRouter(Router $router): void { $this->viewRouter = $router; }
    private Router $apiRouter;
        public function setApiRouter(Router $router): void { $this->apiRouter = $router; }

    private Request $request;
    private float $startRequestTime;

    private LogSlave $logSlave;
    private HeaderSlave $headerSlave;
    private AuthSlave $authSlave;
    private DataSlave $dataSlave;
    private RouterSlave $routerSlave;

    #/ VARIABLES
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ INIT

    public function  __construct() {
        
    }

    #/ INIT
    #----------------------------------------------------------------------
    
    #----------------------------------------------------------------------
    #\ PRIVATE FUNCTIONS



    #/ PRIVATE FUNCTIONS
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ METHODS
    
    public function isApiRequest(): bool { return $this->request->isApi(); }

    public function open(): void {
        $this->startRequestTime = microtime(true);

        define("SITE_PATH", $this->config->getSitePath());

        $envPath = $this->config->getSitePath() . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR;

        Dotenv::createImmutable($envPath, ".env")->safeLoad();
        Dotenv::createImmutable($envPath, Bee::isDev() ? ".env.dev" : ".env.prod")->safeLoad();

        // Add security for remote address
        $this->request = new Request($_SERVER["REQUEST_URI"], $_SERVER["REQUEST_METHOD"], file_get_contents("php://input"), $_SERVER["REMOTE_ADDR"]);
        
        $this->logSlave = LogSlave::zap();
        $this->logSlave->writeOpenLogs($this->request);

        $this->headerSlave = HeaderSlave::zap();
        $this->headerSlave->writeSecurityHeaders($this->config->getAllowedHosts(), $_SERVER["HTTPS"], $_SERVER["HTTP_ORIGIN"] ?? "");
        $this->headerSlave->writeUtilityHeaders($this->request->isApi());

        $this->authSlave = AuthSlave::zap();
        $this->authSlave->initializeSessionCookie($this->config->getAuthSessionName(), $this->config->getAuthLifetime(), $this->config->getAuthDomain());

        $this->dataSlave = DataSlave::zap();

        $this->routerSlave = RouterSlave::zap();
    }

    public function process(): ?Response {
        $api = $this->request->isApi();
        $mid = $api ? "api" : "views";
        $endpoint = $this->routerSlave->route($this->request, $api ? $this->apiRouter : $this->viewRouter);
        $response = null;

        if($endpoint) {
            if($endpoint->getRestricted() && !AuthWorker::isAuthorized($this->request)) throw new NotAuthorizedException("Not authorized to make this request.");

            $filePath = SITE_PATH . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . $mid . DIRECTORY_SEPARATOR . $endpoint->getFile();
            if(!file_exists($filePath)) throw new EndpointFileDoesNotExist("Request file could not be loaded, it does not exist. (Payload: $filePath)");
            
            if($this->request->isApi()) {
                $fn = require_once $filePath;
                if (is_callable($fn)) $response = $fn($this->request);
                if (!$response instanceof Response) throw new BadImplementationException("API file {$endpoint->getFile()} expected to return Response object.");
            } else {
                $template = $this->template;
                $epTemplate = $endpoint->getTemplate();
                if($epTemplate) $template->merge($epTemplate);
                $template->setFile($filePath);

                require_once __DIR__ . DIRECTORY_SEPARATOR . "Template" . DIRECTORY_SEPARATOR . "main.php";
                $response = Response::new(true, 200);
            }
        }

        return $response;
    }   

    public function close(?Response $response): void {
        session_write_close();

        if(!$response) {
            if($this->request->isApi()) {
                $response = Response::new(false, 404);
            } else {
                $response = Response::new(false, 301);
                HeaderWorker::addHeader( "Location", $this->config->getDefaultPathRedirect());
            }
        }
        
        http_response_code($response->getCode());

        $type = $this->request->isApi() ? DataType::JSON : DataType::HTML;
        HeaderWorker::addHeader( "Content-Type", "{$type->value}; charset=utf-8");

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

        $this->logSlave->writeCloseLogs($this->request, $elapsedTime);
        $this->logSlave->dumpLogStashIntoFile();
    }

    #/ METHODS
    #----------------------------------------------------------------------  
}