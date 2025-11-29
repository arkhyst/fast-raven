<?php

namespace SmartGoblin\Components\Routing;

use SmartGoblin\Worker\Bee;
use SmartGoblin\Components\Core\Template;

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

    public static function api(bool $restricted, string $method, string $path, string $fileName): Endpoint {
        return new Endpoint($restricted, $method, "/api/".$path, $fileName);
    }

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
