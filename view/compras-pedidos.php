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
    <?php if (!defined('APP_SHELL')) { $pageTitle = 'Compras - Pedidos'; include __DIR__ . '/../components/app-head.php'; } ?>
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

        .status-badge {
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-rascunho { background: rgba(52, 152, 219, 0.1); color: var(--primary-color); }
        .status-enviado { background: rgba(243, 156, 18, 0.15); color: var(--warning-color); }
        .status-concluido { background: rgba(39, 174, 96, 0.1); color: var(--success-color); }
        .status-cancelado { background: rgba(231, 76, 60, 0.1); color: var(--danger-color); }

        .pedido-tabs {
            border-bottom: 1px solid #e6e6e6;
        }

        .pedido-tabs .nav-link {
            color: var(--secondary-color);
            background: #f4f6f8;
            border: 1px solid #e1e4e8;
            border-bottom: none;
            margin-right: 6px;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
            font-weight: 600;
        }

        .pedido-tabs .nav-link i {
            margin-right: 6px;
        }

        .pedido-tabs .nav-link:hover {
            background: #e9eef3;
            color: var(--secondary-color);
        }

        .pedido-tabs .nav-link.active {
            background: #ffffff;
            color: var(--primary-color);
            border-color: #e1e4e8;
        }

        .summary-card {
            border-left: 4px solid var(--primary-color);
        }

        .summary-card .summary-label {
            font-size: 0.85rem;
            color: #6c757d;
        }

        .summary-card .summary-value {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--secondary-color);
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
                        <li class="breadcrumb-item active">Compras - Pedidos</li>
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
                <h2 class="page-title"><i class="bi bi-file-earmark-text me-2 text-primary"></i>Pedidos de Compra</h2>
                <p class="page-subtitle">Crie e acompanhe pedidos com fornecedores.</p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNovoPedido">
                <i class="bi bi-plus-circle me-2"></i>Novo Pedido
            </button>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card card-custom summary-card">
                    <div class="card-body">
                        <div class="summary-label">Total de pedidos</div>
                        <div class="summary-value" id="totalPedidosResumo">0</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-custom summary-card" style="border-left-color: var(--warning-color);">
                    <div class="card-body">
                        <div class="summary-label">Em aberto</div>
                        <div class="summary-value" id="pedidosEmAbertoResumo">0</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-custom summary-card" style="border-left-color: var(--success-color);">
                    <div class="card-body">
                        <div class="summary-label">Concluidos</div>
                        <div class="summary-value" id="pedidosConcluidosResumo">0</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-custom">
            <div class="card-header-custom">
                <div class="row align-items-center">
                    <div class="col-md-4 mb-2 mb-md-0">
                        <div class="search-box">
                            <i class="bi bi-search"></i>
                            <input type="text" class="form-control" id="searchInput" placeholder="Buscar pedido...">
                        </div>
                    </div>
                    <div class="col-md-3 mb-2 mb-md-0">
                        <select class="form-select" id="filterStatus">
                            <option value="">Todos os status</option>
                            <option value="rascunho">Rascunho</option>
                            <option value="enviado">Enviado</option>
                            <option value="concluido">Concluido</option>
                            <option value="cancelado">Cancelado</option>
                        </select>
                    </div>
                    <div class="col-md-5 text-md-end">
                        <button class="btn btn-sm btn-outline-primary" onclick="carregarPedidos()">
                            <i class="bi bi-arrow-clockwise me-1"></i>Atualizar
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-custom">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Fornecedor</th>
                                <th>Data</th>
                                <th>Status</th>
                                <th>Valor</th>
                                <th>Acoes</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyPedidos">
                            <tr>
                                <td colspan="6" class="text-center text-muted">Carregando pedidos...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="text-muted" id="totalPedidos">0 pedido(s) encontrado(s)</div>
            </div>
        </div>
    </main>

    <div class="modal fade" id="modalNovoPedido" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Novo Pedido</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formPedido">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Filial *</label>
                                <select class="form-select" id="pedidoFilial" required>
                                    <option value="">Carregando filiais...</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Fornecedor *</label>
                                <select class="form-select" id="pedidoFornecedor" required>
                                    <option value="">Carregando fornecedores...</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Data do pedido *</label>
                                <input type="date" class="form-control" id="pedidoData" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Valor total</label>
                                <input type="number" class="form-control" id="pedidoValor" step="0.01">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Observacoes</label>
                                <input type="text" class="form-control" id="pedidoObservacoes">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="salvarPedido()">Salvar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDetalhesPedido" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="detalhePedidoTitulo">Pedido</h5>
                        <small class="text-muted" id="detalhePedidoSubtitulo"></small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs pedido-tabs" id="pedidoTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="pedidoResumoTabBtn" data-bs-toggle="tab" data-bs-target="#pedidoResumoTab" type="button" role="tab">
                                <i class="bi bi-card-text"></i>Resumo
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pedidoItensTabBtn" data-bs-toggle="tab" data-bs-target="#pedidoItensTab" type="button" role="tab">
                                <i class="bi bi-list-check"></i>Itens
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pedidoFornecedorTabBtn" data-bs-toggle="tab" data-bs-target="#pedidoFornecedorTab" type="button" role="tab">
                                <i class="bi bi-truck"></i>Fornecedor
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content pt-3">
                        <div class="tab-pane fade show active" id="pedidoResumoTab" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">ID</label>
                                    <input type="text" class="form-control" id="detalhePedidoId" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Data do pedido</label>
                                    <input type="date" class="form-control" id="detalhePedidoData">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" id="detalhePedidoStatus">
                                        <option value="rascunho">Rascunho</option>
                                        <option value="enviado">Enviado</option>
                                        <option value="concluido">Concluido</option>
                                        <option value="cancelado">Cancelado</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Fornecedor</label>
                                    <select class="form-select" id="detalhePedidoFornecedor">
                                        <option value="">Carregando fornecedores...</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Valor total</label>
                                    <input type="number" class="form-control" id="detalhePedidoValor" step="0.01" readonly>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">Observacoes</label>
                                    <input type="text" class="form-control" id="detalhePedidoObservacoes">
                                </div>
                                <div class="col-md-12 d-flex gap-2 flex-wrap">
                                    <button class="btn btn-primary" type="button" onclick="atualizarPedidoDetalhe()">
                                        <i class="bi bi-save me-2"></i>Salvar alteracoes
                                    </button>
                                    <button class="btn btn-outline-secondary" type="button" onclick="carregarDetalhesPedido()">
                                        <i class="bi bi-arrow-clockwise me-2"></i>Atualizar dados
                                    </button>
                                    <button class="btn btn-outline-danger" type="button" onclick="excluirPedidoDetalhe()">
                                        <i class="bi bi-trash me-2"></i>Excluir pedido
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="pedidoItensTab" role="tabpanel">
                            <div class="row g-3 align-items-end mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Produto</label>
                                    <select class="form-select" id="pedidoItemProduto">
                                        <option value="">Carregando produtos...</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Quantidade</label>
                                    <input type="number" class="form-control" id="pedidoItemQuantidade" step="0.01">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Preco unitario</label>
                                    <input type="number" class="form-control" id="pedidoItemPreco" step="0.01">
                                </div>
                                <div class="col-md-12">
                                    <button class="btn btn-success" type="button" onclick="adicionarItemPedido()">
                                        <i class="bi bi-plus-circle me-2"></i>Adicionar item
                                    </button>
                                    <small class="text-muted ms-2">Edicao e exclusao de itens dependem da API.</small>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover table-custom">
                                    <thead>
                                        <tr>
                                            <th>Produto</th>
                                            <th>Quantidade</th>
                                            <th>Preco</th>
                                            <th>Subtotal</th>
                                            <th>Acoes</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodyPedidoItens">
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">Carregando itens...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="pedidoFornecedorTab" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover table-custom">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Fornecedor</th>
                                            <th>Status</th>
                                            <th>Cidade/UF</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodyPedidoFornecedor">
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">Carregando fornecedor...</td>
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

    <div class="loading-overlay d-none" id="loadingOverlay">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Carregando...</span>
        </div>
    </div>

    <script>
        const idEmpresa = <?php echo $id_empresa; ?>;
        const token = '<?php echo $token; ?>';
        const BASE_URL = '<?= addslashes($config['api_base']) ?>';

        let pedidos = [];
        let produtos = [];
        let fornecedores = [];
        let pedidoDetalhe = null;
        let pedidoSelecionadoId = null;
        let devePersistirTotalPedido = false;
        let modalNovoPedido = null;
        let modalDetalhesPedido = null;

        const API_CONFIG = {
            BASE_URL: BASE_URL,
            PEDIDOS: '/api/v1/compras/pedidos',
            FILIAIS_EMPRESA: '/api/filiais/empresa',
            FORNECEDORES_EMPRESA: '/api/v1/fornecedores/empresa',
            PRODUTOS_EMPRESA: '/api/v1/produtos/empresa',
            PRODUTOS_EMPRESA_ALT: '/api/v1/empresas',
            LOGOUT: '/api/v1/logout',

            getHeaders: function() {
                return {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json',
                    'X-ID-EMPRESA': idEmpresa.toString()
                };
            },

            getJsonHeaders: function() {
                return {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-ID-EMPRESA': idEmpresa.toString()
                };
            }
        };

        document.addEventListener('DOMContentLoaded', function() {
            modalNovoPedido = new bootstrap.Modal(document.getElementById('modalNovoPedido'));
            modalDetalhesPedido = new bootstrap.Modal(document.getElementById('modalDetalhesPedido'));
            carregarPedidos();
            carregarFiliais();
            carregarFornecedores();

            document.getElementById('searchInput').addEventListener('input', filtrarPedidos);
            document.getElementById('filterStatus').addEventListener('change', filtrarPedidos);

            const logoutBtn = document.getElementById('logoutBtn');
            if (logoutBtn) {
                logoutBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    fazerLogoff();
                });
            }

            document.getElementById('pedidoData').value = new Date().toISOString().slice(0, 10);
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

        async function carregarPedidos() {
            mostrarLoading(true);
            try {
                const response = await fetch(`${API_CONFIG.BASE_URL}${API_CONFIG.PEDIDOS}`, {
                    method: 'GET',
                    headers: API_CONFIG.getHeaders()
                });

                if (!response.ok) {
                    throw new Error(`Erro ${response.status}: ${response.statusText}`);
                }

                const data = await response.json();
                const raw = normalizarLista(data);

                pedidos = raw.map(normalizarPedido);
                exibirPedidos(pedidos);
                atualizarResumo(pedidos);
            } catch (error) {
                console.error('Erro ao carregar pedidos:', error);
                mostrarNotificacao('Erro ao carregar pedidos: ' + error.message, 'error');
                document.getElementById('tbodyPedidos').innerHTML = '<tr><td colspan="6" class="text-center text-muted">Erro ao carregar dados</td></tr>';
            } finally {
                mostrarLoading(false);
            }
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

        function normalizarPedido(item) {
            return {
                id: item.id ?? item.id_pedido ?? item.pedido_id ?? null,
                data_pedido: item.data_pedido ?? item.data ?? item.created_at ?? '',
                status: item.status ?? 'rascunho',
                valor_total: item.valor_total ?? item.total ?? 0,
                id_fornecedor: item.id_fornecedor ?? item.fornecedor_id ?? null,
                fornecedor_nome: item.fornecedor?.razao_social ?? item.fornecedor?.nome ?? item.fornecedor_nome ?? ''
            };
        }

        async function carregarFiliais() {
            const select = document.getElementById('pedidoFilial');
            if (!select) return;

            select.innerHTML = '<option value=\"\">Carregando filiais...</option>';

            try {
                const response = await fetch(`${API_CONFIG.BASE_URL}${API_CONFIG.FILIAIS_EMPRESA}/${idEmpresa}`, {
                    method: 'GET',
                    headers: API_CONFIG.getHeaders()
                });

                if (!response.ok) {
                    throw new Error(`Erro ${response.status}: ${response.statusText}`);
                }

                const data = await response.json();
                const filiais = normalizarLista(data);

                if (!Array.isArray(filiais) || filiais.length === 0) {
                    select.innerHTML = '<option value=\"\">Nenhuma filial encontrada</option>';
                    return;
                }

                select.innerHTML = '<option value=\"\">Selecione a filial</option>';
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
            } catch (error) {
                console.error('Erro ao carregar filiais:', error);
                select.innerHTML = '<option value=\"\">Erro ao carregar filiais</option>';
                mostrarNotificacao('Erro ao carregar filiais: ' + error.message, 'error');
            }
        }

        async function carregarFornecedores() {
            try {
                const response = await fetch(`${API_CONFIG.BASE_URL}${API_CONFIG.FORNECEDORES_EMPRESA}/${idEmpresa}`, {
                    method: 'GET',
                    headers: API_CONFIG.getHeaders()
                });

                if (!response.ok) {
                    throw new Error(`Erro ${response.status}: ${response.statusText}`);
                }

                const data = await response.json();
                const raw = normalizarLista(data);
                fornecedores = raw.map(normalizarFornecedor).filter(f => f.id_fornecedor);

                preencherSelectFornecedores();
            } catch (error) {
                console.error('Erro ao carregar fornecedores:', error);
                fornecedores = [];
                preencherSelectFornecedores();
                mostrarNotificacao('Erro ao carregar fornecedores: ' + error.message, 'error');
            }
        }

        async function carregarProdutos() {
            if (produtos.length > 0) {
                preencherSelectProdutos();
                return;
            }

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

                    const raw = normalizarListaProdutos(data);
                    produtos = raw.map(normalizarProduto).filter(p => p.id_produto);
                    if (produtos.length > 0) {
                        preencherSelectProdutos();
                        return;
                    }
                } catch (error) {
                    console.error('Erro ao carregar produtos:', error);
                }
            }

            produtos = [];
            preencherSelectProdutos();
        }

        async function parseJsonResponse(response) {
            try {
                return await response.json();
            } catch (error) {
                console.error('Erro ao interpretar JSON:', error);
                return null;
            }
        }

        function normalizarProduto(item) {
            return {
                id_produto: item.id_produto ?? item.id ?? null,
                descricao: item.descricao ?? item.nome ?? '',
                unidade_medida: item.unidade_medida ?? item.unidade ?? '',
                categoria: item.categoria ?? item.categoria_nome ?? ''
            };
        }

        function normalizarFornecedor(item) {
            return {
                id_fornecedor: item.id_fornecedor ?? item.id ?? null,
                razao_social: item.razao_social ?? item.nome_fantasia ?? item.nome ?? '',
                nome_fantasia: item.nome_fantasia ?? '',
                cidade: item.cidade ?? '',
                estado: item.estado ?? '',
                status: item.status ?? ''
            };
        }

        function preencherSelectFornecedores() {
            const selectNovo = document.getElementById('pedidoFornecedor');
            const selectDetalhe = document.getElementById('detalhePedidoFornecedor');

            const selects = [selectNovo, selectDetalhe].filter(Boolean);
            if (selects.length === 0) return;

            if (!Array.isArray(fornecedores) || fornecedores.length === 0) {
                selects.forEach(select => {
                    select.innerHTML = '<option value=\"\">Nenhum fornecedor encontrado</option>';
                });
                return;
            }

            selects.forEach(select => {
                select.innerHTML = '<option value=\"\">Selecione o fornecedor</option>';
                fornecedores.forEach(fornecedor => {
                    const cidade = fornecedor.cidade ?? '';
                    const estado = fornecedor.estado ?? '';
                    const local = cidade && estado ? ` - ${cidade}/${estado}` : (cidade ? ` - ${cidade}` : '');
                    const status = fornecedor.status ? ` (${fornecedor.status})` : '';

                    const option = document.createElement('option');
                    option.value = fornecedor.id_fornecedor;
                    option.textContent = `${fornecedor.razao_social}${local}${status}`;
                    select.appendChild(option);
                });
            });

            if (pedidoDetalhe?.id_fornecedor && selectDetalhe) {
                selectDetalhe.value = String(pedidoDetalhe.id_fornecedor);
            }
        }

        function preencherSelectProdutos() {
            const select = document.getElementById('pedidoItemProduto');
            if (!select) return;

            if (!Array.isArray(produtos) || produtos.length === 0) {
                select.innerHTML = '<option value=\"\">Nenhum produto encontrado</option>';
                return;
            }

            select.innerHTML = '<option value=\"\">Selecione o produto</option>';
            produtos.forEach(produto => {
                const option = document.createElement('option');
                option.value = produto.id_produto;
                option.textContent = `${produto.descricao} (#${produto.id_produto})`;
                select.appendChild(option);
            });
        }

        function exibirPedidos(lista) {
            const tbody = document.getElementById('tbodyPedidos');
            if (!Array.isArray(lista) || lista.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Nenhum pedido encontrado</td></tr>';
                atualizarTotal(0);
                return;
            }

            tbody.innerHTML = lista.map(pedido => {
                const fornecedor = pedido.fornecedor_nome || (pedido.id_fornecedor ? `#${pedido.id_fornecedor}` : '-');
                return `
                    <tr>
                        <td>${pedido.id ?? '-'}</td>
                        <td>${escapeHtml(fornecedor)}</td>
                        <td>${formatarData(pedido.data_pedido)}</td>
                        <td><span class="status-badge status-${pedido.status}">${escapeHtml(pedido.status)}</span></td>
                        <td>${formatarMoeda(pedido.valor_total)}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="abrirDetalhesPedido(${pedido.id ?? 'null'})" ${pedido.id ? '' : 'disabled'}>
                                <i class="bi bi-eye me-1"></i>Detalhes
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');

            atualizarTotal(lista.length);
        }

        function abrirDetalhesPedido(id) {
            if (!id) {
                mostrarNotificacao('Pedido invalido.', 'warning');
                return;
            }

            pedidoSelecionadoId = id;
            modalDetalhesPedido.show();

            carregarProdutos();
            carregarFornecedores();
            carregarDetalhesPedido();
        }

        async function carregarDetalhesPedido() {
            if (!pedidoSelecionadoId) return;

            mostrarLoading(true);
            try {
                const response = await fetch(`${API_CONFIG.BASE_URL}${API_CONFIG.PEDIDOS}/${pedidoSelecionadoId}`, {
                    method: 'GET',
                    headers: API_CONFIG.getHeaders()
                });

                if (!response.ok) {
                    throw new Error(`Erro ${response.status}: ${response.statusText}`);
                }

                const data = await response.json();
                pedidoDetalhe = normalizarPedidoDetalhe(data);
                renderizarDetalhesPedido();
            } catch (error) {
                console.error('Erro ao carregar pedido:', error);
                mostrarNotificacao('Erro ao carregar pedido: ' + error.message, 'error');
            } finally {
                mostrarLoading(false);
            }
        }

        function normalizarPedidoDetalhe(data) {
            let origem = data ?? {};
            if (origem.success && origem.data) {
                origem = origem.data;
            } else if (origem.data && origem.data.data) {
                origem = origem.data.data;
            } else if (origem.data) {
                origem = origem.data;
            }

            const itensRaw = origem.itens ?? origem.items ?? origem.itens_pedido ?? [];
            const fornecedorObj = origem.fornecedor ?? origem.fornecedor_detalhe ?? null;

            return {
                id: origem.id ?? origem.id_pedido ?? origem.pedido_id ?? pedidoSelecionadoId,
                data_pedido: origem.data_pedido ?? origem.data ?? origem.created_at ?? '',
                status: origem.status ?? 'rascunho',
                valor_total: origem.valor_total ?? origem.total ?? 0,
                id_fornecedor: origem.id_fornecedor ?? origem.fornecedor_id ?? fornecedorObj?.id_fornecedor ?? fornecedorObj?.id ?? null,
                observacoes: origem.observacoes ?? origem.descricao ?? '',
                itens: normalizarItensPedido(itensRaw),
                fornecedor: fornecedorObj
            };
        }

        function normalizarItensPedido(lista) {
            if (!Array.isArray(lista)) return [];
            return lista.map(item => ({
                id_item: item.id_item ?? item.id ?? item.item_id ?? null,
                id_produto: item.id_produto ?? item.produto_id ?? item.produto?.id_produto ?? item.produto?.id ?? null,
                quantidade: item.quantidade ?? item.qtd ?? 0,
                preco_unitario: item.preco_unitario ?? item.preco ?? 0,
                produto: item.produto ?? null
            }));
        }

        function renderizarDetalhesPedido() {
            if (!pedidoDetalhe) return;

            const totalItens = pedidoDetalhe.itens?.length ?? 0;
            document.getElementById('detalhePedidoTitulo').textContent = `Pedido #${pedidoDetalhe.id ?? '-'}`;
            document.getElementById('detalhePedidoSubtitulo').textContent = `Status: ${pedidoDetalhe.status} | Itens: ${totalItens}`;

            document.getElementById('detalhePedidoId').value = pedidoDetalhe.id ?? '';
            document.getElementById('detalhePedidoData').value = formatarDataInput(pedidoDetalhe.data_pedido);
            document.getElementById('detalhePedidoStatus').value = pedidoDetalhe.status ?? 'rascunho';
            document.getElementById('detalhePedidoValor').value = pedidoDetalhe.valor_total ?? 0;
            document.getElementById('detalhePedidoObservacoes').value = pedidoDetalhe.observacoes ?? '';

            if (pedidoDetalhe.id_fornecedor) {
                const select = document.getElementById('detalhePedidoFornecedor');
                if (select) {
                    select.value = String(pedidoDetalhe.id_fornecedor);
                }
            }

            renderizarItensPedido();
            renderizarFornecedorPedido();
        }

        function renderizarItensPedido() {
            const tbody = document.getElementById('tbodyPedidoItens');
            if (!tbody) return;

            if (!Array.isArray(pedidoDetalhe.itens) || pedidoDetalhe.itens.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Nenhum item adicionado</td></tr>';
                atualizarValorTotalPedido(0);
                return;
            }

            let total = 0;
            tbody.innerHTML = pedidoDetalhe.itens.map((item, index) => {
                const produtoNome = obterNomeProdutoPedido(item);
                const subtotal = (parseFloat(item.quantidade || 0) * parseFloat(item.preco_unitario || 0));
                total += subtotal;
                const idItem = item.id_item ?? '';
                const rowId = idItem ? idItem : `tmp-${index}`;
                const disabled = idItem ? '' : 'disabled';
                return `
                    <tr>
                        <td>${escapeHtml(produtoNome)}</td>
                        <td><input type="number" class="form-control form-control-sm" id="pedidoQtd-${rowId}" value="${item.quantidade ?? 0}" ${disabled}></td>
                        <td><input type="number" class="form-control form-control-sm" id="pedidoPreco-${rowId}" value="${item.preco_unitario ?? 0}" step="0.01" ${disabled}></td>
                        <td>${formatarMoeda(subtotal)}</td>
                        <td class="d-flex gap-2 flex-wrap">
                            <button class="btn btn-sm btn-outline-primary" onclick="atualizarItemPedido('${idItem}', '${rowId}')" ${disabled}>
                                <i class="bi bi-save me-1"></i>Salvar
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="removerItemPedido('${idItem}')" ${disabled}>
                                <i class="bi bi-trash me-1"></i>Excluir
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');

            atualizarValorTotalPedido(total);
        }

        function renderizarFornecedorPedido() {
            const tbody = document.getElementById('tbodyPedidoFornecedor');
            if (!tbody) return;

            const fornecedorId = pedidoDetalhe.id_fornecedor;
            const fornecedor = fornecedores.find(f => String(f.id_fornecedor) === String(fornecedorId)) || pedidoDetalhe.fornecedor;

            if (!fornecedor) {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">Fornecedor nao encontrado</td></tr>';
                return;
            }

            const nome = fornecedor.razao_social ?? fornecedor.nome_fantasia ?? fornecedor.nome ?? 'Fornecedor';
            const cidade = fornecedor.cidade ?? '';
            const estado = fornecedor.estado ?? '';
            const local = cidade && estado ? `${cidade}/${estado}` : (cidade ? cidade : '-');
            const status = fornecedor.status ?? '-';
            const id = fornecedor.id_fornecedor ?? fornecedor.id ?? fornecedorId ?? '-';

            tbody.innerHTML = `
                <tr>
                    <td>${id}</td>
                    <td>${escapeHtml(nome)}</td>
                    <td>${escapeHtml(status)}</td>
                    <td>${escapeHtml(local)}</td>
                </tr>
            `;
        }

        function obterNomeProdutoPedido(item) {
            const produtoObj = item.produto ?? null;
            if (produtoObj?.descricao) {
                return produtoObj.descricao;
            }

            const produtoId = item.id_produto ?? null;
            const produto = produtos.find(p => String(p.id_produto) === String(produtoId));
            return produto?.descricao || (produtoId ? `Produto #${produtoId}` : 'Produto');
        }

        function filtrarPedidos() {
            const termo = document.getElementById('searchInput').value.toLowerCase();
            const status = document.getElementById('filterStatus').value;

            const filtrados = pedidos.filter(pedido => {
                const texto = `${pedido.id ?? ''} ${pedido.fornecedor_nome ?? ''}`.toLowerCase();
                const matchTexto = !termo || texto.includes(termo);
                const matchStatus = !status || pedido.status === status;
                return matchTexto && matchStatus;
            });

            exibirPedidos(filtrados);
            atualizarResumo(filtrados);
        }

        function atualizarResumo(lista) {
            const total = lista.length;
            const emAberto = lista.filter(p => !['concluido', 'cancelado'].includes(p.status)).length;
            const concluidos = lista.filter(p => p.status === 'concluido').length;

            document.getElementById('totalPedidosResumo').textContent = total;
            document.getElementById('pedidosEmAbertoResumo').textContent = emAberto;
            document.getElementById('pedidosConcluidosResumo').textContent = concluidos;
        }

        function atualizarTotal(total) {
            document.getElementById('totalPedidos').textContent = `${total} pedido(s) encontrado(s)`;
        }

        async function salvarPedido() {
            const payload = {
                id_empresa: idEmpresa,
                id_filial: parseInt(document.getElementById('pedidoFilial').value || '0', 10),
                id_fornecedor: parseInt(document.getElementById('pedidoFornecedor').value || '0', 10),
                data_pedido: document.getElementById('pedidoData').value,
                valor_total: parseFloat(document.getElementById('pedidoValor').value || '0'),
                observacoes: document.getElementById('pedidoObservacoes').value.trim()
            };

            if (!payload.id_filial || !payload.id_fornecedor || !payload.data_pedido) {
                mostrarNotificacao('Preencha os campos obrigatorios.', 'warning');
                return;
            }

            mostrarLoading(true);
            try {
                const response = await fetch(`${API_CONFIG.BASE_URL}${API_CONFIG.PEDIDOS}`, {
                    method: 'POST',
                    headers: API_CONFIG.getJsonHeaders(),
                    body: JSON.stringify(payload)
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    throw new Error(errorText || `Erro ${response.status}`);
                }

                modalNovoPedido.hide();
                document.getElementById('formPedido').reset();
                document.getElementById('pedidoData').value = new Date().toISOString().slice(0, 10);
                mostrarNotificacao('Pedido criado com sucesso!', 'success');
                carregarPedidos();
            } catch (error) {
                console.error('Erro ao criar pedido:', error);
                mostrarNotificacao('Erro ao criar pedido: ' + error.message, 'error');
            } finally {
                mostrarLoading(false);
            }
        }

        async function adicionarItemPedido() {
            if (!pedidoSelecionadoId) {
                mostrarNotificacao('Selecione um pedido primeiro.', 'warning');
                return;
            }

            const produtoId = document.getElementById('pedidoItemProduto').value;
            const quantidade = document.getElementById('pedidoItemQuantidade').value;
            const preco = document.getElementById('pedidoItemPreco').value;

            if (!produtoId || !quantidade || !preco) {
                mostrarNotificacao('Informe produto, quantidade e preco.', 'warning');
                return;
            }

            mostrarLoading(true);
            try {
                const response = await fetch(`${API_CONFIG.BASE_URL}${API_CONFIG.PEDIDOS}/${pedidoSelecionadoId}/itens`, {
                    method: 'POST',
                    headers: API_CONFIG.getJsonHeaders(),
                    body: JSON.stringify({
                        id_produto: parseInt(produtoId, 10),
                        quantidade: parseFloat(quantidade),
                        preco_unitario: parseFloat(preco)
                    })
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    throw new Error(errorText || `Erro ${response.status}`);
                }

                document.getElementById('pedidoItemProduto').value = '';
                document.getElementById('pedidoItemQuantidade').value = '';
                document.getElementById('pedidoItemPreco').value = '';

                mostrarNotificacao('Item adicionado ao pedido!', 'success');
                devePersistirTotalPedido = true;
                carregarDetalhesPedido();
            } catch (error) {
                console.error('Erro ao adicionar item:', error);
                mostrarNotificacao('Erro ao adicionar item: ' + error.message, 'error');
            } finally {
                mostrarLoading(false);
            }
        }

        async function atualizarItemPedido(idItem, rowId) {
            if (!pedidoSelecionadoId || !idItem) return;

            const key = rowId || idItem;
            const quantidade = document.getElementById(`pedidoQtd-${key}`)?.value;
            const preco = document.getElementById(`pedidoPreco-${key}`)?.value;

            if (!quantidade || !preco) {
                mostrarNotificacao('Quantidade e preco sao obrigatorios.', 'warning');
                return;
            }

            mostrarLoading(true);
            try {
                const response = await fetch(`${API_CONFIG.BASE_URL}${API_CONFIG.PEDIDOS}/${pedidoSelecionadoId}/itens/${idItem}`, {
                    method: 'PUT',
                    headers: API_CONFIG.getJsonHeaders(),
                    body: JSON.stringify({
                        quantidade: parseFloat(quantidade),
                        preco_unitario: parseFloat(preco)
                    })
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    throw new Error(errorText || `Erro ${response.status}`);
                }

                mostrarNotificacao('Item atualizado com sucesso!', 'success');
                devePersistirTotalPedido = true;
                carregarDetalhesPedido();
            } catch (error) {
                console.error('Erro ao atualizar item:', error);
                mostrarNotificacao('Erro ao atualizar item: ' + error.message, 'error');
            } finally {
                mostrarLoading(false);
            }
        }

        async function removerItemPedido(idItem) {
            if (!pedidoSelecionadoId || !idItem) return;
            if (!confirm('Deseja remover este item do pedido?')) {
                return;
            }

            mostrarLoading(true);
            try {
                const response = await fetch(`${API_CONFIG.BASE_URL}${API_CONFIG.PEDIDOS}/${pedidoSelecionadoId}/itens/${idItem}`, {
                    method: 'DELETE',
                    headers: API_CONFIG.getHeaders()
                });

                if (!response.ok && response.status !== 204) {
                    const errorText = await response.text();
                    throw new Error(errorText || `Erro ${response.status}`);
                }

                mostrarNotificacao('Item removido com sucesso!', 'success');
                devePersistirTotalPedido = true;
                carregarDetalhesPedido();
            } catch (error) {
                console.error('Erro ao remover item:', error);
                mostrarNotificacao('Erro ao remover item: ' + error.message, 'error');
            } finally {
                mostrarLoading(false);
            }
        }

        function atualizarValorTotalPedido(total) {
            const valor = Number.isFinite(total) ? total : 0;
            const input = document.getElementById('detalhePedidoValor');
            if (input) {
                input.value = valor.toFixed(2);
            }

            if (pedidoDetalhe) {
                pedidoDetalhe.valor_total = valor;
                const totalItens = pedidoDetalhe.itens?.length ?? 0;
                document.getElementById('detalhePedidoSubtitulo').textContent = `Status: ${pedidoDetalhe.status} | Itens: ${totalItens} | Total: ${formatarMoeda(valor)}`;
            }

            if (devePersistirTotalPedido && pedidoSelecionadoId) {
                persistirTotalPedido(valor);
            }
        }

        async function persistirTotalPedido(total) {
            devePersistirTotalPedido = false;

            try {
                const response = await fetch(`${API_CONFIG.BASE_URL}${API_CONFIG.PEDIDOS}/${pedidoSelecionadoId}`, {
                    method: 'PUT',
                    headers: API_CONFIG.getJsonHeaders(),
                    body: JSON.stringify({
                        valor_total: parseFloat(total || 0)
                    })
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    throw new Error(errorText || `Erro ${response.status}`);
                }

                carregarPedidos();
            } catch (error) {
                console.error('Erro ao atualizar total do pedido:', error);
                mostrarNotificacao('Erro ao atualizar total do pedido: ' + error.message, 'error');
            }
        }

        async function atualizarPedidoDetalhe() {
            if (!pedidoSelecionadoId) return;

            const payload = {
                status: document.getElementById('detalhePedidoStatus').value,
                data_pedido: document.getElementById('detalhePedidoData').value,
                valor_total: parseFloat(document.getElementById('detalhePedidoValor').value || '0'),
                observacoes: document.getElementById('detalhePedidoObservacoes').value.trim(),
                id_fornecedor: parseInt(document.getElementById('detalhePedidoFornecedor').value || '0', 10)
            };

            mostrarLoading(true);
            try {
                const response = await fetch(`${API_CONFIG.BASE_URL}${API_CONFIG.PEDIDOS}/${pedidoSelecionadoId}`, {
                    method: 'PUT',
                    headers: API_CONFIG.getJsonHeaders(),
                    body: JSON.stringify(payload)
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    throw new Error(errorText || `Erro ${response.status}`);
                }

                mostrarNotificacao('Pedido atualizado com sucesso!', 'success');
                carregarPedidos();
                carregarDetalhesPedido();
            } catch (error) {
                console.error('Erro ao atualizar pedido:', error);
                mostrarNotificacao('Erro ao atualizar pedido: ' + error.message, 'error');
            } finally {
                mostrarLoading(false);
            }
        }

        async function excluirPedidoDetalhe() {
            if (!pedidoSelecionadoId) return;
            if (!confirm('Deseja excluir este pedido?')) {
                return;
            }

            mostrarLoading(true);
            try {
                const response = await fetch(`${API_CONFIG.BASE_URL}${API_CONFIG.PEDIDOS}/${pedidoSelecionadoId}`, {
                    method: 'DELETE',
                    headers: API_CONFIG.getHeaders()
                });

                if (!response.ok && response.status !== 204) {
                    const errorText = await response.text();
                    throw new Error(errorText || `Erro ${response.status}`);
                }

                mostrarNotificacao('Pedido excluido com sucesso!', 'success');
                modalDetalhesPedido.hide();
                carregarPedidos();
            } catch (error) {
                console.error('Erro ao excluir pedido:', error);
                mostrarNotificacao('Erro ao excluir pedido: ' + error.message, 'error');
            } finally {
                mostrarLoading(false);
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

        function formatarDataInput(data) {
            if (!data) return '';
            try {
                const date = new Date(data);
                if (Number.isNaN(date.getTime())) {
                    return '';
                }
                return date.toISOString().slice(0, 10);
            } catch (e) {
                return '';
            }
        }

        function formatarMoeda(valor) {
            const numero = parseFloat(valor || 0);
            return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(numero);
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
