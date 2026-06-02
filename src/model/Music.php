<?php

require_once __DIR__ . '/../database/Conexao.php';

class UserMusicRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Conexao::getInstance();
    }

    public function getMusicStatus(int $userId, array $musicIds): array
    {
        if (empty($musicIds)) return [];

        $placeholders = implode(',', array_fill(0, count($musicIds), '?'));

        $stmt = $this->db->prepare("
            SELECT
                ums.music_id,
                ums.liked,
                ums.downloaded,
                p.id   AS playlist_id,
                p.name AS playlist_name
            FROM user_music_status ums
            LEFT JOIN playlist_musics pm
                ON pm.music_id = ums.music_id
               AND pm.user_id  = ums.user_id
            LEFT JOIN playlists p ON p.id = pm.playlist_id
            WHERE ums.user_id = ?
              AND ums.music_id IN ($placeholders)
        ");
        $stmt->execute(array_merge([$userId], $musicIds));

        $result = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $mid = $row['music_id'];
            if (!isset($result[$mid])) {
                $result[$mid] = [
                    'liked'      => (bool) $row['liked'],
                    'downloaded' => (bool) $row['downloaded'],
                    'playlists'  => [],
                ];
            }
            if (!empty($row['playlist_id'])) {
                $result[$mid]['playlists'][] = [
                    'id'   => $row['playlist_id'],
                    'name' => $row['playlist_name'],
                ];
            }
        }

        return $result;
    }

    public function getLiked(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT
                m.id, m.title, m.artist, m.duration, m.cover,
                1 AS liked,
                ums.downloaded,
                0 AS in_playlist
            FROM user_music_status ums
            INNER JOIN musics m ON m.id = ums.music_id
            WHERE ums.user_id = ? AND ums.liked = 1
            ORDER BY ums.music_id DESC
        ");
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($r) => [
            'id'         => $r['id'],
            'title'      => $r['title'],
            'artist'     => $r['artist'],
            'duration'   => $r['duration'],
            'cover'      => $r['cover'],
            'liked'      => true,
            'downloaded' => (bool) $r['downloaded'],
            'playlists'  => [],
        ], $rows);
    }


    public function getDownloaded(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT
                m.id, m.title, m.artist, m.duration, m.cover,
                COALESCE(ums.liked, 0) AS liked,
                1 AS downloaded
            FROM user_music_status ums
            INNER JOIN musics m ON m.id = ums.music_id
            WHERE ums.user_id = ? AND ums.downloaded = 1
            ORDER BY ums.music_id DESC
        ");
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($r) => [
            'id'         => $r['id'],
            'title'      => $r['title'],
            'artist'     => $r['artist'],
            'duration'   => $r['duration'],
            'cover'      => $r['cover'],
            'liked'      => (bool) $r['liked'],
            'downloaded' => true,
            'playlists'  => [],
        ], $rows);
    }

    public function toggleLike(int $userId, int $musicId, array $musicData): bool
    {
        $this->db->prepare("
            INSERT IGNORE INTO musics (id, title, artist, duration, cover)
            VALUES (?, ?, ?, ?, ?)
        ")->execute([
            $musicId,
            $musicData['title'],
            $musicData['artist'],
            $musicData['duration'],
            $musicData['cover'],
        ]);

        $stmt = $this->db->prepare("
            SELECT liked FROM user_music_status
            WHERE user_id = ? AND music_id = ?
        ");
        $stmt->execute([$userId, $musicId]);
        $row = $stmt->fetch();

        if ($row === false) {
            $this->db->prepare("
                INSERT INTO user_music_status (user_id, music_id, liked, downloaded)
                VALUES (?, ?, 1, 0)
            ")->execute([$userId, $musicId]);
            return true;
        }

        $newLiked = $row['liked'] ? 0 : 1;
        $this->db->prepare("
            UPDATE user_music_status SET liked = ?
            WHERE user_id = ? AND music_id = ?
        ")->execute([$newLiked, $userId, $musicId]);

        return (bool) $newLiked;
    }

    public function markDownloaded(int $userId, int $musicId, array $musicData): void
    {
        $this->db->prepare("
            INSERT IGNORE INTO musics (id, title, artist, duration, cover)
            VALUES (?, ?, ?, ?, ?)
        ")->execute([
            $musicId,
            $musicData['title'],
            $musicData['artist'],
            $musicData['duration'],
            $musicData['cover'],
        ]);

        $this->db->prepare("
            INSERT INTO user_music_status (user_id, music_id, liked, downloaded)
            VALUES (?, ?, 0, 1)
            ON DUPLICATE KEY UPDATE downloaded = 1
        ")->execute([$userId, $musicId]);
    }

    public function registerPlay(int $userId, int $musicId, array $musicData): void
    {
        if (!$musicId || empty($musicData['title']) || empty($musicData['artist'])) {
            return;
        }

        $this->db->prepare("
            INSERT IGNORE INTO musics (id, title, artist, duration, cover)
            VALUES (?, ?, ?, ?, ?)
        ")->execute([
            $musicId,
            $musicData['title'],
            $musicData['artist'],
            (int) ($musicData['duration'] ?? 0),
            $musicData['cover'] ?? '',
        ]);

        $this->db->prepare("
            INSERT INTO user_music_status (user_id, music_id, liked, downloaded, played_at)
            VALUES (?, ?, 0, 0, CURRENT_TIMESTAMP)
            ON DUPLICATE KEY UPDATE played_at = CURRENT_TIMESTAMP
        ")->execute([$userId, $musicId]);
    }

    public function getRecentlyPlayed(int $userId, int $limit = 6): array
    {
        $stmt = $this->db->prepare("
            SELECT
                m.id, m.title, m.artist, m.duration, m.cover,
                COALESCE(ums.liked, 0) AS liked,
                COALESCE(ums.downloaded, 0) AS downloaded
            FROM user_music_status ums
            INNER JOIN musics m ON m.id = ums.music_id
            WHERE ums.user_id = ? AND ums.played_at IS NOT NULL
            ORDER BY ums.played_at DESC
            LIMIT ?
        ");
        $stmt->execute([$userId, $limit]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($r) => [
            'id'         => $r['id'],
            'title'      => $r['title'],
            'artist'     => $r['artist'],
            'duration'   => $r['duration'],
            'cover'      => $r['cover'],
            'liked'      => (bool) $r['liked'],
            'downloaded' => (bool) $r['downloaded'],
            'playlists'  => [],
        ], $rows);
    }
}
