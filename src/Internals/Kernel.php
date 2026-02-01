<?php

namespace FastRaven\Internals;

use FastRaven\Exceptions\DeveloperException;
use FastRaven\Services\AuthService;
use FastRaven\Services\FileService;
use FastRaven\Services\HeaderService;
use FastRaven\Services\CacheService;
use FastRaven\Bee;

use FastRaven\Internals\Engines\LogEngine;
use FastRaven\Internals\Engines\HeaderEngine;
use FastRaven\Internals\Engines\AuthEngine;
use FastRaven\Internals\Engines\DataEngine;
use FastRaven\Internals\Engines\ValidationEngine;
use FastRaven\Internals\Engines\MailEngine;
use FastRaven\Internals\Engines\FileEngine;
use FastRaven\Internals\Engines\CacheEngine;

use FastRaven\Components\Core\Config;
use FastRaven\Components\Http\Response;
use FastRaven\Components\Http\Request;
use FastRaven\Components\Core\Template;
use FastRaven\Components\Core\File;
use FastRaven\Components\Routing\Router;
use FastRaven\Components\Routing\Endpoint;
use FastRaven\Components\Routing\Middleware;

use FastRaven\Exceptions\BadImplementationException;
use FastRaven\Exceptions\EndpointFileNotFoundException;
use FastRaven\Exceptions\NotAuthorizedException;
use FastRaven\Exceptions\BadMiddlewareException;
use FastRaven\Exceptions\MiddlewareDeniedException;
use FastRaven\Exceptions\NotFoundException;
use FastRaven\Exceptions\RateLimitExceededException;
use FastRaven\Exceptions\UploadedFileNotFoundException;

use FastRaven\Types\EndpointType;
use FastRaven\Types\ProjectFolderType;

final class Kernel {
    #----------------------------------------------------------------------
    #\ VARIABLES

    private Config $config;
        public function getConfig(): Config { return $this->config; }
    private Request $request;
        public function getRequest(): Request { return $this->request; }

    private Template $template;
        public function getTemplate(): Template { return $this->template; }
    private Middleware $middleware;
    private Router $viewRouter;
    private Router $apiRouter;
    private Router $cdnRouter;

    private LogEngine $logEngine;
    private HeaderEngine $headerEngine;
    private AuthEngine $authEngine;
    private DataEngine $dataEngine;
    private ValidationEngine $validationEngine;
    private MailEngine $mailEngine;
    private FileEngine $fileEngine;
    private CacheEngine $cacheEngine;

    private float $startRequestTime;
    private int $rateLimitRemaining = 0;
    private int $rateLimitTimeRemaining = 0;
    private string $nonce;

    #/ VARIABLES
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ INIT

    public function  __construct(Config $config, Template $template, Middleware $middleware, Router $viewRouter, Router $apiRouter, Router $cdnRouter) {
        $this->config = $config;
        $this->template = $template;
        $this->middleware = $middleware;
        $this->viewRouter = $viewRouter;
        $this->apiRouter = $apiRouter;
        $this->cdnRouter = $cdnRouter;
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
        foreach($router->getSubrouterList() as $ep) {
            if(str_starts_with($this->request->getPath(), $ep->getPath())) {
                $nestedRouter = require Bee::buildProjectPath(ProjectFolderType::CONFIG_ROUTER, $ep->getFile());
                if($nestedRouter instanceof Router) return $this->handleRouting($nestedRouter);
            }
        }
        
        if(isset($router->getEndpointList()[$this->request->getComplexPath()]))
            return $router->getEndpointList()[$this->request->getComplexPath()];

        return null;
    }

    /**
     * Handles rate limiting for the request.
     *
     * @param int $limit The configured rate limit for the request.
     * 
     * @return bool True if host does not exceed its rate limit, false otherwise.
     */
    private function handleRateLimit(int $limit): bool {
        if ($limit > 0) {
            $rateLimitID = Bee::getCacheKey("ratelimit", $_SERVER["REMOTE_ADDR"]);
            
            $cacheItem = CacheService::readWithMeta($rateLimitID);
            $newValue = ($cacheItem["value"] ?? 0) + 1;
            $expires = $cacheItem["expires"] ?? time() + 60;
            
            CacheService::write($rateLimitID, $newValue, $cacheItem ? max(1, $expires - time()) : 60);
            $this->rateLimitRemaining = $limit - $newValue;
            $this->rateLimitTimeRemaining = max(0, $expires - time());

            if ($this->rateLimitRemaining < 0 && $this->rateLimitTimeRemaining > 0) return false;
        }

        return true;
    }

