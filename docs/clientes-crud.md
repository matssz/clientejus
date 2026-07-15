# CRUD de clientes

CRUD é a sigla para quatro operações básicas de persistência:

| Letra | Operação | Ação no ClienteJus |
| --- | --- | --- |
| C | Create | Cadastrar um cliente |
| R | Read | Listar ou visualizar um cliente |
| U | Update | Atualizar os dados de um cliente |
| D | Delete | Excluir um cliente |

## Caminho do cadastro

```text
Formulário Blade
  -> POST /clientes
  -> middleware auth
  -> ClientRequest
  -> ClientController::store()
  -> relação User::clients()
  -> INSERT na tabela clients
  -> redirecionamento para os detalhes
```

## As sete ações do Resource Controller

O comando conceitual `Route::resource('clientes', ClientController::class)` cria sete rotas:

1. `index()`: busca e lista os clientes.
2. `create()`: mostra o formulário vazio.
3. `store()`: valida e grava um novo cliente.
4. `show()`: mostra um cliente específico.
5. `edit()`: mostra o formulário preenchido.
6. `update()`: valida e atualiza um cliente existente.
7. `destroy()`: exclui um cliente.

`create()` e `edit()` apenas exibem formulários. `store()` e `update()` recebem os dados enviados por esses formulários.

## Validação com FormRequest

O arquivo `ClientRequest.php` separa as regras de validação do Controller. Isso deixa o Controller concentrado no fluxo da operação.

`authorize()` confirma que existe um usuário autenticado.

`rules()` define o tipo, a obrigatoriedade e o tamanho máximo de cada campo.

`attributes()` fornece nomes amigáveis para as mensagens de erro.

`prepareForValidation()` limpa espaços extras, transforma e-mails em minúsculas e converte campos vazios em `null`.

`$request->validated()` devolve somente os campos que passaram pelas regras. Campos extras enviados manualmente pelo navegador não entram no cadastro.

## Isolamento entre usuários

O trecho abaixo cria o cliente por meio do usuário autenticado:

```php
$request->user()
    ->clients()
    ->create($request->validated());
```

A relação `clients()` já conhece o `user_id`. O Laravel preenche essa coluna automaticamente.

Para consultar um cliente, usamos a mesma relação:

```php
$request->user()
    ->clients()
    ->findOrFail($clientId);
```

Se o ID pertencer a outro advogado, a consulta não encontra o registro e responde com HTTP 404. Alterar o número na URL não quebra o isolamento.

## Busca e paginação

O método `index()` lê `?search=termo` da URL. A consulta procura o termo em nome, e-mail, telefone e documento.

`paginate(10)` limita a resposta a dez registros por página. Isso evita carregar todos os clientes de uma conta de uma só vez quando o sistema crescer.

`withQueryString()` preserva o termo pesquisado ao navegar entre as páginas.

## Exclusão protegida

Um cliente sem casos pode ser excluído. Quando existem casos vinculados, o Controller bloqueia a exclusão para evitar perda acidental do histórico jurídico.

## Exercício prático

1. Cadastre dois clientes.
2. Pesquise parte do nome de um deles.
3. Edite o telefone do resultado.
4. Abra os detalhes e exclua um cliente sem casos.
5. Em `ClientController.php`, localize o método executado em cada ação.
6. Explique por que `user()->clients()->findOrFail()` é mais seguro que `Client::findOrFail()` neste SaaS.

O checkpoint é conseguir explicar o caminho completo de um cadastro e por que uma conta não acessa os registros de outra.
