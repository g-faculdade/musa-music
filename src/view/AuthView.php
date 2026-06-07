<?php

class AuthView {

    public function renderAuth(
        string $aba    = 'login',
        string $erro   = '',
        string $sucesso = '',
        string $cpfLembrado = '',
        string $csrfToken = ''
    ): void {
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Musa Music – Acesso</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Syne:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg:        #0a0a0f;
            --surface:   #111118;
            --card:      #18181f;
            --border:    #2a2a38;
            --accent:    #6c63ff;
            --accent2:   #ff6584;
            --text:      #e8e8f0;
            --muted:     #7878a0;
        }

        *, *::before, *::after { 
            box-sizing: border-box; 
            margin: 0; 
            padding: 0; 
        }

        html, body {
            height: 100%;
            background: var(--bg);
            color: var(--text);
            font-family: 'DM Sans', sans-serif;
            overflow-x: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .auth-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 20px;
            width: 100%;
            max-width: 520px;
            box-shadow: 0 16px 48px rgba(0, 0, 0, 0.6);
            overflow: hidden;
            transition: transform 0.3s;
        }

        .auth-header {
            padding: 3.5rem 2rem 2rem;
            text-align: center;
        }
        
        .logo {
            font-family: 'Syne', sans-serif;
            font-weight: 800;
            font-size: 2.8rem;
            letter-spacing: -0.03em;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-block;
            margin-bottom: 0.6rem;
        }

        .auth-header p {
            font-size: 0.95rem;
            color: var(--muted);
            font-weight: 500;
        }

