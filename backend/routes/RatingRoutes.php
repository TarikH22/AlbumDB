<?php

require_once __DIR__ . '/../services/RatingService.php';

$ratingService = new RatingService();

/**
 * @OA\Post(
 *     path="/ratings",
 *     tags={"ratings"},
 *     summary="Create or update a rating",
 *     security={
 *         {"bearerAuth": {}}
 *     },
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"user_id", "album_id", "rating"},
 *             @OA\Property(property="user_id", type="integer", example=1),
 *             @OA\Property(property="album_id", type="integer", example=1),
 *             @OA\Property(property="rating", type="number", example=8.5)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Rating saved"
 *     )
 * )
 */
Flight::route('POST /ratings', function () use ($ratingService) {
    Flight::auth_middleware()->authorizeRoles([Roles::USER, Roles::ADMIN]);
    $data = Flight::request()->data->getData();
    Flight::json($ratingService->rateAlbum($data));
});

/**
 * @OA\Get(
 *     path="/ratings/{id}",
 *     tags={"ratings"},
 *     summary="Get rating by ID",
 *     security={
 *         {"bearerAuth": {}}
 *     },
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Rating details"
 *     )
 * )
 */
Flight::route('GET /ratings/@id', function ($id) use ($ratingService) {
    Flight::auth_middleware()->authorizeRoles([Roles::USER, Roles::ADMIN]);
    Flight::json($ratingService->getRating($id));
});

/**
 * @OA\Get(
 *     path="/ratings/user/{userId}",
 *     tags={"ratings"},
 *     summary="Get user ratings",
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
 *         description="User ratings"
 *     )
 * )
 */
Flight::route('GET /ratings/user/@userId', function ($userId) use ($ratingService) {
    Flight::auth_middleware()->authorizeRoles([Roles::USER, Roles::ADMIN]);
    Flight::json($ratingService->getUserRatings($userId));
});

/**
 * @OA\Get(
 *     path="/ratings/album/{albumId}",
 *     tags={"ratings"},
 *     summary="Get album ratings",
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
 *         description="Album ratings"
 *     )
 * )
 */
Flight::route('GET /ratings/album/@albumId', function ($albumId) use ($ratingService) {
    // Public endpoint - no authentication required
    Flight::json($ratingService->getAlbumRatings($albumId));
});

/**
 * @OA\Get(
 *     path="/ratings/recent",
 *     tags={"ratings"},
 *     summary="Get recent ratings",
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
 *         description="Recent ratings"
 *     )
 * )
 */
Flight::route('GET /ratings/recent', function () use ($ratingService) {
    Flight::auth_middleware()->authorizeRoles([Roles::USER, Roles::ADMIN]);
    $limit = Flight::request()->query['limit'] ?? 10;
    Flight::json($ratingService->getRecentRatings($limit));
});

/**
 * @OA\Put(
 *     path="/ratings/{id}",
 *     tags={"ratings"},
 *     summary="Update a rating",
 *     security={
 *         {"bearerAuth": {}}
 *     },
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="rating", type="number", example=9.0)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Rating updated"
 *     )
 * )
 */
Flight::route('PUT /ratings/@id', function ($id) use ($ratingService) {
    Flight::auth_middleware()->authorizeRoles([Roles::USER, Roles::ADMIN]);
    $data = Flight::request()->data->getData();
    Flight::json($ratingService->updateRating($id, $data));
});

/**
 * @OA\Get(
 *     path="/ratings/user/{userId}/album/{albumId}",
 *     tags={"ratings"},
 *     summary="Get user's rating for a specific album",
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
 *         description="User's album rating"
 *     )
 * )
 */
Flight::route('GET /ratings/user/@userId/album/@albumId', function ($userId, $albumId) use ($ratingService) {
    Flight::auth_middleware()->authorizeRoles([Roles::USER, Roles::ADMIN]);
    Flight::json($ratingService->getUserRatingForAlbum($userId, $albumId));
});

/**
 * @OA\Delete(
 *     path="/ratings/{id}",
 *     tags={"ratings"},
 *     summary="Delete a rating",
 *     security={
 *         {"bearerAuth": {}}
 *     },
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Rating deleted"
 *     )
 * )
 */
Flight::route('DELETE /ratings/@id', function ($id) use ($ratingService) {
    Flight::auth_middleware()->authorizeRoles([Roles::USER, Roles::ADMIN]);
    $userId = Flight::request()->query['user_id'] ?? null;
    Flight::json($ratingService->deleteRating($id, $userId));
});
