<?php

require_once __DIR__ . '/../model/Usuario.php';
require_once __DIR__ . '/../view/UsuarioView.php';

class Controller {

    private Usuario $usuario;
    private UsuarioView $view;

    public function __construct(PDO $pdo) {
        $this->usuario = new Usuario($pdo);
        $this->view    = new UsuarioView();
    }

    public function handleRequest(): void {
        $action = $_GET['action'] ?? 'criar';

        switch ($action) {
            case 'criar':
                $this->handleCriar();
                break;

            default:
                $this->view->formCriar();
                break;
        }
    }

    private function handleCriar(): void {
        if (!empty($_POST)) {
            $nome  = trim($_POST['nome']  ?? '');
            $email = trim($_POST['email'] ?? '');
            $senha = $_POST['senha']      ?? '';
            $chave = trim($_POST['chave_recuperacao'] ?? '');

            if ($nome && $email && $senha) {
                $ok = $this->usuario->criar($nome, $email, $senha, $chave);

                if ($ok) {
                    $this->view->mensagemSucesso("Usuário criado com sucesso!");
                } else {
                    $this->view->mensagemErro("Erro ao criar usuário.");
                }
                return;
            }

            $this->view->mensagemErro("Preencha todos os campos obrigatórios.");
        }

        $this->view->formCriar();
    }
}