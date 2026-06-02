const STATE = {
    queue:        [],
    currentIdx:   -1,
    playlists:    window.INITIAL_PLAYLISTS ?? [],
    pendingMusic: null,
    repeat:       false,
};

const audio         = document.getElementById('audio-player');
const playerCover   = document.getElementById('player-cover');
const playerTitle   = document.getElementById('player-title');
const playerArtist  = document.getElementById('player-artist');
const btnPlayPause  = document.getElementById('btn-play-pause');
const btnPrev       = document.getElementById('btn-prev');
const btnNext       = document.getElementById('btn-next');
const progressBar   = document.getElementById('progress-bar');
const progressFill  = document.getElementById('progress-fill');
const timeCurrent   = document.getElementById('time-current');
const timeDuration  = document.getElementById('time-duration');
const volumeBar     = document.getElementById('volume-bar');
const volumeFill    = document.getElementById('volume-fill');
const toastEl       = document.getElementById('toast');
const searchInput   = document.getElementById('search-input');
const profileMenu   = document.getElementById('profile-menu');
const modalPlaylist = document.getElementById('modal-playlist');
const modalAddPl    = document.getElementById('modal-add-pl');
const modalPlList   = document.getElementById('modal-pl-list');
const plNameInput   = document.getElementById('pl-name');
const btnRepeat     = document.getElementById('btn-repeat');
const playerBar     = document.querySelector('.player-bar');

let audioCtx = null;
let analyser = null;
let sourceNode = null;
let frequencyData = null;
let currentWaveformHeights = [];
const numBars = 65;

async function playIndex(idx) {
    if (idx < 0 || idx >= STATE.queue.length) return;
    STATE.currentIdx = idx;
    const music = STATE.queue[idx];

    generateSignatureWaveform(music.title, music.artist);

    document.querySelector('.player-now').classList.add('active');
    playerCover.src          = music.cover;
    playerTitle.textContent  = music.title;
    playerArtist.textContent = music.artist;
    btnPlayPause.textContent = '⏸';

    document.querySelectorAll('.music-card.playing').forEach(c => c.classList.remove('playing'));
    document.querySelector(`.music-card[data-id="${music.id}"]`)?.classList.add('playing');

    btnPlayPause.disabled = true;
    showToast('Buscando áudio…');

    try {
        const url  = `?action=preview&id=${encodeURIComponent(music.id)}&title=${encodeURIComponent(music.title)}&artist=${encodeURIComponent(music.artist)}&cover=${encodeURIComponent(music.cover)}`;
        const res  = await fetch(url);
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const json = await res.json();
        if (!json.url) throw new Error(json.error ?? 'Sem URL');
        audio.src    = json.url;
        audio.volume = volumeBar.value / 100;
        
        setupAudioVisualizer();
        
        await audio.play();
        hideToast();
        prefetchNext(idx);
    } catch (e) {
        console.error('preview error:', e);
        showToast('Não foi possível reproduzir esta música.');
    } finally {
        btnPlayPause.disabled = false;
    }
}

function rebuildQueue() {
    STATE.queue = [...document.querySelectorAll('.music-card')].map(cardToData);
}

function cardToData(card) {
    return {
        id:       card.dataset.id,
        title:    card.dataset.title,
        artist:   card.dataset.artist,
        duration: card.dataset.duration,
        cover:    card.dataset.cover,
    };
}

btnPlayPause.addEventListener('click', () => {
    if (!audio.src) return;
    audio.paused ? audio.play() : audio.pause();
});
audio.addEventListener('play',  () => {
    btnPlayPause.textContent = '⏸';
    playerBar?.classList.add('playing');
});
audio.addEventListener('pause', () => {
    btnPlayPause.textContent = '▶';
    playerBar?.classList.remove('playing');
});

btnRepeat?.addEventListener('click', () => {
    STATE.repeat = !STATE.repeat;
    btnRepeat.classList.toggle('active', STATE.repeat);
});

