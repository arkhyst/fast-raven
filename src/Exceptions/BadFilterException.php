<?php

namespace FastRaven\Exceptions;

class BadFilterException extends SmartException
{
    /**
     * Construct a BadFilterException.
     *
     * This exception is thrown when a filter does not have the correct signature.
     *
     * @param string $missing The missing parameter(s).
     */
    public function __construct(string $missing) {
        parent::__construct("Filter does not have the correct signature. ($missing)", "This resource is not available at this time.", 500);
    }
}