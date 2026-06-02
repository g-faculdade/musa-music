<?php

require_once __DIR__ . '/../services/MusicService.php';
require_once __DIR__ . '/../services/Player.php';
require_once __DIR__ . '/../view/MusicView.php';

class MusicController
{
    private MusicService $service;
    private Player $player;
    private MusicView $view;

    public function __construct(PDO $pdo)
    {
        $this->service = new MusicService($pdo);
        $this->player  = new Player();
        $this->view    = new MusicView();
    }

    public function handle(string $action): void
    {
        $userId = $_SESSION['usuario_id'] ?? null;

        if (!$userId) {
            header('Location: ?action=login');
            exit;
        }

        match ($action) {
            'music'           => $this->musicFeed((int) $userId),
            'search'          => $this->search((int) $userId),
            'preview'         => $this->preview(),
            'like'            => $this->like((int) $userId),
            'liked'           => $this->likedPage((int) $userId),
            'playlist'        => $this->playlistPage((int) $userId),
            'create_playlist' => $this->createPlaylist((int) $userId),
            'add_playlist'    => $this->addToPlaylist((int) $userId),
            'remove_playlist' => $this->removeFromPlaylist((int) $userId),
            'delete_playlist' => $this->deletePlaylist((int) $userId),
            'download'        => $this->download((int) $userId),
            'downloaded'      => $this->downloadedPage((int) $userId),
            default           => $this->json(['error' => 'Ação não encontrada'], 404),
        };
    }

    private function musicFeed(int $userId): void
    {
        $this->view->home(
            $this->service->getMusicFeed($userId),
            $this->service->getUserPlaylists($userId),
            $_SESSION['usuario_nome'] ?? 'U'
        );
    }

    private function likedPage(int $userId): void
    {
        $this->view->collection(
            musics:   $this->service->getLikedMusics($userId),
            playlists: $this->service->getUserPlaylists($userId),
            userName:  $_SESSION['usuario_nome'] ?? 'U',
            title:     'Músicas Curtidas',
            icon:      '♥',
            emptyMsg:  'Você ainda não curtiu nenhuma música.'
        );
    }

    private function playlistPage(int $userId): void
    {
        $playlistId = (int) ($_GET['id'] ?? 0);
        if (!$playlistId) {
            header('Location: ?action=music');
            exit;
        }

        $playlists = $this->service->getUserPlaylists($userId);
        $found     = array_filter($playlists, fn($p) => (int)$p['id'] === $playlistId);

        if (empty($found)) {
            header('Location: ?action=music');
            exit;
        }

        $pl = array_values($found)[0];

        $this->view->collection(
            musics:    $this->service->getPlaylistMusics($playlistId, $userId),
            playlists: $playlists,
            userName:  $_SESSION['usuario_nome'] ?? 'U',
            title:     $pl['name'],
            icon:      '♪',
            emptyMsg:  'Esta playlist ainda não tem músicas.'
        );
    }

    private function downloadedPage(int $userId): void
    {
        $this->view->collection(
            musics:    $this->service->getDownloadedMusics($userId),
            playlists: $this->service->getUserPlaylists($userId),
            userName:  $_SESSION['usuario_nome'] ?? 'U',
            title:     'Músicas Baixadas',
            icon:      '↓',
            emptyMsg:  'Você ainda não baixou nenhuma música.'
        );
    }

    private function search(int $userId): void
    {
        $q = trim($_GET['q'] ?? '');
        $this->json($q !== '' ? $this->service->getSearchMusic($userId, $q) : []);
    }

    private function preview(): void
    {
        $id     = (int)   ($_GET['id']     ?? 0);
        $title  = trim($_GET['title']  ?? '');
        $artist = trim($_GET['artist'] ?? '');

        if (!$title || !$artist) {
            $this->json(['error' => 'Parâmetros inválidos'], 400);
            return;
        }

        if ($id && $localUrl = $this->player->getLocalUrl($id)) {
            $this->json(['url' => $localUrl]);
            return;
        }

        $url = $this->player->getStreamUrl($title, $artist);
        $url
            ? $this->json(['url' => $url])
            : $this->json(['error' => 'Não foi possível obter o áudio'], 404);
    }

    private function like(int $userId): void
    {
        $body    = $this->body();
        $musicId = (int) ($body['id'] ?? 0);

        if (!$musicId) {
            $this->json(['error' => 'ID inválido'], 400);
            return;
        }

        $liked = $this->service->toggleLike($userId, $musicId, [
            'title'    => $body['title']    ?? '',
            'artist'   => $body['artist']   ?? '',
            'duration' => (int) ($body['duration'] ?? 0),
            'cover'    => $body['cover']    ?? '',
        ]);

        $this->json(['liked' => $liked]);
    }

    private function createPlaylist(int $userId): void
    {
        $body = $this->body();
        $name = trim($body['name'] ?? '');

        if (!$name) {
            $this->json(['error' => 'Nome obrigatório'], 400);
            return;
        }

        $id = $this->service->createPlaylist($userId, $name);
        $this->json(['id' => $id, 'name' => $name]);
    }

    private function addToPlaylist(int $userId): void
    {
        $body       = $this->body();
        $playlistId = (int) ($body['playlist_id'] ?? 0);
        $musicId    = (int) ($body['id']          ?? 0);

        if (!$playlistId || !$musicId) {
            $this->json(['error' => 'Parâmetros inválidos'], 400);
            return;
        }

        $ok = $this->service->addToPlaylist($playlistId, $userId, [
            'id'       => $musicId,
            'title'    => $body['title']    ?? '',
            'artist'   => $body['artist']   ?? '',
            'duration' => (int) ($body['duration'] ?? 0),
            'cover'    => $body['cover']    ?? '',
        ]);

        $this->json(['ok' => $ok]);
    }

    private function removeFromPlaylist(int $userId): void
    {
        $body       = $this->body();
        $playlistId = (int) ($body['playlist_id'] ?? 0);
        $musicId    = (int) ($body['music_id']    ?? 0);

        if (!$playlistId || !$musicId) {
            $this->json(['error' => 'Parâmetros inválidos'], 400);
            return;
        }

        $this->json(['ok' => $this->service->removeFromPlaylist($playlistId, $userId, $musicId)]);
    }

    private function deletePlaylist(int $userId): void
    {
        $body       = $this->body();
        $playlistId = (int) ($body['playlist_id'] ?? 0);

        if (!$playlistId) {
            $this->json(['error' => 'ID inválido'], 400);
            return;
        }

        $this->json(['ok' => $this->service->deletePlaylist($playlistId, $userId)]);
    }

    private function download(int $userId): void
    {
        $body    = $this->body();
        $musicId = (int)  ($body['id']     ?? 0);
        $title   = trim($body['title']   ?? '');
        $artist  = trim($body['artist']  ?? '');

        if (!$musicId || !$title || !$artist) {
            $this->json(['error' => 'Parâmetros inválidos'], 400);
            return;
        }

        $url = $this->player->download($musicId, $title, $artist);

        if (!$url) {
            $this->json(['error' => 'Falha ao baixar a música'], 500);
            return;
        }

        $this->service->markDownloaded($userId, $musicId, [
            'title'    => $title,
            'artist'   => $artist,
            'duration' => (int) ($body['duration'] ?? 0),
            'cover'    => $body['cover'] ?? '',
        ]);

        $this->json(['url' => $url]);
    }

    private function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function body(): array
    {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }
}
