# Contratos - Sprint 1

Esta etapa cria a base do controle contratual. O advogado pode cadastrar um
contrato, vinculá-lo a um caso, controlar assinatura e vencimento e guardar o
documento original de forma privada.

## Por que o contrato pertence a um caso?

O caso já pertence a um cliente e a um usuário do sistema. Portanto, a relação
fica assim:

```text
Usuário -> Caso -> Contrato
              -> Cliente
```

Não repetimos `user_id` e `client_id` na tabela `contracts`. Repetir esses dados
permitiria combinações incorretas, como um contrato ligado ao caso de um cliente
e ao `client_id` de outro. A ligação com `legal_case_id` mantém uma única fonte
de verdade.

## Caminho de um cadastro

1. O navegador envia o formulário para `POST /contratos`.
2. A rota chama `ContractController@store`.
3. `ContractRequest` valida os campos e confirma que o caso pertence ao usuário.
4. O controller cria o registro pelo relacionamento `$case->contracts()`.
5. Se houver arquivo, ele é salvo no disco privado `local`.
6. O usuário é redirecionado para a página do contrato.

Cada parte tem uma responsabilidade. A rota escolhe o destino, o request valida,
o controller coordena o fluxo e o model representa os dados e as relações.

## Arquivos principais

- `database/migrations/2026_07_15_180000_create_contracts_table.php`: estrutura
  da tabela no banco.
- `app/Models/Contract.php`: status, datas, relações e regras de vencimento.
- `app/Http/Requests/ContractRequest.php`: validação e proteção do caso informado.
- `app/Http/Controllers/ContractController.php`: listagem, cadastro, edição,
  exclusão e download.
- `resources/views/contracts`: telas Blade do módulo.
- `tests/Feature/ContractManagementTest.php`: provas automatizadas do fluxo e da
  separação entre contas.

## Segurança do documento

O arquivo original não vai para `public`. Ele fica em
`storage/app/private/contracts/{id}`. Por isso, conhecer o endereço do arquivo
não basta para baixá-lo.

O download passa por uma rota autenticada. Antes de entregar o arquivo, o
controller procura o contrato apenas dentro dos casos do usuário conectado. Um
contrato de outro advogado responde como não encontrado.

## Regras atuais

- O vencimento não pode ser anterior à assinatura.
- Os status aceitos são vigente, expirado e encerrado.
- PDF, DOC e DOCX são aceitos até 10 MB.
- Um contrato vigente exibe alerta a partir de 30 dias antes do vencimento.
- Excluir ou substituir o contrato também remove o arquivo antigo.
- Um caso com contrato não pode ser excluído por acidente.

## Limite desta sprint

Ainda não registramos emendas. A próxima etapa criará alterações sequenciais
vinculadas ao contrato, preservando o documento original e o histórico. Essa
separação impede que uma edição apague o que foi originalmente assinado.

## Exercício prático

1. Crie um caso do tipo `Contratos`.
2. Cadastre um contrato com vencimento dentro dos próximos 30 dias.
3. Confirme o alerta na tela de detalhes.
4. Edite o contrato e substitua o documento.
5. Tente abrir o endereço de download sem estar autenticado.
6. Explique em voz alta por que o arquivo não fica em `public`.

O exercício está concluído quando você consegue descrever o caminho entre rota,
request, controller, model, banco e view sem depender de memorização.
