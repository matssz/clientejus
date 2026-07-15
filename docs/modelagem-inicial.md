# Modelagem Inicial - ClienteJus

Esta modelagem guia o MVP. Ela ainda pode mudar depois das entrevistas.

## Entidades do MVP

### users

Representa o advogado ou usuario do sistema.

Campos principais:

- id
- name
- email
- password
- created_at
- updated_at

### clients

Representa o cliente do advogado.

Campos principais:

- id
- user_id
- name
- email
- phone
- document
- notes
- created_at
- updated_at

Relacionamento:

- um usuario tem muitos clientes
- um cliente pertence a um usuario

### case_types

Representa tipos de caso com checklist padrao.

Exemplos:

- Previdenciario
- Consumidor
- Familia

Campos principais:

- id
- name
- description
- created_at
- updated_at

### legal_cases

Representa o caso ou atendimento juridico.

Campos principais:

- id
- user_id
- client_id
- case_type_id
- title
- description
- status
- opened_at
- closed_at
- created_at
- updated_at

Status iniciais:

- novo_atendimento
- documentos_pendentes
- em_analise
- pronto_para_protocolo
- protocolado
- aguardando_retorno
- finalizado

### checklist_items

Representa os itens de checklist de cada caso.

Campos principais:

- id
- legal_case_id
- name
- is_required
- is_completed
- completed_at
- created_at
- updated_at

### case_documents

Representa arquivos enviados ou cadastrados no caso.

Campos principais:

- id
- legal_case_id
- checklist_item_id
- original_name
- file_path
- mime_type
- file_size
- uploaded_at
- created_at
- updated_at

## Decisoes de Arquitetura

- Monolito Laravel.
- Blade + Bootstrap no frontend.
- MySQL.
- Storage local no MVP.
- Sem API oficial do WhatsApp no MVP.
- Sem multiempresa avancado no MVP.

## Primeiro Fluxo a Construir

1. Usuario faz login.
2. Usuario cadastra cliente.
3. Usuario cria caso para esse cliente.
4. Usuario seleciona tipo de caso.
5. Sistema cria checklist inicial.
6. Usuario marca documentos como pendentes ou recebidos.
7. Usuario gera mensagem para WhatsApp cobrando pendencias.
