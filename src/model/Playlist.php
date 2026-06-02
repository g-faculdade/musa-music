<?php

class PlaylistRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getByUser(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT id, name FROM playlists
            WHERE user_id = ? ORDER BY id DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(int $userId, string $name): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO playlists (user_id, name) VALUES (?, ?)
        ");
        $stmt->execute([$userId, $name]);
        return (int) $this->db->lastInsertId();
    }

    public function getMusics(int $playlistId, int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT
                m.id, m.title, m.artist, m.duration, m.cover,
                COALESCE(ums.liked, 0)      AS liked,
                COALESCE(ums.downloaded, 0) AS downloaded
            FROM playlist_musics pm
            INNER JOIN musics m ON m.id = pm.music_id
            LEFT JOIN user_music_status ums
                ON ums.music_id = m.id AND ums.user_id = pm.user_id
            WHERE pm.playlist_id = ? AND pm.user_id = ?
            ORDER BY pm.music_id DESC
        ");
        $stmt->execute([$playlistId, $userId]);

        return array_map(fn($r) => [
            'id'         => $r['id'],
            'title'      => $r['title'],
            'artist'     => $r['artist'],
            'duration'   => $r['duration'],
            'cover'      => $r['cover'],
            'liked'      => (bool) $r['liked'],
            'downloaded' => (bool) $r['downloaded'],
            'playlists'  => [],
        ], $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function addMusic(int $playlistId, int $userId, array $music): bool
    {
        $this->db->prepare("
            INSERT IGNORE INTO musics (id, title, artist, duration, cover)
            VALUES (?, ?, ?, ?, ?)
        ")->execute([
            $music['id'], $music['title'], $music['artist'],
            $music['duration'], $music['cover'],
        ]);

        return $this->db->prepare("
            INSERT IGNORE INTO playlist_musics (playlist_id, music_id, user_id)
            VALUES (?, ?, ?)
        ")->execute([$playlistId, $music['id'], $userId]);
    }

    public function removeMusic(int $playlistId, int $userId, int $musicId): bool
    {
        return $this->db->prepare("
            DELETE FROM playlist_musics
            WHERE playlist_id = ? AND user_id = ? AND music_id = ?
        ")->execute([$playlistId, $userId, $musicId]);
    }

    public function delete(int $playlistId, int $userId): bool
    {
        return $this->db->prepare("
            DELETE FROM playlists WHERE id = ? AND user_id = ?
        ")->execute([$playlistId, $userId]);
    }
}
