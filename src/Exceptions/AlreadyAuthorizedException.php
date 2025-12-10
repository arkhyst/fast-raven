<?php

namespace FastRaven\Exceptions;

class AlreadyAuthorizedException extends SmartException
{
    /**
     * Initializes a new instance of the AlreadyAuthorizedException class.
     *
     * This exception is thrown when the client is already authorized to access an unauthorized exclusive resource.
     */
    public function __construct() {
        parent::__construct("Authorized user tried to access an unauthorized exclusive resource.", "You are already authorized!", 403);
    }
}