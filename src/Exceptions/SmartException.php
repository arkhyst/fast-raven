<?php

namespace SmartGoblin\Exceptions;

class SmartException extends \Exception
{
    private int $statusCode = 500;
        public function getStatusCode(): int { return $this->statusCode; }
    private string $publicMessage = "";
        public function getPublicMessage(): string { return $this->publicMessage; }

    protected function __construct(string $message, string $publicMessage = "", int $statusCode = 500) {
        $this->statusCode = $statusCode;
        $this->publicMessage = $publicMessage;
        parent::__construct($message, 1, null);
    }
}