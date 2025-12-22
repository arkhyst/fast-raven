<?php

namespace FastRaven\Components\Http;

/**
 * Sanitization levels for request data. Levels cascade: higher levels include all previous transformations.
 * 
 * Hierarchy:
 *   RAW ───────────────────────────── No changes
 *     └─ SAFE ────────────────────── Strips null bytes + PHP tags
 *           ├─ ENCODED ───────────── + htmlspecialchars (non-destructive)
 *           └─ SANITIZED ─────────── + strip_tags (destructive)
 *                 └─ ONLY_ALPHA ──── + alphanumeric/spaces only
 */
enum SanitizeType: int {
    case RAW = 0;
    case SAFE = 1;
    case ENCODED = 2;
    case SANITIZED = 3;
    case ONLY_ALPHA = 4;
}

final class Request {
    #----------------------------------------------------------------------
    #\ VARIABLES

    private string $internalID;
        public function getInternalID(): string { return $this->internalID; }
    private array $query = [];
    private array $data = [];
        /**
         * @deprecated Do not use this method to get request data. Use get() instead.
         */
        public function getDataItem(string $key): string|int|float|bool|null { return $this->data[$key] ?? null; }
    private string $method;
        public function getMethod(): string { return $this->method; }
    private string $path;
        public function getPath(): string { return $this->path; }
    private string $complexPath;
        public function getComplexPath(): string { return $this->complexPath; }
    private string $remoteAddress;
        public function getRemoteAddress(): string { return $this->remoteAddress; }

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
        $this->internalID = bin2hex(random_bytes(4));
        parse_str(parse_url($uri, PHP_URL_QUERY) ?? '', $this->query);
        $this->data = json_decode($dataStream, true) ?? [];
        
        $this->method = strtoupper($method);

        $this->path = parse_url($uri ?? "/", PHP_URL_PATH) ?? "/";
        $this->complexPath = (($this->path !== "/") ? rtrim($this->path, "/"): "/") . "#" . $this->method;
        $this->remoteAddress = $remoteAddress;
    }

    #/ INIT
    #----------------------------------------------------------------------
    
    #----------------------------------------------------------------------
    #\ PRIVATE FUNCTIONS

    
    /**
     * Sanitizes a value from an array based on the specified sanitization level.
     * 
     * @param array $array The array to sanitize.
     * @param string $key The key to sanitize.
     * @param SanitizeType $sanitizeType The sanitization level to apply (default: RAW).
     * 
     * @return mixed The sanitized value, or null if the key does not exist.
     */
    private function sanitizeDataItem(array $array, string $key, SanitizeType $sanitizeType = SanitizeType::RAW): mixed {
        $value = $array[$key] ?? null;
        
        if($value === null) return null;
        if(!is_string($value)) return $value;

        if($sanitizeType === SanitizeType::RAW) return $value;
        
        $value = preg_replace('/\x00|%00/i', "", $value);
        $value = preg_replace('/<\?(?:php|=)?[\s\S]*?\?>|<\?(?:php|=)?[\s\S]*/i', "", $value);
        if($sanitizeType === SanitizeType::SAFE) return $value;
        
        if($sanitizeType === SanitizeType::ENCODED) return htmlspecialchars($value, ENT_QUOTES, "UTF-8");
        
        $value = trim(strip_tags($value));
        if($sanitizeType === SanitizeType::SANITIZED) return $value;
        
        return preg_replace('/[^a-zA-Z0-9\s]/', "", $value);
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

    /**
     * Retrieves a value from the URI query string with optional sanitization.
     * 
     * Sanitization is cumulative. Each level includes all transformations from previous levels:
     * 
     * Note: ENCODED and SANITIZED both extend SAFE but are mutually exclusive branches.
     *
     * @param string $key The key to retrieve from the URI query string.
     * @param SanitizeType $sanitizeType The sanitization level to apply (default: RAW).
     * 
     * @return mixed The sanitized value, or null if the key does not exist.
     */
    public function get(string $key, SanitizeType $sanitizeType = SanitizeType::RAW): mixed {
        return $this->sanitizeDataItem($this->query, $key, $sanitizeType);
    }

    /**
     * Retrieves a value from the request data with optional sanitization.
     * 
     * Sanitization is cumulative. Each level includes all transformations from previous levels:
     * 
     * Note: ENCODED and SANITIZED both extend SAFE but are mutually exclusive branches.
     *
     * @param string $key The key to retrieve from request data.
     * @param SanitizeType $sanitizeType The sanitization level to apply (default: RAW).
     * 
     * @return mixed The sanitized value, or null if the key does not exist.
     */
    public function post(string $key, SanitizeType $sanitizeType = SanitizeType::RAW): mixed {
        return $this->sanitizeDataItem($this->data, $key, $sanitizeType);
    }

    #/ METHODS
    #----------------------------------------------------------------------  
}

?>