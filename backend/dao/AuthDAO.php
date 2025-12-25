<?php
require_once __DIR__ . '/BaseDAO.php';

class AuthDao extends BaseDao
{
    public function __construct()
    {
        parent::__construct();
        $this->tableName = 'users';
    }

    public function get_user_by_email($email)
    {
        $query = "SELECT email, password_hash, roles, user_id FROM " . $this->tableName . " WHERE email = :email";
        return $this->query_unique($query, ['email' => $email]);
    }

    public function get_user_by_username($username)
    {
        $query = "SELECT username, user_id FROM " . $this->tableName . " WHERE username = :username";
        return $this->query_unique($query, ['username' => $username]);
    }
}
