<?php

class AuthView {

    public function renderAuth(
        string $aba    = 'login',
        string $erro   = '',
        string $sucesso = '',
        string $cpfLembrado = ''
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
        <a href="?action=login"    class="<?= $aba === 'login'    ? 'active' : '' ?>">Entrar</a>
        <a href="?action=cadastro" class="<?= $aba === 'cadastro' ? 'active' : '' ?>">Cadastrar</a>
        <a href="?action=recuperar"class="<?= $aba === 'recuperar'? 'active' : '' ?>">Recuperar</a>
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

            <button type="submit" class="btn-primary">Criar Conta</button>
        </form>

        <div class="auth-footer">
            Já tem conta? <a href="?action=login">Entrar</a>
        </div>

        <?php elseif ($aba === 'recuperar'): ?>
        <form method="POST" action="?action=recuperar" novalidate>
            <input type="hidden" name="action" value="recuperar">

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
}
