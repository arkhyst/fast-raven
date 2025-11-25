<?php

namespace SmartGoblin\Internal\Stash;

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

    public function addLog(string $log): void {
        $this->logList[] = $log;
    }

    public function empty(): void {
        $this->logList = [];
    }

    #/ METHODS
    #----------------------------------------------------------------------
}