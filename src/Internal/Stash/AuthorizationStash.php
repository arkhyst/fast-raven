<?php

namespace SmartGoblin\Internal\Stash;

final class AuthorizationStash {
    public static function pack(): AuthorizationStash {
        return new AuthorizationStash();
    }

    private function  __construct() {
        
    }
}