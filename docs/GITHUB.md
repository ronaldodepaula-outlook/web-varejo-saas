# Documentacao para GitHub

## Objetivo
Padronizar contribuicoes, releases e automatizacoes deste repositorio.

## Estrutura de Branches
- `main` para codigo pronto para deploy
- `feature/*` para novas features
- `fix/*` para correcoes

## Pipeline
Arquivo `.github/workflows/main.yml`:
- Executa deploy via FTP quando ha push na `main`.
- Segredos exigidos:
  - `ftp_server`
  - `ftp_username`
  - `ftp_password`

## Padrao de Commits
Use mensagens curtas e objetivas.
Exemplos:
- `feat: add clientes menu to varejo`
- `fix: adjust API base url`

## Versionamento
- Atualize `APP_VERSION` no `.env` quando fizer release.
- Atualize `CHANGELOG.md`.

## Arquivos Recomendados
- `README.md`
- `CONTRIBUTING.md`
- `CODE_OF_CONDUCT.md`
- `SECURITY.md`
- `CHANGELOG.md`

## Como Publicar
1. Mescle para `main`.
2. Verifique o deploy do GitHub Actions.
3. Valide em ambiente de producao.

