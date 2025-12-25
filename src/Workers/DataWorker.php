<?php

namespace FastRaven\Workers;

use FastRaven\Internal\Slave\DataSlave;

use FastRaven\Components\Data\Collection;

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
     * @param string[] $cols The columns to retrieve data from.
     * @param int $id The id of the row to retrieve.
     *
     * @warning NEVER TRUST USER INPUT. ONLY COLLECTION VARIABLES ARE PROTECTED AGAINST SQL INJECTION.
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
     * @param string[] $cols The columns to retrieve data from.
     * @param Collection $conditionCollection The conditions to filter the data with.
     *
     * @warning NEVER TRUST USER INPUT. ONLY COLLECTION VARIABLES ARE PROTECTED AGAINST SQL INJECTION.
     * 
     * @return array|null The retrieved data, or null if an error occurred.
     */
    public static function getOneWhere(string $table, array $cols, Collection $conditionCollection): ?array {
        if(self::$busy) {
            return self::$slave->getOne($table, $cols, $conditionCollection->getAllKeys(), $conditionCollection->getAllValues());
        }

        return null;
    }

    /**
     * Retrieves all rows from the database that match the given conditions.
     *
     * @param string $table The table to retrieve data from.
     * @param string[] $cols The columns to retrieve data from.
     * @param Collection $conditionCollection The conditions to filter the data with.
     * @param string $orderBy [optional] The ORDER BY clause (e.g., "name ASC", "created_at DESC").
     * @param int $limit [optional] The maximum number of rows to retrieve.
     * @param int $offset [optional] The number of rows to skip.
     *
     * @warning NEVER TRUST USER INPUT. ONLY COLLECTION VARIABLES ARE PROTECTED AGAINST SQL INJECTION.
     * 
     * @return array|null The retrieved data, or null if an error occurred.
     */
    public static function getAllWhere(string $table, array $cols, Collection $conditionCollection, string $orderBy = "", int $limit = 0, int $offset = 0): ?array {
        if(self::$busy) {
            return self::$slave->getAll($table, $cols, $conditionCollection->getAllKeys(), $conditionCollection->getAllValues(), $orderBy, $limit, $offset);
        }

        return null;
    }

    /**
     * Retrieves all rows from the database without any conditions.
     *
     * @param string $table The table to retrieve data from.
     * @param string[] $cols The columns to retrieve data from.
     * @param string $orderBy [optional] The ORDER BY clause (e.g., "name ASC", "created_at DESC").
     * @param int $limit [optional] The maximum number of rows to retrieve.
     * @param int $offset [optional] The number of rows to skip.
     *
     * @warning NEVER TRUST USER INPUT. ONLY COLLECTION VARIABLES ARE PROTECTED AGAINST SQL INJECTION.
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
     * @param Collection $columnCollection Collection of columns to insert data into and their values.
     *
     * @warning NEVER TRUST USER INPUT. ONLY COLLECTION VARIABLES ARE PROTECTED AGAINST SQL INJECTION.
     * 
     * @return bool True if the insertion was successful, false otherwise.
     */
    public static function insert(string $table, Collection $columnCollection) : bool {
        if(self::$busy) {
            return self::$slave->insert($table, $columnCollection->getAllKeys(), $columnCollection->getAllValues());
        }

        return false;
    }

    
    /**
     * Inserts multiple rows into the database in a single transaction.
     *
     * @param string $table The table to insert into.
     * @param Collection[] $columnCollectionList List of Collections to insert data into and their values.
     *
     * @warning NEVER TRUST USER INPUT. ONLY COLLECTION VARIABLES ARE PROTECTED AGAINST SQL INJECTION.
     * 
     * @return bool True if all insertions were successful, false otherwise.
     */
    public static function insertBatch(string $table, array $columnCollectionList): bool {
        if(self::$busy) {
            if(!empty($columnCollectionList)) { // Hmmmm....
                $cols = $columnCollectionList[0]->getAllKeys();
                $values = [];
                foreach($columnCollectionList as $columnCollection) {
                    $values[] = $columnCollection->getAllValues();
                }
                return self::$slave->insertBatch($table, $cols, $values);
            }
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
     * @param Collection $columnCollection Collection of columns to update and their new values.
     * @param Collection $conditionCollection Collection of conditions to filter the rows to update with.
     *
     * @warning NEVER TRUST USER INPUT. ONLY COLLECTION VARIABLES ARE PROTECTED AGAINST SQL INJECTION.
     * 
     * @return bool True if the update was successful, false otherwise.
     */
    public static function updateWhere(string $table, Collection $columnCollection, Collection $conditionCollection) : bool {
        if(self::$busy) {
            return self::$slave->update($table, $columnCollection->getAllKeys(), $conditionCollection->getAllKeys(), array_merge($columnCollection->getAllValues(), $conditionCollection->getAllValues()));
        }

        return false;
    }

    /**
     * Updates a single row in the database by its ID.
     *
     * @param string $table The table to update the row in.
     * @param Collection $columnCollection Collection of columns to update and their new values.
     * @param int $id The ID of the row to update.
     *
     * @warning NEVER TRUST USER INPUT. ONLY COLLECTION VARIABLES ARE PROTECTED AGAINST SQL INJECTION.
     * 
     * @return bool True if the update was successful, false otherwise.
     */
    public static function updateById(string $table, int $id, Collection $columnCollection): bool {
        if(self::$busy) {
            return self::$slave->update($table, $columnCollection->getAllKeys(), ["id"], array_merge($columnCollection->getAllValues(), [$id]));
        }

        return false;
    }

    /**
     * Deletes a single row from the database by its ID.
     *
     * @param string $table The table to delete the row from.
     * @param int $id The ID of the row to delete.
     *
     * @warning NEVER TRUST USER INPUT. ONLY COLLECTION VARIABLES ARE PROTECTED AGAINST SQL INJECTION.
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
     * @param Collection $conditionCollection Collection of conditions to filter the rows to delete.
     *
     * @warning NEVER TRUST USER INPUT. ONLY COLLECTION VARIABLES ARE PROTECTED AGAINST SQL INJECTION.
     * 
     * @return bool True if the deletion was successful, false otherwise.
     */
    public static function deleteWhere(string $table, Collection $conditionCollection): bool {
        if(self::$busy) {
            return self::$slave->delete($table, $conditionCollection->getAllKeys(), $conditionCollection->getAllValues());
        }

        return false;   
    }

    /**
     * Counts the number of rows in the database that match the given conditions.
     *
     * @param string $table The table to count rows from.
     * @param Collection $conditionCollection Collection of conditions to filter the rows to count.
     *
     * @warning NEVER TRUST USER INPUT. ONLY COLLECTION VARIABLES ARE PROTECTED AGAINST SQL INJECTION.
     * 
     * @return int The number of rows that match the conditions.
     */
    public static function count(string $table, Collection $conditionCollection): int {
        if(self::$busy) {
            return self::$slave->count($table, $conditionCollection->getAllKeys(), $conditionCollection->getAllValues());
        }

        return 0;
    }

    /**
     * Counts all rows in the database table.
     *
     * @param string $table The table to count rows from.
     *
     * @warning NEVER TRUST USER INPUT. ONLY COLLECTION VARIABLES ARE PROTECTED AGAINST SQL INJECTION.
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
     * @param Collection $conditionCollection Collection of conditions to filter the rows.
     * 
     * @warning NEVER TRUST USER INPUT. ONLY COLLECTION VARIABLES ARE PROTECTED AGAINST SQL INJECTION.
     *
     * @return bool True if at least one row exists, false otherwise.
     */
    public static function exists(string $table, Collection $conditionCollection): bool {
        if(self::$busy) {
            return self::$slave->count($table, $conditionCollection->getAllKeys(), $conditionCollection->getAllValues()) > 0;
        }

        return false;
    }

    /**
     * Checks if a row with the given ID exists in the database.
     *
     * @param string $table The table to check for existence.
     * @param int $id The ID to check for.
     *
     * @warning NEVER TRUST USER INPUT. ONLY COLLECTION VARIABLES ARE PROTECTED AGAINST SQL INJECTION.
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