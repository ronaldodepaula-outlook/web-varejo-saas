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
    <?php if (!defined('APP_SHELL')) { $pageTitle = 'Compras - Recebimentos'; include __DIR__ . '/../components/app-head.php'; } ?>
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

        .status-pendente { background: rgba(243, 156, 18, 0.15); color: var(--warning-color); }
        .status-conferido { background: rgba(39, 174, 96, 0.1); color: var(--success-color); }
        .status-cancelado { background: rgba(231, 76, 60, 0.1); color: var(--danger-color); }

        .entrada-tabs {
            border-bottom: 1px solid #e6e6e6;
        }

        .entrada-tabs .nav-link {
            color: var(--secondary-color);
            background: #f4f6f8;
            border: 1px solid #e1e4e8;
            border-bottom: none;
            margin-right: 6px;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
            font-weight: 600;
        }

        .entrada-tabs .nav-link i {
            margin-right: 6px;
        }

        .entrada-tabs .nav-link:hover {
            background: #e9eef3;
            color: var(--secondary-color);
        }

        .entrada-tabs .nav-link.active {
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
                        <li class="breadcrumb-item active">Compras - Recebimentos</li>
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
                <h2 class="page-title"><i class="bi bi-box-arrow-in-down me-2 text-primary"></i>Recebimento de Compras</h2>
                <p class="page-subtitle">Registre entradas e atualize o estoque.</p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNovaEntrada">
                <i class="bi bi-plus-circle me-2"></i>Nova Entrada
            </button>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card card-custom summary-card">
                    <div class="card-body">
                        <div class="summary-label">Total de entradas</div>
                        <div class="summary-value" id="totalEntradasResumo">0</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-custom summary-card" style="border-left-color: var(--warning-color);">
                    <div class="card-body">
                        <div class="summary-label">Pendentes</div>
                        <div class="summary-value" id="entradasPendentesResumo">0</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-custom summary-card" style="border-left-color: var(--success-color);">
                    <div class="card-body">
                        <div class="summary-label">Conferidas</div>
                        <div class="summary-value" id="entradasConferidasResumo">0</div>
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
                            <input type="text" class="form-control" id="searchInput" placeholder="Buscar entrada...">
                        </div>
                    </div>
                    <div class="col-md-3 mb-2 mb-md-0">
                        <select class="form-select" id="filterStatus">
                            <option value="">Todos os status</option>
                            <option value="pendente">Pendente</option>
                            <option value="conferido">Conferido</option>
                            <option value="cancelado">Cancelado</option>
                        </select>
                    </div>
                    <div class="col-md-5 text-md-end">
                        <button class="btn btn-sm btn-outline-primary" onclick="carregarEntradas()">
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
                                <th>Data Entrada</th>
                                <th>Tipo</th>
                                <th>Status</th>
                                <th>Valor</th>
                                <th>Acoes</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyEntradas">
                            <tr>
                                <td colspan="7" class="text-center text-muted">Carregando entradas...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="text-muted" id="totalEntradas">0 entrada(s) encontrada(s)</div>
            </div>
        </div>
    </main>

    <div class="modal fade" id="modalNovaEntrada" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nova Entrada</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formEntrada">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Filial *</label>
                                <select class="form-select" id="entradaFilial" required>
                                    <option value="">Carregando filiais...</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Fornecedor *</label>
                                <select class="form-select" id="entradaFornecedor" required>
                                    <option value="">Carregando fornecedores...</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tipo de entrada *</label>
                                <select class="form-select" id="entradaTipo" required>
                                    <option value="pedido">Pedido</option>
                                    <option value="avulsa">Avulsa</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Pedido (numero)</label>
                                <select class="form-select" id="entradaPedido">
                                    <option value="">Carregando pedidos...</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Data entrada *</label>
                                <input type="datetime-local" class="form-control" id="entradaData" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Data recebimento</label>
                                <input type="datetime-local" class="form-control" id="entradaRecebimento">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Valor total</label>
                                <input type="number" class="form-control" id="entradaValor" step="0.01">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Observacoes</label>
                                <input type="text" class="form-control" id="entradaObservacoes">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="salvarEntrada()">Salvar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDetalhesEntrada" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="detalheEntradaTitulo">Entrada</h5>
                        <small class="text-muted" id="detalheEntradaSubtitulo"></small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs entrada-tabs" id="entradaTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="entradaResumoTabBtn" data-bs-toggle="tab" data-bs-target="#entradaResumoTab" type="button" role="tab">
                                <i class="bi bi-card-text"></i>Resumo
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="entradaItensTabBtn" data-bs-toggle="tab" data-bs-target="#entradaItensTab" type="button" role="tab">
                                <i class="bi bi-list-check"></i>Itens
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="entradaHistoricoTabBtn" data-bs-toggle="tab" data-bs-target="#entradaHistoricoTab" type="button" role="tab">
                                <i class="bi bi-clock-history"></i>Historico
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content pt-3">
                        <div class="tab-pane fade show active" id="entradaResumoTab" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">ID</label>
                                    <input type="text" class="form-control" id="detalheEntradaId" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Tipo de entrada</label>
                                    <input type="text" class="form-control" id="detalheEntradaTipo" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Pedido</label>
                                    <select class="form-select" id="detalheEntradaPedido" disabled>
                                        <option value="">Carregando pedidos...</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Fornecedor</label>
                                    <select class="form-select" id="detalheEntradaFornecedor" disabled>
                                        <option value="">Carregando fornecedores...</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Data entrada</label>
                                    <input type="datetime-local" class="form-control" id="detalheEntradaData" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Data recebimento</label>
                                    <input type="datetime-local" class="form-control" id="detalheEntradaRecebimento">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" id="detalheEntradaStatus">
                                        <option value="pendente">Pendente</option>
                                        <option value="conferido">Conferido</option>
                                        <option value="cancelado">Cancelado</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Observacoes</label>
                                    <input type="text" class="form-control" id="detalheEntradaObservacoes">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Valor total</label>
                                    <input type="number" class="form-control" id="detalheEntradaValor" step="0.01" readonly>
                                </div>
                                <div class="col-md-12 d-flex gap-2 flex-wrap">
                                    <button class="btn btn-primary" type="button" onclick="atualizarEntradaDetalhe()">
                                        <i class="bi bi-save me-2"></i>Salvar alteracoes
                                    </button>
                                    <button class="btn btn-outline-secondary" type="button" onclick="carregarDetalhesEntrada()">
                                        <i class="bi bi-arrow-clockwise me-2"></i>Atualizar dados
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="entradaItensTab" role="tabpanel">
                            <div class="row g-3 align-items-end mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Produto</label>
                                    <select class="form-select" id="entradaItemProduto">
                                        <option value="">Carregando produtos...</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Quantidade recebida</label>
                                    <input type="number" class="form-control" id="entradaItemQuantidade" step="0.01">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Preco unitario</label>
                                    <input type="number" class="form-control" id="entradaItemPreco" step="0.01">
                                </div>
                                <div class="col-md-12">
                                    <button class="btn btn-success" type="button" onclick="adicionarItemEntrada()">
                                        <i class="bi bi-plus-circle me-2"></i>Adicionar item
                                    </button>
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
                                        </tr>
                                    </thead>
                                    <tbody id="tbodyEntradaItens">
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">Carregando itens...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="entradaHistoricoTab" role="tabpanel">
                            <div class="row g-3 align-items-end mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Acao</label>
                                    <input type="text" class="form-control" id="entradaHistoricoAcao" placeholder="ex: conferencia">
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">Descricao</label>
                                    <input type="text" class="form-control" id="entradaHistoricoDescricao" placeholder="Detalhe da acao">
                                </div>
                                <div class="col-md-12">
                                    <button class="btn btn-success" type="button" onclick="adicionarHistoricoEntrada()">
                                        <i class="bi bi-plus-circle me-2"></i>Registrar historico
                                    </button>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover table-custom">
                                    <thead>
                                        <tr>
                                            <th>Data</th>
                                            <th>Acao</th>
                                            <th>Descricao</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodyEntradaHistorico">
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">Carregando historico...</td>
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

        let entradas = [];
        let produtos = [];
        let fornecedores = [];
        let pedidosCache = [];
        let entradaDetalhe = null;
        let entradaSelecionadaId = null;
        let modalNovaEntrada = null;
        let modalDetalhesEntrada = null;

        const API_CONFIG = {
            BASE_URL: BASE_URL,
            ENTRADAS: '/api/v1/compras/entradas',
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
            modalNovaEntrada = new bootstrap.Modal(document.getElementById('modalNovaEntrada'));
            modalDetalhesEntrada = new bootstrap.Modal(document.getElementById('modalDetalhesEntrada'));
            carregarEntradas();
            carregarFiliais();
            carregarFornecedores();
            carregarPedidos();

            document.getElementById('searchInput').addEventListener('input', filtrarEntradas);
            document.getElementById('filterStatus').addEventListener('change', filtrarEntradas);
            document.getElementById('entradaTipo').addEventListener('change', atualizarEntradaTipo);

            const logoutBtn = document.getElementById('logoutBtn');
            if (logoutBtn) {
                logoutBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    fazerLogoff();
                });
            }

            definirDatasPadrao();
            atualizarEntradaTipo();
        });

        function definirDatasPadrao() {
            const agora = new Date();
            const dataIso = new Date(agora.getTime() - agora.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
            document.getElementById('entradaData').value = dataIso;
        }

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

        async function carregarEntradas() {
            mostrarLoading(true);
            try {
                const response = await fetch(`${API_CONFIG.BASE_URL}${API_CONFIG.ENTRADAS}`, {
                    method: 'GET',
                    headers: API_CONFIG.getHeaders()
                });

                if (!response.ok) {
                    throw new Error(`Erro ${response.status}: ${response.statusText}`);
                }

                const data = await response.json();
                const raw = normalizarLista(data);

                entradas = raw.map(normalizarEntrada);
                exibirEntradas(entradas);
                atualizarResumo(entradas);
            } catch (error) {
                console.error('Erro ao carregar entradas:', error);
                mostrarNotificacao('Erro ao carregar entradas: ' + error.message, 'error');
                document.getElementById('tbodyEntradas').innerHTML = '<tr><td colspan="7" class="text-center text-muted">Erro ao carregar dados</td></tr>';
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
            const maybeArray = Object.values(data).find(v => Array.isArray(v));
            return maybeArray || [];
        }

        function normalizarEntrada(item) {
            return {
                id: item.id ?? item.id_entrada ?? item.entrada_id ?? null,
                data_entrada: item.data_entrada ?? item.created_at ?? '',
                tipo_entrada: item.tipo_entrada ?? 'pedido',
                status: item.status ?? 'pendente',
                valor_total: item.valor_total ?? item.total ?? 0,
                id_fornecedor: item.id_fornecedor ?? item.fornecedor_id ?? null,
                fornecedor_nome: item.fornecedor?.razao_social ?? item.fornecedor?.nome ?? item.fornecedor_nome ?? ''
            };
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
            const select = document.getElementById('entradaFilial');
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

        async function carregarPedidos() {
            try {
                const response = await fetch(`${API_CONFIG.BASE_URL}${API_CONFIG.PEDIDOS}`, {
                    method: 'GET',
                    headers: API_CONFIG.getHeaders()
                });

                if (!response.ok) {
                    throw new Error(`Erro ${response.status}: ${response.statusText}`);
                }

                const data = await response.json();
                pedidosCache = normalizarLista(data).map(normalizarPedido);
                preencherSelectPedidos();
            } catch (error) {
                console.error('Erro ao carregar pedidos:', error);
                pedidosCache = [];
                preencherSelectPedidos();
                mostrarNotificacao('Erro ao carregar pedidos: ' + error.message, 'error');
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

                    const data = await response.json();
                    const raw = normalizarLista(data);
                    produtos = raw.map(normalizarProduto).filter(p => p.id_produto);
                    preencherSelectProdutos();
                    return;
                } catch (error) {
                    console.error('Erro ao carregar produtos:', error);
                }
            }

            produtos = [];
            preencherSelectProdutos();
        }

        function normalizarProduto(item) {
            return {
                id_produto: item.id_produto ?? item.id ?? null,
                descricao: item.descricao ?? item.nome ?? '',
                unidade_medida: item.unidade_medida ?? item.unidade ?? ''
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
            const selectNovo = document.getElementById('entradaFornecedor');
            const selectDetalhe = document.getElementById('detalheEntradaFornecedor');
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

            if (entradaDetalhe?.id_fornecedor && selectDetalhe) {
                selectDetalhe.value = String(entradaDetalhe.id_fornecedor);
            }
        }

        function preencherSelectPedidos() {
            const selectNovo = document.getElementById('entradaPedido');
            const selectDetalhe = document.getElementById('detalheEntradaPedido');
            const selects = [selectNovo, selectDetalhe].filter(Boolean);

            if (selects.length === 0) return;

            if (!Array.isArray(pedidosCache) || pedidosCache.length === 0) {
                selects.forEach(select => {
                    select.innerHTML = '<option value=\"\">Nenhum pedido encontrado</option>';
                });
                return;
            }

            selects.forEach(select => {
                select.innerHTML = '<option value=\"\">Selecione o pedido</option>';
                pedidosCache.forEach(pedido => {
                    const id = pedido.id ?? null;
                    if (!id) return;
                    const fornecedor = pedido.fornecedor_nome || (pedido.id_fornecedor ? `Fornecedor #${pedido.id_fornecedor}` : 'Fornecedor');
                    const dataPedido = formatarData(pedido.data_pedido);
                    const option = document.createElement('option');
                    option.value = id;
                    option.textContent = `#${id} - ${fornecedor} - ${dataPedido}`;
                    select.appendChild(option);
                });
            });

            if (entradaDetalhe?.id_pedido && selectDetalhe) {
                selectDetalhe.value = String(entradaDetalhe.id_pedido);
            }
        }

        function preencherSelectProdutos() {
            const select = document.getElementById('entradaItemProduto');
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

        function exibirEntradas(lista) {
            const tbody = document.getElementById('tbodyEntradas');
            if (!Array.isArray(lista) || lista.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Nenhuma entrada encontrada</td></tr>';
                atualizarTotal(0);
                return;
            }

            tbody.innerHTML = lista.map(entrada => {
                const fornecedor = entrada.fornecedor_nome || (entrada.id_fornecedor ? `#${entrada.id_fornecedor}` : '-');
                return `
                    <tr>
                        <td>${entrada.id ?? '-'}</td>
                        <td>${escapeHtml(fornecedor)}</td>
                        <td>${formatarData(entrada.data_entrada)}</td>
                        <td>${escapeHtml(entrada.tipo_entrada)}</td>
                        <td><span class="status-badge status-${entrada.status}">${escapeHtml(entrada.status)}</span></td>
                        <td>${formatarMoeda(entrada.valor_total)}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="abrirDetalhesEntrada(${entrada.id ?? 'null'})" ${entrada.id ? '' : 'disabled'}>
                                <i class="bi bi-eye me-1"></i>Detalhes
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');

            atualizarTotal(lista.length);
        }

        function filtrarEntradas() {
            const termo = document.getElementById('searchInput').value.toLowerCase();
            const status = document.getElementById('filterStatus').value;

            const filtradas = entradas.filter(entrada => {
                const texto = `${entrada.id ?? ''} ${entrada.fornecedor_nome ?? ''}`.toLowerCase();
                const matchTexto = !termo || texto.includes(termo);
                const matchStatus = !status || entrada.status === status;
                return matchTexto && matchStatus;
            });

            exibirEntradas(filtradas);
            atualizarResumo(filtradas);
        }

        function atualizarResumo(lista) {
            const total = lista.length;
            const pendentes = lista.filter(e => e.status === 'pendente').length;
            const conferidas = lista.filter(e => e.status === 'conferido').length;

            document.getElementById('totalEntradasResumo').textContent = total;
            document.getElementById('entradasPendentesResumo').textContent = pendentes;
            document.getElementById('entradasConferidasResumo').textContent = conferidas;
        }

        function atualizarTotal(total) {
            document.getElementById('totalEntradas').textContent = `${total} entrada(s) encontrada(s)`;
        }

        async function salvarEntrada() {
            const tipoEntrada = document.getElementById('entradaTipo').value;
            const pedidoSelecionado = document.getElementById('entradaPedido').value;

            if (tipoEntrada === 'pedido' && !pedidoSelecionado) {
                mostrarNotificacao('Selecione o pedido relacionado.', 'warning');
                return;
            }

            const payload = {
                id_empresa: idEmpresa,
                id_filial: parseInt(document.getElementById('entradaFilial').value || '0', 10),
                id_fornecedor: parseInt(document.getElementById('entradaFornecedor').value || '0', 10),
                data_entrada: formatarDateTimeLocal(document.getElementById('entradaData').value),
                data_recebimento: formatarDateTimeLocal(document.getElementById('entradaRecebimento').value),
                tipo_entrada: tipoEntrada,
                valor_total: parseFloat(document.getElementById('entradaValor').value || '0'),
                observacoes: document.getElementById('entradaObservacoes').value.trim()
            };

            if (pedidoSelecionado) {
                payload.id_pedido = parseInt(pedidoSelecionado, 10);
            }

            if (!payload.id_filial || !payload.id_fornecedor || !payload.data_entrada || !payload.tipo_entrada) {
                mostrarNotificacao('Preencha os campos obrigatorios.', 'warning');
                return;
            }

            mostrarLoading(true);
            try {
                const response = await fetch(`${API_CONFIG.BASE_URL}${API_CONFIG.ENTRADAS}`, {
                    method: 'POST',
                    headers: API_CONFIG.getJsonHeaders(),
                    body: JSON.stringify(payload)
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    throw new Error(errorText || `Erro ${response.status}`);
                }

                modalNovaEntrada.hide();
                document.getElementById('formEntrada').reset();
                definirDatasPadrao();
                mostrarNotificacao('Entrada criada com sucesso!', 'success');
                carregarEntradas();
            } catch (error) {
                console.error('Erro ao criar entrada:', error);
                mostrarNotificacao('Erro ao criar entrada: ' + error.message, 'error');
            } finally {
                mostrarLoading(false);
            }
        }

        function abrirDetalhesEntrada(id) {
            if (!id) {
                mostrarNotificacao('Entrada invalida.', 'warning');
                return;
            }

            entradaSelecionadaId = id;
            modalDetalhesEntrada.show();

            carregarProdutos();
            carregarFornecedores();
            carregarPedidos();
            carregarDetalhesEntrada();
        }

        async function carregarDetalhesEntrada() {
            if (!entradaSelecionadaId) return;

            mostrarLoading(true);
            try {
                const response = await fetch(`${API_CONFIG.BASE_URL}${API_CONFIG.ENTRADAS}/${entradaSelecionadaId}`, {
                    method: 'GET',
                    headers: API_CONFIG.getHeaders()
                });

                if (!response.ok) {
                    throw new Error(`Erro ${response.status}: ${response.statusText}`);
                }

                const data = await response.json();
                entradaDetalhe = normalizarEntradaDetalhe(data);
                renderizarDetalhesEntrada();
            } catch (error) {
                console.error('Erro ao carregar entrada:', error);
                mostrarNotificacao('Erro ao carregar entrada: ' + error.message, 'error');
            } finally {
                mostrarLoading(false);
            }
        }

        function normalizarEntradaDetalhe(data) {
            let origem = data ?? {};
            if (origem.success && origem.data) {
                origem = origem.data;
            } else if (origem.data && origem.data.data) {
                origem = origem.data.data;
            } else if (origem.data) {
                origem = origem.data;
            }

            const itensRaw = origem.itens ?? origem.items ?? origem.itens_entrada ?? [];
            const historicoRaw = origem.historico ?? origem.historicos ?? origem.historico_entrada ?? [];
            const fornecedorObj = origem.fornecedor ?? origem.fornecedor_detalhe ?? null;

            return {
                id: origem.id ?? origem.id_entrada ?? origem.entrada_id ?? entradaSelecionadaId,
                data_entrada: origem.data_entrada ?? origem.data ?? origem.created_at ?? '',
                data_recebimento: origem.data_recebimento ?? origem.recebimento ?? '',
                tipo_entrada: origem.tipo_entrada ?? 'pedido',
                status: origem.status ?? 'pendente',
                valor_total: origem.valor_total ?? origem.total ?? 0,
                id_fornecedor: origem.id_fornecedor ?? origem.fornecedor_id ?? fornecedorObj?.id_fornecedor ?? fornecedorObj?.id ?? null,
                id_pedido: origem.id_pedido ?? origem.pedido_id ?? null,
                observacoes: origem.observacoes ?? origem.descricao ?? '',
                itens: normalizarItensEntrada(itensRaw),
                historico: normalizarHistoricoEntrada(historicoRaw),
                fornecedor: fornecedorObj
            };
        }

        function normalizarItensEntrada(lista) {
            if (!Array.isArray(lista)) return [];
            return lista.map(item => ({
                id_item: item.id_item ?? item.id ?? item.item_id ?? null,
                id_produto: item.id_produto ?? item.produto_id ?? item.produto?.id_produto ?? item.produto?.id ?? null,
                quantidade_recebida: item.quantidade_recebida ?? item.quantidade ?? item.qtd ?? 0,
                preco_unitario: item.preco_unitario ?? item.preco ?? 0,
                produto: item.produto ?? null
            }));
        }

        function normalizarHistoricoEntrada(lista) {
            if (!Array.isArray(lista)) return [];
            return lista.map(item => ({
                id: item.id ?? item.id_historico ?? null,
                acao: item.acao ?? item.tipo ?? '',
                descricao: item.descricao ?? item.detalhe ?? '',
                data: item.data ?? item.created_at ?? item.data_acao ?? ''
            }));
        }

        function renderizarDetalhesEntrada() {
            if (!entradaDetalhe) return;

            const totalItens = entradaDetalhe.itens?.length ?? 0;
            document.getElementById('detalheEntradaTitulo').textContent = `Entrada #${entradaDetalhe.id ?? '-'}`;
            document.getElementById('detalheEntradaSubtitulo').textContent = `Status: ${entradaDetalhe.status} | Itens: ${totalItens}`;

            document.getElementById('detalheEntradaId').value = entradaDetalhe.id ?? '';
            document.getElementById('detalheEntradaTipo').value = entradaDetalhe.tipo_entrada ?? '';
            document.getElementById('detalheEntradaData').value = formatarDateTimeInput(entradaDetalhe.data_entrada);
            document.getElementById('detalheEntradaRecebimento').value = formatarDateTimeInput(entradaDetalhe.data_recebimento);
            document.getElementById('detalheEntradaStatus').value = entradaDetalhe.status ?? 'pendente';
            document.getElementById('detalheEntradaObservacoes').value = entradaDetalhe.observacoes ?? '';

            if (entradaDetalhe.id_pedido) {
                const selectPedido = document.getElementById('detalheEntradaPedido');
                if (selectPedido) {
                    selectPedido.value = String(entradaDetalhe.id_pedido);
                }
            } else {
                const selectPedido = document.getElementById('detalheEntradaPedido');
                if (selectPedido) {
                    selectPedido.value = '';
                }
            }

            if (entradaDetalhe.id_fornecedor) {
                const selectFornecedor = document.getElementById('detalheEntradaFornecedor');
                if (selectFornecedor) {
                    selectFornecedor.value = String(entradaDetalhe.id_fornecedor);
                }
            }

            renderizarItensEntrada();
            renderizarHistoricoEntrada();
        }

        function obterNomeProdutoEntrada(item) {
            if (item.produto?.descricao) return item.produto.descricao;
            if (item.produto?.nome) return item.produto.nome;
            if (item.id_produto) {
                const produto = produtos.find(p => String(p.id_produto) === String(item.id_produto));
                if (produto?.descricao) return produto.descricao;
                if (produto?.nome) return produto.nome;
                return `Produto #${item.id_produto}`;
            }
            return 'Produto';
        }

        function renderizarItensEntrada() {
            const tbody = document.getElementById('tbodyEntradaItens');
            if (!tbody) return;

            if (!Array.isArray(entradaDetalhe.itens) || entradaDetalhe.itens.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">Nenhum item registrado</td></tr>';
                document.getElementById('detalheEntradaValor').value = entradaDetalhe.valor_total ?? 0;
                return;
            }

            let total = 0;
            tbody.innerHTML = entradaDetalhe.itens.map(item => {
                const produtoNome = obterNomeProdutoEntrada(item);
                const subtotal = (parseFloat(item.quantidade_recebida || 0) * parseFloat(item.preco_unitario || 0));
                total += subtotal;
                return `
                    <tr>
                        <td>${escapeHtml(produtoNome)}</td>
                        <td>${item.quantidade_recebida ?? 0}</td>
                        <td>${formatarMoeda(item.preco_unitario)}</td>
                        <td>${formatarMoeda(subtotal)}</td>
                    </tr>
                `;
            }).join('');

            entradaDetalhe.valor_total = total;
            document.getElementById('detalheEntradaValor').value = total.toFixed(2);
        }

        function renderizarHistoricoEntrada() {
            const tbody = document.getElementById('tbodyEntradaHistorico');
            if (!tbody) return;

            if (!Array.isArray(entradaDetalhe.historico) || entradaDetalhe.historico.length === 0) {
                tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted">Nenhum historico registrado</td></tr>';
                return;
            }

            tbody.innerHTML = entradaDetalhe.historico.map(item => {
                return `
                    <tr>
                        <td>${formatarData(item.data)}</td>
                        <td>${escapeHtml(item.acao)}</td>
                        <td>${escapeHtml(item.descricao)}</td>
                    </tr>
                `;
            }).join('');
        }

        async function atualizarEntradaDetalhe() {
            if (!entradaSelecionadaId) return;

            const payload = {
                status: document.getElementById('detalheEntradaStatus').value,
                observacoes: document.getElementById('detalheEntradaObservacoes').value.trim()
            };

            const dataRecebimento = document.getElementById('detalheEntradaRecebimento').value;
            if (dataRecebimento) {
                payload.data_recebimento = formatarDateTimeLocal(dataRecebimento);
            }

            mostrarLoading(true);
            try {
                const response = await fetch(`${API_CONFIG.BASE_URL}${API_CONFIG.ENTRADAS}/${entradaSelecionadaId}`, {
                    method: 'PUT',
                    headers: API_CONFIG.getJsonHeaders(),
                    body: JSON.stringify(payload)
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    throw new Error(errorText || `Erro ${response.status}`);
                }

                mostrarNotificacao('Entrada atualizada com sucesso!', 'success');
                carregarDetalhesEntrada();
                carregarEntradas();
            } catch (error) {
                console.error('Erro ao atualizar entrada:', error);
                mostrarNotificacao('Erro ao atualizar entrada: ' + error.message, 'error');
            } finally {
                mostrarLoading(false);
            }
        }

        async function adicionarItemEntrada() {
            if (!entradaSelecionadaId) return;

            const idProduto = document.getElementById('entradaItemProduto').value;
            const quantidade = document.getElementById('entradaItemQuantidade').value;
            const precoUnitario = document.getElementById('entradaItemPreco').value;

            if (!idProduto || !quantidade) {
                mostrarNotificacao('Selecione o produto e informe a quantidade.', 'warning');
                return;
            }

            const payload = {
                id_produto: parseInt(idProduto, 10),
                quantidade_recebida: parseFloat(quantidade),
                preco_unitario: parseFloat(precoUnitario || '0')
            };

            mostrarLoading(true);
            try {
                const response = await fetch(`${API_CONFIG.BASE_URL}${API_CONFIG.ENTRADAS}/${entradaSelecionadaId}/itens`, {
                    method: 'POST',
                    headers: API_CONFIG.getJsonHeaders(),
                    body: JSON.stringify(payload)
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    throw new Error(errorText || `Erro ${response.status}`);
                }

                document.getElementById('entradaItemProduto').value = '';
                document.getElementById('entradaItemQuantidade').value = '';
                document.getElementById('entradaItemPreco').value = '';

                mostrarNotificacao('Item adicionado com sucesso!', 'success');
                carregarDetalhesEntrada();
                carregarEntradas();
            } catch (error) {
                console.error('Erro ao adicionar item:', error);
                mostrarNotificacao('Erro ao adicionar item: ' + error.message, 'error');
            } finally {
                mostrarLoading(false);
            }
        }

        async function adicionarHistoricoEntrada() {
            if (!entradaSelecionadaId) return;

            const acao = document.getElementById('entradaHistoricoAcao').value.trim();
            const descricao = document.getElementById('entradaHistoricoDescricao').value.trim();

            if (!acao || !descricao) {
                mostrarNotificacao('Informe a acao e a descricao.', 'warning');
                return;
            }

            const payload = { acao, descricao };

            mostrarLoading(true);
            try {
                const response = await fetch(`${API_CONFIG.BASE_URL}${API_CONFIG.ENTRADAS}/${entradaSelecionadaId}/historico`, {
                    method: 'POST',
                    headers: API_CONFIG.getJsonHeaders(),
                    body: JSON.stringify(payload)
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    throw new Error(errorText || `Erro ${response.status}`);
                }

                document.getElementById('entradaHistoricoAcao').value = '';
                document.getElementById('entradaHistoricoDescricao').value = '';

                mostrarNotificacao('Historico registrado com sucesso!', 'success');
                carregarDetalhesEntrada();
            } catch (error) {
                console.error('Erro ao registrar historico:', error);
                mostrarNotificacao('Erro ao registrar historico: ' + error.message, 'error');
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

        function formatarMoeda(valor) {
            const numero = parseFloat(valor || 0);
            return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(numero);
        }

        function formatarDateTimeInput(data) {
            if (!data) return '';
            try {
                const raw = String(data);
                const normalized = raw.includes('T') ? raw : raw.replace(' ', 'T');
                const date = new Date(normalized);
                if (Number.isNaN(date.getTime())) {
                    return '';
                }
                const local = new Date(date.getTime() - date.getTimezoneOffset() * 60000);
                return local.toISOString().slice(0, 16);
            } catch (e) {
                return '';
            }
        }

        function formatarDateTimeLocal(valor) {
            if (!valor) return '';
            if (valor.includes('T')) {
                return `${valor.replace('T', ' ')}:00`;
            }
            return valor;
        }

        function atualizarEntradaTipo() {
            const tipoEntrada = document.getElementById('entradaTipo');
            const pedidoSelect = document.getElementById('entradaPedido');
            if (!tipoEntrada || !pedidoSelect) return;

            const isPedido = tipoEntrada.value === 'pedido';
            pedidoSelect.disabled = !isPedido;
            if (!isPedido) {
                pedidoSelect.value = '';
            }
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
