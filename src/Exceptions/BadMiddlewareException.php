<?php

namespace FastRaven\Exceptions;

class BadMiddlewareException extends SmartException
{
    /**
     * Construct a BadMiddlewareException.
     *
     * This exception is thrown when a middleware does not have the correct signature.
     *
     * @param string $missing The missing parameter(s).
     */
    public function __construct(string $missing) {
        parent::__construct("Middleware does not have the correct signature. ($missing)", "This resource is not available at this time.", 500);
    }
}