<?php

namespace App\Helpers;

use PDO;

class DBConnection {
    private static string $_host, $_dbname, $_user, $_pwd;
    private static ?PDO $connection = null; 



private function __construct() { 
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $db   = $_ENV['DB_NAME'] ?? 'website_db';
    $port = $_ENV['DB_PORT'] ?? '3306';
    $user = $_ENV['DB_USER'] ?? 'root';
    $pass = $_ENV['DB_PWD'] ?? '';

    self::$connection = new PDO(
        "mysql:host=$host;dbname=$db;port=$port;charset=utf8", 
        $user, 
        $pass
    );
    
    self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}

    public static function getInstance(): PDO
    {
        if (!self::$connection) {
            new self();
        }
        return self::$connection;
    }
}
