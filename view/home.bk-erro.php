<?php
session_start();
if (!isset($_SESSION['authToken'], $_SESSION['usuario'], $_SESSION['empresa'], $_SESSION['licenca'])) {
    header('Location: login.php');
    exit;
}
$usuario = $_SESSION['usuario'];
$empresa = $_SESSION['empresa'];
$licenca = $_SESSION['licenca'];
$token = $_SESSION['authToken'];
$segmento = $_SESSION['segmento'];
$id_empresa = $_SESSION['empresa_id'] ?? (is_array($empresa) ? ($empresa['id_empresa'] ?? null) : null);

?>
<main class="main-content">
            <!-- Header -->
            <header class="main-header">
                <div class="header-left">
                    <button class="sidebar-toggle" type="button">
                        <i class="bi bi-list"></i>
                    </button>
                    
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-custom">
                            <li class="breadcrumb-item active">Dashboard Geral</li>
                        </ol>
                    </nav>
                </div>
                
                <div class="header-right">
                    <!-- Notificações -->
                    <div class="dropdown">
                        <button class="btn btn-link text-dark position-relative" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-bell" style="font-size: 1.25rem;"></i>
                            <span class="badge bg-danger position-absolute translate-middle rounded-pill" id="badgeNotificacoes" style="font-size: 0.6rem; top: 8px; right: 8px;">0</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><h6 class="dropdown-header">Notificações do Sistema</h6></li>
                            <li><a class="dropdown-item" href="#">Nova empresa cadastrada</a></li>
                            <li><a class="dropdown-item" href="#">Licença expirando em 3 dias</a></li>
                            <li><a class="dropdown-item" href="#">Pagamento pendente</a></li>
                            <li><a class="dropdown-item" href="#">Novo usuário ativo</a></li>
                            <li><a class="dropdown-item" href="#">Backup concluído</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-center" href="#">Ver todas</a></li>
                        </ul>
                    </div>
                    
                    <!-- Dropdown do Usuário -->
                    <div class="dropdown user-dropdown">
                        <div class="user-avatar" data-bs-toggle="dropdown" aria-expanded="false">
                            A
                        </div>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><h6 class="dropdown-header user-name">Admin Sistema</h6></li>
                            <li><small class="dropdown-header text-muted user-email">admin@nexusflow.com</small></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="perfil.html"><i class="bi bi-person me-2"></i>Meu Perfil</a></li>
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
                        <h1 class="page-title">Dashboard Geral do Sistema</h1>
                        <p class="page-subtitle">Visão geral de todas as empresas e métricas do sistema</p>
                    </div>
                    <div>
                        <button class="btn btn-outline-primary me-2" onclick="exportReport()">
                            <i class="bi bi-download me-2"></i>Exportar Relatório
                        </button>
                        <button class="btn btn-primary" onclick="refreshDashboard()">
                            <i class="bi bi-arrow-clockwise me-2"></i>Atualizar
                        </button>
                    </div>
                </div>
                
                <!-- Métricas Principais -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="metric-card primary">
                            <div class="metric-icon primary">
                                <i class="bi bi-cart-check"></i>
                            </div>
                            <div class="metric-value" id="metricVendas">-</div>
                            <div class="metric-label">Vendas (Per?odo)</div>
                            <div class="metric-change positive" id="metricVendasDelta">-</div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="metric-card success">
                            <div class="metric-icon success">
                                <i class="bi bi-bag-check"></i>
                            </div>
                            <div class="metric-value" id="metricCompras">-</div>
                            <div class="metric-label">Usuários Ativos</div>
                            <div class="metric-change positive" id="metricComprasDelta">-</div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="metric-card warning">
                            <div class="metric-icon warning">
                                <i class="bi bi-box-seam"></i>
                            </div>
                            <div class="metric-value" id="metricEstoque">-</div>
                            <div class="metric-label">Estoque (Valor Venda)</div>
                            <div class="metric-change positive" id="metricEstoqueDelta">-</div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="metric-card danger">
                            <div class="metric-icon danger">
                                <i class="bi bi-cash-coin"></i>
                            </div>
                            <div class="metric-value" id="metricFinanceiro">-</div>
                            <div class="metric-label">Licenças Expirando</div>
                            <div class="metric-change negative" id="metricFinanceiroDelta">-</div>
                        </div>
                    </div>
                </div>
                
                <!-- Gráficos e Dados -->
                <div class="row mb-4">
                    <!-- Gráfico de Crescimento -->
                    <div class="col-xl-8 mb-4">
                        <div class="card-custom">
                            <div class="card-header-custom d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Resumo Operacional</h5>
                                <div class="btn-group btn-group-sm" role="group">
                                    <input type="radio" class="btn-check" name="period" id="period7" checked>
                                    <label class="btn btn-outline-primary" for="period7">7 dias</label>
                                    
                                    <input type="radio" class="btn-check" name="period" id="period30">
                                    <label class="btn btn-outline-primary" for="period30">30 dias</label>
                                    
                                    <input type="radio" class="btn-check" name="period" id="period90">
                                    <label class="btn btn-outline-primary" for="period90">90 dias</label>
                                </div>
                            </div>
                            <div class="card-body">
                                <canvas id="growthChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Distribuição por Segmento -->
                    <div class="col-xl-4 mb-4">
                        <div class="card-custom">
                            <div class="card-header-custom">
                                <h5 class="mb-0">Pagamentos por Forma</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="segmentChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Tabelas de Dados -->
                <div class="row">
                    <!-- Empresas Recentes -->
                    <div class="col-xl-6 mb-4">
                        <div class="card-custom">
                            <div class="card-header-custom d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Empresas Cadastradas Recentemente</h5>
                                <a href="gerenciar-empresas.html" class="btn btn-sm btn-outline-primary">Ver todas</a>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-custom mb-0">
                                        <thead>
                                            <tr>
                                                <th>Empresa</th>
                                                <th>Segmento</th>
                                                <th>Status</th>
                                                <th>Data</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-3">
                                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                                                TI
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <div class="fw-semibold">Tech Inovação Ltda</div>
                                                            <small class="text-muted">tech@inovacao.com</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><span class="badge bg-info">Tecnologia</span></td>
                                                <td><span class="status-badge status-active">Ativa</span></td>
                                                <td>Hoje</td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-3">
                                                            <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                                                CV
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <div class="fw-semibold">Construtora Vanguarda</div>
                                                            <small class="text-muted">contato@vanguarda.com</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><span class="badge bg-warning">Construção</span></td>
                                                <td><span class="status-badge status-pending">Pendente</span></td>
                                                <td>Ontem</td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-3">
                                                            <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                                                MF
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <div class="fw-semibold">Moda & Fashion</div>
                                                            <small class="text-muted">vendas@modafashion.com</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><span class="badge bg-secondary">Varejo</span></td>
                                                <td><span class="status-badge status-active">Ativa</span></td>
                                                <td>2 dias</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Alertas do Sistema -->
                    <div class="col-xl-6 mb-4">
                        <div class="card-custom">
                            <div class="card-header-custom">
                                <h5 class="mb-0">Alertas e Notificações</h5>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-warning d-flex align-items-center mb-3">
                                    <i class="bi bi-cash-coin me-3"></i>
                                    <div>
                                        <strong>23 licenças</strong> expiram nos próximos 30 dias
                                        <div class="mt-1">
                                            <a href="planos-assinaturas.html" class="btn btn-sm btn-warning">Ver detalhes</a>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="alert alert-info d-flex align-items-center mb-3">
                                    <i class="bi bi-info-circle me-3"></i>
                                    <div>
                                        <strong>Backup automático</strong> concluído com sucesso
                                        <div class="mt-1">
                                            <small class="text-muted">Última execução: hoje às 03:00</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="alert alert-success d-flex align-items-center mb-3">
                                    <i class="bi bi-check-circle me-3"></i>
                                    <div>
                                        <strong>Sistema atualizado</strong> para versão 2.1.3
                                        <div class="mt-1">
                                            <small class="text-muted">Novas funcionalidades disponíveis</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="alert alert-danger d-flex align-items-center mb-0">
                                    <i class="bi bi-shield-exclamation me-3"></i>
                                    <div>
                                        <strong>5 tentativas</strong> de login suspeitas detectadas
                                        <div class="mt-1">
                                            <a href="#" class="btn btn-sm btn-danger">Investigar</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

    <script>
