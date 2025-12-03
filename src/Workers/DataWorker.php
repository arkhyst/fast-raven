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
     * @param string $orderBy [optional] The ORDER BY clause (e.g., "name ASC", "created_at DESC").
     * @param int $limit [optional] The maximum number of rows to retrieve.
     * @param int $offset [optional] The number of rows to skip.
     *
     * @return array|null The retrieved data, or null if an error occurred.
     */
    public static function getAllWhere(string $table, array $cols, array $cond, array $condValues, string $orderBy = "", int $limit = 0, int $offset = 0): ?array {
        if(self::$busy) {
            return self::$slave->getAll($table, $cols, $cond, $condValues, $orderBy, $limit, $offset);
        }

        return null;
    }

    /**
     * Retrieves all rows from the database without any conditions.
     *
     * @param string $table The table to retrieve data from.
     * @param array $cols The columns to retrieve data from.
     * @param string $orderBy [optional] The ORDER BY clause (e.g., "name ASC", "created_at DESC").
     * @param int $limit [optional] The maximum number of rows to retrieve.
     * @param int $offset [optional] The number of rows to skip.
     *
     * @return array|null The retrieved data, or null if an error occurred.
     */
    public static function getAll(string $table, array $cols, string $orderBy = "", int $limit = 0, int $offset = 0): ?array {
        if(self::$busy) {
            return self::$slave->getAll($table, $cols, [], [], $orderBy, $limit, $offset);
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
     * Inserts multiple rows into the database in a single transaction.
     *
     * @param string $table The table to insert into.
     * @param array $cols The columns to insert data into.
     * @param array $valuesArray Array of value arrays to insert (e.g., [["John", "john@example.com"], ["Jane", "jane@example.com"]]).
     *
     * @return bool True if all insertions were successful, false otherwise.
     */
    public static function insertBatch(string $table, array $cols, array $valuesArray): bool {
        if(self::$busy) {
            return self::$slave->insertBatch($table, $cols, $valuesArray);
        }

        return false;
    }

    /**
     * Gets the ID of the last inserted row.
     *
     * @return int|null The last insert ID, or null if an error occurred.
     */
    public static function getLastInsertId(): ?int {
        if(self::$busy) {
            return self::$slave->getLastInsertId();
        }

        return null;
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

    /**
     * Updates a single row in the database by its ID.
     *
     * @param string $table The table to update the row in.
     * @param array $cols The columns to update.
     * @param array $values The values to update the columns with.
     * @param int $id The ID of the row to update.
     *
     * @return bool True if the update was successful, false otherwise.
     */
    public static function updateById(string $table, array $cols, array $values, int $id): bool {
        if(self::$busy) {
            return self::$slave->update($table, $cols, ["id"], array_merge($values, [$id]));
        }

        return false;
    }

    /**
     * Deletes a single row from the database by its ID.
     *
     * @param string $table The table to delete the row from.
     * @param int $id The ID of the row to delete.
     *
     * @return bool True if the deletion was successful, false otherwise.
     */
    public static function deleteById(string $table, int $id): bool {
        if(self::$busy) {
            return self::$slave->delete($table, ["id"], [$id]);
        }

        return false;
    }

    /**
     * Deletes rows from the database that match the given conditions.
     *
     * @param string $table The table to delete rows from.
     * @param array $cond The conditions to filter the rows to delete.
     * @param array $condValues The values to bind to the conditions.
     *
     * @return bool True if the deletion was successful, false otherwise.
     */
    public static function deleteWhere(string $table, array $cond, array $condValues): bool {
        if(self::$busy) {
            return self::$slave->delete($table, $cond, $condValues);
        }

        return false;
    }

    /**
     * Counts the number of rows in the database that match the given conditions.
     *
     * @param string $table The table to count rows from.
     * @param array $cond The conditions to filter the rows to count.
     * @param array $condValues The values to bind to the conditions.
     *
     * @return int The number of rows that match the conditions.
     */
    public static function count(string $table, array $cond, array $condValues): int {
        if(self::$busy) {
            return self::$slave->count($table, $cond, $condValues);
        }

        return 0;
    }

    /**
     * Counts all rows in the database table.
     *
     * @param string $table The table to count rows from.
     *
     * @return int The total number of rows in the table.
     */
    public static function countAll(string $table): int {
        if(self::$busy) {
            return self::$slave->count($table, [], []);
        }

        return 0;
    }

    /**
     * Checks if a row exists in the database that matches the given conditions.
     *
     * @param string $table The table to check for existence.
     * @param array $cond The conditions to filter the rows.
     * @param array $condValues The values to bind to the conditions.
     *
     * @return bool True if at least one row exists, false otherwise.
     */
    public static function exists(string $table, array $cond, array $condValues): bool {
        if(self::$busy) {
            return self::$slave->count($table, $cond, $condValues) > 0;
        }

        return false;
    }

    /**
     * Checks if a row with the given ID exists in the database.
     *
     * @param string $table The table to check for existence.
     * @param int $id The ID to check for.
     *
     * @return bool True if a row with the given ID exists, false otherwise.
     */
    public static function existsById(string $table, int $id): bool {
        if(self::$busy) {
            return self::$slave->count($table, ["id"], [$id]) > 0;
        }

        return false;
    }

    #/ METHODS
    #----------------------------------------------------------------------
}