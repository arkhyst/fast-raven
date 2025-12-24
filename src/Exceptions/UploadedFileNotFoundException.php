<?php

namespace FastRaven\Exceptions;

class UploadedFileNotFoundException extends SmartException
{
    /**
     * Initializes a new instance of the UploadedFileNotFoundException class.
     *
     * This exception is thrown when the kernel is unable to locate the file associated with an endpoint.
     *
     * @param string $filePath The path of the file that was not found.
     */
    public function __construct(string $filePath) {
        parent::__construct("Uploaded file does not exist! ($filePath)", "This resource is not available at this time.", 500);
    }
}