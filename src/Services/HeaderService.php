<?php

namespace FastRaven\Services;

use FastRaven\Internals\Engines\HeaderEngine;

final class HeaderService {
    #----------------------------------------------------------------------
    #\ VARIABLES

    private static bool $ready = false;
    private static HeaderEngine $engine;

    #/ VARIABLES
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ INIT

    public static function __getToWork(HeaderEngine &$engine): void {
        if(!self::$ready) {
            self::$ready = true;
            self::$engine = $engine;
        }
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
     * Adds a header to the response.
     *
     * @param string $key The key of the header.
     * @param string $value The value of the header.
     */
    public static function addHeader(string $key, string $value): void {
        if(self::$ready) {
            self::$engine->addHeader($key, $value);
        }
    }

    /**
     * Removes a header from the response.
     *
     * @param string $key The key of the header to remove.
     */
    public static function removeHeader(string $key): void {
        if(self::$ready) {
            self::$engine->removeHeader($key);
        }
    }

    #/ METHODS
    #----------------------------------------------------------------------
}