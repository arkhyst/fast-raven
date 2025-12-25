<?php

namespace FastRaven\Internal\Slave;

use FastRaven\Workers\FileWorker;

use FastRaven\Workers\Bee;

final class FileSlave {
    #----------------------------------------------------------------------
    #\ VARIABLES

    private static bool $busy = false;
    private int $fileUploadSizeLimit = -1;

    #/ VARIABLES
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ INIT

    /**
     * Initializes the FileSlave if it is not already busy.
     * 
     * This function will create a new FileSlave if it is not already busy.
     * It will then call FileWorker::__getToWork() and pass the new FileSlave object.
     * The new FileSlave object will be returned.
     * 
     * @return ?FileSlave The FileSlave object if it was successfully created, null otherwise.
     */
    public static function zap(int $fileUploadSizeLimit = -1): ?FileSlave {
        if(!self::$busy) {
            self::$busy = true;
            $inst = new FileSlave($fileUploadSizeLimit);
            FileWorker::__getToWork($inst);

            return $inst;
        }
        
        return null;
    }

    private function __construct(int $fileUploadSizeLimit = -1) {
        $this->fileUploadSizeLimit = $fileUploadSizeLimit;
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
     * Retrieves the path to an uploaded file.
     * 
     * @param string $file The file to retrieve the path for.
     * 
     * @return string The path to the uploaded file.
     */
    public function getUploadFilePath(string $file): string {
        return SITE_PATH . "storage" . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR . Bee::normalizePath($file);
    }

    public function exists(string $path): bool {
        return file_exists($this->getUploadFilePath($path));
    }
    
    /**
     * Uploads a file to the storage/uploads directory.
     * 
     * @param string $tmpFile The temporary file path from $_FILES["name"]["tmp_name"].
     * @param string $destPath The destination path relative to storage/uploads.
     * 
     * @return bool True if the file was successfully uploaded, false otherwise.
     */
    public function upload(string $tmpFile, string $destPath): bool {
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
    public function read(string $path): ?string {
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
    public function delete(string $path): bool {
        $filePath = $this->getUploadFilePath($path);
        if(!is_file($filePath)) return false;
        
        return unlink($filePath);
    }

    #/ METHODS
    #----------------------------------------------------------------------
}