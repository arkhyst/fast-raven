<?php

namespace FastRaven\Exceptions;

class FilterDeniedException extends SmartException
{
    /**
     * Initializes the exception.
     *
     * This exception is thrown when a filter denies access to a resource.
     */
    public function __construct(int $code = 400, string $publicMessage = "This resource is not available at this time.") {
        parent::__construct("Filter denied access to a resource.", $publicMessage, $code);
    }
}