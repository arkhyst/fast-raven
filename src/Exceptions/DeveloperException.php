<?php

namespace FastRaven\Exceptions;

class DeveloperException extends SmartException
{
    /**
     * Initializes a new instance of the DeveloperException class.
     *
     * This exception is thrown when the developer makes a mistake on View/API/CDN endpoints.
     */
    public function __construct(string $file, string $message) {
        parent::__construct('{'.$file.'} '.$message, "This resource is not available at this time.", 500);
    }
}