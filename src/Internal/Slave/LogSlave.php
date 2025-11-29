<?php

namespace SmartGoblin\Internal\Slave;

use SmartGoblin\Components\Http\Request;
use SmartGoblin\Workers\LogWorker;
use SmartGoblin\Internal\Stash\LogStash;

final class LogSlave {
    #----------------------------------------------------------------------
    #\ VARIABLES

    private static bool $busy = false;
    private LogStash $stash;
        public function insertLogIntoStash(string $text): void { $this->stash->addLog($text); }

    #/ VARIABLES
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ INIT

    /**
     * Initializes the LogSlave if it is not already busy.
     * 
     * This function will create a new LogSlave if it is not already busy.
     * It will then call LogWorker::__getToWork() and pass the new LogSlave object.
     * The new LogSlave object will be returned.
     * 
     * @return ?LogSlave The LogSlave object if it was successfully created, null otherwise.
     */
    public static function zap(): ?LogSlave {
        if(!self::$busy) {
            self::$busy = true;
            $inst = new LogSlave();
            LogWorker::__getToWork($inst);

            return $inst;
        }
    }

    private function __construct() {
        $this->stash = new LogStash();
    }

    #/ INIT
    #----------------------------------------------------------------------
    
    #----------------------------------------------------------------------
    #\ PRIVATE FUNCTIONS

    /**
     * Writes the given text into a log file in the logs directory.
     * If the logs directory does not exist, it will be created.
     * The log file will be named after the current date in the format "dmY.log", and will be appended to.
     * The function will lock the file while writing to ensure thread safety.
     *
     * @param string $text The text to write to the log file.
     */
    private function writeIntoFile(string $text): void {
        $dir = SITE_PATH . DIRECTORY_SEPARATOR . "logs" . DIRECTORY_SEPARATOR;
        if(!is_dir($dir)) mkdir($dir, 0755, true);

        file_put_contents($dir . date("dmY") . ".log", $text, FILE_APPEND | LOCK_EX);
    }

    #/ PRIVATE FUNCTIONS
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ METHODS

    /**
     * Writes an open log entry for the given request.
     * 
     * @param Request $request The request object for which the open log entry should be written.
     */
    public function writeOpenLogs(Request $request): void {
        $type = $request->isApi() ? "API" : "VIEW";
        LogWorker::log("# OPEN {$type} REQUEST({$request->getInternalID()})");
        LogWorker::log("-SG- -- Complex Path: {$request->getComplexPath()}");
        LogWorker::log("-SG- -- Remote Address: {$request->getOriginInfo()["IP"]}");
    }

    /**
     * Writes a close log entry for the given request.
     * This function will log the request time and some other information.
     * The request time will be logged in milliseconds.
     *
     * @param Request $request The request object for which the close log entry should be written.
     * @param float $elapsedTime The time it took to process the request in milliseconds.
     */
    public static function writeCloseLogs(Request $request, float $elapsedTime): void {
        LogWorker::log("-SG- -- Request time: " . $elapsedTime . "ms");
        LogWorker::log("# CLOSE REQUEST({$request->getInternalID()})");
        LogWorker::log("=======================================================");
    }

    /**
     * Writes all the log entries stored in the stash into a log file.
     * The log file will be named after the current date in the format "dmY.log", and will be appended to.
     * The function will lock the file while writing to ensure thread safety.
     * Finally, the stash will be emptied.
     */
    public function dumpLogStashIntoFile(): void { 
        $textBlock = "";
        foreach($this->stash->getLogList() as $log) $textBlock .= $log."\n";
        $this->writeIntoFile($textBlock);

        $this->stash->empty();
    }

    #/ METHODS
    #----------------------------------------------------------------------
}