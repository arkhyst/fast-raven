<?php

namespace SmartGoblin\Data;

use SmartGoblin\Helpers\Database;
use SmartGoblin\Helpers\Bee;

class SQL {
    public static function query(string $contx, string $query, array $vars = []) : array {
        try {
            $pdo = new \PDO(Database::buildDSN($_ENV["DB_HOST"], $_ENV["DB_{$contx}_NAME"]), $_ENV["DB_{$contx}_USER"], $_ENV["DB_{$contx}_PASS"], [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
            ]);
        } catch (\Exception $e) {
            if (Bee::isDev()) echo $e->getMessage();
            http_response_code(500); exit;
        }

        $stmt = $pdo->prepare($query);
        $stmt->execute($vars);
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $data;
    }

    private static function buildSelectQuery(string $table, array $cols = ["*"], array $cond = []) : string {
        $query = "SELECT ";
        foreach ($cols as $col) $query .= $col.",";
        $query = $query.rtrim(",") . " FROM " . $table . " ";
        foreach ($cond as $c) $query .= $c."=? AND ";
        $query = $query.rtrim(" AND ") . ";";

        return $query;
    }

    public static function getFirst(string $contx, string $table, array $cols = ["*"], array $cond = [], array $vars = []) : array|null {
        $data = SQL::query($contx, self::buildSelectQuery($table, $cols, $cond), $vars);
        if(empty($data)) return null;
        else return $data[0];
    }

    public static function getAll(string $contx, string $table, array $cols = ["*"], array $cond = [], array $vars = []) : array {
        $data = SQL::query($contx, self::buildSelectQuery($table, $cols, $cond), $vars);
        return $data;
    }
}

?>