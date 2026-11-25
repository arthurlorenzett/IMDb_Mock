<?php
/* ==========================================================================
   dados.php — todas as consultas ao banco, sempre com PDO e prepared
   statements. Os endpoints e as páginas não escrevem SQL: chamam estas
   funções. Média e votos são calculados a partir das aprovadas (RN02).
   ========================================================================== */

/* ---------------------------------------------------------------- títulos */

// SELECT base: o título com os gêneros juntados por "|||"
const SQL_TITULO = "
    SELECT
        t.id, t.slug, t.titulo, t.tipo, t.ano, t.lancamento, t.duracao_min,
        t.episodios, t.classificacao, t.sinopse, t.direcao, t.elenco,
        t.poster, t.banner, t.popularidade, t.destaque, t.criado_em,
        GROUP_CONCAT(g.nome ORDER BY g.nome SEPARATOR '|||') AS generos
    FROM titulos t
    LEFT JOIN titulo_genero tg ON tg.titulo_id = t.id
    LEFT JOIN generos g ON g.id = tg.genero_id
";

// Converte uma linha do banco para o formato de data/titulos.json (lido pelo JS)
function titulo_para_json(array $t): array
{
    $t['id'] = (int) $t['id'];
    $t['ano'] = (int) $t['ano'];
    $t['duracao_min'] = $t['duracao_min'] !== null ? (int) $t['duracao_min'] : null;
    $t['episodios'] = $t['episodios'] !== null ? (int) $t['episodios'] : null;
    $t['popularidade'] = (float) $t['popularidade'];
    $t['destaque'] = (bool) $t['destaque'];
    $t['generos'] = $t['generos'] ? explode('|||', $t['generos']) : [];
    $t['elenco'] = $t['elenco'] ? array_map('trim', explode(',', $t['elenco'])) : [];
    $t['criado_em'] = str_replace(' ', 'T', $t['criado_em']);
    return $t;
}

function listar_titulos(): array
{
    $linhas = conexao()->query(SQL_TITULO . ' GROUP BY t.id ORDER BY t.id')->fetchAll();
    return array_map('titulo_para_json', $linhas);
}

function titulo_existe(int $id): bool
{
    $stmt = conexao()->prepare('SELECT COUNT(*) FROM titulos WHERE id = :id');
    $stmt->execute(['id' => $id]);
    return (int) $stmt->fetchColumn() > 0;
}

/* RN03: só títulos com no mínimo N avaliações aprovadas, ordem por média e,
   no empate, por votos. Listagem pública gerada pelo PHP (top250.php). */
