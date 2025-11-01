<?php

require_once __DIR__ . '/BaseDAO.php';

class UserDAO extends BaseDAO {

    public function __construct() {
        parent::__construct();
        $this->tableName = 'users';
    }

    public function create($data) {
        try {
            $sql = "INSERT INTO users (username, email, password_hash, first_name, last_name, avatar_url)
                    VALUES (:username, :email, :password_hash, :first_name, :last_name, :avatar_url)";

            $params = [
                ':username' => $data['username'],
                ':email' => $data['email'],
                ':password_hash' => $data['password_hash'],
                ':first_name' => $data['first_name'],
                ':last_name' => $data['last_name'],
                ':avatar_url' => $data['avatar_url'] ?? 'https://via.placeholder.com/200x200?text=User'
            ];

            $this->executeQuery($sql, $params);
            return $this->getLastInsertId();
        } catch (Exception $e) {
            error_log("UserDAO::create() Error: " . $e->getMessage());
            return false;
        }
    }

    public function getById($userId) {
        try {
            $sql = "SELECT user_id, username, email, first_name, last_name, avatar_url,
                           created_at, updated_at, last_login, is_active
                    FROM users WHERE user_id = ?";

            $stmt = $this->executeQuery($sql, [$userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("UserDAO::getById() Error: " . $e->getMessage());
            return false;
        }
    }

    public function getByUsername($username) {
        try {
            $sql = "SELECT user_id, username, email, password_hash, first_name, last_name,
                           avatar_url, created_at, updated_at, last_login, is_active
                    FROM users WHERE username = ?";

            $stmt = $this->executeQuery($sql, [$username]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("UserDAO::getByUsername() Error: " . $e->getMessage());
            return false;
        }
    }

    public function getByEmail($email) {
        try {
            $sql = "SELECT user_id, username, email, password_hash, first_name, last_name,
                           avatar_url, created_at, updated_at, last_login, is_active
                    FROM users WHERE email = ?";

            $stmt = $this->executeQuery($sql, [$email]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("UserDAO::getByEmail() Error: " . $e->getMessage());
            return false;
        }
    }

    public function getAll($limit = null, $offset = 0) {
        try {
            $sql = "SELECT user_id, username, email, first_name, last_name, avatar_url,
                           created_at, updated_at, last_login, is_active
                    FROM users
                    ORDER BY created_at DESC";

            if ($limit !== null) {
                $sql .= " LIMIT :limit OFFSET :offset";
            }

            $stmt = $this->connection->prepare($sql);

            if ($limit !== null) {
                $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
                $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("UserDAO::getAll() Error: " . $e->getMessage());
            return [];
        }
    }

    public function update($userId, $data) {
        try {
            $fields = [];
            $params = [':user_id' => $userId];

            $allowedFields = ['username', 'email', 'first_name', 'last_name', 'avatar_url', 'is_active'];

            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $fields[] = "$field = :$field";
                    $params[":$field"] = $data[$field];
                }
            }

            if (empty($fields)) {
                return false;
            }

            $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE user_id = :user_id";

            $this->executeQuery($sql, $params);
            return true;
        } catch (Exception $e) {
            error_log("UserDAO::update() Error: " . $e->getMessage());
            return false;
        }
    }

    public function updatePassword($userId, $newPasswordHash) {
        try {
            $sql = "UPDATE users SET password_hash = :password_hash WHERE user_id = :user_id";

            $params = [
                ':password_hash' => $newPasswordHash,
                ':user_id' => $userId
            ];

            $this->executeQuery($sql, $params);
            return true;
        } catch (Exception $e) {
            error_log("UserDAO::updatePassword() Error: " . $e->getMessage());
            return false;
        }
    }

    public function updateLastLogin($userId) {
        try {
            $sql = "UPDATE users SET last_login = NOW() WHERE user_id = :user_id";
            $this->executeQuery($sql, [':user_id' => $userId]);
            return true;
        } catch (Exception $e) {
            error_log("UserDAO::updateLastLogin() Error: " . $e->getMessage());
            return false;
        }
    }

    public function delete($userId) {
        try {
            $sql = "UPDATE users SET is_active = 0 WHERE user_id = :user_id";
            $this->executeQuery($sql, [':user_id' => $userId]);
            return true;
        } catch (Exception $e) {
            error_log("UserDAO::delete() Error: " . $e->getMessage());
            return false;
        }
    }

    public function hardDelete($userId) {
        try {
            $sql = "DELETE FROM users WHERE user_id = :user_id";
            $this->executeQuery($sql, [':user_id' => $userId]);
            return true;
        } catch (Exception $e) {
            error_log("UserDAO::hardDelete() Error: " . $e->getMessage());
            return false;
        }
    }

    public function usernameExists($username) {
        return $this->exists('username', $username);
    }

    public function emailExists($email) {
        return $this->exists('email', $email);
    }

    public function search($searchTerm) {
        try {
            $sql = "SELECT user_id, username, email, first_name, last_name, avatar_url,
                           created_at, is_active
                    FROM users
                    WHERE username LIKE :search OR email LIKE :search OR
                          first_name LIKE :search OR last_name LIKE :search
                    ORDER BY username";

            $stmt = $this->executeQuery($sql, [':search' => "%$searchTerm%"]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("UserDAO::search() Error: " . $e->getMessage());
            return [];
        }
    }
}
?>
