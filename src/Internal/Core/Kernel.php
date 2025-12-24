<?php

namespace FastRaven\Internal\Core;

use FastRaven\Workers\AuthWorker;
use FastRaven\Workers\HeaderWorker;
use FastRaven\Workers\StorageWorker;
use FastRaven\Workers\Bee;

use FastRaven\Internal\Slave\LogSlave;
use FastRaven\Internal\Slave\HeaderSlave;
use FastRaven\Internal\Slave\AuthSlave;
use FastRaven\Internal\Slave\DataSlave;
use FastRaven\Internal\Slave\ValidationSlave;
use FastRaven\Internal\Slave\MailSlave;
use FastRaven\Internal\Slave\StorageSlave;

use FastRaven\Components\Core\Config;
use FastRaven\Components\Http\Response;
use FastRaven\Components\Http\Request;
use FastRaven\Components\Core\Template;
use FastRaven\Components\Routing\Router;
use FastRaven\Components\Routing\Endpoint;

use FastRaven\Exceptions\BadImplementationException;
use FastRaven\Exceptions\EndpointFileNotFoundException;
use FastRaven\Exceptions\NotAuthorizedException;
use FastRaven\Exceptions\AlreadyAuthorizedException;
use FastRaven\Exceptions\NotFoundException;
use FastRaven\Exceptions\RateLimitExceededException;
use FastRaven\Exceptions\UploadedFileNotFoundException;

use FastRaven\Components\Types\MiddlewareType;

final class Kernel {
    #----------------------------------------------------------------------
    #\ VARIABLES

    private Config $config;
        public function getConfig(): Config { return $this->config; }
        public function setConfig(Config $config): void { $this->config = $config; }
    private Template $template;
        public function setTemplate(Template $template): void { $this->template = $template; }
    private Router $viewRouter;
        public function setViewRouter(Router $router): void { $this->viewRouter = $router; }
    private Router $apiRouter;
        public function setApiRouter(Router $router): void { $this->apiRouter = $router; }
    private Router $cdnRouter;
        public function setCdnRouter(Router $router): void { $this->cdnRouter = $router; }

    private Request $request;
        public function getRequest(): Request { return $this->request; }

    private LogSlave $logSlave;
    private HeaderSlave $headerSlave;
    private AuthSlave $authSlave;
    private DataSlave $dataSlave;
    private ValidationSlave $validationSlave;
    private MailSlave $mailSlave;
    private StorageSlave $storageSlave;

    private float $startRequestTime;
    private int $rateLimitRemaining = 0;
    private int $rateLimitTimeRemaining = 0;

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

    /**
     * Handles routing for the request.
     *
     * @param Router $router The router to use.
     * 
     * @return Endpoint|null The matched endpoint, or null if no match is found.
     */
    private function handleRouting(Router $router): ?Endpoint {
        foreach($router->getEndpointList() as $ep) {
            if($ep instanceof Endpoint) {
                if($ep->getType() !== MiddlewareType::ROUTER && $ep->getComplexPath() === $this->request->getComplexPath()) {
                    return $ep;
                } else if($ep->getType() === MiddlewareType::ROUTER && str_starts_with($this->request->getPath(), $ep->getPath())) {
                    $nestedRouter = require SITE_PATH . "config" . DIRECTORY_SEPARATOR . "router" . DIRECTORY_SEPARATOR . $ep->getFile();
                    if($nestedRouter instanceof Router) {
                        return $this->handleRouting($nestedRouter);
                    }
                }
            }
        }

        return null;
    }

    /**
     * Handles rate limiting for the request.
     *
     * @param int $limit The configured rate limit for the request.
     * @param string|null $endpoint The endpoint for the request. Empty if global rate limit.
     * 
     * @return bool True if host does not exceed its rate limit, false otherwise.
     */
    private function handleRateLimit(int $limit, ?string $endpoint = null): bool {
        if ($limit > 0) {
            $rateLimitID = "fastraven:". $this->config->getSiteName().($endpoint ? "/$endpoint" : "").":ratelimit:". md5($_SERVER["REMOTE_ADDR"]);
            if (function_exists("apcu_enabled") && apcu_enabled()) {
                $count = apcu_inc($rateLimitID, 1, $success, 60);
                $this->rateLimitRemaining = $limit - $count;
                $this->rateLimitTimeRemaining = apcu_key_info($rateLimitID)["ttl"];
            } else {
                $cacheItem = StorageWorker::getCache($rateLimitID);
                $count = 0;
                if($cacheItem) {
                    StorageWorker::incrementCache($rateLimitID, 1);
                    $count = $cacheItem["value"] + 1;
                } else {
                    StorageWorker::setCache($rateLimitID, 1, 60);
                    $count = 1;
                }
                
                $this->rateLimitRemaining = $limit - $count;
                $this->rateLimitTimeRemaining = $cacheItem ? $cacheItem["expires"] - time() : 60;
            }

            if($this->rateLimitRemaining < 0 && $this->rateLimitTimeRemaining > 0) return false;
        }

        return true;
    }

