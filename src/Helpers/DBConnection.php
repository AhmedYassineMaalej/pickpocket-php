<?php

namespace App\Helpers;

use PDO;

class DBConnection
{
    private static string $_host;
    private static string $_dbname;
    private static string $_user;
    private static string $_pwd;
    private static ?PDO $connection = null;



    private function __construct()
    {
        self::$_host = $_ENV['HOST'];
        self::$_dbname = $_ENV['DB_NAME'];
        self::$_user = $_ENV['USER'];
        self::$_pwd = $_ENV['DB_PASS'];

        self::$connection = new PDO(
            "mysql:host=" . self::$_host . ";dbname=" . self::$_dbname . ";charset=utf8",
            self::$_user,
            self::$_pwd,
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
