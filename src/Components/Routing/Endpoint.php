<?php

namespace FastRaven\Components\Routing;

use FastRaven\Workers\Bee;

use FastRaven\Components\Core\Template;

use FastRaven\Types\MiddlewareType;

final class Endpoint {
    #----------------------------------------------------------------------
    #\ VARIABLES


    private MiddlewareType $type;
        public function getType(): MiddlewareType { return $this->type; }
    private bool $restricted;
        public function getRestricted(): bool { return $this->restricted; }
    private bool $unauthorizedExclusive = false;
        public function getUnauthorizedExclusive(): bool { return $this->unauthorizedExclusive; }
    private int $limitPerMinute = 0;
        public function getLimitPerMinute(): int { return $this->limitPerMinute; }
    private string $path;
        public function getPath(): string { return $this->path; }
    private string $complexPath;
        public function getComplexPath(): string { return $this->complexPath; }
    private string $file;
        public function getFile(): string { return $this->file; }
    private ?Template $template = null;
        public function getTemplate(): ?Template { return $this->template; }
    
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
     * @param Template|null $template The template to use for the endpoint, or null to use the default template.
     * @param bool $unauthorizedExclusive Whether the endpoint should be exclusive to unauthorized users.
     * @param int $limitPerMinute The limit of requests per minute specific for this endpoint. Must be lower than global rate limit configuration.
     *
     * @return Endpoint The created Endpoint instance.
     */
    public static function view(bool $restricted, string $path, string $fileName, ?Template $template = null, bool $unauthorizedExclusive = false, int $limitPerMinute = -1): Endpoint {
        return new Endpoint(MiddlewareType::VIEW, $restricted, "GET", $path, $fileName, $unauthorizedExclusive, $template, $limitPerMinute);
    }

    /**
     * Creates a new Endpoint instance for an API endpoint.
     *
     * @param bool $restricted Whether the endpoint is restricted to authorized users.
     * @param string $method The HTTP method to use for the endpoint.
     * @param string $path The path of the endpoint, relative to /api/.
     * @param string $fileName The filename of the endpoint, relative to the /src/api/ directory.
     * @param bool $unauthorizedExclusive Whether the endpoint should be exclusive to unauthorized users.
     * @param int $limitPerMinute The limit of requests per minute specific for this endpoint. Must be lower than global rate limit configuration.
     *
     * @return Endpoint The created Endpoint instance.
     */
    public static function api(bool $restricted, string $method, string $path, string $fileName, bool $unauthorizedExclusive = false, int $limitPerMinute = -1): Endpoint {
        return new Endpoint(MiddlewareType::API, $restricted, $method, "/api/".$path, $fileName, $unauthorizedExclusive, null, $limitPerMinute);
    }

    /**
     * Creates a new Endpoint instance for a CDN endpoint.
     *
     * @param bool $restricted Whether the endpoint is restricted to authorized users.
     * @param string $method The HTTP method to use for the endpoint.
     * @param string $path The path of the endpoint, relative to /cdn/.
     * @param string $fileName The filename of the endpoint, relative to the /src/cdn/ directory.
     * @param int $limitPerMinute The limit of requests per minute specific for this endpoint. Must be lower than global rate limit configuration.
     *
     * @return Endpoint The created Endpoint instance.
     */
    public static function cdn(bool $restricted, string $method, string $path, string $fileName, int $limitPerMinute = -1): Endpoint {
        return new Endpoint(MiddlewareType::CDN, $restricted, $method, "/cdn/".$path, $fileName, false, null, $limitPerMinute);
    }

    /**
     * Creates a new special Endpoint instance for a router endpoint.
     *
     * @param MiddlewareType $type The type of the router. (prefixes $path with /api/ or /cdn/ if needed)
     * @param bool $restricted Whether the router is restricted to authorized users.
     * @param string $path The path of the router, relative to the website root.
     * @param string $routerFilePath The filename of the router file, relative to the /config/router/ directory.
     * @param int $limitPerMinute The limit of requests per minute specific for this router. Must be lower than global rate limit configuration.
     *
     * @return Endpoint The created Endpoint instance.
     */
    public static function router(MiddlewareType $type, bool $restricted, string $path, string $routerFilePath, int $limitPerMinute = -1): Endpoint {
        if($type == MiddlewareType::API) $path = "/api/".$path;
        else if($type == MiddlewareType::CDN) $path = "/cdn/".$path;
        return new Endpoint(MiddlewareType::ROUTER, $restricted, "GET", $path, $routerFilePath, false, null, $limitPerMinute);
    }

    private function __construct(MiddlewareType $type, bool $restricted, string $method, string $path, string $fileName, bool $unauthorizedExclusive = false, ?Template $template = null, int $limitPerMinute = -1) {
        $this->type = $type;
        $this->restricted = $restricted;
        $this->path = "/".Bee::normalizePath($path);
        if($this->path !== "/") $this->path .= "/";
        $this->complexPath = $this->path."#".$method;
        $this->file = Bee::normalizePath($fileName);
        $this->template = $template;
        $this->unauthorizedExclusive = $unauthorizedExclusive;
        $this->limitPerMinute = $limitPerMinute;
    }

    #/ INIT
    #----------------------------------------------------------------------
    
    #----------------------------------------------------------------------
    #\ PRIVATE FUNCTIONS



    #/ PRIVATE FUNCTIONS
    #----------------------------------------------------------------------

}
