<?php

namespace FastRaven\Internal\Slave;

use FastRaven\Workers\StorageWorker;

use FastRaven\Workers\Bee;

final class StorageSlave {
    #----------------------------------------------------------------------
    #\ VARIABLES

    private static bool $busy = false;

    #/ VARIABLES
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ INIT

    /**
     * Initializes the StorageSlave if it is not already busy.
     * 
     * This function will create a new StorageSlave if it is not already busy.
     * It will then call StorageWorker::__getToWork() and pass the new StorageSlave object.
     * The new StorageSlave object will be returned.
     * 
     * @return ?StorageSlave The StorageSlave object if it was successfully created, null otherwise.
     */
    public static function zap(): ?StorageSlave {
        if(!self::$busy) {
            self::$busy = true;
            $inst = new StorageSlave();
            StorageWorker::__getToWork($inst);

            return $inst;
        }
        
        return null;
    }

    private function __construct() {

    }

    #/ INIT
    #----------------------------------------------------------------------
    
    #----------------------------------------------------------------------
    #\ PRIVATE FUNCTIONS

    private function getCacheFilePath(string $key): string {
        return SITE_PATH . "storage" . DIRECTORY_SEPARATOR . "cache" . DIRECTORY_SEPARATOR . Bee::normalizePath($key) . ".cache";
    }

    private function getUploadFilePath(string $file): string {
        return SITE_PATH . "storage" . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR . Bee::normalizePath($file);
    }

    #/ PRIVATE FUNCTIONS
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ METHODS

    public function cacheExists(string $key): bool {
        return file_exists($this->getCacheFilePath($key));
    }

    public function uploadExists(string $file): bool {
        return file_exists($this->getUploadFilePath($file));
    }

    public function getCache(string $key): ?array {
        if($this->cacheExists($key)) {
            $item = json_decode(file_get_contents($this->getCacheFilePath($key)), true);

            if($item["expires"] > time()) {
                return $item;
            } else {
                $this->deleteCache($key);
                return null;
            }
        }

        return null;
    }

    public function setCache(string $key, mixed $value, int $expires): bool {
        $item = $this->getCache($key);

        if($item) {
            $item["value"] = $value;
        } else {
            $item = [
                "expires" => time() + $expires,
                "value" => $value
            ];
        }

        $path = $this->getCacheFilePath($key);
        return file_put_contents($path, json_encode($item)) !== false;
    }

    public function deleteCache(string $key): bool {
        if($this->cacheExists($key)) {
            return unlink($this->getCacheFilePath($key));
        }

        return false;
    }

    #/ METHODS
    #----------------------------------------------------------------------
}