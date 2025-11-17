<?php

require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/AlbumDAO.php';
require_once __DIR__ . '/../dao/TrackDAO.php';

class AlbumService extends BaseService
{
    private $albumDAO;
    private $trackDAO;

    public function __construct()
    {
        $this->albumDAO = new AlbumDAO();
        $this->trackDAO = new TrackDAO();
    }

    public function createAlbum($data)
    {
        $validation = $this->validateRequiredFields($data, [
            'title',
            'artist',
            'genre',
            'year'
        ]);

        if (!$validation['valid']) {
            return $this->errorResponse($validation['errors']);
        }

        $errors = [];

        if (!$this->validateLength($data['title'], 1, 255)) {
            $errors[] = "Title must be between 1 and 255 characters";
        }

        if (!$this->validateLength($data['artist'], 1, 255)) {
            $errors[] = "Artist must be between 1 and 255 characters";
        }

        if (!$this->validateYear($data['year'])) {
            $errors[] = "Year must be between 1900 and " . (date('Y') + 1);
        }

        if (!empty($errors)) {
            return $this->errorResponse($errors);
        }

        $data['title'] = $this->sanitizeString($data['title']);
        $data['artist'] = $this->sanitizeString($data['artist']);
        $data['genre'] = $this->sanitizeString($data['genre']);

        if (isset($data['description'])) {
            $data['description'] = $this->sanitizeString($data['description']);
        }

        $albumId = $this->albumDAO->create($data);

        if (!$albumId) {
            $this->logError('AlbumService', 'createAlbum', 'Failed to create album');
            return $this->errorResponse('Failed to create album', 500);
        }

        return $this->successResponse(
            ['album_id' => $albumId],
            'Album created successfully'
        );
    }

    public function getAlbum($albumId)
    {
        $album = $this->albumDAO->getByIdWithStats($albumId);

        if (!$album) {
            return $this->errorResponse('Album not found', 404);
        }

        $album['tracks'] = $this->trackDAO->getByAlbumId($albumId);

        return $this->successResponse($album);
    }

    public function getAllAlbums($limit = null, $offset = 0)
    {
        $albums = $this->albumDAO->getAllWithStats($limit, $offset);

        return $this->successResponse([
            'albums' => $albums,
            'count' => count($albums)
        ]);
    }

    public function searchAlbums($searchTerm)
    {
        if (empty($searchTerm) || strlen(trim($searchTerm)) < 2) {
            return $this->errorResponse('Search term must be at least 2 characters');
        }

        $albums = $this->albumDAO->search($this->sanitizeString($searchTerm));

        return $this->successResponse([
            'albums' => $albums,
            'count' => count($albums)
        ]);
    }

    public function getTopRatedAlbums($limit = 10)
    {
        if ($limit < 1 || $limit > 100) {
            $limit = 10;
        }

        $albums = $this->albumDAO->getTopRated($limit);

        return $this->successResponse([
            'albums' => $albums,
            'count' => count($albums)
        ]);
    }

    public function getRecentAlbums($limit = 10)
    {
        if ($limit < 1 || $limit > 100) {
            $limit = 10;
        }

        $albums = $this->albumDAO->getRecent($limit);

        return $this->successResponse([
            'albums' => $albums,
            'count' => count($albums)
        ]);
    }

    public function getAlbumsByGenre($genre)
    {
        $albums = $this->albumDAO->getByGenre($this->sanitizeString($genre));

        return $this->successResponse([
            'albums' => $albums,
            'count' => count($albums)
        ]);
    }

    public function getAlbumsByArtist($artist)
    {
        $albums = $this->albumDAO->getByArtist($this->sanitizeString($artist));

        return $this->successResponse([
            'albums' => $albums,
            'count' => count($albums)
        ]);
    }

    public function getAllGenres()
    {
        $genres = $this->albumDAO->getAllGenres();

        return $this->successResponse($genres);
    }

    public function getAllArtists()
    {
        $artists = $this->albumDAO->getAllArtists();

        return $this->successResponse($artists);
    }

    public function updateAlbum($albumId, $data)
    {
        $album = $this->albumDAO->getById($albumId);
        if (!$album) {
            return $this->errorResponse('Album not found', 404);
        }

        $errors = [];

        if (isset($data['year']) && !$this->validateYear($data['year'])) {
            $errors[] = "Year must be between 1900 and " . (date('Y') + 1);
        }

        if (isset($data['title']) && !$this->validateLength($data['title'], 1, 255)) {
            $errors[] = "Title must be between 1 and 255 characters";
        }

        if (isset($data['artist']) && !$this->validateLength($data['artist'], 1, 255)) {
            $errors[] = "Artist must be between 1 and 255 characters";
        }

        if (!empty($errors)) {
            return $this->errorResponse($errors);
        }

        foreach (['title', 'artist', 'genre', 'label', 'description'] as $field) {
            if (isset($data[$field])) {
                $data[$field] = $this->sanitizeString($data[$field]);
            }
        }

        $result = $this->albumDAO->update($albumId, $data);

        if (!$result) {
            $this->logError('AlbumService', 'updateAlbum', 'Failed to update album');
            return $this->errorResponse('Failed to update album', 500);
        }

        return $this->successResponse(null, 'Album updated successfully');
    }

    public function deleteAlbum($albumId)
    {
        $album = $this->albumDAO->getById($albumId);

        if (!$album) {
            return $this->errorResponse('Album not found', 404);
        }

        $result = $this->albumDAO->delete($albumId);

        if (!$result) {
            $this->logError('AlbumService', 'deleteAlbum', 'Failed to delete album');
            return $this->errorResponse('Failed to delete album', 500);
        }

        return $this->successResponse(null, 'Album deleted successfully');
    }
}
