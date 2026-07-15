# Fluxo completo de documentos

Este módulo transforma o cadastro básico em um fluxo operacional:

```text
Caso criado
  -> checklist gerado pelo tipo do caso
  -> documentos pendentes identificados
  -> mensagem de cobrança preparada no WhatsApp
  -> arquivo recebido e enviado ao ClienteJus
  -> item marcado como concluído
  -> progresso documental atualizado
```

## Checklist padrão

O arquivo `config/case_checklists.php` contém os documentos iniciais de Previdenciário, Consumidor e Família.

`CaseChecklistService::generateDefaults()` lê o tipo do caso e cria os itens. `firstOrCreate()` impede duplicações quando a geração é executada novamente.

O checklist é criado automaticamente junto com casos novos. Casos antigos possuem o comando `Gerar checklist padrão` na página de detalhes.

Os itens iniciais são hipóteses operacionais. Eles devem ser revisados por advogado antes do piloto e podem ser complementados com pendências personalizadas.

## Progresso

Somente itens obrigatórios entram no cálculo:

```text
itens obrigatórios concluídos / total de itens obrigatórios * 100
```

Itens opcionais podem ser controlados sem reduzir o percentual.

O item pode ser marcado manualmente ou concluído automaticamente quando um arquivo é enviado para ele.

## Upload privado

Os formatos permitidos são:

- PDF
- DOC e DOCX
- JPG, JPEG e PNG

O limite por arquivo é 10 MB.

Os arquivos são gravados pelo disco `local` em:

```text
storage/app/private/case-documents/{id-do-caso}
```

Essa pasta não é exposta pelo servidor web. Não existe URL pública direta para o documento.

## Download autorizado

O download passa por `CaseDocumentController::download()`.

Antes de entregar o arquivo, o Controller confirma:

1. O usuário está autenticado.
2. O caso pertence ao usuário.
3. O documento pertence ao caso.
4. O arquivo ainda existe no armazenamento.

Um usuário que altera os IDs na URL recebe HTTP 404.

## Exclusão

Ao excluir um documento:

1. O arquivo físico é removido do disco.
2. O registro é removido de `case_documents`.
3. Se era o último arquivo do item, a pendência volta para aberta.

Isso mantém o checklist coerente com os arquivos realmente disponíveis.

## Cobrança pelo WhatsApp

A mensagem inclui somente itens que são:

- obrigatórios;
- ainda não concluídos.

Itens opcionais e documentos já recebidos não aparecem. O link `wa.me` abre a conversa com a mensagem preenchida, mas o envio continua sob controle do advogado.

## Responsabilidades do código

| Arquivo | Responsabilidade |
| --- | --- |
| `CaseChecklistService.php` | Gera os itens padrão |
| `CaseChecklistController.php` | Gerencia pendências e cobrança |
| `CaseDocumentRequest.php` | Valida arquivo, formato, tamanho e vínculo |
| `CaseDocumentController.php` | Armazena, baixa e exclui arquivos |
| `WhatsAppLink.php` | Normaliza telefone e monta a URL |
| `cases/show.blade.php` | Exibe o fluxo documental |

## Exercício prático

1. Crie um caso do tipo Consumidor.
2. Confira o checklist automático.
3. Use `Cobrar pendências` e leia a mensagem sem enviar.
4. Anexe um PDF a um item.
5. Verifique o progresso e faça o download.
6. Exclua o arquivo e confirme que o item voltou a ficar pendente.
7. Adicione um documento opcional personalizado.
8. Explique por que o arquivo não fica dentro de `public/`.

O checkpoint é demonstrar todo o fluxo sem editar o banco manualmente e explicar como a autorização protege cada arquivo.
