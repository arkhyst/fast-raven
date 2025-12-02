<?php

namespace SmartGoblin;

use SmartGoblin\Exceptions\BadImplementationException;
use SmartGoblin\Exceptions\EndpointFileDoesNotExist;

use SmartGoblin\Exceptions\NotAuthorizedException;
use SmartGoblin\Internal\Core\Kernel;

use SmartGoblin\Components\Core\Config;
use SmartGoblin\Components\Core\Template;
use SmartGoblin\Components\Routing\Router;
use SmartGoblin\Components\Http\Response;

use SmartGoblin\Workers\LogWorker;
use SmartGoblin\Workers\HeaderWorker;

use SmartGoblin\Workers\Bee;
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

    // I don't like this...
    private function getUnauthorizedResponse(bool $domainLevel, string $msg): Response {
        $response = null;
        $redirect = $domainLevel ? $this->kernel->getConfig()->getDefaultUnauthorizedSubdomainRedirect() : $this->kernel->getConfig()->getDefaultUnauthorizedPathRedirect();
        if($this->kernel->isApiRequest()) {
            $response = Response::new(false, 401, $msg);
        } else {
            $response = Response::new(false, 301, $msg);
            HeaderWorker::addHeader("Location", $redirect);
        }

        return $response;
    }

    // Or this ._.
    private function getNotFoundResponse(): Response {
        $response = null;
        if($this->kernel->isApiRequest()) {
            $response = Response::new(false, 404, "Request not found");
        } else {
            $response = Response::new(false, 301);
            HeaderWorker::addHeader("Location", $this->kernel->getConfig()->getDefaultNotFoundPathRedirect());
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
     * Handles BadImplementationException, EndpointFileDoesNotExist, and NotAuthorizedException.
     */
    public function run(): void {
        if ($this->ready) {
            $response = null;
            try {
                $this->kernel->open();
            } catch(NotAuthorizedException $e) {
                $response = $this->getUnauthorizedResponse(true, $e->getMessage());
                LogWorker::error("-SG- " . $e->getMessage());
            }
            

            try {
                $response = $this->kernel->process();
                if($response === null) { 
                    $response = $this->getNotFoundResponse();
                    LogWorker::warning("-SG- Request did not find matching route");
                } else {
                    LogWorker::log("-SG- Request processed successfully");
                }
                
            } catch(BadImplementationException | EndpointFileDoesNotExist $e) {
                $response = Response::new(false, 500);
                LogWorker::error("-SG- " . $e->getMessage());
            } catch(NotAuthorizedException $e) {
                $response = $this->getUnauthorizedResponse(false, $e->getMessage());
                LogWorker::error("-SG- " . $e->getMessage());
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