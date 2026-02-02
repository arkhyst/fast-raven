<?php

namespace FastRaven\Services;

use FastRaven\Internals\Engines\LogEngine;

use FastRaven\Bee;

final class LogService {
    #----------------------------------------------------------------------
    #\ VARIABLES

    private static bool $ready = false;
    private static LogEngine $engine;

    #/ VARIABLES
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ INIT

    public static function __getToWork(LogEngine &$engine): void {
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
     * Logs the given text to the log file.
     * The text will be prefixed with the current date and time in the format "Y-m-d H:i:s".
     *
     * @param string $text The text to log.
     */
    public static function log(string $text): void {
        if(self::$ready) {
            self::$engine->log($text);
        }
    }

    /**
     * Writes an error log entry to the log file.
     * The text will be prefixed with the current date and time in the format "Y-m-d H:i:s",
     * and will be marked as "//ERROR//".
     * 
     * @param string $text The error text to log.
     */
    public static function error(string $text): void {
        if(self::$ready) {
            self::$engine->log("//ERROR// ".$text);
        }
    }

    /**
     * Writes a warning log entry to the log file.
     * The text will be prefixed with the current date and time in the format "Y-m-d H:i:s",
     * and will be marked as "//WARN//".
     * 
     * @param string $text The warning text to log.
     */
    public static function warning(string $text): void {
        if(self::$ready) {
            self::$engine->log("//WARN// ".$text);
        }
    }

    /**
     * Writes a debug log entry to the log file.
     * The text will be prefixed with the current date and time in the format "Y-m-d H:i:s",
     * and will be marked as "/SG/".
     * 
     * @param string $text The debug text to log.
     */
    public static function debug(string $text): void {
        if(self::$ready && Bee::isDev()) {
            self::$engine->log("/SG/ ".$text);
        }
    }

    #/ METHODS
    #----------------------------------------------------------------------
}