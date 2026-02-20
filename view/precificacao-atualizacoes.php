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
    <?php if (!defined('APP_SHELL')) { $pageTitle = 'Precificacao - Atualizacoes'; include __DIR__ . '/../components/app-head.php'; } ?>
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

        .status-badge {
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-rascunho { background: rgba(52, 152, 219, 0.1); color: var(--primary-color); }
        .status-processado { background: rgba(39, 174, 96, 0.1); color: var(--success-color); }
        .status-cancelado { background: rgba(231, 76, 60, 0.1); color: var(--danger-color); }

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
                        <li class="breadcrumb-item">Precificacao</li>
                        <li class="breadcrumb-item active">Atualizacoes de Precos</li>
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
                <h2 class="page-title"><i class="bi bi-arrow-repeat me-2 text-primary"></i>Atualizacoes de Precos</h2>
                <p class="page-subtitle">Gerencie lotes de reajuste e campanhas promocionais.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <button class="btn btn-outline-primary" type="button" onclick="carregarAtualizacoes()">
                    <i class="bi bi-arrow-clockwise me-2"></i>Atualizar
                </button>
                <button class="btn btn-primary" type="button" onclick="abrirModalAtualizacao()">
                    <i class="bi bi-plus-circle me-2"></i>Novo Lote
                </button>
            </div>
        </div>

        <div class="card card-custom mb-4">
            <div class="card-header-custom">
                <strong>Filtros</strong>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select class="form-select" id="filtroStatus">
                            <option value="">Todos</option>
                            <option value="rascunho">Rascunho</option>
                            <option value="processado">Processado</option>
                            <option value="cancelado">Cancelado</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tipo</label>
                        <select class="form-select" id="filtroTipo">
                            <option value="">Todos</option>
                            <option value="custo_fornecedor">Custo por fornecedor</option>
                            <option value="custo_avulso">Custo avulso</option>
                            <option value="venda_geral">Venda geral</option>
                            <option value="promocao">Promocao</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Fornecedor</label>
                        <select class="form-select" id="filtroFornecedor">
                            <option value="">Todos</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-custom">
            <div class="card-header-custom d-flex justify-content-between align-items-center">
                <div>
                    <strong>Lotes de Atualizacao</strong>
                    <div class="text-muted small" id="totalAtualizacoes">0 lote(s)</div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-custom">
                        <thead>
                            <tr>
                                <th>Lote</th>
                                <th>Descricao</th>
                                <th>Tipo</th>
                                <th>Fornecedor</th>
                                <th>Data</th>
                                <th>Status</th>
                                <th>Acoes</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyAtualizacoes">
                            <tr>
                                <td colspan="7" class="text-center text-muted">Carregando lotes...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
    <div class="modal fade" id="modalAtualizacao" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAtualizacaoTitulo">Novo lote</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formAtualizacao">
                        <input type="hidden" id="atualizacaoId">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Filial *</label>
                                <select class="form-select" id="atualizacaoFilial" required>
                                    <option value="">Carregando filiais...</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tipo *</label>
                                <select class="form-select" id="atualizacaoTipo" required>
                                    <option value="custo_fornecedor">Custo por fornecedor</option>
                                    <option value="custo_avulso">Custo avulso</option>
                                    <option value="venda_geral">Venda geral</option>
                                    <option value="promocao">Promocao</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Data *</label>
                                <input type="date" class="form-control" id="atualizacaoData" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Numero do lote</label>
                                <input type="text" class="form-control" id="atualizacaoNumero">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Descricao</label>
                                <input type="text" class="form-control" id="atualizacaoDescricao">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fornecedor</label>
                                <select class="form-select" id="atualizacaoFornecedor">
                                    <option value="">Selecione</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select class="form-select" id="atualizacaoStatus">
                                    <option value="rascunho">Rascunho</option>
                                    <option value="processado">Processado</option>
                                    <option value="cancelado">Cancelado</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Observacoes</label>
                                <textarea class="form-control" id="atualizacaoObservacoes" rows="2"></textarea>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-danger" id="btnExcluirAtualizacao" onclick="excluirAtualizacao()" style="display:none;">
                        <i class="bi bi-trash me-2"></i>Excluir
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="salvarAtualizacao()">Salvar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDetalhesAtualizacao" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="detalheAtualizacaoTitulo">Lote</h5>
                        <small class="text-muted" id="detalheAtualizacaoSubtitulo"></small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs" id="atualizacaoTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="atualizacaoResumoTab" data-bs-toggle="tab" data-bs-target="#tabResumo" type="button" role="tab">
                                <i class="bi bi-card-text"></i>Resumo
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="atualizacaoItensTab" data-bs-toggle="tab" data-bs-target="#tabItens" type="button" role="tab">
                                <i class="bi bi-list-check"></i>Itens
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content pt-3">
                        <div class="tab-pane fade show active" id="tabResumo" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Numero do lote</label>
                                    <input type="text" class="form-control" id="detalheNumero" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Tipo</label>
                                    <input type="text" class="form-control" id="detalheTipo" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" id="detalheStatus">
                                        <option value="rascunho">Rascunho</option>
                                        <option value="processado">Processado</option>
                                        <option value="cancelado">Cancelado</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Data</label>
                                    <input type="date" class="form-control" id="detalheData">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Descricao</label>
                                    <input type="text" class="form-control" id="detalheDescricao">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Fornecedor</label>
                                    <select class="form-select" id="detalheFornecedor">
                                        <option value="">Selecione</option>
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Observacoes</label>
                                    <input type="text" class="form-control" id="detalheObservacoes">
                                </div>
                                <div class="col-md-12 d-flex gap-2 flex-wrap">
                                    <button class="btn btn-primary" type="button" onclick="atualizarAtualizacaoDetalhe()">
                                        <i class="bi bi-save me-2"></i>Salvar alteracoes
                                    </button>
                                    <button class="btn btn-outline-success" type="button" onclick="processarAtualizacao()">
                                        <i class="bi bi-check-circle me-2"></i>Processar
                                    </button>
                                    <button class="btn btn-outline-danger" type="button" onclick="cancelarAtualizacao()">
                                        <i class="bi bi-x-circle me-2"></i>Cancelar lote
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="tabItens" role="tabpanel">
                            <div class="row g-3 align-items-end mb-3">
                                <div class="col-md-5">
                                    <label class="form-label">Produto</label>
                                    <select class="form-select" id="itemProduto">
                                        <option value="">Carregando produtos...</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Preco custo novo</label>
                                    <input type="number" class="form-control" id="itemPrecoCusto" step="0.01">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Preco venda novo</label>
                                    <input type="number" class="form-control" id="itemPrecoVenda" step="0.01">
                                </div>
                                <div class="col-md-1">
                                    <button class="btn btn-success" type="button" onclick="adicionarItemAtualizacao()">
                                        <i class="bi bi-plus"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover table-custom">
                                    <thead>
                                        <tr>
                                            <th>Produto</th>
                                            <th>Custo anterior</th>
                                            <th>Custo novo</th>
                                            <th>Venda anterior</th>
                                            <th>Venda nova</th>
                                            <th>Status</th>
                                            <th>Acoes</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodyItens">
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">Nenhum item adicionado</td>
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

        let atualizacoes = [];
        let fornecedores = [];
        let produtos = [];
        let atualizacaoDetalhe = null;
        let atualizacaoSelecionadaId = null;
        let modalAtualizacao = null;
        let modalDetalhes = null;

        const API_CONFIG = {
            BASE_URL: BASE_URL,
            ATUALIZACOES: '/api/v1/precificacao/atualizacoes',
            FILIAIS_EMPRESA: '/api/filiais/empresa',
            FORNECEDORES_EMPRESA: '/api/v1/fornecedores/empresa',
            PRODUTOS_EMPRESA: '/api/v1/produtos/empresa',
            PRODUTOS_EMPRESA_ALT: '/api/v1/empresas',
            LOGOUT: '/api/v1/logout',

            getHeaders: function() {
                return {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json',
                    'X-ID-EMPRESA': idEmpresa.toString(),
                    'Content-Type': 'application/json'
                };
            }
        };

        document.addEventListener('DOMContentLoaded', function() {
            modalAtualizacao = new bootstrap.Modal(document.getElementById('modalAtualizacao'));
            modalDetalhes = new bootstrap.Modal(document.getElementById('modalDetalhesAtualizacao'));
            carregarFiliais();
            carregarFornecedores();
            carregarProdutos();
            carregarAtualizacoes();

            document.getElementById('filtroStatus').addEventListener('change', carregarAtualizacoes);
            document.getElementById('filtroTipo').addEventListener('change', carregarAtualizacoes);
            document.getElementById('filtroFornecedor').addEventListener('change', carregarAtualizacoes);

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

        function buildQuery(params) {
            const search = new URLSearchParams();
            Object.entries(params).forEach(([key, value]) => {
                if (value !== null && value !== undefined && value !== '') {
                    search.append(key, value);
                }
            });
            return search.toString();
        }

        async function carregarAtualizacoes() {
            mostrarLoading(true);
            try {
                const filtros = {
                    id_empresa: idEmpresa,
                    status: document.getElementById('filtroStatus').value,
                    tipo_atualizacao: document.getElementById('filtroTipo').value,
                    id_fornecedor: document.getElementById('filtroFornecedor').value
                };

                const query = buildQuery(filtros);
                const response = await fetch(`${API_CONFIG.BASE_URL}${API_CONFIG.ATUALIZACOES}?${query}`, {
                    method: 'GET',
                    headers: API_CONFIG.getHeaders()
                });

                if (!response.ok) {
                    throw new Error(`Erro ${response.status}: ${response.statusText}`);
                }

                const data = await parseJsonResponse(response);
                const raw = normalizarLista(data);
                atualizacoes = raw.map(normalizarAtualizacao);
                renderizarAtualizacoes();
            } catch (error) {
                console.error('Erro ao carregar atualizacoes:', error);
                mostrarNotificacao('Erro ao carregar atualizacoes: ' + error.message, 'error');
                document.getElementById('tbodyAtualizacoes').innerHTML = '<tr><td colspan="7" class="text-center text-muted">Erro ao carregar dados</td></tr>';
            } finally {
                mostrarLoading(false);
            }
        }

        function renderizarAtualizacoes() {
            const tbody = document.getElementById('tbodyAtualizacoes');
            const totalEl = document.getElementById('totalAtualizacoes');
            if (!Array.isArray(atualizacoes) || atualizacoes.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Nenhum lote encontrado</td></tr>';
                if (totalEl) totalEl.textContent = '0 lote(s)';
                return;
            }

            tbody.innerHTML = atualizacoes.map(item => {
                const statusClass = `status-${item.status}`;
                return `
                    <tr>
                        <td>${escapeHtml(item.numero_lote || '-')}</td>
                        <td>${escapeHtml(item.descricao || '-')}</td>
                        <td>${escapeHtml(item.tipo_atualizacao || '-')}</td>
                        <td>${escapeHtml(item.fornecedor_nome || '-')}</td>
                        <td>${formatarData(item.data_atualizacao)}</td>
                        <td><span class="status-badge ${statusClass}">${escapeHtml(item.status)}</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="abrirDetalhesAtualizacao(${item.id})">
                                <i class="bi bi-eye"></i>
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');

            if (totalEl) totalEl.textContent = `${atualizacoes.length} lote(s)`;
        }

        function abrirModalAtualizacao() {
            document.getElementById('formAtualizacao').reset();
            document.getElementById('atualizacaoId').value = '';
            document.getElementById('modalAtualizacaoTitulo').textContent = 'Novo lote';
            document.getElementById('btnExcluirAtualizacao').style.display = 'none';
            modalAtualizacao.show();
        }

        async function salvarAtualizacao() {
            const id = document.getElementById('atualizacaoId').value;
            const payload = {
                id_empresa: idEmpresa,
                id_filial: parseInt(document.getElementById('atualizacaoFilial').value || '0', 10),
                numero_lote: document.getElementById('atualizacaoNumero').value.trim(),
                descricao: document.getElementById('atualizacaoDescricao').value.trim(),
                tipo_atualizacao: document.getElementById('atualizacaoTipo').value,
                data_atualizacao: document.getElementById('atualizacaoData').value,
                status: document.getElementById('atualizacaoStatus').value,
                observacoes: document.getElementById('atualizacaoObservacoes').value.trim()
            };

            const fornecedor = document.getElementById('atualizacaoFornecedor').value;
            if (fornecedor) {
                payload.id_fornecedor = parseInt(fornecedor, 10);
            }

            if (!payload.id_filial || !payload.tipo_atualizacao || !payload.data_atualizacao) {
                mostrarNotificacao('Preencha os campos obrigatorios.', 'warning');
                return;
            }

            mostrarLoading(true);
            try {
                const url = id ? `${API_CONFIG.BASE_URL}${API_CONFIG.ATUALIZACOES}/${id}` : `${API_CONFIG.BASE_URL}${API_CONFIG.ATUALIZACOES}`;
                const method = id ? 'PUT' : 'POST';
                const response = await fetch(url, {
                    method,
                    headers: API_CONFIG.getHeaders(),
                    body: JSON.stringify(payload)
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    throw new Error(errorText || `Erro ${response.status}`);
                }

                modalAtualizacao.hide();
                mostrarNotificacao('Lote salvo com sucesso!', 'success');
                carregarAtualizacoes();
            } catch (error) {
                console.error('Erro ao salvar lote:', error);
                mostrarNotificacao('Erro ao salvar lote: ' + error.message, 'error');
            } finally {
                mostrarLoading(false);
            }
        }

        async function excluirAtualizacao() {
            const id = document.getElementById('atualizacaoId').value;
            if (!id) return;
            if (!confirm('Deseja excluir este lote?')) return;

            mostrarLoading(true);
            try {
                const response = await fetch(`${API_CONFIG.BASE_URL}${API_CONFIG.ATUALIZACOES}/${id}`, {
                    method: 'DELETE',
                    headers: API_CONFIG.getHeaders()
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    throw new Error(errorText || `Erro ${response.status}`);
                }

                modalAtualizacao.hide();
                mostrarNotificacao('Lote excluido.', 'success');
                carregarAtualizacoes();
            } catch (error) {
                console.error('Erro ao excluir lote:', error);
                mostrarNotificacao('Erro ao excluir lote: ' + error.message, 'error');
            } finally {
                mostrarLoading(false);
            }
        }

        function abrirDetalhesAtualizacao(id) {
            atualizacaoSelecionadaId = id;
            modalDetalhes.show();
            carregarDetalhesAtualizacao();
        }

        async function carregarDetalhesAtualizacao() {
            if (!atualizacaoSelecionadaId) return;
            mostrarLoading(true);
            try {
                const response = await fetch(`${API_CONFIG.BASE_URL}${API_CONFIG.ATUALIZACOES}/${atualizacaoSelecionadaId}`, {
                    method: 'GET',
                    headers: API_CONFIG.getHeaders()
                });

                if (!response.ok) {
                    throw new Error(`Erro ${response.status}: ${response.statusText}`);
                }

                const data = await parseJsonResponse(response);
                atualizacaoDetalhe = normalizarAtualizacaoDetalhe(data);
                renderizarDetalhesAtualizacao();
            } catch (error) {
                console.error('Erro ao carregar detalhes:', error);
                mostrarNotificacao('Erro ao carregar detalhes: ' + error.message, 'error');
            } finally {
                mostrarLoading(false);
            }
        }

        function renderizarDetalhesAtualizacao() {
            if (!atualizacaoDetalhe) return;

            document.getElementById('detalheAtualizacaoTitulo').textContent = `Lote ${atualizacaoDetalhe.numero_lote || '#' + atualizacaoDetalhe.id}`;
            document.getElementById('detalheAtualizacaoSubtitulo').textContent = `Status: ${atualizacaoDetalhe.status} | Itens: ${atualizacaoDetalhe.itens.length}`;

            document.getElementById('detalheNumero').value = atualizacaoDetalhe.numero_lote || '';
            document.getElementById('detalheTipo').value = atualizacaoDetalhe.tipo_atualizacao || '';
            document.getElementById('detalheStatus').value = atualizacaoDetalhe.status || 'rascunho';
            document.getElementById('detalheData').value = formatarDataInput(atualizacaoDetalhe.data_atualizacao);
            document.getElementById('detalheDescricao').value = atualizacaoDetalhe.descricao || '';
            document.getElementById('detalheObservacoes').value = atualizacaoDetalhe.observacoes || '';
            document.getElementById('detalheFornecedor').value = atualizacaoDetalhe.id_fornecedor || '';

            renderizarItensAtualizacao();
        }

        function renderizarItensAtualizacao() {
            const tbody = document.getElementById('tbodyItens');
            if (!tbody) return;

            if (!Array.isArray(atualizacaoDetalhe.itens) || atualizacaoDetalhe.itens.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Nenhum item adicionado</td></tr>';
                return;
            }

            tbody.innerHTML = atualizacaoDetalhe.itens.map((item, index) => {
                return `
                    <tr>
                        <td>${escapeHtml(item.produto_nome)}</td>
                        <td>${formatarMoeda(item.preco_custo_anterior)}</td>
                        <td>${formatarMoeda(item.preco_custo_novo)}</td>
                        <td>${formatarMoeda(item.preco_venda_anterior)}</td>
                        <td>${formatarMoeda(item.preco_venda_novo)}</td>
                        <td>${escapeHtml(item.status)}</td>
                        <td>
                            ${item.id_item ? `<button class=\"btn btn-sm btn-outline-danger\" onclick=\"removerItemAtualizacao(${item.id_item})\"><i class=\"bi bi-trash\"></i></button>` : ''}
                        </td>
                    </tr>
                `;
            }).join('');
        }

        async function atualizarAtualizacaoDetalhe() {
            if (!atualizacaoSelecionadaId) return;

            const payload = {
                descricao: document.getElementById('detalheDescricao').value.trim(),
                status: document.getElementById('detalheStatus').value,
                data_atualizacao: document.getElementById('detalheData').value,
                observacoes: document.getElementById('detalheObservacoes').value.trim()
            };

            const fornecedor = document.getElementById('detalheFornecedor').value;
            if (fornecedor) {
                payload.id_fornecedor = parseInt(fornecedor, 10);
            }

            mostrarLoading(true);
            try {
                const response = await fetch(`${API_CONFIG.BASE_URL}${API_CONFIG.ATUALIZACOES}/${atualizacaoSelecionadaId}`, {
                    method: 'PUT',
                    headers: API_CONFIG.getHeaders(),
                    body: JSON.stringify(payload)
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    throw new Error(errorText || `Erro ${response.status}`);
                }

                mostrarNotificacao('Lote atualizado.', 'success');
                carregarDetalhesAtualizacao();
                carregarAtualizacoes();
            } catch (error) {
                console.error('Erro ao atualizar lote:', error);
                mostrarNotificacao('Erro ao atualizar lote: ' + error.message, 'error');
            } finally {
                mostrarLoading(false);
            }
        }

        async function processarAtualizacao() {
            if (!atualizacaoSelecionadaId) return;
            mostrarLoading(true);
            try {
                const response = await fetch(`${API_CONFIG.BASE_URL}${API_CONFIG.ATUALIZACOES}/${atualizacaoSelecionadaId}/processar`, {
                    method: 'POST',
                    headers: API_CONFIG.getHeaders()
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    throw new Error(errorText || `Erro ${response.status}`);
                }

                mostrarNotificacao('Lote processado.', 'success');
                carregarDetalhesAtualizacao();
                carregarAtualizacoes();
            } catch (error) {
                console.error('Erro ao processar lote:', error);
                mostrarNotificacao('Erro ao processar lote: ' + error.message, 'error');
            } finally {
                mostrarLoading(false);
            }
        }

        async function cancelarAtualizacao() {
            if (!atualizacaoSelecionadaId) return;
            mostrarLoading(true);
            try {
                const response = await fetch(`${API_CONFIG.BASE_URL}${API_CONFIG.ATUALIZACOES}/${atualizacaoSelecionadaId}/cancelar`, {
                    method: 'POST',
                    headers: API_CONFIG.getHeaders()
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    throw new Error(errorText || `Erro ${response.status}`);
                }

                mostrarNotificacao('Lote cancelado.', 'success');
                carregarDetalhesAtualizacao();
                carregarAtualizacoes();
            } catch (error) {
                console.error('Erro ao cancelar lote:', error);
                mostrarNotificacao('Erro ao cancelar lote: ' + error.message, 'error');
            } finally {
                mostrarLoading(false);
            }
        }

        async function adicionarItemAtualizacao() {
            if (!atualizacaoSelecionadaId) return;
            const idProduto = document.getElementById('itemProduto').value;
            if (!idProduto) {
                mostrarNotificacao('Selecione um produto.', 'warning');
                return;
            }

            const payload = {
                id_produto: parseInt(idProduto, 10),
                preco_custo_novo: parseFloat(document.getElementById('itemPrecoCusto').value || '0') || null,
                preco_venda_novo: parseFloat(document.getElementById('itemPrecoVenda').value || '0') || null
            };

            mostrarLoading(true);
            try {
                const response = await fetch(`${API_CONFIG.BASE_URL}${API_CONFIG.ATUALIZACOES}/${atualizacaoSelecionadaId}/itens`, {
                    method: 'POST',
                    headers: API_CONFIG.getHeaders(),
                    body: JSON.stringify(payload)
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    throw new Error(errorText || `Erro ${response.status}`);
                }

                document.getElementById('itemProduto').value = '';
                document.getElementById('itemPrecoCusto').value = '';
                document.getElementById('itemPrecoVenda').value = '';

                mostrarNotificacao('Item adicionado.', 'success');
                carregarDetalhesAtualizacao();
            } catch (error) {
                console.error('Erro ao adicionar item:', error);
                mostrarNotificacao('Erro ao adicionar item: ' + error.message, 'error');
            } finally {
                mostrarLoading(false);
            }
        }

        async function removerItemAtualizacao(idItem) {
            if (!atualizacaoSelecionadaId || !idItem) return;
            if (!confirm('Remover este item?')) return;

            mostrarLoading(true);
            try {
                const response = await fetch(`${API_CONFIG.BASE_URL}${API_CONFIG.ATUALIZACOES}/${atualizacaoSelecionadaId}/itens/${idItem}`, {
                    method: 'DELETE',
                    headers: API_CONFIG.getHeaders()
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    throw new Error(errorText || `Erro ${response.status}`);
                }

                mostrarNotificacao('Item removido.', 'success');
                carregarDetalhesAtualizacao();
            } catch (error) {
                console.error('Erro ao remover item:', error);
                mostrarNotificacao('Erro ao remover item: ' + error.message, 'error');
            } finally {
                mostrarLoading(false);
            }
        }

        async function carregarFiliais() {
            const select = document.getElementById('atualizacaoFilial');
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

                const data = await parseJsonResponse(response);
                const filiais = normalizarLista(data);

                select.innerHTML = '<option value="">Selecione a filial</option>';
                filiais.forEach(filial => {
                    const id = filial.id_filial ?? filial.id ?? null;
                    if (!id) return;
                    const nome = filial.nome_filial ?? filial.nome ?? `Filial #${id}`;
                    const option = document.createElement('option');
                    option.value = id;
                    option.textContent = nome;
                    select.appendChild(option);
                });
            } catch (error) {
                console.error('Erro ao carregar filiais:', error);
                select.innerHTML = '<option value="">Nenhuma filial encontrada</option>';
            }
        }

        async function carregarFornecedores() {
            const selectFiltro = document.getElementById('filtroFornecedor');
            const selectModal = document.getElementById('atualizacaoFornecedor');
            const selectDetalhe = document.getElementById('detalheFornecedor');
            const selects = [selectFiltro, selectModal, selectDetalhe].filter(Boolean);

            selects.forEach(el => el.innerHTML = '<option value="">Carregando fornecedores...</option>');

            try {
                const response = await fetch(`${API_CONFIG.BASE_URL}${API_CONFIG.FORNECEDORES_EMPRESA}/${idEmpresa}`, {
                    method: 'GET',
                    headers: API_CONFIG.getHeaders()
                });

                if (!response.ok) {
                    throw new Error(`Erro ${response.status}: ${response.statusText}`);
                }

                const data = await parseJsonResponse(response);
                const raw = normalizarLista(data);
                fornecedores = raw.map(normalizarFornecedor).filter(f => f.id_fornecedor);

                selects.forEach(el => {
                    el.innerHTML = '<option value="">Selecione</option>';
                    fornecedores.forEach(fornecedor => {
                        const option = document.createElement('option');
                        option.value = fornecedor.id_fornecedor;
                        option.textContent = fornecedor.razao_social;
                        el.appendChild(option);
                    });
                });
            } catch (error) {
                console.error('Erro ao carregar fornecedores:', error);
                selects.forEach(el => el.innerHTML = '<option value="">Nenhum fornecedor encontrado</option>');
            }
        }

        async function carregarProdutos() {
            const select = document.getElementById('itemProduto');
            if (!select) return;

            select.innerHTML = '<option value="">Carregando produtos...</option>';
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

            select.innerHTML = '<option value="">Nenhum produto encontrado</option>';
        }

        function preencherSelectProdutos() {
            const select = document.getElementById('itemProduto');
            if (!select) return;

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
            if (data && data.data && data.data.items && Array.isArray(data.data.items)) return data.data.items;
            if (data && data.data && data.data.produtos && Array.isArray(data.data.produtos)) return data.data.produtos;
            return Array.isArray(data) ? data : [];
        }

        function normalizarProduto(item) {
            return {
                id_produto: item.id_produto ?? item.id ?? null,
                descricao: item.descricao ?? item.nome ?? item.name ?? ''
            };
        }

        function normalizarFornecedor(item) {
            return {
                id_fornecedor: item.id_fornecedor ?? item.id ?? null,
                razao_social: item.razao_social ?? item.nome_fantasia ?? item.nome ?? ''
            };
        }

        function normalizarAtualizacao(item) {
            return {
                id: item.id_atualizacao ?? item.id ?? null,
                numero_lote: item.numero_lote ?? item.lote ?? '',
                descricao: item.descricao ?? '',
                tipo_atualizacao: item.tipo_atualizacao ?? '',
                id_fornecedor: item.id_fornecedor ?? null,
                fornecedor_nome: item.fornecedor?.razao_social ?? item.fornecedor_nome ?? '',
                data_atualizacao: item.data_atualizacao ?? item.created_at ?? '',
                status: item.status ?? 'rascunho'
            };
        }

        function normalizarAtualizacaoDetalhe(data) {
            let origem = data ?? {};
            if (origem.success && origem.data) origem = origem.data;
            if (origem.data) origem = origem.data;

            const itensRaw = origem.itens ?? origem.items ?? origem.itens_atualizacao ?? [];

            return {
                id: origem.id_atualizacao ?? origem.id ?? null,
                numero_lote: origem.numero_lote ?? '',
                descricao: origem.descricao ?? '',
                tipo_atualizacao: origem.tipo_atualizacao ?? '',
                id_fornecedor: origem.id_fornecedor ?? null,
                data_atualizacao: origem.data_atualizacao ?? '',
                status: origem.status ?? 'rascunho',
                observacoes: origem.observacoes ?? '',
                itens: Array.isArray(itensRaw) ? itensRaw.map(normalizarItemAtualizacao) : []
            };
        }

        function normalizarItemAtualizacao(item) {
            return {
                id_item: item.id_item ?? item.id ?? null,
                produto_nome: item.produto?.descricao ?? item.nome_produto ?? item.produto_nome ?? '',
                preco_custo_anterior: item.preco_custo_anterior ?? 0,
                preco_custo_novo: item.preco_custo_novo ?? 0,
                preco_venda_anterior: item.preco_venda_anterior ?? 0,
                preco_venda_novo: item.preco_venda_novo ?? 0,
                status: item.status ?? 'pendente'
            };
        }

        async function parseJsonResponse(response) {
            try {
                return await response.json();
            } catch (error) {
                console.error('Erro ao interpretar JSON:', error);
                return null;
            }
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
