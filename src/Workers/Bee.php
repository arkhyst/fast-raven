<?php

namespace SmartGoblin\Workers;

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

    /**
     * Gets the value of the environment variable
     * 
     * @param string $key the key of the environment variable
     * @return string the value of the environment variable or empty string if not found
     */
    public static function env(string $key): string {
        return $_ENV[$key] ?? "";
    }
    /**
     * Checks if the application is running in a development environment
     * 
     * @return bool true if the application is running in a development environment, false otherwise
     */
    public static function isDev() {
        return Bee::env("STATE") === "dev";
    }
    
    /**
     * Normalize a path by removing redundant slashes and trimming it
     * 
     * @param string $path the path to normalize
     * @return string the normalized path
     */
    public static function normalizePath(string $path): string {
        $newPath = ltrim($path, "/\\");
        $newPath = preg_replace("#[\\/]+#", "/", $newPath);
        $newPath = rtrim($newPath, "/");

        return $newPath;
    }

    #/ METHODS
    #----------------------------------------------------------------------
}