btnPrev.addEventListener('click', () =>
    playIndex(STATE.currentIdx > 0 ? STATE.currentIdx - 1 : STATE.queue.length - 1)
);
btnNext.addEventListener('click', () =>
    playIndex(STATE.currentIdx < STATE.queue.length - 1 ? STATE.currentIdx + 1 : 0)
);
audio.addEventListener('ended', () => {
    if (STATE.repeat) {
        audio.currentTime = 0;
        audio.play().catch(() => {});
    } else {
        playIndex(STATE.currentIdx < STATE.queue.length - 1 ? STATE.currentIdx + 1 : 0);
    }
});

audio.addEventListener('timeupdate', () => {
    if (!audio.duration) return;
    const pct = (audio.currentTime / audio.duration) * 100;
    progressFill.style.width = pct + '%';
    progressBar.value        = pct;
    timeCurrent.textContent  = fmt(audio.currentTime);
    timeDuration.textContent = fmt(audio.duration);
});
progressBar.addEventListener('input', () => {
    if (audio.duration) audio.currentTime = (progressBar.value / 100) * audio.duration;
});
volumeBar.addEventListener('input', () => {
    audio.volume = volumeBar.value / 100;
    volumeFill.style.width = volumeBar.value + '%';
});

function fmt(sec) {
    const s = Math.floor(sec);
    return `${Math.floor(s / 60)}:${String(s % 60).padStart(2, '0')}`;
}

document.addEventListener('click', async e => {

    const saveBio = e.target.closest('#btn-save-bio');
    if (saveBio) {
        const textarea = document.getElementById('bio-textarea-input');
        const bio = textarea ? textarea.value : '';
        saveBio.disabled = true;
        try {
            const res = await fetch('?action=save_bio&ajax=1', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ bio })
            });
            if (!res.ok) throw new Error('Erro ao salvar');
            showToast('Biografia atualizada!');
        } catch (err) {
            console.error(err);
            showToast('Erro ao salvar biografia.');
        } finally {
            saveBio.disabled = false;
        }
        return;
    }

    if (!e.target.closest('.add-wrapper'))
        document.querySelectorAll('.add-menu:not([hidden])').forEach(m => m.hidden = true);
    if (!e.target.closest('.profile-wrapper'))
        profileMenu.hidden = true;

    const coverWrap = e.target.closest('.music-cover-wrap');
    if (coverWrap) {
        rebuildQueue();
        const cards = [...document.querySelectorAll('.music-card')];
        STATE.currentIdx = cards.indexOf(coverWrap.closest('.music-card'));
        await playIndex(STATE.currentIdx);
        return;
    }

    const likeBtn = e.target.closest('.btn-like');
    if (likeBtn) {
        await toggleLike(likeBtn.closest('.music-card'), likeBtn);
        return;
    }

    const addBtn = e.target.closest('.btn-add');
    if (addBtn) {
        const menu = addBtn.closest('.add-wrapper').querySelector('.add-menu');
        const wasHidden = menu.hidden;
        document.querySelectorAll('.add-menu').forEach(m => m.hidden = true);

        const grid       = document.getElementById('music-list');
        const playlistId = grid?.dataset.playlistId;
        const removeBtn  = menu.querySelector('.btn-remove-playlist');
        if (removeBtn) removeBtn.style.display = (playlistId && playlistId !== '0') ? '' : 'none';

        menu.hidden = !wasHidden;
        return;
    }

    const addPlBtn = e.target.closest('.btn-add-playlist');
    if (addPlBtn) {
        addPlBtn.closest('.add-menu').hidden = true;
        openAddToPlaylistModal(cardToData(addPlBtn.closest('.music-card')));
        return;
    }

    const removeBtn = e.target.closest('.btn-remove-playlist');
    if (removeBtn) {
        removeBtn.closest('.add-menu').hidden = true;
        const card       = removeBtn.closest('.music-card');
        const grid       = document.getElementById('music-list');
        const playlistId = grid?.dataset.playlistId;
        if (playlistId) await removeFromPlaylist(card, parseInt(playlistId));
        return;
    }

    const dlBtn = e.target.closest('.btn-download');
    if (dlBtn) {
        if (dlBtn.classList.contains('downloaded')) return;
        dlBtn.closest('.add-menu').hidden = true;
        await downloadMusic(dlBtn.closest('.music-card'), dlBtn);
        return;
    }

    if (e.target.closest('#btn-search')) {
        await searchMusic();
        return;
    }

    if (e.target.closest('#profile-btn')) {
        profileMenu.hidden = !profileMenu.hidden;
        return;
    }

    const delPlBtn = e.target.closest('.btn-delete-playlist');
    if (delPlBtn) {
        e.preventDefault();
        await deletePlaylist(parseInt(delPlBtn.dataset.id), delPlBtn);
        return;
    }
});

