# Autenticação do ClienteJus

Este módulo permite criar uma conta, entrar, acessar um painel protegido e sair.

## Caminho de uma requisição

Quando alguém envia o formulário de cadastro, o fluxo é:

```text
Navegador
  -> POST /cadastro
  -> routes/web.php
  -> RegisteredUserController::store()
  -> validação dos dados
  -> criação do User no banco
  -> criação da sessão
  -> redirecionamento para /painel
```

Cada parte possui uma responsabilidade. A rota decide qual Controller será executado. O Controller coordena a operação. O Model representa os dados. A view produz o HTML.

## Arquivos principais

| Arquivo | Responsabilidade |
| --- | --- |
| `routes/web.php` | Declara os endereços e os middlewares |
| `RegisteredUserController.php` | Exibe e processa o cadastro |
| `AuthenticatedSessionController.php` | Processa login e logout |
| `DashboardController.php` | Busca os números apresentados no painel |
| `User.php` | Representa um usuário salvo no banco |
| `auth/login.blade.php` | Formulário de login |
| `auth/register.blade.php` | Formulário de cadastro |
| `layouts/app.blade.php` | Estrutura visual compartilhada pelas páginas |

## Rotas e métodos HTTP

- `GET /cadastro`: pede ao servidor a página de cadastro.
- `POST /cadastro`: envia os dados preenchidos para o servidor.
- `GET /login`: pede a página de login.
- `POST /login`: envia e-mail e senha para autenticação.
- `GET /painel`: pede o painel do usuário autenticado.
- `POST /logout`: encerra a sessão atual.

Usamos `POST` para operações que alteram estado. Login cria uma sessão, cadastro cria um usuário e logout destrói uma sessão.

## Funções do cadastro

`create()` apenas devolve a view `auth.register`. Ela responde ao `GET /cadastro`.

`store(Request $request)` recebe os dados enviados pelo formulário. O objeto `$request` contém campos, cabeçalhos, cookies e informações da requisição HTTP.

`$request->validate(...)` verifica as regras. Se alguma falhar, o Laravel volta ao formulário e coloca os erros na sessão. Se todas passarem, devolve somente os dados validados.

`Hash::make(...)` transforma a senha em um hash. O banco nunca deve guardar a senha original.

`User::create(...)` executa a inserção na tabela `users` por meio do Eloquent, o ORM do Laravel.

`Auth::login($user)` informa ao Laravel qual usuário pertence à sessão atual.

`session()->regenerate()` troca o identificador da sessão. Isso reduz o risco de um ataque chamado fixação de sessão.

## Funções do login

`Auth::attempt(...)` procura o e-mail e compara a senha informada com o hash armazenado. A função devolve `true` quando os dados são válidos e `false` quando não são.

`ValidationException::withMessages(...)` devolve uma mensagem segura. Não informamos se foi o e-mail ou a senha que estava errado, evitando revelar quais contas existem.

`redirect()->intended(...)` envia o usuário para a página que ele tentou acessar antes do login. Quando não existe uma página anterior, o destino é o painel.

## Funções do logout

`Auth::logout()` remove o usuário autenticado da sessão.

`session()->invalidate()` invalida todos os dados da sessão anterior.

`session()->regenerateToken()` cria um novo token CSRF após o logout.

## Proteções aplicadas

- `@csrf` adiciona um token secreto a cada formulário `POST`.
- O middleware `guest` impede usuários autenticados de voltarem às telas de login e cadastro.
- O middleware `auth` impede visitantes de acessarem o painel.
- A senha exige pelo menos oito caracteres, uma letra e um número.
- O e-mail é único no banco de dados.
- A senha é armazenada como hash.

## Exercício prático

1. Abra `routes/web.php` e identifique as rotas `GET` e `POST`.
2. Abra `RegisteredUserController.php` e acompanhe o método `store()` de cima para baixo.
3. Crie uma conta pelo navegador.
4. Saia e tente acessar `/painel` diretamente.
5. Explique por que o middleware `auth` redirecionou você para `/login`.

O checkpoint desta etapa é conseguir explicar o caminho completo entre o envio do formulário e a criação do usuário no banco.
