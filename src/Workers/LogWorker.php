<?php

namespace SmartGoblin\Workers;

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

    /**
     * Logs the given text to the log file.
     * The text will be prefixed with the current date and time in the format "Y-m-d H:i:s".
     *
     * @param string $text The text to log.
     */
    public static function log(string $text): void {
        if(self::$busy) {
            self::$slave->insertLogIntoStash("[".date("Y-m-d H:i:s")."] ".$text);
        }
    }

    /**
     * Writes an error log entry to the log file.
     * The text will be prefixed with the current date and time in the format "Y-m-d H:i:s",
     * and will be marked as "**ERROR**".
     * 
     * @param string $text The error text to log.
     */
    public static function error(string $text): void {
        if(self::$busy) {
            self::$slave->insertLogIntoStash("[".date("Y-m-d H:i:s")."] **ERROR** => ".$text);
        }
    }

    /**
     * Writes a warning log entry to the log file.
     * The text will be prefixed with the current date and time in the format "Y-m-d H:i:s",
     * and will be marked as "**WARNING**".
     * 
     * @param string $text The warning text to log.
     */
    public static function warning(string $text): void {
        if(self::$busy) {
            self::$slave->insertLogIntoStash("[".date("Y-m-d H:i:s")."] **WARNING** => ".$text);
        }
    }

    #/ METHODS
    #----------------------------------------------------------------------
}