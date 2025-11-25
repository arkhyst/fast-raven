<?php

namespace SmartGoblin\Worker;

use SmartGoblin\Internal\Slave\HeaderSlave;

class HeaderWorker {
    #----------------------------------------------------------------------
    #\ VARIABLES

    private static bool $busy = false;
    private static HeaderSlave $slave;

    #/ VARIABLES
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ INIT

    public static function __getToWork(HeaderSlave &$slave): void {
        if(!self::$busy) {
            self::$busy = true;
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

    public static function addHeader(string $key, string $value): void {
        if(self::$busy) {
            self::$slave->addHeader($key, $value);
        }
    }

    public static function removeHeader(string $key): void {
        if(self::$busy) {
            self::$slave->removeHeader($key);
        }
    }

    #/ METHODS
    #----------------------------------------------------------------------
}