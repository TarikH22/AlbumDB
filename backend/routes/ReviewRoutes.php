<?php

require_once __DIR__ . '/../services/ReviewService.php';

$reviewService = new ReviewService();

/**
 * @OA\Post(
 *     path="/reviews",
 *     tags={"reviews"},
 *     summary="Create a new review",
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
    $data = Flight::request()->data->getData();
    Flight::json($reviewService->createReview($data));
});

/**
 * @OA\Get(
 *     path="/reviews/{id}",
 *     tags={"reviews"},
 *     summary="Get review by ID",
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
    Flight::json($reviewService->getReview($id));
});

Flight::route('GET /reviews/user/@userId', function ($userId) use ($reviewService) {
    Flight::json($reviewService->getUserReviews($userId));
});

Flight::route('GET /reviews/album/@albumId', function ($albumId) use ($reviewService) {
    $orderBy = Flight::request()->query['order_by'] ?? 'created_at';
    Flight::json($reviewService->getAlbumReviews($albumId, $orderBy));
});

Flight::route('GET /reviews/recent', function () use ($reviewService) {
    $limit = Flight::request()->query['limit'] ?? 10;
    Flight::json($reviewService->getRecentReviews($limit));
});

Flight::route('PUT /reviews/@id', function ($id) use ($reviewService) {
    $data = Flight::request()->data->getData();
    $userId = Flight::request()->query['user_id'] ?? null;
    Flight::json($reviewService->updateReview($id, $data, $userId));
});

/**
 * @OA\Delete(
 *     path="/reviews/{id}",
 *     tags={"reviews"},
 *     summary="Delete a review",
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
    $userId = Flight::request()->query['user_id'] ?? null;
    Flight::json($reviewService->deleteReview($id, $userId));
});
