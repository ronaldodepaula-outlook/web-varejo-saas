<?php
$config = require __DIR__ . '/../funcoes/config.php';

session_start();
if (!isset($_SESSION['authToken'], $_SESSION['usuario'], $_SESSION['empresa'], $_SESSION['licenca'])) {
    header('Location: login.php');
    exit;
}

// Verificar e converter o tipo de dados das variáveis de sessão
$usuario = is_array($_SESSION['usuario']) ? $_SESSION['usuario'] : ['nome' => $_SESSION['usuario']];
$empresa = $_SESSION['empresa'];
$id_empresa = $_SESSION['empresa_id'];
$licenca = $_SESSION['licenca'];
$token = $_SESSION['authToken'];
$segmento = $_SESSION['segmento'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orçamentos Marcenaria - SAS Multi</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Configuração da API -->
    <script>
        const API_CONFIG = {
            BASE_URL: '<?= addslashes($config['api_base']) ?>/api/v1',
            TOKEN: '<?php echo $_SESSION["authToken"]; ?>',
            getHeaders: function() {
                return {
                    'Authorization': 'Bearer ' + this.TOKEN,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                };
            },
            ENDPOINTS: {
                ORCAMENTOS: '/marcenaria/orcamentos'
            },
            ORCAMENTOS_BASE: function() {
                return this.BASE_URL + this.ENDPOINTS.ORCAMENTOS;
            }
        };
    </script>
</head>
<body>
    <!-- Seu conteúdo HTML existente aqui -->
    
    <!-- Incluir os modais -->
    <?php require_once 'modals/orcamento-modals.php'; ?>
    
    <!-- Script com as funções do orçamento -->
    <script src="/saas-multiempresas/public/appV1/js/orcamentos.js"></script>
</body>
</html>




