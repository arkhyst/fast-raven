<?php

namespace FastRaven\Workers;

use FastRaven\Internal\Slave\HeaderSlave;

final class HeaderWorker {
    #----------------------------------------------------------------------
    #\ VARIABLES

    private static bool $ready = false;
    private static HeaderSlave $slave;

    #/ VARIABLES
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ INIT

    public static function __getToWork(HeaderSlave &$slave): void {
        if(!self::$ready) {
            self::$ready = true;
            self::$slave = $slave;
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
            self::$slave->addHeader($key, $value);
        }
    }

    /**
     * Removes a header from the response.
     *
     * @param string $key The key of the header to remove.
     */
    public static function removeHeader(string $key): void {
        if(self::$ready) {
            self::$slave->removeHeader($key);
        }
    }

    #/ METHODS
    #----------------------------------------------------------------------
}