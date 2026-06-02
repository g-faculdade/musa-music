<?php

require_once __DIR__ . '/../helper/Csrf.php';
require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/MusicController.php';
require_once __DIR__ . '/SocialController.php';

class Controller
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->pdo = $pdo;
    }

    public function handleRequest(): void
    {
        $action = $_POST['action'] ?? $_GET['action'] ?? 'login';

        $authActions = ['login', 'cadastro', 'recuperar'];

        $musicActions = [
            'music', 'search', 'preview',
            'like', 'liked',
            'playlist', 'create_playlist', 'add_playlist', 'remove_playlist', 'delete_playlist',
            'download', 'downloaded', 'profile', 'save_bio',
        ];

        $socialActions = [
            'social', 'social_post', 'social_like', 'social_comment', 'social_repost', 'social_view'
        ];

        match (true) {
            in_array($action, $authActions)   => (new AuthController($this->pdo))->handle($action),
            in_array($action, $musicActions)  => (new MusicController($this->pdo))->handle($action),
            in_array($action, $socialActions) => (new SocialController($this->pdo))->handle($action),
            default                           => (new AuthController($this->pdo))->handle('login'),
        };
    }
}
