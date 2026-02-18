# Documentacao do Fluxo do Sistema

## Fluxo de Login
1. Usuario acessa `login.php`.
2. Portal envia JSON para `API_BASE_URL + /api/login`.
3. API valida credenciais e retorna token e dados.
4. Portal grava sessao e redireciona para `index.php?view=...`.

## Fluxo de Navegacao
1. `index.php` carrega o shell e a sidebar.
2. `classe/verURL.php` inclui a view solicitada.
3. View carrega dados via JS usando `window.NEXUSFLOW_API_BASE_URL`.

## Fluxo de Segmento
1. API retorna o segmento da empresa.
2. Portal guarda `$_SESSION['segmento']`.
3. Sidebar monta o menu conforme o segmento.

## Fluxo do Modulo de Clientes
1. Usuario acessa `index.php?view=adm-clientes`.
2. JS chama `GET /api/vendasAssistidas/clientes/empresa/{id_empresa}`.
3. Usuario cria ou edita cliente via `POST/PUT`.
4. Detalhes e debitos usam endpoints dedicados.

## Fluxo de Logout
1. Usuario clica em sair.
2. Sessao eh limpa.
3. Usuario retorna para login.

## Fluxo de Recuperacao de Senha
1. Usuario solicita reset na API.
2. API envia email com token.
3. Usuario redefine senha via endpoint.