    #/ PRIVATE FUNCTIONS
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ METHODS
    
    public function isApiRequest(): bool { return $this->request->getType() === MiddlewareType::API; }
    public function isCdnRequest(): bool { return $this->request->getType() === MiddlewareType::CDN; }

    /**
     * This function initializes the kernel and prepares it for processing the request.
     *
     * It sets up the environment variables, loads the configuration from the .env files,
     * adds security headers, initializes the session cookie, and sets up the router.
     *
     * It should be called at the beginning of every request.
     * 
     * @throws NotAuthorizedException If the endpoint is restricted and the request is not authorized.
     * @throws RateLimitExceededException If the host exceeds its rate limit.
     */
    public function open(): void {
        $this->startRequestTime = microtime(true);

        $inputLengthLimit = $this->config->getLengthLimitInput() >= 0 ? $this->config->getLengthLimitInput() : null;
        $this->request = new Request(
            $_SERVER["REQUEST_URI"],
            $_SERVER["REQUEST_METHOD"],
            file_get_contents("php://input", false, null, 0, $inputLengthLimit),
            $_FILES,
            $this->config->isPrivacyRegisterOrigin() ? $_SERVER["REMOTE_ADDR"] : "HOST"
        );
        
        if($this->config->isPrivacyRegisterLogs()) {
            $this->logSlave = LogSlave::zap($this->request->getInternalID());
            $this->logSlave->writeOpenLogs($this->request);
        }

        if(!$this->handleRateLimit($this->config->getRateLimit($this->request->getType())))
            throw new RateLimitExceededException($this->request->getRemoteAddress(), $this->rateLimitRemaining, $this->rateLimitTimeRemaining);

        $this->authSlave = AuthSlave::zap();
        $this->authSlave->initializeSessionCookie($this->config->getAuthSessionName(), $this->config->getAuthLifetime(), $this->config->isAuthGlobal());

        if($this->config->isRestricted() && !AuthWorker::isAuthorized($this->request))
            throw new NotAuthorizedException();

        $this->headerSlave = HeaderSlave::zap();
        $this->headerSlave->writeSecurityHeaders($_SERVER["HTTPS"]);
        $this->headerSlave->writeUtilityHeaders($this->request->getType() === MiddlewareType::API);
        $this->headerSlave->writeRateLimitHeaders($this->config->getRateLimit($this->request->getType()), $this->rateLimitRemaining, $this->rateLimitTimeRemaining);

        $this->dataSlave = DataSlave::zap();


        $this->validationSlave = ValidationSlave::zap();

        $this->mailSlave = MailSlave::zap();

        $this->storageSlave = StorageSlave::zap();
        $this->storageSlave->setFileUploadSizeLimit($this->config->getLengthLimitFileUpload());
    }

