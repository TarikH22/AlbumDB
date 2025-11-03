<?php

require_once __DIR__ . '/../config.php';

abstract class BaseDAO {
    protected $connection;
    protected $tableName;

    public function __construct() {
        $this->connection = Database::connect();
    }

   
    protected function getConnection() {
        return $this->connection;
    }

   
    protected function executeQuery($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception("Database error: " . $e->getMessage());
        }
    }

   
    protected function getLastInsertId() {
        return $this->connection->lastInsertId();
    }

    
    protected function beginTransaction() {
        $this->connection->beginTransaction();
    }

    
    protected function commit() {
        $this->connection->commit();
    }

    
    protected function rollback() {
        $this->connection->rollBack();
    }


    protected function exists($column, $value) {
        $sql = "SELECT COUNT(*) FROM {$this->tableName} WHERE {$column} = ?";
        $stmt = $this->executeQuery($sql, [$value]);
        return $stmt->fetchColumn() > 0;
    }

    
    public function count() {
        $sql = "SELECT COUNT(*) FROM {$this->tableName}";
        $stmt = $this->executeQuery($sql);
        return (int)$stmt->fetchColumn();
    }
}
?>
