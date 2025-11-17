<?php

require_once __DIR__ . '/../services/FavoriteService.php';

$favoriteService = new FavoriteService();

/**
 * @OA\Post(
 *     path="/favorites",
 *     tags={"favorites"},
 *     summary="Add album to favorites",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"user_id", "album_id"},
 *             @OA\Property(property="user_id", type="integer", example=1),
 *             @OA\Property(property="album_id", type="integer", example=1)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Favorite added"
 *     )
 * )
 */
Flight::route('POST /favorites', function () use ($favoriteService) {
    $data = Flight::request()->data->getData();
    Flight::json($favoriteService->addFavorite($data));
});

Flight::route('POST /favorites/toggle', function () use ($favoriteService) {
    $data = Flight::request()->data->getData();
    $userId = $data['user_id'] ?? null;
    $albumId = $data['album_id'] ?? null;

    if (!$userId || !$albumId) {
        Flight::json(['success' => false, 'errors' => ['User ID and Album ID are required']], 400);
        return;
    }

    Flight::json($favoriteService->toggleFavorite($userId, $albumId));
});

/**
 * @OA\Get(
 *     path="/favorites/user/{userId}",
 *     tags={"favorites"},
 *     summary="Get user favorites",
 *     @OA\Parameter(
 *         name="userId",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="User favorites"
 *     )
 * )
 */
Flight::route('GET /favorites/user/@userId', function ($userId) use ($favoriteService) {
    Flight::json($favoriteService->getUserFavorites($userId));
});

Flight::route('GET /favorites/album/@albumId', function ($albumId) use ($favoriteService) {
    Flight::json($favoriteService->getAlbumFavorites($albumId));
});

Flight::route('GET /favorites/most-favorited', function () use ($favoriteService) {
    $limit = Flight::request()->query['limit'] ?? 10;
    Flight::json($favoriteService->getMostFavorited($limit));
});

/**
 * @OA\Delete(
 *     path="/favorites/user/{userId}/album/{albumId}",
 *     tags={"favorites"},
 *     summary="Remove from favorites",
 *     @OA\Parameter(
 *         name="userId",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Parameter(
 *         name="albumId",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Favorite removed"
 *     )
 * )
 */
Flight::route('DELETE /favorites/user/@userId/album/@albumId', function ($userId, $albumId) use ($favoriteService) {
    Flight::json($favoriteService->removeFavorite($userId, $albumId));
});
