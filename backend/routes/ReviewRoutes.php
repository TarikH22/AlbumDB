<?php

require_once __DIR__ . '/../services/ReviewService.php';

$reviewService = new ReviewService();

/**
 * @OA\Post(
 *     path="/reviews",
 *     tags={"reviews"},
 *     summary="Create a new review",
 *     security={
 *         {"bearerAuth": {}}
 *     },
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"user_id", "album_id", "title", "review_text"},
 *             @OA\Property(property="user_id", type="integer", example=1),
 *             @OA\Property(property="album_id", type="integer", example=1),
 *             @OA\Property(property="title", type="string", example="Amazing Album!"),
 *             @OA\Property(property="review_text", type="string", example="This album is fantastic...")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Review created"
 *     )
 * )
 */
Flight::route('POST /reviews', function () use ($reviewService) {
    Flight::auth_middleware()->authorizeRoles([Roles::USER, Roles::ADMIN]);
    $data = Flight::request()->data->getData();
    Flight::json($reviewService->createReview($data));
});

/**
 * @OA\Get(
 *     path="/reviews/recent",
 *     tags={"reviews"},
 *     summary="Get recent reviews",
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
 *         description="Recent reviews"
 *     )
 * )
 */
Flight::route('GET /reviews/recent', function () use ($reviewService) {
    // Public endpoint - no authentication required
    $limit = Flight::request()->query['limit'] ?? 10;
    Flight::json($reviewService->getRecentReviews($limit));
});

/**
 * @OA\Get(
 *     path="/reviews/{id}",
 *     tags={"reviews"},
 *     summary="Get review by ID",
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
 *         description="Review details"
 *     )
 * )
 */
Flight::route('GET /reviews/@id', function ($id) use ($reviewService) {
    Flight::auth_middleware()->authorizeRoles([Roles::USER, Roles::ADMIN]);
    Flight::json($reviewService->getReview($id));
});

/**
 * @OA\Get(
 *     path="/reviews/user/{userId}",
 *     tags={"reviews"},
 *     summary="Get user reviews",
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
 *         description="User reviews"
 *     )
 * )
 */
Flight::route('GET /reviews/user/@userId', function ($userId) use ($reviewService) {
    Flight::auth_middleware()->authorizeRoles([Roles::USER, Roles::ADMIN]);
    Flight::json($reviewService->getUserReviews($userId));
});

/**
 * @OA\Get(
 *     path="/reviews/album/{albumId}",
 *     tags={"reviews"},
 *     summary="Get album reviews",
 *     security={
 *         {"bearerAuth": {}}
 *     },
 *     @OA\Parameter(
 *         name="albumId",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Parameter(
 *         name="order_by",
 *         in="query",
 *         required=false,
 *         @OA\Schema(type="string", example="created_at")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Album reviews"
 *     )
 * )
 */
Flight::route('GET /reviews/album/@albumId', function ($albumId) use ($reviewService) {
    // Public endpoint - no authentication required
    $orderBy = Flight::request()->query['order_by'] ?? 'created_at';
    Flight::json($reviewService->getAlbumReviews($albumId, $orderBy));
});

/**
 * @OA\Put(
 *     path="/reviews/{id}",
 *     tags={"reviews"},
 *     summary="Update a review",
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
 *             @OA\Property(property="title", type="string", example="Updated Title"),
 *             @OA\Property(property="review_text", type="string", example="Updated review text...")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Review updated"
 *     )
 * )
 */
Flight::route('PUT /reviews/@id', function ($id) use ($reviewService) {
    Flight::auth_middleware()->authorizeRoles([Roles::USER, Roles::ADMIN]);
    $data = Flight::request()->data->getData();
    $userId = Flight::request()->query['user_id'] ?? null;
    Flight::json($reviewService->updateReview($id, $data, $userId));
});

/**
 * @OA\Delete(
 *     path="/reviews/{id}",
 *     tags={"reviews"},
 *     summary="Delete a review",
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
 *         description="Review deleted"
 *     )
 * )
 */
Flight::route('DELETE /reviews/@id', function ($id) use ($reviewService) {
    Flight::auth_middleware()->authorizeRoles([Roles::USER, Roles::ADMIN]);
    $userId = Flight::request()->query['user_id'] ?? null;
    Flight::json($reviewService->deleteReview($id, $userId));
});
