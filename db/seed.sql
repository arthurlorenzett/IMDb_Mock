-- =====================================================
-- IMDb Clone · Grupo 9 · Desenvolvimento Web I (IFC Concórdia)
-- Script de criação do banco: estrutura + dados de exemplo + usuário de teste.
--
-- XAMPP: phpMyAdmin → Importar → este arquivo.
-- Terminal: mysql -u root < db/seed.sql
-- Docker: o docker-compose.yml roda este arquivo na primeira subida.
--
-- Pode ser importado de novo para voltar aos dados originais
-- (as tabelas são apagadas e recriadas).
-- Usuário de teste do painel: admin@exemplo.com / admin123
-- =====================================================

CREATE DATABASE IF NOT EXISTS imdb_clone
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE imdb_clone;

-- Apaga na ordem inversa das chaves estrangeiras
DROP TABLE IF EXISTS titulo_genero;
DROP TABLE IF EXISTS avaliacoes;
DROP TABLE IF EXISTS generos;
DROP TABLE IF EXISTS titulos;
DROP TABLE IF EXISTS administradores;

-- =====================================================
-- TABELA: titulos
-- Média e votos não são colunas: são calculados a partir
-- das avaliações aprovadas (RN02).
-- =====================================================

CREATE TABLE titulos (
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
    elenco TEXT,                -- nomes separados por ", "
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

CREATE TABLE generos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- =====================================================
-- TABELA: titulo_genero
-- =====================================================

CREATE TABLE titulo_genero (
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
-- RN01: UNIQUE (titulo_id, email)
-- RN02: toda avaliação nasce 'pendente'
-- =====================================================

CREATE TABLE avaliacoes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titulo_id INT UNSIGNED NOT NULL,
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(254) NOT NULL,
    nota TINYINT UNSIGNED NOT NULL,
    titulo VARCHAR(150) NOT NULL DEFAULT '',
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
-- A senha fica só como hash (password_hash do PHP).
-- =====================================================

CREATE TABLE administradores (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(254) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =====================================================
-- DADOS DE EXEMPLO
-- (os mesmos de data/titulos.json e data/avaliacoes.json)
-- =====================================================

-- Gêneros (data/site.json → catalogo.generos)
INSERT INTO generos (id, nome) VALUES
    (1, 'Ação'),
    (2, 'Animação'),
    (3, 'Aventura'),
    (4, 'Comédia'),
    (5, 'Documentário'),
    (6, 'Drama'),
    (7, 'Família'),
    (8, 'Fantasia'),
    (9, 'Ficção científica'),
    (10, 'Policial'),
    (11, 'Romance'),
    (12, 'Suspense'),
    (13, 'Terror');

-- 24 títulos
INSERT INTO titulos (id, slug, titulo, tipo, ano, lancamento, duracao_min, episodios, classificacao, sinopse, direcao, elenco, poster, banner, trailer, popularidade, destaque, criado_em) VALUES
    (1, 'um-sonho-de-liberdade', 'Um Sonho de Liberdade', 'filme', 1994, '1995-03-17', 142, NULL, '16', 'Condenado à prisão perpétua por um crime que diz não ter cometido, um bancário tenta manter a esperança dentro de uma penitenciária violenta e constrói, ao longo de duas décadas, uma amizade improvável com outro detento.', 'Frank Darabont', 'Tim Robbins, Morgan Freeman, Bob Gunton', 'https://m.media-amazon.com/images/M/MV5BMDAyY2FhYjctNDc5OS00MDNlLThiMGUtY2UxYWVkNGY2ZjljXkEyXkFqcGc@._V1_SX300.jpg', 'https://m.media-amazon.com/images/M/MV5BNjQ2NDA3MDcxMF5BMl5BanBnXkFtZTgwMjE5NTU0NzE@._V1_SX1280.jpg', 'https://www.imdb.com/pt/video/vi3877612057/', 90, 0, '2026-11-01 10:00:00'),
    (2, 'o-poderoso-chefao', 'O Poderoso Chefão', 'filme', 1972, '1972-09-10', 175, NULL, '14', 'O patriarca de uma poderosa família mafiosa de Nova York sofre um atentado, e o filho caçula, que queria distância dos negócios da família, acaba sendo arrastado para o centro da disputa pelo poder.', 'Francis Ford Coppola', 'Marlon Brando, Al Pacino, James Caan', 'https://m.media-amazon.com/images/M/MV5BYTRmMjkwYzYtYTRiMi00NDJjLTk1NjctMDA3MjY2ZWIyMGQ5XkEyXkFqcGc@._V1_SX300.jpg', 'https://m.media-amazon.com/images/M/MV5BYWNlN2U4YjQtMzI3NC00ZjkxLWEwMTItYWRlNDUxYTYwYjVlXkEyXkFqcGdeQWpvaG5oYXJ0._V1_SX1280.jpg', 'https://www.imdb.com/pt/video/vi1348706585/', 86, 0, '2026-11-01 10:05:00'),
    (3, 'batman-o-cavaleiro-das-trevas', 'Batman: O Cavaleiro das Trevas', 'filme', 2008, '2008-07-18', 152, NULL, '12', 'Batman, o comissário Gordon e o novo promotor de Gotham se unem contra o crime organizado, até que um criminoso imprevisível, o Coringa, decide testar até onde vão os princípios de cada um deles.', 'Christopher Nolan', 'Christian Bale, Heath Ledger, Aaron Eckhart', 'https://m.media-amazon.com/images/M/MV5BMTRkYjU1MjQtODMxMS00YTNmLThkOTYtZThlMjE4YWZkZDI0XkEyXkFqcGc@._V1_SX300.jpg', 'https://m.media-amazon.com/images/M/MV5BNWJkYWJlOWMtY2ZhZi00YWM0LTliZDktYmRiMGYwNzczMTZhXkEyXkFqcGdeQXVyNzU1NzE3NTg@._V1_SX1280.jpg', 'https://www.imdb.com/pt/video/vi324468761/', 95, 1, '2026-11-01 10:10:00'),
    (4, 'a-origem', 'A Origem', 'filme', 2010, '2010-08-06', 148, NULL, '14', 'Um especialista em roubar segredos de dentro dos sonhos recebe uma proposta inversa: implantar uma ideia na mente de um herdeiro. Para isso, ele e sua equipe precisam descer por várias camadas de sonho ao mesmo tempo.', 'Christopher Nolan', 'Leonardo DiCaprio, Joseph Gordon-Levitt, Elliot Page', 'https://m.media-amazon.com/images/M/MV5BZmIxYzJkYWQtOWQ4ZS00NjhlLTk0OTMtN2I4ZWQxZGEyZDVhXkEyXkFqcGc@._V1_SX300.jpg', 'https://m.media-amazon.com/images/M/MV5BMTQ1ZmIzOTAtNDcwZi00NDVkLWE4NWItYWNhZGY1MmVlZGU0XkEyXkFqcGdeQWRvb2xpbmhk._V1_SX1280.jpg', 'https://www.imdb.com/pt/video/vi2959588889/', 88, 0, '2026-11-01 10:15:00'),
    (5, 'interestelar', 'Interestelar', 'filme', 2014, '2014-11-06', 169, NULL, '10', 'Com a Terra devastada por pragas nas lavouras, um ex-piloto deixa os filhos para trás e parte com uma equipe de cientistas por um buraco de minhoca em busca de um novo planeta onde a humanidade possa viver.', 'Christopher Nolan', 'Matthew McConaughey, Anne Hathaway, Jessica Chastain', 'https://m.media-amazon.com/images/M/MV5BMmUwMmFlMzktYWVlNy00N2I0LWFhMTYtZWI2ZTM4N2I3ZTk0XkEyXkFqcGc@._V1_SX300.jpg', 'https://m.media-amazon.com/images/M/MV5BNjM5OTQzMTg4N15BMl5BanBnXkFtZTgwOTgyMjM0NTE@._V1_SX1280.jpg', 'https://www.imdb.com/pt/video/vi1586278169/', 92, 1, '2026-11-01 10:20:00'),
    (6, 'cidade-de-deus', 'Cidade de Deus', 'filme', 2002, '2002-08-30', 130, NULL, '16', 'Entre os anos 1960 e 1980, um jovem que sonha ser fotógrafo acompanha o crescimento da violência e do tráfico em uma comunidade do Rio de Janeiro, enquanto amigos de infância seguem caminhos muito diferentes.', 'Fernando Meirelles e Kátia Lund', 'Alexandre Rodrigues, Leandro Firmino, Matheus Nachtergaele', 'https://m.media-amazon.com/images/M/MV5BNTUyODNkNTItMGUwNi00MTlhLWE5YzMtOTA5MTljNTFhNGRhXkEyXkFqcGc@._V1_SX300.jpg', 'https://m.media-amazon.com/images/M/MV5BNTQzY2M4ZTctZWRjZS00NzlmLWI4YzgtMDUzZGU5YjlmYzkxXkEyXkFqcGdeQXVyNzU1NzE3NTg@._V1_SX1280.jpg', 'https://www.imdb.com/pt/video/vi1783208985/', 84, 1, '2026-11-01 10:25:00'),
    (7, 'breaking-bad', 'Breaking Bad', 'serie', 2008, '2008-01-20', 48, 62, '16', 'Um professor de química com um diagnóstico grave decide garantir o futuro da família produzindo drogas com um ex-aluno. A cada temporada, o homem pacato se aprofunda mais no mundo do crime.', 'Vince Gilligan', 'Bryan Cranston, Aaron Paul, Anna Gunn', 'https://m.media-amazon.com/images/M/MV5BMzU5ZGYzNmQtMTdhYy00OGRiLTg0NmQtYjVjNzliZTg1ZGE4XkEyXkFqcGc@._V1_SX300.jpg', 'https://m.media-amazon.com/images/M/MV5BMTJiMzgwZTktYzZhZC00YzhhLWEzZDUtMGM2NTE4MzQ4NGFmXkEyXkFqcGdeQWpybA@@._V1_SX1280.jpg', 'https://www.imdb.com/pt/video/vi338798873/', 99, 1, '2026-11-01 10:30:00'),
    (8, 'parasita', 'Parasita', 'filme', 2019, '2019-11-07', 132, NULL, '16', 'Uma família pobre de Seul consegue, um a um, empregos na casa de uma família rica, escondendo que são parentes. O plano parece perfeito até que um segredo guardado na mansão muda tudo.', 'Bong Joon Ho', 'Song Kang-ho, Lee Sun-kyun, Cho Yeo-jeong', 'https://m.media-amazon.com/images/M/MV5BYjk1Y2U4MjQtY2ZiNS00OWQyLWI3MmYtZWUwNmRjYWRiNWNhXkEyXkFqcGc@._V1_SX300.jpg', 'https://m.media-amazon.com/images/M/MV5BYWJjNWYzMzItYmEyNC00ODdmLWJmOGEtMWY0N2E2NGJlNTY0XkEyXkFqcGdeQWFybm8@._V1_SX1280.jpg', 'https://www.imdb.com/pt/video/vi1015463705/', 80, 0, '2026-11-01 10:35:00'),
    (9, 'o-auto-da-compadecida', 'O Auto da Compadecida', 'filme', 2000, '2000-09-10', 104, NULL, 'L', 'No sertão da Paraíba, os amigos João Grilo e Chicó sobrevivem à base de esperteza e mentiras, enganando o padre, o padeiro e o coronel, até que as trapaças os levam a um julgamento muito especial.', 'Guel Arraes', 'Matheus Nachtergaele, Selton Mello, Rogério Cardoso', 'https://m.media-amazon.com/images/M/MV5BYzE4YzlmNjctNGFmOC00Nzg3LWFlOWQtMDU4YTA0MTJhODY3XkEyXkFqcGc@._V1_SX300.jpg', 'https://m.media-amazon.com/images/M/MV5BOTliNTYyZTgtMjQzYS00ODVlLWFkMWEtMGVjZmM0MTJlMzJmXkEyXkFqcGdeQWRpZWdtb25n._V1_SX1280.jpg', 'https://www.imdb.com/pt/video/vi1285472793/', 78, 0, '2026-11-01 10:40:00'),
    (10, 'o-iluminado', 'O Iluminado', 'filme', 1980, '1980-12-25', 146, NULL, '16', 'Um escritor aceita cuidar de um hotel isolado nas montanhas durante o inverno, levando a esposa e o filho. Com a neve bloqueando as estradas, a presença do lugar começa a afetar a sanidade dele.', 'Stanley Kubrick', 'Jack Nicholson, Shelley Duvall, Danny Lloyd', 'https://m.media-amazon.com/images/M/MV5BMGMzODJhZmYtODFhOC00N2Y2LThkMDEtNzAxMGUwMTFlNWQxXkEyXkFqcGc@._V1_SX300.jpg', 'https://m.media-amazon.com/images/M/MV5BN2NmM2ZlMmMtN2Q0MS00N2FkLWE4NzUtMzljNTIyNGJjNmE0XkEyXkFqcGdeQTNwaW5nZXN0._V1_SX1280.jpg', 'https://www.imdb.com/pt/video/vi2689121305/', 74, 0, '2026-11-01 10:45:00'),
    (11, 'a-viagem-de-chihiro', 'A Viagem de Chihiro', 'filme', 2001, '2003-07-18', 124, NULL, 'L', 'Durante uma mudança de cidade, uma menina de dez anos entra com os pais em um mundo de espíritos. Depois que os pais são transformados, ela precisa trabalhar em uma casa de banhos mágica para salvá-los.', 'Hayao Miyazaki', 'Miyu Irino, Rumi Hiiragi, Mari Natsuki', 'https://m.media-amazon.com/images/M/MV5BYmZmMmM4OTYtMDkyNi00ZDI5LThiODItNzhlZGI3ZDJmZDZiXkEyXkFqcGc@._V1_SX300.jpg', 'https://m.media-amazon.com/images/M/MV5BMzZjOWEyNTEtNjhmMC00YmZlLTkyMzAtM2Y5MzFjYmJlODIzXkEyXkFqcGdeQXVyNzU1NzE3NTg@._V1_SX1280.jpg', 'https://www.imdb.com/pt/video/vi3619684633/', 82, 0, '2026-11-01 10:50:00'),
    (12, 'planeta-terra-ii', 'Planeta Terra II', 'serie', 2016, '2016-11-06', 50, 6, '10', 'Série documental que mostra a vida selvagem em ilhas, montanhas, selvas, desertos, campos e cidades, acompanhando como os animais se adaptam a cada um desses ambientes.', '', 'David Attenborough', 'https://m.media-amazon.com/images/M/MV5BMzY4NDBkMWYtYzdkYy00YzBjLWJmODctMWM4YjYzZTdjNWE5XkEyXkFqcGc@._V1_SX300.jpg', 'https://m.media-amazon.com/images/M/MV5BMzBjOGVlODctMjgzNi00OGMwLTkzYjYtYTIwZjcyZDUwOTQ2XkEyXkFqcGdeQXVyNzkyNzc3NjU@._V1_SX1280.jpg', 'https://www.imdb.com/pt/video/vi2095757337/', 70, 0, '2026-11-01 10:55:00'),
    (13, 'matrix', 'Matrix', 'filme', 1999, '1999-05-21', 136, NULL, '14', 'Um programador que leva uma vida dupla como hacker descobre que o mundo à sua volta é uma simulação controlada por máquinas e é convidado a se juntar à resistência que luta para libertar a humanidade.', 'Lana Wachowski e Lilly Wachowski', 'Keanu Reeves, Laurence Fishburne, Carrie-Anne Moss', 'https://m.media-amazon.com/images/M/MV5BNThiYTQzOTUtNmFjYy00OWEwLTg2NWUtMmM3NGE0YjFjOWRjXkEyXkFqcGc@._V1_SX300.jpg', 'https://m.media-amazon.com/images/M/MV5BNDQ4NTRmN2ItYjgzMS00MzY3LWEwNmYtYmE2ODllZDdhNGI1XkEyXkFqcGdeQXdvbmtpbQ@@._V1_SX1280.jpg', 'https://www.imdb.com/pt/video/vi1032782617/', 87, 1, '2026-11-02 10:00:00'),
    (14, 'o-senhor-dos-aneis-a-sociedade-do-anel', 'O Senhor dos Anéis: A Sociedade do Anel', 'filme', 2001, '2002-01-01', 178, NULL, '12', 'Um jovem hobbit herda um anel de poder cobiçado pelo senhor das trevas e parte com um grupo de companheiros de diferentes povos em uma longa viagem para destruí-lo.', 'Peter Jackson', 'Elijah Wood, Ian McKellen, Orlando Bloom', 'https://m.media-amazon.com/images/M/MV5BNzIxMDQ2YTctNDY4MC00ZTRhLTk4ODQtMTVlOWY4NTdiYmMwXkEyXkFqcGc@._V1_SX300.jpg', 'https://m.media-amazon.com/images/M/MV5BMzQ2YjI2YzgtZmNmMy00ZjUyLWE0NGItY2Y3NjUzZWM4NmM3XkEyXkFqcGdeQWxiaWFtb250._V1_SX1280.jpg', 'https://www.imdb.com/pt/video/vi684573465/', 85, 0, '2026-11-02 10:05:00'),
    (15, 'forrest-gump', 'Forrest Gump - O Contador de Histórias', 'filme', 1994, '1994-12-07', 142, NULL, '14', 'Sentado em um ponto de ônibus, um homem de bom coração conta como, sem perceber, participou de momentos marcantes da história dos Estados Unidos enquanto nunca deixou de pensar no amor de infância.', 'Robert Zemeckis', 'Tom Hanks, Robin Wright, Gary Sinise', 'https://m.media-amazon.com/images/M/MV5BOGE2OTRmZTAtN2ViZS00YTEzLWIxYzQtMjc1YmNmODBmZDdmXkEyXkFqcGc@._V1_SX300.jpg', 'https://m.media-amazon.com/images/M/MV5BYzQyMWQ1ODMtZGE2OS00YzQyLWE2OWYtZmU5OGFiMWMwYmIyXkEyXkFqcGdeQXVyNzU1NzE3NTg@._V1_SX1280.jpg', 'https://www.imdb.com/pt/video/vi3567517977/', 79, 0, '2026-11-02 10:10:00'),
    (16, 'clube-da-luta', 'Clube da Luta', 'filme', 1999, '1999-10-29', 139, NULL, '18', 'Um funcionário de escritório que sofre de insônia conhece um vendedor de sabonetes carismático, e os dois criam um clube secreto de lutas que logo se transforma em algo muito maior e mais perigoso.', 'David Fincher', 'Brad Pitt, Edward Norton, Meat Loaf', 'https://m.media-amazon.com/images/M/MV5BNjQ3NzM1ZWMtMTExZS00YWU3LTk2MGYtNDI1NDkxZDhiMWJjXkEyXkFqcGc@._V1_SX300.jpg', 'https://m.media-amazon.com/images/M/MV5BMzVjNDRkMTYtMTczYS00ZTEyLWI3NTQtYTc3NWE4MTY3OGNkXkEyXkFqcGdeQWRvb2xpbmhk._V1_SX1280.jpg', 'https://www.imdb.com/pt/video/vi123455001/', 77, 0, '2026-11-02 10:15:00'),
    (17, 'o-silencio-dos-inocentes', 'O Silêncio dos Inocentes', 'filme', 1991, '1991-05-17', 118, NULL, '14', 'Uma aluna da academia do FBI precisa da ajuda de um psiquiatra brilhante e perigoso, preso por seus crimes, para entender a mente de um assassino em série que continua à solta.', 'Jonathan Demme', 'Jodie Foster, Anthony Hopkins, Scott Glenn', 'https://m.media-amazon.com/images/M/MV5BMTBlY2FjM2EtMWIxYy00NWE2LWE0MWUtMjI2NjZjZDE2YWYwXkEyXkFqcGc@._V1_SX300.jpg', 'https://m.media-amazon.com/images/M/MV5BN2ZiYzIyMWQtNDUyNy00YmExLWIwNmMtN2FhNzFiODgyYmU4XkEyXkFqcGdeQXRodW1ibmFpbC1pbml0aWFsaXplcg@@._V1_SX1280.jpg', 'https://www.imdb.com/pt/video/vi3377380121/', 72, 0, '2026-11-02 10:20:00'),
    (18, 'toy-story', 'Toy Story', 'filme', 1995, '1996-01-12', 81, NULL, 'L', 'O caubói de brinquedo preferido de um menino sente o lugar ameaçado com a chegada de um astronauta moderno. A rivalidade entre os dois os leva a se perder longe de casa, e eles precisam se unir para voltar.', 'John Lasseter', 'Tom Hanks, Tim Allen, Don Rickles', 'https://m.media-amazon.com/images/M/MV5BODdhYzcxYmYtOTAzZS00YTRkLTgzODYtNGUzN2Y2ZjNlZTBmXkEyXkFqcGc@._V1_SX300.jpg', 'https://m.media-amazon.com/images/M/MV5BMjAwOTYzODExMF5BMl5BanBnXkFtZTcwNDYyNjc4Mg@@._V1_SX1280.jpg', 'https://www.imdb.com/pt/video/vi2052129305/', 76, 0, '2026-11-02 10:25:00'),
    (19, 'tropa-de-elite', 'Tropa de Elite', 'filme', 2007, '2007-10-12', 115, NULL, '16', 'No Rio de Janeiro de 1997, um capitão do BOPE procura um substituto antes do nascimento do filho, enquanto dois policiais novatos tentam sobreviver à corrupção e à violência dentro e fora da corporação.', 'José Padilha', 'Wagner Moura, André Ramiro, Caio Junqueira', 'https://m.media-amazon.com/images/M/MV5BZTE1ZTlhNWEtNTAwMi00MmUzLTk3MGEtZjJiYjc3NTc0ZTc1XkEyXkFqcGc@._V1_SX300.jpg', 'https://m.media-amazon.com/images/M/MV5BMTc3ODI0MTQ1OF5BMl5BanBnXkFtZTcwODM0MjQ5MQ@@._V1_SX1280.jpg', 'https://www.imdb.com/pt/video/vi3323330585/', 83, 1, '2026-11-02 10:30:00'),
    (20, 'o-rei-leao', 'O Rei Leão', 'filme', 1994, '1994-07-08', 88, NULL, 'L', 'Depois de uma tragédia armada pelo tio ambicioso, um jovem leão foge do reino que deveria herdar e cresce longe de casa, até perceber que precisa voltar e assumir o seu lugar.', 'Roger Allers e Rob Minkoff', 'Matthew Broderick, Jeremy Irons, James Earl Jones', 'https://m.media-amazon.com/images/M/MV5BNzJmNDVmNTAtODI3Mi00Y2Y2LTg4MDItNGI0ODE4NTQ1ZmNlXkEyXkFqcGc@._V1_SX300.jpg', 'https://m.media-amazon.com/images/M/MV5BMTU1MzkwNDI5OV5BMl5BanBnXkFtZTgwODU2MTkwMDI@._V1_SX1280.jpg', 'https://www.imdb.com/pt/video/vi3764362265/', 75, 0, '2026-11-02 10:35:00'),
    (21, 'gladiador', 'Gladiador', 'filme', 2000, '2000-05-19', 155, NULL, '14', 'Traído pelo filho do imperador, um general romano perde a família e é vendido como escravo. Transformado em gladiador, ele luta nas arenas até chegar a Roma, onde pode enfim buscar justiça.', 'Ridley Scott', 'Russell Crowe, Joaquin Phoenix, Connie Nielsen', 'https://m.media-amazon.com/images/M/MV5BNWQxNzkzMmEtNTliNy00NWU2LWI0ZjgtNTI1NjBjZGRmZjAzXkEyXkFqcGc@._V1_SX300.jpg', 'https://m.media-amazon.com/images/M/MV5BMTU1NTQ0OTkyNF5BMl5BanBnXkFtZTgwMTQzMjIwMDI@._V1_SX1280.jpg', 'https://www.imdb.com/pt/video/vi2628367897/', 73, 0, '2026-11-02 10:40:00'),
    (22, 'de-volta-para-o-futuro', 'De Volta para o Futuro', 'filme', 1985, '1985-12-25', 116, NULL, 'L', 'Um adolescente viaja sem querer para 1955 em uma máquina do tempo criada por um cientista excêntrico e precisa garantir que os pais se apaixonem, ou deixará de existir.', 'Robert Zemeckis', 'Michael J. Fox, Christopher Lloyd, Lea Thompson', 'https://m.media-amazon.com/images/M/MV5BZDcyNmYxN2QtMmViZS00NTQ4LTlhZTAtZjc4MzY2Yjg0M2ZmXkEyXkFqcGc@._V1_SX300.jpg', 'https://m.media-amazon.com/images/M/MV5BNzQ0MmJjMzEtZTE4Zi00ZmU5LTlhYTEtMzdjY2EwOTliY2JkXkEyXkFqcGdeQXNvbG5vbXM@._V1_SX1280.jpg', 'https://www.imdb.com/pt/video/vi1126811673/', 71, 0, '2026-11-02 10:45:00'),
    (23, 'stranger-things', 'Stranger Things', 'serie', 2016, '2016-07-15', 60, 42, '14', 'Nos anos 1980, o desaparecimento de um menino em uma pequena cidade americana leva os amigos, a mãe e o delegado a descobrir experimentos secretos e uma menina com poderes misteriosos.', 'Matt Duffer e Ross Duffer', 'Millie Bobby Brown, Finn Wolfhard, Winona Ryder', 'https://m.media-amazon.com/images/M/MV5BMjEzMDAxOTUyMV5BMl5BanBnXkFtZTgwNzAxMzYzOTE@._V1_SX300.jpg', 'https://m.media-amazon.com/images/M/MV5BNTViYjE3NjUtYWM1MS00ODhmLTgwYmItZWQ5YzRlZTNmNGI4XkEyXkFqcGdeQWFybm8@._V1_SX1280.jpg', 'https://www.imdb.com/pt/video/vi412273433/', 96, 0, '2026-11-02 10:50:00'),
    (24, 'game-of-thrones', 'Game of Thrones', 'serie', 2011, '2011-05-08', 60, 74, '18', 'Famílias nobres disputam o Trono de Ferro dos Sete Reinos com alianças, traições e guerras, enquanto uma ameaça antiga desperta além da grande muralha do norte.', 'David Benioff e D.B. Weiss', 'Emilia Clarke, Peter Dinklage, Kit Harington', 'https://m.media-amazon.com/images/M/MV5BNGYxOGJkMjItZjVkZC00OGEzLWExNjktOTZmNGZhZmRlMTk2XkEyXkFqcGc@._V1_SX300.jpg', 'https://m.media-amazon.com/images/M/MV5BZTg4YzdjNTctNDg5Mi00ZmU1LTkzOWEtNmMyNDBjZjNhNTJiXkEyXkFqcGdeQXRyYW5zY29kZS13b3JrZmxvdw@@._V1_SX1280.jpg', 'https://www.imdb.com/pt/video/vi59490329/', 93, 0, '2026-11-02 10:55:00');

INSERT INTO titulo_genero (titulo_id, genero_id) VALUES
    (1, 6),
    (2, 10),
    (2, 6),
    (3, 10),
    (3, 12),
    (4, 3),
    (4, 9),
    (4, 12),
    (5, 3),
    (5, 9),
    (6, 10),
    (6, 6),
    (7, 10),
    (7, 6),
    (7, 12),
    (8, 6),
    (8, 12),
    (9, 4),
    (9, 6),
    (9, 8),
    (10, 6),
    (10, 13),
    (11, 2),
    (11, 3),
    (11, 7),
    (12, 5),
    (12, 7),
    (13, 1),
    (13, 9),
    (14, 3),
    (14, 6),
    (14, 8),
    (15, 6),
    (15, 11),
    (16, 10),
    (16, 6),
    (16, 12),
    (17, 10),
    (17, 6),
    (17, 13),
    (18, 2),
    (18, 3),
    (18, 4),
    (19, 1),
    (19, 10),
    (19, 6),
    (20, 2),
    (20, 3),
    (20, 6),
    (21, 1),
    (21, 3),
    (21, 6),
    (22, 3),
    (22, 4),
    (22, 9),
    (23, 6),
    (23, 8),
    (23, 13),
    (24, 6),
    (24, 8);

-- 88 avaliações de usuários fictícios (@exemplo.com)
INSERT INTO avaliacoes (id, titulo_id, nome, email, nota, titulo, comentario, status, criada_em) VALUES
    (1, 1, 'Marina Souza', 'marina.souza@exemplo.com', 9, 'Esperança do começo ao fim', 'A narração do Morgan Freeman é perfeita e o final é um dos mais bonitos que já vi. Vale cada minuto.', 'aprovada', '2026-10-12 19:20:00'),
    (2, 1, 'Rafael Teixeira', 'rafael.teixeira@exemplo.com', 7, 'Bom, mas longo', 'Atuações ótimas, porém a parte do meio se arrasta um pouco. Ainda assim recomendo.', 'aprovada', '2026-10-05 21:02:00'),
    (3, 1, 'Júlia Campos', 'julia.campos@exemplo.com', 8, 'Amizade que emociona', 'A relação entre Andy e Red é o coração do filme. Saí com vontade de rever.', 'aprovada', '2026-10-18 15:40:00'),
    (4, 1, 'Pedro Alves', 'pedro.alves@exemplo.com', 9, 'Clássico merecido', 'Tim Robbins carrega o filme com uma atuação contida. Entendi por que está no topo do ranking.', 'aprovada', '2026-10-22 10:15:00'),
    (5, 1, 'Camila Rocha', 'camila.rocha@exemplo.com', 8, '', 'Gostei muito da reviravolta, só achei o ritmo do primeiro ato um pouco lento.', 'aprovada', '2026-10-30 18:05:00'),
    (6, 1, 'Lucas Martins', 'lucas.martins@exemplo.com', 9, 'Obra-prima', 'Revi depois de anos e continua emocionante. A trilha sonora é linda.', 'pendente', '2026-11-15 22:10:00'),
    (7, 2, 'Marina Souza', 'marina.souza@exemplo.com', 8, 'Cinema de verdade', 'Cada cena parece um quadro. A abertura no casamento já mostra quem é cada personagem.', 'aprovada', '2026-09-28 20:00:00'),
    (8, 2, 'Rafael Teixeira', 'rafael.teixeira@exemplo.com', 1, 'Compre seguidores', 'Visite meu perfil para ganhar seguidores grátis agora mesmo!!!', 'rejeitada', '2026-10-01 03:12:00'),
    (9, 2, 'Júlia Campos', 'julia.campos@exemplo.com', 8, 'Atuações gigantes', 'Marlon Brando e Al Pacino em estado de graça. A transformação do Michael é assustadora.', 'aprovada', '2026-10-03 14:30:00'),
    (10, 2, 'Lucas Martins', 'lucas.martins@exemplo.com', 7, 'Pede paciência', 'É lento para os padrões de hoje, mas a direção do Coppola segura tudo.', 'aprovada', '2026-10-09 19:45:00'),
    (11, 2, 'Beatriz Lima', 'beatriz.lima@exemplo.com', 8, '', 'Assisti com meu pai, que viu no cinema em 1972. Agora entendo o carinho dele por esse filme.', 'aprovada', '2026-10-20 11:00:00'),
    (12, 3, 'Rafael Teixeira', 'rafael.teixeira@exemplo.com', 8, 'O Coringa rouba a cena', 'Heath Ledger está assustador. Cada cena com ele prende a atenção.', 'aprovada', '2026-09-14 21:30:00'),
    (13, 3, 'Pedro Alves', 'pedro.alves@exemplo.com', 8, 'Mais que um filme de herói', 'Parece um filme policial com um herói mascarado. O dilema da balsa é genial.', 'aprovada', '2026-09-30 16:20:00'),
    (14, 3, 'Camila Rocha', 'camila.rocha@exemplo.com', 7, '', 'Gostei, mas o filme poderia ter uns vinte minutos a menos no final.', 'aprovada', '2026-10-11 09:50:00'),
    (15, 3, 'Marina Souza', 'marina.souza@exemplo.com', 8, 'Duas caras mal aproveitado', 'Ótimo filme, mas o arco do Harvey Dent merecia mais tempo de tela.', 'pendente', '2026-11-16 13:05:00'),
    (16, 4, 'Júlia Campos', 'julia.campos@exemplo.com', 8, 'Quebra-cabeça bem montado', 'As camadas de sonho são confusas no começo, mas tudo se encaixa. O pião no final é perfeito.', 'aprovada', '2026-08-25 20:10:00'),
    (17, 4, 'Lucas Martins', 'lucas.martins@exemplo.com', 7, 'Longo demais', 'A primeira hora é ótima, depois as explicações cansam um pouco.', 'aprovada', '2026-09-02 22:00:00'),
    (18, 4, 'Beatriz Lima', 'beatriz.lima@exemplo.com', 8, '', 'Os efeitos do corredor girando são de tirar o fôlego. Assisti duas vezes seguidas.', 'aprovada', '2026-09-19 17:35:00'),
    (19, 4, 'Gustavo Nunes', 'gustavo.nunes@exemplo.com', 7, 'Trilha marcante', 'A música do Hans Zimmer faz metade do trabalho. Esperava um pouco mais de emoção.', 'aprovada', '2026-10-07 12:25:00'),
    (20, 5, 'Marina Souza', 'marina.souza@exemplo.com', 7, 'Ciência e emoção', 'A cena das mensagens dos filhos me fez chorar. Ótimo para ver no cinema.', 'aprovada', '2026-08-10 15:00:00'),
    (21, 5, 'Camila Rocha', 'camila.rocha@exemplo.com', 8, 'Visual impressionante', 'O buraco negro e o planeta das ondas são lindos. A química do elenco funciona.', 'aprovada', '2026-08-21 21:15:00'),
    (22, 5, 'Gustavo Nunes', 'gustavo.nunes@exemplo.com', 7, '', 'Algumas explicações de física passam rápido demais, mas o filme é bonito.', 'aprovada', '2026-09-05 19:40:00'),
    (23, 6, 'Rafael Teixeira', 'rafael.teixeira@exemplo.com', 8, 'Retrato forte', 'Um filme brasileiro que não deve nada a ninguém. A montagem é muito criativa.', 'aprovada', '2026-07-30 23:00:00'),
    (24, 6, 'Júlia Campos', 'julia.campos@exemplo.com', 6, 'Difícil de assistir', 'A violência é pesada demais para mim, mesmo reconhecendo a qualidade.', 'aprovada', '2026-08-14 18:20:00'),
    (25, 6, 'Pedro Alves', 'pedro.alves@exemplo.com', 7, '', 'O elenco formado por jovens da própria comunidade dá uma verdade rara ao filme.', 'aprovada', '2026-09-01 20:45:00'),
    (26, 6, 'Camila Rocha', 'camila.rocha@exemplo.com', 2, 'Contando o final', 'Vou contar o que acontece com cada personagem, então não percam tempo assistindo.', 'rejeitada', '2026-09-12 01:30:00'),
    (27, 7, 'Marina Souza', 'marina.souza@exemplo.com', 9, 'Melhor série que já vi', 'Cada temporada é melhor que a anterior. Maratonei em poucas semanas.', 'aprovada', '2026-05-02 22:30:00'),
    (28, 7, 'Rafael Teixeira', 'rafael.teixeira@exemplo.com', 8, 'Transformação incrível', 'A mudança do Walter White ao longo da série é muito bem construída.', 'aprovada', '2026-05-10 20:05:00'),
    (29, 7, 'Camila Rocha', 'camila.rocha@exemplo.com', 9, '', 'Bryan Cranston e Aaron Paul têm uma parceria perfeita. A quinta temporada é impecável.', 'aprovada', '2026-06-01 21:50:00'),
    (30, 7, 'Lucas Martins', 'lucas.martins@exemplo.com', 8, 'Roteiro amarrado', 'Detalhes do primeiro episódio voltam lá no final. Muito bem planejada.', 'aprovada', '2026-06-18 19:00:00'),
    (31, 7, 'Beatriz Lima', 'beatriz.lima@exemplo.com', 8, 'Tensão constante', 'Mesmo nos episódios mais calmos dá para sentir que algo vai dar errado.', 'aprovada', '2026-07-04 23:10:00'),
    (32, 7, 'Júlia Campos', 'julia.campos@exemplo.com', 7, 'Começo lento', 'A primeira temporada demora a engrenar, mas depois a série fica muito boa.', 'pendente', '2026-11-14 20:40:00'),
    (33, 8, 'Pedro Alves', 'pedro.alves@exemplo.com', 7, 'Crítica social inteligente', 'A diferença entre a casa rica e o porão é mostrada de um jeito muito criativo.', 'aprovada', '2026-06-11 22:00:00'),
    (34, 8, 'Lucas Martins', 'lucas.martins@exemplo.com', 7, '', 'Começa como comédia e termina como suspense. Não esperava aquela virada.', 'aprovada', '2026-06-29 20:30:00'),
    (35, 8, 'Beatriz Lima', 'beatriz.lima@exemplo.com', 7, 'Merecia o Oscar', 'Roteiro original de verdade. Fiquei pensando nele por dias.', 'aprovada', '2026-07-15 21:15:00'),
    (36, 8, 'Gustavo Nunes', 'gustavo.nunes@exemplo.com', 7, 'Bom, mas estranho', 'Gostei da primeira metade, o final me pareceu exagerado.', 'aprovada', '2026-08-02 18:45:00'),
    (37, 9, 'Marina Souza', 'marina.souza@exemplo.com', 6, 'Engraçado em partes', 'Algumas piadas funcionam muito bem, outras se repetem demais.', 'aprovada', '2026-04-09 16:00:00'),
    (38, 9, 'Júlia Campos', 'julia.campos@exemplo.com', 7, '', 'João Grilo e Chicó são uma dupla impagável. O julgamento final é muito bom.', 'aprovada', '2026-04-20 19:25:00'),
    (39, 9, 'Lucas Martins', 'lucas.martins@exemplo.com', 1, 'Odiei tudo', 'Odiei tudo odiei tudo odiei tudo odiei tudo odiei tudo.', 'rejeitada', '2026-05-01 02:00:00'),
    (40, 9, 'Gustavo Nunes', 'gustavo.nunes@exemplo.com', 7, 'Cara do Brasil', 'Lembra as histórias que meus avós contavam. Elenco muito à vontade.', 'aprovada', '2026-05-17 14:10:00'),
    (41, 10, 'Rafael Teixeira', 'rafael.teixeira@exemplo.com', 5, 'Lento demais', 'Entendo a importância, mas achei arrastado e com poucos sustos.', 'aprovada', '2026-11-02 23:40:00'),
    (42, 10, 'Camila Rocha', 'camila.rocha@exemplo.com', 6, '', 'O hotel é assustador, mas o final deixa muitas perguntas sem resposta.', 'aprovada', '2026-11-05 22:20:00'),
    (43, 10, 'Pedro Alves', 'pedro.alves@exemplo.com', 7, 'Melhor do que lembrava', 'Revi agora e a tensão no corredor do hotel continua funcionando.', 'pendente', '2026-11-16 21:00:00'),
    (44, 11, 'Lucas Martins', 'lucas.martins@exemplo.com', 9, 'Para toda a família', 'Meus sobrinhos ficaram encantados e eu também. A casa de banhos é cheia de detalhes.', 'aprovada', '2026-08-30 17:00:00'),
    (45, 11, 'Beatriz Lima', 'beatriz.lima@exemplo.com', 8, '', 'Animação caprichada e uma história bonita sobre coragem e amizade.', 'aprovada', '2026-09-08 15:30:00'),
    (46, 12, 'Gustavo Nunes', 'gustavo.nunes@exemplo.com', 9, 'Emocionante', 'As imagens são inacreditáveis. O episódio das cidades é o meu favorito.', 'aprovada', '2026-07-22 20:00:00'),
    (47, 12, 'Júlia Campos', 'julia.campos@exemplo.com', 9, 'Aula de natureza', 'Deveria ser exibido nas escolas. A narração do David Attenborough é perfeita.', 'pendente', '2026-11-13 19:15:00'),
    (48, 13, 'Ana Beatriz Costa', 'ana.costa@exemplo.com', 9, 'Mudou o cinema de ação', 'As cenas em câmera lenta continuam impressionantes. Roteiro inteligente e cheio de ideias.', 'aprovada', '2026-09-03 20:10:00'),
    (49, 13, 'Felipe Ramos', 'felipe.ramos@exemplo.com', 9, '', 'Assisti pela primeira vez agora e entendi por que todo mundo cita esse filme.', 'aprovada', '2026-09-15 21:30:00'),
    (50, 13, 'Lucas Martins', 'lucas.martins@exemplo.com', 8, 'Pílula vermelha', 'A primeira metade é perfeita. A parte final tem tiroteio demais para o meu gosto.', 'aprovada', '2026-10-02 19:00:00'),
    (51, 13, 'Marina Souza', 'marina.souza@exemplo.com', 8, 'Clássico', 'Os efeitos envelheceram muito bem para um filme de 1999.', 'pendente', '2026-11-16 22:40:00'),
    (52, 14, 'Sofia Mendes', 'sofia.mendes@exemplo.com', 10, 'Fantasia perfeita', 'A trilha, os cenários e o elenco formam o melhor começo de trilogia que existe.', 'aprovada', '2026-08-20 20:00:00'),
    (53, 14, 'Beatriz Lima', 'beatriz.lima@exemplo.com', 9, '', 'A cena das minas de Moria é tensa do começo ao fim. Vale a versão estendida.', 'aprovada', '2026-09-01 18:45:00'),
    (54, 14, 'Pedro Alves', 'pedro.alves@exemplo.com', 9, 'Jornada inesquecível', 'Ian McKellen como Gandalf é inesquecível. Fiquei com vontade de ler os livros.', 'aprovada', '2026-09-22 21:10:00'),
    (55, 14, 'Vinícius Prado', 'vinicius.prado@exemplo.com', 8, 'Longo, mas vale', 'São quase três horas, mas passam rápido. Só achei o começo no Condado um pouco lento.', 'aprovada', '2026-10-10 16:30:00'),
    (56, 15, 'Camila Rocha', 'camila.rocha@exemplo.com', 9, 'Emocionante', 'Tom Hanks está perfeito. Ri e chorei no mesmo filme.', 'aprovada', '2026-08-11 20:20:00'),
    (57, 15, 'Gustavo Nunes', 'gustavo.nunes@exemplo.com', 8, '', 'Uma história simples contada de um jeito muito bonito. A trilha sonora é ótima.', 'aprovada', '2026-09-07 19:35:00'),
    (58, 15, 'Ana Beatriz Costa', 'ana.costa@exemplo.com', 8, 'Caixa de bombons', 'Gosto de como a história do país aparece pelo olhar ingênuo do Forrest.', 'aprovada', '2026-10-14 21:00:00'),
    (59, 15, 'Felipe Ramos', 'felipe.ramos@exemplo.com', 3, 'Promoção imperdível', 'Clique no link do meu perfil e ganhe um cupom de desconto!!!', 'rejeitada', '2026-10-20 03:15:00'),
    (60, 16, 'Rafael Teixeira', 'rafael.teixeira@exemplo.com', 8, 'Reviravolta marcante', 'Não esperava aquele final. Precisei assistir de novo para pegar os detalhes.', 'aprovada', '2026-09-18 23:00:00'),
    (61, 16, 'Sofia Mendes', 'sofia.mendes@exemplo.com', 9, '', 'Crítica ácida ao consumismo, com Edward Norton e Brad Pitt muito bem.', 'aprovada', '2026-10-05 22:15:00'),
    (62, 16, 'Vinícius Prado', 'vinicius.prado@exemplo.com', 9, 'Filme para debater', 'Rendeu uma conversa enorme com os amigos depois da sessão.', 'pendente', '2026-11-15 20:50:00'),
    (63, 17, 'Júlia Campos', 'julia.campos@exemplo.com', 8, 'Tensão pura', 'As conversas através do vidro são as melhores cenas. Anthony Hopkins assusta com pouco.', 'aprovada', '2026-08-29 22:30:00'),
    (64, 17, 'Lucas Martins', 'lucas.martins@exemplo.com', 9, '', 'Jodie Foster carrega o filme com uma atuação firme e sensível.', 'aprovada', '2026-09-25 21:40:00'),
    (65, 17, 'Ana Beatriz Costa', 'ana.costa@exemplo.com', 8, 'Suspense de verdade', 'O final no porão é de prender a respiração.', 'aprovada', '2026-10-17 23:20:00'),
    (66, 18, 'Camila Rocha', 'camila.rocha@exemplo.com', 9, 'Infância', 'Revi com meu sobrinho e nós dois adoramos. Woody e Buzz são uma dupla perfeita.', 'aprovada', '2026-07-30 15:00:00'),
    (67, 18, 'Pedro Alves', 'pedro.alves@exemplo.com', 8, '', 'Para o primeiro longa feito todo em computador, continua muito bonito.', 'aprovada', '2026-08-18 16:40:00'),
    (68, 18, 'Sofia Mendes', 'sofia.mendes@exemplo.com', 9, 'Amizade', 'A cena na casa do Sid é engraçada e assustadora ao mesmo tempo.', 'aprovada', '2026-09-09 17:25:00'),
    (69, 18, 'Gustavo Nunes', 'gustavo.nunes@exemplo.com', 8, 'Diversão garantida', 'Piadas para crianças e para adultos. Ótimo para ver em família.', 'aprovada', '2026-10-01 14:10:00'),
    (70, 19, 'Rafael Teixeira', 'rafael.teixeira@exemplo.com', 9, 'Brasileiro e intenso', 'Wagner Moura está assustador como Capitão Nascimento. O ritmo não deixa respirar.', 'aprovada', '2026-08-05 22:00:00'),
    (71, 19, 'Felipe Ramos', 'felipe.ramos@exemplo.com', 8, '', 'As cenas de treinamento do BOPE são as mais marcantes do filme.', 'aprovada', '2026-09-12 21:30:00'),
    (72, 19, 'Beatriz Lima', 'beatriz.lima@exemplo.com', 8, 'Polêmico', 'Gera muita discussão, mas é inegável que é um filme muito bem feito.', 'aprovada', '2026-10-08 20:15:00'),
    (73, 19, 'Júlia Campos', 'julia.campos@exemplo.com', 7, 'Pesado', 'Muito bem feito, mas a violência me incomodou em várias cenas.', 'pendente', '2026-11-14 19:45:00'),
    (74, 20, 'Vinícius Prado', 'vinicius.prado@exemplo.com', 10, 'Obra-prima da animação', 'A abertura com o nascer do sol e a música é arrepiante até hoje.', 'aprovada', '2026-09-04 15:30:00'),
    (75, 20, 'Marina Souza', 'marina.souza@exemplo.com', 9, '', 'Chorei de novo na mesma cena de quando era criança.', 'aprovada', '2026-10-11 16:00:00'),
    (76, 21, 'Gustavo Nunes', 'gustavo.nunes@exemplo.com', 8, 'Épico', 'As batalhas no Coliseu são grandiosas. Russell Crowe convence muito.', 'aprovada', '2026-08-14 21:15:00'),
    (77, 21, 'Lucas Martins', 'lucas.martins@exemplo.com', 8, '', 'Joaquin Phoenix faz um vilão mimado e assustador ao mesmo tempo.', 'aprovada', '2026-09-19 22:05:00'),
    (78, 21, 'Camila Rocha', 'camila.rocha@exemplo.com', 7, 'Bom, mas longo', 'Gostei da história de vingança, mas algumas partes no meio se arrastam.', 'aprovada', '2026-10-23 20:40:00'),
    (79, 22, 'Júlia Campos', 'julia.campos@exemplo.com', 9, 'Diversão pura', 'O DeLorean, o Doc e a cena do relógio da torre são inesquecíveis.', 'aprovada', '2026-07-25 19:00:00'),
    (80, 22, 'Beatriz Lima', 'beatriz.lima@exemplo.com', 9, '', 'Roteiro muito bem amarrado: cada detalhe do começo aparece de novo no final.', 'aprovada', '2026-08-30 20:30:00'),
    (81, 22, 'Ana Beatriz Costa', 'ana.costa@exemplo.com', 8, 'Clássico dos anos 80', 'Michael J. Fox tem um carisma enorme. Ótimo para ver com a família.', 'aprovada', '2026-09-27 18:20:00'),
    (82, 22, 'Rafael Teixeira', 'rafael.teixeira@exemplo.com', 9, 'Viagem no tempo bem feita', 'Ainda é um dos filmes de viagem no tempo mais divertidos que existem.', 'aprovada', '2026-10-19 21:45:00'),
    (83, 23, 'Sofia Mendes', 'sofia.mendes@exemplo.com', 8, 'Nostalgia', 'O clima dos anos 1980 é perfeito. As crianças do elenco são ótimas.', 'aprovada', '2026-07-12 22:00:00'),
    (84, 23, 'Pedro Alves', 'pedro.alves@exemplo.com', 7, '', 'A primeira temporada é excelente, as seguintes oscilam um pouco.', 'aprovada', '2026-08-24 21:30:00'),
    (85, 23, 'Vinícius Prado', 'vinicius.prado@exemplo.com', 9, 'Viciante', 'Maratonei a primeira temporada em um fim de semana. Onze é uma personagem incrível.', 'aprovada', '2026-09-30 23:10:00'),
    (86, 24, 'Felipe Ramos', 'felipe.ramos@exemplo.com', 7, 'Começo excelente', 'As primeiras temporadas são fantásticas, mas o final deixou a desejar.', 'aprovada', '2026-06-20 22:00:00'),
    (87, 24, 'Marina Souza', 'marina.souza@exemplo.com', 5, 'Final apressado', 'Acompanhei por anos e o último ano estragou boa parte da experiência.', 'aprovada', '2026-07-18 23:30:00'),
    (88, 24, 'Gustavo Nunes', 'gustavo.nunes@exemplo.com', 9, 'Produção gigante', 'Os episódios de batalha parecem filmes de cinema. Peter Dinklage é o destaque.', 'pendente', '2026-11-13 21:20:00');

-- Usuário de teste do painel: admin@exemplo.com / admin123
-- (hash gerado com password_hash('admin123', PASSWORD_DEFAULT))
INSERT INTO administradores (nome, email, senha_hash) VALUES
    ('Administrador', 'admin@exemplo.com', '$2y$10$APLjLJqdM8ace8ipsXN2c.kk9/jlxsKNwxo25kE0ToPunvUh/Z/SC');
