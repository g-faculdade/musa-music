CREATE DATABASE IF NOT EXISTS musa_music CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE musa_music;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    cpf VARCHAR(11) NOT NULL UNIQUE,
    data_nascimento DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS musicas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(150) NOT NULL,
    artista VARCHAR(100) NOT NULL,
    album VARCHAR(100) NULL,
    genero VARCHAR(50) NOT NULL,
    duracao VARCHAR(10) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS playlists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS playlist_musicas (
    playlist_id INT NOT NULL,
    musica_id INT NOT NULL,
    PRIMARY KEY (playlist_id, musica_id),
    FOREIGN KEY (playlist_id) REFERENCES playlists(id) ON DELETE CASCADE,
    FOREIGN KEY (musica_id) REFERENCES musicas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO musicas (id, titulo, artista, album, genero, duracao) VALUES
(1, 'Blinding Lights', 'The Weeknd', 'After Hours', 'Synthwave', '3:20'),
(2, 'Shape of You', 'Ed Sheeran', 'Divide', 'Pop', '3:53'),
(3, 'Bohemian Rhapsody', 'Queen', 'A Night at the Opera', 'Rock', '5:55'),
(4, 'Levitating', 'Dua Lipa', 'Future Nostalgia', 'Dance-Pop', '3:23'),
(5, 'Stay', 'The Kid LAROI & Justin Bieber', 'F*CK LOVE 3', 'Pop-Rock', '2:21');
