<?php

class MusicView
{
    public function home(array $musicFeed, array $playlists, string $userName): void
    {
        $this->layout(
            title:     'Musa Music',
            playlists: $playlists,
            userName:  $userName,
            activeNav: 'music',
            content:   function() use ($musicFeed) {
                echo '<h2 class="section-title">Em alta agora</h2>';
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

    private function layout(
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
                    <span class="nav-icon">◉</span> Social
                </a>
            </li>
        </ul>
    </nav>

    <aside class="sidebar-library">
        <div class="library-section">
            <a href="?action=liked" class="library-item" title="Músicas curtidas">
                <span class="library-icon">♥</span>
            </a>
        </div>
        <div class="library-section">
            <a href="?action=downloaded" class="library-item library-item-dl" title="Músicas baixadas">
                <span class="library-icon">↓</span>
            </a>
        </div>
        <div class="library-section">
            <button class="btn-create-playlist" id="btn-open-create-pl" title="Criar playlist">
                <span>+</span>
            </button>
        </div>
        <ul class="playlist-list" id="playlist-list">
            <?php foreach ($playlists as $pl): ?>
            <li class="playlist-item" data-id="<?= (int)$pl['id'] ?>">
                <a href="?action=playlist&id=<?= (int)$pl['id'] ?>"
                   title="<?= htmlspecialchars($pl['name']) ?>">
                    <?= htmlspecialchars(mb_strtoupper(mb_substr($pl['name'], 0, 2))) ?>
                </a>
                <button class="btn-delete-playlist" data-id="<?= (int)$pl['id'] ?>" title="Excluir playlist">×</button>
            </li>
            <?php endforeach; ?>
        </ul>
    </aside>

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
            <span class="player-title"  id="player-title">—</span>
            <span class="player-artist" id="player-artist">Nenhuma música</span>
        </div>
    </div>
    <div class="player-center">
        <div class="player-controls">
            <button class="ctrl-btn" id="btn-prev"       aria-label="Anterior">⏮</button>
            <button class="ctrl-btn ctrl-play" id="btn-play-pause" aria-label="Play/Pause">▶</button>
            <button class="ctrl-btn" id="btn-next"       aria-label="Próxima">⏭</button>
        </div>
        <div class="progress-row">
            <span class="time" id="time-current">0:00</span>
            <div class="progress-track">
                <div class="progress-fill" id="progress-fill"></div>
                <input type="range" id="progress-bar" value="0" min="0" max="100" step="0.1" aria-label="Progresso">
            </div>
            <span class="time" id="time-duration">0:00</span>
        </div>
    </div>
    <div class="player-right">
        <span class="volume-icon" aria-hidden="true">🔊</span>
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
