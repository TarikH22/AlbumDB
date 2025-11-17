<?php

require_once __DIR__ . '/../services/RatingService.php';

$ratingService = new RatingService();

/**
 * @OA\Post(
 *     path="/ratings",
 *     tags={"ratings"},
 *     summary="Create or update a rating",
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
    $data = Flight::request()->data->getData();
    Flight::json($ratingService->rateAlbum($data));
});

/**
 * @OA\Get(
 *     path="/ratings/{id}",
 *     tags={"ratings"},
 *     summary="Get rating by ID",
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
    Flight::json($ratingService->getRating($id));
});

Flight::route('GET /ratings/user/@userId', function ($userId) use ($ratingService) {
    Flight::json($ratingService->getUserRatings($userId));
});

Flight::route('GET /ratings/album/@albumId', function ($albumId) use ($ratingService) {
    Flight::json($ratingService->getAlbumRatings($albumId));
});

Flight::route('GET /ratings/recent', function () use ($ratingService) {
    $limit = Flight::request()->query['limit'] ?? 10;
    Flight::json($ratingService->getRecentRatings($limit));
});

Flight::route('PUT /ratings/@id', function ($id) use ($ratingService) {
    $data = Flight::request()->data->getData();
    Flight::json($ratingService->updateRating($id, $data));
});

/**
 * @OA\Delete(
 *     path="/ratings/{id}",
 *     tags={"ratings"},
 *     summary="Delete a rating",
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
    $userId = Flight::request()->query['user_id'] ?? null;
    Flight::json($ratingService->deleteRating($id, $userId));
});
