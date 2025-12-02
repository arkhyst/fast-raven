<?php

namespace SmartGoblin\Exceptions;

class NotFoundException extends SmartException
{
    /**
     * Initializes the exception.
     *
     * This exception is thrown when no matching route is found for a request.
     */
    public function __construct() {
        parent::__construct("No matching route found for request.", "Not found.", 404);
    }
}