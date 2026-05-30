<?php

require_once __DIR__ . '/../model/Usuario.php';
require_once __DIR__ . '/../view/AuthView.php';

class Controller {

    private Usuario $usuario;
    private AuthView $view;

    public function __construct(PDO $pdo) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->usuario = new Usuario($pdo);
        $this->view    = new AuthView();
    }

    public function handleRequest(): void {
        $action = $_POST['action'] ?? $_GET['action'] ?? 'login';

        switch ($action) {
            case 'login':
                $this->handleLogin();
                break;

            case 'cadastro':
                $this->handleCadastro();
                break;

            case 'recuperar':
                $this->handleRecuperar();
                break;

            default:
                $cpfLembrado = $_COOKIE['lembrar_cpf'] ?? '';
                $this->view->renderAuth('login', cpfLembrado: $cpfLembrado);
        }
    }

    private function handleLogin(): void {
        $cpfLembrado = $_COOKIE['lembrar_cpf'] ?? '';
        if (!empty($_POST)) {
            $cpf   = trim($_POST['cpf']   ?? '');
            $senha = $_POST['senha']       ?? '';

            if (!$cpf || !$senha) {
                $this->view->renderAuth('login', erro: 'Preencha o CPF e a senha.', cpfLembrado: $cpfLembrado);
                return;
            }

            $usuario = $this->usuario->login($cpf, $senha);

            if ($usuario) {
                $_SESSION['usuario_id']   = $usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['nome'];

                $lembrar = isset($_POST['lembrar']);
                if ($lembrar) {
                    setcookie('lembrar_cpf', $cpf, time() + (30 * 24 * 60 * 60), "/");
                } else {
                    setcookie('lembrar_cpf', '', time() - 3600, "/");
                }

                header('Location: ?action=painel');
                exit;
            }

            $this->view->renderAuth('login', erro: 'CPF ou senha inválidos.', cpfLembrado: $cpfLembrado);
            return;
        }

        $this->view->renderAuth('login', cpfLembrado: $cpfLembrado);
    }

    private function handleCadastro(): void {
        if (!empty($_POST)) {
            $nome      = trim($_POST['nome']            ?? '');
            $email     = trim($_POST['email']           ?? '');
            $cpf       = trim($_POST['cpf']             ?? '');
            $dataNasc  = trim($_POST['data_nascimento'] ?? '');
            $senha     = $_POST['senha']                ?? '';
            $confirmSenha = $_POST['confirmar_senha']   ?? '';

            if (!$nome || !$email || !$cpf || !$dataNasc || !$senha) {
                $this->view->renderAuth('cadastro', erro: 'Preencha todos os campos obrigatórios.');
                return;
            }

            $cpfLimpo = preg_replace('/\D/', '', $cpf);
            if (strlen($cpfLimpo) !== 11) {
                $this->view->renderAuth('cadastro', erro: 'CPF inválido. Informe 11 dígitos.');
                return;
            }

            if ($senha !== $confirmSenha) {
                $this->view->renderAuth('cadastro', erro: 'As senhas não coincidem.');
                return;
            }

            if ($this->usuario->buscarPorEmail($email)) {
                $this->view->renderAuth('cadastro', erro: 'E-mail já cadastrado.');
                return;
            }

            $ok = $this->usuario->criar($nome, $email, $senha, $cpf, $dataNasc);

            if ($ok) {
                $cpfLembrado = $_COOKIE['lembrar_cpf'] ?? '';
                $this->view->renderAuth('login', sucesso: 'Cadastro realizado! Faça seu login.', cpfLembrado: $cpfLembrado);
            } else {
                $this->view->renderAuth('cadastro', erro: 'Erro ao cadastrar. Tente novamente.');
            }
            return;
        }

        $this->view->renderAuth('cadastro');
    }

    private function handleRecuperar(): void {
        if (!empty($_POST)) {
            $cpf      = trim($_POST['cpf']             ?? '');
            $dataNasc = trim($_POST['data_nascimento'] ?? '');
            $novaSenha = $_POST['nova_senha']          ?? '';
            $confirmSenha = $_POST['confirmar_senha']  ?? '';

            if (!$cpf || !$dataNasc || !$novaSenha) {
                $this->view->renderAuth('recuperar', erro: 'Preencha todos os campos.');
                return;
            }

            if ($novaSenha !== $confirmSenha) {
                $this->view->renderAuth('recuperar', erro: 'As senhas não coincidem.');
                return;
            }

            $usuario = $this->usuario->buscarPorCpfEData($cpf, $dataNasc);

            if (!$usuario) {
                $this->view->renderAuth('recuperar', erro: 'CPF ou data de nascimento não encontrados.');
                return;
            }

            $ok = $this->usuario->atualizarSenha($usuario['id'], $novaSenha);

            if ($ok) {
                $cpfLembrado = $_COOKIE['lembrar_cpf'] ?? '';
                $this->view->renderAuth('login', sucesso: 'Senha atualizada com sucesso! Faça seu login.', cpfLembrado: $cpfLembrado);
            } else {
                $this->view->renderAuth('recuperar', erro: 'Erro ao atualizar a senha. Tente novamente.');
            }
            return;
        }

        $this->view->renderAuth('recuperar');
    }
}