<?php

require_once __DIR__ . '/../services/FavoriteService.php';

$favoriteService = new FavoriteService();

/**
 * @OA\Post(
 *     path="/favorites",
 *     tags={"favorites"},
 *     summary="Add album to favorites",
 *     security={
 *         {"bearerAuth": {}}
 *     },
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
    Flight::auth_middleware()->authorizeRoles([Roles::USER, Roles::ADMIN]);
    $data = Flight::request()->data->getData();
    Flight::json($favoriteService->addFavorite($data));
});

/**
 * @OA\Post(
 *     path="/favorites/toggle",
 *     tags={"favorites"},
 *     summary="Toggle favorite status",
 *     security={
 *         {"bearerAuth": {}}
 *     },
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
 *         description="Favorite toggled"
 *     )
 * )
 */
Flight::route('POST /favorites/toggle', function () use ($favoriteService) {
    Flight::auth_middleware()->authorizeRoles([Roles::USER, Roles::ADMIN]);
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
 *     path="/favorites/user/{userId}/album/{albumId}",
 *     tags={"favorites"},
 *     summary="Check if user has favorited an album",
 *     security={
 *         {"bearerAuth": {}}
 *     },
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
 *         description="Favorite status"
 *     )
 * )
 */
Flight::route('GET /favorites/user/@userId/album/@albumId', function ($userId, $albumId) use ($favoriteService) {
    Flight::auth_middleware()->authorizeRoles([Roles::USER, Roles::ADMIN]);
    Flight::json($favoriteService->isFavorited($userId, $albumId));
});

/**
 * @OA\Get(
 *     path="/favorites/user/{userId}",
 *     tags={"favorites"},
 *     summary="Get user favorites",
 *     security={
 *         {"bearerAuth": {}}
 *     },
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
    Flight::auth_middleware()->authorizeRoles([Roles::USER, Roles::ADMIN]);
    Flight::json($favoriteService->getUserFavorites($userId));
});

/**
 * @OA\Get(
 *     path="/favorites/album/{albumId}",
 *     tags={"favorites"},
 *     summary="Get album favorites count",
 *     security={
 *         {"bearerAuth": {}}
 *     },
 *     @OA\Parameter(
 *         name="albumId",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Album favorites"
 *     )
 * )
 */
Flight::route('GET /favorites/album/@albumId', function ($albumId) use ($favoriteService) {
    Flight::auth_middleware()->authorizeRoles([Roles::USER, Roles::ADMIN]);
    Flight::json($favoriteService->getAlbumFavorites($albumId));
});

/**
 * @OA\Get(
 *     path="/favorites/most-favorited",
 *     tags={"favorites"},
 *     summary="Get most favorited albums",
 *     security={
 *         {"bearerAuth": {}}
 *     },
 *     @OA\Parameter(
 *         name="limit",
 *         in="query",
 *         required=false,
 *         @OA\Schema(type="integer", example=10)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Most favorited albums"
 *     )
 * )
 */
Flight::route('GET /favorites/most-favorited', function () use ($favoriteService) {
    Flight::auth_middleware()->authorizeRoles([Roles::USER, Roles::ADMIN]);
    $limit = Flight::request()->query['limit'] ?? 10;
    Flight::json($favoriteService->getMostFavorited($limit));
});

/**
 * @OA\Delete(
 *     path="/favorites/user/{userId}/album/{albumId}",
 *     tags={"favorites"},
 *     summary="Remove from favorites",
 *     security={
 *         {"bearerAuth": {}}
 *     },
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
    Flight::auth_middleware()->authorizeRoles([Roles::USER, Roles::ADMIN]);
    Flight::json($favoriteService->removeFavorite($userId, $albumId));
});
