<?php

namespace SmartGoblin\Exceptions;

class NotAuthorizedException extends SmartException
{
    private bool $domainLevel;
        public function isDomainLevel(): bool { return $this->domainLevel; }

    /**
     * Initializes a new instance of the NotAuthorizedException class.
     *
     * This exception is thrown when the client is not authorized to access a private resource.
     */
    public function __construct(bool $domainLevel = false) {
        $this->domainLevel = $domainLevel;
        if($domainLevel) parent::__construct("Unauthorized user tried to access private subdomain.", "Authorization required.", 401);
        parent::__construct("Unauthorized user tried to access private resource.", "Authorization required.", 401);
    }
}