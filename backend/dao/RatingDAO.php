<?php

require_once __DIR__ . '/BaseDAO.php';

class RatingDAO extends BaseDAO {

    public function __construct() {
        parent::__construct();
        $this->tableName = 'ratings';
    }

    
    public function create($data) {
        try {
            $existing = $this->getByUserAndAlbum($data['user_id'], $data['album_id']);

            if ($existing) {
                return $this->update($existing['rating_id'], ['rating' => $data['rating']]);
            }

            $sql = "INSERT INTO ratings (user_id, album_id, rating)
                    VALUES (:user_id, :album_id, :rating)";

            $params = [
                ':user_id' => $data['user_id'],
                ':album_id' => $data['album_id'],
                ':rating' => $data['rating']
            ];

            $this->executeQuery($sql, $params);
            return $this->getLastInsertId();
        } catch (Exception $e) {
            error_log("RatingDAO::create() Error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getById($ratingId) {
        try {
            $sql = "SELECT rating_id, user_id, album_id, rating, created_at, updated_at
                    FROM ratings WHERE rating_id = ?";

            $stmt = $this->executeQuery($sql, [$ratingId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("RatingDAO::getById() Error: " . $e->getMessage());
            return false;
        }
    }

    public function getByUserAndAlbum($userId, $albumId) {
        try {
            $sql = "SELECT rating_id, user_id, album_id, rating, created_at, updated_at
                    FROM ratings
                    WHERE user_id = ? AND album_id = ?";

            $stmt = $this->executeQuery($sql, [$userId, $albumId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("RatingDAO::getByUserAndAlbum() Error: " . $e->getMessage());
            return false;
        }
    }


    public function getByUserId($userId) {
        try {
            $sql = "SELECT r.*, a.title, a.artist, a.cover_url, a.year
                    FROM ratings r
                    INNER JOIN albums a ON r.album_id = a.album_id
                    WHERE r.user_id = ?
                    ORDER BY r.created_at DESC";

            $stmt = $this->executeQuery($sql, [$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("RatingDAO::getByUserId() Error: " . $e->getMessage());
            return [];
        }
    }

    
    public function getByAlbumId($albumId) {
        try {
            $sql = "SELECT r.*, u.username, u.first_name, u.last_name, u.avatar_url
                    FROM ratings r
                    INNER JOIN users u ON r.user_id = u.user_id
                    WHERE r.album_id = ?
                    ORDER BY r.created_at DESC";

            $stmt = $this->executeQuery($sql, [$albumId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("RatingDAO::getByAlbumId() Error: " . $e->getMessage());
            return [];
        }
    }

   
    public function getAll($limit = null, $offset = 0) {
        try {
            $sql = "SELECT r.*, u.username, a.title as album_title, a.artist
                    FROM ratings r
                    INNER JOIN users u ON r.user_id = u.user_id
                    INNER JOIN albums a ON r.album_id = a.album_id
                    ORDER BY r.created_at DESC";

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
            error_log("RatingDAO::getAll() Error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getAverageRating($albumId) {
        try {
            $sql = "SELECT AVG(rating) as avg_rating FROM ratings WHERE album_id = ?";
            $stmt = $this->executeQuery($sql, [$albumId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? round((float)$result['avg_rating'], 1) : 0.0;
        } catch (Exception $e) {
            error_log("RatingDAO::getAverageRating() Error: " . $e->getMessage());
            return 0.0;
        }
    }

    
    public function getRatingCount($albumId) {
        try {
            $sql = "SELECT COUNT(*) FROM ratings WHERE album_id = ?";
            $stmt = $this->executeQuery($sql, [$albumId]);
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("RatingDAO::getRatingCount() Error: " . $e->getMessage());
            return 0;
        }
    }

    public function getAlbumRatingStats($albumId) {
        try {
            $sql = "SELECT
                        COUNT(*) as total_ratings,
                        AVG(rating) as avg_rating,
                        MIN(rating) as min_rating,
                        MAX(rating) as max_rating,
                        SUM(CASE WHEN rating >= 9 THEN 1 ELSE 0 END) as excellent_count,
                        SUM(CASE WHEN rating >= 7 AND rating < 9 THEN 1 ELSE 0 END) as good_count,
                        SUM(CASE WHEN rating >= 5 AND rating < 7 THEN 1 ELSE 0 END) as average_count,
                        SUM(CASE WHEN rating < 5 THEN 1 ELSE 0 END) as poor_count
                    FROM ratings
                    WHERE album_id = ?";

            $stmt = $this->executeQuery($sql, [$albumId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                $result['avg_rating'] = round((float)$result['avg_rating'], 1);
            }

            return $result;
        } catch (Exception $e) {
            error_log("RatingDAO::getAlbumRatingStats() Error: " . $e->getMessage());
            return [];
        }
    }

    public function getUserAverageRating($userId) {
        try {
            $sql = "SELECT AVG(rating) as avg_rating FROM ratings WHERE user_id = ?";
            $stmt = $this->executeQuery($sql, [$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? round((float)$result['avg_rating'], 1) : 0.0;
        } catch (Exception $e) {
            error_log("RatingDAO::getUserAverageRating() Error: " . $e->getMessage());
            return 0.0;
        }
    }

    public function getRecent($limit = 10) {
        try {
            $sql = "SELECT r.*, u.username, u.avatar_url, a.title, a.artist, a.cover_url
                    FROM ratings r
                    INNER JOIN users u ON r.user_id = u.user_id
                    INNER JOIN albums a ON r.album_id = a.album_id
                    ORDER BY r.created_at DESC
                    LIMIT :limit";

            $stmt = $this->connection->prepare($sql);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("RatingDAO::getRecent() Error: " . $e->getMessage());
            return [];
        }
    }

    public function update($ratingId, $data) {
        try {
            if (!isset($data['rating'])) {
                return false;
            }

            $sql = "UPDATE ratings SET rating = :rating WHERE rating_id = :rating_id";

            $params = [
                ':rating' => $data['rating'],
                ':rating_id' => $ratingId
            ];

            $this->executeQuery($sql, $params);
            return true;
        } catch (Exception $e) {
            error_log("RatingDAO::update() Error: " . $e->getMessage());
            return false;
        }
    }

    public function updateByUserAndAlbum($userId, $albumId, $rating) {
        try {
            $sql = "UPDATE ratings SET rating = :rating
                    WHERE user_id = :user_id AND album_id = :album_id";

            $params = [
                ':rating' => $rating,
                ':user_id' => $userId,
                ':album_id' => $albumId
            ];

            $this->executeQuery($sql, $params);
            return true;
        } catch (Exception $e) {
            error_log("RatingDAO::updateByUserAndAlbum() Error: " . $e->getMessage());
            return false;
        }
    }

    public function delete($ratingId) {
        try {
            $sql = "DELETE FROM ratings WHERE rating_id = :rating_id";
            $this->executeQuery($sql, [':rating_id' => $ratingId]);
            return true;
        } catch (Exception $e) {
            error_log("RatingDAO::delete() Error: " . $e->getMessage());
            return false;
        }
    }

    public function deleteByUserAndAlbum($userId, $albumId) {
        try {
            $sql = "DELETE FROM ratings WHERE user_id = :user_id AND album_id = :album_id";
            $params = [':user_id' => $userId, ':album_id' => $albumId];
            $this->executeQuery($sql, $params);
            return true;
        } catch (Exception $e) {
            error_log("RatingDAO::deleteByUserAndAlbum() Error: " . $e->getMessage());
            return false;
        }
    }

    public function hasUserRated($userId, $albumId) {
        $rating = $this->getByUserAndAlbum($userId, $albumId);
        return $rating !== false;
    }
}
?>
