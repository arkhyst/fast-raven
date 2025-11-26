<?php

namespace SmartGoblin\Internal\Slave;

use SmartGoblin\Worker\DataWorker;
use SmartGoblin\Worker\LogWorker;

enum QueryType: string {
    case SELECT = "SELECT";
    case INSERT = "INSERT";

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

    private function buildDatabaseDSN(string $host, string $db): string {
        return "mysql:host=$host;dbname=$db;charset=utf8mb4";
    }

    private function initializePDO(): void {
        if(!$this->pdo) {
            try {
                $this->pdo = new \PDO($this->buildDatabaseDSN($_ENV["DB_HOST"], $_ENV["DB_NAME"]), $_ENV["DB_USER"], $_ENV["DB_PASS"]);
                $this->pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
            } catch (\PDOException $e) {
                $this->pdo = null;
                LogWorker::error("-SG- PDOException: ".$e->getMessage());
            }
        }
    }

    private function buildSELECT(string $table, array $cols, array $cond = []): string {
        $q = "SELECT " . implode( ",", $cols) . " FROM " . $table;
        if(!empty($cond)) $q .= " WHERE " . implode(" AND ", array_map(fn($c) => "$c = ?", $cond));

        return "$q;";
    }

    private function buildINSERT(string $table, array $cols): string {
        $q = "INSERT INTO " . $table . "(" . implode(",", $cols) . ") VALUES (" . implode(",", array_fill(0, count($cols), "?")) . ")";
        
        return "$q;";
    }

    private function simpleRequestToDatabase(QueryType $type, string $query, array $vars = [], bool $fetchAll = false): array|bool|null {
        $this->initializePDO();

        if($this->pdo) {
            $stmt = $this->pdo->prepare($query);
            $ok = $stmt->execute($vars);
            if(!$ok) LogWorker::error("-SG- SQL Query was not successfull -> $query");

            if($type == QueryType::SELECT) return ($fetchAll ? $stmt->fetchAll() : $stmt->fetch());
            else if($type == QueryType::INSERT) return $ok;
        }

        return null;
    }

    #/ PRIVATE FUNCTIONS
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ METHODS

    public function getOne(string $table, array $cols, array $cond, array $vars): ?array {
        $query = $this->buildSELECT($table, $cols, $cond);
        return $this->simpleRequestToDatabase(QueryType::SELECT, $query, $vars, false);
    }

    public function getAll(string $table, array $cols, array $cond, array $vars): ?array {
        $query = $this->buildSELECT($table, $cols, $cond);
        return $this->simpleRequestToDatabase(QueryType::SELECT, $query, $vars, true);
    }

    public function insert(string $table, array $cols, array $values): bool {
        $query = $this->buildINSERT($table, $cols);
        return $this->simpleRequestToDatabase(QueryType::INSERT, $query, $values);
    }

    #/ METHODS
    #----------------------------------------------------------------------
}