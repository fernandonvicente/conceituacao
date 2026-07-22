# Gestão de Usuários e Perfis

Aplicação para gerenciamento de usuários e perfis de acesso. O projeto possui uma API em Laravel 12, uma interface em Vue 3 e desassociação assíncrona de perfis utilizando RabbitMQ.

## Tecnologias

- PHP 8.2 e Laravel 12
- Laravel Sanctum
- MySQL 8
- RabbitMQ
- Vue 3
- Pinia
- Vue Router
- Axios
- Docker Compose

## Funcionalidades

- Cadastro, login e logout de usuários
- CRUD de usuários
- CRUD de perfis
- Relacionamento muitos-para-muitos entre usuários e perfis
- Associação de múltiplos perfis a um usuário
- Listagem dos perfis de um usuário
- Controle de acesso para administradores
- Criação automática do perfil e usuário Administrador
- Desassociação assíncrona de perfis com RabbitMQ

## Estrutura do projeto

```text
conceituacao/
├── api/                  # API Laravel
├── front/                # Frontend Vue 3
├── docker-compose.yml
└── README-novo.md
```

O backend foi organizado em camadas:

```text
Controller → Request → Service → Repository → Model
                         ↓
                        DTO
```

As respostas da API utilizam Resources.

## Como executar com Docker

### Requisito

- Docker Desktop instalado
- Docker Desktop aberto e com o Docker Engine em execução

### Subir o projeto

1. Abra o Docker Desktop e aguarde até o Docker Engine iniciar.
2. Abra um terminal na pasta do projeto:

```powershell
cd C:\teste-claro\conceituacao
```

3. Na primeira execução, construa as imagens:

```bash
docker compose build
```

4. Suba os containers:

```bash
docker compose up -d
```

Também é possível construir e subir com um único comando:

```bash
docker compose up --build -d
```

Nas próximas execuções, se não houve alteração nos Dockerfiles ou nas dependências, basta abrir o Docker Desktop e executar:

```bash
docker compose up -d
```

Na primeira inicialização, o backend executa automaticamente:

```bash
php artisan migrate --force
php artisan db:seed --force
```

Depois que os containers estiverem prontos, acesse:

- Frontend: http://localhost:5173
- API: http://localhost:8000/api
- RabbitMQ Management: http://localhost:15672

Credenciais do painel RabbitMQ:

```text
Usuário: guest
Senha: guest
```

### Acesso ao MySQL

Para acessar pelo MySQL-Front, DBeaver, MySQL Workbench ou outro cliente:

```text
Host: 127.0.0.1
Porta: 3306
Banco: teste_claro
Usuário: root
Senha: root
```

O container utiliza `mysql_native_password` para manter compatibilidade com clientes MySQL mais antigos.

Também é possível acessar pelo terminal:

```bash
docker compose exec mysql mysql -uroot -proot teste_claro
```

Exemplos de consultas:

```sql
SHOW TABLES;
SELECT * FROM users;
SELECT * FROM profiles;
SELECT * FROM profile_user;
```

### Verificar os containers

```bash
docker compose ps
```

### Acompanhar os logs

```bash
docker compose logs -f
```

Para visualizar somente o consumer do RabbitMQ:

```bash
docker compose logs -f worker
```

### Parar o projeto

```bash
docker compose down
```

Para remover também o volume e todos os dados do MySQL:

```bash
docker compose down -v
```

## Migrations e seeders

As migrations e seeders são executadas automaticamente ao subir o backend.

Para executar manualmente:

```bash
docker compose exec backend php artisan migrate
docker compose exec backend php artisan db:seed
```

Para recriar o banco:

```bash
docker compose exec backend php artisan migrate:fresh --seed
```

## Usuário de teste

O seeder cria automaticamente um usuário administrador:

```text
E-mail: admin@gmail.com
Senha: 123456
```

O usuário recebe o perfil `Administrador`, que permite gerenciar perfis e associações.

Usuários sem esse perfil não visualizam o menu de perfis e não podem acessar os endpoints administrativos.

Usuário não pode se excluir.

## Mensageria com RabbitMQ

A associação de perfis é realizada de forma síncrona. A desassociação é realizada de forma assíncrona:

1. O administrador desmarca um perfil e salva.
2. A API identifica os perfis removidos.
3. O producer publica uma mensagem persistente na fila `profile.detach`.
4. A API responde com status `202 Accepted`.
5. O serviço `worker` consome a mensagem.
6. O consumer remove o vínculo na tabela `profile_user`.
7. O frontend atualiza a listagem após o processamento.

O comando executado pelo container worker é:

```bash
php artisan rabbitmq:consume-profile-detaches
```

## Principais endpoints

### Autenticação

```text
POST /api/register
POST /api/login
POST /api/logout
GET  /api/me
```

### Usuários

```text
GET    /api/users
POST   /api/users
GET    /api/users/{id}
PUT    /api/users/{id}
DELETE /api/users/{id}
```

### Perfis

```text
GET    /api/profiles
POST   /api/profiles
GET    /api/profiles/{id}
PUT    /api/profiles/{id}
DELETE /api/profiles/{id}
```

### Perfis do usuário

```text
GET /api/users/{id}/profiles
PUT /api/users/{id}/profiles
```

O `PUT` recebe:

```json
{
  "profile_ids": [1, 2, 3]
}
```

## Testes

Os testes automatizados foram aplicados ao módulo de usuários.

Para executar:

```bash
docker compose exec backend php artisan test
```

Os testes validam:

- CRUD de usuários autenticado
- Bloqueio da listagem para usuários não autenticados

## Serviços do Docker Compose

```text
mysql      MySQL 8
rabbitmq   Broker e painel de gerenciamento
backend    API Laravel
worker     Consumer das desassociações
frontend   Aplicação Vue 3
```
