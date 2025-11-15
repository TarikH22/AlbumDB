<?php
class Database
{
    private static $host = '127.0.0.1';
    private static $dbName = 'albumDB';
    private static $username = 'root';
    private static $password = 'Tarik123';
    private static $dbPort = 3306;
    private static $connection = null;


    public static function connect()
    {
        if (self::$connection === null) {
            try {
                self::$connection = new PDO(
                    "mysql:host=" . self::$host . ";port=" . self::$dbPort . ";dbname=" . self::$dbName,
                    self::$username,
                    self::$password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]
                );
            } catch (PDOException $e) {
                die("Connection failed: " . $e->getMessage());
            }
        }
        return self::$connection;
    }
}
