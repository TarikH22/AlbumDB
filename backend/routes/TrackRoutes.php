<?php

require_once __DIR__ . '/../services/TrackService.php';

$trackService = new TrackService();

/**
 * @OA\Post(
 *     path="/tracks",
 *     tags={"tracks"},
 *     summary="Create a new track",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"album_id", "track_number", "title", "duration"},
 *             @OA\Property(property="album_id", type="integer", example=1),
 *             @OA\Property(property="track_number", type="integer", example=1),
 *             @OA\Property(property="title", type="string", example="Speak to Me"),
 *             @OA\Property(property="duration", type="string", example="3:45")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Track created"
 *     )
 * )
 */
Flight::route('POST /tracks', function () use ($trackService) {
    $data = Flight::request()->data->getData();
    Flight::json($trackService->createTrack($data));
});

Flight::route('POST /tracks/bulk', function () use ($trackService) {
    $data = Flight::request()->data->getData();
    $albumId = $data['album_id'] ?? null;
    $tracks = $data['tracks'] ?? [];

    if (!$albumId) {
        Flight::json(['success' => false, 'errors' => ['Album ID is required']], 400);
        return;
    }

    Flight::json($trackService->createMultipleTracks($albumId, $tracks));
});

/**
 * @OA\Get(
 *     path="/tracks/{id}",
 *     tags={"tracks"},
 *     summary="Get track by ID",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Track details"
 *     )
 * )
 */
Flight::route('GET /tracks/@id', function ($id) use ($trackService) {
    Flight::json($trackService->getTrack($id));
});

Flight::route('GET /tracks/album/@albumId', function ($albumId) use ($trackService) {
    Flight::json($trackService->getAlbumTracks($albumId));
});

Flight::route('PUT /tracks/@id', function ($id) use ($trackService) {
    $data = Flight::request()->data->getData();
    Flight::json($trackService->updateTrack($id, $data));
});

/**
 * @OA\Delete(
 *     path="/tracks/{id}",
 *     tags={"tracks"},
 *     summary="Delete a track",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Track deleted"
 *     )
 * )
 */
Flight::route('DELETE /tracks/@id', function ($id) use ($trackService) {
    Flight::json($trackService->deleteTrack($id));
});
