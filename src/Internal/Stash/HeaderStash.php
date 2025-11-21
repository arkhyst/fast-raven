<?php

namespace SmartGoblin\Internal\Stash;

final class HeaderStash {
    private array $headerList = [];
    private array $headerRemoveList = [];
    
    public static function pack(bool $isApi, array $allowedHosts, string $https, string $origin): HeaderStash {
        return new HeaderStash( $isApi, $allowedHosts, $https, $origin);
    }

    protected function  __construct(bool $isApi, array $allowedHosts, string $https, string $origin) {
        $this->setSecurityHeaders($allowedHosts, $https, $origin);
        $this->setUtilityHeaders($isApi);
    }

    // TODO: Enable wildcard for allowedHosts
    private function setSecurityHeaders(array $allowedHosts, string $https, string $origin): void {
        $this->headerRemoveList = array_merge($this->headerRemoveList, ["X-Powered-By"]);

        $this->headerList = array_merge($this->headerList, [
            "X-Content-Type-Options" => "nosniff",
            "Referrer-Policy" => "strict-origin-when-cross-origin",
            "Cross-Origin-Resource-Policy" => "same-origin",
            "Content-Security-Policy" => "frame-ancestors 'none';",
            "X-Frame-Options" => "DENY",
            "Access-Control-Allow-Methods" => "GET, POST, OPTIONS",
            "Access-Control-Allow-Headers" => "Content-Type",
        ]);

        if (!empty($https) && $https !== 'off') {
            $this->headerList = array_merge($this->headerList, [
                "Strict-Transport-Security" => "max-age=31536000; includeSubDomains; preload",
            ]);
        }
        
        $origin = $origin ?? ""; // TODO: Do more research about HTTP_ORIGIN
        if (in_array($origin, $allowedHosts, true)) {
            $this->headerList = array_merge($this->headerList, [
                "Access-Control-Allow-Origin" => "https://$origin",
                "Access-Control-Allow-Credentials" => "true",
                "Vary" => "Origin",
            ]);
        }
    }

    private function setUtilityHeaders(bool $isApi): void {
        // TODO: Add complexity for better cache control
        if($isApi) { 
            $this->headerList = array_merge($this->headerList, [
                "Cache-Control" => "private, no-store, must-revalidate",
            ]);
        } else {
            $this->headerList = array_merge($this->headerList, [
                "Cache-Control" => "private, max-age=0, no-cache, must-revalidate",
            ]);
        }
    }

    public function getHeaderList(): array { return $this->headerList; }
    public function getHeaderRemoveList(): array { return $this->headerRemoveList; }

    public function addHeader(string $key, string $value): void {
        $this->headerList[$key] = $value;
    }
    public function getHeader(string $key): ?string {
        return $this->headerList[$key] ?? null;
    }
}