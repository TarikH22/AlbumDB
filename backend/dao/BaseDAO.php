<?php

require_once __DIR__ . '/../config.php';

abstract class BaseDAO
{
    protected $connection;
    protected $tableName;
    private static $sharedConnection = null;

    public function __construct()
    {
        $this->connection = self::connect();
    }

    private static function connect()
    {
        if (self::$sharedConnection === null) {
            try {
                self::$sharedConnection = new PDO(
                    "mysql:host=" . Config::DB_HOST() . ";port=" . Config::DB_PORT() . ";dbname=" . Config::DB_NAME(),
                    Config::DB_USER(),
                    Config::DB_PASSWORD(),
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]
                );
            } catch (PDOException $e) {
                die("Connection failed: " . $e->getMessage());
            }
        }
        return self::$sharedConnection;
    }


    protected function getConnection()
    {
        return $this->connection;
    }


    protected function executeQuery($sql, $params = [])
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception("Database error: " . $e->getMessage());
        }
    }


    protected function getLastInsertId()
    {
        return $this->connection->lastInsertId();
    }


    protected function beginTransaction()
    {
        $this->connection->beginTransaction();
    }


    protected function commit()
    {
        $this->connection->commit();
    }


    protected function rollback()
    {
        $this->connection->rollBack();
    }


    protected function exists($column, $value)
    {
        $sql = "SELECT COUNT(*) FROM {$this->tableName} WHERE {$column} = ?";
        $stmt = $this->executeQuery($sql, [$value]);
        return $stmt->fetchColumn() > 0;
    }


    public function count()
    {
        $sql = "SELECT COUNT(*) FROM {$this->tableName}";
        $stmt = $this->executeQuery($sql);
        return (int)$stmt->fetchColumn();
    }
    protected function query($query, $params)
    {
        $stmt = $this->connection->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    protected function query_unique($query, $params)
    {
        $results = $this->query($query, $params);
        return reset($results);
    }
    public function add($entity)
    {
        $query = "INSERT INTO " . $this->tableName . " (";
        foreach ($entity as $column => $value) {
            $query .= $column . ', ';
        }
        $query = substr($query, 0, -2);
        $query .= ") VALUES (";
        foreach ($entity as $column => $value) {
            $query .= ":" . $column . ', ';
        }
        $query = substr($query, 0, -2);
        $query .= ")";

        $stmt = $this->connection->prepare($query);
        $stmt->execute($entity);
        $entity['id'] = $this->connection->lastInsertId();
        return $entity;
    }
}
