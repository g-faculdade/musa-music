<?php

require_once __DIR__ . '/Deezer.php';
require_once __DIR__ . '/../model/Music.php';
require_once __DIR__ . '/../model/Playlist.php';

class MusicService
{
    private DeezerClient $deezer;
    private UserMusicRepository $musicRepo;
    private PlaylistRepository $playlistRepo;

    public function __construct(PDO $pdo)
    {
        $this->deezer       = new DeezerClient();
        $this->musicRepo    = new UserMusicRepository();
        $this->playlistRepo = new PlaylistRepository($pdo);
    }

    public function getMusicFeed(int $userId): array
    {
        return $this->buildMusic($userId, $this->deezer->getPopular());
    }

    public function getSearchMusic(int $userId, string $q): array
    {
        return $this->buildMusic($userId, $this->deezer->getSearch($q));
    }

    public function getUserPlaylists(int $userId): array
    {
        return $this->playlistRepo->getByUser($userId);
    }

    public function getPlaylistMusics(int $playlistId, int $userId): array
    {
        return $this->playlistRepo->getMusics($playlistId, $userId);
    }

    public function getLikedMusics(int $userId): array
    {
        return $this->musicRepo->getLiked($userId);
    }

    public function getDownloadedMusics(int $userId): array
    {
        return $this->musicRepo->getDownloaded($userId);
    }

    public function createPlaylist(int $userId, string $name): int
    {
        return $this->playlistRepo->create($userId, $name);
    }

    public function addToPlaylist(int $playlistId, int $userId, array $music): bool
    {
        return $this->playlistRepo->addMusic($playlistId, $userId, $music);
    }

    public function removeFromPlaylist(int $playlistId, int $userId, int $musicId): bool
    {
        return $this->playlistRepo->removeMusic($playlistId, $userId, $musicId);
    }

    public function deletePlaylist(int $playlistId, int $userId): bool
    {
        return $this->playlistRepo->delete($playlistId, $userId);
    }

    public function toggleLike(int $userId, int $musicId, array $musicData): bool
    {
        return $this->musicRepo->toggleLike($userId, $musicId, $musicData);
    }

    public function markDownloaded(int $userId, int $musicId, array $musicData): void
    {
        $this->musicRepo->markDownloaded($userId, $musicId, $musicData);
    }

    private function formatter(array $musics): array
    {
        $result = [];
        $ids    = [];

        foreach ($musics as $music) {
            $result[] = [
                'id'       => $music['id'],
                'title'    => $music['title'],
                'artist'   => $music['artist']['name'],
                'duration' => $music['duration'],
                'cover'    => $music['album']['cover'],
            ];
            $ids[] = $music['id'];
        }

        return ['result' => $result, 'ids' => $ids];
    }

    private function buildMusic(int $userId, array $musicsApi): array
    {
        $formatted = $this->formatter($musicsApi);
        $apiResult = $formatted['result'];
        $apiIds    = $formatted['ids'];

        $musicsDb = $this->musicRepo->getMusicStatus($userId, $apiIds);

        foreach ($apiResult as &$music) {
            $id = $music['id'];
            $music['liked']      = $musicsDb[$id]['liked']      ?? false;
            $music['downloaded'] = $musicsDb[$id]['downloaded'] ?? false;
            $music['playlists']  = $musicsDb[$id]['playlists']  ?? [];
        }
        unset($music);

        return $apiResult;
    }
}
