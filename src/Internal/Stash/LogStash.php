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
     * Replaces a log entry in the stash.
     *
     * @param int $index The index of the log entry to replace.
     * @param string $search The text to search for in the log entry.
     * @param string $replace The text to replace the search text with.
     */
    public function replaceLog(int $index, string $search, string $replace): void {
        if (isset($this->logList[$index])) {
            $this->logList[$index] = str_replace($search, $replace, $this->logList[$index]);
        }
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