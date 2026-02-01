<?php

namespace FastRaven;

use FastRaven\Exceptions\BadProjectSkeletonException;
use FastRaven\Exceptions\NotFoundException;
use FastRaven\Exceptions\NotAuthorizedException;
use FastRaven\Exceptions\RateLimitExceededException;
use FastRaven\Exceptions\SmartException;

use FastRaven\Internals\Kernel;

use FastRaven\Components\Core\Config;
use FastRaven\Components\Core\Template;
use FastRaven\Components\Routing\Router;
use FastRaven\Components\Http\Response;
use FastRaven\Components\Routing\Middleware;

use FastRaven\Services\LogService;
use FastRaven\Services\HeaderService;

use FastRaven\Bee;

use FastRaven\Types\ProjectFolderType;

final class Server {
    #----------------------------------------------------------------------
    #\ VARIABLES

    private Kernel $kernel;
    private bool $ready = false;
    private float $startRequestTime;

    #/ VARIABLES
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ INIT

    public static function getConfiguration(): Config {
        return require_once Bee::buildProjectPath(ProjectFolderType::CONFIG, "config.php");
    }

    public static function getTemplate(): Template {
        return require_once Bee::buildProjectPath(ProjectFolderType::CONFIG, "template.php");
    }

    public static function getMiddleware(): Middleware {
        return require_once Bee::buildProjectPath(ProjectFolderType::CONFIG, "middleware.php");
    }

    public static function getViewRouter(): Router {
        return require_once Bee::buildProjectPath(ProjectFolderType::CONFIG_ROUTER, "views.php");
    }

    public static function getApiRouter(): Router {
        return require_once Bee::buildProjectPath(ProjectFolderType::CONFIG_ROUTER, "api.php");
    }

    public static function getCdnRouter(): Router {
        return require_once Bee::buildProjectPath(ProjectFolderType::CONFIG_ROUTER, "cdn.php");
    }

    /**
     * Initializes the server.
     *
     * @param string $sitePath The local path of the site. Use __DIR__ unless you know what you are doing.
     * @param float|null $startRequestTime The time when the request started. Use microtime(true).
     * 
     * @return Server
     */
    public static function initialize(string $sitePath, ?float $startRequestTime = null): Server {
        $startRequestTime ??= microtime(true);
        define("SITE_PATH", DIRECTORY_SEPARATOR . Bee::normalizePath($sitePath) . DIRECTORY_SEPARATOR);

        if(Bee::isDev()) {
            foreach(ProjectFolderType::cases() as $folder)
                if(!is_dir(Bee::buildProjectPath($folder))) throw new BadProjectSkeletonException($folder);
        }

        require_once Bee::buildProjectPath(ProjectFolderType::CONFIG_ENV, "env.php");

        return new Server($startRequestTime);
    }

    private function __construct(float $startRequestTime) {
        $this->startRequestTime = $startRequestTime;
    }

    /**
     * Configures the server.
     *
     * @param Config $config The configuration to use.
     * @param Template $template The default template for all views.
     * @param Middleware $middleware The middleware to use.
     * @param Router $viewRouter The View Router to use.
     * @param Router $apiRouter The API Router to use.
     * @param Router $cdnRouter The CDN Router to use.
     */
    public function configure(Config $config, Template $template, Middleware $middleware, Router $viewRouter, Router $apiRouter, Router $cdnRouter): Server {
        $this->kernel = new Kernel($config, $template, $middleware, $viewRouter, $apiRouter, $cdnRouter);
        $this->ready = true;
        return $this;
    }

    #/ INIT
    #----------------------------------------------------------------------
    
    #----------------------------------------------------------------------
    #\ PRIVATE FUNCTIONS

    private function handleException(SmartException $e): Response|Template {
        $response = Response::new(false, $e->getStatusCode(), $e->getPublicMessage());
        LogService::error($e->getExceptionName() . ": " . $e->getMessage());

        if($e instanceof RateLimitExceededException || is_subclass_of($e, RateLimitExceededException::class)) {
            HeaderService::addHeader("Retry-After", $e->getTimeLeft());
        }

        if($this->kernel->isViewRequest()) {
            $status = $e->getStatusCode();
            $config = $this->kernel->getConfig();
            if($e instanceof NotFoundException || is_subclass_of($e, NotFoundException::class)) {
                if($config->getDefaultNotFoundPathRedirect() !== null) {
                    HeaderService::addHeader("Location", $config->getDefaultNotFoundPathRedirect());
                    $status = 302;
                }
            } else if($e instanceof NotAuthorizedException || is_subclass_of($e, NotAuthorizedException::class)) {
                if($e->isDomainLevel() && $config->getDefaultUnauthorizedSubdomainRedirect() !== null) {
                    HeaderService::addHeader("Location", "https://".Bee::getBuiltDomain($config->getDefaultUnauthorizedSubdomainRedirect()));
                    $status = 302;
                } else if($config->getDefaultUnauthorizedPathRedirect() !== null) {
                    HeaderService::addHeader("Location", $config->getDefaultUnauthorizedPathRedirect());
                    $status = 302;
                }
            }

            $template = $this->kernel->getTemplate();
            $response = $template
                ->setFile($template->getErrorFile($status))
                ->setTitle($template->getTitle() . " - Error")
                ->addData("errorCode", $status)
                ->addData("errorMessage", $e->getPublicMessage());
        }

        return $response;
    }

    #/ PRIVATE FUNCTIONS
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ METHODS

    /**
     * Starts the server and handles incoming requests.
     *
     * If the server has not been configured, it will return a 500 status code.
     *
     * Handles NotFoundException, BadImplementationException, EndpointFileNotFoundException, NotAuthorizedException,
     * RateLimitExceededException, BadMiddlewareException, MiddlewareDeniedException, UploadedFileNotFoundException
     */
    public function run(): void {
        if ($this->ready) {
            $response = null;
            try {
                $this->kernel->open($this->startRequestTime); // Services/Engines initialization
                $response = $this->kernel->process(); // Request processing
            } catch(SmartException $e) {
                $response = $this->handleException($e); // Exception handling
            }
            $this->kernel->close($response); // Response processing and sending
        } else {
            http_response_code(500);
        }
        exit(0);
    }

    #/ METHODS
    #----------------------------------------------------------------------
}