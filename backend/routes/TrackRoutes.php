<?php

require_once __DIR__ . '/../services/TrackService.php';

$trackService = new TrackService();

/**
 * @OA\Post(
 *     path="/tracks",
 *     tags={"tracks"},
 *     summary="Create a new track",
 *     security={
 *         {"bearerAuth": {}}
 *     },
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
    Flight::auth_middleware()->authorizeRoles([Roles::ADMIN]);
    $data = Flight::request()->data->getData();
    Flight::json($trackService->createTrack($data));
});

/**
 * @OA\Post(
 *     path="/tracks/bulk",
 *     tags={"tracks"},
 *     summary="Create multiple tracks",
 *     security={
 *         {"bearerAuth": {}}
 *     },
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"album_id", "tracks"},
 *             @OA\Property(property="album_id", type="integer", example=1),
 *             @OA\Property(property="tracks", type="array", @OA\Items(type="object"))
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Tracks created"
 *     )
 * )
 */
Flight::route('POST /tracks/bulk', function () use ($trackService) {
    Flight::auth_middleware()->authorizeRoles([Roles::ADMIN]);
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
 *         description="Track details"
 *     )
 * )
 */
Flight::route('GET /tracks/@id', function ($id) use ($trackService) {
    Flight::json($trackService->getTrack($id));
});

/**
 * @OA\Get(
 *     path="/tracks/album/{albumId}",
 *     tags={"tracks"},
 *     summary="Get tracks by album",
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
 *         description="Album tracks"
 *     )
 * )
 */
Flight::route('GET /tracks/album/@albumId', function ($albumId) use ($trackService) {
    Flight::json($trackService->getAlbumTracks($albumId));
});

/**
 * @OA\Put(
 *     path="/tracks/{id}",
 *     tags={"tracks"},
 *     summary="Update a track",
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
 *             @OA\Property(property="duration", type="string", example="4:20")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Track updated"
 *     )
 * )
 */
Flight::route('PUT /tracks/@id', function ($id) use ($trackService) {
    Flight::auth_middleware()->authorizeRoles([Roles::ADMIN]);
    $data = Flight::request()->data->getData();
    Flight::json($trackService->updateTrack($id, $data));
});

/**
 * @OA\Delete(
 *     path="/tracks/{id}",
 *     tags={"tracks"},
 *     summary="Delete a track",
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
 *         description="Track deleted"
 *     )
 * )
 */
Flight::route('DELETE /tracks/@id', function ($id) use ($trackService) {
    Flight::auth_middleware()->authorizeRoles([Roles::ADMIN]);
    Flight::json($trackService->deleteTrack($id));
});
