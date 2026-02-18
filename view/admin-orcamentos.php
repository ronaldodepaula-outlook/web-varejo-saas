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

// Extrair nome do usuário de forma segura
$nomeUsuario = '';
if (is_array($usuario)) {
    $nomeUsuario = $usuario['nome'] ?? $usuario['name'] ?? 'Usuário';
} else {
    $nomeUsuario = (string)$usuario;
}

// Extrair ID do usuário da sessão
$id_usuario = $_SESSION['user_id'] ?? ($usuario['id'] ?? 1);

// Primeira letra para o avatar
$inicialUsuario = strtoupper(substr($nomeUsuario, 0, 1));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?php if (!defined('APP_SHELL')) { $pageTitle = 'Orçamentos Marcenaria - SAS Multi'; include __DIR__ . '/../components/app-head.php'; } ?>

    <style>
        :root {
            --primary-color: #3498DB;
            --secondary-color: #2C3E50;
            --success-color: #27AE60;
            --warning-color: #F39C12;
            --danger-color: #E74C3C;
            --info-color: #17A2B8;
            --light-color: #ECF0F1;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: all 0.3s;
        }
        
        .main-header {
            background: white;
            padding: 15px 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .breadcrumb-custom {
            background: transparent;
            margin-bottom: 0;
        }
        
        .header-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            cursor: pointer;
        }
        
        .card-custom {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            overflow: hidden;
            border: none;
        }
        
        .card-header-custom {
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            background: rgba(248, 249, 250, 0.8);
        }
        
        .card-body {
            padding: 20px;
        }
        
        .page-title {
            font-weight: 700;
            color: var(--secondary-color);
            margin-bottom: 5px;
        }
        
        .page-subtitle {
            color: #6c757d;
            margin-bottom: 0;
        }
        
        .search-box {
            position: relative;
        }
        
        .search-box .form-control {
            padding-left: 40px;
        }
        
        .search-box .bi-search {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }
        
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .table-actions {
            white-space: nowrap;
        }
        
        .modal-xl-custom {
            max-width: 1200px;
        }
        
        .item-material {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            border-left: 4px solid var(--primary-color);
        }
        
        .medidas-badge {
            background: #e9ecef;
            color: #495057;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            margin-right: 5px;
        }
        
        .form-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .form-section-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--secondary-color);
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid var(--primary-color);
        }
        
        .dimensoes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }
        
        .dimensao-input {
            display: flex;
            flex-direction: column;
        }
        
        .dimensao-input label {
            font-size: 0.8rem;
            font-weight: 500;
            margin-bottom: 4px;
            color: #495057;
        }
        
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
            }
            
            .table-actions .btn-group {
                display: flex;
                flex-direction: column;
                gap: 2px;
            }
            
            .table-actions .btn {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }
            
            .dimensoes-grid {
                grid-template-columns: 1fr 1fr;
            }
        }
    </style>
