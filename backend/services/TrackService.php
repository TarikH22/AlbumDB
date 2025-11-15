<?php

require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/TrackDAO.php';
require_once __DIR__ . '/../dao/AlbumDAO.php';

class TrackService extends BaseService
{
    private $trackDAO;
    private $albumDAO;

    public function __construct()
    {
        $this->trackDAO = new TrackDAO();
        $this->albumDAO = new AlbumDAO();
    }

    public function createTrack($data)
    {
        $validation = $this->validateRequiredFields($data, [
            'album_id',
            'track_number',
            'title',
            'duration'
        ]);

        if (!$validation['valid']) {
            return $this->errorResponse($validation['errors']);
        }

        $errors = [];

        if (!$this->validateLength($data['title'], 1, 255)) {
            $errors[] = "Title must be between 1 and 255 characters";
        }

        if (!is_numeric($data['track_number']) || $data['track_number'] < 1) {
            $errors[] = "Track number must be a positive integer";
        }

        if (!preg_match('/^\d{1,3}:[0-5][0-9]$/', $data['duration'])) {
            $errors[] = "Duration must be in format MM:SS (e.g., 3:45)";
        }

        if (!empty($errors)) {
            return $this->errorResponse($errors);
        }

        $album = $this->albumDAO->getById($data['album_id']);
        if (!$album) {
            return $this->errorResponse('Album not found', 404);
        }

        $data['title'] = $this->sanitizeString($data['title']);

        $trackId = $this->trackDAO->create($data);

        if (!$trackId) {
            $this->logError('TrackService', 'createTrack', 'Failed to create track');
            return $this->errorResponse('Failed to create track', 500);
        }

        return $this->successResponse([
            'track_id' => $trackId
        ], 'Track created successfully');
    }

    public function createMultipleTracks($albumId, $tracks)
    {
        $album = $this->albumDAO->getById($albumId);
        if (!$album) {
            return $this->errorResponse('Album not found', 404);
        }

        if (empty($tracks) || !is_array($tracks)) {
            return $this->errorResponse('Tracks array is required');
        }

        $errors = [];
        foreach ($tracks as $index => $track) {
            $trackNum = $index + 1;

            if (!isset($track['title']) || empty(trim($track['title']))) {
                $errors[] = "Track $trackNum: Title is required";
            }

            if (!isset($track['duration']) || !preg_match('/^\d{1,3}:[0-5][0-9]$/', $track['duration'])) {
                $errors[] = "Track $trackNum: Duration must be in format MM:SS";
            }
        }

        if (!empty($errors)) {
            return $this->errorResponse($errors);
        }

        foreach ($tracks as $index => &$track) {
            if (!isset($track['track_number'])) {
                $track['track_number'] = $index + 1;
            }
            $track['title'] = $this->sanitizeString($track['title']);
        }

        $result = $this->trackDAO->createMultiple($albumId, $tracks);

        if (!$result) {
            $this->logError('TrackService', 'createMultipleTracks', 'Failed to create tracks');
            return $this->errorResponse('Failed to create tracks', 500);
        }

        return $this->successResponse(null, 'Tracks created successfully');
    }

    public function getTrack($trackId)
    {
        $track = $this->trackDAO->getByIdWithAlbum($trackId);

        if (!$track) {
            return $this->errorResponse('Track not found', 404);
        }

        return $this->successResponse($track);
    }

    public function getAlbumTracks($albumId)
    {
        $album = $this->albumDAO->getById($albumId);
        if (!$album) {
            return $this->errorResponse('Album not found', 404);
        }

        $tracks = $this->trackDAO->getByAlbumId($albumId);
        $totalDuration = $this->trackDAO->getTotalDuration($albumId);

        return $this->successResponse([
            'tracks' => $tracks,
            'count' => count($tracks),
            'total_duration' => $totalDuration
        ]);
    }

    public function searchTracks($searchTerm)
    {
        if (empty($searchTerm) || strlen(trim($searchTerm)) < 2) {
            return $this->errorResponse('Search term must be at least 2 characters');
        }

        $tracks = $this->trackDAO->search($this->sanitizeString($searchTerm));

        return $this->successResponse([
            'tracks' => $tracks,
            'count' => count($tracks)
        ]);
    }

    public function updateTrack($trackId, $data)
    {
        $track = $this->trackDAO->getById($trackId);

        if (!$track) {
            return $this->errorResponse('Track not found', 404);
        }

        $errors = [];

        if (isset($data['title']) && !$this->validateLength($data['title'], 1, 255)) {
            $errors[] = "Title must be between 1 and 255 characters";
        }

        if (isset($data['track_number']) && (!is_numeric($data['track_number']) || $data['track_number'] < 1)) {
            $errors[] = "Track number must be a positive integer";
        }

        if (isset($data['duration']) && !preg_match('/^\d{1,3}:[0-5][0-9]$/', $data['duration'])) {
            $errors[] = "Duration must be in format MM:SS (e.g., 3:45)";
        }

        if (!empty($errors)) {
            return $this->errorResponse($errors);
        }

        if (isset($data['title'])) {
            $data['title'] = $this->sanitizeString($data['title']);
        }

        $result = $this->trackDAO->update($trackId, $data);

        if (!$result) {
            $this->logError('TrackService', 'updateTrack', 'Failed to update track');
            return $this->errorResponse('Failed to update track', 500);
        }

        return $this->successResponse(null, 'Track updated successfully');
    }

    public function deleteTrack($trackId)
    {
        $track = $this->trackDAO->getById($trackId);

        if (!$track) {
            return $this->errorResponse('Track not found', 404);
        }

        $result = $this->trackDAO->delete($trackId);

        if (!$result) {
            $this->logError('TrackService', 'deleteTrack', 'Failed to delete track');
            return $this->errorResponse('Failed to delete track', 500);
        }

        return $this->successResponse(null, 'Track deleted successfully');
    }

    public function deleteAlbumTracks($albumId)
    {
        $album = $this->albumDAO->getById($albumId);
        if (!$album) {
            return $this->errorResponse('Album not found', 404);
        }

        $result = $this->trackDAO->deleteByAlbumId($albumId);

        if (!$result) {
            $this->logError('TrackService', 'deleteAlbumTracks', 'Failed to delete tracks');
            return $this->errorResponse('Failed to delete tracks', 500);
        }

        return $this->successResponse(null, 'Album tracks deleted successfully');
    }
}
