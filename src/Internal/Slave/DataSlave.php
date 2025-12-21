<?php

namespace FastRaven\Internal\Slave;

use FastRaven\Workers\DataWorker;
use FastRaven\Workers\LogWorker;

use FastRaven\Exceptions\SecurityVulnerabilityException;

use FastRaven\Workers\Bee;

enum QueryType: string {
    case SELECT = "SELECT";
    case INSERT = "INSERT";
    case UPDATE = "UPDATE";
    case DELETE = "DELETE";
    case COUNT = "COUNT";
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

        return null;
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
                $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            } catch (\PDOException $e) {
                $this->pdo = null;
                LogWorker::error("PDOException: ".$e->getMessage());
            }
        }
    }

    /**
     * This function will sanitize the given parameters.
     * It takes a table name, an array of column names, and an optional order by clause as parameters.
     * It will then sanitize the given parameters and throw a SecurityVulnerabilityException if any are found.
     *
     * @param string $table The name of the table to sanitize.
     * @param array $cols The array of column names to sanitize.
     * @param array $cond The array of condition column names to sanitize.
     * @param string $orderBy The optional order by clause to sanitize.
     * 
     * @throws SecurityVulnerabilityException If any possible SQL injection is found.
     */
    private function sanitizeParameters(string &$table, array &$cols, array &$cond = [], string &$orderBy = ""): void {
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $table)) {
            throw new SecurityVulnerabilityException("Invalid table name: $table");
        }

        foreach ($cols as $col) {
            if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $col)) {
                throw new SecurityVulnerabilityException("Invalid column name: $col");
            }
        }

        foreach ($cond as $col) {
            if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $col)) {
                throw new SecurityVulnerabilityException("Invalid condition column name: $col");
            }
        }

        if ($orderBy && !preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*(\s+(ASC|DESC))?(,\s*[a-zA-Z_][a-zA-Z0-9_]*(\s+(ASC|DESC))?)*$/i', $orderBy)) {
            throw new SecurityVulnerabilityException("Invalid order by: $orderBy");
        }

        $table = "`" . str_replace("`", "``", $table) . "`";
        $cols = array_map(fn($col) => "`" . str_replace("`", "``", $col) . "`", $cols);
        $cond = array_map(fn($col) => "`" . str_replace("`", "``", $col) . "`", $cond);
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
     * @param string $orderBy The optional order by clause.
     * @param int $limit The optional limit clause.
     * @param int $offset The optional offset clause.
     * 
     * @throws SecurityVulnerabilityException If the query is unsafe.
     * 
     * @return string The constructed query string.
     */
    private function buildQuery(QueryType $type, string $table, array $cols, array $cond = [], string $orderBy = "", int $limit = 0, int $offset = 0): string {
        $q = "";
        
        try {
            $this->sanitizeParameters($table, $cols, $cond, $orderBy);
        } catch (SecurityVulnerabilityException $e) {
            throw new SecurityVulnerabilityException("Possible SQL injection detected. Query not executed -> ".$e->getMessage());
        }

        if($type == QueryType::SELECT) {
            $q = "SELECT " . implode( ",", $cols) . " FROM " . $table;
            if(!empty($cond)) $q .= " WHERE " . implode(" AND ", array_map(fn($c) => "$c = ?", $cond));
            if($orderBy) $q .= " ORDER BY $orderBy";
            if($limit > 0) $q .= " LIMIT $limit";
            if($offset > 0) $q .= " OFFSET $offset";
        
        } else if($type == QueryType::COUNT) {
            $q = "SELECT COUNT(*) as count FROM " . $table;
            if(!empty($cond)) $q .= " WHERE " . implode(" AND ", array_map(fn($c) => "$c = ?", $cond));
        
        } else if($type == QueryType::INSERT) {
            $q = "INSERT INTO " . $table . "(" . implode(",", $cols) . ") VALUES (" . implode(",", array_fill(0, count($cols), "?")) . ")";
        
        } else if($type == QueryType::UPDATE) {
            $q = "UPDATE " . $table . " SET " . implode(",", array_map(fn($c) => "$c = ?", $cols));
            $q .= " WHERE " . implode(" AND ", array_map(fn($c) => "$c = ?", $cond));
        
        } else if($type == QueryType::DELETE) {
            $q = "DELETE FROM " . $table;
            if(!empty($cond)) $q .= " WHERE " . implode(" AND ", array_map(fn($c) => "$c = ?", $cond));
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
    private function simpleRequestToDatabase(QueryType $type, string $query, array $vars = [], bool $fetchAll = false): array|bool|int|null {
        $this->initializePDO();

        if($this->pdo) {
            try {
                $stmt = $this->pdo->prepare($query);
                $ok = $stmt->execute($vars);

                if($ok) {
                    if($type == QueryType::SELECT) return $fetchAll ? $stmt->fetchAll() : ($stmt->fetch() ?: null);
                    else if($type == QueryType::COUNT) return (int)$stmt->fetch()["count"];
                    else if($type == QueryType::DELETE) return $stmt->rowCount() > 0;
                    else if($type == QueryType::INSERT || $type == QueryType::UPDATE) return $ok;
                } else {
                    LogWorker::error("SQL Query was not successfull -> $query");
                    if($type == QueryType::SELECT) return null;
                    else if($type == QueryType::COUNT) return 0;
                    else if($type == QueryType::INSERT || $type == QueryType::UPDATE || $type == QueryType::DELETE) return false;
                }

            } catch (\PDOException $e) {
                LogWorker::error("PDOException: ".$e->getMessage());
                return null;
            }
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
        try {
            $query = $this->buildQuery(QueryType::SELECT, $table, $cols, $cond);
            return $this->simpleRequestToDatabase(QueryType::SELECT, $query, $vars, false);
        } catch (SecurityVulnerabilityException $e) {
            LogWorker::error($e->getMessage());
            return null;
        }
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
    public function getAll(string $table, array $cols, array $cond, array $vars, string $orderBy = "", int $limit = 0, int $offset = 0): ?array {
        try {
            $query = $this->buildQuery(QueryType::SELECT, $table, $cols, $cond, $orderBy, $limit, $offset);
            return $this->simpleRequestToDatabase(QueryType::SELECT, $query, $vars, true);
        } catch (SecurityVulnerabilityException $e) {
            LogWorker::error($e->getMessage());
            return null;
        }
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
        try {
            $query = $this->buildQuery(QueryType::INSERT, $table, $cols);
            $res = $this->simpleRequestToDatabase(QueryType::INSERT, $query, $values);
            return $res === true;
        } catch (SecurityVulnerabilityException $e) {
            LogWorker::error($e->getMessage());
            return false;
        }
    }

    /**
     * Gets the ID of the last inserted row.
     *
     * @return int|null The last insert ID, or null if an error occurred.
     */
    public function getLastInsertId(): ?int {
        $this->initializePDO();
        if($this->pdo) return (int)$this->pdo->lastInsertId();
        
        return null;
    }

    /**
     * Updates rows in the database that match the given conditions.
     *
     * @param string $table The table to update rows in.
     * @param array $cols The columns to update.
     * @param array $cond The conditions to filter the rows to update with.
     * @param array $vars The variables to bind to the query.
     * 
     * @return bool True if the update was successful, false otherwise.
     */
    public function update(string $table, array $cols, array $cond, array $vars): bool {
        try {
            $query = $this->buildQuery(QueryType::UPDATE, $table, $cols, $cond);
            $res = $this->simpleRequestToDatabase(QueryType::UPDATE, $query, $vars);
            return $res === true;
        } catch (SecurityVulnerabilityException $e) {
            LogWorker::error($e->getMessage());
            return false;
        }
    }

    /**
     * Deletes rows from the database that match the given conditions.
     *
     * @param string $table The table to delete rows from.
     * @param array $cond The conditions to filter the rows to delete.
     * @param array $vars The variables to bind to the query.
     * 
     * @return bool True if the deletion was successful, false otherwise.
     */
    public function delete(string $table, array $cond, array $vars): bool {
        try {
            $query = $this->buildQuery(QueryType::DELETE, $table, [], $cond);
            $res = $this->simpleRequestToDatabase(QueryType::DELETE, $query, $vars);
            return $res === true;
        } catch (SecurityVulnerabilityException $e) {
            LogWorker::error($e->getMessage());
            return false;
        }
    }

    /**
     * Counts rows in the database that match the given conditions.
     *
     * @param string $table The table to count rows from.
     * @param array $cond The conditions to filter the rows to count.
     * @param array $vars The variables to bind to the query.
     * 
     * @return int The number of rows that match the conditions.
     */
    public function count(string $table, array $cond, array $vars): int {
        try {
            $query = $this->buildQuery(QueryType::COUNT, $table, [], $cond);
            $res = $this->simpleRequestToDatabase(QueryType::COUNT, $query, $vars);
            return $res ?? 0;
        } catch (SecurityVulnerabilityException $e) {
            LogWorker::error($e->getMessage());
            return 0;
        }
    }

    /**
     * Inserts multiple rows into the database in a single transaction.
     *
     * @param string $table The table to insert into.
     * @param array $cols The columns to insert data into.
     * @param array $valuesArray Array of value arrays to insert.
     * 
     * @return bool True if all insertions were successful, false otherwise.
     */
    public function insertBatch(string $table, array $cols, array $valuesArray): bool {
        if (empty($valuesArray)) return false;

        try {
            $query = $this->buildQuery(QueryType::INSERT, $table, $cols);
            $this->initializePDO();
            if (!$this->pdo) return false;

            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare($query);

            foreach ($valuesArray as $values) {
                if (!$stmt->execute($values)) {
                    throw new \PDOException("Execution failed for a row in batch insert.");
                }
            }

            return $this->pdo->commit();
        } catch (\PDOException $e) {
            if ($this->pdo?->inTransaction()) $this->pdo->rollBack();
            LogWorker::error("PDOException during batch insert: ".$e->getMessage());
            return false;
        } catch (SecurityVulnerabilityException $e) {
            LogWorker::error($e->getMessage());
            return false;
        }
    }

    #/ METHODS
    #----------------------------------------------------------------------
}