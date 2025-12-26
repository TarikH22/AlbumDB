<?php


// Set the reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL ^ (E_NOTICE | E_DEPRECATED));


class Config
{
    private static function parseDatabaseUrl()
    {
        $databaseUrl = getenv('DATABASE_URL');
        if ($databaseUrl) {
            $parsed = parse_url($databaseUrl);
            return [
                'host' => $parsed['host'] ?? null,
                'port' => $parsed['port'] ?? 3306,
                'user' => $parsed['user'] ?? null,
                'password' => $parsed['pass'] ?? null,
                'dbname' => ltrim($parsed['path'] ?? '', '/')
            ];
        }
        return null;
    }

    public static function DB_NAME()
    {
        $parsed = self::parseDatabaseUrl();
        return getenv('DB_NAME') ?: ($parsed['dbname'] ?? 'albumDB');
    }
    public static function DB_PORT()
    {
        $parsed = self::parseDatabaseUrl();
        return getenv('DB_PORT') ?: ($parsed['port'] ?? 3306);
    }
    public static function DB_USER()
    {
        $parsed = self::parseDatabaseUrl();
        return getenv('DB_USER') ?: ($parsed['user'] ?? 'root');
    }
    public static function DB_PASSWORD()
    {
        $parsed = self::parseDatabaseUrl();
        return getenv('DB_PASSWORD') ?: ($parsed['password'] ?? 'Tarik123');
    }
    public static function DB_HOST()
    {
        $parsed = self::parseDatabaseUrl();
        return getenv('DB_HOST') ?: ($parsed['host'] ?? '127.0.0.1');
    }


    public static function JWT_SECRET()
    {
        return getenv('JWT_SECRET') ?: 'tarik_secret_key';
    }
}
