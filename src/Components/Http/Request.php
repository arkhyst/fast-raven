<?php

namespace SmartGoblin\Components\Http;

final class Request {
    #----------------------------------------------------------------------
    #\ VARIABLES

    private string $internalID;
        public function getInternalID(): string { return $this->internalID; }
    private array $data;
        public function getDataItem(string $key): string|int|bool|null {
            if(isset($this->data[$key])) {
                return trim(strip_tags($this->data[$key]));
            }

            return null;
        }
    private string $method;
        public function getMethod(): string { return $this->method; }
    private string $complexPath;
        public function getComplexPath(): string { return $this->complexPath; }
    private array $originInfo = [];
        public function getOriginInfo(): array { return $this->originInfo; }

    #/ VARIABLES
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ INIT

    public function __construct(string $uri, string $method, string $dataStream, string $remoteAddress) {
        $this->internalID = bin2hex(random_bytes(8));
        $this->data = json_decode($dataStream, true) ?? [];
        $this->method = $method;

        $requestPath = parse_url($uri ?? "/", PHP_URL_PATH) ?: "/";
        $requestPath = ($requestPath !== "/") ? rtrim($requestPath, "/"): "/";
        $this->complexPath = $requestPath . "#" . $method;

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

    public function isApi(): bool {
        return str_starts_with($this->complexPath,"/api/");
    }

    #/ METHODS
    #----------------------------------------------------------------------  
}

?>