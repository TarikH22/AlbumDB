<?php

require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/RatingDAO.php';
require_once __DIR__ . '/../dao/AlbumDAO.php';
require_once __DIR__ . '/../dao/UserDAO.php';

class RatingService extends BaseService
{
    private $ratingDAO;
    private $albumDAO;
    private $userDAO;

    public function __construct()
    {
        $this->ratingDAO = new RatingDAO();
        $this->albumDAO = new AlbumDAO();
        $this->userDAO = new UserDAO();
    }

    public function rateAlbum($data)
    {
        $validation = $this->validateRequiredFields($data, [
            'user_id',
            'album_id',
            'rating'
        ]);

        if (!$validation['valid']) {
            return $this->errorResponse($validation['errors']);
        }

        $errors = [];

        if (!$this->validateRating($data['rating'])) {
            $errors[] = "Rating must be between 1 and 10";
        }

        if (!empty($errors)) {
            return $this->errorResponse($errors);
        }

        $user = $this->userDAO->getById($data['user_id']);
        if (!$user) {
            return $this->errorResponse('User not found', 404);
        }

        $album = $this->albumDAO->getById($data['album_id']);
        if (!$album) {
            return $this->errorResponse('Album not found', 404);
        }

        $ratingId = $this->ratingDAO->create($data);

        if (!$ratingId) {
            $this->logError('RatingService', 'rateAlbum', 'Failed to save rating');
            return $this->errorResponse('Failed to save rating', 500);
        }

        $avgRating = $this->ratingDAO->getAverageRating($data['album_id']);

        return $this->successResponse([
            'rating_id' => $ratingId,
            'average_rating' => $avgRating
        ], 'Rating saved successfully');
    }

    public function getRating($ratingId)
    {
        $rating = $this->ratingDAO->getById($ratingId);

        if (!$rating) {
            return $this->errorResponse('Rating not found', 404);
        }

        return $this->successResponse($rating);
    }

    public function getUserRatingForAlbum($userId, $albumId)
    {
        $rating = $this->ratingDAO->getByUserAndAlbum($userId, $albumId);

        if (!$rating) {
            return $this->successResponse(null, 'No rating found');
        }

        return $this->successResponse($rating);
    }

    public function getUserRatings($userId)
    {
        $user = $this->userDAO->getById($userId);
        if (!$user) {
            return $this->errorResponse('User not found', 404);
        }

        $ratings = $this->ratingDAO->getByUserId($userId);

        return $this->successResponse([
            'ratings' => $ratings,
            'count' => count($ratings)
        ]);
    }

    public function getAlbumRatings($albumId)
    {
        $album = $this->albumDAO->getById($albumId);
        if (!$album) {
            return $this->errorResponse('Album not found', 404);
        }

        $ratings = $this->ratingDAO->getByAlbumId($albumId);
        $stats = $this->ratingDAO->getAlbumRatingStats($albumId);

        return $this->successResponse([
            'ratings' => $ratings,
            'stats' => $stats
        ]);
    }

    public function getRecentRatings($limit = 10)
    {
        if ($limit < 1 || $limit > 100) {
            $limit = 10;
        }

        $ratings = $this->ratingDAO->getRecent($limit);

        return $this->successResponse([
            'ratings' => $ratings,
            'count' => count($ratings)
        ]);
    }

    public function updateRating($ratingId, $data)
    {
        $rating = $this->ratingDAO->getById($ratingId);

        if (!$rating) {
            return $this->errorResponse('Rating not found', 404);
        }

        if (isset($data['rating']) && !$this->validateRating($data['rating'])) {
            return $this->errorResponse('Rating must be between 1 and 10');
        }

        $result = $this->ratingDAO->update($ratingId, $data);

        if (!$result) {
            $this->logError('RatingService', 'updateRating', 'Failed to update rating');
            return $this->errorResponse('Failed to update rating', 500);
        }

        $avgRating = $this->ratingDAO->getAverageRating($rating['album_id']);

        return $this->successResponse([
            'average_rating' => $avgRating
        ], 'Rating updated successfully');
    }

    public function deleteRating($ratingId, $userId = null)
    {
        $rating = $this->ratingDAO->getById($ratingId);

        if (!$rating) {
            return $this->errorResponse('Rating not found', 404);
        }

        if ($userId !== null && $rating['user_id'] != $userId) {
            return $this->errorResponse('Unauthorized to delete this rating', 403);
        }

        $albumId = $rating['album_id'];
        $result = $this->ratingDAO->delete($ratingId);

        if (!$result) {
            $this->logError('RatingService', 'deleteRating', 'Failed to delete rating');
            return $this->errorResponse('Failed to delete rating', 500);
        }

        $avgRating = $this->ratingDAO->getAverageRating($albumId);

        return $this->successResponse([
            'average_rating' => $avgRating
        ], 'Rating deleted successfully');
    }

    public function hasUserRated($userId, $albumId)
    {
        $hasRated = $this->ratingDAO->hasUserRated($userId, $albumId);

        return $this->successResponse([
            'has_rated' => $hasRated
        ]);
    }
}
