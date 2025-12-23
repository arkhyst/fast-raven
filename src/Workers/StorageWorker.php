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
     * @param int $num The number to increment the cache item by.
     * 
     * @return bool True if the cache item was successfully incremented, false otherwise.
     */
    public static function incrementCache(string $key, int $num): bool {
        if(self::$busy) {
            $item = self::$slave->getCache($key);
            if($item) {
                if(gettype($item["value"]) === "integer") return self::$slave->setCache($key, $item["value"] + $num, 0);
            }
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

    #/ METHODS
    #----------------------------------------------------------------------
}