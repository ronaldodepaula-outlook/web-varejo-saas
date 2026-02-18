<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?php if (!defined('APP_SHELL')) { $pageTitle = 'Sistema de Gestão - Marcenaria'; include __DIR__ . '/../components/app-head.php'; } ?>

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
        
        .sidebar {
            width: 250px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background: var(--secondary-color);
            color: white;
            transition: all 0.3s;
            z-index: 1000;
        }
        
        .sidebar-header {
            padding: 20px;
            background: rgba(0, 0, 0, 0.2);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-menu {
            padding: 0;
            list-style: none;
        }
        
        .sidebar-menu li {
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .sidebar-menu a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            padding: 15px 20px;
            display: block;
            transition: all 0.3s;
        }
        
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: rgba(0, 0, 0, 0.2);
            color: white;
            border-left: 4px solid var(--primary-color);
        }
        
        .sidebar-menu a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
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
        
        .table-custom th {
            border-top: none;
            font-weight: 600;
            color: #6c757d;
            font-size: 0.85rem;
            text-transform: uppercase;
        }
        
        .btn-action {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            margin: 0 2px;
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
        
        .status-em_andamento { background: rgba(52, 152, 219, 0.1); color: var(--primary-color); }
        .status-concluido { background: rgba(39, 174, 96, 0.1); color: var(--success-color); }
        .status-cancelado { background: rgba(231, 76, 60, 0.1); color: var(--danger-color); }
        
        .inventory-card {
            transition: all 0.3s ease;
            border-left: 4px solid #dee2e6;
        }
        .inventory-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .inventory-card.status-em_andamento { border-left-color: var(--primary-color); }
        .inventory-card.status-concluido { border-left-color: var(--success-color); }
        .inventory-card.status-cancelado { border-left-color: var(--danger-color); }
        
        .product-item {
            border-left: 3px solid transparent;
            transition: all 0.3s ease;
        }
        .product-item:hover {
            background-color: #f8f9fa;
            border-left-color: var(--primary-color);
        }
        .product-item.counted {
            background-color: #d4edda;
            border-left-color: var(--success-color);
        }
        .product-item.difference {
            background-color: #fff3cd;
            border-left-color: var(--warning-color);
        }
        
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
        }
        .step {
            flex: 1;
            text-align: center;
            position: relative;
        }
        .step::after {
            content: '';
            position: absolute;
            top: 20px;
            left: 50%;
            width: 100%;
            height: 2px;
            background: #dee2e6;
            z-index: -1;
        }
        .step:last-child::after {
            display: none;
        }
        .step.active .step-circle {
            background: var(--primary-color);
            color: white;
        }
        .step.completed .step-circle {
            background: var(--success-color);
            color: white;
        }
        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #dee2e6;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-weight: bold;
        }
        
        .difference-positive { color: var(--success-color); }
        .difference-negative { color: var(--danger-color); }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
            }
            .sidebar .menu-text {
                display: none;
            }
            .main-content {
                margin-left: 70px;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h4 class="mb-0">Marcenaria</h4>
            <small class="text-muted">Sistema de Gestão</small>
        </div>
        <ul class="sidebar-menu">
            <li><a href="#" class="active" data-module="dashboard"><i class="bi bi-speedometer2"></i> <span class="menu-text">Dashboard</span></a></li>
            <li><a href="#" data-module="orcamentos"><i class="bi bi-clipboard-check"></i> <span class="menu-text">Orçamentos</span></a></li>
            <li><a href="#" data-module="ordens-producao"><i class="bi bi-gear"></i> <span class="menu-text">Ordens de Produção</span></a></li>
            <li><a href="#" data-module="contas-receber"><i class="bi bi-cash-coin"></i> <span class="menu-text">Contas a Receber</span></a></li>
            <li><a href="#" data-module="contas-pagar"><i class="bi bi-credit-card"></i> <span class="menu-text">Contas a Pagar</span></a></li>
            <li><a href="#" data-module="health-check"><i class="bi bi-heart-pulse"></i> <span class="menu-text">Health Check</span></a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <header class="main-header">
            <div class="header-left">
                <button class="sidebar-toggle" type="button">
                    <i class="bi bi-list"></i>
                </button>
                
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-custom">
                        <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
                        <li class="breadcrumb-item active">Marcenaria</li>
                    </ol>
                </nav>
            </div>
            
            <div class="header-right">
                <!-- Notificações -->
                <div class="dropdown">
                    <button class="btn btn-link text-dark position-relative" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-bell" style="font-size: 1.25rem;"></i>
                        <span class="badge bg-danger position-absolute translate-middle rounded-pill" style="font-size: 0.6rem; top: 8px; right: 8px;">3</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><h6 class="dropdown-header">Notificações</h6></li>
                        <li><a class="dropdown-item" href="#">2 orçamentos pendentes</a></li>
                        <li><a class="dropdown-item" href="#">1 ordem de produção atrasada</a></li>
                        <li><a class="dropdown-item" href="#">3 contas a vencer</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-center" href="#">Ver todas</a></li>
                    </ul>
                </div>
                
                <!-- Dropdown do Usuário -->
                <div class="dropdown user-dropdown">
                    <div class="user-avatar" data-bs-toggle="dropdown" aria-expanded="false">
                        U
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><h6 class="dropdown-header user-name">Usuário</h6></li>
                        <li><small class="dropdown-header text-muted user-email">usuario@empresa.com</small></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Meu Perfil</a></li>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Configurações</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="#" id="logoutBtn"><i class="bi bi-box-arrow-right me-2"></i>Sair</a></li>
                    </ul>
                </div>
            </div>
        </header>
        
        <!-- Área de Conteúdo Dinâmico -->
        <div id="content-area">
            <!-- Conteúdo será carregado dinamicamente aqui -->
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Carregando...</span>
                </div>
                <p class="mt-2 text-muted">Carregando módulo...</p>
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
        const BASE_URL = '<?= addslashes($config['api_base']) ?>/api/v1';
        const token = '48|GHcPS87SBL842Vrw2Oi1o6mGToHvM2lOrwkWlDNRbdd0b4ff';
        
        // Configuração da API
        const API_CONFIG = {
            getHeaders: function() {
                return {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                };
            }
        };

        // Inicialização
        document.addEventListener('DOMContentLoaded', function() {
            // Carregar módulo inicial
            carregarModulo('dashboard');
            
            // Configurar eventos de navegação
            document.querySelectorAll('.sidebar-menu a').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Ativar link selecionado
                    document.querySelectorAll('.sidebar-menu a').forEach(a => a.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Carregar módulo
                    const modulo = this.getAttribute('data-module');
                    carregarModulo(modulo);
                });
            });
            
            // Logoff
            document.getElementById('logoutBtn').addEventListener('click', function(e) {
                e.preventDefault();
                fazerLogoff();
            });
        });

        // Função para fazer logout
        async function fazerLogoff() {
            try {
                const response = await fetch(BASE_URL + '/logout', {
                    method: 'POST',
                    headers: API_CONFIG.getHeaders()
                });
                
                window.location.href = 'login.php';
                
            } catch (error) {
                console.error('Erro no logout:', error);
                window.location.href = 'login.php';
            }
        }

        // Função para carregar módulos
        function carregarModulo(modulo) {
            mostrarLoading(true);
            
            // Atualizar breadcrumb
            const breadcrumb = document.querySelector('.breadcrumb-custom');
            breadcrumb.innerHTML = `
                <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
                <li class="breadcrumb-item active">${formatarNomeModulo(modulo)}</li>
            `;
            
            // Carregar conteúdo do módulo
            setTimeout(() => {
                const contentArea = document.getElementById('content-area');
                
                switch(modulo) {
                    case 'dashboard':
                        contentArea.innerHTML = carregarDashboard();
                        break;
                    case 'orcamentos':
                        contentArea.innerHTML = carregarOrcamentos();
                        break;
                    case 'ordens-producao':
                        contentArea.innerHTML = carregarOrdensProducao();
                        break;
                    case 'contas-receber':
                        contentArea.innerHTML = carregarContasReceber();
                        break;
                    case 'contas-pagar':
                        contentArea.innerHTML = carregarContasPagar();
                        break;
                    case 'health-check':
                        contentArea.innerHTML = carregarHealthCheck();
                        break;
                    default:
                        contentArea.innerHTML = '<div class="alert alert-danger">Módulo não encontrado</div>';
                }
                
                mostrarLoading(false);
            }, 500);
        }

        // Função para formatar nome do módulo
        function formatarNomeModulo(modulo) {
            const modulos = {
                'dashboard': 'Dashboard',
                'orcamentos': 'Orçamentos',
                'ordens-producao': 'Ordens de Produção',
                'contas-receber': 'Contas a Receber',
                'contas-pagar': 'Contas a Pagar',
                'health-check': 'Health Check'
            };
            
            return modulos[modulo] || modulo;
        }

        // Função para mostrar/ocultar loading
        function mostrarLoading(mostrar) {
            document.getElementById('loadingOverlay').classList.toggle('d-none', !mostrar);
        }

        // ========== MÓDULO DASHBOARD ==========
        function carregarDashboard() {
            return `
                <div class="content-area">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h1 class="page-title">Dashboard Marcenaria</h1>
                            <p class="page-subtitle">Visão geral do sistema de gestão de marcenaria</p>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-primary" onclick="exportarRelatorio()">
                                <i class="bi bi-file-earmark-text me-2"></i>Relatório
                            </button>
                            <button class="btn btn-primary" onclick="atualizarDashboard()">
                                <i class="bi bi-arrow-clockwise me-2"></i>Atualizar
                            </button>
                        </div>
                    </div>
                    
                    <!-- Resumo Geral -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card-custom">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="text-primary mb-0" id="totalOrcamentos">0</h5>
                                            <p class="text-muted mb-0">Total de Orçamentos</p>
                                        </div>
                                        <div class="bg-primary text-white rounded p-3">
                                            <i class="bi bi-clipboard-check" style="font-size: 1.5rem;"></i>
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
                                            <p class="text-muted mb-0">Orçamentos Pendentes</p>
                                        </div>
                                        <div class="bg-warning text-white rounded p-3">
                                            <i class="bi bi-hourglass-split" style="font-size: 1.5rem;"></i>
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
                                            <h5 class="text-success mb-0" id="ordensProducao">0</h5>
                                            <p class="text-muted mb-0">Ordens de Produção</p>
                                        </div>
                                        <div class="bg-success text-white rounded p-3">
                                            <i class="bi bi-gear" style="font-size: 1.5rem;"></i>
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
                                            <h5 class="text-info mb-0" id="receberMes">R$ 0,00</h5>
                                            <p class="text-muted mb-0">A Receber (Mês)</p>
                                        </div>
                                        <div class="bg-info text-white rounded p-3">
                                            <i class="bi bi-cash-coin" style="font-size: 1.5rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Gráficos e Estatísticas -->
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card-custom">
                                <div class="card-header-custom">
                                    <h5 class="mb-0">Estatísticas por Período</h5>
                                </div>
                                <div class="card-body">
                                    <div id="graficoEstatisticas" style="height: 300px;">
                                        <div class="text-center py-5">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Carregando...</span>
                                            </div>
                                            <p class="mt-2 text-muted">Carregando estatísticas...</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card-custom">
                                <div class="card-header-custom">
                                    <h5 class="mb-0">Status dos Orçamentos</h5>
                                </div>
                                <div class="card-body">
                                    <div id="graficoStatusOrcamentos" style="height: 300px;">
                                        <div class="text-center py-5">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Carregando...</span>
                                            </div>
                                            <p class="mt-2 text-muted">Carregando dados...</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Últimos Orçamentos -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card-custom">
                                <div class="card-header-custom d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Últimos Orçamentos</h5>
                                    <a href="#" onclick="carregarModulo('orcamentos')" class="btn btn-sm btn-outline-primary">Ver Todos</a>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Código</th>
                                                    <th>Cliente</th>
                                                    <th>Tipo Móvel</th>
                                                    <th>Valor</th>
                                                    <th>Status</th>
                                                    <th>Data</th>
                                                    <th>Ações</th>
                                                </tr>
                                            </thead>
                                            <tbody id="ultimosOrcamentos">
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
                    </div>
                </div>
            `;
        }

        // ========== MÓDULO ORÇAMENTOS ==========
        function carregarOrcamentos() {
            return `
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
                    
                    <!-- Filtros e Busca -->
                    <div class="card-custom mb-4">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="search-box">
                                        <i class="bi bi-search"></i>
                                        <input type="text" class="form-control" id="searchInputOrcamentos" placeholder="Buscar por cliente, tipo móvel...">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <select class="form-select" id="filterStatusOrcamentos">
                                        <option value="">Todos os status</option>
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
                                <div class="col-md-2 d-flex align-items-end">
                                    <button class="btn btn-outline-secondary w-100" onclick="limparFiltrosOrcamentos()">
                                        <i class="bi bi-arrow-clockwise"></i> Limpar
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
                                <button class="btn btn-outline-success btn-sm" onclick="carregarEstatisticasOrcamentos()">
                                    <i class="bi bi-graph-up me-1"></i>Estatísticas
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
                                            <th>Ações</th>
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
                        <div class="card-footer">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted" id="infoPaginacaoOrcamentos">Mostrando 0 de 0 orçamentos</small>
                                <nav>
                                    <ul class="pagination pagination-sm mb-0" id="paginacaoOrcamentos">
                                        <!-- Paginação será gerada dinamicamente -->
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        // ========== MÓDULO ORDENS DE PRODUÇÃO ==========
        function carregarOrdensProducao() {
            return `
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
                            <button class="btn btn-primary" onclick="abrirModalNovaOrdemProducao()">
                                <i class="bi bi-plus-circle me-2"></i>Nova Ordem
                            </button>
                        </div>
                    </div>
                    
                    <!-- Resumo da Produção -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card-custom">
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
                            <div class="card-custom">
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
                            <div class="card-custom">
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
                            <div class="card-custom">
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
                                        <option value="pendente">Pendente</option>
                                        <option value="em_andamento">Em Andamento</option>
                                        <option value="concluido">Concluído</option>
                                        <option value="cancelado">Cancelado</option>
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
                                <div class="col-md-2 d-flex align-items-end">
                                    <button class="btn btn-outline-secondary w-100" onclick="limparFiltrosOrdens()">
                                        <i class="bi bi-arrow-clockwise"></i> Limpar
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
                                <button class="btn btn-outline-success btn-sm" onclick="carregarEstatisticasProducao()">
                                    <i class="bi bi-graph-up me-1"></i>Estatísticas
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
                                            <th>Responsável</th>
                                            <th>Prazo</th>
                                            <th>Progresso</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tabelaOrdensProducao">
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">
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
                        <div class="card-footer">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted" id="infoPaginacaoOrdens">Mostrando 0 de 0 ordens</small>
                                <nav>
                                    <ul class="pagination pagination-sm mb-0" id="paginacaoOrdens">
                                        <!-- Paginação será gerada dinamicamente -->
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        // ========== MÓDULO CONTAS A RECEBER ==========
        function carregarContasReceber() {
            return `
                <div class="content-area">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h1 class="page-title">Contas a Receber</h1>
                            <p class="page-subtitle">Gestão de receitas e controle financeiro</p>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-primary" onclick="exportarRelatorioReceber()">
                                <i class="bi bi-file-earmark-text me-2"></i>Relatório
                            </button>
                            <button class="btn btn-primary" onclick="abrirModalNovaContaReceber()">
                                <i class="bi bi-plus-circle me-2"></i>Nova Conta
                            </button>
                        </div>
                    </div>
                    
                    <!-- Resumo Financeiro -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card-custom">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="text-primary mb-0" id="totalReceber">R$ 0,00</h5>
                                            <p class="text-muted mb-0">Total a Receber</p>
                                        </div>
                                        <div class="bg-primary text-white rounded p-3">
                                            <i class="bi bi-cash-coin" style="font-size: 1.5rem;"></i>
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
                                            <h5 class="text-success mb-0" id="recebidoMes">R$ 0,00</h5>
                                            <p class="text-muted mb-0">Recebido (Mês)</p>
                                        </div>
                                        <div class="bg-success text-white rounded p-3">
                                            <i class="bi bi-arrow-down-circle" style="font-size: 1.5rem;"></i>
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
                                            <h5 class="text-warning mb-0" id="vencimentoProximo">R$ 0,00</h5>
                                            <p class="text-muted mb-0">Vencimento Próximo</p>
                                        </div>
                                        <div class="bg-warning text-white rounded p-3">
                                            <i class="bi bi-calendar-check" style="font-size: 1.5rem;"></i>
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
                                            <h5 class="text-danger mb-0" id="vencidas">R$ 0,00</h5>
                                            <p class="text-muted mb-0">Vencidas</p>
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
                                        <input type="text" class="form-control" id="searchInputReceber" placeholder="Buscar por cliente, descrição...">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <select class="form-select" id="filterStatusReceber">
                                        <option value="">Todos os status</option>
                                        <option value="pendente">Pendente</option>
                                        <option value="pago">Pago</option>
                                        <option value="vencido">Vencido</option>
                                        <option value="cancelado">Cancelado</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Período</label>
                                    <div class="input-group">
                                        <input type="date" class="form-control" id="filterDataInicioReceber">
                                        <span class="input-group-text">até</span>
                                        <input type="date" class="form-control" id="filterDataFimReceber">
                                    </div>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button class="btn btn-outline-secondary w-100" onclick="limparFiltrosReceber()">
                                        <i class="bi bi-arrow-clockwise"></i> Limpar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Lista de Contas a Receber -->
                    <div class="card-custom">
                        <div class="card-header-custom d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Contas a Receber</h5>
                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-success btn-sm" onclick="carregarEstatisticasReceber()">
                                    <i class="bi bi-graph-up me-1"></i>Estatísticas
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
                                            <th>Descrição</th>
                                            <th>Valor</th>
                                            <th>Vencimento</th>
                                            <th>Status</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tabelaContasReceber">
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                <div class="spinner-border text-primary" role="status">
                                                    <span class="visually-hidden">Carregando...</span>
                                                </div>
                                                <p class="mt-2">Carregando contas a receber...</p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted" id="infoPaginacaoReceber">Mostrando 0 de 0 contas</small>
                                <nav>
                                    <ul class="pagination pagination-sm mb-0" id="paginacaoReceber">
                                        <!-- Paginação será gerada dinamicamente -->
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        // ========== MÓDULO CONTAS A PAGAR ==========
        function carregarContasPagar() {
            return `
                <div class="content-area">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h1 class="page-title">Contas a Pagar</h1>
                            <p class="page-subtitle">Gestão de despesas e controle financeiro</p>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-primary" onclick="exportarRelatorioPagar()">
                                <i class="bi bi-file-earmark-text me-2"></i>Relatório
                            </button>
                            <button class="btn btn-primary" onclick="abrirModalNovaContaPagar()">
                                <i class="bi bi-plus-circle me-2"></i>Nova Conta
                            </button>
                        </div>
                    </div>
                    
                    <!-- Resumo Financeiro -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card-custom">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="text-primary mb-0" id="totalPagar">R$ 0,00</h5>
                                            <p class="text-muted mb-0">Total a Pagar</p>
                                        </div>
                                        <div class="bg-primary text-white rounded p-3">
                                            <i class="bi bi-credit-card" style="font-size: 1.5rem;"></i>
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
                                            <h5 class="text-success mb-0" id="pagoMes">R$ 0,00</h5>
                                            <p class="text-muted mb-0">Pago (Mês)</p>
                                        </div>
                                        <div class="bg-success text-white rounded p-3">
                                            <i class="bi bi-arrow-up-circle" style="font-size: 1.5rem;"></i>
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
                                            <h5 class="text-warning mb-0" id="vencimentoProximoPagar">R$ 0,00</h5>
                                            <p class="text-muted mb-0">Vencimento Próximo</p>
                                        </div>
                                        <div class="bg-warning text-white rounded p-3">
                                            <i class="bi bi-calendar-check" style="font-size: 1.5rem;"></i>
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
                                            <h5 class="text-danger mb-0" id="vencidasPagar">R$ 0,00</h5>
                                            <p class="text-muted mb-0">Vencidas</p>
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
                                        <input type="text" class="form-control" id="searchInputPagar" placeholder="Buscar por fornecedor, descrição...">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <select class="form-select" id="filterStatusPagar">
                                        <option value="">Todos os status</option>
                                        <option value="pendente">Pendente</option>
                                        <option value="pago">Pago</option>
                                        <option value="vencido">Vencido</option>
                                        <option value="cancelado">Cancelado</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Período</label>
                                    <div class="input-group">
                                        <input type="date" class="form-control" id="filterDataInicioPagar">
                                        <span class="input-group-text">até</span>
                                        <input type="date" class="form-control" id="filterDataFimPagar">
                                    </div>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button class="btn btn-outline-secondary w-100" onclick="limparFiltrosPagar()">
                                        <i class="bi bi-arrow-clockwise"></i> Limpar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Lista de Contas a Pagar -->
                    <div class="card-custom">
                        <div class="card-header-custom d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Contas a Pagar</h5>
                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-success btn-sm" onclick="carregarEstatisticasPagar()">
                                    <i class="bi bi-graph-up me-1"></i>Estatísticas
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Código</th>
                                            <th>Fornecedor</th>
                                            <th>Descrição</th>
                                            <th>Valor</th>
                                            <th>Vencimento</th>
                                            <th>Status</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tabelaContasPagar">
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                <div class="spinner-border text-primary" role="status">
                                                    <span class="visually-hidden">Carregando...</span>
                                                </div>
                                                <p class="mt-2">Carregando contas a pagar...</p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted" id="infoPaginacaoPagar">Mostrando 0 de 0 contas</small>
                                <nav>
                                    <ul class="pagination pagination-sm mb-0" id="paginacaoPagar">
                                        <!-- Paginação será gerada dinamicamente -->
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        // ========== MÓDULO HEALTH CHECK ==========
        function carregarHealthCheck() {
            return `
                <div class="content-area">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h1 class="page-title">Health Check</h1>
                            <p class="page-subtitle">Status e monitoramento do sistema</p>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-primary" onclick="atualizarStatus()">
                                <i class="bi bi-arrow-clockwise me-2"></i>Atualizar
                            </button>
                        </div>
                    </div>
                    
                    <!-- Status do Sistema -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card-custom">
                                <div class="card-header-custom">
                                    <h5 class="mb-0">Status do Módulo</h5>
                                </div>
                                <div class="card-body">
                                    <div id="statusModulo" class="text-center py-4">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Carregando...</span>
                                        </div>
                                        <p class="mt-2">Verificando status do módulo...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card-custom">
                                <div class="card-header-custom">
                                    <h5 class="mb-0">Status Detalhado</h5>
                                </div>
                                <div class="card-body">
                                    <div id="statusDetalhado" class="text-center py-4">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Carregando...</span>
                                        </div>
                                        <p class="mt-2">Carregando status detalhado...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Informações do Sistema -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card-custom">
                                <div class="card-header-custom">
                                    <h5 class="mb-0">Informações do Sistema</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-sm">
                                                <tbody>
                                                    <tr>
                                                        <td><strong>Versão do Sistema</strong></td>
                                                        <td id="versaoSistema">Carregando...</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Última Atualização</strong></td>
                                                        <td id="ultimaAtualizacao">Carregando...</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Tempo de Atividade</strong></td>
                                                        <td id="tempoAtividade">Carregando...</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-sm">
                                                <tbody>
                                                    <tr>
                                                        <td><strong>Servidor</strong></td>
                                                        <td id="servidorInfo">Carregando...</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Banco de Dados</strong></td>
                                                        <td id="bancoDadosInfo">Carregando...</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>API Status</strong></td>
                                                        <td id="apiStatus">Carregando...</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        // Função para mostrar notificações
        function mostrarNotificacao(message, type) {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
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

        // Funções placeholder para ações
        function exportarRelatorio() {
            mostrarNotificacao('Relatório exportado com sucesso!', 'success');
        }

        function atualizarDashboard() {
            mostrarNotificacao('Dashboard atualizado!', 'info');
        }

        function abrirModalNovoOrcamento() {
            mostrarNotificacao('Abrindo modal de novo orçamento...', 'info');
        }

        function exportarRelatorioOrcamentos() {
            mostrarNotificacao('Relatório de orçamentos exportado!', 'success');
        }

        function limparFiltrosOrcamentos() {
            mostrarNotificacao('Filtros limpos!', 'info');
        }

        function carregarEstatisticasOrcamentos() {
            mostrarNotificacao('Carregando estatísticas de orçamentos...', 'info');
        }

        function abrirModalNovaOrdemProducao() {
            mostrarNotificacao('Abrindo modal de nova ordem de produção...', 'info');
        }

        function exportarRelatorioProducao() {
            mostrarNotificacao('Relatório de produção exportado!', 'success');
        }

        function limparFiltrosOrdens() {
            mostrarNotificacao('Filtros limpos!', 'info');
        }

        function carregarEstatisticasProducao() {
            mostrarNotificacao('Carregando estatísticas de produção...', 'info');
        }

        function abrirModalNovaContaReceber() {
            mostrarNotificacao('Abrindo modal de nova conta a receber...', 'info');
        }

        function exportarRelatorioReceber() {
            mostrarNotificacao('Relatório de contas a receber exportado!', 'success');
        }

        function limparFiltrosReceber() {
            mostrarNotificacao('Filtros limpos!', 'info');
        }

        function carregarEstatisticasReceber() {
            mostrarNotificacao('Carregando estatísticas de contas a receber...', 'info');
        }

        function abrirModalNovaContaPagar() {
            mostrarNotificacao('Abrindo modal de nova conta a pagar...', 'info');
        }

        function exportarRelatorioPagar() {
            mostrarNotificacao('Relatório de contas a pagar exportado!', 'success');
        }

        function limparFiltrosPagar() {
            mostrarNotificacao('Filtros limpos!', 'info');
        }

        function carregarEstatisticasPagar() {
            mostrarNotificacao('Carregando estatísticas de contas a pagar...', 'info');
        }

        function atualizarStatus() {
            mostrarNotificacao('Status atualizado!', 'info');
        }
    </script>
    <?php if (!defined('APP_SHELL')) { include __DIR__ . '/../components/app-foot.php'; } ?>
</body>
</html>








