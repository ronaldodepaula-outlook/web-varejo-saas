
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
$id_empresa = $_SESSION['empresa_id'] ?? (is_array($empresa) ? ($empresa['id'] ?? $empresa['id_empresa'] ?? $empresa['empresa_id'] ?? null) : null);
$id_filial = $_SESSION['filial_id'] ?? null;

?>
<style>
    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 24px;
    }

    .dashboard-header .page-title {
        margin-bottom: 6px;
    }

    .dashboard-filters {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        align-items: center;
        max-width: 100%;
    }

    .metrics-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
        max-width: 100%;
    }

    .metric-card {
        background: #fff;
        border-radius: 16px;
        padding: 18px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(148, 163, 184, 0.2);
        min-width: 0;
    }

    .metric-card::after {
        content: '';
        position: absolute;
        inset: auto -30px -30px auto;
        width: 90px;
        height: 90px;
        border-radius: 50%;
        background: rgba(99, 102, 241, 0.08);
        z-index: 0;
    }

    .metric-card .metric-icon {
        font-size: 28px;
        color: #4f46e5;
        margin-bottom: 8px;
        z-index: 1;
        position: relative;
    }

    .metric-card .metric-label {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: #64748b;
        margin-bottom: 8px;
    }

    .metric-card .metric-value {
        font-size: 24px;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 4px;
    }

    .metric-card .metric-sub {
        font-size: 13px;
        color: #94a3b8;
    }

    .chart-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 18px;
        margin-bottom: 28px;
        max-width: 100%;
    }

    .chart-card {
        background: #fff;
        border-radius: 16px;
        padding: 18px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
        border: 1px solid rgba(148, 163, 184, 0.2);
        display: flex;
        flex-direction: column;
        position: relative;
        min-width: 0;
    }

    .chart-body {
        position: relative;
        height: 260px;
    }

    .chart-body canvas {
        width: 100% !important;
        height: 100% !important;
    }

    .chart-card.is-expanded {
        grid-column: 1 / -1;
    }

    .chart-card.is-expanded .chart-body {
        height: 420px;
    }

    @media (min-width: 1400px) {
        .chart-body { height: 300px; }
        .chart-card.is-expanded .chart-body { height: 480px; }
    }

    @media (max-width: 1200px) {
        .chart-body { height: 230px; }
    }

    @media (max-width: 768px) {
        .chart-body { height: 200px; }
        .chart-card.is-expanded .chart-body { height: 300px; }
    }

    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
    }

    .chart-title {
        font-weight: 600;
        color: #0f172a;
    }

    .table-container {
        background: #fff;
        border-radius: 16px;
        padding: 18px;
        margin-bottom: 24px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
        border: 1px solid rgba(148, 163, 184, 0.2);
        max-width: 100%;
    }

    .table-container .table-title {
        font-weight: 600;
        color: #0f172a;
        margin-bottom: 14px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .table-custom th {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: #64748b;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .status-success { background: rgba(16, 185, 129, 0.12); color: #047857; }
    .status-warning { background: rgba(245, 158, 11, 0.15); color: #b45309; }
    .status-danger { background: rgba(239, 68, 68, 0.15); color: #b91c1c; }
    .status-info { background: rgba(59, 130, 246, 0.12); color: #1d4ed8; }

    @media (max-width: 768px) {
        .dashboard-header {
            flex-direction: column;
            align-items: flex-start;
        }
    }

    @media (max-width: 640px) {
        .metrics-grid {
            grid-template-columns: 1fr;
        }

        .chart-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 576px) {
        .dashboard-filters {
            width: 100%;
        }

        .dashboard-filters .form-select,
        .dashboard-filters .btn {
            width: 100%;
            min-width: 0 !important;
        }
    }
</style>

<main class="main-content">
    <header class="main-header">
        <div class="header-left">
            <button class="sidebar-toggle" type="button">
                <i class="bi bi-list"></i>
            </button>

            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-custom">
                    <li class="breadcrumb-item active">Dashboard Varejo</li>
                </ol>
            </nav>
        </div>

        <div class="header-right">
            <div class="dropdown">
                <button class="btn btn-link text-dark position-relative" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-bell" style="font-size: 1.25rem;"></i>
                    <span class="badge bg-danger position-absolute translate-middle rounded-pill" id="badgeNotificacoes" style="font-size: 0.6rem; top: 8px; right: 8px;">0</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><h6 class="dropdown-header">Alertas do Varejo</h6></li>
                    <li><a class="dropdown-item" href="#">Produtos com estoque baixo</a></li>
                    <li><a class="dropdown-item" href="#">Promocoes ativas</a></li>
                    <li><a class="dropdown-item" href="#">Pendencias financeiras</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-center" href="#">Ver todos</a></li>
                </ul>
            </div>

            <div class="dropdown user-dropdown">
                <div class="user-avatar" data-bs-toggle="dropdown" aria-expanded="false">
                    <?php echo strtoupper(substr($usuario['nome'] ?? 'U', 0, 1)); ?>
                </div>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><h6 class="dropdown-header user-name"><?php echo htmlspecialchars($usuario['nome'] ?? 'Usuario'); ?></h6></li>
                    <li><small class="dropdown-header text-muted user-email"><?php echo htmlspecialchars($usuario['email'] ?? ''); ?></small></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="?view=perfil"><i class="bi bi-person me-2"></i>Meu Perfil</a></li>
                    <li><a class="dropdown-item text-danger" href="#" id="logoutBtn"><i class="bi bi-box-arrow-right me-2"></i>Sair</a></li>
                </ul>
            </div>
        </div>
    </header>

    <div class="content-area">
        <div class="dashboard-header">
            <div>
                <h1 class="page-title">Dashboard Executivo - Varejo</h1>
                <p class="page-subtitle">Indicadores consolidados de vendas, estoque, compras e financeiro</p>
            </div>
            <div class="dashboard-filters">
                <select class="form-select" id="filtroFilial" style="min-width: 220px;">
                    <option value="">Todas as filiais</option>
                </select>
                <select class="form-select" id="filtroPeriodo" style="min-width: 180px;">
                    <option value="semanal">Ultimos 7 dias</option>
                    <option value="mensal" selected>Mes atual</option>
                    <option value="anual">Ano atual</option>
                    <option value="90dias">Ultimos 90 dias</option>
                </select>
                <button class="btn btn-primary" type="button" onclick="refreshDashboard()">
                    <i class="bi bi-arrow-clockwise me-2"></i>Atualizar
                </button>
            </div>
        </div>

        <div class="metrics-grid">
            <div class="metric-card">
                <div class="metric-icon"><i class="bi bi-cart-check"></i></div>
                <div class="metric-label">Vendas Hoje</div>
                <div class="metric-value" id="metricVendasDiaValor">-</div>
                <div class="metric-sub" id="metricVendasDiaQtd">-</div>
            </div>
            <div class="metric-card">
                <div class="metric-icon"><i class="bi bi-calendar2-week"></i></div>
                <div class="metric-label">Vendas do Mes</div>
                <div class="metric-value" id="metricVendasMesValor">-</div>
                <div class="metric-sub" id="metricVendasMesQtd">-</div>
            </div>
            <div class="metric-card">
                <div class="metric-icon"><i class="bi bi-graph-up"></i></div>
                <div class="metric-label">Ticket Medio</div>
                <div class="metric-value" id="metricTicketMedio">-</div>
                <div class="metric-sub">Periodo selecionado</div>
            </div>
            <div class="metric-card">
                <div class="metric-icon"><i class="bi bi-people"></i></div>
                <div class="metric-label">Clientes Ativos</div>
                <div class="metric-value" id="metricClientesAtivos">-</div>
                <div class="metric-sub">Base ativa</div>
            </div>
            <div class="metric-card">
                <div class="metric-icon"><i class="bi bi-box"></i></div>
                <div class="metric-label">Produtos Ativos</div>
                <div class="metric-value" id="metricProdutosAtivos">-</div>
                <div class="metric-sub">Catalogo</div>
            </div>
            <div class="metric-card">
                <div class="metric-icon"><i class="bi bi-exclamation-triangle"></i></div>
                <div class="metric-label">Estoque Baixo</div>
                <div class="metric-value" id="metricEstoqueBaixo">-</div>
                <div class="metric-sub">Itens criticos</div>
            </div>
            <div class="metric-card">
                <div class="metric-icon"><i class="bi bi-truck"></i></div>
                <div class="metric-label">Pendencias de Compra</div>
                <div class="metric-value" id="metricPendenciasCompra">-</div>
                <div class="metric-sub">Itens pendentes</div>
            </div>
            <div class="metric-card">
                <div class="metric-icon"><i class="bi bi-tags"></i></div>
                <div class="metric-label">Promocoes Ativas</div>
                <div class="metric-value" id="metricPromocoesAtivas">-</div>
                <div class="metric-sub">Campanhas vigentes</div>
            </div>
        </div>
        <div class="chart-grid">
            <div class="chart-card">
                <div class="chart-header">
                    <span class="chart-title">Contas em Aberto</span>
                    <button class="btn btn-sm btn-outline-secondary chart-expand" type="button" data-chart-expand>Expandir</button>
                </div>
                <div class="chart-body">
                    <canvas id="chartContasResumo"></canvas>
                </div>
            </div>
            <div class="chart-card">
                <div class="chart-header">
                    <span class="chart-title">Formas de Pagamento</span>
                    <button class="btn btn-sm btn-outline-secondary chart-expand" type="button" data-chart-expand>Expandir</button>
                </div>
                <div class="chart-body">
                    <canvas id="chartFormasPagamento"></canvas>
                </div>
            </div>
            <div class="chart-card">
                <div class="chart-header">
                    <span class="chart-title">Fluxo de Caixa Projetado</span>
                    <button class="btn btn-sm btn-outline-secondary chart-expand" type="button" data-chart-expand>Expandir</button>
                </div>
                <div class="chart-body">
                    <canvas id="chartFluxoCaixa"></canvas>
                </div>
            </div>
            <div class="chart-card">
                <div class="chart-header">
                    <span class="chart-title">Top Produtos</span>
                    <button class="btn btn-sm btn-outline-secondary chart-expand" type="button" data-chart-expand>Expandir</button>
                </div>
                <div class="chart-body">
                    <canvas id="chartTopProdutos"></canvas>
                </div>
            </div>
            <div class="chart-card">
                <div class="chart-header">
                    <span class="chart-title">Vendas Assistidas (Status)</span>
                    <button class="btn btn-sm btn-outline-secondary chart-expand" type="button" data-chart-expand>Expandir</button>
                </div>
                <div class="chart-body">
                    <canvas id="chartVendasAssistidas"></canvas>
                </div>
            </div>
        </div>

        <div class="table-container">
            <div class="table-title">Contas a Receber (Resumo)</div>
            <div class="table-responsive">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Quantidade</th>
                            <th>Saldo Devedor</th>
                            <th>Recebido</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody id="tabelaContasReceber">
                        <tr><td colspan="5" class="text-center text-muted">Carregando...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="table-container">
            <div class="table-title">Contas a Pagar (Resumo)</div>
            <div class="table-responsive">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Quantidade</th>
                            <th>Saldo Pendente</th>
                            <th>Pago</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody id="tabelaContasPagar">
                        <tr><td colspan="5" class="text-center text-muted">Carregando...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="table-container">
            <div class="table-title">Produtos com Estoque Critico</div>
            <div class="table-responsive">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Cod. Barras</th>
                            <th>Filial</th>
                            <th>Estoque</th>
                            <th>Minimo</th>
                            <th>Pendente</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="tabelaEstoqueCritico">
                        <tr><td colspan="7" class="text-center text-muted">Carregando...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="table-container">
            <div class="table-title">Pedidos de Compra Pendentes</div>
            <div class="table-responsive">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th>Pedido</th>
                            <th>Fornecedor</th>
                            <th>Data Pedido</th>
                            <th>Prev. Entrega</th>
                            <th>Itens Pend.</th>
                            <th>Valor Pend.</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="tabelaPedidosPendentes">
                        <tr><td colspan="7" class="text-center text-muted">Carregando...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="table-container">
            <div class="table-title">Entradas de Compra Recentes</div>
            <div class="table-responsive">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th>Entrada</th>
                            <th>Fornecedor</th>
                            <th>Data Entrada</th>
                            <th>Status</th>
                            <th>Itens</th>
                            <th>Valor</th>
                        </tr>
                    </thead>
                    <tbody id="tabelaEntradasRecentes">
                        <tr><td colspan="6" class="text-center text-muted">Carregando...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="table-container">
            <div class="table-title">Promocoes Ativas</div>
            <div class="table-responsive">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th>Promocao</th>
                            <th>Tipo</th>
                            <th>Inicio</th>
                            <th>Fim</th>
                            <th>Dias Rest.</th>
                            <th>Produtos</th>
                            <th>Desconto Medio</th>
                        </tr>
                    </thead>
                    <tbody id="tabelaPromocoesAtivas">
                        <tr><td colspan="7" class="text-center text-muted">Carregando...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="table-container">
            <div class="table-title">Produtos em Promocao</div>
            <div class="table-responsive">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th>Promocao</th>
                            <th>Produto</th>
                            <th>Codigo</th>
                            <th>Preco Normal</th>
                            <th>Preco Promo</th>
                            <th>Desconto</th>
                            <th>Margem</th>
                            <th>Fim</th>
                        </tr>
                    </thead>
                    <tbody id="tabelaProdutosPromocao">
                        <tr><td colspan="8" class="text-center text-muted">Carregando...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="table-container">
            <div class="table-title">Historico de Alteracoes de Preco</div>
            <div class="table-responsive">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Produto</th>
                            <th>Tipo</th>
                            <th>De</th>
                            <th>Para</th>
                            <th>Variacao</th>
                            <th>Usuario</th>
                            <th>Motivo</th>
                        </tr>
                    </thead>
                    <tbody id="tabelaHistoricoPrecos">
                        <tr><td colspan="8" class="text-center text-muted">Carregando...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="table-container">
            <div class="table-title">Top Clientes</div>
            <div class="table-responsive">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Total Compras</th>
                            <th>Valor Total</th>
                            <th>Ticket Medio</th>
                            <th>Ultima Compra</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="tabelaTopClientes">
                        <tr><td colspan="6" class="text-center text-muted">Carregando...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="table-container">
            <div class="table-title">Debitos de Clientes</div>
            <div class="table-responsive">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Telefone</th>
                            <th>Valor</th>
                            <th>Status</th>
                            <th>Geracao</th>
                            <th>Dias Atraso</th>
                        </tr>
                    </thead>
                    <tbody id="tabelaDebitosClientes">
                        <tr><td colspan="6" class="text-center text-muted">Carregando...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="table-container">
            <div class="table-title">Ultimas Movimentacoes de Estoque</div>
            <div class="table-responsive">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th>Data/Hora</th>
                            <th>Produto</th>
                            <th>Tipo</th>
                            <th>Origem</th>
                            <th>Qtd</th>
                            <th>Saldo Ant.</th>
                            <th>Saldo Atual</th>
                            <th>Usuario</th>
                        </tr>
                    </thead>
                    <tbody id="tabelaMovimentacoesRecentes">
                        <tr><td colspan="8" class="text-center text-muted">Carregando...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<script>
    const idEmpresa = <?php echo (int)($id_empresa ?? 0); ?>;
    const idFilialSessao = <?php echo $id_filial ? (int)$id_filial : 'null'; ?>;
    const token = '<?php echo addslashes($token); ?>';
    const BASE_URL = window.NEXUSFLOW_API_BASE_URL || '';

    const charts = {};
    let filtroAtual = 'mensal';
    let dataInicioPersonalizado = '';
    let dataFimPersonalizado = '';

    document.addEventListener('DOMContentLoaded', function() {
        configurarEventos();
        carregarFiliais();
        carregarDashboard();
        configurarExpansaoGraficos();

        const logoutBtn = document.getElementById('logoutBtn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', function(e) {
                e.preventDefault();
                fetch('logout.php', { method: 'POST' })
                    .then(() => { window.location.href = 'login.php'; });
            });
        }
    });

    function configurarEventos() {
        const filtroPeriodo = document.getElementById('filtroPeriodo');
        if (filtroPeriodo) {
            filtroPeriodo.addEventListener('change', function() {
                filtroAtual = this.value;
                if (filtroAtual === '90dias') {
                    const hoje = new Date();
                    const inicio = new Date();
                    inicio.setDate(hoje.getDate() - 89);
                    dataInicioPersonalizado = inicio.toISOString().slice(0, 10);
                    dataFimPersonalizado = hoje.toISOString().slice(0, 10);
                } else {
                    dataInicioPersonalizado = '';
                    dataFimPersonalizado = '';
                }
                carregarDashboard();
            });
        }

        const filtroFilial = document.getElementById('filtroFilial');
        if (filtroFilial) {
            filtroFilial.addEventListener('change', carregarDashboard);
        }
    }

    function configurarExpansaoGraficos() {
        document.querySelectorAll('[data-chart-expand]').forEach(btn => {
            btn.addEventListener('click', function() {
                const card = this.closest('.chart-card');
                if (!card) return;
                const expanded = card.classList.toggle('is-expanded');
                this.textContent = expanded ? 'Recolher' : 'Expandir';

                const canvas = card.querySelector('canvas');
                if (canvas && charts[canvas.id]) {
                    setTimeout(() => charts[canvas.id].resize(), 200);
                }
            });
        });
    }

    function refreshDashboard() {
        carregarDashboard();
    }

    async function carregarFiliais() {
        const select = document.getElementById('filtroFilial');
        if (!select || !idEmpresa) return;

        select.innerHTML = '<option value="">Todas as filiais</option>';

        try {
            const response = await fetch(`${BASE_URL}/api/filiais/empresa/${idEmpresa}`, {
                headers: getHeaders()
            });
            if (!response.ok) return;
            const data = await response.json();
            const lista = Array.isArray(data) ? data : (data.data || data.items || []);
            lista.forEach(filial => {
                const option = document.createElement('option');
                option.value = filial.id_filial ?? filial.id;
                option.textContent = filial.nome_filial ?? filial.nome ?? 'Filial';
                if (idFilialSessao && option.value == idFilialSessao) {
                    option.selected = true;
                }
                select.appendChild(option);
            });
        } catch (error) {
            console.error('Erro ao carregar filiais:', error);
        }
    }

    function getHeaders() {
        return {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
        };
    }

    function buildQuery(params) {
        const search = new URLSearchParams();
        Object.entries(params).forEach(([key, value]) => {
            if (value !== null && value !== undefined && value !== '') {
                search.append(key, value);
            }
        });
        return search.toString();
    }

    function getFiltroParams() {
        const filial = document.getElementById('filtroFilial')?.value || idFilialSessao || '';
        const params = {
            id_empresa: idEmpresa,
            id_filial: filial || undefined,
            filtro: filtroAtual
        };

        if (filtroAtual === '90dias') {
            params.filtro = 'personalizado';
            params.data_inicio = dataInicioPersonalizado;
            params.data_fim = dataFimPersonalizado;
        }
        return params;
    }

    async function apiGet(path, params = {}) {
        const query = buildQuery(params);
        const url = `${BASE_URL}${path}${query ? '?' + query : ''}`;
        const response = await fetch(url, { headers: getHeaders() });
        if (!response.ok) {
            throw new Error(`Erro ${response.status}`);
        }
        return response.json();
    }

    async function carregarDashboard() {
        if (!idEmpresa) {
            console.warn('id_empresa nao encontrado na sessao.');
            return;
        }

        const params = getFiltroParams();

        try {
            const [
                metricas,
                formasPagamento,
                fluxoCaixa,
                topProdutos,
                vendasAssistidas,
                contasReceber,
                contasPagar,
                estoqueCritico,
                pedidosPendentes,
                entradasRecentes,
                promocoesAtivas,
                produtosPromocao,
                historicoPrecos,
                topClientes,
                debitosClientes,
                movimentacoesRecentes
            ] = await Promise.all([
                apiGet('/api/v1/dashboard/exec/metricas', params),
                apiGet('/api/v1/dashboard/exec/formas-pagamento', params),
                apiGet('/api/v1/dashboard/exec/fluxo-caixa', { ...params, meses: 6 }),
                apiGet('/api/v1/dashboard/exec/top-produtos', { ...params, limit: 10 }),
                apiGet('/api/v1/dashboard/exec/vendas-assistidas-status', params),
                apiGet('/api/v1/dashboard/exec/contas-receber-resumo', params),
                apiGet('/api/v1/dashboard/exec/contas-pagar-resumo', params),
                apiGet('/api/v1/dashboard/exec/estoque-critico', params),
                apiGet('/api/v1/dashboard/exec/pedidos-pendentes', params),
                apiGet('/api/v1/dashboard/exec/entradas-recentes', { ...params, limit: 10 }),
                apiGet('/api/v1/dashboard/exec/promocoes-ativas', params),
                apiGet('/api/v1/dashboard/exec/produtos-promocao', params),
                apiGet('/api/v1/dashboard/exec/historico-precos', { ...params, limit: 10 }),
                apiGet('/api/v1/dashboard/exec/top-clientes', { ...params, limit: 10 }),
                apiGet('/api/v1/dashboard/exec/debitos-clientes', params),
                apiGet('/api/v1/dashboard/exec/movimentacoes-recentes', { ...params, limit: 20 })
            ]);

            atualizarMetricas(metricas.data || metricas);
            renderizarFormasPagamento(formasPagamento.data || []);
            renderizarFluxoCaixa(fluxoCaixa.data || []);
            renderizarTopProdutos(topProdutos.data || []);
            renderizarVendasAssistidas(vendasAssistidas.data || []);

            renderizarTabelaContas('tabelaContasReceber', contasReceber.data || [], 'receber');
            renderizarTabelaContas('tabelaContasPagar', contasPagar.data || [], 'pagar');
            renderizarContasResumo(contasReceber.data || [], contasPagar.data || []);
            renderizarEstoqueCritico(estoqueCritico.data || []);
            renderizarPedidosPendentes(pedidosPendentes.data || []);
            renderizarEntradasRecentes(entradasRecentes.data || []);
            renderizarPromocoesAtivas(promocoesAtivas.data || []);
            renderizarProdutosPromocao(produtosPromocao.data || []);
            renderizarHistoricoPrecos(historicoPrecos.data || []);
            renderizarTopClientes(topClientes.data || []);
            renderizarDebitosClientes(debitosClientes.data || []);
            renderizarMovimentacoesRecentes(movimentacoesRecentes.data || []);
        } catch (error) {
            console.error('Erro ao carregar dashboard:', error);
            if (window.nexusFlow?.showNotification) {
                nexusFlow.showNotification('Erro ao carregar dados do dashboard.', 'error');
            }
        }
    }
    function atualizarMetricas(data) {
        setText('metricVendasDiaValor', formatarMoeda(data.vendas_dia_valor));
        setText('metricVendasDiaQtd', `${data.vendas_dia_quantidade ?? 0} vendas`);
        setText('metricVendasMesValor', formatarMoeda(data.vendas_mes_valor));
        setText('metricVendasMesQtd', `${data.vendas_mes_quantidade ?? 0} vendas`);
        setText('metricTicketMedio', formatarMoeda(data.ticket_medio));
        setText('metricClientesAtivos', formatarNumero(data.clientes_ativos));
        setText('metricProdutosAtivos', formatarNumero(data.produtos_ativos));
        setText('metricEstoqueBaixo', formatarNumero(data.estoque_baixo));
        setText('metricPendenciasCompra', formatarNumero(data.total_pendencias_compra));
        setText('metricPromocoesAtivas', formatarNumero(data.promocoes_ativas));

        const badge = document.getElementById('badgeNotificacoes');
        if (badge) {
            const totalAlertas = (data.estoque_baixo || 0) + (data.promocoes_ativas || 0);
            badge.textContent = totalAlertas;
        }
    }

    function renderizarContasResumo(receberRows, pagarRows) {
        const totalReceber = receberRows.reduce((acc, row) => acc + Number(row.saldo_devedor || 0), 0);
        const totalPagar = pagarRows.reduce((acc, row) => acc + Number(row.saldo_pendente || 0), 0);

        renderChart('chartContasResumo', {
            type: 'doughnut',
            data: {
                labels: ['A Receber', 'A Pagar'],
                datasets: [{
                    data: [totalReceber, totalPagar],
                    backgroundColor: ['#22c55e', '#ef4444']
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    }

    function renderizarFormasPagamento(rows) {
        const labels = rows.map(r => r.forma_pagamento || 'Outro');
        const valores = rows.map(r => Number(r.valor_total || 0));

        renderChart('chartFormasPagamento', {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{
                    data: valores,
                    backgroundColor: ['#4f46e5', '#22c55e', '#f97316', '#e11d48', '#0ea5e9', '#a855f7']
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    }

    function renderizarFluxoCaixa(rows) {
        const labels = rows.map(r => r.mes);
        const receber = rows.map(r => Number(r.a_receber || 0));
        const pagar = rows.map(r => Number(r.a_pagar || 0));
        const saldo = rows.map(r => Number(r.saldo_projetado || 0));

        renderChart('chartFluxoCaixa', {
            type: 'line',
            data: {
                labels,
                datasets: [
                    { label: 'A Receber', data: receber, borderColor: '#22c55e', backgroundColor: 'rgba(34, 197, 94, 0.2)', fill: true },
                    { label: 'A Pagar', data: pagar, borderColor: '#ef4444', backgroundColor: 'rgba(239, 68, 68, 0.2)', fill: true },
                    { label: 'Saldo Projetado', data: saldo, borderColor: '#0f172a', backgroundColor: 'rgba(15, 23, 42, 0.1)', fill: false }
                ]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    }

    function renderizarTopProdutos(rows) {
        const labels = rows.map(r => r.produto || 'Produto');
        const valores = rows.map(r => Number(r.quantidade_vendida || 0));

        renderChart('chartTopProdutos', {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Quantidade vendida',
                    data: valores,
                    backgroundColor: '#f59e0b'
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    }

    function renderizarVendasAssistidas(rows) {
        const labels = rows.map(r => r.status || 'status');
        const valores = rows.map(r => Number(r.quantidade || 0));

        renderChart('chartVendasAssistidas', {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{
                    data: valores,
                    backgroundColor: ['#22c55e', '#f97316', '#e11d48', '#0ea5e9']
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    }

    function renderizarTabelaContas(id, rows, tipo) {
        const tbody = document.getElementById(id);
        if (!tbody) return;

        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Sem dados</td></tr>';
            return;
        }

        tbody.innerHTML = rows.map(row => `
            <tr>
                <td>${escapeHtml(row.status || '-')}</td>
                <td>${formatarNumero(row.quantidade)}</td>
                <td>${formatarMoeda(tipo === 'receber' ? row.saldo_devedor : row.saldo_pendente)}</td>
                <td>${formatarMoeda(tipo === 'receber' ? row.total_recebido : row.total_pago)}</td>
                <td>${formatarMoeda(row.total_geral)}</td>
            </tr>
        `).join('');
    }

    function renderizarEstoqueCritico(rows) {
        const tbody = document.getElementById('tabelaEstoqueCritico');
        if (!tbody) return;

        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Nenhum produto critico</td></tr>';
            return;
        }

        tbody.innerHTML = rows.map(item => {
            const statusClass = item.nivel_alerta?.includes('CRITICO') || item.nivel_alerta?.includes('URGENTE')
                ? 'status-danger'
                : (item.nivel_alerta?.includes('ATENCAO') ? 'status-warning' : 'status-success');
            return `
                <tr>
                    <td>${escapeHtml(item.produto)}</td>
                    <td>${escapeHtml(item.codigo_barras || '-')}</td>
                    <td>${escapeHtml(item.nome_filial || '-')}</td>
                    <td>${formatarNumero(item.estoque_atual)}</td>
                    <td>${formatarNumero(item.estoque_minimo)}</td>
                    <td>${formatarNumero(item.pendencia_compra)}</td>
                    <td><span class="status-pill ${statusClass}">${escapeHtml(item.nivel_alerta || '')}</span></td>
                </tr>
            `;
        }).join('');
    }

    function renderizarPedidosPendentes(rows) {
        const tbody = document.getElementById('tabelaPedidosPendentes');
        if (!tbody) return;

        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Nenhum pedido pendente</td></tr>';
            return;
        }

        tbody.innerHTML = rows.map(item => {
            const statusClass = item.status_entrega === 'ATRASADO'
                ? 'status-danger'
                : (item.status_entrega === 'PROXIMO' ? 'status-warning' : 'status-info');
            return `
                <tr>
                    <td>${escapeHtml(item.numero_pedido)}</td>
                    <td>${escapeHtml(item.fornecedor)}</td>
                    <td>${formatarData(item.data_pedido)}</td>
                    <td>${formatarData(item.data_previsao_entrega)}</td>
                    <td>${formatarNumero(item.itens_pendentes)}</td>
                    <td>${formatarMoeda(item.valor_pendente)}</td>
                    <td><span class="status-pill ${statusClass}">${escapeHtml(item.status_entrega)}</span></td>
                </tr>
            `;
        }).join('');
    }

    function renderizarEntradasRecentes(rows) {
        const tbody = document.getElementById('tabelaEntradasRecentes');
        if (!tbody) return;

        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Nenhuma entrada recente</td></tr>';
            return;
        }

        tbody.innerHTML = rows.map(item => `
            <tr>
                <td>${escapeHtml(item.numero_entrada)}</td>
                <td>${escapeHtml(item.fornecedor)}</td>
                <td>${formatarData(item.data_entrada)}</td>
                <td>${escapeHtml(item.status || '-')}</td>
                <td>${formatarNumero(item.itens_recebidos)}</td>
                <td>${formatarMoeda(item.valor_total)}</td>
            </tr>
        `).join('');
    }

    function renderizarPromocoesAtivas(rows) {
        const tbody = document.getElementById('tabelaPromocoesAtivas');
        if (!tbody) return;

        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Nenhuma promocao ativa</td></tr>';
            return;
        }

        tbody.innerHTML = rows.map(item => `
            <tr>
                <td><strong>${escapeHtml(item.nome_promocao)}</strong><br><small class="text-muted">${escapeHtml(item.codigo_promocao)}</small></td>
                <td>${escapeHtml(item.tipo_promocao)}</td>
                <td>${formatarDataHora(item.data_inicio)}</td>
                <td>${formatarDataHora(item.data_fim)}</td>
                <td>${escapeHtml(item.dias_restantes)} dias</td>
                <td>${formatarNumero(item.total_produtos)}</td>
                <td>${formatarNumero(item.desconto_medio)}%</td>
            </tr>
        `).join('');
    }

    function renderizarProdutosPromocao(rows) {
        const tbody = document.getElementById('tabelaProdutosPromocao');
        if (!tbody) return;

        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">Nenhum produto em promocao</td></tr>';
            return;
        }

        tbody.innerHTML = rows.map(item => `
            <tr>
                <td>${escapeHtml(item.nome_promocao)}</td>
                <td>${escapeHtml(item.produto)}</td>
                <td>${escapeHtml(item.codigo_barras || '-')}</td>
                <td>${formatarMoeda(item.preco_normal)}</td>
                <td>${formatarMoeda(item.preco_promocional)}</td>
                <td>${formatarNumero(item.desconto_percentual)}%</td>
                <td>${formatarNumero(item.margem_promocional)}%</td>
                <td>${formatarData(item.data_fim)}</td>
            </tr>
        `).join('');
    }

    function renderizarHistoricoPrecos(rows) {
        const tbody = document.getElementById('tabelaHistoricoPrecos');
        if (!tbody) return;

        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">Sem alteracoes recentes</td></tr>';
            return;
        }

        tbody.innerHTML = rows.map(item => `
            <tr>
                <td>${formatarDataHora(item.data_alteracao)}</td>
                <td>${escapeHtml(item.produto)}</td>
                <td>${escapeHtml(item.tipo_alteracao)}</td>
                <td>${formatarMoeda(item.preco_anterior)}</td>
                <td>${formatarMoeda(item.preco_novo)}</td>
                <td>${escapeHtml(item.variacao || '-')}</td>
                <td>${escapeHtml(item.usuario || '-')}</td>
                <td>${escapeHtml(item.motivo || '-')}</td>
            </tr>
        `).join('');
    }

    function renderizarTopClientes(rows) {
        const tbody = document.getElementById('tabelaTopClientes');
        if (!tbody) return;

        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Nenhum cliente no periodo</td></tr>';
            return;
        }

        tbody.innerHTML = rows.map(item => `
            <tr>
                <td>${escapeHtml(item.nome_cliente)}</td>
                <td>${formatarNumero(item.total_compras)}</td>
                <td>${formatarMoeda(item.valor_total_gasto)}</td>
                <td>${formatarMoeda(item.ticket_medio)}</td>
                <td>${formatarData(item.ultima_compra)}</td>
                <td>${escapeHtml(item.status_cliente)}</td>
            </tr>
        `).join('');
    }

    function renderizarDebitosClientes(rows) {
        const tbody = document.getElementById('tabelaDebitosClientes');
        if (!tbody) return;

        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Sem debitos pendentes</td></tr>';
            return;
        }

        tbody.innerHTML = rows.map(item => `
            <tr>
                <td>${escapeHtml(item.nome_cliente)}</td>
                <td>${escapeHtml(item.telefone || '-')}</td>
                <td>${formatarMoeda(item.valor)}</td>
                <td>${escapeHtml(item.status)}</td>
                <td>${formatarData(item.data_geracao)}</td>
                <td>${formatarNumero(item.dias_em_atraso)}</td>
            </tr>
        `).join('');
    }

    function renderizarMovimentacoesRecentes(rows) {
        const tbody = document.getElementById('tabelaMovimentacoesRecentes');
        if (!tbody) return;

        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">Sem movimentacoes recentes</td></tr>';
            return;
        }

        tbody.innerHTML = rows.map(item => {
            const tipoClass = item.tipo_movimentacao === 'entrada' ? 'status-success' : 'status-danger';
            return `
                <tr>
                    <td>${formatarDataHora(item.data_movimentacao)}</td>
                    <td>${escapeHtml(item.produto)}</td>
                    <td><span class="status-pill ${tipoClass}">${escapeHtml(item.tipo_movimentacao)}</span></td>
                    <td>${escapeHtml(item.origem || '-')}</td>
                    <td>${formatarNumero(item.quantidade)}</td>
                    <td>${formatarNumero(item.saldo_anterior)}</td>
                    <td>${formatarNumero(item.saldo_atual)}</td>
                    <td>${escapeHtml(item.usuario || '-')}</td>
                </tr>
            `;
        }).join('');
    }

    function renderChart(id, config) {
        const canvas = document.getElementById(id);
        if (!canvas) return;
        const ctx = canvas.getContext('2d');

        if (charts[id]) {
            charts[id].destroy();
        }

        charts[id] = new Chart(ctx, config);
    }

    function setText(id, value) {
        const el = document.getElementById(id);
        if (el) {
            el.textContent = value ?? '-';
        }
    }

    function formatarMoeda(valor) {
        const numero = Number(valor || 0);
        return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(numero);
    }

    function formatarNumero(valor) {
        const numero = Number(valor || 0);
        return new Intl.NumberFormat('pt-BR').format(numero);
    }

    function formatarData(valor) {
        if (!valor) return '-';
        const date = new Date(valor);
        if (Number.isNaN(date.getTime())) return valor;
        return date.toLocaleDateString('pt-BR');
    }

    function formatarDataHora(valor) {
        if (!valor) return '-';
        const date = new Date(valor);
        if (Number.isNaN(date.getTime())) return valor;
        return date.toLocaleString('pt-BR');
    }

    function escapeHtml(text) {
        if (text === null || text === undefined) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/\"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }
</script>
