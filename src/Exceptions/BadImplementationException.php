<?php

namespace FastRaven\Exceptions;

class BadImplementationException extends SmartException
{
    /**
     * Construct a BadImplementationException.
     *
     * This exception is thrown when the function associated with an endpoint does not return a Response object.
     *
     * @param string $filePath The path of the endpoint file.
     */
    public function __construct(string $filePath) {
        parent::__construct("Endpoint does not return Response object. ($filePath)", "This resource is not available at this time.", 500);
    }
}