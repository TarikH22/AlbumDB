<?php

require_once __DIR__ . '/BaseDAO.php';

class FavoriteDAO extends BaseDAO {

    public function __construct() {
        parent::__construct();
        $this->tableName = 'favorites';
    }


    public function create($data) {
        try {
            if ($this->isFavorited($data['user_id'], $data['album_id'])) {
                return false;
            }

            $sql = "INSERT INTO favorites (user_id, album_id)
                    VALUES (:user_id, :album_id)";

            $params = [
                ':user_id' => $data['user_id'],
                ':album_id' => $data['album_id']
            ];

            $this->executeQuery($sql, $params);
            return $this->getLastInsertId();
        } catch (Exception $e) {
            error_log("FavoriteDAO::create() Error: " . $e->getMessage());
            return false;
        }
    }

    
    public function getById($favoriteId) {
        try {
            $sql = "SELECT favorite_id, user_id, album_id, created_at
                    FROM favorites WHERE favorite_id = ?";

            $stmt = $this->executeQuery($sql, [$favoriteId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("FavoriteDAO::getById() Error: " . $e->getMessage());
            return false;
        }
    }

    
    public function getByUserId($userId) {
        try {
            $sql = "SELECT f.favorite_id, f.created_at,
                           a.album_id, a.title, a.artist, a.genre, a.year, a.cover_url,
                           COALESCE(AVG(r.rating), 0) as avg_rating,
                           COUNT(DISTINCT r.rating_id) as rating_count
                    FROM favorites f
                    INNER JOIN albums a ON f.album_id = a.album_id
                    LEFT JOIN ratings r ON a.album_id = r.album_id
                    WHERE f.user_id = ?
                    GROUP BY f.favorite_id, a.album_id
                    ORDER BY f.created_at DESC";

            $stmt = $this->executeQuery($sql, [$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("FavoriteDAO::getByUserId() Error: " . $e->getMessage());
            return [];
        }
    }

    
    public function getByAlbumId($albumId) {
        try {
            $sql = "SELECT f.favorite_id, f.created_at,
                           u.user_id, u.username, u.first_name, u.last_name, u.avatar_url
                    FROM favorites f
                    INNER JOIN users u ON f.user_id = u.user_id
                    WHERE f.album_id = ?
                    ORDER BY f.created_at DESC";

            $stmt = $this->executeQuery($sql, [$albumId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("FavoriteDAO::getByAlbumId() Error: " . $e->getMessage());
            return [];
        }
    }

   
    public function getAll($limit = null, $offset = 0) {
        try {
            $sql = "SELECT f.*,
                           u.username,
                           a.title as album_title, a.artist, a.cover_url
                    FROM favorites f
                    INNER JOIN users u ON f.user_id = u.user_id
                    INNER JOIN albums a ON f.album_id = a.album_id
                    ORDER BY f.created_at DESC";

            if ($limit !== null) {
                $sql .= " LIMIT :limit OFFSET :offset";
            }

            $stmt = $this->connection->prepare($sql);

            if ($limit !== null) {
                $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
                $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("FavoriteDAO::getAll() Error: " . $e->getMessage());
            return [];
        }
    }

    
    public function isFavorited($userId, $albumId) {
        try {
            $sql = "SELECT COUNT(*) FROM favorites WHERE user_id = ? AND album_id = ?";
            $stmt = $this->executeQuery($sql, [$userId, $albumId]);
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            error_log("FavoriteDAO::isFavorited() Error: " . $e->getMessage());
            return false;
        }
    }

 
    public function getUserFavoriteCount($userId) {
        try {
            $sql = "SELECT COUNT(*) FROM favorites WHERE user_id = ?";
            $stmt = $this->executeQuery($sql, [$userId]);
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("FavoriteDAO::getUserFavoriteCount() Error: " . $e->getMessage());
            return 0;
        }
    }

    public function getAlbumFavoriteCount($albumId) {
        try {
            $sql = "SELECT COUNT(*) FROM favorites WHERE album_id = ?";
            $stmt = $this->executeQuery($sql, [$albumId]);
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("FavoriteDAO::getAlbumFavoriteCount() Error: " . $e->getMessage());
            return 0;
        }
    }

    
    public function getMostFavorited($limit = 10) {
        try {
            $sql = "SELECT a.album_id, a.title, a.artist, a.cover_url, a.genre, a.year,
                           COUNT(f.favorite_id) as favorite_count,
                           COALESCE(AVG(r.rating), 0) as avg_rating
                    FROM albums a
                    INNER JOIN favorites f ON a.album_id = f.album_id
                    LEFT JOIN ratings r ON a.album_id = r.album_id
                    GROUP BY a.album_id
                    ORDER BY favorite_count DESC
                    LIMIT :limit";

            $stmt = $this->connection->prepare($sql);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("FavoriteDAO::getMostFavorited() Error: " . $e->getMessage());
            return [];
        }
    }

    
    public function getRecent($limit = 10) {
        try {
            $sql = "SELECT f.*,
                           u.username, u.avatar_url,
                           a.title, a.artist, a.cover_url
                    FROM favorites f
                    INNER JOIN users u ON f.user_id = u.user_id
                    INNER JOIN albums a ON f.album_id = a.album_id
                    ORDER BY f.created_at DESC
                    LIMIT :limit";

            $stmt = $this->connection->prepare($sql);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("FavoriteDAO::getRecent() Error: " . $e->getMessage());
            return [];
        }
    }

   
    public function getUserFavoritesByGenre($userId, $genre) {
        try {
            $sql = "SELECT f.favorite_id, f.created_at,
                           a.album_id, a.title, a.artist, a.genre, a.year, a.cover_url
                    FROM favorites f
                    INNER JOIN albums a ON f.album_id = a.album_id
                    WHERE f.user_id = ? AND a.genre = ?
                    ORDER BY f.created_at DESC";

            $stmt = $this->executeQuery($sql, [$userId, $genre]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("FavoriteDAO::getUserFavoritesByGenre() Error: " . $e->getMessage());
            return [];
        }
    }

   
    public function getByUserAndAlbum($userId, $albumId) {
        try {
            $sql = "SELECT favorite_id, user_id, album_id, created_at
                    FROM favorites
                    WHERE user_id = ? AND album_id = ?";

            $stmt = $this->executeQuery($sql, [$userId, $albumId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("FavoriteDAO::getByUserAndAlbum() Error: " . $e->getMessage());
            return false;
        }
    }

    
    public function update($favoriteId, $data) {
        return false;
    }

    
    public function delete($favoriteId) {
        try {
            $sql = "DELETE FROM favorites WHERE favorite_id = :favorite_id";
            $this->executeQuery($sql, [':favorite_id' => $favoriteId]);
            return true;
        } catch (Exception $e) {
            error_log("FavoriteDAO::delete() Error: " . $e->getMessage());
            return false;
        }
    }

    
    public function deleteByUserAndAlbum($userId, $albumId) {
        try {
            $sql = "DELETE FROM favorites WHERE user_id = :user_id AND album_id = :album_id";
            $params = [':user_id' => $userId, ':album_id' => $albumId];
            $this->executeQuery($sql, $params);
            return true;
        } catch (Exception $e) {
            error_log("FavoriteDAO::deleteByUserAndAlbum() Error: " . $e->getMessage());
            return false;
        }
    }

   
    public function deleteByUserId($userId) {
        try {
            $sql = "DELETE FROM favorites WHERE user_id = :user_id";
            $this->executeQuery($sql, [':user_id' => $userId]);
            return true;
        } catch (Exception $e) {
            error_log("FavoriteDAO::deleteByUserId() Error: " . $e->getMessage());
            return false;
        }
    }

    public function deleteByAlbumId($albumId) {
        try {
            $sql = "DELETE FROM favorites WHERE album_id = :album_id";
            $this->executeQuery($sql, [':album_id' => $albumId]);
            return true;
        } catch (Exception $e) {
            error_log("FavoriteDAO::deleteByAlbumId() Error: " . $e->getMessage());
            return false;
        }
    }

   
    public function toggle($userId, $albumId) {
        try {
            if ($this->isFavorited($userId, $albumId)) {
                $result = $this->deleteByUserAndAlbum($userId, $albumId);
                return [
                    'success' => $result,
                    'action' => 'removed',
                    'message' => 'Album removed from favorites'
                ];
            } else {
                $result = $this->create(['user_id' => $userId, 'album_id' => $albumId]);
                return [
                    'success' => $result !== false,
                    'action' => 'added',
                    'message' => 'Album added to favorites',
                    'favorite_id' => $result
                ];
            }
        } catch (Exception $e) {
            error_log("FavoriteDAO::toggle() Error: " . $e->getMessage());
            return [
                'success' => false,
                'action' => 'error',
                'message' => 'Failed to toggle favorite status'
            ];
        }
    }
}
?>
