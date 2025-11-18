<?php

namespace SmartGoblin\Helpers;

class Database {
    public static function buildDSN(string $host, string $db) : string {
        return "mysql:host=$host;dbname=$db;charset=utf8mb4";
    }

}    