<?php

namespace FastRaven\Workers;

use FastRaven\Internal\Slave\StorageSlave;

class StorageWorker {
    #----------------------------------------------------------------------
    #\ VARIABLES

    private static bool $busy = false;
    private static StorageSlave $slave;

    #/ VARIABLES
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ INIT

    public static function __getToWork(StorageSlave &$slave): void {
        if(!self::$busy) {
            self::$busy = true;
            self::$slave = $slave;
        }
    }

    #/ INIT
    #----------------------------------------------------------------------
    
    #----------------------------------------------------------------------
    #\ PRIVATE FUNCTIONS


    #/ PRIVATE FUNCTIONS
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ METHODS
    
    public static function getCacheFilePath(string $key): ?string {
        if(self::$busy) { 
            return self::$slave->getCacheFilePath($key); 
        }

        return null;
    }

    public static function getUploadFilePath(string $file): ?string {
        if(self::$busy) { 
            return self::$slave->getUploadFilePath($file);
        }

        return null;
    }

    public static function cacheExists(string $key): bool {
        if(self::$busy) { 
            return self::$slave->cacheExists($key); 
        }

        return false;
    }

    public static function fileExists(string $path): bool {
        if(self::$busy) { 
            return self::$slave->fileExists($path); 
        }

        return false;
    }

    /**
     * Retrieves a cache item.
     * 
     * @param string $key The key of the cache item to retrieve.
     * 
     * @return ?array The cache item if it exists, null otherwise.
     */
    public static function getCache(string $key): ?array {
        if(self::$busy) { 
            return self::$slave->getCache($key); 
        }

        return null;
    }

    /**
     * Retrieves a cache value.
     * 
     * @param string $key The key of the cache value to retrieve.
     * 
     * @return mixed The cache value if it exists, null otherwise.
     */
    public static function getCacheValue(string $key): mixed {
        if(self::$busy) { 
            return self::$slave->getCache($key)["value"] ?? null; 
        }

        return null;
    }

    /**
     * Sets a cache item.
     * 
     * @param string $key The key of the cache item to set.
     * @param mixed $value The value of the cache item to set.
     * @param int $expires The number of seconds until the cache item expires.
     * 
     * @return bool True if the cache item was successfully set, false otherwise.
     */
    public static function setCache(string $key, mixed $value, int $expires): bool {
        if(self::$busy) {
            return self::$slave->setCache($key, $value, $expires);
        }

        return false;
    }

    /**
     * Updates a cache item.
     * 
     * @param string $key The key of the cache item to update.
     * @param mixed $value The value of the cache item to update.
     * 
     * @return bool True if the cache item was successfully updated, false otherwise.
     */
    public static function updateCache(string $key, mixed $value): bool {
        if(self::$busy) {
            if(self::$slave->cacheExists($key)) {
                return self::$slave->setCache($key, $value, 0);
            }
        }

        return false;
    }

    /**
     * Increments a cache item.
     * 
     * @param string $key The key of the cache item to increment.
     * @param int $num The number to increment the cache item by. Default is 1.
     * 
     * @return bool True if the cache item was successfully incremented, false otherwise.
     */
    public static function incrementCache(string $key, int $num = 1): bool {
        if(self::$busy) {
            return self::$slave->incrementCache($key, $num);
        }

        return false;
    }

    /**
     * Deletes a cache item.
     * 
     * @param string $key The key of the cache item to delete.
     * 
     * @return bool True if the cache item was successfully deleted, false otherwise.
     */
    public static function deleteCache(string $key): bool {
        if(self::$busy) {
            return self::$slave->deleteCache($key);
        }

        return false;
    }

    /**
     * Clears the cache.
     * 
     * @return bool True if the cache was successfully cleared, false otherwise.
     */
    public static function clearCache(): bool {
        if(self::$busy) {
            return self::$slave->clearCache();
        }

        return false;
    }

    /**
     * Uploads a file to the storage/uploads directory.
     * 
     * @param string $tmpFile The temporary file path from $_FILES["name"]["tmp_name"].
     * @param string $destPath The destination path relative to storage/uploads.
     * 
     * @return bool True if the file was successfully uploaded, false otherwise.
     */
    public static function uploadFile(string $tmpFile, string $destPath): bool {
        if(self::$busy) {
            return self::$slave->uploadFile($tmpFile, $destPath);
        }

        return false;
    }

    /**
     * Reads a file from the storage/uploads directory.
     * 
     * @param string $path The file path relative to storage/uploads.
     * 
     * @return ?string File contents, or null if file doesn't exist.
     */
    public static function readFileContents(string $path): ?string {
        if(self::$busy) {
            return self::$slave->readFileContents($path);
        }

        return null;
    }

    /**
     * Deletes a file from the storage/uploads directory.
     * 
     * @param string $path The file path relative to storage/uploads.
     * 
     * @return bool True if file was deleted, false otherwise.
     */
    public static function deleteFile(string $path): bool {
        if(self::$busy) {
            return self::$slave->deleteFile($path);
        }

        return false;
    }

    #/ METHODS
    #----------------------------------------------------------------------
}