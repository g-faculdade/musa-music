<?php

class Usuario {

    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function criar(string $nome, string $email, string $senha, string $chave): bool {
        $sql = "INSERT INTO usuarios (nome, email, senha, chave_recuperacao)
                VALUES (:nome, :email, :senha, :chave)";

        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':nome'  => $nome,
            ':email' => $email,
            ':senha' => $senhaHash,
            ':chave' => $chave,
        ]);
    }

    public function buscarPorEmail(string $email): array|false {
        $sql  = "SELECT * FROM usuarios WHERE email = :email LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }

    public function buscarPorId(int $id): array|false {
        $sql  = "SELECT * FROM usuarios WHERE id = :id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function atualizar(int $id, string $nome, string $email): bool {
        $sql  = "UPDATE usuarios SET nome = :nome, email = :email WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':nome'  => $nome,
            ':email' => $email,
            ':id'    => $id,
        ]);
    }

    public function deletar(int $id): bool {
        $sql  = "DELETE FROM usuarios WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}