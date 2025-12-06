<?php

namespace FastRaven\Components\Http;

enum DataType: string {
    case JSON = "application/json";
    case HTML = "text/html";
    case TEXT = "text";
}

final class Response {
    #----------------------------------------------------------------------
    #\ VARIABLES

    private string $status;
        public function getStatus(): string { return $this->status; }
    private int $code;
        public function getCode(): int { return $this->code; }
    private string $message = "";
        public function getMessage(): string { return $this->message; }
    private mixed $data = [];
        public function getData(): mixed { return $this->data; }
    
    #/ VARIABLES
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ INIT

    /**
     * Creates a new Response instance.
     *
     * @param bool $success Whether the response is a success or not.
     * @param int $code The HTTP status code of the response.
     * @param string $message [optional] The message to set on the response.
     * @param string|array $data [optional] The data to set on the response.
     * 
     * @return Response The new Response instance.
     */
    public static function new(bool $success, int $code, string $message = "", string|array $data = []): Response
    {
        $response = new Response($success, $code);
        $response->setBody($message, $data);
        return $response;
    }

    private function __construct(bool $success, int $code) {
        $this->status = $success ? "OK" : "ERROR";
        $this->code = $code;
    }

    #/ INIT
    #----------------------------------------------------------------------
    
    #----------------------------------------------------------------------
    #\ PRIVATE FUNCTIONS



    #/ PRIVATE FUNCTIONS
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ METHODS

    /**
     * Sets the body of the response.
     *
     * @param string $message [optional] The message to set on the response.
     * @param mixed $data [optional] The data to set on the response.
     */
    public function setBody(string $message = "", mixed $data = []): void {
        $this->message = $message;
        $this->data = $data;
    }

    #/ METHODS
    #----------------------------------------------------------------------
}

?>