searchInput?.addEventListener('keydown', e => {
    if (e.key === 'Enter') searchMusic();
});

async function toggleLike(card, btn) {
    btn.disabled = true;
    try {
        const res  = await api('like', cardToData(card));
        const json = await res.json();
        if (json.liked !== undefined) {
            card.dataset.liked = json.liked ? '1' : '0';
            btn.classList.toggle('liked', json.liked);
            showToast(json.liked ? '♥ Curtido!' : 'Removido dos curtidos');
        }
    } catch { showToast('Erro ao curtir.'); }
    finally  { btn.disabled = false; }
}

async function searchMusic() {
    const q = searchInput?.value.trim();
    if (!q) {
        navigateTo('?action=music');
        return;
    }
    const grid = document.getElementById('music-list');
    if (!grid) return;

    const recentlyPlayedWrap = document.querySelector('.recently-played-wrapper');
    if (recentlyPlayedWrap) {
        recentlyPlayedWrap.style.display = 'none';
    }

    const homeSectionTitle = document.getElementById('home-section-title');
    if (homeSectionTitle) {
        homeSectionTitle.textContent = 'Resultados da busca';
    }

    grid.innerHTML = skeletons(8);
    try {
        const res    = await fetch(`?action=search&q=${encodeURIComponent(q)}`);
        const musics = await res.json();
        if (!Array.isArray(musics) || !musics.length) {
            grid.innerHTML = '<div class="empty"><p>Nenhuma música encontrada.</p></div>';
            return;
        }
        grid.innerHTML = musics.map(renderCard).join('');
        rebuildQueue();
    } catch {
        showToast('Erro na busca.');
        grid.innerHTML = '';
    }
}

async function downloadMusic(card, btn) {
    const data = cardToData(card);
    btn.textContent = 'Baixando…';
    btn.disabled    = true;
    showToast('Baixando música, aguarde…');
    try {
        const res  = await api('download', data);
        const json = await res.json();
        if (json.url) {
            btn.textContent = '✓ Baixado';
            btn.classList.add('downloaded');
            card.dataset.downloaded = '1';
            card.classList.add('is-downloaded');
            showToast('Música salva no servidor!');
        } else {
            throw new Error(json.error ?? 'Falha');
        }
    } catch (e) {
        console.error('download error:', e);
        btn.textContent = 'Baixar música';
        btn.disabled    = false;
        showToast('Falha no download.');
    }
}

async function removeFromPlaylist(card, playlistId) {
    try {
        const res  = await api('remove_playlist', {
            playlist_id: playlistId,
            music_id:    parseInt(card.dataset.id),
        });
        const json = await res.json();
        if (json.ok) {
            card.remove();
            showToast('Música removida da playlist.');
            const count = document.querySelector('.collection-count');
            if (count) {
                const n = document.querySelectorAll('.music-card').length;
                count.textContent = n + ' música' + (n !== 1 ? 's' : '');
            }
        } else {
            showToast(json.error ?? 'Erro ao remover.');
        }
    } catch { showToast('Erro ao remover da playlist.'); }
}

