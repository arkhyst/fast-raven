<?php

namespace FastRaven\Exceptions;

class MiddlewareDeniedException extends SmartException
{
    /**
     * Initializes the exception.
     *
     * This exception is thrown when a middleware denies access to a resource.
     */
    public function __construct(int $code = 400, string $publicMessage = "Request does not meet the requirements.") {
        parent::__construct("Middleware denied access to a resource.", $publicMessage, $code);
    }
}