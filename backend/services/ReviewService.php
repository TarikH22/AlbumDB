<?php

require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/ReviewDAO.php';
require_once __DIR__ . '/../dao/AlbumDAO.php';
require_once __DIR__ . '/../dao/UserDAO.php';

class ReviewService extends BaseService
{
    private $reviewDAO;
    private $albumDAO;
    private $userDAO;

    public function __construct()
    {
        $this->reviewDAO = new ReviewDAO();
        $this->albumDAO = new AlbumDAO();
        $this->userDAO = new UserDAO();
    }

    public function createReview($data)
    {
        $validation = $this->validateRequiredFields($data, [
            'user_id',
            'album_id',
            'title',
            'review_text'
        ]);

        if (!$validation['valid']) {
            return $this->errorResponse($validation['errors']);
        }

        $errors = [];

        if (!$this->validateLength($data['title'], 3, 255)) {
            $errors[] = "Title must be between 3 and 255 characters";
        }

        if (!$this->validateLength($data['review_text'], 10, 5000)) {
            $errors[] = "Review text must be between 10 and 5000 characters";
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

        if ($this->reviewDAO->hasUserReviewed($data['user_id'], $data['album_id'])) {
            return $this->errorResponse('You have already reviewed this album');
        }

        $data['title'] = $this->sanitizeString($data['title']);
        $data['review_text'] = $this->sanitizeString($data['review_text']);

        $reviewId = $this->reviewDAO->create($data);

        if (!$reviewId) {
            $this->logError('ReviewService', 'createReview', 'Failed to create review');
            return $this->errorResponse('Failed to create review', 500);
        }

        $review = $this->reviewDAO->getByIdWithDetails($reviewId);

        return $this->successResponse($review, 'Review created successfully');
    }

    public function getReview($reviewId)
    {
        $review = $this->reviewDAO->getByIdWithDetails($reviewId);

        if (!$review) {
            return $this->errorResponse('Review not found', 404);
        }

        return $this->successResponse($review);
    }

    public function getUserReviews($userId)
    {
        $user = $this->userDAO->getById($userId);
        if (!$user) {
            return $this->errorResponse('User not found', 404);
        }

        $reviews = $this->reviewDAO->getByUserId($userId);

        return $this->successResponse([
            'reviews' => $reviews,
            'count' => count($reviews)
        ]);
    }

    public function getAlbumReviews($albumId, $orderBy = 'created_at')
    {
        $album = $this->albumDAO->getById($albumId);
        if (!$album) {
            return $this->errorResponse('Album not found', 404);
        }

        $reviews = $this->reviewDAO->getByAlbumId($albumId, $orderBy);

        return $this->successResponse([
            'reviews' => $reviews,
            'count' => count($reviews)
        ]);
    }

    public function getRecentReviews($limit = 10)
    {
        if ($limit < 1 || $limit > 100) {
            $limit = 10;
        }

        $reviews = $this->reviewDAO->getRecent($limit);

        return $this->successResponse([
            'reviews' => $reviews,
            'count' => count($reviews)
        ]);
    }

    public function searchReviews($searchTerm)
    {
        if (empty($searchTerm) || strlen(trim($searchTerm)) < 2) {
            return $this->errorResponse('Search term must be at least 2 characters');
        }

        $reviews = $this->reviewDAO->search($this->sanitizeString($searchTerm));

        return $this->successResponse([
            'reviews' => $reviews,
            'count' => count($reviews)
        ]);
    }

    public function updateReview($reviewId, $data, $userId = null)
    {
        $review = $this->reviewDAO->getById($reviewId);

        if (!$review) {
            return $this->errorResponse('Review not found', 404);
        }

        if ($userId !== null && $review['user_id'] != $userId) {
            return $this->errorResponse('Unauthorized to update this review', 403);
        }

        $errors = [];

        if (isset($data['title']) && !$this->validateLength($data['title'], 3, 255)) {
            $errors[] = "Title must be between 3 and 255 characters";
        }

        if (isset($data['review_text']) && !$this->validateLength($data['review_text'], 10, 5000)) {
            $errors[] = "Review text must be between 10 and 5000 characters";
        }

        if (!empty($errors)) {
            return $this->errorResponse($errors);
        }

        if (isset($data['title'])) {
            $data['title'] = $this->sanitizeString($data['title']);
        }

        if (isset($data['review_text'])) {
            $data['review_text'] = $this->sanitizeString($data['review_text']);
        }

        $result = $this->reviewDAO->update($reviewId, $data);

        if (!$result) {
            $this->logError('ReviewService', 'updateReview', 'Failed to update review');
            return $this->errorResponse('Failed to update review', 500);
        }

        return $this->successResponse(null, 'Review updated successfully');
    }

    public function deleteReview($reviewId, $userId = null)
    {
        $review = $this->reviewDAO->getById($reviewId);

        if (!$review) {
            return $this->errorResponse('Review not found', 404);
        }

        if ($userId !== null && $review['user_id'] != $userId) {
            return $this->errorResponse('Unauthorized to delete this review', 403);
        }

        $result = $this->reviewDAO->delete($reviewId);

        if (!$result) {
            $this->logError('ReviewService', 'deleteReview', 'Failed to delete review');
            return $this->errorResponse('Failed to delete review', 500);
        }

        return $this->successResponse(null, 'Review deleted successfully');
    }

    public function getUserReviewForAlbum($userId, $albumId)
    {
        $review = $this->reviewDAO->getUserReviewForAlbum($userId, $albumId);

        if (!$review) {
            return $this->successResponse(null, 'No review found');
        }

        return $this->successResponse($review);
    }

    public function hasUserReviewed($userId, $albumId)
    {
        $hasReviewed = $this->reviewDAO->hasUserReviewed($userId, $albumId);

        return $this->successResponse([
            'has_reviewed' => $hasReviewed
        ]);
    }

    public function getMostReviewedAlbums($limit = 10)
    {
        if ($limit < 1 || $limit > 100) {
            $limit = 10;
        }

        $albums = $this->reviewDAO->getMostReviewedAlbums($limit);

        return $this->successResponse([
            'albums' => $albums,
            'count' => count($albums)
        ]);
    }
}
