<?php
$config = require __DIR__ . '/../funcoes/config.php';

session_start();
if (!isset($_SESSION['authToken'], $_SESSION['usuario'], $_SESSION['empresa'], $_SESSION['licenca'])) {
    header('Location: login.php');
    exit;
}

$usuario = is_array($_SESSION['usuario']) ? $_SESSION['usuario'] : ['nome' => $_SESSION['usuario']];
$empresa = $_SESSION['empresa'];
$id_empresa = $_SESSION['empresa_id'];
$licenca = $_SESSION['licenca'];
$token = $_SESSION['authToken'];
$segmento = $_SESSION['segmento'] ?? '';

$nomeUsuario = '';
if (is_array($usuario)) {
    $nomeUsuario = $usuario['nome'] ?? $usuario['name'] ?? 'Usuario';
} else {
    $nomeUsuario = (string)$usuario;
}

$inicialUsuario = strtoupper(substr($nomeUsuario, 0, 1));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?php if (!defined('APP_SHELL')) { $pageTitle = 'Auditorias de Estoque'; include __DIR__ . '/../components/app-head.php'; } ?>
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

        .table-custom th {
            border-top: none;
            font-weight: 600;
            color: #6c757d;
            font-size: 0.85rem;
            text-transform: uppercase;
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

        .summary-card {
            border-left: 4px solid var(--primary-color);
        }

        .summary-card .summary-label {
            font-size: 0.85rem;
            color: #6c757d;
        }

        .summary-card .summary-value {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--secondary-color);
        }

        .movement-entry { border-left: 3px solid #28a745; }
        .movement-exit { border-left: 3px solid #dc3545; }
        .movement-transfer { border-left: 3px solid #0d6efd; }
        .movement-adjustment { border-left: 3px solid #ffc107; }

        .badge-tipo {
            font-size: 0.75rem;
            font-weight: 600;
        }

        .analitico-header {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 16px;
        }

        .analitico-meta {
            font-size: 0.9rem;
            color: #6c757d;
        }

        .analitico-table th {
            white-space: nowrap;
        }

        .analitico-table td {
            vertical-align: top;
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
        <header class="main-header">
            <div class="header-left">
                <button class="sidebar-toggle" type="button">
                    <i class="bi bi-list"></i>
                </button>

                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-custom">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item">Gestao de Estoque</li>
                        <li class="breadcrumb-item active">Auditorias</li>
                    </ol>
                </nav>
            </div>

            <div class="header-right">
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
                        <li><a class="dropdown-item text-danger" href="#" id="logoutBtn"><i class="bi bi-box-arrow-right me-2"></i>Sair</a></li>
                    </ul>
                </div>
            </div>
        </header>

        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h2 class="page-title"><i class="bi bi-clipboard-check me-2 text-primary"></i>Auditorias de Estoque (Kardex)</h2>
                <p class="page-subtitle">Acompanhe movimentacoes e saldos por periodo.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <button class="btn btn-outline-secondary" type="button" onclick="abrirRelatorioAnalitico()">
                    <i class="bi bi-table me-2"></i>Relatorio Analitico
                </button>
                <button class="btn btn-outline-primary" type="button" onclick="exportarRelatorioAnalitico()">
                    <i class="bi bi-download me-2"></i>Exportar
                </button>
                <button class="btn btn-primary" type="button" onclick="gerarRelatorio()">
                    <i class="bi bi-search me-2"></i>Gerar Relatorio
                </button>
            </div>
        </div>

        <div class="card card-custom mb-4">
            <div class="card-header-custom">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-funnel text-primary"></i>
                    <strong>Filtros do Kardex</strong>
                </div>
            </div>
            <div class="card-body">
                <form id="formFiltrosKardex">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Filial *</label>
                            <select class="form-select" id="filtroFilial" required>
                                <option value="">Carregando filiais...</option>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Produto *</label>
                            <select class="form-select" id="filtroProduto" required>
                                <option value="">Carregando produtos...</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Periodo</label>
                            <div class="input-group">
                                <input type="date" class="form-control" id="filtroDataInicio" placeholder="Data inicio">
                                <span class="input-group-text">ate</span>
                                <input type="date" class="form-control" id="filtroDataFim" placeholder="Data fim">
                            </div>
                        </div>
                        <div class="col-12 text-muted small">
                            Se nao informar datas, o sistema considera todo o historico do produto.
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card card-custom summary-card">
                    <div class="card-body">
                        <div class="summary-label">Saldo Inicial</div>
                        <div class="summary-value" id="resumoSaldoInicial">-</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-custom summary-card" style="border-left-color: var(--success-color);">
                    <div class="card-body">
                        <div class="summary-label">Entradas</div>
                        <div class="summary-value" id="resumoEntradas">-</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-custom summary-card" style="border-left-color: var(--danger-color);">
                    <div class="card-body">
                        <div class="summary-label">Saidas</div>
                        <div class="summary-value" id="resumoSaidas">-</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-custom summary-card" style="border-left-color: var(--info-color);">
                    <div class="card-body">
                        <div class="summary-label">Saldo Final</div>
                        <div class="summary-value" id="resumoSaldoFinal">-</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card card-custom summary-card">
                    <div class="card-body">
                        <div class="summary-label">Custo Medio</div>
                        <div class="summary-value" id="resumoCustoMedio">-</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-custom summary-card" style="border-left-color: var(--warning-color);">
                    <div class="card-body">
                        <div class="summary-label">Valor Total</div>
                        <div class="summary-value" id="resumoValorTotal">-</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-custom summary-card" style="border-left-color: var(--secondary-color);">
                    <div class="card-body">
                        <div class="summary-label">Movimentacoes</div>
                        <div class="summary-value" id="resumoMovimentacoes">0</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card card-custom">
            <div class="card-header-custom d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <strong>Detalhamento Kardex</strong>
                    <div class="text-muted small" id="kardexContexto">Selecione filial e produto para gerar o relatorio.</div>
                </div>
                <button class="btn btn-sm btn-outline-primary" onclick="gerarRelatorio()">
                    <i class="bi bi-arrow-clockwise me-1"></i>Atualizar
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-custom">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Tipo</th>
                                <th>Referencia</th>
                                <th>Quantidade</th>
                                <th>Custo Unitario</th>
                                <th>Saldo</th>
                                <th>Valor</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyKardex">
                            <tr>
                                <td colspan="7" class="text-center text-muted">Nenhum dado carregado.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <div class="modal fade" id="modalAnalitico" tabindex="-1">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title">Relatorio Analitico - Kardex</h5>
                        <small class="text-muted" id="analiticoTitulo"></small>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary btn-sm" type="button" onclick="exportarRelatorioAnalitico()">
                            <i class="bi bi-download me-1"></i>Exportar Excel
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" type="button" onclick="gerarPdfAnalitico()">
                            <i class="bi bi-printer me-1"></i>Gerar PDF
                        </button>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="analitico-header mb-3">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <div class="analitico-meta">Empresa</div>
                                <strong id="analiticoEmpresa">-</strong>
                            </div>
                            <div class="col-md-4">
                                <div class="analitico-meta">Filial</div>
                                <strong id="analiticoFilial">-</strong>
                            </div>
                            <div class="col-md-4">
                                <div class="analitico-meta">Produto</div>
                                <strong id="analiticoProduto">-</strong>
                            </div>
                            <div class="col-md-4">
                                <div class="analitico-meta">Codigo de Barras</div>
                                <strong id="analiticoCodigoBarras">-</strong>
                            </div>
                            <div class="col-md-4">
                                <div class="analitico-meta">Unidade</div>
                                <strong id="analiticoUnidade">-</strong>
                            </div>
                            <div class="col-md-4">
                                <div class="analitico-meta">Periodo</div>
                                <strong id="analiticoPeriodo">-</strong>
                            </div>
                            <div class="col-md-4">
                                <div class="analitico-meta">Saldo Anterior</div>
                                <strong id="analiticoSaldoAnterior">-</strong>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover table-custom analitico-table">
                            <thead>
                                <tr>
                                    <th>Data/Hora</th>
                                    <th>Tipo</th>
                                    <th>Origem</th>
                                    <th>Documento</th>
                                    <th>Entrada</th>
                                    <th>Saida</th>
                                    <th>Saldo</th>
                                    <th>Custo Unitario</th>
                                    <th>Valor Total Estoque</th>
                                    <th>Usuario</th>
                                    <th>Observacao</th>
                                    <th>Detalhes</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyAnalitico">
                                <tr>
                                    <td colspan="12" class="text-center text-muted">Nenhum dado carregado.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="loading-overlay d-none" id="loadingOverlay">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Carregando...</span>
        </div>
    </div>

    <script>
        const idEmpresa = <?php echo $id_empresa; ?>;
        const token = '<?php echo $token; ?>';
        const BASE_URL = '<?= addslashes($config['api_base']) ?>';

        let filiais = [];
        let produtos = [];
        let kardexMovimentos = [];
        let analiticoCache = { key: '', data: null };
        let modalAnalitico = null;

        const API_CONFIG = {
            BASE_URL: BASE_URL,
            KARDEX: '/api/estoque/relatorios/kardex',
            KARDEX_RESUMO: '/api/estoque/relatorios/kardex/resumo',
            KARDEX_EXPORT: '/api/estoque/relatorios/kardex/export',
            FILIAIS_EMPRESA: '/api/filiais/empresa',
            PRODUTOS_EMPRESA: '/api/v1/produtos/empresa',
            PRODUTOS_EMPRESA_ALT: '/api/v1/empresas',
            LOGOUT: '/api/v1/logout',

            getHeaders: function() {
                return {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json',
                    'X-ID-EMPRESA': idEmpresa.toString()
                };
            }
        };

        document.addEventListener('DOMContentLoaded', function() {
            modalAnalitico = new bootstrap.Modal(document.getElementById('modalAnalitico'));
            carregarFiliais();
            carregarProdutos();

            const logoutBtn = document.getElementById('logoutBtn');
            if (logoutBtn) {
                logoutBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    fazerLogoff();
                });
            }
        });

        async function fazerLogoff() {
            try {
                await fetch(API_CONFIG.BASE_URL + API_CONFIG.LOGOUT, {
                    method: 'POST',
                    headers: API_CONFIG.getHeaders()
                });
            } catch (error) {
                console.error('Erro no logout:', error);
            } finally {
                window.location.href = 'login.php';
            }
        }

        function mostrarLoading(mostrar) {
            document.getElementById('loadingOverlay').classList.toggle('d-none', !mostrar);
        }

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
        async function carregarFiliais() {
            const select = document.getElementById('filtroFilial');
            if (!select) return;

            select.innerHTML = '<option value="">Carregando filiais...</option>';
            try {
                const response = await fetch(`${API_CONFIG.BASE_URL}${API_CONFIG.FILIAIS_EMPRESA}/${idEmpresa}`, {
                    method: 'GET',
                    headers: API_CONFIG.getHeaders()
                });

                if (!response.ok) {
                    throw new Error(`Erro ${response.status}: ${response.statusText}`);
                }

                const data = await response.json();
                filiais = normalizarLista(data);
                preencherSelectFiliais();
            } catch (error) {
                console.error('Erro ao carregar filiais:', error);
                select.innerHTML = '<option value="">Erro ao carregar filiais</option>';
                mostrarNotificacao('Erro ao carregar filiais: ' + error.message, 'error');
            }
        }

        async function carregarProdutos() {
            const select = document.getElementById('filtroProduto');
            if (!select) return;

            select.innerHTML = '<option value="">Carregando produtos...</option>';

            const urls = [
                `${API_CONFIG.BASE_URL}${API_CONFIG.PRODUTOS_EMPRESA}/${idEmpresa}`,
                `${API_CONFIG.BASE_URL}${API_CONFIG.PRODUTOS_EMPRESA_ALT}/${idEmpresa}/produtos`
            ];

            for (const url of urls) {
                try {
                    const response = await fetch(url, {
                        method: 'GET',
                        headers: API_CONFIG.getHeaders()
                    });

                    if (!response.ok) {
                        continue;
                    }

                    const data = await parseJsonResponse(response);
                    if (!data) {
                        continue;
                    }

                    produtos = normalizarListaProdutos(data).map(normalizarProduto).filter(p => p.id_produto);
                    if (produtos.length > 0) {
                        preencherSelectProdutos();
                        return;
                    }
                } catch (error) {
                    console.error('Erro ao carregar produtos:', error);
                }
            }

            select.innerHTML = '<option value="">Nenhum produto encontrado</option>';
        }

        async function parseJsonResponse(response) {
            try {
                return await response.json();
            } catch (error) {
                console.error('Erro ao interpretar JSON:', error);
                return null;
            }
        }

        function preencherSelectFiliais() {
            const select = document.getElementById('filtroFilial');
            if (!select) return;

            if (!Array.isArray(filiais) || filiais.length === 0) {
                select.innerHTML = '<option value="">Nenhuma filial encontrada</option>';
                return;
            }

            select.innerHTML = '<option value="">Selecione a filial</option>';
            filiais.forEach(filial => {
                const id = filial.id_filial ?? filial.id ?? null;
                if (!id) return;
                const nome = filial.nome_filial ?? filial.nome ?? `Filial #${id}`;
                const cidade = filial.cidade ?? '';
                const estado = filial.estado ?? '';
                const local = cidade && estado ? ` - ${cidade}/${estado}` : (cidade ? ` - ${cidade}` : '');

                const option = document.createElement('option');
                option.value = id;
                option.textContent = `${nome}${local}`;
                select.appendChild(option);
            });
        }

        function preencherSelectProdutos() {
            const select = document.getElementById('filtroProduto');
            if (!select) return;

            if (!Array.isArray(produtos) || produtos.length === 0) {
                select.innerHTML = '<option value="">Nenhum produto encontrado</option>';
                return;
            }

            select.innerHTML = '<option value="">Selecione o produto</option>';
            produtos.forEach(produto => {
                const option = document.createElement('option');
                option.value = produto.id_produto;
                option.textContent = `${produto.descricao} (#${produto.id_produto})`;
                select.appendChild(option);
            });
        }

        function normalizarLista(data) {
            if (!data) return [];
            if (Array.isArray(data)) return data;
            if (data.data && Array.isArray(data.data)) return data.data;
            if (data.data && data.data.data && Array.isArray(data.data.data)) return data.data.data;
            if (data.success && data.data && Array.isArray(data.data)) return data.data;
            if (data.success && data.data && data.data.data && Array.isArray(data.data.data)) return data.data.data;
            if (data.items && Array.isArray(data.items)) return data.items;
            if (data.data && data.data.items && Array.isArray(data.data.items)) return data.data.items;
            if (data.produtos && Array.isArray(data.produtos)) return data.produtos;
            if (data.data && data.data.produtos && Array.isArray(data.data.produtos)) return data.data.produtos;
            const maybeArray = Object.values(data).find(v => Array.isArray(v));
            return maybeArray || [];
        }

        function normalizarListaProdutos(data) {
            const lista = normalizarLista(data);
            if (Array.isArray(lista) && lista.length > 0) return lista;

            if (data && data.data && data.data.items && Array.isArray(data.data.items)) {
                return data.data.items;
            }

            if (data && data.data && data.data.produtos && Array.isArray(data.data.produtos)) {
                return data.data.produtos;
            }

            return Array.isArray(data) ? data : [];
        }

        function normalizarProduto(item) {
            return {
                id_produto: item.id_produto ?? item.id ?? null,
                descricao: item.descricao ?? item.nome ?? '',
                unidade_medida: item.unidade_medida ?? item.unidade ?? ''
            };
        }
        function obterFiltros() {
            return {
                id_empresa: idEmpresa,
                id_filial: document.getElementById('filtroFilial').value,
                id_produto: document.getElementById('filtroProduto').value,
                data_inicio: document.getElementById('filtroDataInicio').value,
                data_fim: document.getElementById('filtroDataFim').value
            };
        }

        function obterTextoSelecionado(selectId) {
            const select = document.getElementById(selectId);
            if (!select) return '';
            const option = select.selectedOptions && select.selectedOptions[0];
            return option ? option.textContent.trim() : '';
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

        async function gerarRelatorio() {
            const filtros = obterFiltros();
            if (!filtros.id_filial || !filtros.id_produto) {
                mostrarNotificacao('Selecione a filial e o produto.', 'warning');
                return;
            }

            mostrarLoading(true);
            try {
                const query = buildQuery(filtros);
                const [detalheResp, resumoResp] = await Promise.all([
                    fetch(`${API_CONFIG.BASE_URL}${API_CONFIG.KARDEX}?${query}`, {
                        method: 'GET',
                        headers: API_CONFIG.getHeaders()
                    }),
                    fetch(`${API_CONFIG.BASE_URL}${API_CONFIG.KARDEX_RESUMO}?${query}`, {
                        method: 'GET',
                        headers: API_CONFIG.getHeaders()
                    })
                ]);

                if (!detalheResp.ok) {
                    throw new Error(`Erro ${detalheResp.status}: ${detalheResp.statusText}`);
                }

                const detalheData = await detalheResp.json();
                const resumoData = resumoResp.ok ? await resumoResp.json() : null;

                const detalheNormalizado = normalizarKardexDetalhado(detalheData);
                kardexMovimentos = detalheNormalizado.movimentacoes;
                renderizarMovimentacoes(detalheNormalizado);
                renderizarResumo(normalizarResumo(resumoData, detalheNormalizado));
            } catch (error) {
                console.error('Erro ao gerar relatorio:', error);
                mostrarNotificacao('Erro ao gerar relatorio: ' + error.message, 'error');
                renderizarMovimentacoes({ movimentacoes: [] });
            } finally {
                mostrarLoading(false);
            }
        }

        function abrirRelatorioAnalitico() {
            const filtros = obterFiltros();
            if (!filtros.id_filial || !filtros.id_produto) {
                mostrarNotificacao('Selecione a filial e o produto.', 'warning');
                return;
            }

            if (modalAnalitico) {
                modalAnalitico.show();
            }

            carregarRelatorioAnalitico(filtros);
        }

        function getFiltroKey(filtros) {
            return `${filtros.id_filial}|${filtros.id_produto}|${filtros.data_inicio || ''}|${filtros.data_fim || ''}`;
        }

        async function carregarRelatorioAnalitico(filtros) {
            const key = getFiltroKey(filtros);
            if (analiticoCache.key === key && analiticoCache.data) {
                renderizarRelatorioAnalitico(analiticoCache.data);
                return;
            }

            mostrarLoading(true);
            try {
                const query = buildQuery(filtros);
                const response = await fetch(`${API_CONFIG.BASE_URL}${API_CONFIG.KARDEX}?${query}`, {
                    method: 'GET',
                    headers: API_CONFIG.getHeaders()
                });

                if (!response.ok) {
                    throw new Error(`Erro ${response.status}: ${response.statusText}`);
                }

                const data = await response.json();
                const analitico = normalizarKardexAnalitico(data, filtros);
                analiticoCache = { key, data: analitico };
                renderizarRelatorioAnalitico(analitico);
            } catch (error) {
                console.error('Erro ao carregar relatorio analitico:', error);
                mostrarNotificacao('Erro ao carregar relatorio analitico: ' + error.message, 'error');
                renderizarRelatorioAnalitico({ header: {}, movimentacoes: [] });
            } finally {
                mostrarLoading(false);
            }
        }

        function normalizarKardexAnalitico(data, filtros) {
            let origem = data ?? {};
            if (origem.success && origem.data) {
                origem = origem.data;
            } else if (origem.data && origem.data.data) {
                origem = origem.data.data;
            } else if (origem.data) {
                origem = origem.data;
            }

            let movimentacoes = origem.movimentacoes ?? origem.lancamentos ?? origem.itens ?? origem.data ?? origem;
            if (!Array.isArray(movimentacoes)) {
                movimentacoes = [];
            }

            const primeira = movimentacoes[0] ?? {};
            const header = {
                nome_empresa: origem.nome_empresa ?? primeira.nome_empresa ?? '',
                nome_filial: origem.nome_filial ?? primeira.nome_filial ?? '',
                id_produto: origem.id_produto ?? primeira.id_produto ?? filtros.id_produto ?? '',
                nome_produto: origem.nome_produto ?? origem.produto?.descricao ?? origem.produto?.nome ?? primeira.nome_produto ?? primeira.nome_produto ?? '',
                codigo_barras: origem.codigo_barras ?? primeira.codigo_barras ?? '',
                unidade_medida: origem.unidade_medida ?? primeira.unidade_medida ?? '',
                data_inicio: origem.data_inicio ?? primeira.data_inicio ?? filtros.data_inicio ?? '',
                data_fim: origem.data_fim ?? primeira.data_fim ?? filtros.data_fim ?? '',
                saldo_anterior: origem.saldo_anterior ?? primeira.saldo_anterior ?? null
            };

            return {
                header,
                movimentacoes: movimentacoes.map(normalizarMovimentacaoAnalitica)
            };
        }

        function normalizarMovimentacaoAnalitica(item) {
            const tipoRaw = item.tipo_movimento ?? item.tipo_movimentacao ?? item.tipo ?? '';
            const tipo = String(tipoRaw).toLowerCase();
            const quantidade = parseFloat(item.quantidade ?? item.qtd ?? 0);

            const entrada = item.quantidade_entrada ?? (tipo === 'entrada' || tipo === 'ajuste' ? quantidade : 0);
            const saida = item.quantidade_saida ?? (tipo === 'saida' || tipo === 'transferencia' ? quantidade : 0);
            const saldo = item.saldo_atual ?? item.saldo ?? item.estoque_atual ?? null;
            const custo = item.custo_unitario ?? item.custo ?? item.preco_unitario ?? 0;
            const valorTotal = item.valor_total_estoque ?? (saldo !== null ? parseFloat(saldo) * parseFloat(custo || 0) : null);

            return {
                data_hora: item.data_hora ?? item.data_movimentacao ?? item.data ?? item.created_at ?? '',
                tipo_movimento: (item.tipo_movimento ?? item.tipo_movimentacao ?? item.tipo ?? '').toString(),
                origem: item.origem ?? '',
                documento: item.documento ?? item.referencia ?? item.id_referencia ?? item.observacao ?? '',
                quantidade_entrada: entrada,
                quantidade_saida: saida,
                saldo_atual: saldo,
                custo_unitario: custo,
                valor_total_estoque: valorTotal,
                usuario_responsavel: item.usuario_responsavel ?? item.usuario ?? '',
                observacao: item.observacao ?? '',
                detalhes_adicionais: item.detalhes_adicionais ?? item.detalhes ?? ''
            };
        }

        function renderizarRelatorioAnalitico(analitico) {
            const header = analitico.header ?? {};
            const tbody = document.getElementById('tbodyAnalitico');

            const setText = (id, value) => {
                const el = document.getElementById(id);
                if (!el) return;
                el.textContent = value || '-';
            };

            const filialSelecionada = obterTextoSelecionado('filtroFilial');
            const produtoSelecionado = obterTextoSelecionado('filtroProduto');

            setText('analiticoEmpresa', header.nome_empresa);
            setText('analiticoFilial', header.nome_filial || filialSelecionada);
            setText('analiticoProduto', header.nome_produto || produtoSelecionado || (header.id_produto ? `#${header.id_produto}` : '-'));
            setText('analiticoCodigoBarras', header.codigo_barras);
            setText('analiticoUnidade', header.unidade_medida);

            const periodo = header.data_inicio || header.data_fim
                ? `${header.data_inicio || '-'} ate ${header.data_fim || '-'}`.trim()
                : 'Todo o periodo';
            setText('analiticoPeriodo', periodo);
            setText('analiticoSaldoAnterior', header.saldo_anterior !== null && header.saldo_anterior !== undefined ? formatarNumero(header.saldo_anterior) : '-');

            const titulo = document.getElementById('analiticoTitulo');
            if (titulo) {
                const produto = header.nome_produto || produtoSelecionado || (header.id_produto ? `Produto #${header.id_produto}` : '');
                const filial = header.nome_filial || filialSelecionada ? ` - ${header.nome_filial || filialSelecionada}` : '';
                titulo.textContent = `${produto}${filial}`.trim();
            }

            if (!tbody) return;
            const movimentos = analitico.movimentacoes ?? [];
            if (!Array.isArray(movimentos) || movimentos.length === 0) {
                tbody.innerHTML = '<tr><td colspan="12" class="text-center text-muted">Nenhuma movimentacao encontrada.</td></tr>';
                return;
            }

            tbody.innerHTML = movimentos.map(item => {
                return `
                    <tr>
                        <td>${formatarDataHora(item.data_hora)}</td>
                        <td>${escapeHtml(item.tipo_movimento)}</td>
                        <td>${escapeHtml(item.origem)}</td>
                        <td>${escapeHtml(item.documento)}</td>
                        <td>${formatarNumero(item.quantidade_entrada)}</td>
                        <td>${formatarNumero(item.quantidade_saida)}</td>
                        <td>${item.saldo_atual ?? '-'}</td>
                        <td>${formatarMoeda(item.custo_unitario)}</td>
                        <td>${item.valor_total_estoque !== null && item.valor_total_estoque !== undefined ? formatarMoeda(item.valor_total_estoque) : '-'}</td>
                        <td>${escapeHtml(item.usuario_responsavel)}</td>
                        <td>${escapeHtml(item.observacao)}</td>
                        <td>${escapeHtml(item.detalhes_adicionais)}</td>
                    </tr>
                `;
            }).join('');
        }

        function gerarPdfAnalitico() {
            if (!analiticoCache.data) {
                mostrarNotificacao('Nenhum relatorio analitico carregado.', 'warning');
                return;
            }

            const html = buildAnaliticoPrintHtml(analiticoCache.data);
            const printWindow = window.open('', '_blank');
            if (!printWindow) {
                mostrarNotificacao('Nao foi possivel abrir a janela de impressao.', 'warning');
                return;
            }

            printWindow.document.open();
            printWindow.document.write(html);
            printWindow.document.close();
            printWindow.focus();
            printWindow.onload = () => {
                printWindow.print();
            };
        }

        function buildAnaliticoPrintHtml(analitico) {
            const header = analitico.header ?? {};
            const movimentos = analitico.movimentacoes ?? [];
            const periodo = header.data_inicio || header.data_fim
                ? `${header.data_inicio || '-'} ate ${header.data_fim || '-'}`.trim()
                : 'Todo o periodo';

            const rows = movimentos.map(item => `
                <tr>
                    <td>${formatarDataHora(item.data_hora)}</td>
                    <td>${escapeHtml(item.tipo_movimento)}</td>
                    <td>${escapeHtml(item.origem)}</td>
                    <td>${escapeHtml(item.documento)}</td>
                    <td style="text-align:right;">${formatarNumero(item.quantidade_entrada)}</td>
                    <td style="text-align:right;">${formatarNumero(item.quantidade_saida)}</td>
                    <td style="text-align:right;">${item.saldo_atual ?? '-'}</td>
                    <td style="text-align:right;">${formatarMoeda(item.custo_unitario)}</td>
                    <td style="text-align:right;">${item.valor_total_estoque !== null && item.valor_total_estoque !== undefined ? formatarMoeda(item.valor_total_estoque) : '-'}</td>
                    <td>${escapeHtml(item.usuario_responsavel)}</td>
                    <td>${escapeHtml(item.observacao)}</td>
                    <td>${escapeHtml(item.detalhes_adicionais)}</td>
                </tr>
            `).join('');

            return `
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatorio Analitico Kardex</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #222; }
        h1 { font-size: 18px; margin-bottom: 6px; }
        .meta { margin-bottom: 12px; }
        .meta div { margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px; vertical-align: top; }
        th { background: #f2f2f2; text-align: left; font-size: 11px; }
    </style>
</head>
<body>
    <h1>Relatorio Analitico - Kardex</h1>
    <div class="meta">
        <div><strong>Empresa:</strong> ${escapeHtml(header.nome_empresa || '-')}</div>
        <div><strong>Filial:</strong> ${escapeHtml(header.nome_filial || '-')}</div>
        <div><strong>Produto:</strong> ${escapeHtml(header.nome_produto || header.id_produto || '-')}</div>
        <div><strong>Codigo de Barras:</strong> ${escapeHtml(header.codigo_barras || '-')}</div>
        <div><strong>Unidade:</strong> ${escapeHtml(header.unidade_medida || '-')}</div>
        <div><strong>Periodo:</strong> ${escapeHtml(periodo)}</div>
        <div><strong>Saldo Anterior:</strong> ${header.saldo_anterior !== null && header.saldo_anterior !== undefined ? formatarNumero(header.saldo_anterior) : '-'}</div>
    </div>
    <table>
        <thead>
            <tr>
                <th>Data/Hora</th>
                <th>Tipo</th>
                <th>Origem</th>
                <th>Documento</th>
                <th>Entrada</th>
                <th>Saida</th>
                <th>Saldo</th>
                <th>Custo Unitario</th>
                <th>Valor Total Estoque</th>
                <th>Usuario</th>
                <th>Observacao</th>
                <th>Detalhes</th>
            </tr>
        </thead>
        <tbody>
            ${rows || '<tr><td colspan="12">Nenhum dado encontrado.</td></tr>'}
        </tbody>
    </table>
</body>
</html>`;
        }

        function normalizarKardexDetalhado(data) {
            let origem = data ?? {};
            if (origem.success && origem.data) {
                origem = origem.data;
            } else if (origem.data && origem.data.data) {
                origem = origem.data.data;
            } else if (origem.data) {
                origem = origem.data;
            }

            let movimentacoes = origem.movimentacoes ?? origem.lancamentos ?? origem.itens ?? origem.data ?? origem;
            if (!Array.isArray(movimentacoes)) {
                movimentacoes = [];
            }

            return {
                produto: origem.produto ?? null,
                filial: origem.filial ?? null,
                movimentacoes: movimentacoes.map(normalizarMovimentacao)
            };
        }

        function normalizarMovimentacao(item) {
            return {
                data: item.data_movimentacao ?? item.data ?? item.created_at ?? '',
                tipo: item.tipo_movimentacao ?? item.tipo ?? 'entrada',
                referencia: item.id_referencia ?? item.referencia ?? item.observacao ?? '',
                quantidade: item.quantidade ?? item.qtd ?? 0,
                custo_unitario: item.custo_unitario ?? item.custo ?? item.preco_unitario ?? 0,
                saldo_atual: item.saldo_atual ?? item.saldo ?? item.estoque_atual ?? null
            };
        }

        function normalizarResumo(resumoData, detalheNormalizado) {
            let origem = resumoData ?? {};
            if (origem.success && origem.data) {
                origem = origem.data;
            } else if (origem.data && origem.data.data) {
                origem = origem.data.data;
            } else if (origem.data) {
                origem = origem.data;
            }

            return {
                saldo_inicial: origem.saldo_inicial ?? origem.saldoInicial ?? null,
                saldo_final: origem.saldo_final ?? origem.saldoFinal ?? null,
                entradas: origem.total_entradas ?? origem.entradas ?? null,
                saidas: origem.total_saidas ?? origem.saidas ?? null,
                custo_medio: origem.custo_medio ?? origem.custoMedio ?? null,
                valor_total: origem.valor_total ?? origem.valorTotal ?? null,
                movimentacoes: origem.movimentacoes ?? detalheNormalizado.movimentacoes.length
            };
        }

        function renderizarResumo(resumo) {
            const setValue = (id, value, isMoney = false) => {
                const el = document.getElementById(id);
                if (!el) return;
                if (value === null || value === undefined || value === '') {
                    el.textContent = '-';
                    return;
                }
                el.textContent = isMoney ? formatarMoeda(value) : value;
            };

            setValue('resumoSaldoInicial', resumo.saldo_inicial);
            setValue('resumoEntradas', resumo.entradas);
            setValue('resumoSaidas', resumo.saidas);
            setValue('resumoSaldoFinal', resumo.saldo_final);
            setValue('resumoCustoMedio', resumo.custo_medio, true);
            setValue('resumoValorTotal', resumo.valor_total, true);
            setValue('resumoMovimentacoes', resumo.movimentacoes ?? 0);
        }
        function renderizarMovimentacoes(detalhe) {
            const tbody = document.getElementById('tbodyKardex');
            if (!tbody) return;

            const movimentos = detalhe.movimentacoes ?? [];
            const contexto = document.getElementById('kardexContexto');

            if (contexto) {
                const produtoLabel = detalhe.produto?.descricao ?? detalhe.produto?.nome ?? '';
                const filialLabel = detalhe.filial?.nome_filial ?? detalhe.filial?.nome ?? '';
                if (produtoLabel || filialLabel) {
                    contexto.textContent = `${produtoLabel} ${filialLabel ? '- ' + filialLabel : ''}`.trim();
                }
            }

            if (!Array.isArray(movimentos) || movimentos.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Nenhuma movimentacao encontrada.</td></tr>';
                return;
            }

            tbody.innerHTML = movimentos.map(mov => {
                const badgeClass = {
                    'entrada': 'bg-success',
                    'saida': 'bg-danger',
                    'transferencia': 'bg-primary',
                    'ajuste': 'bg-warning'
                }[mov.tipo] || 'bg-secondary';

                const rowClass = {
                    'entrada': 'movement-entry',
                    'saida': 'movement-exit',
                    'transferencia': 'movement-transfer',
                    'ajuste': 'movement-adjustment'
                }[mov.tipo] || '';

                const sinal = mov.tipo === 'entrada' || mov.tipo === 'transferencia' ? '+' : '-';
                const quantidade = `${sinal}${mov.quantidade ?? 0}`;
                const subtotal = parseFloat(mov.quantidade || 0) * parseFloat(mov.custo_unitario || 0);

                return `
                    <tr class="${rowClass}">
                        <td>${formatarData(mov.data)}</td>
                        <td><span class="badge ${badgeClass} badge-tipo">${formatarTipoMovimentacao(mov.tipo)}</span></td>
                        <td>${escapeHtml(mov.referencia || '-')}</td>
                        <td>${quantidade}</td>
                        <td>${formatarMoeda(mov.custo_unitario)}</td>
                        <td>${mov.saldo_atual ?? '-'}</td>
                        <td>${formatarMoeda(subtotal)}</td>
                    </tr>
                `;
            }).join('');
        }

        async function exportarRelatorio() {
            await exportarRelatorioAnalitico();
        }

        async function exportarRelatorioAnalitico() {
            const filtros = obterFiltros();
            if (!filtros.id_filial || !filtros.id_produto) {
                mostrarNotificacao('Selecione a filial e o produto antes de exportar.', 'warning');
                return;
            }

            const key = getFiltroKey(filtros);
            if (analiticoCache.key !== key || !analiticoCache.data) {
                await carregarRelatorioAnalitico(filtros);
            }

            if (!analiticoCache.data) {
                mostrarNotificacao('Nenhum dado analitico para exportar.', 'warning');
                return;
            }

            const csv = buildAnaliticoCsv(analiticoCache.data);
            downloadArquivo(csv, 'kardex-analitico.csv', 'text/csv;charset=utf-8;');
        }

        function buildAnaliticoCsv(analitico) {
            const header = analitico.header ?? {};
            const movimentos = analitico.movimentacoes ?? [];
            const periodo = header.data_inicio || header.data_fim
                ? `${header.data_inicio || '-'} ate ${header.data_fim || '-'}`.trim()
                : 'Todo o periodo';

            const linhas = [];
            linhas.push(['Empresa', header.nome_empresa || '-']);
            linhas.push(['Filial', header.nome_filial || '-']);
            linhas.push(['Produto', header.nome_produto || header.id_produto || '-']);
            linhas.push(['Codigo de Barras', header.codigo_barras || '-']);
            linhas.push(['Unidade', header.unidade_medida || '-']);
            linhas.push(['Periodo', periodo]);
            linhas.push(['Saldo Anterior', header.saldo_anterior !== null && header.saldo_anterior !== undefined ? formatarNumero(header.saldo_anterior) : '-']);
            linhas.push([]);
            linhas.push(['Data/Hora', 'Tipo', 'Origem', 'Documento', 'Entrada', 'Saida', 'Saldo', 'Custo Unitario', 'Valor Total Estoque', 'Usuario', 'Observacao', 'Detalhes']);

            movimentos.forEach(item => {
                linhas.push([
                    formatarDataHora(item.data_hora),
                    item.tipo_movimento,
                    item.origem,
                    item.documento,
                    formatarNumero(item.quantidade_entrada),
                    formatarNumero(item.quantidade_saida),
                    item.saldo_atual ?? '',
                    formatarMoeda(item.custo_unitario),
                    item.valor_total_estoque !== null && item.valor_total_estoque !== undefined ? formatarMoeda(item.valor_total_estoque) : '',
                    item.usuario_responsavel,
                    item.observacao,
                    item.detalhes_adicionais
                ]);
            });

            return linhas.map(linha => linha.map(valor => escapeCsv(valor)).join(';')).join('\n');
        }

        function escapeCsv(value) {
            if (value === null || value === undefined) return '';
            const texto = String(value);
            if (texto.includes(';') || texto.includes('"') || texto.includes('\n')) {
                return `"${texto.replace(/"/g, '""')}"`;
            }
            return texto;
        }

        function downloadArquivo(content, filename, type) {
            const blob = new Blob([content], { type });
            const url = window.URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            link.remove();
            window.URL.revokeObjectURL(url);
        }

        function formatarData(data) {
            if (!data) return '-';
            try {
                const date = new Date(data);
                if (Number.isNaN(date.getTime())) {
                    return data;
                }
                return date.toLocaleDateString('pt-BR');
            } catch (e) {
                return data;
            }
        }

        function formatarDataHora(data) {
            if (!data) return '-';
            try {
                const date = new Date(data);
                if (Number.isNaN(date.getTime())) {
                    return data;
                }
                return date.toLocaleString('pt-BR');
            } catch (e) {
                return data;
            }
        }

        function formatarNumero(valor) {
            const numero = parseFloat(valor || 0);
            if (Number.isNaN(numero)) return '-';
            return numero.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function formatarMoeda(valor) {
            const numero = parseFloat(valor || 0);
            return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(numero);
        }

        function formatarTipoMovimentacao(tipo) {
            const map = {
                entrada: 'Entrada',
                saida: 'Saida',
                transferencia: 'Transferencia',
                ajuste: 'Ajuste'
            };
            return map[tipo] || tipo;
        }

        function escapeHtml(value) {
            if (value === null || value === undefined) return '';
            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/\"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }
    </script>
    <?php if (!defined('APP_SHELL')) { include __DIR__ . '/../components/app-foot.php'; } ?>
</body>
</html>
