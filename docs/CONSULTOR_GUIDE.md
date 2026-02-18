# Documentacao Tecnica para Consultores

## Objetivo
Orientar consultores na implantacao do NexusFlow em clientes, cobrindo configuracao, requisitos e validacao.

## Escopo do Produto
- Portal web em PHP
- API externa em Laravel
- Multi-empresa e multi-segmento
- Licencas por empresa

## Requisitos Minimos
1. Servidor web com PHP 8.x e Apache ou Nginx.
2. Banco MySQL para a API.
3. Certificado SSL no dominio final.
4. Acesso ao DNS e ao painel do servidor.

## Checklist de Implantacao
1. Validar dominio e SSL.
2. Definir URLs base do portal e da API.
3. Configurar `.env` do portal.
4. Configurar `.env` da API Laravel.
5. Validar acesso ao banco da API.
6. Criar usuario administrador inicial.
7. Testar login e navegacao.
8. Liberar acesso para segmentos e modulos acordados.

## Parametros do Portal
Arquivo `.env` do portal:
- `APP_URL_BASE` URL base do portal
- `API_BASE_URL` URL base da API Laravel
- `APP_NAME` nome exibido

## Segmentos e Menus
O menu eh baseado no segmento da empresa e fica em `components/sidebar.php`.
- Segmento `varejo` inclui o modulo de clientes.
- Ajustes de menu devem ser validados com o cliente.

## Fluxo de Autenticacao
1. Usuario faz login no portal.
2. Portal chama `API_BASE_URL + /api/login`.
3. API retorna token e dados do usuario.
4. Portal abre o dashboard do segmento.

## Integracoes Relevantes
- API de clientes e vendas assistidas
- Modulos financeiros e estoque
- Notas fiscais

## Validacao com o Cliente
1. Testar login com usuario real.
2. Conferir menu por segmento.
3. Validar acesso ao modulo de clientes no varejo.
4. Checar permissao de usuarios e filiais.
5. Revisar relatorios criticos.

## Treinamento Recomendado
1. Administrador: configuracoes e cadastros basicos.
2. Operacao: vendas assistidas, clientes, estoque.
3. Financeiro: contas a pagar/receber.

## Riscos Comuns
- API fora do ar ou sem acesso ao banco.
- URL base incorreta no portal.
- Falta de usuario ativo na base.

## Entregaveis do Consultor
- Ambiente configurado e validado
- Credenciais iniciais
- Relatorio de testes
- Plano de capacitacao

