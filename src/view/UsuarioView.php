<?php

class UsuarioView {

    public function formCriar(): void {
        ?>
        <h2>Criar Usuário</h2>
        <form method="POST" action="?action=criar">
            Nome: <input name="nome"><br>
            Email: <input type="email" name="email"><br>
            Senha: <input type="password" name="senha"><br>
            Palavra de recuperação: <input name="chave_recuperacao"><br>
            <button type="submit">Salvar</button>
        </form>
        <?php
    }

    public function mensagemSucesso(string $msg): void {
        echo "<p style='color:green;'> {$msg}</p>";
    }

    public function mensagemErro(string $msg): void {
        echo "<p style='color:red;'> {$msg}</p>";
    }
}