function ranking_top250(int $minimo, int $limite): array
{
    $sql = "
        SELECT t.id, t.titulo, t.ano, t.tipo, t.duracao_min, t.episodios, t.poster,
               ROUND(AVG(a.nota), 1) AS media,
               COUNT(*) AS votos
        FROM titulos t
        INNER JOIN avaliacoes a ON a.titulo_id = t.id AND a.status = 'aprovada'
        GROUP BY t.id
        HAVING COUNT(*) >= :minimo
        ORDER BY media DESC, votos DESC
        LIMIT :limite
    ";
    $stmt = conexao()->prepare($sql);
    $stmt->bindValue('minimo', $minimo, PDO::PARAM_INT);
    $stmt->bindValue('limite', $limite, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

// Gera um slug que ainda não existe (acrescenta -2, -3… se precisar)
function slug_livre(string $titulo, ?int $ignorar_id = null): string
{
    $base = gerar_slug($titulo) ?: 'titulo';
    $stmt = conexao()->prepare('SELECT COUNT(*) FROM titulos WHERE slug = :slug AND id <> :id');
    $slug = $base;
    for ($n = 2; ; $n++) {
        $stmt->execute(['slug' => $slug, 'id' => $ignorar_id ?? 0]);
        if ((int) $stmt->fetchColumn() === 0) {
            return $slug;
        }
        $slug = "{$base}-{$n}";
    }
}

// Cria (sem $id) ou atualiza (com $id) um título e os gêneros dele; devolve o id
function salvar_titulo(array $d, ?int $id = null): int
{
    $pdo = conexao();

    $campos = [
        'slug'          => slug_livre($d['titulo'], $id),
        'titulo'        => $d['titulo'],
        'tipo'          => $d['tipo'],
        'ano'           => $d['ano'],
        'lancamento'    => $d['lancamento'] ?: null,
        'duracao_min'   => $d['duracao_min'],
        'episodios'     => $d['episodios'],
        'classificacao' => $d['classificacao'],
        'sinopse'       => $d['sinopse'],
        'direcao'       => $d['direcao'],
        // Mesmo formato do seed.sql e da API: nomes separados por ", "
        'elenco'        => implode(', ', $d['elenco']),
        'poster'        => $d['poster'] ?: null,
        'banner'        => $d['banner'] ?: null,
        'popularidade'  => $d['popularidade'],
        'destaque'      => $d['destaque'] ? 1 : 0
    ];

    $pdo->beginTransaction();
    try {
        if ($id === null) {
            $campos['criado_em'] = date('Y-m-d H:i:s');
            $sql = "
                INSERT INTO titulos (
                    slug, titulo, tipo, ano, lancamento, duracao_min,
                    episodios, classificacao, sinopse, direcao, elenco,
                    poster, banner, popularidade, destaque, criado_em
                ) VALUES (
                    :slug, :titulo, :tipo, :ano, :lancamento, :duracao_min,
                    :episodios, :classificacao, :sinopse, :direcao, :elenco,
                    :poster, :banner, :popularidade, :destaque, :criado_em
                )
            ";
            $pdo->prepare($sql)->execute($campos);
            $id = (int) $pdo->lastInsertId();
        } else {
            $sql = "
                UPDATE titulos SET
                    slug = :slug, titulo = :titulo, tipo = :tipo, ano = :ano,
                    lancamento = :lancamento, duracao_min = :duracao_min,
                    episodios = :episodios, classificacao = :classificacao,
                    sinopse = :sinopse, direcao = :direcao, elenco = :elenco,
                    poster = :poster, banner = :banner,
                    popularidade = :popularidade, destaque = :destaque
                WHERE id = :id
            ";
            $pdo->prepare($sql)->execute($campos + ['id' => $id]);
        }

        // Gêneros: apaga os antigos e grava os marcados
        $pdo->prepare('DELETE FROM titulo_genero WHERE titulo_id = ?')->execute([$id]);
        $inserir = $pdo->prepare('INSERT INTO titulo_genero (titulo_id, genero_id) SELECT ?, id FROM generos WHERE nome = ?');
        foreach ($d['generos'] as $genero) {
            $inserir->execute([$id, $genero]);
        }

        $pdo->commit();
    } catch (Throwable $erro) {
        $pdo->rollBack();
        throw $erro;
    }
    return $id;
}

// Exclui o título; avaliações e gêneros dele saem junto (ON DELETE CASCADE)
function excluir_titulo(int $id): bool
{
    $stmt = conexao()->prepare('DELETE FROM titulos WHERE id = :id');
    $stmt->execute(['id' => $id]);
    return $stmt->rowCount() > 0;
}

/* ------------------------------------------------------------- avaliações */

// API pública: só as aprovadas e sem o e-mail de quem avaliou (RN02)
function listar_avaliacoes_aprovadas(): array
{
    $sql = "
        SELECT id, titulo_id, nome, nota, titulo, comentario, status, criada_em
        FROM avaliacoes
        WHERE status = 'aprovada'
        ORDER BY id
    ";
    return array_map(function (array $a) {
        $a['id'] = (int) $a['id'];
        $a['titulo_id'] = (int) $a['titulo_id'];
        $a['nota'] = (int) $a['nota'];
        $a['criada_em'] = str_replace(' ', 'T', $a['criada_em']);
        return $a;
    }, conexao()->query($sql)->fetchAll());
}

// Painel: avaliações pendentes (registros do formulário público), as mais antigas primeiro
function listar_pendentes(): array
{
    $sql = "
        SELECT a.id, a.titulo_id, t.titulo AS nome_titulo, a.email, a.nota, a.comentario, a.criada_em
        FROM avaliacoes a
        INNER JOIN titulos t ON t.id = a.titulo_id
        WHERE a.status = 'pendente'
        ORDER BY a.criada_em ASC, a.id ASC
    ";
    return array_map(function (array $a) {
        $a['id'] = (int) $a['id'];
        $a['titulo_id'] = (int) $a['titulo_id'];
        $a['nota'] = (int) $a['nota'];
        return $a;
    }, conexao()->query($sql)->fetchAll());
}

// RN01: o e-mail já avaliou este título? (sem diferenciar maiúsculas)
function email_ja_avaliou(int $titulo_id, string $email): bool
{
    $stmt = conexao()->prepare('SELECT COUNT(*) FROM avaliacoes WHERE titulo_id = :titulo AND LOWER(email) = LOWER(:email)');
    $stmt->execute(['titulo' => $titulo_id, 'email' => $email]);
    return (int) $stmt->fetchColumn() > 0;
}

// Grava a avaliação como pendente (RN02). Devolve false se o UNIQUE do banco barrar (RN01).
function inserir_avaliacao(array $d): bool
{
    $sql = "
        INSERT INTO avaliacoes (titulo_id, nome, email, nota, titulo, comentario, status, criada_em)
        VALUES (:titulo_id, :nome, :email, :nota, :titulo, :comentario, :status, NOW())
    ";
    try {
        conexao()->prepare($sql)->execute([
            'titulo_id'  => $d['titulo_id'],
            'nome'       => $d['nome'],
            'email'      => $d['email'],
            'nota'       => $d['nota'],
            'titulo'     => $d['titulo'],
            'comentario' => $d['comentario'],
            'status'     => rn02_status_inicial()
        ]);
        return true;
    } catch (PDOException $erro) {
        if ($erro->getCode() === '23000') {
            return false;
        }
        throw $erro;
    }
}

// Aprova ou rejeita uma avaliação que ainda está pendente
function moderar_avaliacao(int $id, string $status): bool
{
    $stmt = conexao()->prepare("UPDATE avaliacoes SET status = :status WHERE id = :id AND status = 'pendente'");
    $stmt->execute(['status' => $status, 'id' => $id]);
    return $stmt->rowCount() > 0;
}

/* -------------------------------------------------------- administradores */

function buscar_admin_por_email(string $email): ?array
{
    $stmt = conexao()->prepare('SELECT id, nome, email, senha_hash FROM administradores WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    return $stmt->fetch() ?: null;
}
