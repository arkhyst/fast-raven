<?php

namespace SmartGoblin\Workers;

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

    /**
     * Retrieves one row from the database that matches the given id.
     *
     * @param string $table The table to retrieve data from.
     * @param array $cols The columns to retrieve data from.
     * @param int $id The id of the row to retrieve.
     *
     * @return array|null The retrieved data, or null if an error occurred.
     */
    public static function getOneById(string $table, array $cols, int $id): ?array {
        if(self::$busy) {
            return self::$slave->getOne($table, $cols, ["id"], [$id]);
        }

        return null;
    }

    /**
     * Retrieves one row from the database that matches the given conditions.
     *
     * @param string $table The table to retrieve data from.
     * @param array $cols The columns to retrieve data from.
     * @param array $cond The conditions to filter the data with.
     * @param array $condValues The values to bind to the conditions.
     *
     * @return array|null The retrieved data, or null if an error occurred.
     */
    public static function getOneWhere(string $table, array $cols, array $cond, array $condValues): ?array {
        if(self::$busy) {
            return self::$slave->getOne($table, $cols, $cond, $condValues);
        }

        return null;
    }

    /**
     * Retrieves all rows from the database that match the given conditions.
     *
     * @param string $table The table to retrieve data from.
     * @param array $cols The columns to retrieve data from.
     * @param array $cond The conditions to filter the data with.
     * @param array $condValues The values to bind to the conditions.
     *
     * @return array|null The retrieved data, or null if an error occurred.
     */
    public static function getAllWhere(string $table, array $cols, array $cond, array $condValues): ?array {
        if(self::$busy) {
            return self::$slave->getAll($table, $cols, $cond, $condValues);
        }

        return null;
    }

    /**
     * Inserts a new row into the database.
     *
     * @param string $table The table to insert into.
     * @param array $cols The columns to insert data into.
     * @param array $values The values to insert into the columns.
     *
     * @return bool True if the insertion was successful, false otherwise.
     */
    public static function insert(string $table, array $cols, array $values) : bool {
        if(self::$busy) {
            return self::$slave->insert($table, $cols, $values);
        }

        return false;
    }

    /**
     * Updates existing rows in the database that match the given conditions.
     *
     * @param string $table The table to update rows in.
     * @param array $cols The columns to update.
     * @param array $newValues The values to update the columns with.
     * @param array $cond The conditions to filter the rows to update with.
     * @param array $condValues The values to bind to the conditions.
     *
     * @return bool True if the update was successful, false otherwise.
     */
    public static function updateWhere(string $table, array $cols, array $newValues, array $cond, array $condValues) : bool {
        if(self::$busy) {
            return self::$slave->update($table, $cols, $cond, array_merge($newValues, $condValues));
        }

        return false;
    }

    #/ METHODS
    #----------------------------------------------------------------------
}