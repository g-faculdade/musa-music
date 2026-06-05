<?php

class MusicView
{
    public function home(array $musicFeed, array $recentlyPlayed, array $playlists, string $userName): void
    {
        $this->layout(
            title:     'Musa Music',
            playlists: $playlists,
            userName:  $userName,
            activeNav: 'music',
            content:   function() use ($musicFeed, $recentlyPlayed) {
                if (!empty($recentlyPlayed)) {
                    echo '<div class="recently-played-wrapper">';
                    echo '<h2 class="section-title">Tocadas recentemente</h2>';
                    echo '<section class="music-grid" id="recently-played-list" style="margin-bottom: 40px;">';
                    foreach ($recentlyPlayed as $m) echo $this->renderCard($m);
                    echo '</section>';
                    echo '</div>';
                }

                echo '<h2 class="section-title" id="home-section-title">Em alta agora</h2>';
                echo '<section class="music-grid" id="music-list">';
                foreach ($musicFeed as $m) echo $this->renderCard($m);
                echo '</section>';
            }
        );
    }

    public function collection(
        array  $musics,
        array  $playlists,
        string $userName,
        string $title,
        string $icon,
        string $emptyMsg,
    ): void {
        $this->layout(
            title:     $title . ' — Musa Music',
            playlists: $playlists,
            userName:  $userName,
            activeNav: '',
            content:   function() use ($musics, $title, $icon, $emptyMsg) {
                echo '<div class="collection-header">';
                echo '<div class="collection-cover-icon">' . $icon . '</div>';
                echo '<div><h1 class="collection-title">' . htmlspecialchars($title) . '</h1>';
                $n = count($musics);
                echo '<p class="collection-count">' . $n . ' música' . ($n !== 1 ? 's' : '') . '</p></div>';
                echo '</div>';

                $plId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
                echo '<section class="music-grid" id="music-list" data-playlist-id="' . $plId . '">';
                if (empty($musics)) {
                    echo '<div class="empty"><p>' . htmlspecialchars($emptyMsg) . '</p></div>';
                } else {
                    foreach ($musics as $m) echo $this->renderCard($m);
                }
                echo '</section>';
            }
        );
    }

    public function profile(array $user, array $likedSongs, array $playlists, string $userName): void
    {
        $this->layout(
            title: 'Perfil de ' . htmlspecialchars($user['nome']) . ' — Musa Music',
            playlists: $playlists,
            userName: $userName,
            activeNav: 'profile',
            content: function() use ($user, $likedSongs) {
                $initial = htmlspecialchars(strtoupper(mb_substr($user['nome'], 0, 1)));
                $memberSince = date('d/m/Y', strtotime($user['created_at']));
                ?>
                <section class="profile-container">
                    <div class="profile-header-card">
                        <div class="profile-big-avatar"><span><?= $initial ?></span></div>
                        <div class="profile-meta-info">
                            <?php if (($user['tipo'] ?? 'normal') === 'premium'): ?>
                                <span class="profile-badge badge-premium-profile">Membro Premium</span>
                            <?php elseif (($user['tipo'] ?? 'normal') === 'artista'): ?>
                                <span class="profile-badge badge-artist-profile">Artista Oficial</span>
                            <?php else: ?>
                                <span class="profile-badge">Usuário Musa</span>
                            <?php endif; ?>
                            <h1 class="profile-name">
                                <?= htmlspecialchars($user['nome']) ?>
                                <?php if (($user['tipo'] ?? 'normal') === 'premium'): ?>
                                    <span class="badge-premium" title="Premium">🎵</span>
                                <?php elseif (($user['tipo'] ?? 'normal') === 'artista'): ?>
                                    <span class="badge-artist">Artista</span>
                                <?php endif; ?>
                            </h1>
                            <p class="profile-email">✉ <?= htmlspecialchars($user['email']) ?></p>
                            <p class="profile-date">📅 Membro desde <?= $memberSince ?></p>
                            <div class="profile-bio-container">
                                <label for="bio-textarea-input" class="profile-bio-label">Fale um pouco sobre você</label>
                                <textarea id="bio-textarea-input" maxlength="255" placeholder="Escreva algo sobre você..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                                <div class="bio-actions">
                                    <button id="btn-save-bio" class="btn-primary-sm">Salvar Bio</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="profile-liked-section">
                        <h2 class="section-title">♥ Músicas Curtidas</h2>
                        <section class="music-grid" id="music-list">
                            <?php if (empty($likedSongs)): ?>
                                <div class="empty"><p>Você ainda não curtiu nenhuma música.</p></div>
                            <?php else: ?>
                                <?php foreach ($likedSongs as $m) echo $this->renderCard($m); ?>
                            <?php endif; ?>
                        </section>
                    </div>
                </section>
                <?php
            }
        );
    }

