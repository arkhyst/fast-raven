<?php

namespace FastRaven\Workers;

use FastRaven\Internal\Slave\CacheSlave;
use FastRaven\Types\CacheType;

class CacheWorker {
    #----------------------------------------------------------------------
    #\ VARIABLES

    private static bool $busy = false;
    private static CacheSlave $slave;

    #/ VARIABLES
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ INIT

    public static function __getToWork(CacheSlave &$slave): void {
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
     * Returns the current cache backend type.
     * 
     * @return CacheType The cache type (APCU, SHARED, or FILE).
     */
    public static function getUsedType(): CacheType {
        if(self::$busy) {
            return self::$slave->getType();
        }

        return CacheType::FILE;
    }

    /**
     * Checks if a cache item exists and is not expired.
     * 
     * @param string $key The cache key.
     * @return bool True if the item exists.
     */
    public static function exists(string $key): bool {
        if(self::$busy) {
            return match(self::$slave->getType()) {
                CacheType::APCU => self::$slave->apcuExists($key),
                CacheType::SHARED => self::$slave->shmopExists($key),
                CacheType::FILE => self::$slave->fileExists($key),
            };
        }

        return false;
    }

    /**
     * Retrieves a cache value.
     * 
     * @param string $key The cache key.
     * 
     * @return mixed The cached value, or null if not found/expired.
     */
    public static function read(string $key): mixed {
        if(self::$busy) {
            return match(self::$slave->getType()) {
                CacheType::APCU => self::$slave->apcuRead($key)["value"] ?? null,
                CacheType::SHARED => self::$slave->shmopRead($key)["value"] ?? null,
                CacheType::FILE => self::$slave->fileRead($key)["value"] ?? null,
            };
        }

        return null;
    }

    /**
     * Retrieves a cache item with metadata (value and expiry).
     * 
     * @param string $key The cache key.
     * @return ?array ["value" => mixed, "expires" => int] or null if not found.
     */
    public static function readWithMeta(string $key): ?array {
        if(self::$busy) {
            return match(self::$slave->getType()) {
                CacheType::APCU => self::$slave->apcuRead($key),
                CacheType::SHARED => self::$slave->shmopRead($key),
                CacheType::FILE => self::$slave->fileRead($key),
            };
        }

        return null;
    }

    /**
     * Stores a value in cache.
     * 
     * @param string $key The cache key.
     * @param mixed $value The value to cache.
     * @param int $expires Time-to-live in seconds.
     * @return bool True on success.
     */
    public static function write(string $key, mixed $value, int $expires): bool {
        if(self::$busy) {
            return match(self::$slave->getType()) {
                CacheType::APCU => self::$slave->apcuWrite($key, $value, $expires),
                CacheType::SHARED => self::$slave->shmopWrite($key, $value, $expires),
                CacheType::FILE => self::$slave->fileWrite($key, $value, $expires),
            };
        }

        return false;
    }

    /**
     * Increments an integer cache value atomically.
     * 
     * @param string $key The cache key (must contain an integer value).
     * @param int $step The amount to increment by (default: 1).
     * @return int The incremented value or 0 on failure.
     */
    public static function increment(string $key, int $step = 1): int {
        if(self::$busy) {
            return match(self::$slave->getType()) {
                CacheType::APCU => self::$slave->apcuIncrement($key, $step),
                CacheType::SHARED => self::$slave->shmopIncrement($key, $step),
                CacheType::FILE => self::$slave->fileIncrement($key, $step),
            };
        }

        return 0;
    }

    /**
     * Decrements an integer cache value atomically.
     * 
     * @param string $key The cache key (must contain an integer value).
     * @param int $step The amount to decrement by (default: 1).
     * @return int The decremented value or 0 on failure.
     */
    public static function decrement(string $key, int $step = 1): int {
        if(self::$busy) {
            return match(self::$slave->getType()) {
                CacheType::APCU => self::$slave->apcuIncrement($key, -$step),
                CacheType::SHARED => self::$slave->shmopIncrement($key, -$step),
                CacheType::FILE => self::$slave->fileIncrement($key, -$step),
            };
        }

        return 0;
    }

    /**
     * Removes a cache item.
     * 
     * @param string $key The cache key.
     * @return bool True if removed successfully.
     */
    public static function remove(string $key): bool {
        if(self::$busy) {
            return match(self::$slave->getType()) {
                CacheType::APCU => self::$slave->apcuRemove($key),
                CacheType::SHARED => self::$slave->shmopRemove($key),
                CacheType::FILE => self::$slave->fileRemove($key),
            };
        }

        return false;
    }

    /**
     * Clears all cache entries.
     * Note: For shmop backend, this only returns false (cannot enumerate keys).
     * 
     * @return bool True on success.
     */
    public static function empty(): bool {
        if(self::$busy) {
            return match(self::$slave->getType()) {
                CacheType::APCU => self::$slave->apcuEmpty(),
                CacheType::SHARED => false,
                CacheType::FILE => self::$slave->fileEmpty(),
            };
        }

        return false;
    }

    /**
     * Runs garbage collection on file-based cache.
     * Only has effect when using FILE backend.
     * 
     * @param int $power Number of cache files to check for expiry.
     */
    public static function runGarbageCollector(int $power): void {
        if(self::$busy) {
            match(self::$slave->getType()) {
                CacheType::FILE => self::$slave->runGarbageCollector($power),
            };
        }
    }

    #/ METHODS
    #----------------------------------------------------------------------
}
