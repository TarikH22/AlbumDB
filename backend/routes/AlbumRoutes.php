<?php

require_once __DIR__ . '/../services/AlbumService.php';

$albumService = new AlbumService();

/**
 * @OA\Get(
 *      path="/albums",
 *      tags={"albums"},
 *      summary="Get all albums",
 *      @OA\Parameter(
 *          name="limit",
 *          in="query",
 *          required=false,
 *          @OA\Schema(type="integer"),
 *          description="Number of results to return"
 *      ),
 *      @OA\Parameter(
 *          name="offset",
 *          in="query",
 *          required=false,
 *          @OA\Schema(type="integer"),
 *          description="Offset for pagination"
 *      ),
 *      @OA\Response(
 *           response=200,
 *           description="Array of all albums in the database"
 *      )
 * )
 */
Flight::route('GET /albums', function () use ($albumService) {
    $limit = Flight::request()->query['limit'] ?? null;
    $offset = Flight::request()->query['offset'] ?? 0;
    Flight::json($albumService->getAllAlbums($limit, $offset));
});

/**
 * @OA\Get(
 *     path="/albums/{id}",
 *     tags={"albums"},
 *     summary="Get album by ID",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the album",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Returns the album with the given ID"
 *     )
 * )
 */
Flight::route('GET /albums/@id', function ($id) use ($albumService) {
    Flight::json($albumService->getAlbum($id));
});

/**
 * @OA\Post(
 *     path="/albums",
 *     tags={"albums"},
 *     summary="Add a new album",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"title", "artist", "genre", "year"},
 *             @OA\Property(property="title", type="string", example="Dark Side of the Moon"),
 *             @OA\Property(property="artist", type="string", example="Pink Floyd"),
 *             @OA\Property(property="genre", type="string", example="Progressive Rock"),
 *             @OA\Property(property="year", type="integer", example=1973)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="New album created"
 *     )
 * )
 */
Flight::route('POST /albums', function () use ($albumService) {
    $data = Flight::request()->data->getData();
    Flight::json($albumService->createAlbum($data));
});

/**
 * @OA\Put(
 *     path="/albums/{id}",
 *     tags={"albums"},
 *     summary="Update an existing album by ID",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Album ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="title", type="string", example="Updated Title"),
 *             @OA\Property(property="artist", type="string", example="Updated Artist"),
 *             @OA\Property(property="genre", type="string", example="Rock"),
 *             @OA\Property(property="year", type="integer", example=1975)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Album updated"
 *     )
 * )
 */
Flight::route('PUT /albums/@id', function ($id) use ($albumService) {
    $data = Flight::request()->data->getData();
    Flight::json($albumService->updateAlbum($id, $data));
});

/**
 * @OA\Delete(
 *     path="/albums/{id}",
 *     tags={"albums"},
 *     summary="Delete an album by ID",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Album ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Album deleted"
 *     )
 * )
 */
Flight::route('DELETE /albums/@id', function ($id) use ($albumService) {
    Flight::json($albumService->deleteAlbum($id));
});

/**
 * @OA\Get(
 *     path="/albums/search",
 *     tags={"albums"},
 *     summary="Search albums",
 *     @OA\Parameter(
 *         name="q",
 *         in="query",
 *         required=true,
 *         description="Search term",
 *         @OA\Schema(type="string", example="pink")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Search results"
 *     )
 * )
 */
Flight::route('GET /albums/search', function () use ($albumService) {
    $searchTerm = Flight::request()->query['q'] ?? '';
    Flight::json($albumService->searchAlbums($searchTerm));
});

/**
 * @OA\Get(
 *     path="/albums/top-rated",
 *     tags={"albums"},
 *     summary="Get top rated albums",
 *     @OA\Parameter(
 *         name="limit",
 *         in="query",
 *         required=false,
 *         description="Number of results",
 *         @OA\Schema(type="integer", example=10)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Top rated albums"
 *     )
 * )
 */
Flight::route('GET /albums/top-rated', function () use ($albumService) {
    $limit = Flight::request()->query['limit'] ?? 10;
    Flight::json($albumService->getTopRatedAlbums($limit));
});

Flight::route('GET /albums/recent', function () use ($albumService) {
    $limit = Flight::request()->query['limit'] ?? 10;
    Flight::json($albumService->getRecentAlbums($limit));
});

Flight::route('GET /albums/genre/@genre', function ($genre) use ($albumService) {
    Flight::json($albumService->getAlbumsByGenre($genre));
});

Flight::route('GET /albums/artist/@artist', function ($artist) use ($albumService) {
    Flight::json($albumService->getAlbumsByArtist($artist));
});

Flight::route('GET /genres', function () use ($albumService) {
    Flight::json($albumService->getAllGenres());
});

Flight::route('GET /artists', function () use ($albumService) {
    Flight::json($albumService->getAllArtists());
});