async function deletePlaylist(playlistId, btn) {
    if (!confirm('Excluir esta playlist?')) return;
    btn.disabled = true;
    try {
        const res  = await api('delete_playlist', { playlist_id: playlistId });
        const json = await res.json();
        if (json.ok) {
            document.querySelector(`.playlist-item[data-id="${playlistId}"]`)?.remove();
            STATE.playlists = STATE.playlists.filter(p => p.id !== playlistId);
            showToast('Playlist excluída.');
            const grid = document.getElementById('music-list');
            if (grid && parseInt(grid.dataset.playlistId) === playlistId) {
                window.location.href = '?action=music';
            }
        } else {
            showToast(json.error ?? 'Erro ao excluir.');
            btn.disabled = false;
        }
    } catch {
        showToast('Erro ao excluir playlist.');
        btn.disabled = false;
    }
}

document.getElementById('btn-open-create-pl')?.addEventListener('click', () => {
    plNameInput.value    = '';
    modalPlaylist.hidden = false;
    plNameInput.focus();
});

document.getElementById('btn-pl-cancel')?.addEventListener('click', () => {
    modalPlaylist.hidden = true;
});

document.getElementById('btn-pl-save')?.addEventListener('click', async () => {
    const name = plNameInput.value.trim();
    if (!name) { plNameInput.focus(); return; }
    const btn = document.getElementById('btn-pl-save');
    btn.disabled = true;
    try {
        const res  = await api('create_playlist', { name });
        const json = await res.json();
        if (json.id) {
            STATE.playlists.push(json);
            addPlaylistToSidebar(json);
            showToast(`Playlist "${name}" criada!`);
            modalPlaylist.hidden = true;
        } else {
            showToast(json.error ?? 'Erro ao criar playlist.');
        }
    } catch { showToast('Erro ao criar playlist.'); }
    finally  { btn.disabled = false; }
});

function openAddToPlaylistModal(music) {
    STATE.pendingMusic    = music;
    modalPlList.innerHTML = '';

    if (!STATE.playlists.length) {
        modalPlList.innerHTML =
            '<li style="padding:10px;color:var(--muted);font-size:.85rem;">Nenhuma playlist criada ainda.</li>';
    } else {
        STATE.playlists.forEach(pl => {
            const li       = document.createElement('li');
            li.className   = 'modal-pl-item';
            li.textContent = pl.name;
            li.dataset.id  = pl.id;
            li.addEventListener('click', () => addMusicToPlaylist(pl));
            modalPlList.appendChild(li);
        });
    }

    modalAddPl.hidden = false;
}

async function addMusicToPlaylist(playlist) {
    const music = STATE.pendingMusic;
    if (!music) return;
    try {
        const res  = await api('add_playlist', { playlist_id: playlist.id, ...music });
        const json = await res.json();
        showToast(json.ok ? `Adicionado a "${playlist.name}"!` : (json.error ?? 'Erro.'));
    } catch { showToast('Erro ao adicionar à playlist.'); }
    finally {
        modalAddPl.hidden  = true;
        STATE.pendingMusic = null;
    }
}

document.getElementById('btn-addpl-cancel')?.addEventListener('click', () => {
    modalAddPl.hidden  = true;
    STATE.pendingMusic = null;
});

[modalPlaylist, modalAddPl].forEach(modal => {
    modal?.addEventListener('click', e => { if (e.target === modal) modal.hidden = true; });
});

function api(action, body) {
    return fetch(`?action=${action}`, {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify(body),
    });
}

