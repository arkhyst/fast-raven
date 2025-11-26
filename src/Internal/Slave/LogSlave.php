<?php

namespace SmartGoblin\Internal\Slave;

use SmartGoblin\Components\Http\Request;
use SmartGoblin\Worker\LogWorker;
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

    private function writeIntoFile(string $text): void {
        $dir = SITE_PATH . DIRECTORY_SEPARATOR . "logs" . DIRECTORY_SEPARATOR;
        if(!is_dir($dir)) mkdir($dir, 0755, true);

        file_put_contents($dir . date("dmY") . ".log", $text, FILE_APPEND | LOCK_EX);
    }

    #/ PRIVATE FUNCTIONS
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ METHODS

    public function writeOpenLogs(Request $request): void {
        $type = $request->isApi() ? "API" : "VIEW";
        LogWorker::log("# OPEN {$type} REQUEST({$request->getInternalID()})");
        LogWorker::log("-SG- -- Complex Path: {$request->getComplexPath()}");
        LogWorker::log("-SG- -- Remote Address: {$request->getOriginInfo()["IP"]}");
    }

    public static function writeCloseLogs(Request $request, float $elapsedTime): void {
        LogWorker::log("-SG- -- Request time: " . $elapsedTime . "ms");
        LogWorker::log("# CLOSE REQUEST({$request->getInternalID()})");
        LogWorker::log("=======================================================");
    }

    public function dumpLogStashIntoFile(): void { 
        $textBlock = "";
        foreach($this->stash->getLogList() as $log) $textBlock .= $log."\n";
        $this->writeIntoFile($textBlock);

        $this->stash->empty();
    }

    #/ METHODS
    #----------------------------------------------------------------------
}