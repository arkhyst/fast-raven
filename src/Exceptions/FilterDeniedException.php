<?php

namespace FastRaven\Exceptions;

class FilterDeniedException extends SmartException
{
    /**
     * Initializes the exception.
     *
     * This exception is thrown when a filter denies access to a resource.
     */
    public function __construct(int $code = 400, string $publicMessage = "Request does not meet the requirements.") {
        parent::__construct("Filter denied access to a resource.", $publicMessage, $code);
    }
}