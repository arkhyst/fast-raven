<?php

namespace SmartGoblin\Components\Http;

enum DataType: string {
    case JSON = "application/json";
    case HTML = "text/html";
    case TEXT = "text";
}

final class Response {
    private string $status;
    private int $code;
    private string $message = "";
    private array $data = [];

    public static function new(bool $success, int $code): Response {
        return new Response($success, $code);
    }
    
    protected function __construct(bool $success, int $code) {
        $this->status = $success ? "OK" : "ERROR";
        $this->code = $code;
    }

    public function setBody(string $message = "", array $data = []): void {
        $this->message = $message;
        $this->data = $data;
    }

    public function getStatus(): string { return $this->status; }
    public function getCode(): int { return $this->code; }
    public function getMessage(): string { return $this->message; }
    public function getData(): array { return $this->data; }    
}

?>