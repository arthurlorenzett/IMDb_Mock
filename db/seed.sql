
CREATE DATABASE IF NOT EXISTS imdb_clone
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_0900_ai_ci;

USE imdb_clone;

-- =====================================================
-- TABELA: titulos
-- =====================================================

CREATE TABLE IF NOT EXISTS titulos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(255) NOT NULL UNIQUE,
    titulo VARCHAR(255) NOT NULL,
    tipo ENUM('filme', 'serie') NOT NULL,
    ano SMALLINT UNSIGNED NOT NULL,
    lancamento DATE,
    duracao_min SMALLINT UNSIGNED,
    episodios SMALLINT UNSIGNED,
    classificacao VARCHAR(2) NOT NULL,
    sinopse TEXT,
    direcao VARCHAR(255),
    elenco TEXT,
    poster VARCHAR(2048),
    banner VARCHAR(2048),
    trailer VARCHAR(2048),
    popularidade DECIMAL(8,2) DEFAULT 0,
    destaque BOOLEAN NOT NULL DEFAULT FALSE,
    criado_em DATETIME NOT NULL
) ENGINE=InnoDB;

-- =====================================================
-- TABELA: generos
-- =====================================================

CREATE TABLE IF NOT EXISTS generos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- =====================================================
-- TABELA: titulo_genero
-- =====================================================

CREATE TABLE IF NOT EXISTS titulo_genero (
    titulo_id INT UNSIGNED NOT NULL,
    genero_id INT UNSIGNED NOT NULL,

    PRIMARY KEY (titulo_id, genero_id),

    FOREIGN KEY (titulo_id)
        REFERENCES titulos(id)
        ON DELETE CASCADE,

    FOREIGN KEY (genero_id)
        REFERENCES generos(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- TABELA: avaliacoes
-- =====================================================

CREATE TABLE IF NOT EXISTS avaliacoes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titulo_id INT UNSIGNED NOT NULL,
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(254) NOT NULL,
    nota TINYINT UNSIGNED NOT NULL,
    titulo VARCHAR(150) NOT NULL,
    comentario TEXT NOT NULL,
    status ENUM('pendente', 'aprovada', 'rejeitada')
        NOT NULL DEFAULT 'pendente',
    criada_em DATETIME NOT NULL,

    CONSTRAINT chk_nota CHECK (nota BETWEEN 1 AND 10),

    CONSTRAINT uq_avaliacao_email
        UNIQUE (titulo_id, email),

    FOREIGN KEY (titulo_id)
        REFERENCES titulos(id)
        ON DELETE CASCADE,

    INDEX idx_avaliacoes_status (status),
    INDEX idx_avaliacoes_titulo_status (titulo_id, status)
) ENGINE=InnoDB;

-- =====================================================
-- TABELA: administradores
-- =====================================================

CREATE TABLE IF NOT EXISTS administradores (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(254) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

