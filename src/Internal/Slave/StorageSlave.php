<?php

namespace FastRaven\Internal\Slave;

use FastRaven\Workers\StorageWorker;

use FastRaven\Workers\Bee;

final class StorageSlave {
    #----------------------------------------------------------------------
    #\ VARIABLES

    private static bool $busy = false;
    private int $fileUploadSizeLimit = -1;
        public function setFileUploadSizeLimit(int $fileUploadSizeLimit): void { $this->fileUploadSizeLimit = $fileUploadSizeLimit; }

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

    /**
     * Checks if a cache file exists.
     * 
     * @param string $key The key of the cache file to check.
     * 
     * @return bool True if the cache file exists, false otherwise.
     */
    public function cacheExists(string $key): bool {
        return file_exists($this->getCacheFilePath($key));
    }

    /**
     * Checks if an upload file exists.
     * 
     * @param string $file The file to check.
     * 
     * @return bool True if the upload file exists, false otherwise.
     */
    public function uploadExists(string $file): bool {
        return file_exists($this->getUploadFilePath($file));
    }

    /**
     * Retrieves a cache item.
     * 
     * @param string $key The key of the cache item to retrieve.
     * 
     * @return ?array The cache item if it exists, null otherwise.
     */
    public function getCache(string $key): ?array {
        if($this->cacheExists($key)) {
            $item = json_decode(file_get_contents($this->getCacheFilePath($key)), true);

            if(($item["expires"] ?? 0) > time()) {
                return $item;
            } else {
                $this->deleteCache($key);
                return null;
            }
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

    /**
     * Increments a cache item.
     * 
     * @param string $key The key of the cache item to increment.
     * @param int $value The number to increment the cache item by. Default is 1.
     * 
     * @return bool True if the cache item was successfully incremented, false otherwise.
     */
    public function incrementCache(string $key, int $value = 1): bool {
        $item = $this->getCache($key);

        if($item) {
            if(gettype($item["value"]) === "integer") return $this->setCache($key, $item["value"] + $value, 0);
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
    public function deleteCache(string $key): bool {
        if($this->cacheExists($key)) {
            return unlink($this->getCacheFilePath($key));
        }

        return false;
    }

    /**
     * Clears the cache.
     * 
     * @return bool True if the cache was successfully cleared, false otherwise.
     */
    public function clearCache(): bool {
        $files = glob(SITE_PATH . "storage" . DIRECTORY_SEPARATOR . "cache" . DIRECTORY_SEPARATOR . "*.cache", GLOB_NOSORT);
        
        if($files === false) return false;

        foreach($files as $file)
            if(!unlink($file)) return false;

        return true;
    }

    /**
     * Runs the garbage collector.
     * 
     * @param int $power The power of the garbage collector. The higher the power, the more files will be checked.
     */
    public function runGarbageCollector(int $power): void {
        if($power > 0) {
            $files = glob(SITE_PATH . "storage" . DIRECTORY_SEPARATOR . "cache" . DIRECTORY_SEPARATOR . "*.cache", GLOB_NOSORT);

            if($files !== false) {
                shuffle($files);
                for($i = 0; $i < count($files) && $i < $power; $i++) {
                    $this->getCache(pathinfo($files[$i], PATHINFO_FILENAME)); // Auto-checks if the cache is expired
                }
            }
        }
    }

    /**
     * Uploads a file to the storage/uploads directory.
     * 
     * @param string $tmpFile The temporary file path from $_FILES["name"]["tmp_name"].
     * @param string $destPath The destination path relative to storage/uploads.
     * 
     * @return bool True if the file was successfully uploaded, false otherwise.
     */
    public function uploadFile(string $tmpFile, string $destPath): bool {
        if(!is_uploaded_file($tmpFile)) return false;
        if($this->fileUploadSizeLimit >= 0 && filesize($tmpFile) > $this->fileUploadSizeLimit) return false;

        $dest = $this->getUploadFilePath($destPath);
        $dir = dirname($dest);

        if(!is_dir($dir) && !mkdir($dir, 0755, true)) return false;

        return move_uploaded_file($tmpFile, $dest);
    }

    /**
     * Reads a file from the storage/uploads directory.
     * 
     * @param string $path The file path relative to storage/uploads.
     * 
     * @return ?string File contents, or null if file doesn't exist.
     */
    public function readFileContents(string $path): ?string {
        $filePath = $this->getUploadFilePath($path);
        if(!is_file($filePath)) return null;
        
        return file_get_contents($filePath) ?: null;
    }

    /**
     * Deletes a file from the storage/uploads directory.
     * 
     * @param string $path The file path relative to storage/uploads.
     * 
     * @return bool True if file was deleted, false otherwise.
     */
    public function deleteFile(string $path): bool {
        $filePath = $this->getUploadFilePath($path);
        if(!is_file($filePath)) return false;
        
        return unlink($filePath);
    }

    #/ METHODS
    #----------------------------------------------------------------------
}