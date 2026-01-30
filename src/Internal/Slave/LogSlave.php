<?php

namespace FastRaven\Internal\Slave;

use FastRaven\Workers\LogWorker;

use FastRaven\Components\Http\Request;

use FastRaven\Types\ProjectFolderType;

use FastRaven\Bee;

final class LogSlave {
    #----------------------------------------------------------------------
    #\ VARIABLES

    private static bool $busy = false;
    private array $logs = [];
    private string $requestInternalId;

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
    public static function zap(string $requestInternalId): ?LogSlave {
        if(!self::$busy) {
            self::$busy = true;
            $inst = new LogSlave($requestInternalId);
            LogWorker::__getToWork($inst);

            return $inst;
        }

        return null;
    }

    private function __construct(string $requestInternalId) {
        $this->requestInternalId = $requestInternalId;
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
        file_put_contents(Bee::buildProjectPath(ProjectFolderType::STORAGE_LOGS, date("Y-m-d").".log"), $text, FILE_APPEND | LOCK_EX);
    }

    #/ PRIVATE FUNCTIONS
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ METHODS

    /**
     * Inserts a log entry into the stash with a timestamp and request internal ID.
     *
     * @param string $text The log message to be inserted.
     */
    public function log(string $text): void {
        $this->logs[] = "[".date("Y-m-d H:i:s")."]-({$this->requestInternalId}) {$text}"; 
    }

    /**
     * Writes an open log entry for the given request.
     * 
     * @param Request $request The request object for which the open log entry should be written.
     */
    public function writeOpenLogs(Request $request): void {
        LogWorker::log("{$request->getType()->value}[{$request->getMethod()}] > {$request->getPath()} < STATUS_CODE > {$request->getRemoteAddress()} < ELAPSED_TIMEms");
    }

    /**
     * Writes a close log entry for the given request.
     * This function will log the request time and some other information.
     * The request time will be logged in milliseconds.
     *
     * @param float $elapsedTime The time it took to process the request in milliseconds.
     * @param int $statusCode The status code of the response.
     */
    public function writeCloseLogs(float $elapsedTime, int $statusCode): void {
        $this->logs[0] = str_replace("ELAPSED_TIME", strval($elapsedTime), $this->logs[0]);
        $this->logs[0] = str_replace("STATUS_CODE", strval($statusCode), $this->logs[0]);
    }

    /**
     * Writes all the log entries stored in the stash into a log file.
     * The log file will be named after the current date in the format "dmY.log", and will be appended to.
     * The function will lock the file while writing to ensure thread safety.
     * Finally, the stash will be emptied.
     */
    public function dumpLogsIntoFile(): void { 
        if(empty($this->logs)) return;

        $textBlock = implode("\n", $this->logs) . "\n";
        $this->writeIntoFile($textBlock);
        $this->logs = [];
    }

    #/ METHODS
    #----------------------------------------------------------------------
}