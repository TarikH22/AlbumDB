<?php

require './vendor/autoload.php';
require __DIR__ . '/config.php';

require __DIR__ . '/services/AuthService.php';
require __DIR__ . '/MiddleWare/AuthMiddleware.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

Flight::before('error', function () {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET,PUT,POST,DELETE');
    header('Access-Control-Allow-Headers: Content-Type');
});

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}


Flight::register('auth_service', 'AuthService');
Flight::register('auth_middleware', "AuthMiddleware");

//globalni middleware za provjeru JWT tokena applied to all routes, calls authmiddleware::verifyToken
Flight::route('/*', function () {
    $url = Flight::request()->url;
    error_log("Requested URL: " . $url);
    if (
        strpos($url, '/auth/login') === 0 ||
        strpos($url, '/auth/register') === 0 ||
        strpos($url, '/albums') === 0 ||
        strpos($url, '/tracks/album') === 0 ||
        strpos($url, '/reviews/recent') === 0 ||
        strpos($url, '/reviews/album') === 0 ||
        strpos($url, '/ratings/album') === 0
    ) {
        return TRUE;
    } else {
        try {
            // Try multiple methods to get the Authorization header
            $authHeader = null;
            
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

            // DEBUG: check raw header
            error_log("Authorization Header: " . var_export($authHeader, true));

            // Extract token using regex
            if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
                Flight::halt(401, "Missing or malformed Authorization header");
            }

            $token = $matches[1];
            Flight::auth_middleware()->verifyToken($token);
            return TRUE;
        } catch (\Exception $e) {
            Flight::halt(401, $e->getMessage());
        }
    }
});

require_once __DIR__ . '/routes/AlbumRoutes.php';
require_once __DIR__ . '/routes/UserRoutes.php';
require_once __DIR__ . '/routes/RatingRoutes.php';
require_once __DIR__ . '/routes/ReviewRoutes.php';
require_once __DIR__ . '/routes/TrackRoutes.php';
require_once __DIR__ . '/routes/FavoriteRoutes.php';
require_once __DIR__ . '/routes/AuthRoutes.php';


Flight::start();
