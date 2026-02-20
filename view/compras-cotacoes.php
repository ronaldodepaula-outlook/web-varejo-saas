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
    <?php if (!defined('APP_SHELL')) { $pageTitle = 'Compras - Cotacoes'; include __DIR__ . '/../components/app-head.php'; } ?>
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

        .status-aberta { background: rgba(52, 152, 219, 0.1); color: var(--primary-color); }
        .status-enviada { background: rgba(243, 156, 18, 0.15); color: var(--warning-color); }
        .status-encerrada { background: rgba(39, 174, 96, 0.1); color: var(--success-color); }

        .cotacao-tabs {
            border-bottom: 1px solid #e6e6e6;
        }

        .cotacao-tabs .nav-link {
            color: var(--secondary-color);
            background: #f4f6f8;
            border: 1px solid #e1e4e8;
            border-bottom: none;
            margin-right: 6px;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
            font-weight: 600;
        }

        .cotacao-tabs .nav-link i {
            margin-right: 6px;
        }

        .cotacao-tabs .nav-link:hover {
            background: #e9eef3;
            color: var(--secondary-color);
        }

        .cotacao-tabs .nav-link.active {
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
                        <li class="breadcrumb-item active">Compras - Cotacoes</li>
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
                <h2 class="page-title"><i class="bi bi-clipboard-check me-2 text-primary"></i>Cotacoes de Compra</h2>
                <p class="page-subtitle">Crie e acompanhe cotacoes com fornecedores.</p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNovaCotacao">
                <i class="bi bi-plus-circle me-2"></i>Nova Cotacao
            </button>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card card-custom summary-card">
                    <div class="card-body">
                        <div class="summary-label">Total de cotacoes</div>
                        <div class="summary-value" id="totalCotacoesResumo">0</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-custom summary-card" style="border-left-color: var(--warning-color);">
                    <div class="card-body">
                        <div class="summary-label">Em andamento</div>
                        <div class="summary-value" id="cotacoesEmAndamentoResumo">0</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-custom summary-card" style="border-left-color: var(--success-color);">
                    <div class="card-body">
                        <div class="summary-label">Encerradas</div>
                        <div class="summary-value" id="cotacoesEncerradasResumo">0</div>
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
                            <input type="text" class="form-control" id="searchInput" placeholder="Buscar cotacao...">
                        </div>
                    </div>
                    <div class="col-md-3 mb-2 mb-md-0">
                        <select class="form-select" id="filterStatus">
                            <option value="">Todos os status</option>
                            <option value="aberta">Aberta</option>
                            <option value="enviada">Enviada</option>
                            <option value="encerrada">Encerrada</option>
                        </select>
                    </div>
                    <div class="col-md-5 text-md-end">
                        <button class="btn btn-sm btn-outline-primary" onclick="carregarCotacoes()">
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
                                <th>Data</th>
                                <th>Validade</th>
                                <th>Status</th>
                                <th>Fornecedores</th>
                                <th>Observacoes</th>
                                <th>Acoes</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyCotacoes">
                            <tr>
                                <td colspan="7" class="text-center text-muted">Carregando cotacoes...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="text-muted" id="totalCotacoes">0 cotacao(oes) encontrada(s)</div>
            </div>
        </div>
    </main>

    <div class="modal fade" id="modalNovaCotacao" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nova Cotacao</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formCotacao">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Filial *</label>
                                <select class="form-select" id="cotacaoFilial" required>
                                    <option value="">Carregando filiais...</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Data da cotacao *</label>
                                <input type="date" class="form-control" id="cotacaoData" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Validade</label>
                                <input type="date" class="form-control" id="cotacaoValidade">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Observacoes</label>
                                <textarea class="form-control" id="cotacaoObservacoes" rows="3"></textarea>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="salvarCotacao()">Salvar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDetalhesCotacao" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="detalheCotacaoTitulo">Cotacao</h5>
                        <small class="text-muted" id="detalheCotacaoSubtitulo"></small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs cotacao-tabs" id="cotacaoTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tabResumoBtn" data-bs-toggle="tab" data-bs-target="#tabResumo" type="button" role="tab">
                                <i class="bi bi-card-text"></i>Resumo
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tabItensBtn" data-bs-toggle="tab" data-bs-target="#tabItens" type="button" role="tab">
                                <i class="bi bi-list-check"></i>Itens
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tabFornecedoresBtn" data-bs-toggle="tab" data-bs-target="#tabFornecedores" type="button" role="tab">
                                <i class="bi bi-truck"></i>Fornecedores
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tabRespostasBtn" data-bs-toggle="tab" data-bs-target="#tabRespostas" type="button" role="tab">
                                <i class="bi bi-chat-left-text"></i>Respostas
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content pt-3">
                        <div class="tab-pane fade show active" id="tabResumo" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">ID</label>
                                    <input type="text" class="form-control" id="detalheCotacaoId" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Data da cotacao</label>
                                    <input type="text" class="form-control" id="detalheCotacaoData" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Validade</label>
                                    <input type="date" class="form-control" id="detalheCotacaoValidade">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" id="detalheCotacaoStatus">
                                        <option value="aberta">Aberta</option>
                                        <option value="enviada">Enviada</option>
                                        <option value="encerrada">Encerrada</option>
                                        <option value="cancelada">Cancelada</option>
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Observacoes</label>
                                    <textarea class="form-control" id="detalheCotacaoObservacoes" rows="3"></textarea>
                                </div>
                                <div class="col-md-12 d-flex gap-2">
                                    <button class="btn btn-primary" type="button" onclick="atualizarCotacaoDetalhe()">
                                        <i class="bi bi-save me-2"></i>Salvar alteracoes
                                    </button>
                                    <button class="btn btn-outline-secondary" type="button" onclick="carregarDetalhesCotacao()">
                                        <i class="bi bi-arrow-clockwise me-2"></i>Atualizar dados
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="tabItens" role="tabpanel">
                            <div class="row g-3 align-items-end mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Produto</label>
                                    <select class="form-select" id="itemProdutoSelect">
                                        <option value="">Carregando produtos...</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Quantidade</label>
                                    <input type="number" class="form-control" id="itemQuantidade" step="0.01">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Unidade</label>
                                    <input type="text" class="form-control" id="itemUnidade" placeholder="UN">
                                </div>
                                <div class="col-md-12">
                                    <button class="btn btn-success" type="button" onclick="adicionarItemCotacao()">
                                        <i class="bi bi-plus-circle me-2"></i>Adicionar item
                                    </button>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover table-custom">
                                    <thead>
                                        <tr>
                                            <th>ID Item</th>
                                            <th>Produto</th>
                                            <th>Quantidade</th>
                                            <th>Unidade</th>
                                            <th>Acoes</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodyItensCotacao">
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">Carregando itens...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="tabFornecedores" role="tabpanel">
                            <div class="row g-3 align-items-end mb-3">
                                <div class="col-md-8">
                                    <label class="form-label">Fornecedor</label>
                                    <select class="form-select" id="fornecedorSelect">
                                        <option value="">Carregando fornecedores...</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <button class="btn btn-success" type="button" onclick="adicionarFornecedorCotacao()">
                                        <i class="bi bi-person-plus me-2"></i>Adicionar fornecedor
                                    </button>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover table-custom">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Fornecedor</th>
                                            <th>Status</th>
                                            <th>Contato</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodyFornecedoresCotacao">
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">Carregando fornecedores...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="tabRespostas" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover table-custom">
                                    <thead>
                                        <tr>
                                            <th>Fornecedor</th>
                                            <th>Produto</th>
                                            <th>Quantidade</th>
                                            <th>Preco</th>
                                            <th>Prazo (dias)</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodyRespostasCotacao">
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">Carregando respostas...</td>
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

        let cotacoes = [];
        let produtos = [];
        let fornecedores = [];
        let cotacaoDetalhe = null;
        let cotacaoSelecionadaId = null;
        let modalNovaCotacao = null;
        let modalDetalhesCotacao = null;

        const API_CONFIG = {
            BASE_URL: BASE_URL,
            COTACOES: '/api/v1/compras/cotacoes',
            FORNECEDORES_EMPRESA: '/api/v1/fornecedores/empresa',
            PRODUTOS_EMPRESA: '/api/v1/produtos/empresa',
            PRODUTOS_EMPRESA_ALT: '/api/v1/empresas',
            FILIAIS_EMPRESA: '/api/filiais/empresa',
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
            modalNovaCotacao = new bootstrap.Modal(document.getElementById('modalNovaCotacao'));
            modalDetalhesCotacao = new bootstrap.Modal(document.getElementById('modalDetalhesCotacao'));
            carregarCotacoes();
            carregarFiliais();

            document.getElementById('searchInput').addEventListener('input', filtrarCotacoes);
            document.getElementById('filterStatus').addEventListener('change', filtrarCotacoes);

            const logoutBtn = document.getElementById('logoutBtn');
            if (logoutBtn) {
                logoutBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    fazerLogoff();
                });
            }

            definirDatasPadrao();
        });

        function definirDatasPadrao() {
            const hoje = new Date();
            const validade = new Date(hoje.getTime());
            validade.setDate(validade.getDate() + 30);

            document.getElementById('cotacaoData').value = hoje.toISOString().slice(0, 10);
            document.getElementById('cotacaoValidade').value = validade.toISOString().slice(0, 10);
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

        async function carregarCotacoes() {
            mostrarLoading(true);
            try {
                const response = await fetch(`${API_CONFIG.BASE_URL}${API_CONFIG.COTACOES}`, {
                    method: 'GET',
                    headers: API_CONFIG.getHeaders()
                });

                if (!response.ok) {
                    throw new Error(`Erro ${response.status}: ${response.statusText}`);
                }

                const data = await response.json();
                const raw = normalizarLista(data);

                cotacoes = raw.map(normalizarCotacao);
                exibirCotacoes(cotacoes);
                atualizarResumo(cotacoes);
            } catch (error) {
                console.error('Erro ao carregar cotacoes:', error);
                mostrarNotificacao('Erro ao carregar cotacoes: ' + error.message, 'error');
                document.getElementById('tbodyCotacoes').innerHTML = '<tr><td colspan="7" class="text-center text-muted">Erro ao carregar dados</td></tr>';
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

        function normalizarCotacao(item) {
            const fornecedores = Array.isArray(item.fornecedores) ? item.fornecedores.length : (item.fornecedores_count ?? 0);
            return {
                id: item.id ?? item.id_cotacao ?? item.cotacao_id ?? null,
                data_cotacao: item.data_cotacao ?? item.data ?? item.created_at ?? '',
                data_validade: item.data_validade ?? item.validade ?? '',
                status: item.status ?? 'aberta',
                observacoes: item.observacoes ?? item.descricao ?? '',
                fornecedores: fornecedores
            };
        }

        async function carregarProdutos() {
            if (produtos.length > 0) {
                preencherSelectProdutos();
                return;
            }

            const urls = [
                `${API_CONFIG.BASE_URL}${API_CONFIG.PRODUTOS_EMPRESA_ALT}/${idEmpresa}/produtos`,
                `${API_CONFIG.BASE_URL}${API_CONFIG.PRODUTOS_EMPRESA}/${idEmpresa}`
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

        async function carregarFornecedores() {
            if (fornecedores.length > 0) {
                preencherSelectFornecedores();
                return;
            }

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

        function preencherSelectProdutos() {
            const select = document.getElementById('itemProdutoSelect');
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
                option.dataset.unidade = produto.unidade_medida || '';
                select.appendChild(option);
            });

            select.onchange = () => {
                const unidade = select.options[select.selectedIndex]?.dataset?.unidade || '';
                const unidadeInput = document.getElementById('itemUnidade');
                if (unidadeInput && unidade) {
                    unidadeInput.value = unidade;
                }
            };
        }

        function preencherSelectFornecedores() {
            const select = document.getElementById('fornecedorSelect');
            if (!select) return;

            if (!Array.isArray(fornecedores) || fornecedores.length === 0) {
                select.innerHTML = '<option value=\"\">Nenhum fornecedor encontrado</option>';
                return;
            }

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
        }

        function abrirDetalhesCotacao(id) {
            if (!id) {
                mostrarNotificacao('Cotacao invalida.', 'warning');
                return;
            }

            cotacaoSelecionadaId = id;
            modalDetalhesCotacao.show();

            carregarProdutos();
            carregarFornecedores();
            carregarDetalhesCotacao();
        }

        async function carregarDetalhesCotacao() {
            if (!cotacaoSelecionadaId) {
                return;
            }

            mostrarLoading(true);
            try {
                const response = await fetch(`${API_CONFIG.BASE_URL}${API_CONFIG.COTACOES}/${cotacaoSelecionadaId}`, {
                    method: 'GET',
                    headers: API_CONFIG.getHeaders()
                });

                if (!response.ok) {
                    throw new Error(`Erro ${response.status}: ${response.statusText}`);
                }

                const data = await response.json();
                cotacaoDetalhe = normalizarCotacaoDetalhe(data);

                renderizarDetalhesCotacao();
            } catch (error) {
                console.error('Erro ao carregar cotacao:', error);
                mostrarNotificacao('Erro ao carregar cotacao: ' + error.message, 'error');
            } finally {
                mostrarLoading(false);
            }
        }

        function normalizarCotacaoDetalhe(data) {
            let origem = data ?? {};
            if (origem.success && origem.data) {
                origem = origem.data;
            } else if (origem.data && origem.data.data) {
                origem = origem.data.data;
            } else if (origem.data) {
                origem = origem.data;
            }

            const itensRaw = origem.itens ?? origem.items ?? origem.itens_cotacao ?? [];
            const fornecedoresRaw = origem.fornecedores ?? origem.cotacao_fornecedores ?? origem.fornecedores_cotacao ?? [];

            const itens = normalizarItensCotacao(itensRaw);
            const fornecedoresLista = normalizarFornecedoresCotacao(fornecedoresRaw);
            const respostas = extrairRespostasCotacao(origem, itens, fornecedoresLista);

            return {
                id: origem.id ?? origem.id_cotacao ?? origem.cotacao_id ?? cotacaoSelecionadaId,
                data_cotacao: origem.data_cotacao ?? origem.data ?? origem.created_at ?? '',
                data_validade: origem.data_validade ?? origem.validade ?? '',
                status: origem.status ?? 'aberta',
                observacoes: origem.observacoes ?? origem.descricao ?? '',
                itens: itens,
                fornecedores: fornecedoresLista,
                respostas: respostas
            };
        }

        function normalizarItensCotacao(lista) {
            if (!Array.isArray(lista)) return [];
            return lista.map(item => ({
                id_item: item.id_item ?? item.id ?? item.item_id ?? null,
                id_produto: item.id_produto ?? item.produto_id ?? (item.produto?.id_produto ?? item.produto?.id) ?? null,
                quantidade: item.quantidade ?? item.qtd ?? item.quantidade_solicitada ?? 0,
                unidade_medida: item.unidade_medida ?? item.unidade ?? item.produto?.unidade_medida ?? '',
                produto: item.produto ?? null,
                respostas: Array.isArray(item.respostas) ? item.respostas : []
            }));
        }

        function normalizarFornecedoresCotacao(lista) {
            if (!Array.isArray(lista)) return [];
            return lista.map(item => {
                const fornecedor = item.fornecedor ?? item;
                return {
                    id_fornecedor: item.id_fornecedor ?? item.fornecedor_id ?? fornecedor.id_fornecedor ?? fornecedor.id ?? null,
                    razao_social: fornecedor.razao_social ?? fornecedor.nome_fantasia ?? fornecedor.nome ?? '',
                    nome_fantasia: fornecedor.nome_fantasia ?? '',
                    status: item.status ?? fornecedor.status ?? '',
                    contato: fornecedor.contato ?? fornecedor.email ?? '',
                    id_cotacao_fornecedor: item.id_cotacao_fornecedor ?? item.cotacao_fornecedor_id ?? item.id ?? null,
                    respostas: Array.isArray(item.respostas) ? item.respostas : []
                };
            });
        }

        function extrairRespostasCotacao(origem, itens, fornecedoresLista) {
            let respostas = [];

            if (Array.isArray(origem.respostas)) {
                respostas = respostas.concat(origem.respostas.map(resposta => normalizarResposta(resposta)));
            }

            fornecedoresLista.forEach(fornecedor => {
                if (Array.isArray(fornecedor.respostas)) {
                    fornecedor.respostas.forEach(resposta => {
                        respostas.push(normalizarResposta(resposta, fornecedor));
                    });
                }
            });

            itens.forEach(item => {
                if (Array.isArray(item.respostas)) {
                    item.respostas.forEach(resposta => {
                        respostas.push(normalizarResposta(resposta, null, item));
                    });
                }
            });

            return respostas;
        }

        function normalizarResposta(resposta, fornecedorContexto, itemContexto) {
            const fornecedorNome = fornecedorContexto?.razao_social ?? resposta.fornecedor?.razao_social ?? resposta.fornecedor_nome ?? '';
            const produtoContexto = itemContexto?.produto ?? resposta.produto ?? null;
            return {
                id_produto: resposta.id_produto ?? resposta.produto_id ?? produtoContexto?.id_produto ?? produtoContexto?.id ?? null,
                produto_nome: produtoContexto?.descricao ?? resposta.produto_nome ?? '',
                quantidade: resposta.quantidade ?? resposta.quantidade_solicitada ?? itemContexto?.quantidade ?? 0,
                preco_unitario: resposta.preco_unitario ?? resposta.preco ?? 0,
                prazo_entrega_item: resposta.prazo_entrega_item ?? resposta.prazo ?? '',
                status: resposta.status ?? (resposta.selecionado ? 'selecionada' : ''),
                fornecedor: fornecedorNome || fornecedorContexto?.nome_fantasia || ''
            };
        }

        function renderizarDetalhesCotacao() {
            if (!cotacaoDetalhe) return;

            document.getElementById('detalheCotacaoTitulo').textContent = `Cotacao #${cotacaoDetalhe.id ?? '-'}`;
            const totalItens = cotacaoDetalhe.itens?.length ?? 0;
            const totalFornecedores = cotacaoDetalhe.fornecedores?.length ?? 0;
            document.getElementById('detalheCotacaoSubtitulo').textContent = `Status: ${cotacaoDetalhe.status} | Itens: ${totalItens} | Fornecedores: ${totalFornecedores}`;

            document.getElementById('detalheCotacaoId').value = cotacaoDetalhe.id ?? '';
            document.getElementById('detalheCotacaoData').value = formatarData(cotacaoDetalhe.data_cotacao);
            document.getElementById('detalheCotacaoValidade').value = formatarDataInput(cotacaoDetalhe.data_validade);
            document.getElementById('detalheCotacaoStatus').value = cotacaoDetalhe.status ?? 'aberta';
            document.getElementById('detalheCotacaoObservacoes').value = cotacaoDetalhe.observacoes ?? '';

            renderizarItensCotacao();
            renderizarFornecedoresCotacao();
            renderizarRespostasCotacao();
        }

        function renderizarItensCotacao() {
            const tbody = document.getElementById('tbodyItensCotacao');
            if (!tbody) return;

            if (!Array.isArray(cotacaoDetalhe.itens) || cotacaoDetalhe.itens.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Nenhum item adicionado</td></tr>';
                return;
            }

            tbody.innerHTML = cotacaoDetalhe.itens.map((item, index) => {
                const produtoNome = obterNomeProduto(item);
                const idItem = item.id_item ?? '';
                const rowId = idItem ? idItem : `tmp-${index}`;
                const disabled = idItem ? '' : 'disabled';
                return `
                    <tr>
                        <td>${idItem || '-'}</td>
                        <td>${escapeHtml(produtoNome)}</td>
                        <td><input type="number" class="form-control form-control-sm" id="itemQtd-${rowId}" value="${item.quantidade ?? 0}" ${disabled}></td>
                        <td><input type="text" class="form-control form-control-sm" id="itemUnd-${rowId}" value="${escapeHtml(item.unidade_medida || '')}" ${disabled}></td>
                        <td class="d-flex gap-2 flex-wrap">
                            <button class="btn btn-sm btn-outline-primary" onclick="atualizarItemCotacao('${idItem}', '${rowId}')" ${disabled}>
                                <i class="bi bi-save me-1"></i>Salvar
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="removerItemCotacao('${idItem}')" ${disabled}>
                                <i class="bi bi-trash me-1"></i>Excluir
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        function renderizarFornecedoresCotacao() {
            const tbody = document.getElementById('tbodyFornecedoresCotacao');
            if (!tbody) return;

            if (!Array.isArray(cotacaoDetalhe.fornecedores) || cotacaoDetalhe.fornecedores.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">Nenhum fornecedor associado</td></tr>';
                return;
            }

            tbody.innerHTML = cotacaoDetalhe.fornecedores.map(fornecedor => `
                <tr>
                    <td>${fornecedor.id_fornecedor ?? '-'}</td>
                    <td>${escapeHtml(fornecedor.razao_social || fornecedor.nome_fantasia || 'Fornecedor')}</td>
                    <td>${escapeHtml(fornecedor.status || '-')}</td>
                    <td>${escapeHtml(fornecedor.contato || '-')}</td>
                </tr>
            `).join('');
        }

        function renderizarRespostasCotacao() {
            const tbody = document.getElementById('tbodyRespostasCotacao');
            if (!tbody) return;

            if (!Array.isArray(cotacaoDetalhe.respostas) || cotacaoDetalhe.respostas.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Nenhuma resposta registrada</td></tr>';
                return;
            }

            tbody.innerHTML = cotacaoDetalhe.respostas.map(resposta => {
                const produtoNome = resposta.produto_nome || obterNomeProduto({ id_produto: resposta.id_produto }) || '-';
                return `
                    <tr>
                        <td>${escapeHtml(resposta.fornecedor || '-')}</td>
                        <td>${escapeHtml(produtoNome)}</td>
                        <td>${resposta.quantidade ?? '-'}</td>
                        <td>${formatarMoeda(resposta.preco_unitario || 0)}</td>
                        <td>${resposta.prazo_entrega_item ?? '-'}</td>
                        <td>${escapeHtml(resposta.status || '-')}</td>
                    </tr>
                `;
            }).join('');
        }

        function obterNomeProduto(item) {
            const produtoObj = item.produto ?? null;
            if (produtoObj?.descricao) {
                return produtoObj.descricao;
            }

            const produtoId = item.id_produto ?? null;
            const produto = produtos.find(p => String(p.id_produto) === String(produtoId));
            return produto?.descricao || (produtoId ? `Produto #${produtoId}` : 'Produto');
        }

        async function adicionarItemCotacao() {
            if (!cotacaoSelecionadaId) return;

            const produtoId = document.getElementById('itemProdutoSelect').value;
            const quantidade = document.getElementById('itemQuantidade').value;
            const unidade = document.getElementById('itemUnidade').value.trim();

            if (!produtoId || !quantidade) {
                mostrarNotificacao('Informe produto e quantidade.', 'warning');
                return;
            }

            mostrarLoading(true);
            try {
                const response = await fetch(`${API_CONFIG.BASE_URL}${API_CONFIG.COTACOES}/${cotacaoSelecionadaId}/itens`, {
                    method: 'POST',
                    headers: API_CONFIG.getJsonHeaders(),
                    body: JSON.stringify({
                        id_produto: parseInt(produtoId, 10),
                        quantidade: parseFloat(quantidade),
                        unidade_medida: unidade || undefined
                    })
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    throw new Error(errorText || `Erro ${response.status}`);
                }

                document.getElementById('itemQuantidade').value = '';
                document.getElementById('itemUnidade').value = '';
                document.getElementById('itemProdutoSelect').value = '';

                mostrarNotificacao('Item adicionado com sucesso!', 'success');
                carregarDetalhesCotacao();
            } catch (error) {
                console.error('Erro ao adicionar item:', error);
                mostrarNotificacao('Erro ao adicionar item: ' + error.message, 'error');
            } finally {
                mostrarLoading(false);
            }
        }

        async function atualizarItemCotacao(idItem, rowId) {
            if (!cotacaoSelecionadaId || !idItem) return;

            const key = rowId || idItem;
            const quantidade = document.getElementById(`itemQtd-${key}`)?.value;
            const unidade = document.getElementById(`itemUnd-${key}`)?.value?.trim();

            if (!quantidade) {
                mostrarNotificacao('Quantidade invalida.', 'warning');
                return;
            }

            mostrarLoading(true);
            try {
                const response = await fetch(`${API_CONFIG.BASE_URL}${API_CONFIG.COTACOES}/${cotacaoSelecionadaId}/itens/${idItem}`, {
                    method: 'PUT',
                    headers: API_CONFIG.getJsonHeaders(),
                    body: JSON.stringify({
                        quantidade: parseFloat(quantidade),
                        unidade_medida: unidade || undefined
                    })
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    throw new Error(errorText || `Erro ${response.status}`);
                }

                mostrarNotificacao('Item atualizado com sucesso!', 'success');
                carregarDetalhesCotacao();
            } catch (error) {
                console.error('Erro ao atualizar item:', error);
                mostrarNotificacao('Erro ao atualizar item: ' + error.message, 'error');
            } finally {
                mostrarLoading(false);
            }
        }

        async function removerItemCotacao(idItem) {
            if (!cotacaoSelecionadaId || !idItem) return;
            if (!confirm('Deseja remover este item da cotacao?')) {
                return;
            }

            mostrarLoading(true);
            try {
                const response = await fetch(`${API_CONFIG.BASE_URL}${API_CONFIG.COTACOES}/${cotacaoSelecionadaId}/itens/${idItem}`, {
                    method: 'DELETE',
                    headers: API_CONFIG.getHeaders()
                });

                if (!response.ok && response.status !== 204) {
                    const errorText = await response.text();
                    throw new Error(errorText || `Erro ${response.status}`);
                }

                mostrarNotificacao('Item removido com sucesso!', 'success');
                carregarDetalhesCotacao();
            } catch (error) {
                console.error('Erro ao remover item:', error);
                mostrarNotificacao('Erro ao remover item: ' + error.message, 'error');
            } finally {
                mostrarLoading(false);
            }
        }

        async function adicionarFornecedorCotacao() {
            if (!cotacaoSelecionadaId) return;
            const fornecedorId = document.getElementById('fornecedorSelect').value;

            if (!fornecedorId) {
                mostrarNotificacao('Selecione um fornecedor.', 'warning');
                return;
            }

            mostrarLoading(true);
            try {
                const response = await fetch(`${API_CONFIG.BASE_URL}${API_CONFIG.COTACOES}/${cotacaoSelecionadaId}/fornecedores`, {
                    method: 'POST',
                    headers: API_CONFIG.getJsonHeaders(),
                    body: JSON.stringify({
                        id_fornecedor: parseInt(fornecedorId, 10)
                    })
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    throw new Error(errorText || `Erro ${response.status}`);
                }

                document.getElementById('fornecedorSelect').value = '';
                mostrarNotificacao('Fornecedor adicionado com sucesso!', 'success');
                carregarDetalhesCotacao();
            } catch (error) {
                console.error('Erro ao adicionar fornecedor:', error);
                mostrarNotificacao('Erro ao adicionar fornecedor: ' + error.message, 'error');
            } finally {
                mostrarLoading(false);
            }
        }

        async function atualizarCotacaoDetalhe() {
            if (!cotacaoSelecionadaId) return;

            const payload = {
                descricao: document.getElementById('detalheCotacaoObservacoes').value.trim(),
                status: document.getElementById('detalheCotacaoStatus').value,
                data_validade: document.getElementById('detalheCotacaoValidade').value
            };

            mostrarLoading(true);
            try {
                const response = await fetch(`${API_CONFIG.BASE_URL}${API_CONFIG.COTACOES}/${cotacaoSelecionadaId}`, {
                    method: 'PUT',
                    headers: API_CONFIG.getJsonHeaders(),
                    body: JSON.stringify(payload)
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    throw new Error(errorText || `Erro ${response.status}`);
                }

                mostrarNotificacao('Cotacao atualizada com sucesso!', 'success');
                carregarCotacoes();
                carregarDetalhesCotacao();
            } catch (error) {
                console.error('Erro ao atualizar cotacao:', error);
                mostrarNotificacao('Erro ao atualizar cotacao: ' + error.message, 'error');
            } finally {
                mostrarLoading(false);
            }
        }
        async function carregarFiliais() {
            const select = document.getElementById('cotacaoFilial');
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

        function exibirCotacoes(lista) {
            const tbody = document.getElementById('tbodyCotacoes');
            if (!Array.isArray(lista) || lista.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Nenhuma cotacao encontrada</td></tr>';
                atualizarTotal(0);
                return;
            }

            tbody.innerHTML = lista.map(cotacao => `
                <tr>
                    <td>${cotacao.id ?? '-'}</td>
                    <td>${formatarData(cotacao.data_cotacao)}</td>
                    <td>${formatarData(cotacao.data_validade)}</td>
                    <td><span class="status-badge status-${cotacao.status}">${escapeHtml(cotacao.status)}</span></td>
                    <td>${cotacao.fornecedores ?? 0}</td>
                    <td>${escapeHtml(cotacao.observacoes || '-')}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" onclick="abrirDetalhesCotacao(${cotacao.id ?? 'null'})" ${cotacao.id ? '' : 'disabled'}>
                            <i class="bi bi-eye me-1"></i>Detalhes
                        </button>
                    </td>
                </tr>
            `).join('');

            atualizarTotal(lista.length);
        }

        function filtrarCotacoes() {
            const termo = document.getElementById('searchInput').value.toLowerCase();
            const status = document.getElementById('filterStatus').value;

            const filtradas = cotacoes.filter(cotacao => {
                const texto = `${cotacao.id ?? ''} ${cotacao.observacoes ?? ''}`.toLowerCase();
                const matchTexto = !termo || texto.includes(termo);
                const matchStatus = !status || cotacao.status === status;
                return matchTexto && matchStatus;
            });

            exibirCotacoes(filtradas);
            atualizarResumo(filtradas);
        }

        function atualizarResumo(lista) {
            const total = lista.length;
            const emAndamento = lista.filter(c => c.status !== 'encerrada').length;
            const encerradas = lista.filter(c => c.status === 'encerrada').length;

            document.getElementById('totalCotacoesResumo').textContent = total;
            document.getElementById('cotacoesEmAndamentoResumo').textContent = emAndamento;
            document.getElementById('cotacoesEncerradasResumo').textContent = encerradas;
        }

        function atualizarTotal(total) {
            document.getElementById('totalCotacoes').textContent = `${total} cotacao(oes) encontrada(s)`;
        }

        async function salvarCotacao() {
            const payload = {
                id_empresa: idEmpresa,
                id_filial: parseInt(document.getElementById('cotacaoFilial').value || '0', 10),
                data_cotacao: document.getElementById('cotacaoData').value,
                data_validade: document.getElementById('cotacaoValidade').value,
                observacoes: document.getElementById('cotacaoObservacoes').value.trim()
            };

            if (!payload.id_filial || !payload.data_cotacao) {
                mostrarNotificacao('Preencha os campos obrigatorios.', 'warning');
                return;
            }

            mostrarLoading(true);
            try {
                const response = await fetch(`${API_CONFIG.BASE_URL}${API_CONFIG.COTACOES}`, {
                    method: 'POST',
                    headers: API_CONFIG.getJsonHeaders(),
                    body: JSON.stringify(payload)
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    throw new Error(errorText || `Erro ${response.status}`);
                }

                modalNovaCotacao.hide();
                document.getElementById('formCotacao').reset();
                definirDatasPadrao();
                mostrarNotificacao('Cotacao criada com sucesso!', 'success');
                carregarCotacoes();
            } catch (error) {
                console.error('Erro ao criar cotacao:', error);
                mostrarNotificacao('Erro ao criar cotacao: ' + error.message, 'error');
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
