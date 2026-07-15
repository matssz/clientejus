# Casos, status e WhatsApp

Este módulo conclui o fluxo principal do Core: um advogado cadastra um cliente, cria um caso, acompanha seu andamento e prepara uma atualização pelo WhatsApp.

## Relacionamentos

```text
User
  -> possui muitos Clients
  -> possui muitos LegalCases

Client
  -> pertence a um User
  -> possui muitos LegalCases

LegalCase
  -> pertence a um User
  -> pertence a um Client
  -> pertence a um CaseType
```

O `user_id` permite separar os escritórios. O `client_id` informa para quem é o atendimento. O `case_type_id` classifica o caso.

## Cadastro seguro

O `LegalCaseRequest` valida o cliente com uma condição adicional:

```php
Rule::exists('clients', 'id')->where(
    fn ($query) => $query->where('user_id', $this->user()->id),
)
```

Não basta o cliente existir. Ele precisa pertencer ao usuário autenticado. Isso impede criar um caso usando o cliente de outra conta.

As consultas de casos também começam na relação do usuário:

```php
$request->user()
    ->legalCases()
    ->findOrFail($caseId);
```

Um ID de outro escritório retorna HTTP 404.

## Ciclo de status

Os status disponíveis são:

1. Novo atendimento
2. Documentos pendentes
3. Em análise
4. Pronto para protocolo
5. Protocolado
6. Aguardando retorno
7. Finalizado

Os valores gravados no banco usam letras minúsculas e sublinhado, como `documentos_pendentes`. Os textos apresentados ao usuário ficam no método `LegalCase::statuses()`.

Quando o caso passa para `finalizado`, o sistema preenche `closed_at` se a data estiver vazia. Quando um caso finalizado é reaberto, `closed_at` volta para `null`.

## Busca e filtros

O método `index()` permite buscar pelo título do caso ou nome do cliente. O filtro de status pode ser combinado com a busca.

`with(['client', 'caseType'])` carrega os relacionamentos junto com os casos. Isso evita uma consulta adicional para cada linha da tabela, problema conhecido como N+1.

## Mensagem de WhatsApp

O botão chama uma rota interna. O Controller:

1. Confirma que o caso pertence ao usuário.
2. Remove símbolos do telefone.
3. Acrescenta o código `55` quando o telefone possui DDD e número.
4. Monta uma mensagem com advogado, cliente, caso e status.
5. Codifica o texto para uso em uma URL.
6. Redireciona para `https://wa.me/...`.

Não usamos a API oficial neste MVP. O link não envia nada sozinho: ele abre o WhatsApp com a mensagem preenchida, e o advogado revisa antes de enviar. Isso elimina custo e complexidade prematuros.

## Tipos de caso

O `CaseTypeSeeder` cadastra Previdenciário, Consumidor e Família. O comando é:

```bash
php artisan db:seed
```

`updateOrCreate()` permite executar o seeder mais de uma vez sem duplicar registros.

## Exercício prático

1. Abra um cliente e crie um caso vinculado.
2. Altere o status para `Em análise`.
3. Confira o novo status no painel e na listagem.
4. Abra o WhatsApp e leia a mensagem sem enviá-la.
5. Finalize o caso e verifique a data de encerramento.
6. Reabra o caso e verifique que a data foi removida.
7. Explique por que validar apenas `exists:clients,id` seria insuficiente.

O checkpoint é explicar como o sistema garante que caso e cliente pertencem à mesma conta e demonstrar o fluxo até a mensagem do WhatsApp.
