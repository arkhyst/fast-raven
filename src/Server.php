<?php

namespace FastRaven;

use FastRaven\Exceptions\NotFoundException;
use FastRaven\Exceptions\NotAuthorizedException;
use FastRaven\Exceptions\SmartException;
use FastRaven\Internal\Core\Kernel;

use FastRaven\Components\Core\Config;
use FastRaven\Components\Core\Template;
use FastRaven\Components\Routing\Router;
use FastRaven\Components\Http\Response;

use FastRaven\Workers\LogWorker;
use FastRaven\Workers\HeaderWorker;

use FastRaven\Workers\Bee;
use Dotenv\Dotenv;

final class Server {
    #----------------------------------------------------------------------
    #\ VARIABLES

    private Kernel $kernel;
    private bool $ready = false;

    #/ VARIABLES
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ INIT

    /**
     * Preloads the environment configuration.
     *
     * This function is used to load the configuration from the .env files before the server is configured.
     * It is useful for setting up the environment variables before the server is configured.
     *
     * @param string $sitePath The local path of the site. Use __DIR__ unless you know what you are doing.
     */
    public static function preload(string $sitePath): void {
        define("SITE_PATH", $sitePath);

        $envPath = $sitePath . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR . "env" . DIRECTORY_SEPARATOR;

        Dotenv::createImmutable($envPath, ".env")->safeLoad();
        Dotenv::createImmutable($envPath, Bee::isDev() ? ".env.dev" : ".env.prod")->safeLoad();
    }

    public static function getConfiguration(string $sitePath = SITE_PATH): Config {
        return require_once $sitePath . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR . "config.php";
    }

    public static function getTemplate(string $sitePath = SITE_PATH): Template {
        return require_once $sitePath . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR . "template.php";
    }

    public static function getViewRouter(string $sitePath = SITE_PATH): Router {
        return require_once $sitePath . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR . "router" . DIRECTORY_SEPARATOR . "views.php";
    }

    public static function getApiRouter(string $sitePath = SITE_PATH): Router {
        return require_once $sitePath . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR . "router" . DIRECTORY_SEPARATOR . "api.php";
    }

    /**
     * Creates a new instance of the Server class.
     *
     * @return Server
     */
    public static function createInstance(): Server {
        return new Server();
    }

    private function __construct() {
        $this->kernel = new Kernel();
    }

    /**
     * Configures the server.
     *
     * @param Config $config The configuration to use.
     * @param Template $template The default template for all views.
     * @param Router $viewRouter The View Router to use.
     * @param Router $apiRouter The API Router to use.
     */
    public function configure(Config $config, Template $template, Router $viewRouter, Router $apiRouter): void {
        $this->kernel->setConfig($config);
        $this->kernel->setTemplate($template);
        $this->kernel->setViewRouter($viewRouter);
        $this->kernel->setApiRouter($apiRouter);
        $this->ready = true;
    }

    #/ INIT
    #----------------------------------------------------------------------
    
    #----------------------------------------------------------------------
    #\ PRIVATE FUNCTIONS

    private function handleException(SmartException $e): Response {
        $statusCode = $this->kernel->isApiRequest() ? $e->getStatusCode() : 301;
        $response = Response::new(false, $statusCode, $e->getPublicMessage());
        LogWorker::error("-SG- " . $e->getMessage());

        if(!$this->kernel->isApiRequest()) {
            if($e instanceof NotFoundException || is_subclass_of($e, NotFoundException::class)) {
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

    #/ PRIVATE FUNCTIONS
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ METHODS

    /**
     * Starts the server and handles incoming requests.
     *
     * If the server has not been configured, it will return a 500 status code.
     *
     * Handles NotFoundException, BadImplementationException, EndpointFileDoesNotExist, NotAuthorizedException and AlreadyAuthorizedException.
     */
    public function run(): void {
        if ($this->ready) {
            $response = null;
            try {
                $this->kernel->open();
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