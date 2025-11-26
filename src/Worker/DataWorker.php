<?php

namespace SmartGoblin\Worker;

use SmartGoblin\Internal\Slave\DataSlave;

class DataWorker {
    #----------------------------------------------------------------------
    #\ VARIABLES

    private static bool $busy = false;
    private static DataSlave $slave;

    #/ VARIABLES
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ INIT

    public static function __getToWork(DataSlave &$slave): void {
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

    public static function getOneById(string $table, array $cols, int $id): ?array {
        if(self::$busy) {
            return self::$slave->getOne($table, $cols, ["id"], [$id]);
        }

        return null;
    }

    public static function getOneWhere(string $table, array $cols, array $cond, array $vars): ?array {
        if(self::$busy) {
            return self::$slave->getOne($table, $cols, $cond, $vars);
        }

        return null;
    }

    public static function getAllWhere(string $table, array $cols, array $cond, array $vars): ?array {
        if(self::$busy) {
            return self::$slave->getAll($table, $cols, $cond, $vars);
        }

        return null;
    }

    public static function insert(string $table, array $cols, array $values) : bool {
        if(self::$busy) {
            return self::$slave->insert($table, $cols, $values);
        }

        return false;
    }

    #/ METHODS
    #----------------------------------------------------------------------
}