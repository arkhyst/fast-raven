<?php

namespace FastRaven\Components\Core;

use FastRaven\Workers\Bee;

final class File {
    #----------------------------------------------------------------------
    #\ VARIABLES

    private string $path = "";
        public function getPath(): string { return $this->path; }
    private string $name = "";
        public function getName(): string { return $this->name; }
    private string $extension = "";
        public function getExtension(): string { return $this->extension; }
    

    #/ VARIABLES
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ INIT

    /**
     * Creates a new File instance with the specified path.
     *
     * @param string $path The path to the file.
     *
     * @return File A new File instance configured with the provided parameters.
     */
    public static function new(string $name, string $path): File {
        return new File($name, $path);
    }

    private function __construct(string $name, string $path) {
        $this->path = $path;
        $this->name = $name;
        $this->extension = pathinfo($name, PATHINFO_EXTENSION);
    }

    #/ INIT
    #----------------------------------------------------------------------
    
    #----------------------------------------------------------------------
    #\ PRIVATE FUNCTIONS



    #/ PRIVATE FUNCTIONS
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ METHODS



    #/ METHODS
    #----------------------------------------------------------------------
}