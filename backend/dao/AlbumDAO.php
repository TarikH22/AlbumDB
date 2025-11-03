<?php

require_once __DIR__ . '/BaseDAO.php';

class AlbumDAO extends BaseDAO {

    public function __construct() {
        parent::__construct();
        $this->tableName = 'albums';
    }


    public function create($data) {
        try {
            $sql = "INSERT INTO albums (title, artist, genre, year, label, cover_url, description, total_duration)
                    VALUES (:title, :artist, :genre, :year, :label, :cover_url, :description, :total_duration)";

            $params = [
                ':title' => $data['title'],
                ':artist' => $data['artist'],
                ':genre' => $data['genre'],
                ':year' => $data['year'],
                ':label' => $data['label'] ?? null,
                ':cover_url' => $data['cover_url'] ?? null,
                ':description' => $data['description'] ?? null,
                ':total_duration' => $data['total_duration'] ?? null
            ];

            $this->executeQuery($sql, $params);
            return $this->getLastInsertId();
        } catch (Exception $e) {
            error_log("AlbumDAO::create() Error: " . $e->getMessage());
            return false;
        }
    }

    
    public function getById($albumId) {
        try {
            $sql = "SELECT album_id, title, artist, genre, year, label, cover_url,
                           description, total_duration, created_at, updated_at
                    FROM albums WHERE album_id = ?";

            $stmt = $this->executeQuery($sql, [$albumId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("AlbumDAO::getById() Error: " . $e->getMessage());
            return false;
        }
    }

    
    public function getByIdWithStats($albumId) {
        try {
            $sql = "SELECT a.*,
                           COALESCE(AVG(r.rating), 0) as avg_rating,
                           COUNT(DISTINCT r.rating_id) as rating_count,
                           COUNT(DISTINCT rev.review_id) as review_count
                    FROM albums a
                    LEFT JOIN ratings r ON a.album_id = r.album_id
                    LEFT JOIN reviews rev ON a.album_id = rev.album_id
                    WHERE a.album_id = ?
                    GROUP BY a.album_id";

            $stmt = $this->executeQuery($sql, [$albumId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("AlbumDAO::getByIdWithStats() Error: " . $e->getMessage());
            return false;
        }
    }

    
    public function getAll($limit = null, $offset = 0) {
        try {
            $sql = "SELECT album_id, title, artist, genre, year, label, cover_url,
                           description, total_duration, created_at, updated_at
                    FROM albums
                    ORDER BY created_at DESC";

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
            error_log("AlbumDAO::getAll() Error: " . $e->getMessage());
            return [];
        }
    }


    public function getAllWithStats($limit = null, $offset = 0) {
        try {
            $sql = "SELECT a.*,
                           COALESCE(AVG(r.rating), 0) as avg_rating,
                           COUNT(DISTINCT r.rating_id) as rating_count,
                           COUNT(DISTINCT rev.review_id) as review_count
                    FROM albums a
                    LEFT JOIN ratings r ON a.album_id = r.album_id
                    LEFT JOIN reviews rev ON a.album_id = rev.album_id
                    GROUP BY a.album_id
                    ORDER BY a.created_at DESC";

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
            error_log("AlbumDAO::getAllWithStats() Error: " . $e->getMessage());
            return [];
        }
    }

    
    public function getByGenre($genre) {
        try {
            $sql = "SELECT a.*,
                           COALESCE(AVG(r.rating), 0) as avg_rating,
                           COUNT(DISTINCT r.rating_id) as rating_count
                    FROM albums a
                    LEFT JOIN ratings r ON a.album_id = r.album_id
                    WHERE a.genre = ?
                    GROUP BY a.album_id
                    ORDER BY a.year DESC";

            $stmt = $this->executeQuery($sql, [$genre]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("AlbumDAO::getByGenre() Error: " . $e->getMessage());
            return [];
        }
    }

    
    public function getByArtist($artist) {
        try {
            $sql = "SELECT a.*,
                           COALESCE(AVG(r.rating), 0) as avg_rating,
                           COUNT(DISTINCT r.rating_id) as rating_count
                    FROM albums a
                    LEFT JOIN ratings r ON a.album_id = r.album_id
                    WHERE a.artist LIKE ?
                    GROUP BY a.album_id
                    ORDER BY a.year DESC";

            $stmt = $this->executeQuery($sql, ["%$artist%"]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("AlbumDAO::getByArtist() Error: " . $e->getMessage());
            return [];
        }
    }

    
    public function getByYear($year) {
        try {
            $sql = "SELECT a.*,
                           COALESCE(AVG(r.rating), 0) as avg_rating,
                           COUNT(DISTINCT r.rating_id) as rating_count
                    FROM albums a
                    LEFT JOIN ratings r ON a.album_id = r.album_id
                    WHERE a.year = ?
                    GROUP BY a.album_id
                    ORDER BY a.title";

            $stmt = $this->executeQuery($sql, [$year]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("AlbumDAO::getByYear() Error: " . $e->getMessage());
            return [];
        }
    }

   
    public function search($searchTerm) {
        try {
            $sql = "SELECT a.*,
                           COALESCE(AVG(r.rating), 0) as avg_rating,
                           COUNT(DISTINCT r.rating_id) as rating_count
                    FROM albums a
                    LEFT JOIN ratings r ON a.album_id = r.album_id
                    WHERE a.title LIKE :search OR a.artist LIKE :search
                    GROUP BY a.album_id
                    ORDER BY a.title";

            $stmt = $this->executeQuery($sql, [':search' => "%$searchTerm%"]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("AlbumDAO::search() Error: " . $e->getMessage());
            return [];
        }
    }

   
    public function getTopRated($limit = 10) {
        try {
            $sql = "SELECT a.*,
                           AVG(r.rating) as avg_rating,
                           COUNT(r.rating_id) as rating_count
                    FROM albums a
                    INNER JOIN ratings r ON a.album_id = r.album_id
                    GROUP BY a.album_id
                    HAVING rating_count >= 1
                    ORDER BY avg_rating DESC, rating_count DESC
                    LIMIT :limit";

            $stmt = $this->connection->prepare($sql);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("AlbumDAO::getTopRated() Error: " . $e->getMessage());
            return [];
        }
    }

    
    public function getRecent($limit = 10) {
        try {
            $sql = "SELECT a.*,
                           COALESCE(AVG(r.rating), 0) as avg_rating,
                           COUNT(DISTINCT r.rating_id) as rating_count
                    FROM albums a
                    LEFT JOIN ratings r ON a.album_id = r.album_id
                    GROUP BY a.album_id
                    ORDER BY a.created_at DESC
                    LIMIT :limit";

            $stmt = $this->connection->prepare($sql);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("AlbumDAO::getRecent() Error: " . $e->getMessage());
            return [];
        }
    }

    
    public function update($albumId, $data) {
        try {
            $fields = [];
            $params = [':album_id' => $albumId];

            $allowedFields = ['title', 'artist', 'genre', 'year', 'label', 'cover_url', 'description', 'total_duration'];

            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $fields[] = "$field = :$field";
                    $params[":$field"] = $data[$field];
                }
            }

            if (empty($fields)) {
                return false;
            }

            $sql = "UPDATE albums SET " . implode(', ', $fields) . " WHERE album_id = :album_id";

            $this->executeQuery($sql, $params);
            return true;
        } catch (Exception $e) {
            error_log("AlbumDAO::update() Error: " . $e->getMessage());
            return false;
        }
    }

   
    public function delete($albumId) {
        try {
            $sql = "DELETE FROM albums WHERE album_id = :album_id";
            $this->executeQuery($sql, [':album_id' => $albumId]);
            return true;
        } catch (Exception $e) {
            error_log("AlbumDAO::delete() Error: " . $e->getMessage());
            return false;
        }
    }

    
    public function getAllGenres() {
        try {
            $sql = "SELECT DISTINCT genre FROM albums ORDER BY genre";
            $stmt = $this->executeQuery($sql);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            error_log("AlbumDAO::getAllGenres() Error: " . $e->getMessage());
            return [];
        }
    }

   
    public function getAllArtists() {
        try {
            $sql = "SELECT DISTINCT artist FROM albums ORDER BY artist";
            $stmt = $this->executeQuery($sql);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            error_log("AlbumDAO::getAllArtists() Error: " . $e->getMessage());
            return [];
        }
    }
}
?>
