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

    public static function getCache(string $key): ?array {
        if(self::$busy) { 
            return self::$slave->getCache($key); 
        }

        return null;
    }

    public static function getCacheValue(string $key): mixed {
        if(self::$busy) { 
            return self::$slave->getCache($key)["value"] ?? null; 
        }

        return null;
    }

    public static function setCache(string $key, mixed $value, int $expires): bool {
        if(self::$busy) {
            return self::$slave->setCache($key, $value, $expires);
        }

        return false;
    }

    public static function updateCache(string $key, mixed $value): bool {
        if(self::$busy) {
            if(self::$slave->cacheExists($key)) {
                return self::$slave->setCache($key, $value, 0);
            }
        }

        return false;
    }

    public static function incrementCache(string $key, int $num): bool {
        if(self::$busy) {
            $item = self::$slave->getCache($key);
            if($item) {
                if(gettype($item["value"]) == "integer") return self::$slave->setCache($key, $item["value"] + $num, 0);
            }
        }

        return false;
    }

    public static function deleteCache(string $key): bool {
        if(self::$busy) {
            return self::$slave->deleteCache($key);
        }

        return false;
    }

    #/ METHODS
    #----------------------------------------------------------------------
}