const BASE_URL = window.NEXUSFLOW_API_BASE_URL || '';
const token = '<?php echo $token; ?>';
const idEmpresa = <?php echo $id_empresa ? $id_empresa : 'null'; ?>;
const API_HEADERS = {
    'Authorization': `Bearer ${token}`,
    'Accept': 'application/json',
    'Content-Type': 'application/json'
};
if (idEmpresa) {
    API_HEADERS['X-ID-EMPRESA'] = String(idEmpresa);
}

let resumoChart = null;
let pagamentosChart = null;

function buildQuery(params) {
    const search = new URLSearchParams();
    Object.entries(params).forEach(([key, value]) => {
        if (value !== null && value !== undefined && value !== '') {
            search.append(key, value);
        }
    });
    return search.toString();
}

function formatarMoeda(valor) {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(Number(valor || 0));
}

function formatarFormaPagamento(forma) {
    const map = {
        dinheiro: 'Dinheiro',
        cartao: 'Cartao',
        cartao_credito: 'Cartao Credito',
        cartao_debito: 'Cartao Debito',
        pix: 'PIX',
        boleto: 'Boleto',
        transferencia: 'Transferencia',
        fiado: 'Fiado',
        outros: 'Outros'
    };
    return map[forma] || (forma ? forma.toString().toUpperCase() : 'Outros');
}

