<?php

namespace FastRaven\Workers;

use FastRaven\Types\DataType;

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
    public static function isDev() : bool {
        return Bee::env("STATE") === "dev";
    }
    
   /**
     * Normalize a path by removing redundant slashes and trimming it
     * 
     * @param string $path the path to normalize
     * @return string the normalized path (e.g., "path/to/endpoint")
     */
    public static function normalizePath(string $path): string {
        $path = str_replace("\0", "", $path);
        $path = preg_replace("#[\\\\/]+#", "/", $path);
        $segments = array_filter(
            explode("/", $path),
            fn($s) => $s !== "" && $s !== "." && $s !== ".."
        );
        return implode("/", $segments);
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
            $domain = array_slice($parts, -2); # UK... Why, common wealth, WHY???
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
    
    /**
     * Returns the MIME type of a file.
     * 
     * @param string $file the path to the file
     * @param bool $returnType whether to return the MIME type as a DataType enum value
     * 
     * @return string|DataType the MIME type of the file. If the file does not exist or cannot be read, returns "application/octet-stream".
     */
    public static function getFileMimeType(string $file, bool $returnType = false): string|DataType {
        if (!is_file($file)) {
            return "application/octet-stream";
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file);

        if($mimeType === false) return $returnType ? DataType::BINARY : "application/octet-stream";

        if ($returnType) {
            try { return DataType::from($mimeType); }
            catch (\ValueError $e) { return DataType::BINARY; }
        }
        
        return $mimeType;
    }

    #/ METHODS
    #----------------------------------------------------------------------
}