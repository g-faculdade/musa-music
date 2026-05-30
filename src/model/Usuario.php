<?php

class Usuario {

    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function criar(
        string $nome,
        string $email,
        string $senha,
        string $cpf,
        string $data_nascimento
    ): bool {
        $sql = "INSERT INTO usuarios (nome, email, senha, cpf, data_nascimento)
                VALUES (:nome, :email, :senha, :cpf, :data_nascimento)";

        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':nome'            => $nome,
            ':email'           => $email,
            ':senha'           => $senhaHash,
            ':cpf'             => preg_replace('/\D/', '', $cpf),
            ':data_nascimento' => $data_nascimento,
        ]);
    }

    public function login(string $cpf, string $senha): array|false {
        $cpfLimpo = preg_replace('/\D/', '', $cpf);
        $sql  = "SELECT * FROM usuarios WHERE cpf = :cpf LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':cpf' => $cpfLimpo]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($senha, $usuario['senha'])) {
            return $usuario;
        }
        return false;
    }

    public function buscarPorCpfEData(string $cpf, string $data_nascimento): array|false {
        $cpfLimpo = preg_replace('/\D/', '', $cpf);
        $sql  = "SELECT * FROM usuarios WHERE cpf = :cpf AND data_nascimento = :data LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':cpf' => $cpfLimpo, ':data' => $data_nascimento]);
        return $stmt->fetch();
    }

    public function atualizarSenha(int $id, string $novaSenha): bool {
        $sql  = "UPDATE usuarios SET senha = :senha WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':senha' => password_hash($novaSenha, PASSWORD_DEFAULT),
            ':id'    => $id,
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

    public function deletar(int $id): bool {
        $sql  = "DELETE FROM usuarios WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}