function atualizarMetricasOperacionais(data) {
    const vendasValor = data?.vendas?.total?.valor ?? 0;
    const vendasQtd = data?.vendas?.total?.quantidade ?? 0;
    const ticketMedio = data?.vendas?.total?.ticket_medio ?? 0;

    const comprasValor = data?.compras?.entradas?.valor ?? 0;
    const comprasQtd = data?.compras?.entradas?.quantidade ?? 0;
    const pedidosQtd = data?.compras?.pedidos?.quantidade ?? 0;

    const estoqueValor = data?.estoque?.valor_venda ?? 0;
    const estoqueItens = data?.estoque?.itens_com_estoque ?? 0;
    const estoqueQtd = data?.estoque?.quantidade_total ?? 0;

    const receberAberto = data?.financeiro?.contas_receber?.valor_em_aberto ?? 0;
    const pagarAberto = data?.financeiro?.contas_pagar?.valor_em_aberto ?? 0;
    const saldoFinanceiro = receberAberto - pagarAberto;

    const receberAtrasadas = data?.financeiro?.contas_receber?.atrasadas ?? 0;
    const pagarAtrasadas = data?.financeiro?.contas_pagar?.atrasadas ?? 0;

    const vendasEl = document.getElementById('metricVendas');
    if (vendasEl) vendasEl.textContent = formatarMoeda(vendasValor);
    const vendasDelta = document.getElementById('metricVendasDelta');
    if (vendasDelta) vendasDelta.innerHTML = `<i class="bi bi-info-circle"></i> Qtd: ${vendasQtd} | Ticket: ${formatarMoeda(ticketMedio)}`;

    const comprasEl = document.getElementById('metricCompras');
    if (comprasEl) comprasEl.textContent = formatarMoeda(comprasValor);
    const comprasDelta = document.getElementById('metricComprasDelta');
    if (comprasDelta) comprasDelta.innerHTML = `<i class="bi bi-info-circle"></i> Entradas: ${comprasQtd} | Pedidos: ${pedidosQtd}`;

    const estoqueEl = document.getElementById('metricEstoque');
    if (estoqueEl) estoqueEl.textContent = formatarMoeda(estoqueValor);
    const estoqueDelta = document.getElementById('metricEstoqueDelta');
    if (estoqueDelta) estoqueDelta.innerHTML = `<i class="bi bi-info-circle"></i> Itens: ${estoqueItens} | Qtde: ${Number(estoqueQtd || 0).toLocaleString('pt-BR')}`;

    const financeiroEl = document.getElementById('metricFinanceiro');
    if (financeiroEl) financeiroEl.textContent = formatarMoeda(saldoFinanceiro);
    const financeiroDelta = document.getElementById('metricFinanceiroDelta');
    if (financeiroDelta) financeiroDelta.innerHTML = `<i class="bi bi-info-circle"></i> Receber: ${formatarMoeda(receberAberto)} | Pagar: ${formatarMoeda(pagarAberto)}`;

    const badge = document.getElementById('badgeNotificacoes');
    if (badge) badge.textContent = (receberAtrasadas + pagarAtrasadas).toString();
}

