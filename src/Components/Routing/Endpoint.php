<?php

namespace FastRaven\Components\Routing;

use FastRaven\Bee;

use FastRaven\Types\EndpointType;

final class Endpoint {
    #----------------------------------------------------------------------
    #\ VARIABLES


    private EndpointType $type;
        public function getType(): EndpointType { return $this->type; }
    private bool $restricted;
        public function getRestricted(): bool { return $this->restricted; }
    private string $path;
        public function getPath(): string { return $this->path; }
    private string $complexPath;
        public function getComplexPath(): string { return $this->complexPath; }
    private string $file;
        public function getFile(): string { return $this->file; }
    private string $middlewareId = "";
        public function getMiddlewareId(): string { return $this->middlewareId; }
    
    #/ VARIABLES
    #----------------------------------------------------------------------
    
    #----------------------------------------------------------------------
    #\ INIT

    /**
     * Creates a new Endpoint instance for a view endpoint.
     *
     * @param bool $restricted Whether the endpoint is restricted to authorized users.
     * @param string $path The path of the endpoint, relative to the website root.
     * @param string $fileName The filename of the endpoint, relative to the /src/views/ directory.
     * @param string $middlewareId The ID of the middleware to use for the endpoint.
     *
     * @return Endpoint The created Endpoint instance.
     */
    public static function view(bool $restricted, string $path, string $fileName, string $middlewareId = ""): Endpoint {
        return new Endpoint(EndpointType::VIEW, $restricted, "GET", $path, $fileName, $middlewareId);
    }

    /**
     * Creates a new Endpoint instance for an API endpoint.
     *
     * @param bool $restricted Whether the endpoint is restricted to authorized users.
     * @param string $method The HTTP method to use for the endpoint.
     * @param string $path The path of the endpoint, relative to /api/.
     * @param string $fileName The filename of the endpoint, relative to the /src/api/ directory.
     * @param string $middlewareId The ID of the middleware to use for the endpoint.
     *
     * @return Endpoint The created Endpoint instance.
     */
    public static function api(bool $restricted, string $method, string $path, string $fileName, string $middlewareId = ""): Endpoint {
        return new Endpoint(EndpointType::API, $restricted, $method, "/api/".$path, $fileName, $middlewareId);
    }

    /**
     * Creates a new Endpoint instance for a CDN endpoint.
     *
     * @param bool $restricted Whether the endpoint is restricted to authorized users.
     * @param string $method The HTTP method to use for the endpoint.
     * @param string $path The path of the endpoint, relative to /cdn/.
     * @param string $fileName The filename of the endpoint, relative to the /src/cdn/ directory.
     * @param string $middlewareId The ID of the middleware to use for the endpoint.
     *
     * @return Endpoint The created Endpoint instance.
     */
    public static function cdn(bool $restricted, string $method, string $path, string $fileName, string $middlewareId = ""): Endpoint {
        return new Endpoint(EndpointType::CDN, $restricted, $method, "/cdn/".$path, $fileName, $middlewareId);
    }

    /**
     * Creates a new special Endpoint instance for a router endpoint.
     *
     * @param EndpointType $type The type of the router. (prefixes $path with /api/ or /cdn/ if needed)
     * @param bool $restricted Whether the router is restricted to authorized users.
     * @param string $path The path of the router, relative to the website root.
     * @param string $routerFilePath The filename of the router file, relative to the /config/router/ directory.
     * @param string $middlewareId The ID of the middleware to use for the endpoint.
     *
     * @return Endpoint The created Endpoint instance.
     */
    public static function router(EndpointType $type, bool $restricted, string $path, string $routerFilePath, string $middlewareId = ""): Endpoint {
        if($type == EndpointType::API) $path = "/api/".$path;
        else if($type == EndpointType::CDN) $path = "/cdn/".$path;
        return new Endpoint(EndpointType::ROUTER, $restricted, "GET", $path, $routerFilePath, $middlewareId);
    }

    private function __construct(EndpointType $type, bool $restricted, string $method, string $path, string $fileName, string $middlewareId = "") {
        $this->type = $type;
        $this->restricted = $restricted;
        $this->path = "/".Bee::normalizePath($path);
        if($this->path !== "/") $this->path .= "/";
        $this->complexPath = $this->path."#".$method;
        $this->file = Bee::normalizePath($fileName);
        $this->middlewareId = $middlewareId;
    }

    #/ INIT
    #----------------------------------------------------------------------
    
    #----------------------------------------------------------------------
    #\ PRIVATE FUNCTIONS



    #/ PRIVATE FUNCTIONS
    #----------------------------------------------------------------------

}
