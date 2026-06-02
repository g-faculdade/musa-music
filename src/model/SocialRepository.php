<?php

require_once __DIR__ . '/../database/Conexao.php';

class SocialRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Conexao::getInstance();
    }

    public function createPost(int $userId, string $content, ?int $musicId, ?int $playlistId, ?int $originalPostId = null): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO posts (usuario_id, conteudo, musica_id, playlist_id, original_post_id)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $userId,
            $content,
            $musicId ?: null,
            $playlistId ?: null,
            $originalPostId ?: null
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function getFeed(int $userId): array
    {
        // 1. Buscar todas as postagens cronologicamente (decrescente)
        $stmt = $this->db->prepare("
            SELECT 
                p.id,
                p.usuario_id,
                u.nome AS autor_nome,
                u.tipo AS autor_tipo,
                p.conteudo,
                p.musica_id,
                p.playlist_id,
                p.original_post_id,
                p.created_at,
                m.title AS musica_titulo,
                m.artist AS musica_artista,
                m.cover AS musica_capa,
                m.duration AS musica_duracao,
                pl.name AS playlist_nome,
                orig_p.conteudo AS orig_conteudo,
                orig_u.nome AS orig_autor_nome,
                orig_u.id AS orig_autor_id,
                orig_u.tipo AS orig_autor_tipo,
                orig_p.musica_id AS orig_musica_id,
                orig_p.playlist_id AS orig_playlist_id,
                orig_m.title AS orig_musica_titulo,
                orig_m.artist AS orig_musica_artista,
                orig_m.cover AS orig_musica_capa,
                orig_m.duration AS orig_musica_duracao,
                orig_pl.name AS orig_playlist_nome
            FROM posts p
            INNER JOIN usuarios u ON u.id = p.usuario_id
            LEFT JOIN musics m ON m.id = p.musica_id
            LEFT JOIN playlists pl ON pl.id = p.playlist_id
            
            -- Joins para posts originais se for um repost
            LEFT JOIN posts orig_p ON orig_p.id = p.original_post_id
            LEFT JOIN usuarios orig_u ON orig_u.id = orig_p.usuario_id
            LEFT JOIN musics orig_m ON orig_m.id = orig_p.musica_id
            LEFT JOIN playlists orig_pl ON orig_pl.id = orig_p.playlist_id
            
            ORDER BY p.id DESC
        ");
        $stmt->execute();
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($posts as &$post) {
            $postId = (int)$post['id'];

            // 2. Obter contagem de likes e status de "curtido" pelo usuário logado
            $stmtLike = $this->db->prepare("
                SELECT COUNT(*) AS total, 
                       SUM(CASE WHEN usuario_id = ? THEN 1 ELSE 0 END) AS liked
                FROM post_likes
                WHERE post_id = ?
            ");
            $stmtLike->execute([$userId, $postId]);
            $likeInfo = $stmtLike->fetch(PDO::FETCH_ASSOC);
            $post['likes_count'] = (int)$likeInfo['total'];
            $post['liked'] = $likeInfo['liked'] > 0;

            // 3. Obter contagem total de comentários
            $stmtCountComm = $this->db->prepare("
                SELECT COUNT(*) FROM post_comments WHERE post_id = ?
            ");
            $stmtCountComm->execute([$postId]);
            $post['comments_count'] = (int)$stmtCountComm->fetchColumn();

            // 4. Obter apenas os top 3 comentários
            $stmtComm = $this->db->prepare("
                SELECT c.id, c.conteudo, c.created_at, u.nome AS autor_nome, u.tipo AS autor_tipo
                FROM post_comments c
                INNER JOIN usuarios u ON u.id = c.usuario_id
                WHERE c.post_id = ?
                ORDER BY c.id ASC
                LIMIT 3
            ");
            $stmtComm->execute([$postId]);
            $post['top_comments'] = $stmtComm->fetchAll(PDO::FETCH_ASSOC);
        }
        unset($post);

        return $posts;
    }

    public function getPostDetails(int $postId, int $userId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT 
                p.id,
                p.usuario_id,
                u.nome AS autor_nome,
                u.tipo AS autor_tipo,
                p.conteudo,
                p.musica_id,
                p.playlist_id,
                p.original_post_id,
                p.created_at,
                m.title AS musica_titulo,
                m.artist AS musica_artista,
                m.cover AS musica_capa,
                m.duration AS musica_duracao,
                pl.name AS playlist_nome,
                orig_p.conteudo AS orig_conteudo,
                orig_u.nome AS orig_autor_nome,
                orig_u.id AS orig_autor_id,
                orig_u.tipo AS orig_autor_tipo,
                orig_p.musica_id AS orig_musica_id,
                orig_p.playlist_id AS orig_playlist_id,
                orig_m.title AS orig_musica_titulo,
                orig_m.artist AS orig_musica_artista,
                orig_m.cover AS orig_musica_capa,
                orig_m.duration AS orig_musica_duracao,
                orig_pl.name AS orig_playlist_nome
            FROM posts p
            INNER JOIN usuarios u ON u.id = p.usuario_id
            LEFT JOIN musics m ON m.id = p.musica_id
            LEFT JOIN playlists pl ON pl.id = p.playlist_id
            
            -- Joins para posts originais se for um repost
            LEFT JOIN posts orig_p ON orig_p.id = p.original_post_id
            LEFT JOIN usuarios orig_u ON orig_u.id = orig_p.usuario_id
            LEFT JOIN musics orig_m ON orig_m.id = orig_p.musica_id
            LEFT JOIN playlists orig_pl ON orig_pl.id = orig_p.playlist_id
            
            WHERE p.id = ?
        ");
        $stmt->execute([$postId]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$post) return null;

        // Obter contagem de likes e status de "curtido" pelo usuário logado
        $stmtLike = $this->db->prepare("
            SELECT COUNT(*) AS total, 
                   SUM(CASE WHEN usuario_id = ? THEN 1 ELSE 0 END) AS liked
            FROM post_likes
            WHERE post_id = ?
        ");
        $stmtLike->execute([$userId, $postId]);
        $likeInfo = $stmtLike->fetch(PDO::FETCH_ASSOC);
        $post['likes_count'] = (int)$likeInfo['total'];
        $post['liked'] = $likeInfo['liked'] > 0;

        // Obter todos os comentários cronologicamente
        $stmtComm = $this->db->prepare("
            SELECT c.id, c.conteudo, c.created_at, u.nome AS autor_nome, u.tipo AS autor_tipo
            FROM post_comments c
            INNER JOIN usuarios u ON u.id = c.usuario_id
            WHERE c.post_id = ?
            ORDER BY c.id ASC
        ");
        $stmtComm->execute([$postId]);
        $post['comments'] = $stmtComm->fetchAll(PDO::FETCH_ASSOC);
        $post['comments_count'] = count($post['comments']);

        return $post;
    }

    public function toggleLike(int $userId, int $postId): bool
    {
        $stmt = $this->db->prepare("
            SELECT 1 FROM post_likes WHERE post_id = ? AND usuario_id = ?
        ");
        $stmt->execute([$postId, $userId]);
        $liked = $stmt->fetch();

        if ($liked) {
            $stmtDel = $this->db->prepare("
                DELETE FROM post_likes WHERE post_id = ? AND usuario_id = ?
            ");
            $stmtDel->execute([$postId, $userId]);
            return false; // unliked
        } else {
            $stmtIns = $this->db->prepare("
                INSERT INTO post_likes (post_id, usuario_id) VALUES (?, ?)
            ");
            $stmtIns->execute([$postId, $userId]);
            return true; // liked
        }
    }

    public function createComment(int $postId, int $userId, string $content): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO post_comments (post_id, usuario_id, conteudo)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$postId, $userId, $content]);
        return (int) $this->db->lastInsertId();
    }
}
