# Plataforma Prof. Jubilut

Sistema de gerenciamento acadêmico com backend em Laravel 12 e frontend em Vue 3, totalmente conteinerizados via Docker.

---

## Sumário

- [Requisitos](#requisitos)
- [Instalação](#instalação)
- [Usuários padrão](#usuários-padrão)
- [Arquitetura do Backend](#arquitetura-do-backend)
- [Arquitetura do Frontend](#arquitetura-do-frontend)
- [Testes](#testes)

---

## Requisitos

- Docker e Docker Compose instalados
- Portas `8000`, `5173` e `5432` disponíveis na máquina host

---

## Instalação

### 1. Clonar o repositório

```bash
git clone <url-do-repositorio>
cd plataform
```

### 2. Subir os contêineres

```bash
docker-compose up -d --build
```

O entrypoint do contêiner `api` executa automaticamente na inicialização:
- Instalação das dependências PHP via Composer
- Criação e configuração do `.env` com os valores corretos para o Docker
- Geração do `APP_KEY` e do `JWT_SECRET`
- Execução das migrations e seeders

Serviços iniciados:

| Serviço  | Descrição                        | Porta host |
|----------|----------------------------------|------------|
| `db`     | PostgreSQL 13                    | 5432       |
| `api`    | PHP-FPM com Laravel              | -          |
| `nginx`  | Proxy reverso para a API         | 8000       |
| `front`  | Servidor de desenvolvimento Vite | 5173       |

### 3. Acessar a aplicação

- Frontend: [http://localhost:5173](http://localhost:5173)
- API: [http://localhost:8000/api](http://localhost:8000/api)

---

## Usuários padrão

Após rodar os seeders, os seguintes usuários estarão disponíveis:

| E-mail                      | Senha    | Perfil   |
|-----------------------------|----------|----------|
| admin@jubilut.com.br        | password | admin    |
| student@jubilut.com.br      | password | student  |

---

## Arquitetura do Backend

### Tecnologias

- **PHP 7.2** / **Laravel 5.8**
- **PostgreSQL 13**
- **JWT** via `tymon/jwt-auth`
- **CORS** via `fruitcake/laravel-cors`
- **PHPUnit 7.5** para testes

### Diferenças em relação ao Laravel padrão

A instalação padrão do Laravel assume uma aplicação web convencional com renderização server-side. Este projeto diverge em vários pontos deliberados:

**1. API-only — sem Blade, sem rotas web**

O arquivo `routes/web.php` não é utilizado. Toda a comunicação ocorre via `routes/api.php`, e todos os controladores retornam exclusivamente JSON. Não há views, templates ou sessões HTTP.

**2. Autenticação stateless com JWT**

O Laravel 5.8 utiliza autenticação baseada em sessão por padrão. Aqui isso é substituído por `tymon/jwt-auth`: o login retorna um token JWT que o cliente armazena e envia no header `Authorization: Bearer <token>` em cada requisição. Não há cookies de sessão.

**3. Autorização por middleware customizado, sem Gates nem Policies**

Em vez do sistema nativo de Gates e Policies do Laravel, a autorização é feita pelo middleware `CheckRole`, que compara o campo `role` do usuário autenticado com o perfil exigido pela rota (`admin` ou `student`). A escolha mantém a lógica simples dado que há apenas dois perfis fixos.

**4. Form Requests sempre retornam JSON**

Por padrão, quando um Form Request falha, o Laravel redireciona o usuário de volta com os erros na sessão — comportamento adequado para apps web, mas inadequado para APIs. Todos os Form Requests deste projeto sobrescrevem o método `failedValidation` para lançar uma `HttpResponseException` com resposta `422` em JSON, independentemente do header `Accept` da requisição.

**5. Camadas Service e Repository além do MVC padrão**

O Laravel não impõe camadas além de Model-View-Controller. Para desacoplar responsabilidades, este projeto adiciona:

- **Services** (`app/Services/`): contêm a lógica de negócio. Os controladores delegam operações aos services, sem acessar models diretamente.
- **Repositories** (`app/Repositories/`): encapsulam o acesso ao banco de dados. Um `AbstractRepository` implementa as operações comuns (CRUD, paginação), e os repositórios concretos estendem ou especializam esse comportamento.

```
Controller -> Service -> Repository -> Model (Eloquent) -> Banco
```

**6. CORS via pacote**

O suporte nativo a CORS foi adicionado ao Laravel apenas na versão 7. Como este projeto usa Laravel 5.8, o CORS é gerenciado pelo pacote `fruitcake/laravel-cors`.

**7. PostgreSQL em vez de MySQL**

O Laravel assume MySQL como banco padrão. Aqui o driver é `pgsql` com PostgreSQL 13, sem impacto na API do Eloquent, mas relevante para configurações de conexão e tipos de coluna.

### Princípios

O backend é uma **API REST pura**. Não há views Blade ou renderização de HTML. Todas as respostas são em JSON.

### Estrutura de rotas

As rotas estão definidas em `api/routes/api.php` e seguem a seguinte organização:

```
POST   /api/login                              Autenticação (público)

POST   /api/logout                             Encerra sessão (autenticado)
GET    /api/me                                 Dados do usuário logado (autenticado)

/api/admin/*  (requer role: admin)
  GET|POST|PUT|DELETE  /courses                CRUD de cursos
  GET|POST|PUT|DELETE  /professors             CRUD de professores
  GET|POST|PUT|DELETE  /subjects               CRUD de disciplinas
  GET|POST|PUT|DELETE  /students               CRUD de alunos
  GET|POST|DELETE      /students/{id}/courses  Matrículas do aluno
  GET                  /reports/intelligence   Relatório analítico

/api/student/*  (requer role: student)
  GET|PUT              /profile                Perfil do aluno logado
  GET                  /courses                Cursos matriculados
```

### Camadas da aplicação

```
app/
  Http/
    Controllers/Api/    # Controladores da API, um por recurso
    Middleware/         # CheckRole, Authenticate e outros
    Requests/           # Form Requests com validação e resposta JSON (422)
  Models/               # Eloquent: User, Course, Professor, Subject, Student
  Services/             # Lógica de negócio desacoplada dos controladores
  Repositories/         # Acesso a dados desacoplado dos modelos
```

**Convenção de nomenclatura:** todos os campos de modelos, migrations e controladores utilizam nomes em inglês (`name`, `email`, `birth_date`, `title`, `description`, `start_date`, `end_date`).

### Autenticação e autorização

- O login retorna um token JWT que deve ser enviado no header `Authorization: Bearer <token>` em todas as requisições autenticadas.
- O middleware `CheckRole` verifica o campo `role` do usuário (`admin` ou `student`) e restringe o acesso aos grupos de rotas correspondentes.

### Banco de dados

As tabelas são gerenciadas exclusivamente via migrations:

| Tabela           | Descrição                              |
|------------------|----------------------------------------|
| `users`          | Usuários com campo `role`              |
| `courses`        | Cursos                                 |
| `professors`     | Professores                            |
| `subjects`       | Disciplinas (vinculadas a curso e prof)|
| `students`       | Perfis de alunos (FK para `users`)     |
| `enrollments`    | Tabela pivot aluno-curso               |

---

## Arquitetura do Frontend

### Tecnologias

- **Vue 3** com Composition API (`<script setup>`)
- **Vite** como bundler
- **Vue Router 4** para navegação
- **Pinia** para gerenciamento de estado
- **Axios** para comunicação com a API
- **Chart.js** / **vue-chartjs** para visualizações

### Estrutura de diretórios

```
front/src/
  assets/         # Arquivos estáticos
  components/     # Componentes reutilizáveis
  layouts/        # Layouts base (AdminLayout, StudentLayout, AuthLayout)
  router/         # Definição de rotas com guards de autenticação
  services/       # Uma classe de serviço por entidade, usando api.js
  stores/         # Stores Pinia (auth e outros)
  views/          # Páginas organizadas por perfil de acesso
    admin/
      courses/
      professors/
      subjects/
      students/
      enrollments/
      DashboardView.vue
    student/
    auth/
```

### Fluxo de dados

```
View/Component
    |
    v
Service (ex: courseService.js)
    |
    v
api.js  <-- instância Axios configurada com baseURL e interceptors de token
    |
    v
API REST (Laravel)
```

Cada entidade possui seu próprio arquivo de serviço (`courseService.js`, `studentService.js`, etc.) que encapsula as chamadas HTTP e é importado diretamente nas views.

### Gerenciamento de estado

O Pinia é utilizado para estado global. A store de autenticação (`stores/auth.js`) armazena o token JWT e os dados do usuário logado, e é consultada pelo Vue Router para proteger rotas que exigem autenticação ou um perfil específico.

### Proteção de rotas

O Vue Router possui guards de navegação que verificam:

1. Se o usuário está autenticado (token presente na store).
2. Se o perfil do usuário (`admin` ou `student`) corresponde à rota acessada.

Usuários não autenticados são redirecionados para a tela de login. Usuários autenticados com perfil incorreto são redirecionados para sua área correspondente.

---

## Testes

Os testes de backend utilizam PHPUnit com banco de dados SQLite em memória.

Para executar:

```bash
docker-compose exec api php artisan test
```

Os testes cobrem:

- Registro e validação de dados de alunos (`StudentRegistrationTest`)
- Matrículas e prevenção de duplicatas (`EnrollmentTest`)
- Geração do relatório de inteligência (`IntelligenceReportTest`)