function renderCard(music) {
    const liked      = music.liked      ? 'liked'      : '';
    const downloaded = music.downloaded ? 'downloaded' : '';
    return `
    <div class="music-card ${liked ? 'is-liked' : ''} ${downloaded ? 'is-downloaded' : ''}"
         data-id="${music.id}"
         data-title="${esc(music.title)}"
         data-artist="${esc(music.artist)}"
         data-duration="${music.duration}"
         data-cover="${esc(music.cover)}"
         data-liked="${music.liked ? '1' : '0'}"
         data-downloaded="${music.downloaded ? '1' : '0'}">
        <div class="music-cover-wrap">
            <img src="${esc(music.cover)}" alt="${esc(music.title)}" class="music-cover" loading="lazy">
            <div class="play-overlay"><div class="play-circle">▶</div></div>
            <div class="bars"><div class="bar"></div><div class="bar"></div><div class="bar"></div></div>
        </div>
        <div class="music-info">
            <h3 class="music-title">${esc(music.title)}</h3>
            <p  class="music-artist">${esc(music.artist)}</p>
        </div>
        <div class="music-actions">
            <button class="btn-like ${liked}" aria-label="Curtir">♥</button>
            <div class="add-wrapper">
                <button class="btn-add" aria-label="Mais opções">+</button>
                <div class="add-menu" hidden>
                    <button class="add-menu-item btn-add-playlist">Adicionar à playlist</button>
                    <button class="add-menu-item btn-remove-playlist" style="display:none">Remover da playlist</button>
                    <button class="add-menu-item btn-download ${downloaded}">
                        ${downloaded ? '✓ Baixado' : 'Baixar música'}
                    </button>
                </div>
            </div>
        </div>
    </div>`;
}

function addPlaylistToSidebar(pl) {
    const list = document.getElementById('playlist-list');
    if (!list) return;
    const li   = document.createElement('li');
    li.className  = 'playlist-item';
    li.dataset.id = pl.id;
    li.innerHTML  = `
        <a href="?action=playlist&id=${pl.id}" title="${esc(pl.name)}">
            ${esc(pl.name)}
        </a>
        <button class="btn-delete-playlist" data-id="${pl.id}" title="Excluir playlist">×</button>`;
    list.appendChild(li);
}

function skeletons(n) {
    return Array(n).fill(`
        <div class="skeleton">
            <div class="skeleton-cover"></div>
            <div class="skeleton-info">
                <div class="skeleton-line"></div>
                <div class="skeleton-line short"></div>
            </div>
        </div>`).join('');
}

