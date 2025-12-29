<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthMiddleware
{

    public function verifyToken($token)
    {
        if (!$token)
            Flight::halt(401, "Missing authentication header");


        $decoded_token = JWT::decode($token, new Key(Config::JWT_SECRET(), 'HS256'));

        Flight::set('user', $decoded_token->user);
        Flight::set('jwt_token', $token);
        return TRUE;
    }

    public function authorizeRole($requiredRole)
    {
        $user = Flight::get('user');
        if ($user->role !== $requiredRole) {
            Flight::halt(403, 'Access denied: insufficient privileges');
        }
    }

    public function authorizeRoles($roles)
    {
        $user = Flight::get('user');

        // If user is not set, verify token first
        if (!$user) {
            // Try multiple methods to get the Authorization header
            $authHeader = null;
            
            // Method 1: Check $_SERVER
            if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
                $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
            } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
                $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
            } elseif (function_exists('apache_request_headers')) {
                $headers = apache_request_headers();
                $headers = array_change_key_case($headers, CASE_LOWER);
                $authHeader = $headers['authorization'] ?? null;
            } elseif (function_exists('getallheaders')) {
                $headers = getallheaders();
                $headers = array_change_key_case($headers, CASE_LOWER);
                $authHeader = $headers['authorization'] ?? null;
            }

            if (!$authHeader) {
                Flight::halt(401, 'Missing authentication token');
            }

            $token = str_replace('Bearer ', '', $authHeader);
            $this->verifyToken($token);
            $user = Flight::get('user');
        }

        if (!$user || !in_array($user->roles, $roles)) {
            Flight::halt(403, 'Forbidden: role not allowed');
        }
    }

    function authorizePermission($permission)
    {
        $user = Flight::get('user');
        if (!in_array($permission, $user->permissions)) {
            Flight::halt(403, 'Access denied: permission missing');
        }
    }
}
