# IMDb Clone (Trabalho III · Desenvolvimento Web I)

Clone educacional do [IMDb](https://www.imdb.com/), feito para a disciplina de Desenvolvimento Web I do IFC Campus Concórdia (Prof. Alisson Borges Zanetti).
**Este não é o site oficial do IMDb** e não tem vínculo com o IMDb ou a Amazon. As informações dos filmes e séries (título, ano, duração, gêneros, classificação, direção, elenco e link do trailer) foram consultadas no IMDb; as sinopses foram escritas pelo grupo. Pôsteres e imagens de cena são carregados direto do IMDb e pertencem aos seus detentores. **Avaliações e usuários são ilustrativos.**

- **Site reproduzido:** IMDb
- **Grupo 9:** Arthur Lorenzett, Lorenzo May e Carlos Conceição
- **Repositório:** https://github.com/arthurlorenzett/IMDb_Mock
- **Versão estática da Parte 2 (GitHub Pages, sem PHP):** https://arthurlorenzett.github.io/IMDb_Mock/

---

## Como testar (passo a passo para o professor)

O site precisa de **PHP 8.1+** (com `pdo_mysql`) e **MySQL ou MariaDB**. Há três formas; a primeira é a mais simples.

### Opção 1 · XAMPP (recomendada)

1. Instale o XAMPP (https://www.apachefriends.org) e, no painel dele, clique em **Start** em **Apache** e em **MySQL**.
2. Copie a pasta do projeto para `C:\xampp\htdocs\IMDb_Mock` (ou clone o repositório lá dentro).
3. Crie o banco: abra http://localhost/phpmyadmin → aba **Importar** → escolha o arquivo `db/seed.sql` → **Importar**.
   O script cria o banco `imdb_clone` com as 5 tabelas, os 24 títulos, as 88 avaliações e o usuário de teste.
4. Abra http://localhost/IMDb_Mock/index.php.

O projeto já usa o padrão do XAMPP (usuário `root`, sem senha). Se o seu MySQL tiver senha, copie `includes/config.exemplo.php` para `includes/config.local.php` e ajuste os dados.

### Opção 2 · Docker

Na pasta do projeto:

```
docker compose up -d --build
```

Espere uns 30 segundos na primeira vez (o MySQL importa o `db/seed.sql` sozinho) e abra http://localhost:8080/index.php.
Para desligar: `docker compose down`. Para recriar o banco do zero: `docker compose down -v` e subir de novo.

### Opção 3 · Servidor embutido do PHP

Com um MySQL/MariaDB ligado e o `db/seed.sql` importado (`mysql -u root < db/seed.sql`), rode na pasta do projeto:

```
php -S localhost:8000
```

e abra http://localhost:8000/index.php.

### Usuário de teste do painel

| Página | E-mail | Senha |
|---|---|---|
| `admin/login.php` | `admin@exemplo.com` | `admin123` |

A senha fica no banco só como hash (`password_hash`); o login confere com `password_verify`.

### Roteiro sugerido de teste

1. **Início, Busca e Top 250:** navegar pelo catálogo, buscar "batman", filtrar por gênero, ordenar o Top 250 pelas colunas.
2. **Avaliação (formulário público, RN01 e RN02):** abrir um título, clicar em **Avaliar**, enviar uma avaliação com um e-mail novo → aparece "Avaliação enviada! Ela aparecerá depois de aprovada.". Enviar de novo com o mesmo e-mail → "Este e-mail já avaliou este título." e os dados continuam no formulário.
3. **Proteção do painel:** abrir `admin/painel.php` sem login → volta para a tela de login.
4. **Login:** entrar com o usuário de teste. Com a senha errada, a mensagem aparece e o e-mail digitado continua no campo.
5. **Moderação (RN02):** no painel, a avaliação enviada no passo 2 está em "Avaliações pendentes". Aprovar → a nota média do título muda.
6. **CRUD de títulos:** **+ Novo título** (cadastrar), **Editar** e **Excluir** na tabela de títulos.
7. **Top 250 (RN03):** só aparecem títulos com pelo menos 3 avaliações aprovadas, ordenados pela média e, no empate, pelos votos.
8. **Sair:** botão **Sair** no topo do painel.

Para voltar aos dados originais, basta importar o `db/seed.sql` de novo.

---

## Descrição

O site permite navegar por um catálogo de 24 filmes e séries, buscar com filtros, ver a ficha de cada título, avaliar, montar uma Watchlist e consultar o ranking Top 250. A área administrativa, protegida por login, cadastra, edita e exclui títulos e modera as avaliações enviadas pelo público.

| Página | Arquivo | O que faz |
|---|---|---|
| P1 · Início | `index.php` | Carrossel de destaques, Top da semana e "Em alta" |
| P2 · Busca | `busca.php` | Busca por termo e filtros (GET), ordenação, grade/lista e paginação |
| P3 · Título | `titulo.php?id=` | Ficha do título, avaliações aprovadas, trailer e formulário de avaliação |
| P4 · Top 250 | `top250.php` | Ranking gerado pelo PHP a partir do banco (RN03) |
| P5 · Watchlist | `watchlist.php` | Lista salva no navegador (`localStorage`) |
| P6 · Login | `admin/login.php` | Login do administrador (POST, `password_verify`, `$_SESSION`) |
| P7 · Painel | `admin/painel.php` | Títulos (editar/excluir) e moderação das avaliações pendentes |
| P8 · Cadastro | `admin/titulo-form.php` | Cadastro e edição de título |

### Regras de negócio (definidas na Parte 1, validadas no PHP)

- **RN01:** cada e-mail avalia um mesmo título uma única vez (`rn01_pode_avaliar()` em `includes/funcoes.php` e `UNIQUE (titulo_id, email)` no banco).
- **RN02:** toda avaliação entra como pendente; só as aprovadas aparecem e entram na nota média (1 casa decimal). A média é calculada, nunca gravada.
- **RN03:** só entra no Top 250 o título com pelo menos 3 avaliações aprovadas, ordenado pela média e, no empate, pelos votos (`HAVING` + `ORDER BY` em `ranking_top250()`).

## Tecnologias

- **Front-end:** HTML5, CSS3 e JavaScript puros (módulos ES). Sem frameworks, sem Bootstrap e sem bibliotecas de JS. Fonte Roboto (Google Fonts) e ícones Bootstrap Icons.
- **Back-end:** PHP 8 puro e MySQL/MariaDB via PDO com prepared statements.

## Estrutura

```
index.php  busca.php  titulo.php  top250.php  watchlist.php
admin/      login.php  painel.php  titulo-form.php  sair.php                 (páginas do painel)
            avaliacoes-pendentes.php  moderar-avaliacao.php
            salvar-titulo.php  excluir-titulo.php                           (endpoints JSON, só com login)
api/        titulos.php  avaliacoes.php  enviar-avaliacao.php              (endpoints JSON públicos)
includes/   config.php (sessão + PDO) · funcoes.php (validação, RN01–RN03) · dados.php (consultas)
            cabecalho.php · menu.php · rodape.php (incluídos em todas as páginas)
            verificar-sessao.php (bloqueia os endpoints do painel sem login)
db/         seed.sql (estrutura + dados de exemplo + usuário de teste)
css/        variaveis.css (tokens) · base.css · componentes.css · paginas.css
js/         app.js · nucleo/ · componentes/ · paginas/
data/       site.json (configuração mestre) · titulos.json · avaliacoes.json
Dockerfile, docker-compose.yml   (opção de execução com Docker)
```

### Como o front e o back conversam

- **Leitura:** com `data/site.json → dados.fonte = "api"`, o `js/nucleo/dados.js` busca `api/titulos.php` e `api/avaliacoes.php`, que devolvem do MySQL o mesmo formato dos arquivos JSON da Parte 2. A API pública só devolve avaliações aprovadas e nunca o e-mail de quem avaliou.
- **Escrita:** o login é um formulário POST tratado pelo `login.php`. Avaliação, cadastro, edição, exclusão e moderação são enviados pelo JS por POST (JSON) para os endpoints. O JS valida antes de enviar e o PHP valida de novo (`filter_var`, tamanhos, listas fechadas); os erros voltam por campo e aparecem na página sem apagar o que foi digitado.
- **Sem banco:** Watchlist e tema continuam no `localStorage` do navegador.

O GitHub Pages não roda PHP; o link do Pages mostra a versão estática da Parte 2.
