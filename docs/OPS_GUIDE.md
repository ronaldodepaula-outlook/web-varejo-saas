# Documentacao Operacional

## Objetivo
Descrever operacao diaria, monitoramento e respostas a incidentes do NexusFlow.

## Componentes em Producao
1. Portal web PHP
2. API Laravel
3. Banco MySQL
4. Servidor web (Apache ou Nginx)

## Rotina Diaria
1. Verificar disponibilidade do portal.
2. Verificar resposta da API em `/api/login`.
3. Conferir logs de erro.
4. Monitorar espaco em disco.

## Health Check Basico
- Portal: abrir pagina de login
- API: `GET /api/nfe-health` ou `POST /api/login` com credencial de teste

## Logs
- Portal: logs do servidor web (Apache/Nginx)
- API: `storage/logs/laravel.log`

## Backup
1. Backup diario do banco da API.
2. Backup semanal dos arquivos do portal.
3. Backup mensal dos arquivos do backend.

## Atualizacoes
1. Atualizar repositorio web.
2. Atualizar repositorio API.
3. Limpar cache da API quando necessario:
   - `php artisan config:clear`

## Falhas Comuns e Solucoes
1. Erro de login generico
   - Verificar `API_BASE_URL`
   - Verificar log do Laravel
2. Erro 401 no login
   - Credenciais invalidas ou usuario inexistente
3. Erro 403 no login
   - Usuario inativo ou email nao verificado
4. Erro 500 na API
   - Falha de conexao ao banco ou exception

## Controle de Acesso
- O portal usa sessao PHP.
- Revogar acesso limpando sessao ou removendo token no backend.

## Segurança
1. Usar HTTPS em todas as URLs.
2. Nao expor `.env` em ambiente publico.
3. Restringir acesso ao banco por IP.
4. Fazer rotacao de senhas periodicamente.

## Plano de Incidente
1. Identificar impacto (portal, API ou banco).
2. Acionar responsavel tecnico.
3. Coletar logs e timestamps.
4. Restaurar servico.
5. Registrar causa raiz.

