<?php

namespace FastRaven;

use FastRaven\Exceptions\BadProjectSkeletonException;
use FastRaven\Exceptions\NotFoundException;
use FastRaven\Exceptions\NotAuthorizedException;
use FastRaven\Exceptions\AlreadyAuthorizedException;
use FastRaven\Exceptions\RateLimitExceededException;
use FastRaven\Exceptions\FilterDeniedException;
use FastRaven\Exceptions\SmartException;

use FastRaven\Internal\Core\Kernel;

use FastRaven\Components\Core\Config;
use FastRaven\Components\Core\Template;
use FastRaven\Components\Routing\Router;
use FastRaven\Components\Http\Response;

use FastRaven\Workers\LogWorker;
use FastRaven\Workers\HeaderWorker;

use FastRaven\Workers\Bee;

use FastRaven\Types\ProjectFolderType;

use Dotenv\Dotenv;

final class Server {
    #----------------------------------------------------------------------
    #\ VARIABLES

    private Kernel $kernel;
    private bool $ready = false;
    private array $filters = [];

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
     * 
     * @return Server
     */
    public static function initialize(string $sitePath): Server {
        define("SITE_PATH", DIRECTORY_SEPARATOR . Bee::normalizePath($sitePath) . DIRECTORY_SEPARATOR);

        foreach(ProjectFolderType::cases() as $folder)
            if(!is_dir(Bee::buildProjectPath($folder))) throw new BadProjectSkeletonException($folder);

        Dotenv::createImmutable(ProjectFolderType::CONFIG_ENV->value, ".env")->safeLoad();
        Dotenv::createImmutable(ProjectFolderType::CONFIG_ENV->value, Bee::isDev() ? ".env.dev" : ".env.prod")->safeLoad();

        return new Server();
    }

    private function __construct() {
        
    }

    /**
     * Configures the server.
     *
     * @param Config $config The configuration to use.
     * @param Template $template The default template for all views.
     * @param Router $viewRouter The View Router to use.
     * @param Router $apiRouter The API Router to use.
     * @param Router $cdnRouter The CDN Router to use.
     */
    public function configure(Config $config, Template $template, Router $viewRouter, Router $apiRouter, Router $cdnRouter): Server {
        $this->kernel = new Kernel($config, $template, $viewRouter, $apiRouter, $cdnRouter);
        $this->ready = true;
        return $this;
    }

    /**
     * Adds a filter callback to be executed before the request is processed.
     *
     * @param callable $filter func(Request $request): bool - return true to continue processing, false to deny access.
     * Supports FilterDeniedException to handle custom responses.
     */
    public function addFilter(callable $filter): Server {
        $this->filters[] = $filter;
        return $this;
    }

    #/ INIT
    #----------------------------------------------------------------------
    
    #----------------------------------------------------------------------
    #\ PRIVATE FUNCTIONS

    private function handleException(SmartException $e): Response {
        $statusCode = $this->kernel->isApiRequest() ? $e->getStatusCode() : 301;
        $response = Response::new(false, $statusCode, $e->getPublicMessage());
        LogWorker::error("SmartException: " . $e->getMessage());

        if($e instanceof RateLimitExceededException || is_subclass_of($e, RateLimitExceededException::class)) {
            HeaderWorker::addHeader("Retry-After", $e->getTimeLeft());
        }

        if(!$this->kernel->isApiRequest()) {
            if($e instanceof NotFoundException || is_subclass_of($e, NotFoundException::class) ||
            $e instanceof AlreadyAuthorizedException || is_subclass_of($e, AlreadyAuthorizedException::class)) {
                HeaderWorker::addHeader("Location", $this->kernel->getConfig()->getDefaultNotFoundPathRedirect());
            } else if($e instanceof NotAuthorizedException || is_subclass_of($e, NotAuthorizedException::class)) {
                if($e->isDomainLevel())
                    HeaderWorker::addHeader("Location", "https://".Bee::getBuiltDomain($this->kernel->getConfig()->getDefaultUnauthorizedSubdomainRedirect()));
                else
                    HeaderWorker::addHeader("Location", $this->kernel->getConfig()->getDefaultUnauthorizedPathRedirect());
            }
        }

        return $response;
    }

    /**
     * Processes the filters.
     *
     * @throws FilterDeniedException If a filter denies access to a resource.
     */
    private function processFilters(): void {
        foreach($this->filters as $filter) {
            if ($filter($this->kernel->getRequest()) !== true) throw new FilterDeniedException();
        }
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
     * AlreadyAuthorizedException, RateLimitExceededException, FilterDeniedException.
     */
    public function run(): void {
        if ($this->ready) {
            $response = null;
            try {
                $this->kernel->open();
                $this->processFilters();
                $response = $this->kernel->process();
            } catch(SmartException $e) {
                $response = $this->handleException($e);
            }
            $this->kernel->close($response);
        } else {
            http_response_code(500);
        }
        
        exit(0);
    }

    #/ METHODS
    #----------------------------------------------------------------------
}