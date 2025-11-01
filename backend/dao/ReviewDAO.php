<?php

require_once __DIR__ . '/BaseDAO.php';

class ReviewDAO extends BaseDAO {

    public function __construct() {
        parent::__construct();
        $this->tableName = 'reviews';
    }

    public function create($data) {
        try {
            $sql = "INSERT INTO reviews (user_id, album_id, title, review_text)
                    VALUES (:user_id, :album_id, :title, :review_text)";

            $params = [
                ':user_id' => $data['user_id'],
                ':album_id' => $data['album_id'],
                ':title' => $data['title'],
                ':review_text' => $data['review_text']
            ];

            $this->executeQuery($sql, $params);
            return $this->getLastInsertId();
        } catch (Exception $e) {
            error_log("ReviewDAO::create() Error: " . $e->getMessage());
            return false;
        }
    }

    public function getById($reviewId) {
        try {
            $sql = "SELECT review_id, user_id, album_id, title, review_text,
                           created_at, updated_at
                    FROM reviews WHERE review_id = ?";

            $stmt = $this->executeQuery($sql, [$reviewId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("ReviewDAO::getById() Error: " . $e->getMessage());
            return false;
        }
    }


    public function getByIdWithDetails($reviewId) {
        try {
            $sql = "SELECT r.*,
                           u.username, u.first_name, u.last_name, u.avatar_url,
                           a.title as album_title, a.artist, a.cover_url, a.year
                    FROM reviews r
                    INNER JOIN users u ON r.user_id = u.user_id
                    INNER JOIN albums a ON r.album_id = a.album_id
                    WHERE r.review_id = ?";

            $stmt = $this->executeQuery($sql, [$reviewId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("ReviewDAO::getByIdWithDetails() Error: " . $e->getMessage());
            return false;
        }
    }


    public function getByUserId($userId) {
        try {
            $sql = "SELECT r.*, a.title, a.artist, a.cover_url, a.year
                    FROM reviews r
                    INNER JOIN albums a ON r.album_id = a.album_id
                    WHERE r.user_id = ?
                    ORDER BY r.created_at DESC";

            $stmt = $this->executeQuery($sql, [$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("ReviewDAO::getByUserId() Error: " . $e->getMessage());
            return [];
        }
    }


    public function getByAlbumId($albumId, $orderBy = 'created_at') {
        try {
            $allowedOrderBy = ['created_at', 'updated_at'];
            if (!in_array($orderBy, $allowedOrderBy)) {
                $orderBy = 'created_at';
            }

            $sql = "SELECT r.*, u.username, u.first_name, u.last_name, u.avatar_url
                    FROM reviews r
                    INNER JOIN users u ON r.user_id = u.user_id
                    WHERE r.album_id = ?
                    ORDER BY r.$orderBy DESC";

            $stmt = $this->executeQuery($sql, [$albumId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("ReviewDAO::getByAlbumId() Error: " . $e->getMessage());
            return [];
        }
    }

    public function getAll($limit = null, $offset = 0) {
        try {
            $sql = "SELECT r.*,
                           u.username, u.first_name, u.last_name, u.avatar_url,
                           a.title as album_title, a.artist, a.cover_url
                    FROM reviews r
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
            error_log("ReviewDAO::getAll() Error: " . $e->getMessage());
            return [];
        }
    }

    public function getRecent($limit = 10) {
        try {
            $sql = "SELECT r.*,
                           u.username, u.avatar_url,
                           a.title, a.artist, a.cover_url
                    FROM reviews r
                    INNER JOIN users u ON r.user_id = u.user_id
                    INNER JOIN albums a ON r.album_id = a.album_id
                    ORDER BY r.created_at DESC
                    LIMIT :limit";

            $stmt = $this->connection->prepare($sql);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("ReviewDAO::getRecent() Error: " . $e->getMessage());
            return [];
        }
    }

    public function getReviewCount($albumId) {
        try {
            $sql = "SELECT COUNT(*) FROM reviews WHERE album_id = ?";
            $stmt = $this->executeQuery($sql, [$albumId]);
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("ReviewDAO::getReviewCount() Error: " . $e->getMessage());
            return 0;
        }
    }

    public function getUserReviewCount($userId) {
        try {
            $sql = "SELECT COUNT(*) FROM reviews WHERE user_id = ?";
            $stmt = $this->executeQuery($sql, [$userId]);
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("ReviewDAO::getUserReviewCount() Error: " . $e->getMessage());
            return 0;
        }
    }

    public function search($searchTerm) {
        try {
            $sql = "SELECT r.*,
                           u.username, u.avatar_url,
                           a.title as album_title, a.artist, a.cover_url
                    FROM reviews r
                    INNER JOIN users u ON r.user_id = u.user_id
                    INNER JOIN albums a ON r.album_id = a.album_id
                    WHERE r.title LIKE :search OR r.review_text LIKE :search
                    ORDER BY r.created_at DESC";

            $stmt = $this->executeQuery($sql, [':search' => "%$searchTerm%"]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("ReviewDAO::search() Error: " . $e->getMessage());
            return [];
        }
    }

    public function hasUserReviewed($userId, $albumId) {
        try {
            $sql = "SELECT COUNT(*) FROM reviews WHERE user_id = ? AND album_id = ?";
            $stmt = $this->executeQuery($sql, [$userId, $albumId]);
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            error_log("ReviewDAO::hasUserReviewed() Error: " . $e->getMessage());
            return false;
        }
    }

 
    public function getUserReviewForAlbum($userId, $albumId) {
        try {
            $sql = "SELECT r.*
                    FROM reviews r
                    WHERE r.user_id = ? AND r.album_id = ?";

            $stmt = $this->executeQuery($sql, [$userId, $albumId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("ReviewDAO::getUserReviewForAlbum() Error: " . $e->getMessage());
            return false;
        }
    }

    public function update($reviewId, $data) {
        try {
            $fields = [];
            $params = [':review_id' => $reviewId];

            $allowedFields = ['title', 'review_text'];

            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $fields[] = "$field = :$field";
                    $params[":$field"] = $data[$field];
                }
            }

            if (empty($fields)) {
                return false;
            }

            $sql = "UPDATE reviews SET " . implode(', ', $fields) . " WHERE review_id = :review_id";

            $this->executeQuery($sql, $params);
            return true;
        } catch (Exception $e) {
            error_log("ReviewDAO::update() Error: " . $e->getMessage());
            return false;
        }
    }

    public function delete($reviewId) {
        try {
            $sql = "DELETE FROM reviews WHERE review_id = :review_id";
            $this->executeQuery($sql, [':review_id' => $reviewId]);
            return true;
        } catch (Exception $e) {
            error_log("ReviewDAO::delete() Error: " . $e->getMessage());
            return false;
        }
    }

    public function deleteByUserId($userId) {
        try {
            $sql = "DELETE FROM reviews WHERE user_id = :user_id";
            $this->executeQuery($sql, [':user_id' => $userId]);
            return true;
        } catch (Exception $e) {
            error_log("ReviewDAO::deleteByUserId() Error: " . $e->getMessage());
            return false;
        }
    }

    public function deleteByAlbumId($albumId) {
        try {
            $sql = "DELETE FROM reviews WHERE album_id = :album_id";
            $this->executeQuery($sql, [':album_id' => $albumId]);
            return true;
        } catch (Exception $e) {
            error_log("ReviewDAO::deleteByAlbumId() Error: " . $e->getMessage());
            return false;
        }
    }

    public function getMostReviewedAlbums($limit = 10) {
        try {
            $sql = "SELECT a.album_id, a.title, a.artist, a.cover_url,
                           COUNT(r.review_id) as review_count
                    FROM albums a
                    INNER JOIN reviews r ON a.album_id = r.album_id
                    GROUP BY a.album_id
                    ORDER BY review_count DESC
                    LIMIT :limit";

            $stmt = $this->connection->prepare($sql);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("ReviewDAO::getMostReviewedAlbums() Error: " . $e->getMessage());
            return [];
        }
    }
}
?>
