<?php

namespace SmartGoblin\Internal\Stash;

final class MetaStash {
    private string $startRequestTime;
    
    public static function pack(): MetaStash {
        return new MetaStash();
    }

    private function  __construct() {
        $this->startRequestTime = microtime(true);
    }

    public function getStartRequestTime(): string { return $this->startRequestTime; }
}