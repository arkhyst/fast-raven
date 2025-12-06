<?php

namespace FastRaven\Components\Routing;

use FastRaven\Workers\Bee;
use FastRaven\Components\Core\Template;

class Endpoint {
    #----------------------------------------------------------------------
    #\ VARIABLES

    private bool $restricted;
        public function getRestricted(): bool { return $this->restricted; }
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
     * Creates a new Endpoint instance for an API endpoint.
     *
     * @param bool $restricted Whether the endpoint is restricted to authorized users.
     * @param string $method The HTTP method to use for the endpoint.
     * @param string $path The path of the endpoint, relative to /api/.
     * @param string $fileName The filename of the endpoint, relative to the /src/api/ directory.
     *
     * @return Endpoint The created Endpoint instance.
     */
    public static function api(bool $restricted, string $method, string $path, string $fileName): Endpoint {
        return new Endpoint($restricted, $method, "/api/".$path, $fileName);
    }

    /**
     * Creates a new Endpoint instance for a view endpoint.
     *
     * @param bool $restricted Whether the endpoint is restricted to authorized users.
     * @param string $path The path of the endpoint, relative to the website root.
     * @param string $fileName The filename of the endpoint, relative to the /src/views/ directory.
     * @param Template|null $template The template to use for the endpoint, or null to use the default template.
     *
     * @return Endpoint The created Endpoint instance.
     */
    public static function view(bool $restricted, string $path, string $fileName, ?Template $template = null): Endpoint {
        return new Endpoint($restricted, "GET", $path, $fileName, $template);
    }

    private function __construct(bool $restricted, string $method, string $path, string $fileName, ?Template $template = null) {
        $this->restricted = $restricted;
        $this->complexPath = "/".Bee::normalizePath($path)."#".$method;
        $this->file = Bee::normalizePath($fileName);
        $this->template = $template;
    }

    #/ INIT
    #----------------------------------------------------------------------
    
    #----------------------------------------------------------------------
    #\ PRIVATE FUNCTIONS



    #/ PRIVATE FUNCTIONS
    #----------------------------------------------------------------------

}
