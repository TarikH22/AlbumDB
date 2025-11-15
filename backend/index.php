<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require './vendor/autoload.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/routes/AlbumRoutes.php';
require_once __DIR__ . '/routes/UserRoutes.php';
require_once __DIR__ . '/routes/RatingRoutes.php';
require_once __DIR__ . '/routes/ReviewRoutes.php';
require_once __DIR__ . '/routes/TrackRoutes.php';
require_once __DIR__ . '/routes/FavoriteRoutes.php';

Flight::route('/', function () {
    echo 'AlbumDB API is running!';
});


Flight::start();
