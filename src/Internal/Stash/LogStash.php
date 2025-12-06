<?php

namespace FastRaven\Internal\Stash;

final class LogStash {
    #----------------------------------------------------------------------
    #\ VARIABLES

    private array $logList = [];
        public function getLogList(): array { return $this->logList; }

    #/ VARIABLES
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ INIT

    public function  __construct() {
        
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
     * Adds a log entry to the stash.
     * 
     * @param string $log The log entry to add.
     */
    public function addLog(string $log): void {
        $this->logList[] = $log;
    }

    /**
     * Empties the log stash.
     *
     * This function is used to empty the log stash after it has been written to a file.
     */

    public function empty(): void {
        $this->logList = [];
    }

    #/ METHODS
    #----------------------------------------------------------------------
}