    protected function layout(
        string   $title,
        array    $playlists,
        string   $userName,
        string   $activeNav,
        callable $content
    ): void {
        if (isset($_GET['ajax'])) {
            ob_start();
            ($content)();
            $html = ob_get_clean();

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'title'     => $title,
                'activeNav' => $activeNav,
                'html'      => $html,
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $initial = htmlspecialchars(strtoupper(mb_substr($userName, 0, 1)));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500&family=Syne:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./style.css">
</head>
<body>

<div id="modal-playlist" class="modal-backdrop" hidden>
    <div class="modal-box">
        <h2 class="modal-title">Nova Playlist</h2>
        <div class="form-group">
            <label>Nome da playlist</label>
            <input type="text" id="pl-name" placeholder="Ex: Favoritas">
        </div>
        <div class="modal-actions">
            <button id="btn-pl-cancel" class="btn-secondary">Cancelar</button>
            <button id="btn-pl-save"   class="btn-primary-sm">Salvar</button>
        </div>
    </div>
</div>

<div id="modal-add-pl" class="modal-backdrop" hidden>
    <div class="modal-box">
        <h2 class="modal-title">Adicionar à Playlist</h2>
        <ul id="modal-pl-list" class="modal-pl-list"></ul>
        <div class="modal-actions" style="margin-top:1rem;">
            <button id="btn-addpl-cancel" class="btn-secondary">Fechar</button>
        </div>
    </div>
</div>

<div class="layout">

    <nav class="sidebar-nav">
        <div class="logo">MUSA</div>
        <ul class="nav-links">
            <li>
                <a href="?action=music" class="nav-link <?= $activeNav === 'music' ? 'active' : '' ?>">
                    <span class="nav-icon">♪</span> Músicas
                </a>
            </li>
            <li>
                <a href="?action=social" class="nav-link <?= $activeNav === 'social' ? 'active' : '' ?>">
                    <span class="nav-icon">☰</span> Social
                </a>
            </li>
            <li>
                <a href="?action=liked" class="nav-link <?= $activeNav === 'liked' ? 'active' : '' ?>">
                    <span class="nav-icon">♥</span> Mais Curtidas
                </a>
            </li>
            <li>
                <a href="?action=downloaded" class="nav-link <?= $activeNav === 'downloaded' ? 'active' : '' ?>">
                    <span class="nav-icon">↓</span> Baixadas
                </a>
            </li>
            <li>
                <button class="nav-link-btn btn-create-playlist-menu" id="btn-open-create-pl">
                    <span class="nav-icon">+</span> Criar Playlist
                </button>
            </li>
        </ul>

        <div class="playlists-section">
            <h3 class="playlists-title">Playlists Criadas</h3>
            <ul class="playlist-list" id="playlist-list">
                <?php foreach ($playlists as $pl): ?>
                <li class="playlist-item" data-id="<?= (int)$pl['id'] ?>">
                    <a href="?action=playlist&id=<?= (int)$pl['id'] ?>"
                       title="<?= htmlspecialchars($pl['name']) ?>">
                        <?= htmlspecialchars($pl['name']) ?>
                    </a>
                    <button class="btn-delete-playlist" data-id="<?= (int)$pl['id'] ?>" title="Excluir playlist">×</button>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </nav>

    <main class="main-content">
        <header class="main-header">
            <div class="search-area">
                <label class="search-label" for="search-input">Buscar música</label>
                <div class="search-row">
                    <input type="text" id="search-input" class="search-input"
                           placeholder="Artista, música, álbum..." autocomplete="off">
                    <button class="btn-search" id="btn-search">Buscar</button>
                </div>
            </div>
            <div class="profile-wrapper">
                <button class="profile-avatar" id="profile-btn" aria-label="Perfil">
                    <span class="avatar-initials"><?= $initial ?></span>
                </button>
                <div class="profile-menu" id="profile-menu" hidden>
                    <a href="?action=profile" class="profile-menu-item">Meu Perfil</a>
                    <a href="?action=login" class="profile-menu-item">Sair</a>
                </div>
            </div>
        </header>

        <div id="content-area">
            <?php ($content)(); ?>
        </div>
    </main>
</div>

<footer class="player-bar">
    <div class="player-now">
        <img src="" alt="" class="player-cover" id="player-cover">
        <div class="player-info">
            <div class="player-title-row">
                <span class="player-title"  id="player-title">—</span>
                <div class="player-mini-bars" id="player-mini-bars">
                    <div class="mini-bar"></div>
                    <div class="mini-bar"></div>
                    <div class="mini-bar"></div>
                </div>
            </div>
            <span class="player-artist" id="player-artist">Nenhuma música</span>
        </div>
    </div>
    <div class="player-center">
        <div class="player-controls">
            <button class="ctrl-btn" id="btn-prev"       aria-label="Anterior">⏮</button>
            <button class="ctrl-btn ctrl-play" id="btn-play-pause" aria-label="Play/Pause">▶</button>
            <button class="ctrl-btn" id="btn-next"       aria-label="Próxima">⏭</button>
            <button class="ctrl-btn" id="btn-repeat"     aria-label="Repetir">↺</button>
        </div>
        <div class="progress-row">
            <span class="time" id="time-current">0:00</span>
            <div class="progress-track">
                <canvas id="waveform-canvas" style="width: 100%; height: 100%; display: block;"></canvas>
                <div class="progress-fill" id="progress-fill" style="display: none;"></div>
                <input type="range" id="progress-bar" value="0" min="0" max="100" step="0.1" aria-label="Progresso">
            </div>
            <span class="time" id="time-duration">0:00</span>
        </div>
    </div>
    <div class="player-right">
        <span class="volume-icon" aria-hidden="true">🔊︎</span>
        <div class="volume-track">
            <div class="volume-fill" id="volume-fill"></div>
            <input type="range" id="volume-bar" value="80" min="0" max="100" step="1" aria-label="Volume">
        </div>
    </div>
</footer>

<audio id="audio-player" preload="metadata"></audio>
<div class="toast" id="toast"></div>

<script>
    window.INITIAL_PLAYLISTS = <?= json_encode($playlists, JSON_UNESCAPED_UNICODE) ?>;
</script>
<script src="./app.js"></script>
</body>
</html>
<?php
    }

    public function renderCard(array $music): string
    {
        $id         = (int)  $music['id'];
        $title      = htmlspecialchars($music['title']);
        $artist     = htmlspecialchars($music['artist']);
        $cover      = htmlspecialchars($music['cover']);
        $duration   = (int)  $music['duration'];
        $liked      = $music['liked']      ? 'liked'      : '';
        $downloaded = $music['downloaded'] ? 'downloaded' : '';

        ob_start(); ?>
<div class="music-card <?= $liked ? 'is-liked' : '' ?> <?= $downloaded ? 'is-downloaded' : '' ?>"
     data-id="<?= $id ?>"
     data-title="<?= $title ?>"
     data-artist="<?= $artist ?>"
     data-duration="<?= $duration ?>"
     data-cover="<?= $cover ?>"
     data-liked="<?= $music['liked'] ? '1' : '0' ?>"
     data-downloaded="<?= $music['downloaded'] ? '1' : '0' ?>">

    <div class="music-cover-wrap">
        <img src="<?= $cover ?>" alt="<?= $title ?>" class="music-cover" loading="lazy">
        <div class="play-overlay"><div class="play-circle">▶</div></div>
        <div class="bars">
            <div class="bar"></div><div class="bar"></div><div class="bar"></div>
        </div>
    </div>

    <div class="music-info">
        <h3 class="music-title"><?= $title ?></h3>
        <p  class="music-artist"><?= $artist ?></p>
    </div>

    <div class="music-actions">
        <button class="btn-like <?= $liked ?>" aria-label="Curtir">♥</button>
        <div class="add-wrapper">
            <button class="btn-add" aria-label="Mais opções">+</button>
            <div class="add-menu" hidden>
                <button class="add-menu-item btn-add-playlist">Adicionar à playlist</button>
                <button class="add-menu-item btn-remove-playlist" style="display:none">Remover da playlist</button>
                <button class="add-menu-item btn-download <?= $downloaded ?>">
                    <?= $downloaded ? '✓ Baixado' : 'Baixar música' ?>
                </button>
            </div>
        </div>
    </div>
</div>
<?php
        return ob_get_clean();
    }
}
