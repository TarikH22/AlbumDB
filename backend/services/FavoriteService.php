<?php

require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/FavoriteDAO.php';
require_once __DIR__ . '/../dao/AlbumDAO.php';
require_once __DIR__ . '/../dao/UserDAO.php';

class FavoriteService extends BaseService
{
    private $favoriteDAO;
    private $albumDAO;
    private $userDAO;

    public function __construct()
    {
        $this->favoriteDAO = new FavoriteDAO();
        $this->albumDAO = new AlbumDAO();
        $this->userDAO = new UserDAO();
    }

    public function addFavorite($data)
    {
        $validation = $this->validateRequiredFields($data, [
            'user_id',
            'album_id'
        ]);

        if (!$validation['valid']) {
            return $this->errorResponse($validation['errors']);
        }

        $user = $this->userDAO->getById($data['user_id']);
        if (!$user) {
            return $this->errorResponse('User not found', 404);
        }

        $album = $this->albumDAO->getById($data['album_id']);
        if (!$album) {
            return $this->errorResponse('Album not found', 404);
        }

        if ($this->favoriteDAO->isFavorited($data['user_id'], $data['album_id'])) {
            return $this->errorResponse('Album is already in favorites');
        }

        $favoriteId = $this->favoriteDAO->create($data);

        if (!$favoriteId) {
            $this->logError('FavoriteService', 'addFavorite', 'Failed to add favorite');
            return $this->errorResponse('Failed to add to favorites', 500);
        }

        return $this->successResponse([
            'favorite_id' => $favoriteId
        ], 'Album added to favorites');
    }

    public function removeFavorite($userId, $albumId)
    {
        if (!$this->favoriteDAO->isFavorited($userId, $albumId)) {
            return $this->errorResponse('Album is not in favorites', 404);
        }

        $result = $this->favoriteDAO->deleteByUserAndAlbum($userId, $albumId);

        if (!$result) {
            $this->logError('FavoriteService', 'removeFavorite', 'Failed to remove favorite');
            return $this->errorResponse('Failed to remove from favorites', 500);
        }

        return $this->successResponse(null, 'Album removed from favorites');
    }

    public function toggleFavorite($userId, $albumId)
    {
        $user = $this->userDAO->getById($userId);
        if (!$user) {
            return $this->errorResponse('User not found', 404);
        }

        $album = $this->albumDAO->getById($albumId);
        if (!$album) {
            return $this->errorResponse('Album not found', 404);
        }

        $result = $this->favoriteDAO->toggle($userId, $albumId);

        if (!$result['success']) {
            $this->logError('FavoriteService', 'toggleFavorite', 'Failed to toggle favorite');
            return $this->errorResponse($result['message'], 500);
        }

        return $this->successResponse([
            'action' => $result['action'],
            'is_favorited' => $result['action'] === 'added'
        ], $result['message']);
    }

    public function getUserFavorites($userId)
    {
        $user = $this->userDAO->getById($userId);
        if (!$user) {
            return $this->errorResponse('User not found', 404);
        }

        $favorites = $this->favoriteDAO->getByUserId($userId);

        return $this->successResponse([
            'favorites' => $favorites,
            'count' => count($favorites)
        ]);
    }

    public function getAlbumFavorites($albumId)
    {
        $album = $this->albumDAO->getById($albumId);
        if (!$album) {
            return $this->errorResponse('Album not found', 404);
        }

        $favorites = $this->favoriteDAO->getByAlbumId($albumId);
        $count = $this->favoriteDAO->getAlbumFavoriteCount($albumId);

        return $this->successResponse([
            'favorites' => $favorites,
            'count' => $count
        ]);
    }

    public function getMostFavorited($limit = 10)
    {
        if ($limit < 1 || $limit > 100) {
            $limit = 10;
        }

        $albums = $this->favoriteDAO->getMostFavorited($limit);

        return $this->successResponse([
            'albums' => $albums,
            'count' => count($albums)
        ]);
    }

    public function getRecentFavorites($limit = 10)
    {
        if ($limit < 1 || $limit > 100) {
            $limit = 10;
        }

        $favorites = $this->favoriteDAO->getRecent($limit);

        return $this->successResponse([
            'favorites' => $favorites,
            'count' => count($favorites)
        ]);
    }

    public function isFavorited($userId, $albumId)
    {
        $isFavorited = $this->favoriteDAO->isFavorited($userId, $albumId);

        return $this->successResponse([
            'is_favorited' => $isFavorited
        ]);
    }

    public function getUserFavoritesByGenre($userId, $genre)
    {
        $user = $this->userDAO->getById($userId);
        if (!$user) {
            return $this->errorResponse('User not found', 404);
        }

        $favorites = $this->favoriteDAO->getUserFavoritesByGenre($userId, $this->sanitizeString($genre));

        return $this->successResponse([
            'favorites' => $favorites,
            'count' => count($favorites)
        ]);
    }
}
