<?php


// Set the reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL ^ (E_NOTICE | E_DEPRECATED));


class Config
{
    public static function DB_NAME()
    {
        return getenv('DB_NAME') ?: 'albumDB';
    }
    public static function DB_PORT()
    {
        return getenv('DB_PORT') ?: 3306;
    }
    public static function DB_USER()
    {
        return getenv('DB_USER') ?: 'root';
    }
    public static function DB_PASSWORD()
    {
        return getenv('DB_PASSWORD') ?: 'Tarik123';
    }
    public static function DB_HOST()
    {
        return getenv('DB_HOST') ?: '127.0.0.1';
    }


    public static function JWT_SECRET()
    {
        return getenv('JWT_SECRET') ?: 'tarik_secret_key';
    }
}
