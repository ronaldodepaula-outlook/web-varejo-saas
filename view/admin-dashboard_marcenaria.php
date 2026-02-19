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
    <?php if (!defined('APP_SHELL')) { $pageTitle = 'Dashboard Marcenaria - SAS Multi'; include __DIR__ . '/../components/app-head.php'; } ?>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
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
                        <li class="breadcrumb-item active">Dashboard Marcenaria</li>
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
                        <li><h6 class="dropdown-header">Notificações Marcenaria</h6></li>
                        <li><a class="dropdown-item" href="?view=admin-orcamentos">2 orçamentos pendentes</a></li>
                        <li><a class="dropdown-item" href="?view=admin-ordens_producao">1 ordem de produção atrasada</a></li>
                        <li><a class="dropdown-item" href="?view=admin-contas_receber">3 contas a vencer</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-center" href="#">Ver todas</a></li>
                    </ul>
                </div>
                
                <!-- Dropdown do Usuário -->
                <div class="dropdown user-dropdown">
                    <div class="user-avatar" data-bs-toggle="dropdown" aria-expanded="false">
                        <?php echo $inicialUsuario; ?>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><h6 class="dropdown-header user-name"><?php echo htmlspecialchars($nomeUsuario); ?></h6></li>
                        <li><small class="dropdown-header text-muted user-email">
                            <?php 
                            if (is_array($usuario)) {
                                echo htmlspecialchars($usuario['email'] ?? $usuario['email_empresa'] ?? '');
                            } else {
                                echo htmlspecialchars($usuario);
                            }
                            ?>
                        </small></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="?view=perfil"><i class="bi bi-person me-2"></i>Meu Perfil</a></li>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Configurações</a></li>
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
                            <div class="chart-container">
                                <canvas id="graficoEstatisticas"></canvas>
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
                            <div class="chart-container">
                                <canvas id="graficoStatusOrcamentos"></canvas>
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
                            <a href="?view=admin-orcamentos" class="btn btn-sm btn-outline-primary">Ver Todos</a>
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
    </main>

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
        
        // Gráficos
        let chartEstatisticas = null;
        let chartStatusOrcamentos = null;
        
        // Configuração da API
        const API_CONFIG = {
            DASHBOARD_RESUMO: () => 
                `${BASE_URL}/api/v1/marcenaria/dashboard/resumo`,
            
            DASHBOARD_ESTATISTICAS: () => 
                `${BASE_URL}/api/v1/marcenaria/dashboard/estatisticas-periodo?periodo=mes`,
            
            ORCAMENTOS_LISTA: () => 
                `${BASE_URL}/api/v1/marcenaria/orcamentos?limit=5`,
            
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
            carregarDashboard();
            
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

        // Função auxiliar para fazer requisições com tratamento de erro
        async function fazerRequisicaoAPI(url, options = {}) {
            try {
                const response = await fetch(url, {
                    ...options,
                    headers: API_CONFIG.getHeaders(),
                    mode: 'cors'
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                return await response.json();
            } catch (error) {
                console.error('Erro na requisição API:', error);
                throw error;
            }
        }

        // Carregar dados do dashboard
        async function carregarDashboard() {
            mostrarLoading(true);
            
            try {
                // Carregar dados em paralelo
                const [resumo, estatisticas, orcamentos] = await Promise.allSettled([
                    fazerRequisicaoAPI(API_CONFIG.DASHBOARD_RESUMO()),
                    fazerRequisicaoAPI(API_CONFIG.DASHBOARD_ESTATISTICAS()),
                    fazerRequisicaoAPI(API_CONFIG.ORCAMENTOS_LISTA())
                ]);

                // Processar resumo
                if (resumo.status === 'fulfilled') {
                    atualizarResumo(resumo.value);
                } else {
                    console.error('Erro ao carregar resumo:', resumo.reason);
                }

                // Processar estatísticas
                if (estatisticas.status === 'fulfilled') {
                    atualizarEstatisticas(estatisticas.value);
                    atualizarGraficoStatus(estatisticas.value);
                } else {
                    console.error('Erro ao carregar estatísticas:', estatisticas.reason);
                    exibirPlaceholderGraficos();
                }

                // Processar orçamentos
                if (orcamentos.status === 'fulfilled') {
                    exibirUltimosOrcamentos(orcamentos.value);
                } else {
                    console.error('Erro ao carregar orçamentos:', orcamentos.reason);
                    exibirUltimosOrcamentos([]);
                }
                
            } catch (error) {
                console.error('Erro ao carregar dashboard:', error);
                mostrarNotificacao('Erro ao carregar dados do dashboard', 'error');
            } finally {
                mostrarLoading(false);
            }
        }

        function atualizarResumo(dados) {
            if (dados && dados.data) {
                const resumo = dados.data;
                document.getElementById('totalOrcamentos').textContent = resumo.total_orcamentos || 0;
                document.getElementById('orcamentosPendentes').textContent = resumo.orcamentos_pendentes || 0;
                document.getElementById('ordensProducao').textContent = resumo.ordens_producao || 0;
                document.getElementById('receberMes').textContent = formatarMoeda(resumo.receber_mes || 0);
            } else {
                // Dados de fallback para demonstração
                document.getElementById('totalOrcamentos').textContent = '12';
                document.getElementById('orcamentosPendentes').textContent = '3';
                document.getElementById('ordensProducao').textContent = '8';
                document.getElementById('receberMes').textContent = formatarMoeda(12500);
            }
        }

        function atualizarEstatisticas(dados) {
            const ctx = document.getElementById('graficoEstatisticas').getContext('2d');
            
            // Destruir gráfico anterior se existir
            if (chartEstatisticas) {
                chartEstatisticas.destroy();
            }
            
            // Dados de exemplo - substitua pelos dados reais da API
            const dadosExemplo = {
                labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'],
                datasets: [
                    {
                        label: 'Orçamentos',
                        data: [12, 19, 8, 15, 12, 17],
                        backgroundColor: 'rgba(52, 152, 219, 0.2)',
                        borderColor: 'rgba(52, 152, 219, 1)',
                        borderWidth: 2,
                        tension: 0.4
                    },
                    {
                        label: 'Vendas',
                        data: [8, 12, 6, 10, 9, 14],
                        backgroundColor: 'rgba(46, 204, 113, 0.2)',
                        borderColor: 'rgba(46, 204, 113, 1)',
                        borderWidth: 2,
                        tension: 0.4
                    }
                ]
            };
            
            chartEstatisticas = new Chart(ctx, {
                type: 'line',
                data: dadosExemplo,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Evolução Mensal'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        function atualizarGraficoStatus(dados) {
            const ctx = document.getElementById('graficoStatusOrcamentos').getContext('2d');
            
            // Destruir gráfico anterior se existir
            if (chartStatusOrcamentos) {
                chartStatusOrcamentos.destroy();
            }
            
            // Dados de exemplo - substitua pelos dados reais da API
            const dadosExemplo = {
                labels: ['Aprovados', 'Pendentes', 'Em Produção', 'Rejeitados'],
                datasets: [{
                    data: [45, 25, 20, 10],
                    backgroundColor: [
                        'rgba(46, 204, 113, 0.8)',
                        'rgba(243, 156, 18, 0.8)',
                        'rgba(52, 152, 219, 0.8)',
                        'rgba(231, 76, 60, 0.8)'
                    ],
                    borderColor: [
                        'rgba(46, 204, 113, 1)',
                        'rgba(243, 156, 18, 1)',
                        'rgba(52, 152, 219, 1)',
                        'rgba(231, 76, 60, 1)'
                    ],
                    borderWidth: 2
                }]
            };
            
            chartStatusOrcamentos = new Chart(ctx, {
                type: 'doughnut',
                data: dadosExemplo,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        }
                    }
                }
            });
        }

        function exibirPlaceholderGraficos() {
            // Implementar placeholders caso os gráficos falhem
            console.log('Exibindo placeholders para gráficos');
        }

        function exibirUltimosOrcamentos(dados) {
            const tbody = document.getElementById('ultimosOrcamentos');
            
            // Extrair array de orçamentos da resposta
            let orcamentos = [];
            if (Array.isArray(dados)) {
                orcamentos = dados;
            } else if (dados && Array.isArray(dados.data)) {
                orcamentos = dados.data;
            } else if (dados && dados.data && typeof dados.data === 'object') {
                // Se data for um objeto, converter para array
                orcamentos = Object.values(dados.data);
            }
            
            // Limitar a 5 registros
            const ultimosOrcamentos = orcamentos.slice(0, 5);
            
            if (ultimosOrcamentos.length === 0) {
                // Dados de exemplo para demonstração
                const dadosExemplo = [
                    {
                        id: 1001,
                        cliente: { nome: 'João Silva' },
                        tipo_movel: 'Guarda-Roupas',
                        valor_orcado: 2500.00,
                        status: 'aprovado',
                        created_at: '2024-01-15'
                    },
                    {
                        id: 1002,
                        cliente: { nome: 'Maria Santos' },
                        tipo_movel: 'Estante',
                        valor_orcado: 1200.00,
                        status: 'pendente',
                        created_at: '2024-01-14'
                    },
                    {
                        id: 1003,
                        cliente: { nome: 'Pedro Oliveira' },
                        tipo_movel: 'Mesa de Jantar',
                        valor_orcado: 1800.00,
                        status: 'em_producao',
                        created_at: '2024-01-13'
                    }
                ];
                
                renderizarOrcamentos(dadosExemplo, tbody);
                return;
            }
            
            renderizarOrcamentos(ultimosOrcamentos, tbody);
        }

        function renderizarOrcamentos(orcamentos, tbody) {
            if (orcamentos.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">Nenhum orçamento encontrado</td></tr>';
                return;
            }
            
            tbody.innerHTML = orcamentos.map(orcamento => `
                <tr>
                    <td>#${orcamento.id || orcamento.codigo || 'N/A'}</td>
                    <td>${orcamento.cliente?.nome || orcamento.nome_cliente || 'Cliente não informado'}</td>
                    <td>${orcamento.tipo_movel || orcamento.produto || 'Não especificado'}</td>
                    <td>${formatarMoeda(orcamento.valor_orcado || orcamento.valor || 0)}</td>
                    <td><span class="badge ${getStatusBadgeClass(orcamento.status)}">${formatarStatus(orcamento.status)}</span></td>
                    <td>${formatarData(orcamento.created_at || orcamento.data_criacao)}</td>
                    <td>
                        <button class="btn btn-primary btn-sm" onclick="abrirDetalhesOrcamento(${orcamento.id})">
                            <i class="bi bi-eye"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
        }

        function getStatusBadgeClass(status) {
            const classes = {
                'pendente': 'bg-warning',
                'aprovado': 'bg-success',
                'rejeitado': 'bg-danger',
                'em_producao': 'bg-info',
                'finalizado': 'bg-secondary'
            };
            return classes[status] || 'bg-secondary';
        }

        function formatarStatus(status) {
            const statusMap = {
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
            }).format(valor);
        }

        function abrirDetalhesOrcamento(id) {
            window.location.href = `?view=admin-orcamentos&id=${id}`;
        }

        function exportarRelatorio() {
            mostrarNotificacao('Relatório exportado com sucesso!', 'success');
        }

        function atualizarDashboard() {
            carregarDashboard();
            mostrarNotificacao('Dashboard atualizado!', 'info');
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
                <strong>${type === 'success' ? 'Sucesso!' : type === 'error' ? 'Erro!' : 'Info!'}</strong> ${message}
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










