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
    public static function env(string $key, string $default = ""): string {
        return $_ENV[$key] ?? $default;
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
     * @return string the normalized path (e.g., "path/to/endpoint")
     */
    public static function normalizePath(string $path): string
    {
        $newPath = str_replace("\0", "", $path);
        $newPath = ltrim($newPath, "/\\");
        $newPath = str_replace("\\", "/", $newPath);
        $newPath = preg_replace("#[\\\\/]+#", "/", $newPath);
        $newPath = rtrim($newPath, "/\\");
        $segments = explode("/", $newPath);
        $normalized = [];

        foreach ($segments as $segment) {
            if ($segment === "" || $segment === "." || $segment === "..") continue;
            $normalized[] = $segment;
        }

        return implode("/", $normalized);
    }

    #/ METHODS
    #----------------------------------------------------------------------
}