        .tabs {
            display: flex;
            background: rgba(255, 255, 255, 0.02);
            border-bottom: 1px solid var(--border);
        }
        .tabs a {
            flex: 1;
            text-align: center;
            padding: 1.25rem 0.5rem;
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--muted);
            text-decoration: none;
            transition: background 0.2s, color 0.2s, border-color 0.2s;
            border-bottom: 2px solid transparent;
            font-family: 'Syne', sans-serif;
        }
        .tabs a:hover { 
            background: rgba(255, 255, 255, 0.04); 
            color: var(--text); 
        }
        .tabs a.active {
            background: transparent;
            color: var(--accent);
            border-bottom: 2px solid var(--accent);
        }

        .auth-body { 
            padding: 2.5rem 2.25rem;
        }

        .alert {
            padding: 1rem 1.25rem;
            border-radius: 10px;
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
            line-height: 1.4;
        }
        .alert-erro { 
            background: rgba(231, 76, 60, 0.1); 
            color: #ff6b6b; 
            border: 1px solid rgba(231, 76, 60, 0.3); 
        }
        .alert-sucesso { 
            background: rgba(46, 204, 113, 0.1); 
            color: #2ecc71; 
            border: 1px solid rgba(46, 204, 113, 0.3); 
        }

        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--muted);
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }
        .form-group input {
            width: 100%;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 0.9rem 1.15rem;
            font-family: 'DM Sans', sans-serif;
            font-size: 1rem;
            color: var(--text);
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-group input::placeholder {
            color: rgba(120, 120, 160, 0.5);
        }
        .form-group input:focus { 
            border-color: var(--accent); 
            box-shadow: 0 0 0 3px rgba(108, 99, 255, 0.15);
        }
        .form-group select {
            width: 100%;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 0.9rem 1.15rem;
            font-family: 'DM Sans', sans-serif;
            font-size: 1rem;
            color: var(--text);
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%237878a0' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1.15rem center;
            background-size: 1.2rem;
            cursor: pointer;
        }
        .form-group select:focus { 
            border-color: var(--accent); 
            box-shadow: 0 0 0 3px rgba(108, 99, 255, 0.15);
        }

        .form-row {
            display: flex;
            gap: 1.25rem;
        }
        .form-row .form-group { 
            flex: 1; 
        }

        .btn-primary {
            width: 100%;
            padding: 1.05rem;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            color: #fff;
            border: none;
            border-radius: 10px;
            font-family: 'Syne', sans-serif;
            font-size: 1.05rem;
            font-weight: 800;
            cursor: pointer;
            transition: opacity 0.2s, transform 0.1s, box-shadow 0.2s;
            margin-top: 0.75rem;
            letter-spacing: 0.02em;
        }
        .btn-primary:hover { 
            opacity: 0.95;
            box-shadow: 0 8px 24px rgba(108, 99, 255, 0.35);
        }
        .btn-primary:active { 
            transform: scale(0.98); 
        }

        .auth-footer {
            text-align: center;
            font-size: 0.85rem;
            color: var(--muted);
            margin-top: 1.75rem;
            line-height: 1.6;
        }
        .auth-footer a { 
            color: var(--accent); 
            text-decoration: none; 
            font-weight: 700; 
            transition: color 0.15s;
        }
        .auth-footer a:hover { 
            color: var(--accent2);
            text-decoration: underline; 
        }

        .required {
            color: var(--accent2);
        }
    </style>
</head>
<body>

<div class="auth-card">

    <div class="auth-header">
        <div class="logo">Musa Music</div>
        <p>Sua plataforma de música favorita</p>
    </div>

    <div class="tabs">
        <?php if ($aba === 'recuperar'): ?>
            <a href="?action=recuperar" class="active">Recuperar</a>
        <?php else: ?>
            <a href="?action=login"    class="<?= $aba === 'login'    ? 'active' : '' ?>">Entrar</a>
            <a href="?action=cadastro" class="<?= $aba === 'cadastro' ? 'active' : '' ?>">Cadastrar</a>
        <?php endif; ?>
    </div>

    <div class="auth-body">

        <?php if ($erro): ?>
            <div class="alert alert-erro"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <?php if ($sucesso): ?>
            <div class="alert alert-sucesso"><?= htmlspecialchars($sucesso) ?></div>
        <?php endif; ?>

        <?php if ($aba === 'login'): ?>
        <form method="POST" action="?action=login" novalidate>
            <input type="hidden" name="action" value="login">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

            <div class="form-group">
                <label for="cpf-login">CPF</label>
                <input type="text" id="cpf-login" name="cpf"
                       placeholder="000.000.000-00"
                       maxlength="14"
                       required
                       value="<?= htmlspecialchars($cpfLembrado) ?>"
                       oninput="mascararCPF(this)">
            </div>

            <div class="form-group">
                <label for="senha-login">Senha</label>
                <input type="password" id="senha-login" name="senha"
                       placeholder="Sua senha" required>
            </div>

            <div class="form-group" style="display: flex; align-items: center; gap: 0.5rem; margin-top: -0.5rem; margin-bottom: 1.25rem;">
                <input type="checkbox" id="lembrar" name="lembrar" style="width: auto;" <?= $cpfLembrado !== '' ? 'checked' : '' ?>>
                <label for="lembrar" style="margin-bottom: 0; cursor: pointer; text-transform: none; font-size: 0.85rem; font-weight: 500;">Lembrar meu CPF</label>
            </div>

            <button type="submit" class="btn-primary">Entrar</button>
        </form>

        <div class="auth-footer">
            Não tem conta? <a href="?action=cadastro">Cadastre-se</a><br>
            <a href="?action=recuperar">Esqueci minha senha</a>
        </div>

        <?php elseif ($aba === 'cadastro'): ?>
        <form method="POST" action="?action=cadastro" novalidate>
            <input type="hidden" name="action" value="cadastro">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

            <div class="form-group">
                <label for="nome-cad">Nome completo <span class="required">*</span></label>
                <input type="text" id="nome-cad" name="nome"
                       placeholder="Seu nome completo" required>
            </div>

            <div class="form-group">
                <label for="email-cad">E-mail <span class="required">*</span></label>
                <input type="email" id="email-cad" name="email"
                       placeholder="seuemail@exemplo.com" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="cpf-cad">CPF <span class="required">*</span></label>
                    <input type="text" id="cpf-cad" name="cpf"
                           placeholder="000.000.000-00"
                           maxlength="14"
                           required
                           oninput="mascararCPF(this)">
                </div>
                <div class="form-group">
                    <label for="data-cad">Nascimento <span class="required">*</span></label>
                    <input type="date" id="data-cad" name="data_nascimento" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="senha-cad">Senha <span class="required">*</span></label>
                    <input type="password" id="senha-cad" name="senha"
                           placeholder="Nova senha" required minlength="6">
                </div>
                <div class="form-group">
                    <label for="conf-cad">Confirmar <span class="required">*</span></label>
                    <input type="password" id="conf-cad" name="confirmar_senha"
                           placeholder="Repita" required>
                </div>
            </div>

            <div class="form-group">
                <label for="tipo-cad">Tipo de Conta <span class="required">*</span></label>
                <select id="tipo-cad" name="tipo" required>
                    <option value="normal">Usuário Normal</option>
                    <option value="premium">Membro Premium 🎵</option>
                    <option value="artista">Artista Oficial (Selo Roxo)</option>
                </select>
            </div>

            <button type="submit" class="btn-primary">Criar Conta</button>
        </form>

        <div class="auth-footer">
            Já tem conta? <a href="?action=login">Entrar</a>
        </div>

        <?php elseif ($aba === 'recuperar'): ?>
        <form method="POST" action="?action=recuperar" novalidate>
            <input type="hidden" name="action" value="recuperar">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

            <p style="font-size:0.8rem;color:var(--muted);margin-bottom:1.25rem;line-height:1.4;">
                Informe seu <strong>CPF</strong> e <strong>data de nascimento</strong> cadastrados para redefinir sua senha.
            </p>

            <div class="form-group">
                <label for="cpf-rec">CPF</label>
                <input type="text" id="cpf-rec" name="cpf"
                       placeholder="000.000.000-00"
                       maxlength="14"
                       required
                       oninput="mascararCPF(this)">
            </div>

            <div class="form-group">
                <label for="data-rec">Data de nascimento</label>
                <input type="date" id="data-rec" name="data_nascimento" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="nova-senha">Nova senha</label>
                    <input type="password" id="nova-senha" name="nova_senha"
                           placeholder="Senha" required minlength="6">
                </div>
                <div class="form-group">
                    <label for="conf-rec">Confirmar</label>
                    <input type="password" id="conf-rec" name="confirmar_senha"
                           placeholder="Repita" required>
                </div>
            </div>

            <button type="submit" class="btn-primary">Redefinir Senha</button>
        </form>

        <div class="auth-footer">
            Lembrou a senha? <a href="?action=login">Entrar</a>
        </div>

        <?php endif; ?>

        <div style="margin-top: 2rem; padding-top: 1.25rem; border-top: 1px solid var(--border); display: flex; justify-content: center; gap: 15px; font-size: 0.8rem; flex-wrap: wrap; text-align: center;">
            <a href="?action=player" style="color: var(--muted); text-decoration: none; font-weight: 500; transition: color 0.2s;" onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='var(--muted)'">Conheça o Player</a>
            <span style="color: var(--border);">|</span>
            <a href="?action=planos" style="color: var(--muted); text-decoration: none; font-weight: 500; transition: color 0.2s;" onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='var(--muted)'">Nossos Planos</a>
            <span style="color: var(--border);">|</span>
            <a href="?action=social_media" style="color: var(--muted); text-decoration: none; font-weight: 500; transition: color 0.2s;" onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='var(--muted)'">Rede Social</a>
            <span style="color: var(--border);">|</span>
            <a href="?action=sobre" style="color: var(--muted); text-decoration: none; font-weight: 500; transition: color 0.2s;" onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='var(--muted)'">Sobre Nós</a>
        </div>

    </div>
</div>

<script>
function mascararCPF(input) {
    let v = input.value.replace(/\D/g, '').slice(0, 11);
    if (v.length > 9)      v = v.replace(/^(\d{3})(\d{3})(\d{3})(\d{0,2})/, '$1.$2.$3-$4');
    else if (v.length > 6) v = v.replace(/^(\d{3})(\d{3})(\d{0,3})/,        '$1.$2.$3');
    else if (v.length > 3) v = v.replace(/^(\d{3})(\d{0,3})/,               '$1.$2');
    input.value = v;
}
</script>

</body>
</html>
<?php
    }

    public function renderOpenPage(string $page): void {
        $title = match($page) {
            'player'       => 'Conheça o Player – Musa Music',
            'planos'       => 'Nossos Planos – Musa Music',
            'social_media' => 'Rede Social – Musa Music',
            'sobre'        => 'Sobre Nós – Musa Music',
            default        => 'Musa Music'
        };
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Syne:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg:        #0a0a0f;
            --surface:   #111118;
            --card:      #18181f;
            --border:    #2a2a38;
            --accent:    #6c63ff;
            --accent2:   #ff6584;
            --text:      #e8e8f0;
            --muted:     #7878a0;
        }

        *, *::before, *::after { 
            box-sizing: border-box; 
            margin: 0; 
            padding: 0; 
        }

        html, body {
            height: 100%;
            background: var(--bg);
            color: var(--text);
            font-family: 'DM Sans', sans-serif;
            overflow-x: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .info-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 20px;
            width: 100%;
            max-width: 650px;
            box-shadow: 0 16px 48px rgba(0, 0, 0, 0.6);
            overflow: hidden;
            padding: 2.5rem;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            color: var(--accent);
            text-decoration: none;
            font-weight: 700;
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
            transition: color 0.2s;
        }
        .back-link:hover {
            color: var(--accent2);
        }

        .page-title {
            font-family: 'Syne', sans-serif;
            font-weight: 800;
            font-size: 2.2rem;
            letter-spacing: -0.03em;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1.5rem;
        }

        .content-section {
            line-height: 1.6;
            font-size: 0.95rem;
            color: #d1d1d6;
        }

        .content-section p {
            margin-bottom: 1.25rem;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.25rem;
            margin-top: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .feature-item {
            background: var(--card);
            border: 1px solid var(--border);
            padding: 1.25rem;
            border-radius: 12px;
            transition: transform 0.2s, border-color 0.2s;
        }
        .feature-item:hover {
            transform: translateY(-3px);
            border-color: var(--accent);
        }
        .feature-item h3 {
            font-family: 'Syne', sans-serif;
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: #fff;
        }
        .feature-item p {
            font-size: 0.85rem;
            color: var(--muted);
            margin-bottom: 0;
        }

        /* Pricing Table Styles */
        .price-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 1rem;
            margin-top: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .price-card {
            background: var(--card);
            border: 1px solid var(--border);
            padding: 1.5rem 1rem;
            border-radius: 15px;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform 0.2s, border-color 0.2s;
        }
        .price-card.popular {
            border-color: var(--accent);
            box-shadow: 0 8px 20px rgba(108, 99, 255, 0.15);
            position: relative;
        }
        .price-card.popular::before {
            content: 'Popular';
            position: absolute;
            top: -10px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            color: #fff;
            font-size: 0.7rem;
            font-weight: 800;
            padding: 2px 10px;
            border-radius: 10px;
            text-transform: uppercase;
        }
        .price-card:hover {
            transform: translateY(-5px);
        }
        .price-name {
            font-family: 'Syne', sans-serif;
            font-weight: 700;
            font-size: 1.1rem;
            color: #fff;
            margin-bottom: 0.5rem;
        }
        .price-value {
            font-size: 1.4rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 1rem;
        }
        .price-value span {
            font-size: 0.8rem;
            color: var(--muted);
        }
        .price-features {
            list-style: none;
            text-align: left;
            font-size: 0.8rem;
            color: var(--muted);
            margin-bottom: 1.5rem;
        }
        .price-features li {
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .btn-pricing {
            width: 100%;
            padding: 0.6rem;
            background: var(--border);
            color: var(--text);
            border: none;
            border-radius: 8px;
            font-family: 'Syne', sans-serif;
            font-weight: 700;
            font-size: 0.85rem;
            cursor: pointer;
            text-decoration: none;
            display: block;
            transition: background 0.2s, color 0.2s;
            text-align: center;
        }
        .price-card.popular .btn-pricing {
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            color: #fff;
        }
        .btn-pricing:hover {
            opacity: 0.9;
        }

        @media (max-width: 600px) {
            .feature-grid, .price-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<div class="info-card">
    <a href="?action=login" class="back-link">← Voltar para o Acesso</a>
    
    <?php if ($page === 'player'): ?>
        <h1 class="page-title">Nosso Player</h1>
        <div class="content-section">
            <p>O player do <strong>Musa Music</strong> foi desenvolvido com tecnologias de ponta para oferecer a melhor experiência musical diretamente no seu navegador, integrando-se nativamente com a poderosa API Deezer.</p>
            
            <div class="feature-grid">
                <div class="feature-item">
                    <h3>♪ Áudio de Alta Qualidade</h3>
                    <p>Transmissão contínua em formato de alta qualidade com busca inteligente de metadados.</p>
                </div>
                <div class="feature-item">
                    <h3>📊 Waveform Dinâmico</h3>
                    <p>Um visualizador de onda em canvas que pulsa em sincronia perfeita com o ritmo da música tocada.</p>
                </div>
                <div class="feature-item">
                    <h3>↓ Modo Offline</h3>
                    <p>Baixe as faixas favoritas diretamente para o servidor e ouça-as mesmo sem acesso à API.</p>
                </div>
                <div class="feature-item">
                    <h3>📂 Fila Dinâmica</h3>
                    <p>Navegação SPA instantânea que atualiza e gerencia a fila de reprodução sem interrupção de áudio.</p>
                </div>
            </div>
            <p>Criamos um player robusto que suporta atalhos de reprodução, controle fino de volume, e sincronização de dados instantânea com o banco de dados.</p>
        </div>
        
    <?php elseif ($page === 'planos'): ?>
        <h1 class="page-title">Planos e Assinaturas</h1>
        <div class="content-section">
            <p>Escolha o plano ideal para o seu perfil e desfrute de vantagens exclusivas na melhor plataforma de música e interação social.</p>
            
            <div class="price-grid">
                <div class="price-card">
                    <div>
                        <div class="price-name">Usuário Normal</div>
                        <div class="price-value">Grátis</div>
                        <ul class="price-features">
                            <li>✓ Player completo</li>
                            <li>✓ Busca Deezer</li>
                            <li>✓ Criar Playlists</li>
                            <li>✓ Postar no Feed</li>
                        </ul>
                    </div>
                    <a href="?action=cadastro" class="btn-pricing">Começar</a>
                </div>
                <div class="price-card popular">
                    <div>
                        <div class="price-name">Membro Premium</div>
                        <div class="price-value">R$ 14,90<span>/mês</span></div>
                        <ul class="price-features">
                            <li>✓ Tudo do Grátis</li>
                            <li>✓ Downloads ilimitados</li>
                            <li>✓ Selo Premium 🎵</li>
                            <li>✓ Zero Anúncios</li>
                        </ul>
                    </div>
                    <a href="?action=cadastro" class="btn-pricing">Assinar</a>
                </div>
                <div class="price-card">
                    <div>
                        <div class="price-name">Artista Oficial</div>
                        <div class="price-value">R$ 29,90<span>/mês</span></div>
                        <ul class="price-features">
                            <li>✓ Tudo do Premium</li>
                            <li>✓ Selo de Verificação</li>
                            <li>✓ Upload de músicas</li>
                            <li>✓ Estatísticas e Feed</li>
                        </ul>
                    </div>
                    <a href="?action=cadastro" class="btn-pricing">Criar Canal</a>
                </div>
            </div>
        </div>

    <?php elseif ($page === 'social_media'): ?>
        <h1 class="page-title">Rede Social Musa</h1>
        <div class="content-section">
            <p>O <strong>Musa Music</strong> vai além da reprodução: ele conecta você a amigos e outros amantes da música através de uma rede social integrada em tempo real.</p>
            
            <div class="feature-grid">
                <div class="feature-item">
                    <h3>✉ Compartilhar Faixas</h3>
                    <p>Anexe músicas tocadas recentemente ou playlists completas em suas publicações com um clique.</p>
                </div>
                <div class="feature-item">
                    <h3>💬 Respostas & Discussão</h3>
                    <p>Comente nas postagens de amigos e debata sobre os últimos lançamentos do mundo musical.</p>
                </div>
                <div class="feature-item">
                    <h3>🔁 Repostar</h3>
                    <p>Gostou de uma indicação musical de outra pessoa? Reposte-a no seu feed instantaneamente.</p>
                </div>
                <div class="feature-item">
                    <h3>♥ Curtidas & Interação</h3>
                    <p>Deixe o seu feedback rápido e acompanhe quais publicações estão fazendo mais sucesso.</p>
                </div>
            </div>
        </div>

    <?php elseif ($page === 'sobre'): ?>
        <h1 class="page-title">Sobre Nós</h1>
        <div class="content-section">
            <p>O <strong>Musa Music</strong> nasceu da paixão por conectar pessoas por meio do som. Acreditamos que a música é uma linguagem universal que merece um espaço de consumo e compartilhamento unificado.</p>
            <p>O projeto foi projetado utilizando as melhores práticas acadêmicas e de engenharia de software:</p>
            
            <div class="feature-grid">
                <div class="feature-item">
                    <h3>📦 Padrão MVC</h3>
                    <p>Organização limpa e escalável separando lógica de negócio, persistência de banco de dados e views.</p>
                </div>
                <div class="feature-item">
                    <h3>🛡 Segurança Ativa</h3>
                    <p>Prevenção ativa contra CSRF, senhas criptografadas com hash de segurança e validação severa de dados.</p>
                </div>
                <div class="feature-item">
                    <h3>⚙ Banco Relacional</h3>
                    <p>Uso de banco de dados MySQL via transações PDO organizadas com chaves estrangeiras robustas.</p>
                </div>
                <div class="feature-item">
                    <h3>⚡ Navegação Instantânea</h3>
                    <p>Interações SPA rápidas com chamadas assíncronas assentes no formato JSON.</p>
                </div>
            </div>
            <p style="margin-top: 1rem; text-align: center; color: var(--muted); font-size: 0.8rem;">Musa Music © 2026 – Todos os direitos reservados.</p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
<?php
        exit;
    }
}

