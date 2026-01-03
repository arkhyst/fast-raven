<?php

namespace FastRaven;

use FastRaven\Exceptions\BadFilterException;
use FastRaven\Exceptions\BadProjectSkeletonException;
use FastRaven\Exceptions\BadImplementationException;
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
use FastRaven\Components\Http\Request;
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
    private array $starters = [];
    private array $finishers = [];

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
     * Adds a starter filter to be executed before the request processing.
     *
     * @param callable $starter func(Request $request): bool - return false to deny request processing.
     * Supports FilterDeniedException to handle custom responses.
     */
    public function addStarter(callable $starter): Server {
        $this->starters[] = $starter;
        return $this;
    }

    /**
     * Adds a finisher filter to be executed after the request processing and before the response is sent.
     *
     * @param callable $finisher func(Request $request, Response $response): bool - return false to deny response return.
     * Supports FilterDeniedException to handle custom responses.
     */
    public function addFinisher(callable $finisher): Server {
        $this->finishers[] = $finisher;
        return $this;
    }

    #/ INIT
    #----------------------------------------------------------------------
    
    #----------------------------------------------------------------------
    #\ PRIVATE FUNCTIONS

    private function handleException(SmartException $e): Response {
        $statusCode = $this->kernel->isViewRequest() ? 301 : $e->getStatusCode();
        $response = Response::new(false, $statusCode, $e->getPublicMessage());
        LogWorker::error("SmartException: " . $e->getMessage());

        if($e instanceof RateLimitExceededException || is_subclass_of($e, RateLimitExceededException::class)) {
            HeaderWorker::addHeader("Retry-After", $e->getTimeLeft());
        }

        if($this->kernel->isViewRequest()) {
            if($e instanceof NotFoundException || is_subclass_of($e, NotFoundException::class) ||
            $e instanceof AlreadyAuthorizedException || is_subclass_of($e, AlreadyAuthorizedException::class)) {
                HeaderWorker::addHeader("Location", $this->kernel->getConfig()->getDefaultNotFoundPathRedirect());
            } else if($e instanceof NotAuthorizedException || is_subclass_of($e, NotAuthorizedException::class)) {
                if($e->isDomainLevel()) {
                    HeaderWorker::addHeader("Location", "https://".Bee::getBuiltDomain($this->kernel->getConfig()->getDefaultUnauthorizedSubdomainRedirect()));
                } else {
                    HeaderWorker::addHeader("Location", $this->kernel->getConfig()->getDefaultUnauthorizedPathRedirect());
                }
            }
        }

        return $response;
    }

    /**
     * Validates the filter callable signature.
     *
     * @throws BadImplementationException If the callable has invalid parameters.
     */
    private function validateFilter(callable $filter, array $params = []): void {
        $reflection = new \ReflectionFunction($filter instanceof \Closure ? $filter : $filter(...));
        $callbackParams = array_map(fn($p) => $p->getType(), $reflection->getParameters());

        for($i = 0; $i < count($callbackParams); $i++) {
            $type = $callbackParams[$i];
            $expected = $params[$i] ?? "none";
            
            if ($type !== null && $type->getName() !== $expected) throw new BadFilterException("{$i}: Should be {$expected}");
        }
    }

    /**
     * Processes the starters.
     *
     * @throws FilterDeniedException If a starter filter denies access to a resource.
     */
    private function processStarters(): void {
        foreach($this->starters as $starter) {
            $this->validateFilter($starter, [Request::class]);
            if ($starter($this->kernel->getRequest()) === false) throw new FilterDeniedException();
        }
    }

    /**
     * Processes the finishers.
     *
     * @throws FilterDeniedException If a finisher filter denies access to a resource.
     */
    private function processFinishers(Response $response): void {
        foreach($this->finishers as $finisher) {
            $this->validateFilter($finisher, [Request::class, Response::class]);
            if ($finisher($this->kernel->getRequest(), $response) === false) throw new FilterDeniedException();
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
     * AlreadyAuthorizedException, RateLimitExceededException, FilterDeniedException, UploadedFileNotFoundException
     */
    public function run(): void {
        if ($this->ready) {
            $response = null;
            try {
                $this->kernel->open(); // Workers/Slaves initialization
                $this->processStarters(); // Starter callbacks execution
                $response = $this->kernel->process(); // Request processing
                $this->processFinishers($response); // Finisher callbacks execution
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