    #/ PRIVATE FUNCTIONS
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ METHODS
    
    public function isViewRequest(): bool { return $this->request->getType() === EndpointType::VIEW; }
    public function isApiRequest(): bool { return $this->request->getType() === EndpointType::API; }
    public function isCdnRequest(): bool { return $this->request->getType() === EndpointType::CDN; }

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
    public function open(float $startRequestTime): void {
        $this->startRequestTime = $startRequestTime;
        $this->nonce = bin2hex(random_bytes(16));

        $inputLengthLimit = $this->config->getLengthLimitInput() >= 0 ? $this->config->getLengthLimitInput() : null;
        $this->request = new Request(
            $_SERVER["REQUEST_URI"],
            $_SERVER["REQUEST_METHOD"],
            file_get_contents("php://input", false, null, 0, $inputLengthLimit),
            $_FILES,
            $this->config->isPrivacyRegisterOrigin() ? $_SERVER["REMOTE_ADDR"] : "HOST"
        );
        
        if($this->config->isPrivacyRegisterLogs()) {
            $this->logEngine = LogEngine::zap($this->request->getInternalID());
            $this->logEngine->writeOpenLogs($this->request);
        }

        $this->cacheEngine = CacheEngine::zap();

        if(!$this->handleRateLimit($this->config->getRateLimit($this->request->getType())))
            throw new RateLimitExceededException($this->request->getRemoteAddress(), $this->rateLimitRemaining, $this->rateLimitTimeRemaining);

        $this->authEngine = AuthEngine::zap();
        $this->authEngine->initializeSessionCookie($this->config->getAuthSessionName(), $this->config->getAuthLifetime(), $this->config->isAuthGlobal());
        
        $this->headerEngine = HeaderEngine::zap();
        $this->headerEngine->writeSecurityHeaders($_SERVER["HTTPS"], $this->nonce);
        $this->headerEngine->writeUtilityHeaders($this->request->getType() === EndpointType::API);
        $this->headerEngine->writeRateLimitHeaders($this->config->getRateLimit($this->request->getType()), $this->rateLimitRemaining, $this->rateLimitTimeRemaining);

        if($this->config->isRestricted()) {
            if(!AuthService::isAuthorized($this->request)) throw new NotAuthorizedException(true);
        }

        $this->dataEngine = DataEngine::zap();

        $this->validationEngine = ValidationEngine::zap();

        $this->mailEngine = MailEngine::zap();

        $this->fileEngine = FileEngine::zap($this->config->getLengthLimitFileUpload());
    }

