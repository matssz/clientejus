# ClienteJus

SaaS LegalTech simples para advogados autonomos e pequenos escritorios organizarem clientes, casos, documentos, contratos, checklists e status de atendimento.

## Problema

Advogados pequenos atendem muito pelo WhatsApp e acabam perdendo documentos, esquecendo pendencias e repetindo manualmente o status dos casos para clientes.

O ClienteJus nasce para organizar essa rotina antes de tentar competir com ERPs juridicos completos.

## MVP

Escopo fechado da primeira versao:

1. Login e autenticacao.
2. Cadastro de clientes.
3. Cadastro de casos vinculados a clientes.
4. Checklist de documentos por tipo de caso.
5. Upload e organizacao de documentos.
6. Status simples do caso.
7. Botao de WhatsApp com mensagem pronta.
8. Cadastro e controle de vigencia de contratos.

## Stack

- PHP 8.2+
- Laravel 12
- MySQL
- Blade
- Bootstrap 5
- JavaScript simples

## Documentos do Produto

- [Validacao de mercado](docs/validacao-mercado.md)
- [Roteiro de entrevista](docs/roteiro-entrevista.md)
- [Modelagem inicial](docs/modelagem-inicial.md)
- [Backlog MVP](docs/backlog-mvp.md)
- [Autenticação explicada](docs/autenticacao.md)
- [CRUD de clientes explicado](docs/clientes-crud.md)
- [Casos, status e WhatsApp](docs/casos-core.md)
- [Fluxo completo de documentos](docs/fluxo-documentos.md)
- [Contratos - Sprint 1](docs/contratos-sprint-1.md)

## Como Rodar Localmente

Instale dependencias PHP:

```bash
composer install
```

Copie o ambiente:

```bash
copy .env.example .env
```

Gere a chave:

```bash
php artisan key:generate
```

Execute as migrations e cadastre os tipos de caso:

```bash
php artisan migrate --seed
```

Suba o servidor:

```bash
php artisan serve
```

Abra:

```txt
http://127.0.0.1:8000
```

## Regra de Produto

Nenhuma feature fora do MVP entra sem validacao com advogado real.

O objetivo e vender, nao apenas estudar Laravel.