</head>
<body>
    <main class="main-content">
        <!-- Header -->
        <header class="main-header">
            <div class="header-left">
                <button class="sidebar-toggle" type="button">
                    <i class="bi bi-list"></i>
                </button>
                
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-custom">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="?view=admin-dashboard_marcenaria">Marcenaria</a></li>
                        <li class="breadcrumb-item active">Orçamentos</li>
                    </ol>
                </nav>
            </div>
            
            <div class="header-right">
                <!-- Dropdown do Usuário -->
                <div class="dropdown user-dropdown">
                    <div class="user-avatar" data-bs-toggle="dropdown" aria-expanded="false">
                        <?php echo $inicialUsuario; ?>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><h6 class="dropdown-header user-name"><?php echo htmlspecialchars($nomeUsuario); ?></h6></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="#" id="logoutBtn"><i class="bi bi-box-arrow-right me-2"></i>Sair</a></li>
                    </ul>
                </div>
            </div>
        </header>
        
        <!-- Área de Conteúdo -->
        <div class="content-area">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="page-title">Gestão de Orçamentos</h1>
                    <p class="page-subtitle">Crie, gerencie e acompanhe orçamentos de marcenaria</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary" onclick="exportarRelatorioOrcamentos()">
                        <i class="bi bi-file-earmark-text me-2"></i>Relatório
                    </button>
                    <button class="btn btn-primary" onclick="abrirModalNovoOrcamento()">
                        <i class="bi bi-plus-circle me-2"></i>Novo Orçamento
                    </button>
                </div>
            </div>
            
            <!-- Estatísticas Rápidas -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card-custom">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="text-primary mb-0" id="totalOrcamentos">0</h5>
                                    <p class="text-muted mb-0">Total</p>
                                </div>
                                <div class="bg-primary text-white rounded p-3">
                                    <i class="bi bi-clipboard-check"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card-custom">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="text-success mb-0" id="orcamentosAprovados">0</h5>
                                    <p class="text-muted mb-0">Aprovados</p>
                                </div>
                                <div class="bg-success text-white rounded p-3">
                                    <i class="bi bi-check-circle"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card-custom">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="text-warning mb-0" id="orcamentosPendentes">0</h5>
                                    <p class="text-muted mb-0">Pendentes</p>
                                </div>
                                <div class="bg-warning text-white rounded p-3">
                                    <i class="bi bi-hourglass-split"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card-custom">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="text-info mb-0" id="valorTotalAprovado">R$ 0,00</h5>
                                    <p class="text-muted mb-0">Valor Aprovado</p>
                                </div>
                                <div class="bg-info text-white rounded p-3">
                                    <i class="bi bi-currency-dollar"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Filtros e Busca -->
            <div class="card-custom mb-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="search-box">
                                <i class="bi bi-search"></i>
                                <input type="text" class="form-control" id="searchInputOrcamentos" placeholder="Buscar por cliente, código, tipo móvel...">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" id="filterStatusOrcamentos">
                                <option value="">Todos os status</option>
                                <option value="rascunho">Rascunho</option>
                                <option value="pendente">Pendente</option>
                                <option value="aprovado">Aprovado</option>
                                <option value="rejeitado">Rejeitado</option>
                                <option value="em_producao">Em Produção</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Período</label>
                            <div class="input-group">
                                <input type="date" class="form-control" id="filterDataInicioOrcamentos">
                                <span class="input-group-text">até</span>
                                <input type="date" class="form-control" id="filterDataFimOrcamentos">
                            </div>
                        </div>
                        <div class="col-md-3 d-flex align-items-end gap-2">
                            <button class="btn btn-outline-secondary w-50" onclick="limparFiltrosOrcamentos()">
                                <i class="bi bi-arrow-clockwise"></i> Limpar
                            </button>
                            <button class="btn btn-primary w-50" onclick="carregarEstatisticasOrcamentos()">
                                <i class="bi bi-graph-up"></i> Estatísticas
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Lista de Orçamentos -->
            <div class="card-custom">
                <div class="card-header-custom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Orçamentos</h5>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary btn-sm" onclick="carregarOrcamentos()">
                            <i class="bi bi-arrow-clockwise me-1"></i>Atualizar
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Código</th>
                                    <th>Cliente</th>
                                    <th>Tipo Móvel</th>
                                    <th>Valor</th>
                                    <th>Status</th>
                                    <th>Data</th>
                                    <th width="150">Ações</th>
                                </tr>
                            </thead>
                            <tbody id="tabelaOrcamentos">
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Carregando...</span>
                                        </div>
                                        <p class="mt-2">Carregando orçamentos...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal Detalhes Orçamento -->
    <div class="modal fade" id="modalDetalhesOrcamento" tabindex="-1" aria-labelledby="modalDetalhesOrcamentoLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl-custom">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDetalhesOrcamentoLabel">Detalhes do Orçamento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modalDetalhesOrcamentoBody">
                    <!-- Conteúdo será carregado via JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="button" class="btn btn-primary" onclick="imprimirOrcamento()">
                        <i class="bi bi-printer me-1"></i>Imprimir
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Novo/Editar Orçamento -->
    <div class="modal fade" id="modalOrcamentoForm" tabindex="-1" aria-labelledby="modalOrcamentoFormLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl-custom">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalOrcamentoFormLabel">Novo Orçamento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formOrcamento">
                        <input type="hidden" id="id_orcamento" name="id_orcamento">
                        
                        <!-- Seção: Informações Básicas -->
                        <div class="form-section">
                            <div class="form-section-title">Informações Básicas</div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="id_cliente" class="form-label">Cliente *</label>
                                        <select class="form-select" id="id_cliente" name="id_cliente" required>
                                            <option value="">Selecione um cliente</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="id_filial" class="form-label">Filial *</label>
                                        <select class="form-select" id="id_filial" name="id_filial" required>
                                            <option value="">Selecione uma filial</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="tipo_movel" class="form-label">Tipo de Móvel *</label>
                                        <select class="form-select" id="tipo_movel" name="tipo_movel" required>
                                            <option value="">Selecione o tipo de móvel</option>
                                            <option value="armario">Armário</option>
                                            <option value="estante">Estante</option>
                                            <option value="mesa">Mesa</option>
                                            <option value="cadeira">Cadeira</option>
                                            <option value="sofa">Sofá</option>
                                            <option value="cama">Cama</option>
                                            <option value="guarda_roupa">Guarda-Roupa</option>
                                            <option value="painel">Painel</option>
                                            <option value="balcao">Balcão</option>
                                            <option value="prateleira">Prateleira</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="categoria_movel" class="form-label">Categoria</label>
                                        <select class="form-select" id="categoria_movel" name="categoria_movel">
                                            <option value="">Selecione uma categoria</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Seção: Medidas e Descrição -->
                        <div class="form-section">
                            <div class="form-section-title">Medidas e Descrição</div>
                            <div class="mb-3">
                                <label class="form-label">Medidas Gerais</label>
                                <div class="row g-2">
                                    <div class="col-md-3">
                                        <input type="number" class="form-control" placeholder="Largura" 
                                               id="medida_largura" name="medidas_gerais[largura]" step="0.1" min="0">
                                    </div>
                                    <div class="col-md-3">
                                        <input type="number" class="form-control" placeholder="Altura" 
                                               id="medida_altura" name="medidas_gerais[altura]" step="0.1" min="0">
                                    </div>
                                    <div class="col-md-3">
                                        <input type="number" class="form-control" placeholder="Profundidade" 
                                               id="medida_profundidade" name="medidas_gerais[profundidade]" step="0.1" min="0">
                                    </div>
                                    <div class="col-md-3">
                                        <select class="form-select" id="medida_unidades" name="medidas_gerais[unidades]">
                                            <option value="cm">cm</option>
                                            <option value="m">m</option>
                                            <option value="mm">mm</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="descricao_detalhada" class="form-label">Descrição Detalhada *</label>
                                <textarea class="form-control" id="descricao_detalhada" name="descricao_detalhada" 
                                          rows="3" placeholder="Descreva detalhadamente o móvel, características especiais, acabamentos, etc..." required></textarea>
                            </div>
                        </div>

                        <!-- Seção: Valores e Prazos -->
                        <div class="form-section">
                            <div class="form-section-title">Valores e Prazos</div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="margem_lucro" class="form-label">Margem de Lucro (%) *</label>
                                        <input type="number" class="form-control" id="margem_lucro" name="margem_lucro" 
                                               step="0.01" min="0" max="100" value="35" required>
                                        <div class="form-text">Percentual de lucro sobre o custo</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="prazo_entrega" class="form-label">Prazo de Entrega (dias) *</label>
                                        <input type="number" class="form-control" id="prazo_entrega" name="prazo_entrega" 
                                               min="1" value="15" required>
                                        <div class="form-text">Dias úteis para entrega</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="valor_orcado" class="form-label">Valor Orçado (R$) *</label>
                                        <input type="number" class="form-control" id="valor_orcado" name="valor_orcado" 
                                               step="0.01" min="0" required>
                                        <div class="form-text">Valor final para o cliente</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Seção: Observações -->
                        <div class="form-section">
                            <div class="form-section-title">Observações</div>
                            <div class="mb-3">
                                <label for="observacoes_cliente" class="form-label">Observações do Cliente</label>
                                <textarea class="form-control" id="observacoes_cliente" name="observacoes_cliente" 
                                          rows="2" placeholder="Observações, preferências ou requisitos especiais do cliente..."></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="observacoes_internas" class="form-label">Observações Internas</label>
                                <textarea class="form-control" id="observacoes_internas" name="observacoes_internas" 
                                          rows="2" placeholder="Observações para a equipe interna, considerações técnicas..."></textarea>
                            </div>
                        </div>
                        
                        <!-- Seção: Itens do Orçamento -->
                        <div class="form-section">
                            <div class="form-section-title">Itens do Orçamento</div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="mb-0">Materiais e Componentes</h6>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="adicionarItem()">
                                        <i class="bi bi-plus-circle me-1"></i>Adicionar Item
                                    </button>
                                </div>
                                <div id="itensOrcamentoContainer">
                                    <!-- Itens serão adicionados dinamicamente -->
                                </div>
                                <div class="mt-2">
                                    <small class="text-muted">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Adicione todos os materiais e componentes necessários para a produção do móvel
                                    </small>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="salvarOrcamento()">
                        <i class="bi bi-check-circle me-1"></i>Salvar Orçamento
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay d-none" id="loadingOverlay">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Carregando...</span>
        </div>
    </div>

    <script>
        // Variáveis globais
        const idEmpresa = <?php echo $id_empresa; ?>;
        const token = '<?php echo $token; ?>';
        const idUsuario = <?php echo $id_usuario; ?>;
        const BASE_URL = '<?= addslashes($config['api_base']) ?>';
        
        let orcamentos = [];
        let orcamentosFiltrados = [];
        let clientes = [];
        let filiais = [];
        let categorias = [];
        let tiposMoveis = [];
        let estatisticas = {};
        let itemCounter = 0;

        // Tipos de componentes válidos para a API
        const TIPOS_COMPONENTES = [
            'estrutura',
            'porta',
            'gaveta',
            'prateleira',
            'fundo',
            'tampo',
            'puxador',
            'dobradiça',
            'ferragem',
            'acabamento',
            'outro'
        ];

        const ENDPOINTS_TIPOS_MOVEIS = [
            `${BASE_URL}/api/v1/marcenaria/tipos-moveis`,
            `${BASE_URL}/api/v1/marcenaria/tipos_moveis`,
            `${BASE_URL}/api/v1/marcenaria/orcamentos/tipos-moveis`,
            `${BASE_URL}/api/v1/marcenaria/orcamentos/tipos_moveis`,
            `${BASE_URL}/api/v1/tipos-moveis`,
            `${BASE_URL}/api/v1/tipos_moveis`
        ];

        // Configuração da API
        const API_CONFIG = {
            ORCAMENTOS_LISTA: () => `${BASE_URL}/api/v1/marcenaria/orcamentos`,
            ORCAMENTO_CREATE: () => `${BASE_URL}/api/v1/marcenaria/orcamentos`,
            ORCAMENTO_DETAIL: (id) => `${BASE_URL}/api/v1/marcenaria/orcamentos/${id}`,
            ORCAMENTO_UPDATE: (id) => `${BASE_URL}/api/v1/marcenaria/orcamentos/${id}`,
            ORCAMENTO_APROVAR: (id) => `${BASE_URL}/api/v1/marcenaria/orcamentos/${id}/aprovar`,
            ORCAMENTO_CRIAR_ORDEM: (id) => `${BASE_URL}/api/v1/marcenaria/orcamentos/${id}/criar-ordem-producao`,
            ORCAMENTOS_ESTATISTICAS: () => `${BASE_URL}/api/v1/marcenaria/orcamentos/estatisticas`,
            CLIENTES_LISTA: () => `${BASE_URL}/api/vendasAssistidas/clientes/empresa/${idEmpresa}`,
            FILIAIS_LISTA: () => `${BASE_URL}/api/filiais/empresa/${idEmpresa}`,
            CATEGORIAS_LISTA: () => `${BASE_URL}/api/v1/categorias/empresa/${idEmpresa}`,
            
            getHeaders: function() {
                return {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-ID-EMPRESA': idEmpresa.toString()
                };
            }
        };

        // Inicialização
        document.addEventListener('DOMContentLoaded', function() {
            carregarDadosIniciais();
            
            // Configurar eventos
            document.getElementById('searchInputOrcamentos').addEventListener('input', filtrarOrcamentos);
            document.getElementById('filterStatusOrcamentos').addEventListener('change', filtrarOrcamentos);
            document.getElementById('filterDataInicioOrcamentos').addEventListener('change', filtrarOrcamentos);
            document.getElementById('filterDataFimOrcamentos').addEventListener('change', filtrarOrcamentos);
            
            // Logoff
            document.getElementById('logoutBtn').addEventListener('click', function(e) {
                e.preventDefault();
                fazerLogoff();
            });
        });

        // Carregar dados iniciais
        async function carregarDadosIniciais() {
            mostrarLoading(true);
            
            try {
                await Promise.all([
                    carregarOrcamentos(),
                    carregarClientes(),
                    carregarFiliais(),
                    carregarCategorias(),
                    carregarTiposMoveis(),
                    carregarEstatisticasOrcamentos()
                ]);
            } catch (error) {
                console.error('Erro ao carregar dados iniciais:', error);
                mostrarNotificacao('Erro ao carregar dados iniciais', 'error');
            } finally {
                mostrarLoading(false);
            }
        }

        // Função para fazer logout
        async function fazerLogoff() {
            try {
                const response = await fetch(BASE_URL + '/api/v1/logout', {
                    method: 'POST',
                    headers: API_CONFIG.getHeaders()
                });
                
                window.location.href = 'login.php';
                
            } catch (error) {
                console.error('Erro no logout:', error);
                window.location.href = 'login.php';
            }
        }

        // Função auxiliar para fazer requisições
        async function fazerRequisicaoAPI(url, options = {}) {
            try {
                console.log(`Fazendo requisição para: ${url}`);
                
                const config = {
                    ...options,
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-ID-EMPRESA': idEmpresa.toString()
                    },
                    mode: 'cors'
                };

                // Adicionar Authorization apenas se não for uma rota pública
                if (!url.includes('/api/vendasAssistidas/')) {
                    config.headers['Authorization'] = `Bearer ${token}`;
                }
                
                const response = await fetch(url, config);
                
                console.log(`Status da resposta: ${response.status}`);
                
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('Resposta de erro:', errorText);
                    
                    // Tentar parsear como JSON para obter erros detalhados
                    try {
                        const errorData = JSON.parse(errorText);
                        if (errorData.errors) {
                            throw new Error(`HTTP ${response.status}: ${JSON.stringify(errorData.errors)}`);
                        }
                    } catch (e) {
                        // Se não for JSON, usar o texto original
                    }
                    
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const data = await response.json();
                console.log('Dados recebidos:', data);
                return data;
                
            } catch (error) {
                console.error('Erro na requisição API:', error);
                throw error;
            }
        }

        // Carregar orçamentos
        async function carregarOrcamentos() {
            try {
                const data = await fazerRequisicaoAPI(API_CONFIG.ORCAMENTOS_LISTA());
                
                if (data.success && data.data && data.data.data) {
                    orcamentos = data.data.data;
                    orcamentosFiltrados = [...orcamentos];
                    exibirOrcamentos(orcamentosFiltrados);
                    if (tiposMoveis.length === 0) {
                        const unicos = [...new Set(orcamentos.map(o => o.tipo_movel).filter(Boolean))];
                        if (unicos.length > 0) {
                            tiposMoveis = unicos.map((tipo) => ({ value: tipo, label: String(tipo) }));
                            preencherSelectTiposMoveis();
                        }
                    }
                } else {
                    throw new Error('Estrutura de dados inválida');
                }
                
            } catch (error) {
                console.error('Erro ao carregar orçamentos:', error);
                mostrarNotificacao('Erro ao carregar orçamentos: ' + error.message, 'error');
                exibirOrcamentos([]);
            }
        }

        // Carregar clientes
        async function carregarClientes() {
            try {
                const data = await fazerRequisicaoAPI(API_CONFIG.CLIENTES_LISTA());
                clientes = Array.isArray(data) ? data : (data.data || []);
                preencherSelectClientes();
            } catch (error) {
                console.error('Erro ao carregar clientes:', error);
                clientes = [];
                mostrarNotificacao('Erro ao carregar lista de clientes', 'warning');
            }
        }

        // Carregar filiais
        async function carregarFiliais() {
            try {
                const data = await fazerRequisicaoAPI(API_CONFIG.FILIAIS_LISTA());
                filiais = Array.isArray(data) ? data : (data.data || []);
                preencherSelectFiliais();
            } catch (error) {
                console.error('Erro ao carregar filiais:', error);
                filiais = [];
                mostrarNotificacao('Erro ao carregar lista de filiais', 'warning');
            }
        }

        // Carregar categorias
        async function carregarCategorias() {
            try {
                const data = await fazerRequisicaoAPI(API_CONFIG.CATEGORIAS_LISTA());
                categorias = Array.isArray(data) ? data : (data.data || []);
                preencherSelectCategorias();
            } catch (error) {
                console.error('Erro ao carregar categorias:', error);
                categorias = [];
                mostrarNotificacao('Erro ao carregar lista de categorias', 'warning');
            }
        }

        function normalizarListaTipos(data) {
            const raw = Array.isArray(data)
                ? data
                : (Array.isArray(data?.data) ? data.data : (Array.isArray(data?.data?.data) ? data.data.data : []));

            return raw.map((item) => {
                if (typeof item === 'string' || typeof item === 'number') {
                    return { value: item, label: String(item) };
                }
                if (item && typeof item === 'object') {
                    const value = item.slug ?? item.codigo ?? item.valor ?? item.id ?? item.id_tipo_movel ?? item.nome ?? item.descricao ?? item.tipo;
                    const label = item.nome ?? item.descricao ?? item.tipo ?? String(value ?? '');
                    if (value !== undefined && value !== null && value !== '') {
                        return { value, label: String(label) };
                    }
                }
                return null;
            }).filter(Boolean);
        }

        function obterTiposMoveisFallback() {
            const select = document.getElementById('tipo_movel');
            if (!select) return [];
            return Array.from(select.querySelectorAll('option'))
                .map(option => ({
                    value: option.value,
                    label: option.textContent.trim()
                }))
                .filter(item => item.value);
        }

        function preencherSelectTiposMoveis() {
            const select = document.getElementById('tipo_movel');
            if (!select) return;
            select.innerHTML = '<option value="">Selecione o tipo de móvel</option>';
            tiposMoveis.forEach(tipo => {
                const option = document.createElement('option');
                option.value = tipo.value;
                option.textContent = tipo.label;
                select.appendChild(option);
            });
        }

        async function carregarTiposMoveis() {
            try {
                for (const endpoint of ENDPOINTS_TIPOS_MOVEIS) {
                    try {
                        const data = await fazerRequisicaoAPI(endpoint);
                        const lista = normalizarListaTipos(data);
                        if (lista.length > 0) {
                            tiposMoveis = lista;
                            preencherSelectTiposMoveis();
                            return;
                        }
                    } catch (error) {
                        // tenta o próximo endpoint
                    }
                }
            } catch (error) {
                console.error('Erro ao carregar tipos de móvel:', error);
            }

            // Fallback para opções estáticas já existentes no HTML
            tiposMoveis = obterTiposMoveisFallback();
        }

        // Carregar estatísticas
        async function carregarEstatisticasOrcamentos() {
            try {
                const data = await fazerRequisicaoAPI(API_CONFIG.ORCAMENTOS_ESTATISTICAS());
                
                if (data.success && data.data) {
                    estatisticas = data.data;
                    atualizarEstatisticasUI();
                }
            } catch (error) {
                console.error('Erro ao carregar estatísticas:', error);
            }
        }

        function preencherSelectClientes() {
            const select = document.getElementById('id_cliente');
            select.innerHTML = '<option value="">Selecione um cliente</option>';
            
            if (clientes.length === 0) {
                const option = document.createElement('option');
                option.value = '';
                option.textContent = 'Nenhum cliente encontrado';
                option.disabled = true;
                select.appendChild(option);
                return;
            }
            
            clientes.forEach(cliente => {
                const option = document.createElement('option');
                option.value = cliente.id_cliente || cliente.id;
                option.textContent = cliente.nome_cliente || cliente.nome || 'Cliente sem nome';
                select.appendChild(option);
            });
        }

        function preencherSelectFiliais() {
            const select = document.getElementById('id_filial');
            select.innerHTML = '<option value="">Selecione uma filial</option>';
            
            if (filiais.length === 0) {
                const option = document.createElement('option');
                option.value = '';
                option.textContent = 'Nenhuma filial encontrada';
                option.disabled = true;
                select.appendChild(option);
                return;
            }
            
            filiais.forEach(filial => {
                const option = document.createElement('option');
                option.value = filial.id_filial;
                option.textContent = filial.nome_filial;
                select.appendChild(option);
            });
        }

        function preencherSelectCategorias() {
            const select = document.getElementById('categoria_movel');
            select.innerHTML = '<option value="">Selecione uma categoria</option>';
            
            if (categorias.length === 0) {
                const option = document.createElement('option');
                option.value = '';
                option.textContent = 'Nenhuma categoria encontrada';
                option.disabled = true;
                select.appendChild(option);
                return;
            }
            
            categorias.forEach(categoria => {
                const option = document.createElement('option');
                option.value = categoria.nome_categoria;
                option.textContent = categoria.nome_categoria;
                select.appendChild(option);
            });
        }

        function atualizarEstatisticasUI() {
            document.getElementById('totalOrcamentos').textContent = estatisticas.total_orcamentos || 0;
            document.getElementById('orcamentosAprovados').textContent = estatisticas.orcamentos_aprovados || 0;
            document.getElementById('orcamentosPendentes').textContent = estatisticas.orcamentos_pendentes || 0;
            document.getElementById('valorTotalAprovado').textContent = formatarMoeda(estatisticas.valor_total_aprovado || 0);
        }

        function exibirOrcamentos(listaOrcamentos) {
            const tbody = document.getElementById('tabelaOrcamentos');
            
            if (!listaOrcamentos || listaOrcamentos.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                            <p class="mt-3">Nenhum orçamento encontrado</p>
                            <button class="btn btn-primary btn-sm" onclick="abrirModalNovoOrcamento()">
                                <i class="bi bi-plus-circle me-1"></i>Criar Primeiro Orçamento
                            </button>
                        </td>
                    </tr>
                `;
                return;
            }
            
            tbody.innerHTML = listaOrcamentos.map(orcamento => `
                <tr>
                    <td><strong>${orcamento.codigo_orcamento}</strong></td>
                    <td>${orcamento.cliente?.nome_cliente || 'N/A'}</td>
                    <td>${orcamento.tipo_movel} - ${orcamento.categoria_movel}</td>
                    <td><strong>${formatarMoeda(orcamento.valor_orcado || 0)}</strong></td>
                    <td>
                        <span class="badge ${getStatusBadgeClass(orcamento.status)}">
                            ${formatarStatus(orcamento.status)}
                        </span>
                    </td>
                    <td>${formatarData(orcamento.created_at)}</td>
                    <td class="table-actions">
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary" onclick="verDetalhesOrcamento(${orcamento.id_orcamento})" title="Ver detalhes">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button class="btn btn-outline-secondary" onclick="editarOrcamento(${orcamento.id_orcamento})" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </button>
                            ${orcamento.status === 'rascunho' || orcamento.status === 'pendente' ? `
                                <button class="btn btn-outline-success" onclick="aprovarOrcamento(${orcamento.id_orcamento})" title="Aprovar">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                            ` : ''}
                            ${orcamento.status === 'aprovado' && !orcamento.ordem_producao ? `
                                <button class="btn btn-outline-info" onclick="criarOrdemProducao(${orcamento.id_orcamento})" title="Criar Ordem de Produção">
                                    <i class="bi bi-gear"></i>
                                </button>
                            ` : ''}
                        </div>
                    </td>
                </tr>
            `).join('');
        }

        function getStatusBadgeClass(status) {
            const classes = {
                'rascunho': 'bg-secondary',
                'pendente': 'bg-warning text-dark',
                'aprovado': 'bg-success',
                'rejeitado': 'bg-danger',
                'em_producao': 'bg-info',
                'finalizado': 'bg-primary'
            };
            return classes[status] || 'bg-secondary';
        }

        function formatarStatus(status) {
            const statusMap = {
                'rascunho': 'Rascunho',
                'pendente': 'Pendente',
                'aprovado': 'Aprovado',
                'rejeitado': 'Rejeitado',
                'em_producao': 'Em Produção',
                'finalizado': 'Finalizado'
            };
            return statusMap[status] || status;
        }

        function formatarData(data) {
            if (!data) return 'N/A';
            try {
                const date = new Date(data);
                return date.toLocaleDateString('pt-BR');
            } catch (e) {
                return data;
            }
        }

        function formatarMoeda(valor) {
            return new Intl.NumberFormat('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            }).format(valor || 0);
        }

        // Funções de CRUD
        async function verDetalhesOrcamento(id) {
            mostrarLoading(true);
            
            try {
                const data = await fazerRequisicaoAPI(API_CONFIG.ORCAMENTO_DETAIL(id));
                
                if (data.success) {
                    exibirModalDetalhes(data.data);
                }
            } catch (error) {
                console.error('Erro ao carregar detalhes:', error);
                mostrarNotificacao('Erro ao carregar detalhes do orçamento', 'error');
            } finally {
                mostrarLoading(false);
            }
        }

        function exibirModalDetalhes(orcamento) {
            const modalBody = document.getElementById('modalDetalhesOrcamentoBody');
            
            modalBody.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Informações do Orçamento</h6>
                        <table class="table table-sm">
                            <tr><td><strong>Código:</strong></td><td>${orcamento.codigo_orcamento}</td></tr>
                            <tr><td><strong>Cliente:</strong></td><td>${orcamento.cliente?.nome_cliente}</td></tr>
                            <tr><td><strong>Tipo Móvel:</strong></td><td>${orcamento.tipo_movel} - ${orcamento.categoria_movel}</td></tr>
                            <tr><td><strong>Status:</strong></td><td><span class="badge ${getStatusBadgeClass(orcamento.status)}">${formatarStatus(orcamento.status)}</span></td></tr>
                            <tr><td><strong>Valor Orçado:</strong></td><td>${formatarMoeda(orcamento.valor_orcado)}</td></tr>
                            <tr><td><strong>Prazo:</strong></td><td>${orcamento.prazo_entrega} dias</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Medidas e Detalhes</h6>
                        <table class="table table-sm">
                            <tr><td><strong>Medidas:</strong></td><td>${orcamento.medidas_gerais?.largura || 0}x${orcamento.medidas_gerais?.altura || 0}x${orcamento.medidas_gerais?.profundidade || 0} ${orcamento.medidas_gerais?.unidades || ''}</td></tr>
                            <tr><td><strong>Margem Lucro:</strong></td><td>${orcamento.margem_lucro}%</td></tr>
                            <tr><td><strong>Data Validade:</strong></td><td>${formatarData(orcamento.data_validade)}</td></tr>
                            <tr><td><strong>Criado em:</strong></td><td>${formatarData(orcamento.created_at)}</td></tr>
                        </table>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-12">
                        <h6>Descrição Detalhada</h6>
                        <p>${orcamento.descricao_detalhada || 'Sem descrição'}</p>
                    </div>
                </div>
                
                ${orcamento.observacoes_cliente ? `
                <div class="row mt-3">
                    <div class="col-12">
                        <h6>Observações do Cliente</h6>
                        <p>${orcamento.observacoes_cliente}</p>
                    </div>
                </div>
                ` : ''}
                
                ${orcamento.observacoes_internas ? `
                <div class="row mt-3">
                    <div class="col-12">
                        <h6>Observações Internas</h6>
                        <p>${orcamento.observacoes_internas}</p>
                    </div>
                </div>
                ` : ''}
                
                <div class="row mt-4">
                    <div class="col-12">
                        <h6>Itens do Orçamento</h6>
                        ${orcamento.itens && orcamento.itens.length > 0 ? 
                            orcamento.itens.map(item => `
                                <div class="item-material">
                                    <div class="d-flex justify-content-between">
                                        <strong>${item.tipo_componente} - ${item.material}</strong>
                                        <span>${formatarMoeda(item.custo_total)}</span>
                                    </div>
                                    <div class="mt-2">
                                        <small class="text-muted">Quantidade: ${item.quantidade} ${item.unidade_medida}</small>
                                        ${item.dimensoes ? `
                                            <div class="mt-1">
                                                ${Object.entries(item.dimensoes).map(([key, value]) => 
                                                    `<span class="medidas-badge">${key}: ${value}</span>`
                                                ).join('')}
                                            </div>
                                        ` : ''}
                                        ${item.observacoes ? `<div class="mt-1"><small>Obs: ${item.observacoes}</small></div>` : ''}
                                    </div>
                                </div>
                            `).join('') 
                            : '<p class="text-muted">Nenhum item cadastrado</p>'
                        }
                    </div>
                </div>
            `;
            
            const modal = new bootstrap.Modal(document.getElementById('modalDetalhesOrcamento'));
            modal.show();
        }

        function abrirModalNovoOrcamento() {
            document.getElementById('modalOrcamentoFormLabel').textContent = 'Novo Orçamento';
            document.getElementById('formOrcamento').reset();
            document.getElementById('id_orcamento').value = '';
            document.getElementById('itensOrcamentoContainer').innerHTML = '';
            itemCounter = 0;
            
            // Resetar valores padrão
            document.getElementById('margem_lucro').value = '35';
            document.getElementById('prazo_entrega').value = '15';
            document.getElementById('medida_unidades').value = 'cm';
            
            const modal = new bootstrap.Modal(document.getElementById('modalOrcamentoForm'));
            modal.show();
        }

        async function editarOrcamento(id) {
            mostrarLoading(true);
            
            try {
                const data = await fazerRequisicaoAPI(API_CONFIG.ORCAMENTO_DETAIL(id));
                
                if (data.success) {
                    preencherFormOrcamento(data.data);
                    
                    document.getElementById('modalOrcamentoFormLabel').textContent = 'Editar Orçamento';
                    const modal = new bootstrap.Modal(document.getElementById('modalOrcamentoForm'));
                    modal.show();
                }
            } catch (error) {
                console.error('Erro ao carregar orçamento para edição:', error);
                mostrarNotificacao('Erro ao carregar orçamento', 'error');
            } finally {
                mostrarLoading(false);
            }
        }

        function preencherFormOrcamento(orcamento) {
            document.getElementById('id_orcamento').value = orcamento.id_orcamento;
            document.getElementById('id_cliente').value = orcamento.id_cliente;
            document.getElementById('id_filial').value = orcamento.id_filial;
            document.getElementById('tipo_movel').value = orcamento.tipo_movel;
            document.getElementById('categoria_movel').value = orcamento.categoria_movel;
            document.getElementById('descricao_detalhada').value = orcamento.descricao_detalhada;
            document.getElementById('margem_lucro').value = orcamento.margem_lucro;
            document.getElementById('prazo_entrega').value = orcamento.prazo_entrega;
            document.getElementById('valor_orcado').value = orcamento.valor_orcado;
            document.getElementById('observacoes_cliente').value = orcamento.observacoes_cliente || '';
            document.getElementById('observacoes_internas').value = orcamento.observacoes_internas || '';
            
            // Medidas
            if (orcamento.medidas_gerais) {
                document.getElementById('medida_largura').value = orcamento.medidas_gerais.largura || '';
                document.getElementById('medida_altura').value = orcamento.medidas_gerais.altura || '';
                document.getElementById('medida_profundidade').value = orcamento.medidas_gerais.profundidade || '';
                document.getElementById('medida_unidades').value = orcamento.medidas_gerais.unidades || 'cm';
            }
            
            // Itens
            document.getElementById('itensOrcamentoContainer').innerHTML = '';
            itemCounter = 0;
            
            if (orcamento.itens && orcamento.itens.length > 0) {
                orcamento.itens.forEach(item => {
                    adicionarItem(item);
                });
            }
        }

        function adicionarItem(itemData = null) {
            itemCounter++;
            const itemId = `item_${itemCounter}`;
            
            const itemHTML = `
                <div class="item-material" id="${itemId}">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Item ${itemCounter}</h6>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removerItem('${itemId}')">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label small">Tipo Componente *</label>
                            <select class="form-select form-select-sm" name="itens[${itemCounter}][tipo_componente]" required>
                                <option value="">Selecione...</option>
                                ${TIPOS_COMPONENTES.map(tipo => 
                                    `<option value="${tipo}" ${itemData?.tipo_componente === tipo ? 'selected' : ''}>
                                        ${tipo.charAt(0).toUpperCase() + tipo.slice(1)}
                                    </option>`
                                ).join('')}
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Material *</label>
                            <input type="text" class="form-control form-control-sm" placeholder="Ex: MDF 15mm, Madeira Maciça..." 
                                   name="itens[${itemCounter}][material]" 
                                   value="${itemData?.material || ''}" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Quantidade *</label>
                            <input type="number" class="form-control form-control-sm" placeholder="Qtd" 
                                   name="itens[${itemCounter}][quantidade]" step="0.001" min="0"
                                   value="${itemData?.quantidade || ''}" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Unidade</label>
                            <select class="form-select form-select-sm" name="itens[${itemCounter}][unidade_medida]">
                                <option value="un" ${itemData?.unidade_medida === 'un' ? 'selected' : ''}>un</option>
                                <option value="m2" ${itemData?.unidade_medida === 'm2' ? 'selected' : ''}>m²</option>
                                <option value="m" ${itemData?.unidade_medida === 'm' ? 'selected' : ''}>m</option>
                                <option value="kg" ${itemData?.unidade_medida === 'kg' ? 'selected' : ''}>kg</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 mt-2">
                        <div class="col-md-3">
                            <label class="form-label small">Custo Unitário *</label>
                            <input type="number" class="form-control form-control-sm" placeholder="R$" 
                                   name="itens[${itemCounter}][custo_unitario]" step="0.01" min="0"
                                   value="${itemData?.custo_unitario || ''}" required>
                        </div>
                        <div class="col-md-9">
                            <label class="form-label small">Observações</label>
                            <input type="text" class="form-control form-control-sm" placeholder="Observações sobre o item..." 
                                   name="itens[${itemCounter}][observacoes]"
                                   value="${itemData?.observacoes || ''}">
                        </div>
                    </div>
                    <div class="mt-2">
                        <label class="form-label small">Dimensões *</label>
                        <div class="dimensoes-grid">
                            <div class="dimensao-input">
                                <label>Quantidade</label>
                                <input type="number" class="form-control form-control-sm" name="itens[${itemCounter}][dimensoes][quantidade]" 
                                       step="1" min="1" value="${itemData?.dimensoes?.quantidade || 1}" required>
                            </div>
                            <div class="dimensao-input">
                                <label>Largura</label>
                                <input type="number" class="form-control form-control-sm" name="itens[${itemCounter}][dimensoes][largura]" 
                                       step="0.1" min="0" value="${itemData?.dimensoes?.largura || ''}" required>
                            </div>
                            <div class="dimensao-input">
                                <label>Altura</label>
                                <input type="number" class="form-control form-control-sm" name="itens[${itemCounter}][dimensoes][altura]" 
                                       step="0.1" min="0" value="${itemData?.dimensoes?.altura || ''}" required>
                            </div>
                            <div class="dimensao-input">
                                <label>Profundidade</label>
                                <input type="number" class="form-control form-control-sm" name="itens[${itemCounter}][dimensoes][profundidade]" 
                                       step="0.1" min="0" value="${itemData?.dimensoes?.profundidade || ''}">
                            </div>
                            <div class="dimensao-input">
                                <label>Espessura</label>
                                <input type="number" class="form-control form-control-sm" name="itens[${itemCounter}][dimensoes][espessura]" 
                                       step="0.1" min="0" value="${itemData?.dimensoes?.espessura || ''}">
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            document.getElementById('itensOrcamentoContainer').insertAdjacentHTML('beforeend', itemHTML);
        }

        function removerItem(itemId) {
            const itemElement = document.getElementById(itemId);
            if (itemElement) {
                itemElement.remove();
            }
        }

        async function salvarOrcamento() {
            const form = document.getElementById('formOrcamento');
            const formData = new FormData(form);
            const orcamentoData = {};
            
            // Validar campos obrigatórios
            const camposObrigatorios = ['id_cliente', 'id_filial', 'tipo_movel', 'descricao_detalhada', 'margem_lucro', 'prazo_entrega', 'valor_orcado'];
            for (const campo of camposObrigatorios) {
                if (!formData.get(campo)) {
                    mostrarNotificacao(`O campo ${campo.replace('_', ' ')} é obrigatório`, 'error');
                    return;
                }
            }
            
            // Validar itens
            const itensContainer = document.getElementById('itensOrcamentoContainer');
            if (itensContainer.children.length === 0) {
                mostrarNotificacao('É necessário adicionar pelo menos um item ao orçamento', 'error');
                return;
            }
            
            // Converter FormData para objeto de forma estruturada
            orcamentoData.id_filial = parseInt(formData.get('id_filial'));
            orcamentoData.id_cliente = parseInt(formData.get('id_cliente'));
            const tipoMovelRaw = formData.get('tipo_movel');
            if (tiposMoveis.length > 0) {
                const validSet = new Set(tiposMoveis.map(t => String(t.value)));
                if (!validSet.has(String(tipoMovelRaw))) {
                    mostrarNotificacao('Tipo de móvel inválido. Recarregue a lista ou selecione outra opção.', 'warning');
                    return;
                }
            }
            orcamentoData.tipo_movel = /^\d+$/.test(tipoMovelRaw) ? parseInt(tipoMovelRaw, 10) : tipoMovelRaw;
            orcamentoData.categoria_movel = formData.get('categoria_movel') || '';
            orcamentoData.descricao_detalhada = formData.get('descricao_detalhada');
            orcamentoData.margem_lucro = parseFloat(formData.get('margem_lucro'));
            orcamentoData.prazo_entrega = parseInt(formData.get('prazo_entrega'));
            orcamentoData.valor_orcado = parseFloat(formData.get('valor_orcado'));
            orcamentoData.observacoes_cliente = formData.get('observacoes_cliente') || '';
            orcamentoData.observacoes_internas = formData.get('observacoes_internas') || '';
            
            // Processar medidas gerais
            const medidas = {};
            const largura = formData.get('medidas_gerais[largura]');
            const altura = formData.get('medidas_gerais[altura]');
            const profundidade = formData.get('medidas_gerais[profundidade]');
            const unidades = formData.get('medidas_gerais[unidades]');
            
            if (largura || altura || profundidade) {
                orcamentoData.medidas_gerais = {
                    largura: largura ? parseFloat(largura) : 0,
                    altura: altura ? parseFloat(altura) : 0,
                    profundidade: profundidade ? parseFloat(profundidade) : 0,
                    unidades: unidades || 'cm'
                };
            }
            
            // Processar itens
            orcamentoData.itens = [];
            
            // Coletar dados dos itens do formulário
            const itemElements = document.querySelectorAll('[id^="item_"]');
            itemElements.forEach((itemElement, index) => {
                const itemData = {
                    tipo_componente: itemElement.querySelector('[name*="[tipo_componente]"]').value,
                    material: itemElement.querySelector('[name*="[material]"]').value,
                    quantidade: parseFloat(itemElement.querySelector('[name*="[quantidade]"]').value),
                    unidade_medida: itemElement.querySelector('[name*="[unidade_medida]"]').value,
                    custo_unitario: parseFloat(itemElement.querySelector('[name*="[custo_unitario]"]').value),
                    observacoes: itemElement.querySelector('[name*="[observacoes]"]').value || '',
                    dimensoes: {
                        quantidade: parseInt(itemElement.querySelector('[name*="[dimensoes][quantidade]"]').value),
                        largura: parseFloat(itemElement.querySelector('[name*="[dimensoes][largura]"]').value),
                        altura: parseFloat(itemElement.querySelector('[name*="[dimensoes][altura]"]').value),
                        profundidade: parseFloat(itemElement.querySelector('[name*="[dimensoes][profundidade]"]').value) || 0,
                        espessura: parseFloat(itemElement.querySelector('[name*="[dimensoes][espessura]"]').value) || 0
                    }
                };
                
                orcamentoData.itens.push(itemData);
            });
            
            console.log('Dados a serem enviados:', orcamentoData);
            
            const idOrcamento = document.getElementById('id_orcamento').value;
            const isEdit = !!idOrcamento;
            
            mostrarLoading(true);
            
            try {
                let response;
                
                if (isEdit) {
                    response = await fazerRequisicaoAPI(API_CONFIG.ORCAMENTO_UPDATE(idOrcamento), {
                        method: 'PUT',
                        body: JSON.stringify(orcamentoData)
                    });
                } else {
                    response = await fazerRequisicaoAPI(API_CONFIG.ORCAMENTO_CREATE(), {
                        method: 'POST',
                        body: JSON.stringify(orcamentoData)
                    });
                }
                
                if (response.success) {
                    mostrarNotificacao(response.message || 'Orçamento salvo com sucesso!', 'success');
                    carregarOrcamentos();
                    carregarEstatisticasOrcamentos();
                    
                    const modal = bootstrap.Modal.getInstance(document.getElementById('modalOrcamentoForm'));
                    modal.hide();
                }
            } catch (error) {
                console.error('Erro ao salvar orçamento:', error);
                
                // Extrair mensagens de erro específicas da API
                let errorMessage = 'Erro ao salvar orçamento';
                if (error.message.includes('{') && error.message.includes('}')) {
                    try {
                        const errorObj = JSON.parse(error.message.split('HTTP 422: ')[1]);
                        const errorDetails = Object.values(errorObj).flat().join(', ');
                        errorMessage = `Erro de validação: ${errorDetails}`;
                    } catch (e) {
                        // Se não conseguir parsear, usar a mensagem original
                    }
                }
                
                mostrarNotificacao(errorMessage, 'error');
            } finally {
                mostrarLoading(false);
            }
        }

        async function aprovarOrcamento(id) {
            if (!confirm('Deseja aprovar este orçamento?')) return;
            
            mostrarLoading(true);
            
            try {
                const response = await fazerRequisicaoAPI(API_CONFIG.ORCAMENTO_APROVAR(id), {
                    method: 'POST'
                });
                
                if (response.success) {
                    mostrarNotificacao('Orçamento aprovado com sucesso!', 'success');
                    carregarOrcamentos();
                    carregarEstatisticasOrcamentos();
                }
            } catch (error) {
                console.error('Erro ao aprovar orçamento:', error);
                mostrarNotificacao('Erro ao aprovar orçamento', 'error');
            } finally {
                mostrarLoading(false);
            }
        }

        async function criarOrdemProducao(id) {
            if (!confirm('Deseja criar uma ordem de produção para este orçamento?')) return;
            
            mostrarLoading(true);
            
            try {
                const response = await fazerRequisicaoAPI(API_CONFIG.ORCAMENTO_CRIAR_ORDEM(id), {
                    method: 'POST'
                });
                
                if (response.success) {
                    mostrarNotificacao('Ordem de produção criada com sucesso!', 'success');
                    carregarOrcamentos();
                }
            } catch (error) {
                console.error('Erro ao criar ordem de produção:', error);
                mostrarNotificacao('Erro ao criar ordem de produção', 'error');
            } finally {
                mostrarLoading(false);
            }
        }

        function filtrarOrcamentos() {
            const termoBusca = document.getElementById('searchInputOrcamentos').value.toLowerCase();
            const statusFiltro = document.getElementById('filterStatusOrcamentos').value;
            const dataInicio = document.getElementById('filterDataInicioOrcamentos').value;
            const dataFim = document.getElementById('filterDataFimOrcamentos').value;
            
            orcamentosFiltrados = orcamentos.filter(orcamento => {
                // Filtro por termo de busca
                if (termoBusca) {
                    const buscaMatch = 
                        (orcamento.codigo_orcamento && orcamento.codigo_orcamento.toLowerCase().includes(termoBusca)) ||
                        (orcamento.cliente?.nome_cliente && orcamento.cliente.nome_cliente.toLowerCase().includes(termoBusca)) ||
                        (orcamento.tipo_movel && orcamento.tipo_movel.toLowerCase().includes(termoBusca)) ||
                        (orcamento.categoria_movel && orcamento.categoria_movel.toLowerCase().includes(termoBusca));
                    
                    if (!buscaMatch) return false;
                }
                
                // Filtro por status
                if (statusFiltro && orcamento.status !== statusFiltro) {
                    return false;
                }
                
                // Filtro por data
                if (dataInicio || dataFim) {
                    const dataOrcamento = orcamento.created_at;
                    if (!dataOrcamento) return false;
                    
                    try {
                        const dataObj = new Date(dataOrcamento);
                        const dataOrcamentoStr = dataObj.toISOString().split('T')[0];
                        
                        if (dataInicio && dataOrcamentoStr < dataInicio) return false;
                        if (dataFim && dataOrcamentoStr > dataFim) return false;
                    } catch (e) {
                        return false;
                    }
                }
                
                return true;
            });
            
            exibirOrcamentos(orcamentosFiltrados);
                    if (tiposMoveis.length === 0) {
                        const unicos = [...new Set(orcamentos.map(o => o.tipo_movel).filter(Boolean))];
                        if (unicos.length > 0) {
                            tiposMoveis = unicos.map((tipo) => ({ value: tipo, label: String(tipo) }));
                            preencherSelectTiposMoveis();
                        }
                    }
        }

        function limparFiltrosOrcamentos() {
            document.getElementById('searchInputOrcamentos').value = '';
            document.getElementById('filterStatusOrcamentos').value = '';
            document.getElementById('filterDataInicioOrcamentos').value = '';
            document.getElementById('filterDataFimOrcamentos').value = '';
            
            orcamentosFiltrados = [...orcamentos];
            exibirOrcamentos(orcamentosFiltrados);
                    if (tiposMoveis.length === 0) {
                        const unicos = [...new Set(orcamentos.map(o => o.tipo_movel).filter(Boolean))];
                        if (unicos.length > 0) {
                            tiposMoveis = unicos.map((tipo) => ({ value: tipo, label: String(tipo) }));
                            preencherSelectTiposMoveis();
                        }
                    }
        }

        function exportarRelatorioOrcamentos() {
            mostrarNotificacao('Relatório de orçamentos exportado!', 'success');
        }

        function imprimirOrcamento() {
            window.print();
        }

        function mostrarLoading(mostrar) {
            document.getElementById('loadingOverlay').classList.toggle('d-none', !mostrar);
        }

        function mostrarNotificacao(message, type) {
            // Remover notificações existentes
            const notificacoesExistentes = document.querySelectorAll('.alert-notification');
            notificacoesExistentes.forEach(notif => notif.remove());
            
            const notification = document.createElement('div');
            notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-notification alert-dismissible fade show position-fixed`;
            notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            notification.innerHTML = `
                <strong>${type === 'success' ? 'Sucesso!' : type === 'error' ? 'Erro!' : type === 'warning' ? 'Atenção!' : 'Info!'}</strong> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.body.appendChild(notification);
            
            // Auto-remover após 5 segundos
            setTimeout(() => {
                if (notification.parentNode) {
                    try {
                        notification.parentNode.removeChild(notification);
                    } catch (e) {
                        console.log('Erro ao remover notificação:', e);
                    }
                }
            }, 5000);
        }
    </script>
    <?php if (!defined('APP_SHELL')) { include __DIR__ . '/../components/app-foot.php'; } ?>
</body>
</html>











