<?php
$config = require __DIR__ . '/../funcoes/config.php';

session_start();
if (!isset($_SESSION['authToken'], $_SESSION['usuario'], $_SESSION['empresa'], $_SESSION['licenca'])) {
    header('Location: login.php');
    exit;
}

// Verificar e converter o tipo de dados das variaveis de sessao
$usuario = is_array($_SESSION['usuario']) ? $_SESSION['usuario'] : ['nome' => $_SESSION['usuario']];
$empresa = $_SESSION['empresa'];
$id_empresa = $_SESSION['empresa_id'];
$licenca = $_SESSION['licenca'];
$token = $_SESSION['authToken'];
$segmento = $_SESSION['segmento'] ?? '';

// Extrair nome do usuario de forma segura
$nomeUsuario = '';
if (is_array($usuario)) {
    $nomeUsuario = $usuario['nome'] ?? $usuario['name'] ?? 'Usuario';
} else {
    $nomeUsuario = (string)$usuario;
}

// Primeira letra para o avatar
$inicialUsuario = strtoupper(substr($nomeUsuario, 0, 1));

$fornecedorId = isset($_GET['fornecedor']) ? (int)$_GET['fornecedor'] : 0;
$fornecedorId = $fornecedorId > 0 ? $fornecedorId : 0;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?php if (!defined('APP_SHELL')) { $pageTitle = 'Gerenciar Produtos do Fornecedor'; include __DIR__ . '/../components/app-head.php'; } ?>

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

        .status-ativo { background: rgba(39, 174, 96, 0.1); color: var(--success-color); }
        .status-inativo { background: rgba(231, 76, 60, 0.1); color: var(--danger-color); }

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
        <!-- Header -->
        <header class="main-header">
            <div class="header-left">
                <button class="sidebar-toggle" type="button" title="Abrir ou fechar o menu lateral" aria-label="Abrir ou fechar o menu lateral">
                    <i class="bi bi-list"></i>
                </button>

                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-custom">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="?view=adm-fornecedores">Gestao de Fornecedores</a></li>
                        <li class="breadcrumb-item active">Gerenciar Produtos</li>
                    </ol>
                </nav>
            </div>

            <div class="header-right">
                <!-- Notificacoes -->
                <div class="dropdown">
                    <button class="btn btn-link text-dark position-relative" type="button" data-bs-toggle="dropdown" title="Ver alertas do sistema" aria-label="Ver alertas do sistema">
                        <i class="bi bi-bell" style="font-size: 1.25rem;"></i>
                        <span class="badge bg-danger position-absolute translate-middle rounded-pill" style="font-size: 0.6rem; top: 8px; right: 8px;">3</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><h6 class="dropdown-header">Alertas do Sistema</h6></li>
                        <li><a class="dropdown-item" href="#">2 fornecedores pendentes de validacao</a></li>
                        <li><a class="dropdown-item" href="#">1 fornecedor atualizado hoje</a></li>
                        <li><a class="dropdown-item" href="#">Novas notas fiscais recebidas</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-center" href="#">Ver todos</a></li>
                    </ul>
                </div>

                <!-- Dropdown do Usuario -->
                <div class="dropdown user-dropdown">
                    <div class="user-avatar" data-bs-toggle="dropdown" aria-expanded="false" title="Abrir menu do usuario" aria-label="Abrir menu do usuario">
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
                        <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Configuracoes</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="#" id="logoutBtn"><i class="bi bi-box-arrow-right me-2"></i>Sair</a></li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Titulo e Acoes -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h2 class="page-title"><i class="bi bi-boxes me-2 text-primary"></i>Gerenciar Produtos do Fornecedor</h2>
                <p class="page-subtitle" id="fornecedorResumo"><i class="bi bi-truck me-2"></i>Fornecedor #<?php echo $fornecedorId > 0 ? (int)$fornecedorId : 'N/A'; ?></p>
            </div>
            <div class="d-flex gap-2">
                <a class="btn btn-outline-secondary" href="?view=adm-fornecedores" title="Voltar para a lista de fornecedores">
                    <i class="bi bi-arrow-left me-2"></i>Voltar
                </a>
                <button class="btn btn-primary" onclick="carregarDados()" title="Atualizar dados do fornecedor e produtos">
                    <i class="bi bi-arrow-clockwise me-2"></i>Atualizar
                </button>
            </div>
        </div>

        <!-- Resumo -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card card-custom summary-card">
                    <div class="card-body">
                        <div class="summary-label"><i class="bi bi-link-45deg me-2 text-primary"></i>Produtos vinculados</div>
                        <div class="summary-value" id="totalVinculadosResumo">0</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-custom summary-card" style="border-left-color: var(--danger-color);">
                    <div class="card-body">
                        <div class="summary-label"><i class="bi bi-slash-circle me-2 text-danger"></i>Vinculos inativos</div>
                        <div class="summary-value" id="totalInativosResumo">0</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-custom summary-card" style="border-left-color: var(--success-color);">
                    <div class="card-body">
                        <div class="summary-label"><i class="bi bi-box-seam me-2 text-success"></i>Produtos disponiveis</div>
                        <div class="summary-value" id="totalDisponiveisResumo">0</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Produtos Vinculados -->
            <div class="col-lg-6">
                <div class="card card-custom">
                    <div class="card-header-custom">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div>
                                <h5 class="mb-0"><i class="bi bi-link-45deg me-2 text-primary"></i>Produtos do fornecedor</h5>
                                <small class="text-muted" id="totalVinculados">0 produto(s)</small>
                            </div>
                            <div class="search-box" style="min-width: 240px;">
                                <i class="bi bi-search"></i>
                                <input type="text" class="form-control" id="searchVinculados" placeholder="Buscar produto vinculado..." title="Buscar produto vinculado">
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-custom">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Produto</th>
                                        <th>Status</th>
                                        <th>Acoes</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyVinculados">
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">Carregando produtos...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Produtos Disponiveis -->
            <div class="col-lg-6">
                <div class="card card-custom">
                    <div class="card-header-custom">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div>
                                <h5 class="mb-0"><i class="bi bi-boxes me-2 text-success"></i>Produtos disponiveis</h5>
                                <small class="text-muted" id="totalDisponiveis">0 produto(s)</small>
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <div class="search-box" style="min-width: 240px;">
                                    <i class="bi bi-search"></i>
                                    <input type="text" class="form-control" id="searchDisponiveis" placeholder="Buscar produto disponivel..." title="Buscar produto disponivel">
                                </div>
                                <button class="btn btn-sm btn-success" id="btnAdicionarSelecionados" onclick="adicionarProdutosSelecionados()" title="Adicionar todos os produtos selecionados" disabled>
                                    <i class="bi bi-plus-lg me-1"></i>Adicionar selecionados
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-custom">
                                <thead>
                                    <tr>
                                        <th style="width: 34px;">
                                            <input class="form-check-input" type="checkbox" id="selectAllDisponiveis" title="Selecionar todos os produtos disponiveis" aria-label="Selecionar todos os produtos disponiveis">
                                        </th>
                                        <th>ID</th>
                                        <th>Produto</th>
                                        <th>Categoria</th>
                                        <th>Acoes</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyDisponiveis">
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">Carregando produtos...</td>
                                    </tr>
                                </tbody>
                            </table>
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
        const idEmpresa = <?php echo $id_empresa; ?>;
        const token = '<?php echo $token; ?>';
        const BASE_URL = '<?= addslashes($config['api_base']) ?>';
        const fornecedorId = <?php echo (int)$fornecedorId; ?>;

        let fornecedor = null;
        let produtos = [];
        let relacoes = [];
        let selecionadosDisponiveis = new Set();

        const API_CONFIG = {
            BASE_URL: BASE_URL,
            FORNECEDORES: '/api/v1/fornecedores',
            RELACOES: '/api/v1/fornecedor-produto',
            RELACOES_EMPRESA: '/api/v1/fornecedor-produto/empresa',
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
            },

            getFornecedorUrl: function(id) {
                return `${this.BASE_URL}${this.FORNECEDORES}/${id}`;
            },

            getRelacoesEmpresaUrl: function() {
                return `${this.BASE_URL}${this.RELACOES_EMPRESA}/${idEmpresa}`;
            },

            getRelacaoUrl: function(idFornecedor, idProduto) {
                return `${this.BASE_URL}${this.RELACOES}/fornecedor/${idFornecedor}/produto/${idProduto}`;
            },

            getProdutosUrl: function() {
                return `${this.BASE_URL}/api/v1/produtos/empresa/${idEmpresa}`;
            },

            getProdutosAltUrl: function() {
                return `${this.BASE_URL}/api/v1/empresas/${idEmpresa}/produtos`;
            }
        };

        document.addEventListener('DOMContentLoaded', function() {
            const logoutBtn = document.getElementById('logoutBtn');
            if (logoutBtn) {
                logoutBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    fazerLogoff();
                });
            }

            document.getElementById('searchVinculados').addEventListener('input', renderizarListas);
            document.getElementById('searchDisponiveis').addEventListener('input', renderizarListas);
            document.getElementById('selectAllDisponiveis').addEventListener('change', toggleSelectAllDisponiveis);

            if (!fornecedorId) {
                mostrarNotificacao('Fornecedor nao informado.', 'warning');
                return;
            }

            carregarDados();
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

        async function carregarDados() {
            mostrarLoading(true);
            try {
                await Promise.all([
                    carregarFornecedor(),
                    carregarProdutos(),
                    carregarRelacoes()
                ]);
                renderizarListas();
            } catch (error) {
                console.error('Erro ao carregar dados:', error);
                mostrarNotificacao('Erro ao carregar dados: ' + error.message, 'error');
            } finally {
                mostrarLoading(false);
            }
        }

        async function carregarFornecedor() {
            if (!fornecedorId) {
                return;
            }
            try {
                const response = await fetch(API_CONFIG.getFornecedorUrl(fornecedorId), {
                    method: 'GET',
                    headers: API_CONFIG.getHeaders()
                });
                if (!response.ok) {
                    return;
                }
                const data = await response.json();
                fornecedor = {
                    id_fornecedor: data.id_fornecedor ?? data.id ?? fornecedorId,
                    razao_social: data.razao_social ?? data.nome ?? '',
                    nome_fantasia: data.nome_fantasia ?? '',
                    cnpj: data.cnpj ?? data.documento ?? '',
                    status: data.status ?? 'ativo'
                };
                atualizarResumoFornecedor();
            } catch (error) {
                console.error('Erro ao carregar fornecedor:', error);
            }
        }

        function atualizarResumoFornecedor() {
            const resumo = document.getElementById('fornecedorResumo');
            if (!resumo) return;
            if (!fornecedor) {
                resumo.innerHTML = `<i class="bi bi-truck me-2"></i>Fornecedor #${fornecedorId}`;
                return;
            }
            const nome = fornecedor.razao_social || fornecedor.nome_fantasia || `Fornecedor #${fornecedor.id_fornecedor}`;
            const status = fornecedor.status === 'inativo' ? 'Inativo' : 'Ativo';
            resumo.innerHTML = `<i class="bi bi-truck me-2"></i>${escapeHtml(nome)} (${escapeHtml(status)})`;
        }

        async function carregarProdutos() {
            const urls = [API_CONFIG.getProdutosUrl(), API_CONFIG.getProdutosAltUrl()];
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
                    return;
                } catch (error) {
                    console.error('Erro ao carregar produtos:', error);
                }
            }
            produtos = [];
        }

        async function carregarRelacoes() {
            try {
                const response = await fetch(API_CONFIG.getRelacoesEmpresaUrl(), {
                    method: 'GET',
                    headers: API_CONFIG.getHeaders()
                });
                if (!response.ok) {
                    throw new Error(`Erro ${response.status}: ${response.statusText}`);
                }
                const data = await response.json();
                const raw = normalizarLista(data);
                relacoes = raw
                    .map(normalizarRelacao)
                    .filter(r => String(r.id_fornecedor) === String(fornecedorId));
            } catch (error) {
                console.error('Erro ao carregar relacoes:', error);
                relacoes = [];
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

        function normalizarProduto(p) {
            const produto = (p && typeof p === 'object') ? p : {};
            return {
                id_produto: produto.id_produto ?? produto.id ?? null,
                descricao: produto.descricao ?? produto.nome ?? '',
                categoria: produto.categoria ?? produto.categoria_nome ?? '',
                codigo_barras: produto.codigo_barras ?? produto.codigo ?? '',
                status: produto.status ?? 'ativo'
            };
        }

        function normalizarRelacao(r) {
            const rel = (r && typeof r === 'object') ? r : {};
            const produtoObj = (rel.produto && typeof rel.produto === 'object') ? rel.produto : null;
            const produtoId = rel.id_produto ?? rel.produto_id ?? (produtoObj ? (produtoObj.id_produto ?? produtoObj.id) : (typeof rel.produto === 'number' || typeof rel.produto === 'string' ? rel.produto : null));

            return {
                id_fornecedor: rel.id_fornecedor ?? rel.fornecedor_id ?? rel.fornecedor ?? null,
                id_produto: produtoId,
                status: rel.status ?? 'ativo',
                produto: produtoObj
            };
        }

        function renderizarListas() {
            const termoVinculados = document.getElementById('searchVinculados').value.toLowerCase();
            const termoDisponiveis = document.getElementById('searchDisponiveis').value.toLowerCase();

            const relacaoPorProduto = new Map();
            relacoes.forEach(rel => {
                if (rel.id_produto) {
                    relacaoPorProduto.set(String(rel.id_produto), rel);
                }
            });

            const vinculados = [];
            relacaoPorProduto.forEach(rel => {
                const produtoBase = obterProdutoPorId(rel.id_produto) || normalizarProduto(rel.produto || {});
                vinculados.push({
                    relacao: rel,
                    produto: produtoBase && produtoBase.id_produto ? produtoBase : {
                        id_produto: rel.id_produto,
                        descricao: 'Produto nao encontrado',
                        categoria: '',
                        codigo_barras: ''
                    }
                });
            });

            const disponiveis = produtos.filter(produto => !relacaoPorProduto.has(String(produto.id_produto)));
            const disponiveisIds = new Set(disponiveis.map(produto => String(produto.id_produto)));
            selecionadosDisponiveis.forEach(id => {
                if (!disponiveisIds.has(String(id))) {
                    selecionadosDisponiveis.delete(id);
                }
            });

            const vinculadosFiltrados = termoVinculados
                ? vinculados.filter(item => produtoMatch(item.produto, termoVinculados))
                : vinculados;
            const disponiveisFiltrados = termoDisponiveis
                ? disponiveis.filter(produto => produtoMatch(produto, termoDisponiveis))
                : disponiveis;

            atualizarResumo(vinculados, disponiveis);
            renderizarVinculados(vinculadosFiltrados);
            renderizarDisponiveis(disponiveisFiltrados);
        }

        function obterProdutoPorId(idProduto) {
            return produtos.find(p => String(p.id_produto) === String(idProduto)) || null;
        }

        function produtoMatch(produto, termo) {
            const texto = `${produto.id_produto ?? ''} ${produto.descricao ?? ''} ${produto.categoria ?? ''} ${produto.codigo_barras ?? ''}`.toLowerCase();
            return texto.includes(termo);
        }

        function atualizarResumo(vinculados, disponiveis) {
            const totalVinculados = vinculados.length;
            const totalInativos = vinculados.filter(item => item.relacao.status === 'inativo').length;
            const totalDisponiveis = disponiveis.length;

            document.getElementById('totalVinculadosResumo').textContent = totalVinculados;
            document.getElementById('totalInativosResumo').textContent = totalInativos;
            document.getElementById('totalDisponiveisResumo').textContent = totalDisponiveis;

            document.getElementById('totalVinculados').textContent = `${totalVinculados} produto(s)`;
            document.getElementById('totalDisponiveis').textContent = `${totalDisponiveis} produto(s)`;
        }

        function renderizarVinculados(lista) {
            const tbody = document.getElementById('tbodyVinculados');
            if (!Array.isArray(lista) || lista.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">Nenhum produto vinculado</td></tr>';
                return;
            }

            tbody.innerHTML = lista.map(item => {
                const produto = item.produto || {};
                const status = item.relacao.status === 'inativo' ? 'inativo' : 'ativo';
                const acaoBtn = status === 'ativo'
                    ? `<button class="btn btn-sm btn-outline-warning" onclick="alterarStatusRelacao(${produto.id_produto}, 'inativo')" title="Inativar vinculo com o fornecedor"><i class="bi bi-pause-circle me-1"></i>Inativar</button>`
                    : `<button class="btn btn-sm btn-outline-success" onclick="alterarStatusRelacao(${produto.id_produto}, 'ativo')" title="Ativar vinculo com o fornecedor"><i class="bi bi-check-circle me-1"></i>Ativar</button>`;
                const excluirBtn = `<button class="btn btn-sm btn-outline-danger" onclick="excluirRelacao(${produto.id_produto})" title="Excluir vinculo definitivamente"><i class="bi bi-trash me-1"></i>Excluir</button>`;

                return `
                    <tr>
                        <td>${produto.id_produto ?? '-'}</td>
                        <td>
                            <div class="fw-semibold">${escapeHtml(produto.descricao || 'Produto')}</div>
                            ${produto.codigo_barras ? `<small class="text-muted">${escapeHtml(produto.codigo_barras)}</small>` : ''}
                        </td>
                        <td><span class="status-badge status-${status}">${status === 'ativo' ? 'Ativo' : 'Inativo'}</span></td>
                        <td class="d-flex flex-wrap gap-1">${acaoBtn}${excluirBtn}</td>
                    </tr>
                `;
            }).join('');
        }

        function renderizarDisponiveis(lista) {
            const tbody = document.getElementById('tbodyDisponiveis');
            if (!Array.isArray(lista) || lista.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Nenhum produto disponivel</td></tr>';
                atualizarSelecaoDisponiveis([]);
                return;
            }

            tbody.innerHTML = lista.map(produto => `
                <tr>
                    <td>
                        <input class="form-check-input disponivel-checkbox" type="checkbox" value="${produto.id_produto}" ${selecionadosDisponiveis.has(String(produto.id_produto)) ? 'checked' : ''} onchange="atualizarSelecionadoDisponivel(this)" title="Selecionar produto" aria-label="Selecionar produto">
                    </td>
                    <td>${produto.id_produto ?? '-'}</td>
                    <td>
                        <div class="fw-semibold">${escapeHtml(produto.descricao || 'Produto')}</div>
                        ${produto.codigo_barras ? `<small class="text-muted">${escapeHtml(produto.codigo_barras)}</small>` : ''}
                    </td>
                    <td>${escapeHtml(produto.categoria || '-')}</td>
                    <td>
                        <button class="btn btn-sm btn-success" onclick="adicionarProduto(${produto.id_produto})" title="Adicionar este produto ao fornecedor">
                            <i class="bi bi-plus-circle me-1"></i>Adicionar
                        </button>
                    </td>
                </tr>
            `).join('');

            atualizarSelecaoDisponiveis(lista);
        }

        async function adicionarProduto(idProduto) {
            if (!idProduto) {
                return;
            }
            mostrarLoading(true);
            try {
                await criarRelacaoFornecedorProduto(idProduto);
                mostrarNotificacao('Produto vinculado com sucesso!', 'success');
                await carregarRelacoes();
                renderizarListas();
            } catch (error) {
                console.error('Erro ao vincular produto:', error);
                mostrarNotificacao('Erro ao vincular produto: ' + error.message, 'error');
            } finally {
                mostrarLoading(false);
            }
        }

        async function adicionarProdutosSelecionados() {
            const ids = Array.from(selecionadosDisponiveis);
            if (ids.length === 0) {
                return;
            }

            mostrarLoading(true);
            let sucesso = 0;
            let falhas = 0;

            for (const id of ids) {
                try {
                    await criarRelacaoFornecedorProduto(id);
                    sucesso += 1;
                } catch (error) {
                    falhas += 1;
                    console.error('Erro ao vincular produto:', error);
                }
            }

            selecionadosDisponiveis.clear();
            await carregarRelacoes();
            renderizarListas();

            if (sucesso > 0) {
                mostrarNotificacao(`${sucesso} produto(s) vinculado(s) com sucesso!`, 'success');
            }
            if (falhas > 0) {
                mostrarNotificacao(`${falhas} produto(s) nao puderam ser vinculados.`, 'warning');
            }

            mostrarLoading(false);
        }

        async function alterarStatusRelacao(idProduto, status) {
            if (!idProduto) {
                return;
            }
            mostrarLoading(true);
            try {
                const response = await fetch(API_CONFIG.getRelacaoUrl(fornecedorId, idProduto), {
                    method: 'PUT',
                    headers: API_CONFIG.getJsonHeaders(),
                    body: JSON.stringify({ status: status })
                });
                if (!response.ok) {
                    const errorText = await response.text();
                    throw new Error(errorText || `Erro ${response.status}`);
                }
                mostrarNotificacao('Status atualizado com sucesso!', 'success');
                await carregarRelacoes();
                renderizarListas();
            } catch (error) {
                console.error('Erro ao atualizar status:', error);
                mostrarNotificacao('Erro ao atualizar status: ' + error.message, 'error');
            } finally {
                mostrarLoading(false);
            }
        }

        async function excluirRelacao(idProduto) {
            if (!idProduto) {
                return;
            }
            if (!confirm('Deseja excluir este vinculo?')) {
                return;
            }

            mostrarLoading(true);
            try {
                const response = await fetch(API_CONFIG.getRelacaoUrl(fornecedorId, idProduto), {
                    method: 'DELETE',
                    headers: API_CONFIG.getHeaders()
                });
                if (!response.ok && response.status !== 204) {
                    const errorText = await response.text();
                    throw new Error(errorText || `Erro ${response.status}`);
                }
                mostrarNotificacao('Vinculo excluido com sucesso!', 'success');
                await carregarRelacoes();
                renderizarListas();
            } catch (error) {
                console.error('Erro ao excluir vinculo:', error);
                mostrarNotificacao('Erro ao excluir vinculo: ' + error.message, 'error');
            } finally {
                mostrarLoading(false);
            }
        }

        async function criarRelacaoFornecedorProduto(idProduto) {
            const response = await fetch(`${API_CONFIG.BASE_URL}${API_CONFIG.RELACOES}`, {
                method: 'POST',
                headers: API_CONFIG.getJsonHeaders(),
                body: JSON.stringify({
                    id_fornecedor: fornecedorId,
                    id_produto: idProduto,
                    status: 'ativo'
                })
            });
            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(errorText || `Erro ${response.status}`);
            }
            return response;
        }

        function atualizarSelecionadoDisponivel(checkbox) {
            if (!checkbox) return;
            const id = String(checkbox.value || '');
            if (!id) return;

            if (checkbox.checked) {
                selecionadosDisponiveis.add(id);
            } else {
                selecionadosDisponiveis.delete(id);
            }

            atualizarSelecaoDisponiveis();
        }

        function toggleSelectAllDisponiveis() {
            const selectAll = document.getElementById('selectAllDisponiveis');
            const checkboxes = document.querySelectorAll('.disponivel-checkbox');

            checkboxes.forEach(cb => {
                cb.checked = selectAll.checked;
                const id = String(cb.value || '');
                if (!id) return;
                if (selectAll.checked) {
                    selecionadosDisponiveis.add(id);
                } else {
                    selecionadosDisponiveis.delete(id);
                }
            });

            atualizarSelecaoDisponiveis();
        }

        function atualizarSelecaoDisponiveis(listaAtual) {
            const btn = document.getElementById('btnAdicionarSelecionados');
            if (btn) {
                btn.disabled = selecionadosDisponiveis.size === 0;
            }

            const selectAll = document.getElementById('selectAllDisponiveis');
            if (!selectAll) return;

            const checkboxes = document.querySelectorAll('.disponivel-checkbox');
            if (checkboxes.length === 0) {
                selectAll.checked = false;
                selectAll.indeterminate = false;
                return;
            }

            let marcados = 0;
            checkboxes.forEach(cb => {
                if (cb.checked) marcados += 1;
            });

            if (marcados === 0) {
                selectAll.checked = false;
                selectAll.indeterminate = false;
            } else if (marcados === checkboxes.length) {
                selectAll.checked = true;
                selectAll.indeterminate = false;
            } else {
                selectAll.checked = false;
                selectAll.indeterminate = true;
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
                <button type="button" class="btn-close" data-bs-dismiss="alert" title="Fechar notificacao" aria-label="Fechar notificacao"></button>
            `;

            document.body.appendChild(notification);

            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 5000);
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
