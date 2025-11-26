<?php

namespace SmartGoblin\Worker;

class Bee {
    #----------------------------------------------------------------------
    #\ VARIABLES

    

    #/ VARIABLES
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ INIT



    #/ INIT
    #----------------------------------------------------------------------
    
    #----------------------------------------------------------------------
    #\ PRIVATE FUNCTIONS



    #/ PRIVATE FUNCTIONS
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ METHODS

    public static function isDev() { return $_ENV["STATE"] === "dev"; }
    
    public static function normalizePath(string $path, bool $cleanExtension = false): string {
        $newPath = ltrim($path, "/\\");
        $newPath = preg_replace("#[\\/]+#", "/", $newPath);

        if($cleanExtension) {
            $extensions = [".php", ".html", ".phtml", ".txt". ".md", ".log"];
            foreach($extensions as $ext) {
                if(str_ends_with($newPath, $ext)) {
                    $newPath = substr($newPath, 0, -strlen($ext));
                }
            }
        }

        return $newPath;
    }

    #/ METHODS
    #----------------------------------------------------------------------
}