<?php

require_once __DIR__ . '/../model/SocialRepository.php';
require_once __DIR__ . '/../services/MusicService.php';
require_once __DIR__ . '/../view/SocialView.php';

class SocialController
{
    private SocialRepository $socialRepo;
    private MusicService $musicService;
    private SocialView $view;

    public function __construct(PDO $pdo)
    {
        $this->socialRepo   = new SocialRepository();
        $this->musicService = new MusicService($pdo);
        $this->view         = new SocialView();
    }

    public function handle(string $action): void
    {
        $userId = $_SESSION['usuario_id'] ?? null;

        if (!$userId) {
            header('Location: ?action=login');
            exit;
        }

        match ($action) {
            'social'         => $this->feed((int) $userId),
            'social_post'    => $this->createPost((int) $userId),
            'social_like'    => $this->toggleLike((int) $userId),
            'social_comment' => $this->createComment((int) $userId),
            'social_repost'  => $this->createRepost((int) $userId),
            'social_view'    => $this->viewPost((int) $userId),
            default          => $this->json(['error' => 'Ação não encontrada'], 404),
        };
    }

    private function feed(int $userId): void
    {
        $feed      = $this->socialRepo->getFeed($userId);
        $liked     = $this->musicService->getLikedMusics($userId);
        $playlists = $this->musicService->getUserPlaylists($userId);
        $userName  = $_SESSION['usuario_nome'] ?? 'U';

        $this->view->renderFeed($feed, $liked, $playlists, $userName);
    }

    private function createPost(int $userId): void
    {
        $body = $this->body();
        $content = trim($body['conteudo'] ?? $_POST['conteudo'] ?? '');
        $musicId = !empty($body['musica_id']) ? (int)$body['musica_id'] : (!empty($_POST['musica_id']) ? (int)$_POST['musica_id'] : null);
        $playlistId = !empty($body['playlist_id']) ? (int)$body['playlist_id'] : (!empty($_POST['playlist_id']) ? (int)$_POST['playlist_id'] : null);

        if ($content === '') {
            $this->json(['error' => 'O conteúdo do post não pode ser vazio.'], 400);
            return;
        }

        $postId = $this->socialRepo->createPost($userId, $content, $musicId, $playlistId);

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) || isset($_GET['ajax']) || !empty($body)) {
            $this->json(['success' => true, 'post_id' => $postId]);
        } else {
            header('Location: ?action=social');
            exit;
        }
    }

    private function toggleLike(int $userId): void
    {
        $body   = $this->body();
        $postId = (int)($body['post_id'] ?? $_POST['post_id'] ?? 0);

        if (!$postId) {
            $this->json(['error' => 'ID do post inválido.'], 400);
            return;
        }

        $liked = $this->socialRepo->toggleLike($userId, $postId);
        
        // Buscar contagem atualizada
        $postDetails = $this->socialRepo->getPostDetails($postId, $userId);

        $this->json([
            'liked'       => $liked,
            'likes_count' => $postDetails['likes_count'] ?? 0
        ]);
    }

    private function createComment(int $userId): void
    {
        $body    = $this->body();
        $postId  = (int)($body['post_id'] ?? $_POST['post_id'] ?? 0);
        $content = trim($body['conteudo'] ?? $_POST['conteudo'] ?? '');

        if (!$postId || $content === '') {
            $this->json(['error' => 'Parâmetros de comentário inválidos.'], 400);
            return;
        }

        $this->socialRepo->createComment($postId, $userId, $content);

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) || isset($_GET['ajax']) || !empty($body)) {
            $this->json(['success' => true]);
        } else {
            header("Location: ?action=social_view&id=$postId");
            exit;
        }
    }

    private function createRepost(int $userId): void
    {
        $body   = $this->body();
        $postId = (int)($body['post_id'] ?? $_POST['post_id'] ?? 0);

        if (!$postId) {
            $this->json(['error' => 'ID do post original inválido.'], 400);
            return;
        }

        // Criar o repost
        $repostId = $this->socialRepo->createPost($userId, '', null, null, $postId);

        $this->json(['success' => true, 'repost_id' => $repostId]);
    }

    private function viewPost(int $userId): void
    {
        $postId = (int)($_GET['id'] ?? 0);

        if (!$postId) {
            header('Location: ?action=social');
            exit;
        }

        $post = $this->socialRepo->getPostDetails($postId, $userId);

        if (!$post) {
            header('Location: ?action=social');
            exit;
        }

        $playlists = $this->musicService->getUserPlaylists($userId);
        $userName  = $_SESSION['usuario_nome'] ?? 'U';

        $this->view->renderPostDetail($post, $playlists, $userName);
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
