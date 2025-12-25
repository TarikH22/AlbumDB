<?php
require_once __DIR__ . "/BaseService.php";
require_once __DIR__ . '/../dao/AuthDAO.php';
require_once __DIR__ . "/../utils/Roles.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthService extends BaseService
{
    private $auth_dao;
    public function __construct()
    {
        $this->auth_dao = new AuthDao();
        $this->dao = $this->auth_dao;
    }

    public function get_user_by_email($email)
    {
        return $this->auth_dao->get_user_by_email($email);
    }
    public function register($entity)
    {
        // Validate required fields
        if (empty($entity['email']) || empty($entity['password'])) {
            return ['success' => false, 'error' => 'Email and password are required.'];
        }

        if (empty($entity['username'])) {
            return ['success' => false, 'error' => 'Username is required.'];
        }

        if (empty($entity['first_name']) || empty($entity['last_name'])) {
            return ['success' => false, 'error' => 'First name and last name are required.'];
        }

        // Check if email already exists
        $email_exists = $this->auth_dao->get_user_by_email($entity['email']);
        if ($email_exists) {
            return ['success' => false, 'error' => 'Email already registered.'];
        }

        // Check if username already exists
        $username_exists = $this->auth_dao->get_user_by_username($entity['username']);
        if ($username_exists) {
            return ['success' => false, 'error' => 'Username already taken. Please choose another.'];
        }

        try {
            $entity['password_hash'] = password_hash($entity['password'], PASSWORD_BCRYPT);
            unset($entity['password']);
            
            // Check if email ends with @admin.ba and assign admin role
            if (str_ends_with(strtolower($entity['email']), '@admin.ba')) {
                $entity['roles'] = Roles::ADMIN;
            } else {
                $entity['roles'] = Roles::USER;
            }
            
            $entity = parent::add($entity);

            unset($entity['password_hash']);

            return ['success' => true, 'data' => $entity];
        } catch (PDOException $e) {
            // Handle duplicate entry error
            if ($e->getCode() == 23000) {
                return ['success' => false, 'error' => 'Email or username already registered. Please use different values.'];
            }
            // Log the actual error for debugging
            error_log("Registration PDOException: " . $e->getMessage());
            return ['success' => false, 'error' => 'Registration failed. Please try again.'];
        } catch (Exception $e) {
            error_log("Registration Exception: " . $e->getMessage());
            return ['success' => false, 'error' => 'Registration failed. Please try again.'];
        }
    }

    public function login($entity)
    {
        if (empty($entity['email']) || empty($entity['password'])) {
            return ['success' => false, 'error' => 'Email and password are required.'];
        }

        $user = $this->auth_dao->get_user_by_email($entity['email']);

        if (!$user || !password_verify($entity['password'], $user['password_hash'])) {
            return ['success' => false, 'error' => 'Invalid username or password.'];
        }

        unset($user['password_hash']);

        $jwt_payload = [
            'user' => $user,
            'iat' => time(),
            'exp' => time() + (60 * 60 * 24), // valid for day,
            'roles' => $user['roles']
        ];

        $token = JWT::encode(
            $jwt_payload,
            Config::JWT_SECRET(),
            'HS256'
        );

        return ['success' => true, 'data' => array_merge($user, ['token' => $token])];
    }
}
