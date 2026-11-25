# IMDb Clone (Trabalho III · Desenvolvimento Web I)

Clone educacional do [IMDb](https://www.imdb.com/), feito para a disciplina de Desenvolvimento Web I do IFC Campus Concórdia (Prof. Alisson Borges Zanetti).
**Este não é o site oficial do IMDb** e não tem vínculo com o IMDb ou a Amazon. As informações dos filmes e séries (título, ano, duração, gêneros, classificação, direção e elenco) foram consultadas no IMDb; as sinopses foram escritas pelo grupo. Pôsteres e imagens de cena são carregados direto do IMDb e pertencem aos seus detentores. **Avaliações e usuários são ilustrativos.**

- **Site reproduzido:** IMDb
- **Grupo 9:** Arthur Lorenzett, Lorenzo May e Carlos Conceição
- **Site publicado (GitHub Pages):** https://arthurlorenzett.github.io/IMDb_Mock/
- **Repositório:** https://github.com/arthurlorenzett/IMDb_Mock

## Descrição

O site permite navegar por um catálogo de 24 filmes e séries reais, buscar com filtros, ver a ficha de cada título, avaliar, montar uma Watchlist e consultar o ranking Top 250. Uma área administrativa (protótipo) cadastra títulos e modera as avaliações.

| Página | Arquivo | O que faz |
|---|---|---|
| P1 · Início | `index.html` | Carrossel de destaques, Top da semana e "Em alta" |
| P2 · Busca | `busca.html` | Busca por termo e filtros (gênero, ano, nota, tipo), ordenação, grade/lista e paginação |
| P3 · Título | `titulo.html?id=` | Ficha do título, avaliações aprovadas, modal de avaliação |
| P4 · Top 250 | `top250.html` | Tabela do ranking com ordenação por coluna e filtro de gênero |
| P5 · Watchlist | `watchlist.html` | Lista salva no navegador, com ordenação, filtro e limpeza |
| P6 · Login | `admin/login.html` | Entrada da área administrativa |
| P7 · Painel | `admin/painel.html` | Lista de títulos (editar/excluir) e moderação de avaliações |
| P8 · Cadastro | `admin/titulo-form.html` | Cadastro e edição de título |

### Regras de negócio

- **RN01:** cada e-mail avalia um mesmo título uma única vez.
- **RN02:** toda avaliação entra como pendente; só as aprovadas aparecem e entram na nota média (1 casa decimal).
- **RN03:** só entra no Top 250 o título com pelo menos 3 avaliações aprovadas, ordenado pela média e, no empate, pelo número de votos.

## Tecnologias

HTML5, CSS3 e JavaScript puros (módulos ES). Sem frameworks, sem Bootstrap e sem bibliotecas de JS.
Fonte Roboto (Google Fonts), ícones Bootstrap Icons, dados, pôsteres e imagens dos títulos vindos do IMDb.

## Como usar

O JavaScript carrega os dados com `fetch`, então o site **não funciona abrindo o HTML direto** (`file://`). Use um servidor local na pasta do projeto:

- VS Code: extensão **Live Server** → "Open with Live Server"; ou
- Terminal: `python -m http.server 8000` e abra `http://localhost:8000`.

**Área administrativa (demonstração):** em `admin/login.html`, entre com `admin@exemplo.com` e a senha `admin123`.
Na Parte 2 não há servidor: avaliações enviadas, aprovações, cadastros e exclusões ficam salvos só no `localStorage` do navegador (chaves com o prefixo `imdbclone:`). Para voltar aos dados originais, limpe os dados do site no navegador.

## Estrutura

```
index.html  busca.html  titulo.html  top250.html  watchlist.html
admin/      login.html  painel.html  titulo-form.html
css/        variaveis.css (tokens) · base.css · componentes.css · paginas.css
js/app.js   ponto de entrada: carrega o site.json e o script da página
js/nucleo/       config, dados, regras (RN01–RN03), armazenamento, sessao, util
js/componentes/  layout, tema, modal, carrossel, card, validacao
js/paginas/      um arquivo por página
data/       site.json (configuração mestre) · titulos.json · avaliacoes.json
img/        logo e imagem padrão de pôster
```

O `data/site.json` concentra a configuração (regras, mensagens de validação, gêneros, textos). `js/nucleo/dados.js` é a única porta de acesso aos dados: na Parte 3 basta mudar `dados.fonte` de `"json"` para `"api"` para ler os mesmos formatos do PHP + MySQL.

