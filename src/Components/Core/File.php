<?php

namespace FastRaven\Components\Core;

use FastRaven\Types\DataType;
use FastRaven\Bee;

final class File {
    #----------------------------------------------------------------------
    #\ VARIABLES

    private string $path = "";
        public function getPath(): string { return $this->path; }
    private string $name = "";
        public function getName(): string { return $this->name; }
    private string $extension = "";
        public function getExtension(): string { return $this->extension; }
    private DataType $type = DataType::TEXT;
        public function getType(): DataType { return $this->type; }
    

    #/ VARIABLES
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ INIT

    /**
     * Creates a new File instance with the specified path.
     *
     * @param string $name The name to assign to the file (NOT THE PATH)
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
        $this->extension = pathinfo($path, PATHINFO_EXTENSION);
        $this->type = Bee::getFileMimeType($path, true);
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