<?php

namespace FastRaven\Components\Http;

final class Request {
    #----------------------------------------------------------------------
    #\ VARIABLES

    private string $internalID;
        public function getInternalID(): string { return $this->internalID; }
    private array $data;
        public function getDataItem(string $key): string|int|float|bool|null { return $this->data[$key] ?? null; }
    private string $method;
        public function getMethod(): string { return $this->method; }
    private string $path;
        public function getPath(): string { return $this->path; }
    private string $complexPath;
        public function getComplexPath(): string { return $this->complexPath; }
    private array $originInfo = [];
        public function getOriginInfo(): array { return $this->originInfo; }

    #/ VARIABLES
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ INIT

    /**
     * Initializes a new Request instance.
     *
     * @param string $uri              The original URI of the request.
     * @param string $method           The HTTP method of the request (e.g. GET, POST, PUT, DELETE).
     * @param string $dataStream       The data stream of the request.
     * @param string $remoteAddress    The remote address of the request.
     */
    public function __construct(string $uri, string $method, string $dataStream, string $remoteAddress) {
        $this->internalID = bin2hex(random_bytes(8));
        $this->data = json_decode($dataStream, true) ?? [];
        $this->data = $this->sanitizeData($this->data);
        
        $this->method = strtoupper($method);

        $this->path = parse_url($uri ?? "/", PHP_URL_PATH) ?? "/";
        $this->complexPath = (($this->path !== "/") ? rtrim($this->path, "/"): "/") . "#" . $this->method;
        $this->originInfo["IP"] = $remoteAddress;
    }

    #/ INIT
    #----------------------------------------------------------------------
    
    #----------------------------------------------------------------------
    #\ PRIVATE FUNCTIONS

    /**
     * Recursively sanitizes an array of data.
     *
     * This method cleans string values within the array by trimming whitespace,
     * stripping HTML tags, and converting special characters to HTML entities.
     *
     * @param array $data The data array to sanitize.
     *
     * @return array The sanitized data array.
     */
    private function sanitizeData(array $data): array {
        foreach ($data as $key => $item) {
            if(is_string($item)) {
                $data[$key] = trim(strip_tags($item));
            } elseif(is_array($item)) {
                $data[$key] = $this->sanitizeData($item);
            } 
        }

        return $data;
    }

    #/ PRIVATE FUNCTIONS
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ METHODS

    /**
     * Checks if the request is an API request.
     *
     * This method checks if the request's complex path starts with "/api/".
     *
     * @return bool True if the request is an API request, false otherwise.
     */
    public function isApi(): bool {
        return str_starts_with($this->complexPath,"/api/");
    }

    #/ METHODS
    #----------------------------------------------------------------------  
}

?>