<?php

require_once __DIR__ . '/BaseDAO.php';

class TrackDAO extends BaseDAO {

    public function __construct() {
        parent::__construct();
        $this->tableName = 'tracks';
    }

    public function create($data) {
        try {
            $sql = "INSERT INTO tracks (album_id, track_number, title, duration)
                    VALUES (:album_id, :track_number, :title, :duration)";

            $params = [
                ':album_id' => $data['album_id'],
                ':track_number' => $data['track_number'],
                ':title' => $data['title'],
                ':duration' => $data['duration']
            ];

            $this->executeQuery($sql, $params);
            return $this->getLastInsertId();
        } catch (Exception $e) {
            error_log("TrackDAO::create() Error: " . $e->getMessage());
            return false;
        }
    }

    public function createMultiple($albumId, $tracks) {
        try {
            $this->beginTransaction();

            foreach ($tracks as $track) {
                $track['album_id'] = $albumId;
                $result = $this->create($track);

                if (!$result) {
                    $this->rollback();
                    return false;
                }
            }

            $this->commit();
            return true;
        } catch (Exception $e) {
            $this->rollback();
            error_log("TrackDAO::createMultiple() Error: " . $e->getMessage());
            return false;
        }
    }

    public function getById($trackId) {
        try {
            $sql = "SELECT track_id, album_id, track_number, title, duration
                    FROM tracks WHERE track_id = ?";

            $stmt = $this->executeQuery($sql, [$trackId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("TrackDAO::getById() Error: " . $e->getMessage());
            return false;
        }
    }

    public function getByAlbumId($albumId) {
        try {
            $sql = "SELECT track_id, album_id, track_number, title, duration
                    FROM tracks
                    WHERE album_id = ?
                    ORDER BY track_number ASC";

            $stmt = $this->executeQuery($sql, [$albumId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("TrackDAO::getByAlbumId() Error: " . $e->getMessage());
            return [];
        }
    }

    public function getByIdWithAlbum($trackId) {
        try {
            $sql = "SELECT t.*, a.title as album_title, a.artist, a.year, a.cover_url
                    FROM tracks t
                    INNER JOIN albums a ON t.album_id = a.album_id
                    WHERE t.track_id = ?";

            $stmt = $this->executeQuery($sql, [$trackId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("TrackDAO::getByIdWithAlbum() Error: " . $e->getMessage());
            return false;
        }
    }

    public function getAll($limit = null, $offset = 0) {
        try {
            $sql = "SELECT track_id, album_id, track_number, title, duration
                    FROM tracks
                    ORDER BY album_id, track_number";

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
            error_log("TrackDAO::getAll() Error: " . $e->getMessage());
            return [];
        }
    }

    public function search($searchTerm) {
        try {
            $sql = "SELECT t.*, a.title as album_title, a.artist, a.cover_url
                    FROM tracks t
                    INNER JOIN albums a ON t.album_id = a.album_id
                    WHERE t.title LIKE :search
                    ORDER BY a.artist, a.title, t.track_number";

            $stmt = $this->executeQuery($sql, [':search' => "%$searchTerm%"]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("TrackDAO::search() Error: " . $e->getMessage());
            return [];
        }
    }

    public function getTrackCountByAlbum($albumId) {
        try {
            $sql = "SELECT COUNT(*) FROM tracks WHERE album_id = ?";
            $stmt = $this->executeQuery($sql, [$albumId]);
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("TrackDAO::getTrackCountByAlbum() Error: " . $e->getMessage());
            return 0;
        }
    }

    public function update($trackId, $data) {
        try {
            $fields = [];
            $params = [':track_id' => $trackId];

            $allowedFields = ['track_number', 'title', 'duration'];

            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $fields[] = "$field = :$field";
                    $params[":$field"] = $data[$field];
                }
            }

            if (empty($fields)) {
                return false;
            }

            $sql = "UPDATE tracks SET " . implode(', ', $fields) . " WHERE track_id = :track_id";

            $this->executeQuery($sql, $params);
            return true;
        } catch (Exception $e) {
            error_log("TrackDAO::update() Error: " . $e->getMessage());
            return false;
        }
    }

    public function delete($trackId) {
        try {
            $sql = "DELETE FROM tracks WHERE track_id = :track_id";
            $this->executeQuery($sql, [':track_id' => $trackId]);
            return true;
        } catch (Exception $e) {
            error_log("TrackDAO::delete() Error: " . $e->getMessage());
            return false;
        }
    }

    public function deleteByAlbumId($albumId) {
        try {
            $sql = "DELETE FROM tracks WHERE album_id = :album_id";
            $this->executeQuery($sql, [':album_id' => $albumId]);
            return true;
        } catch (Exception $e) {
            error_log("TrackDAO::deleteByAlbumId() Error: " . $e->getMessage());
            return false;
        }
    }

    public function getTotalDuration($albumId) {
        try {
            $tracks = $this->getByAlbumId($albumId);
            $totalSeconds = 0;

            foreach ($tracks as $track) {
                // Parse duration (format: "MM:SS")
                $parts = explode(':', $track['duration']);
                if (count($parts) === 2) {
                    $totalSeconds += (int)$parts[0] * 60 + (int)$parts[1];
                }
            }

            $hours = floor($totalSeconds / 3600);
            $minutes = floor(($totalSeconds % 3600) / 60);
            $seconds = $totalSeconds % 60;

            if ($hours > 0) {
                return sprintf("%d:%02d:%02d", $hours, $minutes, $seconds);
            } else {
                return sprintf("%d:%02d", $minutes, $seconds);
            }
        } catch (Exception $e) {
            error_log("TrackDAO::getTotalDuration() Error: " . $e->getMessage());
            return "0:00";
        }
    }
}
?>
