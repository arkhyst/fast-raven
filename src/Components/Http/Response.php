<?php

namespace SmartGoblin\Components\Http;

enum DataType: string {
    case JSON = "application/json";
    case HTML = "text/html";
    case TEXT = "text";
}

class Response {
    private string $status;
    private int $code;
    private DataType $type;
    private string $message;
    private array $data;

    public static function new(bool $success, int $code, DataType $type = DataType::HTML): Response {
        return new Response($success, $code, $type);
    }
    
    public function __construct(bool $success, int $code, DataType $type = DataType::HTML) {
        $this->status = $success ? "OK" : "ERROR";
        $this->code = $code;
        $this->type = $type;
    }

    public function setBody(string $message = "", array $data = []): void {
        $this->message = $message;
        $this->data = $data;
    }

    public function getStatus(): string { return $this->status; }
    public function getCode(): int { return $this->code; }
    public function getType(): DataType { return $this->type; }
    public function getMessage(): string { return $this->message; }
    public function getData(): array { return $this->data; }    
}

?>