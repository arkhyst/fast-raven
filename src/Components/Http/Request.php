<?php

namespace SmartGoblin\Components\Http;

final class Request {
    #----------------------------------------------------------------------
    #\ VARIABLES

    private string $internalID;
        public function getInternalID(): string { return $this->internalID; }
    private array $data;
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
        $this->method = $method;

        $this->path = parse_url($uri ?? "/", PHP_URL_PATH) ?? "/";
        $this->complexPath = (($this->path !== "/") ? rtrim($this->path, "/"): "/") . "#" . $method;
        $this->originInfo["IP"] = $remoteAddress;
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
     * Checks if the request is an API request.
     *
     * This method checks if the request's complex path starts with "/api/".
     *
     * @return bool True if the request is an API request, false otherwise.
     */
    public function isApi(): bool {
        return str_starts_with($this->complexPath,"/api/");
    }

    /**
     * Get a data item from the request body.
     *
     * @param string $key The key of the data item to retrieve.
     *
     * @return string|int|bool|null The value of the data item if it exists, null otherwise.
     */
    public function getDataItem(string $key): string|int|float|bool|null {
        if (!isset($this->data[$key]))
            return null;

            $value = $this->data[$key];
            if (is_string($value)) return trim(strip_tags($value));
            if (is_scalar($value)) return $value;

            return null;
    }

    #/ METHODS
    #----------------------------------------------------------------------  
}

?>