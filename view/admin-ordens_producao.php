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
    <?php if (!defined('APP_SHELL')) { $pageTitle = 'Ordens de Produção - SAS Multi'; include __DIR__ . '/../components/app-head.php'; } ?>

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
        
        .spinner-border {
            width: 3rem;
            height: 3rem;
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .status-planejada { background: rgba(149, 165, 166, 0.1); color: #95a5a6; }
        .status-em_andamento { background: rgba(52, 152, 219, 0.1); color: var(--primary-color); }
        .status-concluida { background: rgba(39, 174, 96, 0.1); color: var(--success-color); }
        .status-cancelada { background: rgba(231, 76, 60, 0.1); color: var(--danger-color); }
        
        .prioridade-badge {
            padding: 3px 6px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
        }
        
        .prioridade-alta { background: rgba(231, 76, 60, 0.1); color: var(--danger-color); }
        .prioridade-media { background: rgba(243, 156, 18, 0.1); color: var(--warning-color); }
        .prioridade-baixa { background: rgba(39, 174, 96, 0.1); color: var(--success-color); }
        
        .progresso-barra {
            height: 8px;
            border-radius: 4px;
        }
        
        .hover-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .btn-action {
            padding: 4px 8px;
            font-size: 0.8rem;
        }
        
        .modal-xl-custom {
            max-width: 1200px;
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(52, 152, 219, 0.05);
        }
        
        .badge-days {
            font-size: 0.7rem;
            padding: 2px 6px;
        }
        
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
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
                        <li class="breadcrumb-item active">Ordens de Produção</li>
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
                    <h1 class="page-title">Ordens de Produção</h1>
                    <p class="page-subtitle">Controle e acompanhamento do processo produtivo</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary" onclick="exportarRelatorioProducao()">
                        <i class="bi bi-file-earmark-text me-2"></i>Relatório
                    </button>
                    <button class="btn btn-outline-success" onclick="carregarEstatisticasProducao()">
                        <i class="bi bi-graph-up me-2"></i>Estatísticas
                    </button>
                </div>
            </div>
            
            <!-- Resumo da Produção -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card-custom hover-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="text-primary mb-0" id="totalOrdens">0</h5>
                                    <p class="text-muted mb-0">Total de Ordens</p>
                                </div>
                                <div class="bg-primary text-white rounded p-3">
                                    <i class="bi bi-gear" style="font-size: 1.5rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card-custom hover-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="text-warning mb-0" id="ordensAndamento">0</h5>
                                    <p class="text-muted mb-0">Em Andamento</p>
                                </div>
                                <div class="bg-warning text-white rounded p-3">
                                    <i class="bi bi-hourglass-split" style="font-size: 1.5rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card-custom hover-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="text-success mb-0" id="ordensConcluidas">0</h5>
                                    <p class="text-muted mb-0">Concluídas</p>
                                </div>
                                <div class="bg-success text-white rounded p-3">
                                    <i class="bi bi-check-circle" style="font-size: 1.5rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card-custom hover-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="text-danger mb-0" id="ordensAtrasadas">0</h5>
                                    <p class="text-muted mb-0">Atrasadas</p>
                                </div>
                                <div class="bg-danger text-white rounded p-3">
                                    <i class="bi bi-exclamation-triangle" style="font-size: 1.5rem;"></i>
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
                                <input type="text" class="form-control" id="searchInputOrdens" placeholder="Buscar por código, cliente...">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" id="filterStatusOrdens">
                                <option value="">Todos os status</option>
                                <option value="planejada">Planejada</option>
                                <option value="em_andamento">Em Andamento</option>
                                <option value="concluida">Concluída</option>
                                <option value="cancelada">Cancelada</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" id="filterPrioridadeOrdens">
                                <option value="">Todas as prioridades</option>
                                <option value="alta">Alta</option>
                                <option value="media">Média</option>
                                <option value="baixa">Baixa</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Período</label>
                            <div class="input-group">
                                <input type="date" class="form-control" id="filterDataInicioOrdens">
                                <span class="input-group-text">até</span>
                                <input type="date" class="form-control" id="filterDataFimOrdens">
                            </div>
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button class="btn btn-outline-secondary w-100" onclick="limparFiltrosOrdens()" title="Limpar filtros">
                                <i class="bi bi-arrow-clockwise"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Lista de Ordens de Produção -->
            <div class="card-custom">
                <div class="card-header-custom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Ordens de Produção</h5>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary btn-sm" onclick="carregarOrdensProducao()" title="Recarregar">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Código</th>
                                    <th>Orçamento</th>
                                    <th>Cliente</th>
                                    <th>Status</th>
                                    <th>Prioridade</th>
                                    <th>Prazo</th>
                                    <th>Progresso</th>
                                    <th>Custo</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody id="tabelaOrdensProducao">
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Carregando...</span>
                                        </div>
                                        <p class="mt-2">Carregando ordens de produção...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal Detalhes Ordem Produção -->
    <div class="modal fade" id="modalDetalhesOrdem" tabindex="-1" aria-labelledby="modalDetalhesOrdemLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl-custom">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDetalhesOrdemLabel">Detalhes da Ordem de Produção</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="detalhesOrdemConteudo">
                        <!-- Conteúdo carregado dinamicamente -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Editar Ordem -->
    <div class="modal fade" id="modalEditarOrdem" tabindex="-1" aria-labelledby="modalEditarOrdemLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarOrdemLabel">Editar Ordem de Produção</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formEditarOrdem">
                        <input type="hidden" id="editOrdemId">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" id="editStatus" required>
                                        <option value="planejada">Planejada</option>
                                        <option value="em_andamento">Em Andamento</option>
                                        <option value="concluida">Concluída</option>
                                        <option value="cancelada">Cancelada</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Prioridade</label>
                                    <select class="form-select" id="editPrioridade" required>
                                        <option value="alta">Alta</option>
                                        <option value="media">Média</option>
                                        <option value="baixa">Baixa</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Horas Realizadas</label>
                                    <input type="number" class="form-control" id="editHorasRealizadas" step="0.5" min="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Custo Real</label>
                                    <input type="number" class="form-control" id="editCustoReal" step="0.01" min="0">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Observações de Produção</label>
                            <textarea class="form-control" id="editObservacoes" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="salvarEdicaoOrdem()">Salvar Alterações</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Adicionar Consumo -->
    <div class="modal fade" id="modalAdicionarConsumo" tabindex="-1" aria-labelledby="modalAdicionarConsumoLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAdicionarConsumoLabel">Adicionar Consumo de Material</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formAdicionarConsumo">
                        <input type="hidden" id="consumoOrdemId">
                        <div class="mb-3">
                            <label class="form-label">Produto</label>
                            <select class="form-select" id="consumoProdutoId" required>
                                <option value="">Selecione um produto...</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Quantidade Utilizada</label>
                                    <input type="number" class="form-control" id="consumoQuantidade" step="0.001" min="0" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Custo Unitário (R$)</label>
                                    <input type="number" class="form-control" id="consumoCustoUnitario" step="0.01" min="0" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Observações</label>
                            <textarea class="form-control" id="consumoObservacoes" rows="2"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="salvarConsumo()">Adicionar Consumo</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Estatísticas -->
    <div class="modal fade" id="modalEstatisticas" tabindex="-1" aria-labelledby="modalEstatisticasLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEstatisticasLabel">Estatísticas de Produção</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="estatisticasConteudo">
                        <!-- Conteúdo carregado dinamicamente -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
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
        
        let ordensProducao = [];
        let modalDetalhesOrdem = null;
        let modalEditarOrdem = null;
        let modalAdicionarConsumo = null;
        let modalEstatisticas = null;

        // Configuração da API
        const API_CONFIG = {
            ORDENS_PRODUCAO_EMPRESA: () => 
                `${BASE_URL}/api/v1/marcenaria/ordens-producao/empresa/${idEmpresa}`,
            
            ORDEM_PRODUCAO_DETAIL: (id) => 
                `${BASE_URL}/api/v1/marcenaria/ordens-producao/${id}`,
            
            ORDEM_PRODUCAO_UPDATE: (id) => 
                `${BASE_URL}/api/v1/marcenaria/ordens-producao/${id}`,
            
            ORDEM_INICIAR_PRODUCAO: (id) => 
                `${BASE_URL}/api/v1/marcenaria/ordens-producao/${id}/iniciar-producao`,
            
            ORDEM_CONCLUIR_PRODUCAO: (id) => 
                `${BASE_URL}/api/v1/marcenaria/ordens-producao/${id}/concluir-producao`,
            
            ORDEM_ADICIONAR_CONSUMO: (id) => 
                `${BASE_URL}/api/v1/marcenaria/ordens-producao/${id}/adicionar-consumo`,
            
            ORDENS_ESTATISTICAS: () => 
                `${BASE_URL}/api/v1/marcenaria/ordens-producao/estatisticas`,
            
            PRODUTOS_ESTOQUE: () =>
                `${BASE_URL}/api/v1/produtos/empresa/${idEmpresa}`,
            
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
            modalDetalhesOrdem = new bootstrap.Modal(document.getElementById('modalDetalhesOrdem'));
            modalEditarOrdem = new bootstrap.Modal(document.getElementById('modalEditarOrdem'));
            modalAdicionarConsumo = new bootstrap.Modal(document.getElementById('modalAdicionarConsumo'));
            modalEstatisticas = new bootstrap.Modal(document.getElementById('modalEstatisticas'));
            
            carregarOrdensProducao();
            
            // Configurar eventos
            document.getElementById('searchInputOrdens').addEventListener('input', filtrarOrdens);
            document.getElementById('filterStatusOrdens').addEventListener('change', filtrarOrdens);
            document.getElementById('filterPrioridadeOrdens').addEventListener('change', filtrarOrdens);
            
            // Logoff
            document.getElementById('logoutBtn').addEventListener('click', function(e) {
                e.preventDefault();
                fazerLogoff();
            });
        });

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

        // Carregar ordens de produção
        async function carregarOrdensProducao() {
            mostrarLoading(true);
            
            try {
                const response = await fetch(API_CONFIG.ORDENS_PRODUCAO_EMPRESA(), {
                    method: 'GET',
                    headers: API_CONFIG.getHeaders()
                });
                
                if (!response.ok) {
                    throw new Error(`Erro ${response.status}: ${response.statusText}`);
                }
                
                const data = await response.json();
                console.log('Resposta da API:', data);
                
                // Extrair ordens da resposta paginada
                ordensProducao = data.data?.data || [];
                
                exibirOrdensProducao(ordensProducao);
                atualizarResumoOrdens(ordensProducao);
                
            } catch (error) {
                console.error('Erro ao carregar ordens de produção:', error);
                mostrarNotificacao('Erro ao carregar ordens de produção: ' + error.message, 'error');
                exibirOrdensProducao([]);
            } finally {
                mostrarLoading(false);
            }
        }

        function exibirOrdensProducao(listaOrdens) {
            const tbody = document.getElementById('tabelaOrdensProducao');
            
            if (!Array.isArray(listaOrdens)) {
                console.error('listaOrdens não é um array:', listaOrdens);
                listaOrdens = [];
            }
            
            if (listaOrdens.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9" class="text-center text-muted py-4">Nenhuma ordem de produção encontrada</td></tr>';
                return;
            }
            
            tbody.innerHTML = listaOrdens.map(ordem => {
                const progresso = calcularProgresso(ordem);
                const estaAtrasada = verificarAtraso(ordem);
                const diasRestantes = calcularDiasRestantes(ordem);
                
                return `
                    <tr class="${estaAtrasada ? 'table-warning' : ''}">
                        <td>
                            <strong>${ordem.numero_ordem || `#${ordem.id_ordem_producao}`}</strong>
                            ${estaAtrasada ? '<i class="bi bi-exclamation-triangle text-warning ms-1" title="Ordem atrasada"></i>' : ''}
                        </td>
                        <td>${ordem.orcamento?.codigo_orcamento || 'N/A'}</td>
                        <td>${ordem.orcamento?.cliente?.nome_cliente || 'N/A'}</td>
                        <td>
                            <span class="status-badge status-${ordem.status}">
                                ${formatarStatus(ordem.status)}
                            </span>
                        </td>
                        <td>
                            <span class="prioridade-badge prioridade-${ordem.prioridade}">
                                ${formatarPrioridade(ordem.prioridade)}
                            </span>
                        </td>
                        <td>
                            ${diasRestantes !== null ? `
                                <span class="badge ${diasRestantes < 3 ? 'bg-danger' : diasRestantes < 7 ? 'bg-warning' : 'bg-success'} badge-days">
                                    ${diasRestantes} ${diasRestantes === 1 ? 'dia' : 'dias'}
                                </span>
                            ` : 'N/A'}
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="progress flex-grow-1 progresso-barra">
                                    <div class="progress-bar ${getProgressBarColor(progresso)}" style="width: ${progresso}%"></div>
                                </div>
                                <small class="ms-2">${progresso}%</small>
                            </div>
                        </td>
                        <td>
                            <small class="text-muted">${formatarMoeda(ordem.custo_estimado)}</small>
                            ${ordem.custo_real ? `<br><small class="text-success">${formatarMoeda(ordem.custo_real)}</small>` : ''}
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary btn-action" onclick="verDetalhesOrdem(${ordem.id_ordem_producao})" title="Ver detalhes">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-outline-secondary btn-action" onclick="editarOrdem(${ordem.id_ordem_producao})" title="Editar ordem">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                ${ordem.status === 'planejada' ? `
                                    <button class="btn btn-outline-success btn-action" onclick="iniciarProducao(${ordem.id_ordem_producao})" title="Iniciar Produção">
                                        <i class="bi bi-play-circle"></i>
                                    </button>
                                ` : ''}
                                ${ordem.status === 'em_andamento' ? `
                                    <button class="btn btn-outline-info btn-action" onclick="concluirProducao(${ordem.id_ordem_producao})" title="Concluir Produção">
                                        <i class="bi bi-check-circle"></i>
                                    </button>
                                    <button class="btn btn-outline-warning btn-action" onclick="abrirModalConsumo(${ordem.id_ordem_producao})" title="Adicionar Consumo">
                                        <i class="bi bi-plus-circle"></i>
                                    </button>
                                ` : ''}
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        function calcularProgresso(ordem) {
            if (!ordem) return 0;
            
            if (ordem.status === 'concluida') return 100;
            if (ordem.status === 'em_andamento') return 50;
            if (ordem.status === 'planejada') return 10;
            return 0;
        }

        function verificarAtraso(ordem) {
            if (!ordem || !ordem.data_previsao_entrega || ordem.status === 'concluida') return false;
            
            try {
                const dataPrevisao = new Date(ordem.data_previsao_entrega);
                return new Date() > dataPrevisao;
            } catch (e) {
                console.error('Erro ao verificar atraso:', e);
                return false;
            }
        }

        function calcularDiasRestantes(ordem) {
            if (!ordem || !ordem.data_previsao_entrega || ordem.status === 'concluida') return null;
            
            try {
                const dataPrevisao = new Date(ordem.data_previsao_entrega);
                const hoje = new Date();
                const diffTime = dataPrevisao - hoje;
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                return Math.max(0, diffDays);
            } catch (e) {
                console.error('Erro ao calcular dias restantes:', e);
                return null;
            }
        }

        function getProgressBarColor(progresso) {
            if (progresso < 30) return 'bg-danger';
            if (progresso < 70) return 'bg-warning';
            return 'bg-success';
        }

        function formatarStatus(status) {
            const statusMap = {
                'planejada': 'Planejada',
                'em_andamento': 'Em Andamento',
                'concluida': 'Concluída',
                'cancelada': 'Cancelada'
            };
            return statusMap[status] || status;
        }

        function formatarPrioridade(prioridade) {
            const prioridadeMap = {
                'alta': 'Alta',
                'media': 'Média',
                'baixa': 'Baixa'
            };
            return prioridadeMap[prioridade] || prioridade;
        }

        function atualizarResumoOrdens(ordens) {
            if (!Array.isArray(ordens)) {
                ordens = [];
            }
            
            const total = ordens.length;
            const emAndamento = ordens.filter(o => o.status === 'em_andamento').length;
            const concluidas = ordens.filter(o => o.status === 'concluida').length;
            const atrasadas = ordens.filter(verificarAtraso).length;
            
            document.getElementById('totalOrdens').textContent = total;
            document.getElementById('ordensAndamento').textContent = emAndamento;
            document.getElementById('ordensConcluidas').textContent = concluidas;
            document.getElementById('ordensAtrasadas').textContent = atrasadas;
        }

        function filtrarOrdens() {
            const termoBusca = document.getElementById('searchInputOrdens').value.toLowerCase();
            const statusFiltro = document.getElementById('filterStatusOrdens').value;
            const prioridadeFiltro = document.getElementById('filterPrioridadeOrdens').value;
            
            let ordensFiltradas = ordensProducao;
            
            if (termoBusca) {
                ordensFiltradas = ordensFiltradas.filter(ordem => {
                    if (!ordem) return false;
                    
                    const idMatch = ordem.numero_ordem && ordem.numero_ordem.toLowerCase().includes(termoBusca);
                    const clienteMatch = (
                        (ordem.orcamento?.cliente?.nome_cliente && ordem.orcamento.cliente.nome_cliente.toLowerCase().includes(termoBusca)) ||
                        (ordem.orcamento?.codigo_orcamento && ordem.orcamento.codigo_orcamento.toLowerCase().includes(termoBusca))
                    );
                    
                    return idMatch || clienteMatch;
                });
            }
            
            if (statusFiltro) {
                ordensFiltradas = ordensFiltradas.filter(ordem => ordem.status === statusFiltro);
            }
            
            if (prioridadeFiltro) {
                ordensFiltradas = ordensFiltradas.filter(ordem => ordem.prioridade === prioridadeFiltro);
            }
            
            exibirOrdensProducao(ordensFiltradas);
        }

        function limparFiltrosOrdens() {
            document.getElementById('searchInputOrdens').value = '';
            document.getElementById('filterStatusOrdens').value = '';
            document.getElementById('filterPrioridadeOrdens').value = '';
            document.getElementById('filterDataInicioOrdens').value = '';
            document.getElementById('filterDataFimOrdens').value = '';
            
            exibirOrdensProducao(ordensProducao);
        }

        async function verDetalhesOrdem(id) {
            if (!id) {
                mostrarNotificacao('ID da ordem inválido', 'error');
                return;
            }
            
            mostrarLoading(true);
            
            try {
                const response = await fetch(API_CONFIG.ORDEM_PRODUCAO_DETAIL(id), {
                    method: 'GET',
                    headers: API_CONFIG.getHeaders()
                });
                
                if (response.ok) {
                    const data = await response.json();
                    const ordem = data.data;
                    abrirModalDetalhesOrdem(ordem);
                } else {
                    throw new Error(`Erro ${response.status}: ${response.statusText}`);
                }
            } catch (error) {
                console.error('Erro ao carregar detalhes da ordem:', error);
                mostrarNotificacao('Erro ao carregar detalhes: ' + error.message, 'error');
            } finally {
                mostrarLoading(false);
            }
        }

        function abrirModalDetalhesOrdem(ordem) {
            if (!ordem) {
                mostrarNotificacao('Dados da ordem não disponíveis', 'error');
                return;
            }
            
            const conteudo = document.getElementById('detalhesOrdemConteudo');
            
            conteudo.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Informações da Ordem</h6>
                        <table class="table table-sm">
                            <tr><td><strong>Código:</strong></td><td>${ordem.numero_ordem || `#${ordem.id_ordem_producao}`}</td></tr>
                            <tr><td><strong>Status:</strong></td><td><span class="status-badge status-${ordem.status}">${formatarStatus(ordem.status)}</span></td></tr>
                            <tr><td><strong>Prioridade:</strong></td><td><span class="prioridade-badge prioridade-${ordem.prioridade}">${formatarPrioridade(ordem.prioridade)}</span></td></tr>
                            <tr><td><strong>Orçamento:</strong></td><td>${ordem.orcamento?.codigo_orcamento || 'N/A'}</td></tr>
                            <tr><td><strong>Cliente:</strong></td><td>${ordem.orcamento?.cliente?.nome_cliente || 'N/A'}</td></tr>
                            <tr><td><strong>Data Emissão:</strong></td><td>${formatarData(ordem.data_emissao)}</td></tr>
                            <tr><td><strong>Previsão Entrega:</strong></td><td>${formatarData(ordem.data_previsao_entrega)}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Detalhes de Produção</h6>
                        <table class="table table-sm">
                            <tr><td><strong>Data Início:</strong></td><td>${formatarData(ordem.data_inicio_producao)}</td></tr>
                            <tr><td><strong>Data Conclusão:</strong></td><td>${formatarData(ordem.data_conclusao)}</td></tr>
                            <tr><td><strong>Horas Estimadas:</strong></td><td>${ordem.horas_estimadas || 'N/A'}h</td></tr>
                            <tr><td><strong>Horas Realizadas:</strong></td><td>${ordem.horas_realizadas || '0'}h</td></tr>
                            <tr><td><strong>Custo Estimado:</strong></td><td>${formatarMoeda(ordem.custo_estimado)}</td></tr>
                            <tr><td><strong>Custo Real:</strong></td><td>${formatarMoeda(ordem.custo_real)}</td></tr>
                            <tr><td><strong>Observações:</strong></td><td>${ordem.observacoes_producao || 'Nenhuma'}</td></tr>
                        </table>
                    </div>
                </div>
                
                ${ordem.orcamento?.itens && ordem.orcamento.itens.length > 0 ? `
                <div class="mt-4">
                    <h6>Itens do Orçamento</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Componente</th>
                                    <th>Material</th>
                                    <th>Quantidade</th>
                                    <th>Unidade</th>
                                    <th>Custo Unitário</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${ordem.orcamento.itens.map(item => `
                                    <tr>
                                        <td>${item.tipo_componente || 'N/A'}</td>
                                        <td>${item.material || 'N/A'}</td>
                                        <td>${item.quantidade || '0'}</td>
                                        <td>${item.unidade_medida || 'un'}</td>
                                        <td>${formatarMoeda(item.custo_unitario)}</td>
                                        <td>${formatarMoeda(item.custo_total)}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                </div>
                ` : ''}
                
                ${ordem.consumos && ordem.consumos.length > 0 ? `
                <div class="mt-4">
                    <h6>Consumo de Materiais</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th>Quantidade</th>
                                    <th>Unidade</th>
                                    <th>Custo Unitário</th>
                                    <th>Total</th>
                                    <th>Data</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${ordem.consumos.map(consumo => `
                                    <tr>
                                        <td>${consumo.produto?.descricao || 'N/A'}</td>
                                        <td>${consumo.quantidade_utilizada || '0'}</td>
                                        <td>${consumo.unidade_medida || 'un'}</td>
                                        <td>${formatarMoeda(consumo.custo_unitario)}</td>
                                        <td>${formatarMoeda(consumo.custo_total)}</td>
                                        <td>${formatarData(consumo.data_consumo)}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                </div>
                ` : '<div class="mt-4"><p class="text-muted">Nenhum consumo registrado.</p></div>'}
            `;
            
            modalDetalhesOrdem.show();
        }

        async function editarOrdem(id) {
            if (!id) {
                mostrarNotificacao('ID da ordem inválido', 'error');
                return;
            }
            
            mostrarLoading(true);
            
            try {
                const response = await fetch(API_CONFIG.ORDEM_PRODUCAO_DETAIL(id), {
                    method: 'GET',
                    headers: API_CONFIG.getHeaders()
                });
                
                if (response.ok) {
                    const data = await response.json();
                    const ordem = data.data;
                    preencherFormEdicao(ordem);
                    modalEditarOrdem.show();
                } else {
                    throw new Error(`Erro ${response.status}: ${response.statusText}`);
                }
            } catch (error) {
                console.error('Erro ao carregar dados da ordem:', error);
                mostrarNotificacao('Erro ao carregar dados: ' + error.message, 'error');
            } finally {
                mostrarLoading(false);
            }
        }

        function preencherFormEdicao(ordem) {
            document.getElementById('editOrdemId').value = ordem.id_ordem_producao;
            document.getElementById('editStatus').value = ordem.status;
            document.getElementById('editPrioridade').value = ordem.prioridade;
            document.getElementById('editHorasRealizadas').value = ordem.horas_realizadas || '';
            document.getElementById('editCustoReal').value = ordem.custo_real || '';
            document.getElementById('editObservacoes').value = ordem.observacoes_producao || '';
        }

        async function salvarEdicaoOrdem() {
            const ordemId = document.getElementById('editOrdemId').value;
            const dados = {
                status: document.getElementById('editStatus').value,
                prioridade: document.getElementById('editPrioridade').value,
                horas_realizadas: parseFloat(document.getElementById('editHorasRealizadas').value) || null,
                custo_real: parseFloat(document.getElementById('editCustoReal').value) || null,
                observacoes_producao: document.getElementById('editObservacoes').value
            };
            
            mostrarLoading(true);
            
            try {
                const response = await fetch(API_CONFIG.ORDEM_PRODUCAO_UPDATE(ordemId), {
                    method: 'PUT',
                    headers: API_CONFIG.getHeaders(),
                    body: JSON.stringify(dados)
                });
                
                if (response.ok) {
                    const data = await response.json();
                    mostrarNotificacao('Ordem atualizada com sucesso!', 'success');
                    modalEditarOrdem.hide();
                    carregarOrdensProducao();
                } else {
                    const errorData = await response.json();
                    throw new Error(errorData.message || 'Erro ao atualizar ordem');
                }
            } catch (error) {
                console.error('Erro ao atualizar ordem:', error);
                mostrarNotificacao('Erro ao atualizar ordem: ' + error.message, 'error');
            } finally {
                mostrarLoading(false);
            }
        }

        async function abrirModalConsumo(ordemId) {
            document.getElementById('consumoOrdemId').value = ordemId;
            
            // Carregar lista de produtos
            await carregarProdutosEstoque();
            
            modalAdicionarConsumo.show();
        }

        async function carregarProdutosEstoque() {
            try {
                const response = await fetch(API_CONFIG.PRODUTOS_ESTOQUE(), {
                    method: 'GET',
                    headers: API_CONFIG.getHeaders()
                });
                
                if (response.ok) {
                    const data = await response.json();
                    const produtos = data.data || [];
                    preencherSelectProdutos(produtos);
                }
            } catch (error) {
                console.error('Erro ao carregar produtos:', error);
                mostrarNotificacao('Erro ao carregar lista de produtos', 'error');
            }
        }

        function preencherSelectProdutos(produtos) {
            const select = document.getElementById('consumoProdutoId');
            select.innerHTML = '<option value="">Selecione um produto...</option>';
            
            produtos.forEach(produto => {
                const option = document.createElement('option');
                option.value = produto.id_produto;
                option.textContent = `${produto.descricao} - ${formatarMoeda(produto.preco_custo)}`;
                select.appendChild(option);
            });
        }

        async function salvarConsumo() {
            const ordemId = document.getElementById('consumoOrdemId').value;
            const produtoId = document.getElementById('consumoProdutoId').value;
            const quantidade = parseFloat(document.getElementById('consumoQuantidade').value);
            const custoUnitario = parseFloat(document.getElementById('consumoCustoUnitario').value);
            const observacoes = document.getElementById('consumoObservacoes').value;
            
            if (!produtoId || !quantidade || !custoUnitario) {
                mostrarNotificacao('Preencha todos os campos obrigatórios', 'error');
                return;
            }
            
            const dados = {
                id_produto: parseInt(produtoId),
                quantidade_utilizada: quantidade,
                custo_unitario: custoUnitario,
                observacoes: observacoes
            };
            
            mostrarLoading(true);
            
            try {
                const response = await fetch(API_CONFIG.ORDEM_ADICIONAR_CONSUMO(ordemId), {
                    method: 'POST',
                    headers: API_CONFIG.getHeaders(),
                    body: JSON.stringify(dados)
                });
                
                if (response.ok) {
                    const data = await response.json();
                    mostrarNotificacao('Consumo registrado com sucesso!', 'success');
                    modalAdicionarConsumo.hide();
                    document.getElementById('formAdicionarConsumo').reset();
                    carregarOrdensProducao();
                } else {
                    const errorData = await response.json();
                    throw new Error(errorData.message || 'Erro ao registrar consumo');
                }
            } catch (error) {
                console.error('Erro ao registrar consumo:', error);
                mostrarNotificacao('Erro ao registrar consumo: ' + error.message, 'error');
            } finally {
                mostrarLoading(false);
            }
        }

        async function iniciarProducao(id) {
            if (!id) {
                mostrarNotificacao('ID da ordem inválido', 'error');
                return;
            }
            
            if (!confirm('Deseja iniciar a produção desta ordem?')) return;
            
            mostrarLoading(true);
            
            try {
                const response = await fetch(API_CONFIG.ORDEM_INICIAR_PRODUCAO(id), {
                    method: 'POST',
                    headers: API_CONFIG.getHeaders(),
                    body: JSON.stringify({})
                });
                
                if (response.ok) {
                    mostrarNotificacao('Produção iniciada com sucesso!', 'success');
                    carregarOrdensProducao();
                } else {
                    const errorData = await response.json();
                    throw new Error(errorData.message || 'Erro ao iniciar produção');
                }
            } catch (error) {
                console.error('Erro ao iniciar produção:', error);
                mostrarNotificacao('Erro ao iniciar produção: ' + error.message, 'error');
            } finally {
                mostrarLoading(false);
            }
        }

        async function concluirProducao(id) {
            if (!id) {
                mostrarNotificacao('ID da ordem inválido', 'error');
                return;
            }
            
            if (!confirm('Deseja concluir a produção desta ordem?')) return;
            
            mostrarLoading(true);
            
            try {
                const response = await fetch(API_CONFIG.ORDEM_CONCLUIR_PRODUCAO(id), {
                    method: 'POST',
                    headers: API_CONFIG.getHeaders(),
                    body: JSON.stringify({})
                });
                
                if (response.ok) {
                    mostrarNotificacao('Produção concluída com sucesso!', 'success');
                    carregarOrdensProducao();
                } else {
                    const errorData = await response.json();
                    throw new Error(errorData.message || 'Erro ao concluir produção');
                }
            } catch (error) {
                console.error('Erro ao concluir produção:', error);
                mostrarNotificacao('Erro ao concluir produção: ' + error.message, 'error');
            } finally {
                mostrarLoading(false);
            }
        }

        async function carregarEstatisticasProducao() {
            mostrarLoading(true);
            
            try {
                const response = await fetch(API_CONFIG.ORDENS_ESTATISTICAS(), {
                    method: 'GET',
                    headers: API_CONFIG.getHeaders()
                });
                
                if (response.ok) {
                    const data = await response.json();
                    exibirEstatisticas(data.data);
                    modalEstatisticas.show();
                } else {
                    throw new Error(`Erro ${response.status}: ${response.statusText}`);
                }
            } catch (error) {
                console.error('Erro ao carregar estatísticas:', error);
                mostrarNotificacao('Erro ao carregar estatísticas: ' + error.message, 'error');
            } finally {
                mostrarLoading(false);
            }
        }

        function exibirEstatisticas(estatisticas) {
            const conteudo = document.getElementById('estatisticasConteudo');
            
            conteudo.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <div class="card-custom mb-3">
                            <div class="card-body text-center">
                                <h3 class="text-primary">${estatisticas.total_ordens || 0}</h3>
                                <p class="text-muted mb-0">Total de Ordens</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card-custom mb-3">
                            <div class="card-body text-center">
                                <h3 class="text-warning">${estatisticas.ordens_em_andamento || 0}</h3>
                                <p class="text-muted mb-0">Em Andamento</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card-custom mb-3">
                            <div class="card-body text-center">
                                <h3 class="text-success">${estatisticas.ordens_concluidas || 0}</h3>
                                <p class="text-muted mb-0">Concluídas</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card-custom mb-3">
                            <div class="card-body text-center">
                                <h3 class="text-danger">${estatisticas.ordens_atrasadas || 0}</h3>
                                <p class="text-muted mb-0">Atrasadas</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card-custom">
                            <div class="card-body text-center">
                                <h3 class="text-info">${formatarMoeda(estatisticas.custo_total_producao || 0)}</h3>
                                <p class="text-muted mb-0">Custo Total de Produção</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        function exportarRelatorioProducao() {
            mostrarNotificacao('Relatório de produção exportado!', 'success');
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
            if (valor === null || valor === undefined || valor === '') return 'R$ 0,00';
            return new Intl.NumberFormat('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            }).format(parseFloat(valor));
        }

        function mostrarLoading(mostrar) {
            document.getElementById('loadingOverlay').classList.toggle('d-none', !mostrar);
        }

        function mostrarNotificacao(message, type) {
            // Remover notificações existentes
            const existingNotifications = document.querySelectorAll('.alert-notification');
            existingNotifications.forEach(notification => notification.remove());
            
            const notification = document.createElement('div');
            notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed alert-notification`;
            notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            notification.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 5000);
        }
    </script>
    <?php if (!defined('APP_SHELL')) { include __DIR__ . '/../components/app-foot.php'; } ?>
</body>
</html>