function esc(str) {
    return String(str ?? '')
        .replace(/&/g, '&amp;').replace(/"/g, '&quot;')
        .replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

let toastTimer;
function showToast(msg) {
    toastEl.textContent = msg;
    toastEl.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(hideToast, 3500);
}
function hideToast() { toastEl.classList.remove('show'); }

rebuildQueue();


// === SPA / AJAX Navigation ===

document.addEventListener('click', e => {
    const link = e.target.closest('a');
    if (!link) return;

    const href = link.getAttribute('href');
    if (href && href.startsWith('?action=') && !href.includes('action=login')) {
        e.preventDefault();
        navigateTo(href);
    }
});

function updateActiveNav(url) {
    document.querySelectorAll('.sidebar-nav .nav-link, .sidebar-library .library-item, .playlist-item a').forEach(el => {
        el.classList.remove('active');
    });

    const normalizedUrl = url.split('&ajax=')[0];

    const activeLink = document.querySelector(`a[href="${normalizedUrl}"], a[href^="${normalizedUrl}&"]`);
    if (activeLink) {
        activeLink.classList.add('active');
    }
}

function updatePlayingCardHighlight() {
    if (STATE.currentIdx !== -1 && STATE.queue[STATE.currentIdx]) {
        const music = STATE.queue[STATE.currentIdx];
        document.querySelectorAll('.music-card.playing').forEach(c => c.classList.remove('playing'));
        document.querySelector(`.music-card[data-id="${music.id}"]`)?.classList.add('playing');
    }
}

async function navigateTo(url, pushState = true) {
    try {
        const separator = url.includes('?') ? '&' : '?';
        const res = await fetch(url + separator + 'ajax=1');
        if (!res.ok) throw new Error('HTTP ' + res.status);

        const json = await res.json();

        document.title = json.title;

        const contentArea = document.getElementById('content-area');
        if (contentArea) {
            contentArea.innerHTML = json.html;
        }

        updateActiveNav(url);
        updatePlayingCardHighlight();

        if (pushState) {
            history.pushState({ url }, json.title, url);
        }
    } catch (e) {
        console.error('navigation error:', e);
        showToast('Não foi possível carregar a página.');
    }
}

window.addEventListener('popstate', e => {
    if (e.state && e.state.url) {
        navigateTo(e.state.url, false);
    } else {
        navigateTo(window.location.search || '?action=music', false);
    }
});

// === Background pre-fetching ===

function prefetchNext(idx) {
    const nextIdx = idx < STATE.queue.length - 1 ? idx + 1 : 0;
    if (nextIdx === idx || nextIdx < 0 || nextIdx >= STATE.queue.length) return;

    const nextMusic = STATE.queue[nextIdx];
    if (nextMusic.downloaded === '1') return;

    const url = `?action=preview&id=${encodeURIComponent(nextMusic.id)}&title=${encodeURIComponent(nextMusic.title)}&artist=${encodeURIComponent(nextMusic.artist)}`;
    fetch(url).catch(() => {});
}

// === MUSA Social Interaction Listeners ===

document.addEventListener('submit', async e => {
    // 1. Criar novo post
    if (e.target.id === 'social-post-form') {
        e.preventDefault();
        const content = document.getElementById('post-content').value.trim();
        const select = document.getElementById('post-attachment');
        const attachmentValue = select ? select.value : '';

        let musicId = null;
        let playlistId = null;

        if (attachmentValue.startsWith('music_')) {
            musicId = parseInt(attachmentValue.replace('music_', ''));
        } else if (attachmentValue.startsWith('playlist_')) {
            playlistId = parseInt(attachmentValue.replace('playlist_', ''));
        }

        const btnSubmit = e.target.querySelector('button[type="submit"]');
        if (btnSubmit) btnSubmit.disabled = true;

        try {
            const res = await fetch('?action=social_post&ajax=1', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ conteudo: content, musica_id: musicId, playlist_id: playlistId })
            });

            if (!res.ok) throw new Error('Erro ao postar');

            showToast('Publicado com sucesso!');
            navigateTo('?action=social');
        } catch (err) {
            console.error(err);
            showToast('Não foi possível publicar.');
        } finally {
            if (btnSubmit) btnSubmit.disabled = false;
        }
    }

    // 2. Enviar resposta/comentário
    if (e.target.id === 'social-comment-form') {
        e.preventDefault();
        const postId = parseInt(e.target.dataset.postId);
        const content = document.getElementById('comment-content').value.trim();

        const btnSubmit = e.target.querySelector('button[type="submit"]');
        if (btnSubmit) btnSubmit.disabled = true;

        try {
            const res = await fetch('?action=social_comment&ajax=1', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ post_id: postId, conteudo: content })
            });

            if (!res.ok) throw new Error('Erro ao comentar');

            showToast('Resposta enviada!');
            navigateTo('?action=social_view&id=' + postId);
        } catch (err) {
            console.error(err);
            showToast('Não foi possível comentar.');
        } finally {
            if (btnSubmit) btnSubmit.disabled = false;
        }
    }
});

document.addEventListener('click', async e => {
    // 3. Curtir Post
    const btnLike = e.target.closest('.btn-social-like');
    if (btnLike) {
        e.preventDefault();
        const postId = parseInt(btnLike.dataset.id);
        btnLike.disabled = true;

        try {
            const res = await fetch('?action=social_like&ajax=1', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ post_id: postId })
            });

            if (!res.ok) throw new Error('Erro ao curtir');
            const json = await res.json();

            btnLike.classList.toggle('liked', json.liked);
            const countSpan = btnLike.querySelector('.like-count');
            if (countSpan) {
                countSpan.textContent = json.likes_count;
            }
        } catch (err) {
            console.error(err);
            showToast('Erro ao curtir.');
        } finally {
            btnLike.disabled = false;
        }
        return;
    }

    // 4. Repostar Post
    const btnRepost = e.target.closest('.btn-social-repost');
    if (btnRepost) {
        e.preventDefault();
        if (!confirm('Deseja repostar esta publicação?')) return;
        const postId = parseInt(btnRepost.dataset.id);
        btnRepost.disabled = true;

        try {
            const res = await fetch('?action=social_repost&ajax=1', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ post_id: postId })
            });

            if (!res.ok) throw new Error('Erro ao repostar');

            showToast('Repostado com sucesso!');
            navigateTo('?action=social');
        } catch (err) {
            console.error(err);
            showToast('Erro ao repostar.');
        } finally {
            btnRepost.disabled = false;
        }
        return;
    }
});