function atualizarGraficosOperacionais(data) {
    const vendasValor = data?.vendas?.total?.valor ?? 0;
    const comprasValor = data?.compras?.entradas?.valor ?? 0;
    const estoqueValor = data?.estoque?.valor_venda ?? 0;
    const receberAberto = data?.financeiro?.contas_receber?.valor_em_aberto ?? 0;
    const pagarAberto = data?.financeiro?.contas_pagar?.valor_em_aberto ?? 0;
    const saldoFinanceiro = receberAberto - pagarAberto;

    const resumoCtx = document.getElementById('growthChart');
    if (resumoCtx) {
        if (resumoChart) resumoChart.destroy();
        resumoChart = new Chart(resumoCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: ['Vendas', 'Compras', 'Estoque', 'Saldo'],
                datasets: [{
                    label: 'Resumo Operacional (R$)',
                    data: [vendasValor, comprasValor, estoqueValor, saldoFinanceiro],
                    backgroundColor: ['#3498DB', '#F39C12', '#2ECC71', '#9B59B6']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    }

    const formas = data?.pagamentos?.por_forma || [];
    const labels = formas.map(f => formatarFormaPagamento(f.forma_pagamento));
    const valores = formas.map(f => Number(f.total || 0));

    const pagamentosCtx = document.getElementById('segmentChart');
    if (pagamentosCtx) {
        if (pagamentosChart) pagamentosChart.destroy();
        pagamentosChart = new Chart(pagamentosCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: labels.length ? labels : ['Sem dados'],
                datasets: [{
                    data: valores.length ? valores : [1],
                    backgroundColor: ['#3498DB','#2ECC71','#F39C12','#E74C3C','#9B59B6','#95A5A6']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }
}

async function carregarDashboard(filtro = 'mensal') {
    try {
        const query = buildQuery({ id_empresa: idEmpresa || undefined, filtro });
        const indicadoresResp = await fetch(`${BASE_URL}/api/v1/dashboard/indicadores?${query}`, {
            method: 'GET',
            headers: API_HEADERS
        });
        if (!indicadoresResp.ok) throw new Error(`Erro ${indicadoresResp.status}`);
        const indicadores = await indicadoresResp.json();
        atualizarMetricasOperacionais(indicadores);
        atualizarGraficosOperacionais(indicadores);
    } catch (error) {
        if (window.nexusFlow?.showNotification) {
            nexusFlow.showNotification('Erro ao carregar dashboard: ' + error.message, 'error');
        }
        console.error('Erro ao carregar dashboard:', error);
    }
}

// Logoff
        document.addEventListener('DOMContentLoaded', function() {
            var logoutBtn = document.getElementById('logoutBtn');
            if (logoutBtn) {
                logoutBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    fetch('logout.php', { method: 'POST' })
                        .then(() => { window.location.href = 'login.php'; });
                });
            }
        });

// Filtros de periodo
        document.addEventListener('DOMContentLoaded', function() {
            const period7 = document.getElementById('period7');
            const period30 = document.getElementById('period30');
            const period90 = document.getElementById('period90');
            if (period7) period7.addEventListener('change', () => carregarDashboard('semanal'));
            if (period30) period30.addEventListener('change', () => carregarDashboard('mensal'));
            if (period90) period90.addEventListener('change', () => carregarDashboard('anual'));
            carregarDashboard('mensal');
        });

function exportReport() {
    if (window.nexusFlow?.showNotification) {
        nexusFlow.showNotification('Exportando relatorio...', 'info');
        setTimeout(() => {
            nexusFlow.showNotification('Relatorio exportado com sucesso!', 'success');
        }, 2000);
    }
}

function refreshDashboard() {
    if (window.nexusFlow?.showNotification) {
        nexusFlow.showNotification('Atualizando dashboard...', 'info');
    }
    carregarDashboard('mensal');
}

// Definir papel como super admin
localStorage.setItem('userRole', 'super_admin');
</script>





