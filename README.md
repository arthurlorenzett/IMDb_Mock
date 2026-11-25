# IMDb Clone (Trabalho III · Desenvolvimento Web I)

Clone educacional do [IMDb](https://www.imdb.com/) para a disciplina de Desenvolvimento Web I do IFC Campus Concórdia.
**Não é o site oficial do IMDb** e não tem vínculo com o IMDb ou a Amazon. Os dados dos títulos foram consultados no IMDb, as sinopses foram escritas pelo grupo e os pôsteres pertencem aos seus detentores. Avaliações e usuários são ilustrativos.

- **Grupo 9:** Arthur Lorenzett, Lorenzo May e Carlos Conceição
- **Repositório:** https://github.com/arthurlorenzett/IMDb_Mock
- **Versão estática da Parte 2 (sem PHP):** https://arthurlorenzett.github.io/IMDb_Mock/

## Como rodar

Requisitos: PHP 8.1+ (com `pdo_mysql`) e MySQL ou MariaDB.

### XAMPP

1. Ligar **Apache** e **MySQL**.
2. Copiar o projeto para `C:\xampp\htdocs\IMDb_Mock`.
3. Importar `db/seed.sql` no phpMyAdmin.
4. Abrir http://localhost/IMDb_Mock/index.php

Se o MySQL tiver senha: copiar `includes/config.exemplo.php` para `includes/config.local.php` e ajustar.

### Docker

```
docker compose up -d --build
```

Abrir http://localhost:8080/index.php (na primeira vez o banco leva uns 30 segundos para ser importado).

## Usuário de teste

| Página | E-mail | Senha |
|---|---|---|
| `admin/login.php` | `admin@exemplo.com` | `admin123` |

## Roteiro de teste

1. Avaliar um título com um e-mail novo e depois repetir o mesmo e-mail (RN01).
2. Abrir `admin/painel.php` sem login (volta para o login).
3. Entrar e aprovar a avaliação pendente (RN02: a média é recalculada).
4. Cadastrar, editar e excluir um título.
5. Conferir o Top 250: só títulos com 3 ou mais avaliações aprovadas (RN03).

## Tecnologias

HTML5, CSS3 e JavaScript puros · PHP 8 com PDO · MySQL/MariaDB. Sem frameworks.
