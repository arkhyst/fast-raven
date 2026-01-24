<?php

namespace FastRaven\Exceptions;

class BadImplementationException extends SmartException
{
    /**
     * Construct a BadImplementationException.
     *
     * This exception is thrown when the function associated with an endpoint does not return a valid object.
     *
     * @param string $filePath The path of the endpoint file.
     * @param string $type The type of object that was expected.
     */
    public function __construct(string $filePath, string $type = "Response") {
        parent::__construct("Endpoint does not return a valid $type object. ($filePath)", "This resource is not available at this time.", 500);
    }
}