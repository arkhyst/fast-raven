<?php

namespace SmartGoblin\Http;

enum DataType : string {
    case JSON = "application/json";
    case HTML = "text/html";
    case TEXT = "text";
}

class Response {
    private bool $completed;

    private string $status;
    private int $code;
    private DataType $type;
    private array $data;

    
    public function __construct(bool $success, int $code, DataType $type) {
        $this->completed = false;

        $this->status = $success ? "OK" : "NOK";
        $this->code = $code;
        $this->type = $type;
    }

    public function setData(array $data): void {
        $this->data = $data;
    }

    public function send() : void {
        http_response_code($this->code);
        header("Content-Type: {$this->type->value}; charset=utf-8");

        if ($this->type == DataType::JSON) {
            echo json_encode([
                "status" => $this->status,
                "data" => $this->data
            ]);
        }

        exit(0);
    }
}

?>