<?php

namespace FastRaven\Workers;

use FastRaven\Internal\Slave\FileSlave;

class FileWorker {
    #----------------------------------------------------------------------
    #\ VARIABLES

    private static bool $busy = false;
    private static FileSlave $slave;

    #/ VARIABLES
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ INIT

    public static function __getToWork(FileSlave &$slave): void {
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

    public static function getUploadFilePath(string $file): ?string {
        if(self::$busy) { 
            return self::$slave->getUploadFilePath($file);
        }

        return null;
    }

    public static function exists(string $path): bool {
        if(self::$busy) { 
            return self::$slave->exists($path); 
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
    public static function upload(string $tmpFile, string $destPath): bool {
        if(self::$busy) {
            return self::$slave->upload($tmpFile, $destPath);
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
    public static function read(string $path): ?string {
        if(self::$busy) {
            return self::$slave->read($path);
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
    public static function delete(string $path): bool {
        if(self::$busy) {
            return self::$slave->delete($path);
        }

        return false;
    }

    #/ METHODS
    #----------------------------------------------------------------------
}