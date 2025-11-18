<?php

namespace SmartGoblin\Core\Craft;

class Meta {
    private string $startRequestTime;
    
    public function  __construct() {
        $this->startRequestTime = microtime(true);
    }

    public function getStartRequestTime(): string { return $this->startRequestTime; }
}