    /**
     * Processes the request and returns a response.
     *
     * This function will try to match the request with an endpoint in the router.
     * If the endpoint is restricted and the request is not authorized, it will throw a NotAuthorizedException.
     * If the endpoint file does not exist, it will throw an EndpointFileNotFoundException exception.
     * If the request is an API request, it will call the function in the endpoint file and expect a Response object to be returned.
     * If the request is not an API request, it will render the template in the endpoint file.
     *
     * @return Response The response to return to the client.
     * 
     * @throws NotFoundException If no matching route is found for the request.
     * @throws NotAuthorizedException If the endpoint is restricted and the request is not authorized.
     * @throws AlreadyAuthorizedException If the endpoint is unauthorized exclusive and the request is authorized.
     * @throws EndpointFileNotFoundException If the endpoint file does not exist.
     * @throws BadImplementationException If the API function does not return a Response object.
     */
    public function process(): Response {
        $mid = "web/views/pages";
        $router = $this->viewRouter;
        if($this->request->getType() == MiddlewareType::API) {
            $router = $this->apiRouter;
            $mid = "api";
        } elseif($this->request->getType() == MiddlewareType::CDN) {
            $router = $this->cdnRouter;
            $mid = "cdn";
        }

        $endpoint = $this->handleRouting($router);
        $response = null;

        if(!$endpoint) throw new NotFoundException();

        if(!$this->handleRateLimit($endpoint->getLimitPerMinute(), $endpoint->getComplexPath())) {
            $this->headerSlave->writeRateLimitHeaders($endpoint->getLimitPerMinute(), $this->rateLimitRemaining, $this->rateLimitTimeRemaining);
            throw new RateLimitExceededException($this->request->getRemoteAddress(), $this->rateLimitRemaining, $this->rateLimitTimeRemaining);
        }

        if($endpoint->getRestricted() && !AuthWorker::isAuthorized($this->request)) throw new NotAuthorizedException();
        if($endpoint->getUnauthorizedExclusive() && AuthWorker::isAuthorized($this->request)) throw new AlreadyAuthorizedException();

        $filePath = SITE_PATH . "src" . DIRECTORY_SEPARATOR . $mid . DIRECTORY_SEPARATOR . $endpoint->getFile();
        if(!file_exists($filePath)) throw new EndpointFileNotFoundException($filePath);
        
        if($this->request->getType() === MiddlewareType::VIEW) {
            $response = Response::new(true, 200, "", [
                "path" => __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "Template" . DIRECTORY_SEPARATOR . "main.php",
                "template"=> $this->template->merge($endpoint->getTemplate())->setFile($filePath)->sanitize()
            ]);
        } else {
            $fn = require_once $filePath;
            if (is_callable($fn)) $response = $fn($this->request);
            if ($response === null || !$response instanceof Response) throw new BadImplementationException($endpoint->getFile());
            
            $resPath = $response->getData()["path"] ?? null;
            if($resPath) {
                if(!StorageWorker::fileExists($resPath)) throw new UploadedFileNotFoundException($resPath);
                else {
                    $fullPath = StorageWorker::getUploadFilePath($resPath);
                    $response->setDataType(Bee::getFileMimeType($fullPath, true));
                    $response->setBody("", ["path" => $fullPath]);
                }
            }
        }

        return $response;
    }   

    /**
     * This function is called at the end of every request and is responsible for outputting the response,
     * writing the close logs, and dumping the log stash into a file.
     *
     * If no response is provided, it will generate a default response based on the request type.
     * If the request is an API request, it will generate a 404 response.
     * If the request is not an API request, it will generate a 301 response with a default path redirect.
     *
     * @param Response $response The response to output or process
     */
    public function close(Response $response): void {
        http_response_code($response->getCode());
        session_write_close();

        if($this->request->getType() == MiddlewareType::VIEW) {
            HeaderWorker::addHeader( "Content-Type", "text/html; charset=utf-8");
            $template = $response->getData()["template"];
            require_once $response->getData()["path"];
        } else if ($this->request->getType() == MiddlewareType::API) {
            HeaderWorker::addHeader( "Content-Type", "application/json; charset=utf-8");
            echo json_encode([
                "success" => $response->getSuccess(),
                "msg" => $response->getMessage(),
                "data" => $response->getData()
            ]);
        } elseif($this->request->getType() == MiddlewareType::CDN) {
            HeaderWorker::addHeader( "Content-Type", $response->getDataType()->value);
            readfile($response->getData()["path"]);
        }

        if (function_exists("fastcgi_finish_request")) fastcgi_finish_request();

        $diff = microtime(true) - $this->startRequestTime;
        $elapsedTime = round(($diff - floor($diff)) * 1000);

        if($this->logSlave) {
            $this->logSlave->writeCloseLogs($elapsedTime);
            $this->logSlave->dumpLogStashIntoFile();
        }
        
        // TODO: Test performance impact.
        if(random_int(0,100) < $this->config->getCacheFileGCProbability()) { 
            $this->storageSlave->runGarbageCollector($this->config->getCacheFileGCPower());
        }
    }

    #/ METHODS
    #----------------------------------------------------------------------  
}