<?php

namespace FastRaven\Services;

use FastRaven\Internals\Engines\FileEngine;

use FastRaven\Components\Core\File;

final class FileService {
    #----------------------------------------------------------------------
    #\ VARIABLES

    private static bool $ready = false;
    private static FileEngine $engine;

    #/ VARIABLES
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ INIT

    public static function __getToWork(FileEngine &$engine): void {
        if(!self::$ready) {
            self::$ready = true;
            self::$engine = $engine;
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
        if(self::$ready) { 
            return self::$engine->getUploadFilePath($file);
        }

        return null;
    }

    public static function exists(string $path): bool {
        if(self::$ready) { 
            return self::$engine->exists($path); 
        }

        return false;
    }

    /**
     * Uploads a file to the storage/uploads directory.
     * 
     * @param File $file The file to upload.
     * @param string $destPath The destination path relative to storage/uploads.
     * 
     * @return bool True if the file was successfully uploaded, false otherwise.
     */
    public static function upload(File $file, string $destPath): bool {
        if(self::$ready) {
            return self::$engine->upload($file->getPath(), $destPath);
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
        if(self::$ready) {
            return self::$engine->read($path);
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
        if(self::$ready) {
            return self::$engine->delete($path);
        }

        return false;
    }

    #/ METHODS
    #----------------------------------------------------------------------
}