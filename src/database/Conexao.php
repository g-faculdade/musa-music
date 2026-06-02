<?php
 
class Conexao {
 
    private static ?PDO $instancia = null;
 
    private const HOST   = 'localhost';
    private const BANCO  = 'musa_music';
    private const USUARIO = 'root';
    private const SENHA  = '';      
    private const PORTA  = '3306';
 
    private function __construct() {}
 
    public static function getInstance(): PDO {
        if (self::$instancia === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                self::HOST,
                self::PORTA,
                self::BANCO
            );
 
            try {
                self::$instancia = new PDO($dsn, self::USUARIO, self::SENHA, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
                self::checkAndCreateSocialTables(self::$instancia);
            } catch (PDOException $e) {
                die("Erro na conexão com o banco: " . $e->getMessage());
            }
        }
 
        return self::$instancia;
    }

    private static function checkAndCreateSocialTables(PDO $pdo): void {
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE 'posts'");
            if ($stmt->rowCount() === 0) {
                $pdo->exec("
                    CREATE TABLE IF NOT EXISTS `posts` (
                      `id` INT AUTO_INCREMENT PRIMARY KEY,
                      `usuario_id` INT NOT NULL,
                      `conteudo` TEXT NOT NULL,
                      `musica_id` BIGINT NULL,
                      `playlist_id` INT NULL,
                      `original_post_id` INT NULL,
                      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                      FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                      FOREIGN KEY (`original_post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                ");

                $pdo->exec("
                    CREATE TABLE IF NOT EXISTS `post_likes` (
                      `post_id` INT NOT NULL,
                      `usuario_id` INT NOT NULL,
                      PRIMARY KEY (`post_id`, `usuario_id`),
                      FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                      FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                ");

                $pdo->exec("
                    CREATE TABLE IF NOT EXISTS `post_comments` (
                      `id` INT AUTO_INCREMENT PRIMARY KEY,
                      `post_id` INT NOT NULL,
                      `usuario_id` INT NOT NULL,
                      `conteudo` TEXT NOT NULL,
                      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                      FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                      FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                ");
            }

            // Auto migration: add played_at column to user_music_status if it doesn't exist
            $stmtCol = $pdo->query("SHOW COLUMNS FROM `user_music_status` LIKE 'played_at'");
            if ($stmtCol->rowCount() === 0) {
                $pdo->exec("ALTER TABLE `user_music_status` ADD COLUMN `played_at` TIMESTAMP NULL DEFAULT NULL");
            }

            // Auto migration: add bio column to usuarios if it doesn't exist
            $stmtBio = $pdo->query("SHOW COLUMNS FROM `usuarios` LIKE 'bio'");
            if ($stmtBio->rowCount() === 0) {
                $pdo->exec("ALTER TABLE `usuarios` ADD COLUMN `bio` VARCHAR(255) DEFAULT NULL");
            }

            // Auto migration: add tipo column to usuarios if it doesn't exist
            $stmtTipo = $pdo->query("SHOW COLUMNS FROM `usuarios` LIKE 'tipo'");
            if ($stmtTipo->rowCount() === 0) {
                $pdo->exec("ALTER TABLE `usuarios` ADD COLUMN `tipo` VARCHAR(20) NOT NULL DEFAULT 'normal'");
            }
        } catch (PDOException $e) {
            // Silently ignore to avoid breaking the application
        }
    }
}