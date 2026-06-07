<?php

require_once __DIR__ . '/MusicView.php';

class SocialView extends MusicView
{
    public function renderFeed(array $feed, array $likedSongs, array $playlists, string $userName): void
    {
        $this->layout(
            title:     'Social — Musa Music',
            playlists: $playlists,
            userName:  $userName,
            activeNav: 'social',
            content:   function() use ($feed, $likedSongs, $playlists) {
                ?>
                <h2 class="section-title">☰ Musa Social</h2>
                
                <section class="social-composer-card">
                    <form id="social-post-form" class="social-form">
                        <div class="composer-header">
                            <div class="composer-avatar">
                                <span><?= htmlspecialchars(strtoupper(mb_substr($_SESSION['usuario_nome'] ?? 'U', 0, 1))) ?></span>
                            </div>
                            <textarea id="post-content" name="conteudo" placeholder="O que você está ouvindo agora?" required autocomplete="off"></textarea>
                        </div>
                        <div class="composer-footer">
                            <div class="attachment-selector">
                                <label for="post-attachment">Anexar:</label>
                                <select id="post-attachment" name="attachment" class="social-select">
                                    <option value="">Nenhum anexo</option>
                                    
                                    <?php if (!empty($likedSongs)): ?>
                                        <optgroup label="Músicas Curtidas">
                                            <?php foreach ($likedSongs as $song): ?>
                                                <option value="music_<?= $song['id'] ?>" 
                                                        data-title="<?= htmlspecialchars($song['title']) ?>"
                                                        data-artist="<?= htmlspecialchars($song['artist']) ?>"
                                                        data-cover="<?= htmlspecialchars($song['cover']) ?>"
                                                        data-duration="<?= $song['duration'] ?>">
                                                    🎵 <?= htmlspecialchars($song['title']) ?> - <?= htmlspecialchars($song['artist']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php endif; ?>

                                    <?php if (!empty($playlists)): ?>
                                        <optgroup label="Playlists Criadas">
                                            <?php foreach ($playlists as $pl): ?>
                                                <option value="playlist_<?= $pl['id'] ?>"
                                                        data-name="<?= htmlspecialchars($pl['name']) ?>">
                                                    📁 <?= htmlspecialchars($pl['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn-search">Postar</button>
                        </div>
                    </form>
                </section>

                <section class="social-feed" id="social-feed-list">
                    <?php if (empty($feed)): ?>
                        <div class="empty"><p>Nenhum post no feed ainda. Seja o primeiro a postar!</p></div>
                    <?php else: ?>
                        <?php foreach ($feed as $post): ?>
                            <?= $this->renderPostCard($post) ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </section>
                <?php
            }
        );
    }

    public function renderPostDetail(array $post, array $playlists, string $userName): void
    {
        $this->layout(
            title:     'Post de ' . htmlspecialchars($post['autor_nome']) . ' — Musa Music',
            playlists: $playlists,
            userName:  $userName,
            activeNav: 'social',
            content:   function() use ($post) {
                ?>
                <div class="back-row">
                    <a href="?action=social" class="btn-back">← Voltar para o Social</a>
                </div>

                <section class="social-detail-container">
                    <?= $this->renderPostCard($post, true) ?>

                    <div class="comment-composer">
                        <h3 class="composer-title">Responder</h3>
                        <form id="social-comment-form" data-post-id="<?= $post['id'] ?>" class="comment-form-row">
                            <input type="text" id="comment-content" placeholder="Escreva sua resposta..." required autocomplete="off">
                            <button type="submit" class="btn-search">Responder</button>
                        </form>
                    </div>

                    <div class="comments-section-full">
                        <h3 class="composer-title">Respostas (<?= $post['comments_count'] ?>)</h3>
                        <div class="comments-list-full">
                            <?php if (empty($post['comments'])): ?>
                                <p class="empty-comments">Nenhuma resposta ainda. Seja o primeiro a responder!</p>
                            <?php else: ?>
                                <?php foreach ($post['comments'] as $comm): ?>
                                    <div class="full-comment-card">
                                        <div class="comment-avatar">
                                            <span><?= htmlspecialchars(strtoupper(mb_substr($comm['autor_nome'], 0, 1))) ?></span>
                                        </div>
                                        <div class="comment-main">
                                            <div class="comment-header">
                                                <span class="comment-author">
                                                    <?= htmlspecialchars($comm['autor_nome']) ?>
                                                    <?php if (($comm['autor_tipo'] ?? 'normal') === 'premium'): ?>
                                                        <span class="badge-premium" title="Premium">🎵</span>
                                                    <?php elseif (($comm['autor_tipo'] ?? 'normal') === 'artista'): ?>
                                                        <span class="badge-artist">Artista</span>
                                                    <?php endif; ?>
                                                </span>
                                                <span class="comment-time"><?= $this->timeElapsed($comm['created_at']) ?></span>
                                            </div>
                                            <p class="comment-text"><?= htmlspecialchars($comm['conteudo']) ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>
                <?php
            }
        );
    }

    private function renderPostCard(array $post, bool $isDetail = false): string
    {
        $postId = (int)$post['id'];
        $isRepost = !empty($post['original_post_id']);
        
        ob_start();
        ?>
        <div class="social-post-card" data-id="<?= $postId ?>">
            <?php if ($isRepost): ?>
                <div class="repost-header">
                    <span>
                        🔁 <?= htmlspecialchars($post['autor_nome']) ?>
                        <?php if (($post['autor_tipo'] ?? 'normal') === 'premium'): ?>
                            <span class="badge-premium" title="Premium">🎵</span>
                        <?php elseif (($post['autor_tipo'] ?? 'normal') === 'artista'): ?>
                            <span class="badge-artist">Artista</span>
                        <?php endif; ?>
                        repostou
                    </span>
                </div>
            <?php endif; ?>

            <div class="post-body">
                <div class="post-avatar">
                    <span>
                        <?php 
                        $autor = $isRepost ? $post['orig_autor_nome'] : $post['autor_nome'];
                        echo htmlspecialchars(strtoupper(mb_substr($autor ?? 'U', 0, 1))); 
                        ?>
                    </span>
                </div>

                <div class="post-main">
                    <div class="post-header">
                        <span class="post-author">
                            <?= htmlspecialchars($autor) ?>
                            <?php 
                            $autorTipo = $isRepost ? ($post['orig_autor_tipo'] ?? 'normal') : ($post['autor_tipo'] ?? 'normal');
                            if ($autorTipo === 'premium'): ?>
                                <span class="badge-premium" title="Premium">🎵</span>
                            <?php elseif ($autorTipo === 'artista'): ?>
                                <span class="badge-artist">Artista</span>
                            <?php endif; ?>
                        </span>
                        <span class="post-time"><?= $this->timeElapsed($post['created_at']) ?></span>
                    </div>

                    <div class="post-text-container">
                        <p class="post-text">
                            <?php 
                            $text = $isRepost ? $post['orig_conteudo'] : $post['conteudo'];
                            echo htmlspecialchars($text); 
                            ?>
                        </p>
                    </div>

                    <?php
                    $musicId = $isRepost ? $post['orig_musica_id'] : $post['musica_id'];
                    $playlistId = $isRepost ? $post['orig_playlist_id'] : $post['playlist_id'];

                    if ($musicId) {
                        $mTitle  = $isRepost ? $post['orig_musica_titulo'] : $post['musica_titulo'];
                        $mArtist = $isRepost ? $post['orig_musica_artista'] : $post['musica_artista'];
                        $mCover  = $isRepost ? $post['orig_musica_capa'] : $post['musica_capa'];
                        $mDur    = $isRepost ? $post['orig_musica_duracao'] : $post['musica_duracao'];

                        $musicArray = [
                            'id'         => $musicId,
                            'title'      => $mTitle,
                            'artist'     => $mArtist,
                            'cover'      => $mCover,
                            'duration'   => $mDur,
                            'liked'      => false,
                            'downloaded' => false
                        ];
                        
                        echo '<div class="post-attachment-wrap">';
                        echo $this->renderCard($musicArray);
                        echo '</div>';
                    } elseif ($playlistId) {
                        $plName = $isRepost ? $post['orig_playlist_nome'] : $post['playlist_nome'];
                        ?>
                        <div class="post-attachment-wrap">
                            <div class="post-playlist-card" data-id="<?= $playlistId ?>">
                                <div class="playlist-card-icon">📁</div>
                                <div class="playlist-card-info">
                                    <h4 class="playlist-card-name"><?= htmlspecialchars($plName) ?></h4>
                                    <span class="playlist-card-sub">Playlist Musa</span>
                                </div>
                                <a href="?action=playlist&id=<?= $playlistId ?>" class="btn-play-playlist-social">Ouvir</a>
                            </div>
                        </div>
                        <?php
                    }
                    ?>

                    <div class="post-actions">
                        <button class="social-btn btn-social-like <?= $post['liked'] ? 'liked' : '' ?>" data-id="<?= $postId ?>" title="Curtir">
                            ♥ <span class="like-count"><?= $post['likes_count'] ?></span>
                        </button>
                        
                        <?php if ($isDetail): ?>
                            <span class="social-btn" title="Comentários">
                                💬 <span class="comment-count"><?= $post['comments_count'] ?></span>
                            </span>
                        <?php else: ?>
                            <a href="?action=social_view&id=<?= $postId ?>" class="social-btn btn-social-comment" title="Comentar">
                                💬 <span class="comment-count"><?= $post['comments_count'] ?></span>
                            </a>
                        <?php endif; ?>

                        <button class="social-btn btn-social-repost" data-id="<?= $postId ?>" title="Repostar">
                            🔁 Repostar
                        </button>

                        <?php 
                        $isOwner = (isset($_SESSION['usuario_id']) && (int)$_SESSION['usuario_id'] === (int)$post['usuario_id']);
                        if ($isOwner && !$isRepost): ?>
                            <button class="social-btn btn-social-edit" data-id="<?= $postId ?>" title="Editar Post">
                                ✎ Editar
                            </button>
                        <?php endif; ?>
                        <?php if ($isOwner): ?>
                            <button class="social-btn btn-social-delete" data-id="<?= $postId ?>" title="Excluir Post" style="color: var(--accent2);">
                                🗑 Excluir
                            </button>
                        <?php endif; ?>
                    </div>

                    <?php if (!$isDetail): ?>
                        <div class="post-comments-quick-list">
                            <?php if (!empty($post['top_comments'])): ?>
                                <div class="quick-comments-container">
                                    <?php foreach ($post['top_comments'] as $comm): ?>
                                        <div class="quick-comment-item">
                                            <span class="quick-author">
                                                <?= htmlspecialchars($comm['autor_nome']) ?>
                                                <?php if (($comm['autor_tipo'] ?? 'normal') === 'premium'): ?>
                                                    <span class="badge-premium" title="Premium">🎵</span>
                                                <?php elseif (($comm['autor_tipo'] ?? 'normal') === 'artista'): ?>
                                                    <span class="badge-artist">Artista</span>
                                                <?php endif; ?>:
                                            </span>
                                            <span class="quick-text"><?= htmlspecialchars($comm['conteudo']) ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($post['comments_count'] > 3): ?>
                                <a href="?action=social_view&id=<?= $postId ?>" class="social-view-more-link">
                                    Veja mais respostas (<?= $post['comments_count'] - 3 ?>)...
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    private function timeElapsed(string $timestamp): string
    {
        $time = strtotime($timestamp);
        $diff = time() - $time;

        if ($diff < 60) return 'agora';
        $mins = round($diff / 60);
        if ($mins < 60) return 'há ' . $mins . ' min';
        $hours = round($diff / 3600);
        if ($hours < 24) return 'há ' . $hours . ' h';
        $days = round($diff / 86400);
        return 'há ' . $days . ' d';
    }
}
