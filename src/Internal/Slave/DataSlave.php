<?php

namespace SmartGoblin\Internal\Slave;

use SmartGoblin\Workers\DataWorker;
use SmartGoblin\Workers\LogWorker;

use SmartGoblin\Workers\Bee;

enum QueryType: string {
    case SELECT = "SELECT";
    case INSERT = "INSERT";
    case UPDATE = "UPDATE";

}

final class DataSlave {
    #----------------------------------------------------------------------
    #\ VARIABLES

    private static bool $busy = false;
    private ?\PDO $pdo = null;

    #/ VARIABLES
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ INIT

    /**
     * This function will create a new DataSlave if it is not already busy.
     * It will then call DataWorker::__getToWork() and pass the new DataSlave object.
     * The new DataSlave object will be returned.
     *
     * @return ?DataSlave The DataSlave object if it was successfully created, null otherwise.
     */
    public static function zap(): ?DataSlave {
        if(!self::$busy) {
            self::$busy = true;
            $inst = new DataSlave();
            DataWorker::__getToWork($inst);

            return $inst;
        }
    }

    private function __construct() {

    }

    #/ INIT
    #----------------------------------------------------------------------
    
    #----------------------------------------------------------------------
    #\ PRIVATE FUNCTIONS

    /**
     * This function will return a DSN string for the database connection.
     * It takes the host and database name as parameters and returns a string in the format:
     * "mysql:host=$host;dbname=$db;charset=utf8mb4"
     *
     * @param string $host The host of the database server.
     * @param string $db The name of the database.
     * @return string The DSN string.
     */
    private function buildDatabaseDSN(string $host, string $db): string {
        return "mysql:host=$host;dbname=$db;charset=utf8mb4";
    }

    /**
     * This function will initialize the PDO object for the DataSlave.
     * If the PDO object is not already initialized, it will attempt to create a new PDO object with the database connection settings.
     * If the PDO object cannot be created, an error will be logged and the PDO object will be set to null.
     */
    private function initializePDO(): void {
        if(!$this->pdo) {
            try {
                $this->pdo = new \PDO($this->buildDatabaseDSN(Bee::env("DB_HOST"), Bee::env("DB_NAME")), Bee::env("DB_USER"), Bee::env("DB_PASS"));
                $this->pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
                $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_SILENT);
            } catch (\PDOException $e) {
                $this->pdo = null;
                LogWorker::error("-SG- PDOException: ".$e->getMessage());
            }
        }
    }

    /**
     * This function will build a SQL query string based on the given parameters.
     * It takes a QueryType enum, a table name, an array of column names, and an optional array of condition key-value pairs.
     * It will then construct a query string based on the given parameters and return it.
     *
     * @param QueryType $type The type of query to construct.
     * @param string $table The name of the table to query.
     * @param array $cols The array of column names to query.
     * @param array $cond The optional array of condition key-value pairs.
     * 
     * @return string The constructed query string.
     */
    private function buildQuery(QueryType $type, string $table, array $cols, array $cond = []): string {
        $q = "";
        if($type == QueryType::SELECT) {
            $q = "SELECT " . implode( ",", $cols) . " FROM " . $table;
            if(!empty($cond)) $q .= " WHERE " . implode(" AND ", array_map(fn($c) => "$c = ?", $cond));
        
        } else if($type == QueryType::INSERT) {
            $q = "INSERT INTO " . $table . "(" . implode(",", $cols) . ") VALUES (" . implode(",", array_fill(0, count($cols), "?")) . ")";
        
        } else if($type == QueryType::UPDATE) {
            $q = "UPDATE " . $table . " SET " . implode(",", array_map(fn($c) => "$c = ?", $cols));
            $q .= " WHERE " . implode(" AND ", array_map(fn($c) => "$c = ?", $cond));
        }

        return "$q;";
    }

    /**
     * This function will execute a given SQL query with given variables and return the results.
     * It takes a QueryType enum, a query string, an array of variables, and a boolean flag to indicate whether to fetch all results or not.
     * If the query is of type SELECT, it will return an array of results, otherwise it will return a boolean indicating whether the query was successful or not.
     *
     * @param QueryType $type The type of query to execute.
     * @param string $query The SQL query string to execute.
     * @param array $vars The array of variables to bind to the query.
     * @param bool $fetchAll Whether to fetch all results or not.
     * 
     * @return array|bool|null The result of the query, or null if an error occurred.
     */
    private function simpleRequestToDatabase(QueryType $type, string $query, array $vars = [], bool $fetchAll = false): array|bool|null {
        $this->initializePDO();

        if($this->pdo) {
            $stmt = $this->pdo->prepare($query);
            $ok = $stmt->execute($vars);
            if(!$ok) LogWorker::error("-SG- SQL Query was not successfull -> $query");

            if($type == QueryType::SELECT) return ($fetchAll ? $stmt->fetchAll() : $stmt->fetch());
            else if($type == QueryType::INSERT || $type == QueryType::UPDATE) return $ok;
        }

        return null;
    }

    #/ PRIVATE FUNCTIONS
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ METHODS

    /**
     * Executes a SQL query to retrieve one row from the database.
     *
     * @param string $table The table to retrieve data from.
     * @param array $cols The columns to retrieve data from.
     * @param array $cond The conditions to filter the data with.
     * @param array $vars The variables to bind to the query.
     * 
     * @return array|null The retrieved data, or null if an error occurred.
     */
    public function getOne(string $table, array $cols, array $cond, array $vars): ?array {
        $query = $this->buildQuery(QueryType::SELECT, $table, $cols, $cond);
        return $this->simpleRequestToDatabase(QueryType::SELECT, $query, $vars, false);
    }

    /**
     * Executes a SQL query to retrieve all rows from the database that match the given conditions.
     *
     * @param string $table The table to retrieve data from.
     * @param array $cols The columns to retrieve data from.
     * @param array $cond The conditions to filter the data with.
     * @param array $vars The variables to bind to the query.
     * 
     * @return array|null The retrieved data, or null if an error occurred.
     */
    public function getAll(string $table, array $cols, array $cond, array $vars): ?array {
        $query = $this->buildQuery(QueryType::SELECT, $table, $cols, $cond);
        return $this->simpleRequestToDatabase(QueryType::SELECT, $query, $vars, true);
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
    public function insert(string $table, array $cols, array $values): bool {
        $query = $this->buildQuery(QueryType::INSERT, $table, $cols);
        return $this->simpleRequestToDatabase(QueryType::INSERT, $query, $values);
    }


    /**
     * Updates existing rows in the database.
     *
     * @param string $table The table to update rows in.
     * @param array $cols The columns to update.
     * @param array $cond The conditions to filter the rows to update with.
     * @param array $vars The variables to bind to the query.
     * 
     * @return bool True if the update was successful, false otherwise.
     */
    public function update(string $table, array $cols, array $cond, array $vars): bool {
        $query = $this->buildQuery(QueryType::UPDATE, $table, $cols, $cond);
        return $this->simpleRequestToDatabase(QueryType::UPDATE, $query, $vars);
    }

    #/ METHODS
    #----------------------------------------------------------------------
}