// Waveform visualizer drawing & animation engine (SoundCloud style)
function setupAudioVisualizer() {
    // CORS-safe implementation: visualizer runs dynamically in JS to guarantee perfect audio playback
}

function generateSignatureWaveform(title, artist) {
    const seedStr = (title || '') + (artist || '');
    let hash = 0;
    for (let i = 0; i < seedStr.length; i++) {
        hash = seedStr.charCodeAt(i) + ((hash << 5) - hash);
    }
    currentWaveformHeights = [];
    for (let i = 0; i < numBars; i++) {
        const val = 0.2 + Math.abs(Math.sin(hash + i * 1.5)) * 0.75;
        currentWaveformHeights.push(val);
    }
}

function drawWaveform() {
    const canvas = document.getElementById('waveform-canvas');
    if (!canvas) {
        requestAnimationFrame(drawWaveform);
        return;
    }
    const ctx = canvas.getContext('2d');
    if (!ctx) {
        requestAnimationFrame(drawWaveform);
        return;
    }
    
    const rect = canvas.getBoundingClientRect();
    if (canvas.width !== rect.width || canvas.height !== rect.height) {
        canvas.width = rect.width;
        canvas.height = rect.height;
    }
    
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    
    const pct = audio.duration ? (audio.currentTime / audio.duration) : 0;
    const barWidth = canvas.width / numBars;
    const gap = 2.5;
    const time = Date.now() * 0.006;
    
    const activeGrad = ctx.createLinearGradient(0, 0, canvas.width, 0);
    activeGrad.addColorStop(0, '#6c63ff');
    activeGrad.addColorStop(1, '#ff6584');
    
    for (let i = 0; i < numBars; i++) {
        const baseHeight = currentWaveformHeights[i] || 0.45;
        let scale = 1.0;
        
        if (!audio.paused) {
            // Pulse base frequency (beat)
            const beat = Math.sin(time * 3.5) * Math.cos(time * 1.5);
            const activeBeat = Math.max(0, beat) * 0.4;
            
            // Traveling wave along the bar indices
            const wave = Math.sin(time * 2.0 - i * 0.25) * 0.25;
            
            // Micro-jitter to simulate high-frequency ticks
            const jitter = Math.sin(time * 15.0 + i) * 0.08;
            
            scale = 0.55 + activeBeat + wave + jitter;
        }
        
        const height = Math.max(0.1, Math.min(1.1, baseHeight * scale));
        const hPixels = height * (canvas.height * 0.82);
        const x = i * barWidth + gap / 2;
        const y = (canvas.height - hPixels) / 2;
        const w = barWidth - gap;
        
        const isPast = (i / numBars) <= pct;
        ctx.fillStyle = isPast ? activeGrad : 'rgba(255, 255, 255, 0.12)';
        
        drawRoundedRect(ctx, x, y, w, hPixels, 2);
    }
    
    requestAnimationFrame(drawWaveform);
}

function drawRoundedRect(ctx, x, y, width, height, radius) {
    if (width <= 0 || height <= 0) return;
    ctx.beginPath();
    ctx.moveTo(x + radius, y);
    ctx.lineTo(x + width - radius, y);
    ctx.quadraticCurveTo(x + width, y, x + width, y + radius);
    ctx.lineTo(x + width, y + height - radius);
    ctx.quadraticCurveTo(x + width, y + height, x + width - radius, y + height);
    ctx.lineTo(x + radius, y + height);
    ctx.quadraticCurveTo(x, y + height, x, y + height - radius);
    ctx.lineTo(x, y + radius);
    ctx.quadraticCurveTo(x, y, x + radius, y);
    ctx.closePath();
    ctx.fill();
}

// Start continuous animation loop
requestAnimationFrame(drawWaveform);
