<?php

namespace SmartGoblin\Worker;

use SmartGoblin\Internal\Slave\LogSlave;

class LogWorker {
    #----------------------------------------------------------------------
    #\ VARIABLES

    private static bool $busy = false;
    private static LogSlave $slave;

    #/ VARIABLES
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ INIT

    public static function __getToWork(LogSlave &$slave): void {
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

    public static function log(string $text): void {
        if(self::$busy) {
            self::$slave->insertLogIntoStash("[".date("Y-m-d H:i:s")."] ".$text);
        }
    }

    #/ METHODS
    #----------------------------------------------------------------------
}