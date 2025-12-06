<?php

namespace FastRaven\Workers;

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

    /**
     * Returns the base domain of the site from the SITE_ADDRESS environment variable.
     * If the SITE_ADDRESS environment variable is not set, returns "localhost".
     * If the SITE_ADDRESS environment variable is set to a domain with 3 or more parts (e.g., "sub.example.com"), returns the last 2 parts of the domain (e.g., "example.com").
     *
     * @return string the base domain of the site
     */
    public static function getBaseDomain(): string {
        $host = Bee::env("SITE_ADDRESS", "localhost");
        $parts = explode(".", $host);
        $count = count($parts);

        if ($count >= 3) {
            $domain = array_slice($parts, -2);
            return implode(".", $domain);
        }

        return $host;
    }

    /**
     * Returns the built domain of the site based on the SITE_ADDRESS environment variable and the $subdomain parameter.
     * If the $subdomain parameter is empty, returns the base domain of the site.
     * If the $subdomain parameter is not empty, returns the built domain by concatenating the $subdomain parameter with the base domain of the site.
     *
     * @param string $subdomain the subdomain to use for the built domain
     * 
     * @return string the built domain of the site
     */
    public static function getBuiltDomain(string $subdomain = ""): string {
        $baseDomain = Bee::getBaseDomain();
        if ($subdomain === "") return $baseDomain;
        else return $subdomain . "." . $baseDomain;
    }

    /**
     * Hashes a password using the Argon2ID algorithm with a memory cost of 2^16, a time cost of 4 and 2 threads.
     * 
     * @param string $password the password to hash
     * 
     * @return string the hashed password
     */
    public static function hashPassword(string $password): string {
        return password_hash($password, PASSWORD_ARGON2ID, ['memory_cost' => 1 << 16, 'time_cost' => 4, 'threads' => 2]);
    }

    #/ METHODS
    #----------------------------------------------------------------------
}