    /**
     * Processes the request and returns a response.
     *
     * This function will try to match the request with an endpoint in the router and process it.
     * If the request is a View request, it will render the template from the endpoint file and expect a Template object to be returned.
     * If the request is an API request, it will call the function from the endpoint file and expect a Response object to be returned.
     * If the request is a CDN request, it will retrieve the file from the endpoint file and expect a File object to be returned.
     *
     * @return Template|Response|File The response to output or process.
     * 
     * @throws NotFoundException If no matching route is found for the request.
     * @throws NotAuthorizedException If the endpoint is restricted and the request is not authorized.
     * @throws EndpointFileNotFoundException If the endpoint file does not exist.
     * @throws BadImplementationException If the API function does not return a Response object.
     * @throws DeveloperException If the developer makes a mistake on View/API/CDN endpoints.
     */
    public function process(): Template|Response|File {
        [$router, $folder] = match ($this->request->getType()) {
            EndpointType::VIEW => [$this->viewRouter, ProjectFolderType::SRC_VIEWS],
            EndpointType::API => [$this->apiRouter, ProjectFolderType::SRC_API],
            EndpointType::CDN => [$this->cdnRouter, ProjectFolderType::SRC_CDN],
        };

        if(!$this->handleRateLimit($router->getLimitPerMinute())) {
            $this->headerEngine->writeRateLimitHeaders($router->getLimitPerMinute(), $this->rateLimitRemaining, $this->rateLimitTimeRemaining);
            throw new RateLimitExceededException($this->request->getRemoteAddress(), $this->rateLimitRemaining, $this->rateLimitTimeRemaining);
        }

        $endpoint = $this->handleRouting($router);
        if(!$endpoint) throw new NotFoundException();
        
        if($endpoint->getRestricted())
            if(!AuthService::isAuthorized($this->request)) throw new NotAuthorizedException();

        if($endpoint->getMiddlewareId() !== "") {
            $middleware = $this->middleware->get($endpoint->getMiddlewareId());
            if(!Bee::validateCallable($middleware, [Request::class])) throw new BadMiddlewareException($endpoint->getFile());
            if($middleware($this->request) === false) throw new MiddlewareDeniedException();
        }

        $filePath = Bee::buildProjectPath($folder, $endpoint->getFile());
        if(!file_exists($filePath)) throw new EndpointFileNotFoundException($filePath);

        $fn = require_once $filePath;
        $response = null;

        if($this->request->getType() === EndpointType::VIEW) {
            if(Bee::validateCallable($fn, [Request::class, Template::class])) {
                try {
                    $response = $fn($this->request, $this->template);
                } catch (\Throwable $e) {
                    throw new DeveloperException($endpoint->getFile(), $e->getMessage());
                }
            }
            if($response === null || !$response instanceof Template) throw new BadImplementationException($endpoint->getFile(), "Template");

            if($this->template !== $response) $response = $this->template->merge($response);
        } else {
            if(Bee::validateCallable($fn, [Request::class])) {
                try {
                    $response = $fn($this->request);
                } catch (\Throwable $e) {
                    throw new DeveloperException($endpoint->getFile(), $e->getMessage());
                }
            }
            if($response === null || !$response instanceof Response) throw new BadImplementationException($endpoint->getFile(), "Response");

            if($this->request->getType() === EndpointType::CDN) {
                $cdnFilePath = $response->getFrameworkMetadata()["path"];
                if(!FileService::exists($cdnFilePath)) throw new UploadedFileNotFoundException(FileService::getUploadFilePath($cdnFilePath));
                $response = File::new("cdn_file", FileService::getUploadFilePath($cdnFilePath));
            }
        }

        return $response;
    }

    /**
     * This function is called at the end of every request and is responsible for outputting the response,
     * writing the close logs, and dumping the log stash into a file.
     *
     * @param Template|Response|File $response The response to output or process
     */
    public function close(Template|Response|File $response): void {
        $statusCode = match(get_class($response)) {
            Template::class => $response->hasData("errorCode") ? intval($response->getData("errorCode")) : 200,
            Response::class => $response->getCode(),
            File::class => 200,
        };
        http_response_code($statusCode);

        if($response instanceof Template) {
            HeaderService::addHeader("Content-Type", "text/html; charset=utf-8");
            $template = $response;
            $nonce = $this->nonce;
            require_once __DIR__ . DIRECTORY_SEPARATOR . "View" . DIRECTORY_SEPARATOR . "main.php";
        } else if ($response instanceof Response) {
            HeaderService::addHeader("Content-Type", "application/json; charset=utf-8");
            echo json_encode([
                "success" => $response->getSuccess(),
                "msg" => $response->getMessage(),
                "data" => $response->getData()
            ]);
        } elseif($response instanceof File) {
            HeaderService::addHeader("Content-Type", $response->getType()->value);
            HeaderService::addHeader("Content-Length", filesize($response->getPath()));
            readfile($response->getPath());
        }
        
        if(session_status() === PHP_SESSION_ACTIVE) session_write_close();

        if (function_exists("fastcgi_finish_request")) fastcgi_finish_request();

        $elapsedTime = round((microtime(true) - $this->startRequestTime) * 1000);

        if($this->mailEngine) {
            $this->mailEngine->processDeferredMails();
        }

        if($this->logEngine) {
            $this->logEngine->writeCloseLogs($elapsedTime, $statusCode);
            $this->logEngine->dumpLogsIntoFile();
        }
        
        if (random_int(0, 100) < $this->config->getCacheFileGCProbability()) { 
            CacheService::runGarbageCollector($this->config->getCacheFileGCPower());
        }
    }

    #/ METHODS
    #----------------------------------------------------------------------  
}