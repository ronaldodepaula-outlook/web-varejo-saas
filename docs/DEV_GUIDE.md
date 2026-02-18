# Documentacao Tecnica para Desenvolvedores

**Repositorio:** saas-multiempresas-web
**Papel:** Frontend/portal PHP que consome a API Laravel (saas-multiempresas-new)

## Visao Geral
Este projeto entrega a camada web (PHP + HTML + JS) e depende de uma API REST externa para autenticar, buscar dados e executar operacoes. O front usa sessoes PHP para guardar token e dados do usuario/empresa.

## Arquitetura
1. Browser acessa `login.php`.
2. `login.php` envia JSON para `API_BASE_URL + /api/login`.
3. API retorna `token`, `usuario`, `empresa` e `licenca`.
4. `login.php` grava em `$_SESSION` e redireciona para `index.php?view=...`.
5. `index.php` inclui a view solicitada via `classe/verURL.php`.
6. As views usam JS para consumir a API com o token.

## Estrutura de Pastas
- `assets/` CSS e JS globais
- `components/` cabecalho e sidebar
- `view/` telas do sistema (PHP com HTML + JS)
- `classe/` roteamento de views
- `funcoes/` configuracao e utilitarios
- `login.php` fluxo de autenticacao
- `index.php` shell principal

## Configuracao (.env)
Arquivo `\.env` na raiz do projeto:
- `APP_URL_BASE` base do app web
- `API_BASE_URL` base da API Laravel
- `APP_NAME`, `APP_VERSION`, `APP_LOCALE`, `APP_TIMEZONE`
- `DB_*` usado em integracoes locais se houver

Carregamento: `funcoes/config.php` le o `.env` sem dependencias externas.

## Configuracao da API no Front
- `components/app-head.php` exporta `window.NEXUSFLOW_API_BASE_URL`.
- `view/config.js` monta URLs com base nessa variavel.

## Roteamento de Views
`index.php` chama `classe/verURL.php`.
- Views validas sao geradas a partir dos arquivos em `view/*.php`.
- O parametro `view` aceita nomes com `-` ou `_` e eh normalizado.

Exemplo:
- `index.php?view=adm-clientes` -> `view/adm-clientes.php`

## Autenticacao e Sessao
`login.php` define:
- `$_SESSION['authToken']`
- `$_SESSION['usuario']`
- `$_SESSION['empresa']`
- `$_SESSION['licenca']`
- `$_SESSION['segmento']`
- `$_SESSION['user_id']` e outros dados

Views criticas conferem a sessao no topo do arquivo.

## Menus por Segmento
`components/sidebar.php` monta o menu conforme `$_SESSION['segmento']`.
- O segmento `varejo` inclui `adm-clientes`.
- Ajuste o menu alterando o array `$menus`.

## Modulo de Clientes
Arquivo: `view/adm-clientes.php`
- Endpoints consumidos:
  - `GET /api/vendasAssistidas/clientes/empresa/{id_empresa}`
  - `POST /api/vendasAssistidas/clientes`
  - `GET /api/vendasAssistidas/clientes/{id}`
  - `PUT /api/vendasAssistidas/clientes/{id}`
  - `DELETE /api/vendasAssistidas/clientes/{id}`
  - `GET /api/debitos-clientes/cliente/{id}`

## Padroes de Resposta Esperados
Login espera JSON com:
- `token`
- `usuario`
- `empresa`
- `licenca`

Caso a API retorne HTML ou erro 500, o login cai em erro generico.

## Desenvolvimento Local
1. Suba o Apache no XAMPP.
2. Configure `API_BASE_URL` apontando para a pasta `public` do Laravel.
3. Garanta que a API responde em `/api/login`.
4. Verifique logs em `saas-multiempresas-new/storage/logs/laravel.log`.

## Debug Rapido
- Teste o login com curl/postman.
- Verifique `$_SESSION` no PHP.
- Cheque erros JS no console do navegador.

## Deploy
- Web: enviar este repositorio para o servidor web.
- API: manter o backend Laravel em servidor separado.
- GitHub Actions em `.github/workflows/main.yml` faz deploy via FTP.

## Boas Praticas
- Nunca versionar credenciais reais.
- Usar variaveis de ambiente por ambiente.
- Manter consistencia entre endpoints do front e routes da API.

