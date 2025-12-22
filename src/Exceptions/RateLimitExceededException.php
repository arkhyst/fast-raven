<?php

namespace FastRaven\Exceptions;

class RateLimitExceededException extends SmartException
{
    private int $timeLeft;
    public function getTimeLeft(): int { return $this->timeLeft; }
    
    /**
     * Initializes the exception.
     *
     * This exception is thrown when a host exceeds its rate limit.
     */
    public function __construct(string $host, int $requestLeft, int $timeLeft = 60) {
        $this->timeLeft = $timeLeft;
        parent::__construct("$host reached its rate limit. ($requestLeft requests left)", "Please wait before making another request.", 429);
    }
}