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

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?php if (!defined('APP_SHELL')) { $pageTitle = 'Gestao de Fornecedores - SAS Multi'; include __DIR__ . '/../components/app-head.php'; } ?>

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

        .pagination-custom .page-link {
            color: var(--primary-color);
            border: 1px solid #dee2e6;
        }

        .pagination-custom .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
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
                        <li class="breadcrumb-item active">Gestao de Fornecedores</li>
                    </ol>
                </nav>
            </div>

            <div class="header-right">
                <!-- Notificacoes -->
                <div class="dropdown">
                    <button class="btn btn-link text-dark position-relative" type="button" data-bs-toggle="dropdown">
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
                        <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Configuracoes</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="#" id="logoutBtn"><i class="bi bi-box-arrow-right me-2"></i>Sair</a></li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Titulo e Acoes -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="page-title">Gestao de Fornecedores</h2>
                <p class="page-subtitle">Cadastre e gerencie seus fornecedores</p>
            </div>
            <button class="btn btn-primary" onclick="abrirModalFornecedor()">
                <i class="bi bi-plus-lg me-2"></i>Novo Fornecedor
            </button>
        </div>

        <!-- Resumo -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card card-custom summary-card">
                    <div class="card-body">
                        <div class="summary-label">Total de fornecedores</div>
                        <div class="summary-value" id="totalFornecedoresResumo">0</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-custom summary-card" style="border-left-color: var(--success-color);">
                    <div class="card-body">
                        <div class="summary-label">Ativos</div>
                        <div class="summary-value" id="fornecedoresAtivosResumo">0</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-custom summary-card" style="border-left-color: var(--danger-color);">
                    <div class="card-body">
                        <div class="summary-label">Inativos</div>
                        <div class="summary-value" id="fornecedoresInativosResumo">0</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de Fornecedores -->
        <div class="card card-custom">
            <div class="card-header-custom">
                <div class="row align-items-center">
                    <div class="col-md-4 mb-2 mb-md-0">
                        <div class="search-box">
                            <i class="bi bi-search"></i>
                            <input type="text" class="form-control" id="searchInput" placeholder="Buscar fornecedor...">
                        </div>
                    </div>
                    <div class="col-md-3 mb-2 mb-md-0">
                        <select class="form-select" id="filterStatus">
                            <option value="">Todos os status</option>
                            <option value="ativo">Ativo</option>
                            <option value="inativo">Inativo</option>
                        </select>
                    </div>
                    <div class="col-md-5 text-md-end">
                        <button class="btn btn-sm btn-outline-primary" onclick="carregarFornecedores()">
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
                                <th>CNPJ</th>
                                <th>Cidade/UF</th>
                                <th>Status</th>
                                <th>Cadastro</th>
                                <th>Acoes</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyFornecedores">
                            <tr>
                                <td colspan="7" class="text-center text-muted">Carregando fornecedores...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted" id="totalFornecedores">0 fornecedor(es) encontrado(s)</div>
                    <nav aria-label="Navegacao de paginas" id="paginationContainer" style="display: none;">
                        <ul class="pagination pagination-custom mb-0"></ul>
                    </nav>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal Novo/Editar Fornecedor -->
    <div class="modal fade" id="modalFornecedor" tabindex="-1" aria-labelledby="modalFornecedorLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalFornecedorLabel">Novo Fornecedor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formFornecedor">
                        <input type="hidden" id="fornecedorId" value="">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Razao Social</label>
                                <input type="text" class="form-control" id="razao_social" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">CNPJ</label>
                                <input type="text" class="form-control" id="cnpj" placeholder="00.000.000/0000-00">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nome Fantasia</label>
                                <input type="text" class="form-control" id="nome_fantasia">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Cidade</label>
                                <input type="text" class="form-control" id="cidade">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">UF</label>
                                <input type="text" class="form-control" id="estado" maxlength="2">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <select class="form-select" id="status">
                                    <option value="ativo">Ativo</option>
                                    <option value="inativo">Inativo</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="salvarFornecedor()">Salvar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Confirmacao -->
    <div class="modal fade" id="modalConfirmacao" tabindex="-1" aria-labelledby="modalConfirmacaoLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalConfirmacaoLabel">Confirmar Exclusao</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza que deseja excluir o fornecedor <strong id="nomeFornecedorExcluir"></strong>?</p>
                    <p class="text-danger"><small>Esta acao nao pode ser desfeita.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="btnConfirmarExclusao">Excluir Fornecedor</button>
                </div>
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
        const idEmpresa = <?php echo $id_empresa; ?>;
        const token = '<?php echo $token; ?>';
        const BASE_URL = '<?= addslashes($config['api_base']) ?>';

        let fornecedores = [];
        let fornecedorEditando = null;
        let modalFornecedor = null;
        let modalConfirmacao = null;
        let currentPage = 1;
        let lastPage = 1;

        const API_CONFIG = {
            BASE_URL: BASE_URL,
            FORNECEDORES: '/api/v1/fornecedores',
            FORNECEDORES_EMPRESA: '/api/v1/fornecedores/empresa',
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

            getFornecedoresEmpresaUrl: function(page = 1) {
                return `${this.BASE_URL}${this.FORNECEDORES_EMPRESA}/${idEmpresa}?page=${page}`;
            },

            getFornecedorUrl: function(id) {
                return `${this.BASE_URL}${this.FORNECEDORES}/${id}`;
            }
        };

        document.addEventListener('DOMContentLoaded', function() {
            modalFornecedor = new bootstrap.Modal(document.getElementById('modalFornecedor'));
            modalConfirmacao = new bootstrap.Modal(document.getElementById('modalConfirmacao'));

            carregarFornecedores();

            document.getElementById('searchInput').addEventListener('input', filtrarFornecedores);
            document.getElementById('filterStatus').addEventListener('change', filtrarFornecedores);

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

        async function carregarFornecedores(page = 1) {
            mostrarLoading(true);

            try {
                const response = await fetch(API_CONFIG.getFornecedoresEmpresaUrl(page), {
                    method: 'GET',
                    headers: API_CONFIG.getHeaders()
                });

                if (!response.ok) {
                    throw new Error(`Erro ${response.status}: ${response.statusText}`);
                }

                let data = null;
                try {
                    data = await response.json();
                } catch (err) {
                    data = null;
                }

                let raw = [];
                let meta = { current_page: 1, last_page: 1, total: 0 };

                if (!data) {
                    raw = [];
                } else if (Array.isArray(data)) {
                    raw = data;
                } else if (data.data && Array.isArray(data.data)) {
                    raw = data.data;
                    meta.current_page = data.current_page ?? meta.current_page;
                    meta.last_page = data.last_page ?? meta.last_page;
                    meta.total = data.total ?? meta.total;
                } else if (data.data && data.data.data && Array.isArray(data.data.data)) {
                    raw = data.data.data;
                    meta.current_page = data.data.current_page ?? meta.current_page;
                    meta.last_page = data.data.last_page ?? meta.last_page;
                    meta.total = data.data.total ?? meta.total;
                } else if (data.success && data.data && data.data.data && Array.isArray(data.data.data)) {
                    raw = data.data.data;
                    meta.current_page = data.data.current_page ?? meta.current_page;
                    meta.last_page = data.data.last_page ?? meta.last_page;
                    meta.total = data.data.total ?? meta.total;
                } else if (data.success && data.data && Array.isArray(data.data)) {
                    raw = data.data;
                    meta.current_page = data.current_page ?? meta.current_page;
                    meta.last_page = data.last_page ?? meta.last_page;
                    meta.total = data.total ?? meta.total;
                } else {
                    const maybeArray = Object.values(data).find(v => Array.isArray(v));
                    raw = maybeArray || [];
                }

                fornecedores = raw.map(f => ({
                    id_fornecedor: f.id_fornecedor ?? f.id ?? null,
                    razao_social: f.razao_social ?? f.nome ?? '',
                    nome_fantasia: f.nome_fantasia ?? '',
                    cnpj: f.cnpj ?? f.documento ?? '',
                    cidade: f.cidade ?? '',
                    estado: f.estado ?? '',
                    status: f.status ?? 'ativo',
                    created_at: f.created_at ?? f.data_cadastro ?? ''
                }));

                currentPage = meta.current_page;
                lastPage = meta.last_page;

                exibirFornecedores(fornecedores);
                atualizarResumo(fornecedores, meta.total);
                atualizarPaginacao({ current_page: currentPage, last_page: lastPage });

            } catch (error) {
                console.error('Erro ao carregar fornecedores:', error);
                mostrarNotificacao('Erro ao carregar fornecedores: ' + error.message, 'error');
                document.getElementById('tbodyFornecedores').innerHTML = '<tr><td colspan="7" class="text-center text-muted">Erro ao carregar dados</td></tr>';
            } finally {
                mostrarLoading(false);
            }
        }

        function exibirFornecedores(lista) {
            const tbody = document.getElementById('tbodyFornecedores');
            const fornecedoresLista = Array.isArray(lista) ? lista : [];

            if (fornecedoresLista.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Nenhum fornecedor encontrado</td></tr>';
                atualizarTotalFornecedores(0);
                return;
            }

            tbody.innerHTML = fornecedoresLista.map(fornecedor => `
                <tr>
                    <td>${fornecedor.id_fornecedor ?? '-'}</td>
                    <td>
                        <div class="fw-semibold">${escapeHtml(fornecedor.razao_social || fornecedor.nome_fantasia || '-')}</div>
                        ${fornecedor.nome_fantasia ? `<small class="text-muted">${escapeHtml(fornecedor.nome_fantasia)}</small>` : ''}
                    </td>
                    <td>${fornecedor.cnpj ? formatarCnpj(fornecedor.cnpj) : '-'}</td>
                    <td>${formatarCidadeEstado(fornecedor.cidade, fornecedor.estado)}</td>
                    <td><span class="status-badge status-${fornecedor.status}">${fornecedor.status === 'ativo' ? 'Ativo' : 'Inativo'}</span></td>
                    <td>${formatarData(fornecedor.created_at)}</td>
                    <td>
                        <div class="d-flex flex-wrap gap-1">
                            <button class="btn btn-sm btn-outline-secondary" onclick="gerenciarFornecedorProdutos(${fornecedor.id_fornecedor})" title="Gerenciar produtos">
                                <i class="bi bi-box-seam me-1"></i>Gerenciar
                            </button>
                            <button class="btn btn-action btn-outline-primary" onclick="editarFornecedor(${fornecedor.id_fornecedor})" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-action btn-outline-danger" onclick="confirmarExclusao(${fornecedor.id_fornecedor}, '${escapeJsString(fornecedor.razao_social || fornecedor.nome_fantasia || '')}')" title="Excluir">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');

            atualizarTotalFornecedores(fornecedoresLista.length);
        }

        function atualizarPaginacao(data) {
            const paginationContainer = document.getElementById('paginationContainer');
            const paginationUl = paginationContainer.querySelector('.pagination');

            if (!data || data.last_page <= 1) {
                paginationContainer.style.display = 'none';
                return;
            }

            paginationContainer.style.display = 'block';

            let paginationHTML = '';
            if (data.current_page > 1) {
                paginationHTML += `
                    <li class="page-item">
                        <a class="page-link" href="#" onclick="carregarFornecedores(${data.current_page - 1})" aria-label="Anterior">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                `;
            }

            for (let i = 1; i <= data.last_page; i++) {
                if (i === data.current_page) {
                    paginationHTML += `
                        <li class="page-item active">
                            <span class="page-link">${i}</span>
                        </li>
                    `;
                } else {
                    paginationHTML += `
                        <li class="page-item">
                            <a class="page-link" href="#" onclick="carregarFornecedores(${i})">${i}</a>
                        </li>
                    `;
                }
            }

            if (data.current_page < data.last_page) {
                paginationHTML += `
                    <li class="page-item">
                        <a class="page-link" href="#" onclick="carregarFornecedores(${data.current_page + 1})" aria-label="Proximo">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                `;
            }

            paginationUl.innerHTML = paginationHTML;
        }

        function abrirModalFornecedor() {
            fornecedorEditando = null;
            document.getElementById('modalFornecedorLabel').textContent = 'Novo Fornecedor';
            document.getElementById('formFornecedor').reset();
            document.getElementById('fornecedorId').value = '';
            document.getElementById('status').value = 'ativo';
            modalFornecedor.show();
        }

        async function editarFornecedor(id) {
            const fornecedor = fornecedores.find(f => String(f.id_fornecedor) === String(id));

            if (!fornecedor) {
                try {
                    const response = await fetch(API_CONFIG.getFornecedorUrl(id), {
                        method: 'GET',
                        headers: API_CONFIG.getHeaders()
                    });
                    if (response.ok) {
                        const data = await response.json();
                        preencherFormularioFornecedor(data);
                        fornecedorEditando = data;
                        modalFornecedor.show();
                        return;
                    }
                } catch (error) {
                    console.error('Erro ao buscar fornecedor:', error);
                }
                mostrarNotificacao('Fornecedor nao encontrado.', 'warning');
                return;
            }

            fornecedorEditando = fornecedor;
            preencherFormularioFornecedor(fornecedor);
            document.getElementById('modalFornecedorLabel').textContent = 'Editar Fornecedor';
            modalFornecedor.show();
        }

        function gerenciarFornecedorProdutos(id) {
            if (!id) {
                mostrarNotificacao('Fornecedor invalido.', 'warning');
                return;
            }
            window.location.href = `?view=adm-fornecedor-produtos&fornecedor=${id}`;
        }

        function preencherFormularioFornecedor(fornecedor) {
            document.getElementById('fornecedorId').value = fornecedor.id_fornecedor ?? fornecedor.id ?? '';
            document.getElementById('razao_social').value = fornecedor.razao_social ?? '';
            document.getElementById('nome_fantasia').value = fornecedor.nome_fantasia ?? '';
            document.getElementById('cnpj').value = fornecedor.cnpj ?? '';
            document.getElementById('cidade').value = fornecedor.cidade ?? '';
            document.getElementById('estado').value = fornecedor.estado ?? '';
            document.getElementById('status').value = fornecedor.status ?? 'ativo';
        }

        async function salvarFornecedor() {
            const payload = {
                razao_social: document.getElementById('razao_social').value.trim(),
                nome_fantasia: document.getElementById('nome_fantasia').value.trim(),
                cnpj: document.getElementById('cnpj').value.trim(),
                cidade: document.getElementById('cidade').value.trim(),
                estado: document.getElementById('estado').value.trim(),
                status: document.getElementById('status').value
            };

            if (!payload.razao_social) {
                mostrarNotificacao('Razao social e obrigatoria.', 'warning');
                return;
            }

            mostrarLoading(true);

            try {
                let response;
                if (fornecedorEditando && (fornecedorEditando.id_fornecedor || fornecedorEditando.id)) {
                    const id = fornecedorEditando.id_fornecedor ?? fornecedorEditando.id;
                    response = await fetch(API_CONFIG.getFornecedorUrl(id), {
                        method: 'PUT',
                        headers: API_CONFIG.getJsonHeaders(),
                        body: JSON.stringify(payload)
                    });
                } else {
                    response = await fetch(`${API_CONFIG.BASE_URL}${API_CONFIG.FORNECEDORES}`, {
                        method: 'POST',
                        headers: API_CONFIG.getJsonHeaders(),
                        body: JSON.stringify({
                            id_empresa: idEmpresa,
                            ...payload
                        })
                    });
                }

                if (!response.ok) {
                    const errorText = await response.text();
                    throw new Error(errorText || `Erro ${response.status}`);
                }

                modalFornecedor.hide();
                mostrarNotificacao('Fornecedor salvo com sucesso!', 'success');
                carregarFornecedores(currentPage);

            } catch (error) {
                console.error('Erro ao salvar fornecedor:', error);
                mostrarNotificacao('Erro ao salvar fornecedor: ' + error.message, 'error');
            } finally {
                mostrarLoading(false);
            }
        }

        function confirmarExclusao(id, nome) {
            document.getElementById('nomeFornecedorExcluir').textContent = nome;
            const btnConfirmar = document.getElementById('btnConfirmarExclusao');
            btnConfirmar.replaceWith(btnConfirmar.cloneNode(true));
            document.getElementById('btnConfirmarExclusao').onclick = () => excluirFornecedor(id);
            modalConfirmacao.show();
        }

        async function excluirFornecedor(id) {
            mostrarLoading(true);

            try {
                const response = await fetch(API_CONFIG.getFornecedorUrl(id), {
                    method: 'DELETE',
                    headers: API_CONFIG.getHeaders()
                });

                if (!response.ok && response.status !== 204) {
                    throw new Error(`Erro ${response.status}: ${response.statusText}`);
                }

                modalConfirmacao.hide();
                mostrarNotificacao('Fornecedor excluido com sucesso!', 'success');
                carregarFornecedores(currentPage);
            } catch (error) {
                console.error('Erro ao excluir fornecedor:', error);
                mostrarNotificacao('Erro ao excluir fornecedor: ' + error.message, 'error');
                modalConfirmacao.hide();
            } finally {
                mostrarLoading(false);
            }
        }

        function filtrarFornecedores() {
            const termoBusca = document.getElementById('searchInput').value.toLowerCase();
            const statusFiltro = document.getElementById('filterStatus').value;

            if (!termoBusca && !statusFiltro) {
                exibirFornecedores(fornecedores);
                atualizarResumo(fornecedores);
                return;
            }

            const filtrados = fornecedores.filter(fornecedor => {
                const texto = `${fornecedor.razao_social || ''} ${fornecedor.nome_fantasia || ''} ${fornecedor.cnpj || ''} ${fornecedor.cidade || ''} ${fornecedor.estado || ''}`.toLowerCase();
                const matchBusca = !termoBusca || texto.includes(termoBusca);
                const matchStatus = !statusFiltro || fornecedor.status === statusFiltro;
                return matchBusca && matchStatus;
            });

            exibirFornecedores(filtrados);
            atualizarResumo(filtrados);
        }

        function atualizarResumo(lista, totalAPI) {
            const total = typeof totalAPI === 'number' && totalAPI > 0 ? totalAPI : (lista ? lista.length : 0);
            const ativos = (lista || []).filter(f => f.status === 'ativo').length;
            const inativos = (lista || []).filter(f => f.status === 'inativo').length;

            document.getElementById('totalFornecedoresResumo').textContent = total;
            document.getElementById('fornecedoresAtivosResumo').textContent = ativos;
            document.getElementById('fornecedoresInativosResumo').textContent = inativos;
        }

        function atualizarTotalFornecedores(total) {
            document.getElementById('totalFornecedores').textContent = `${total} fornecedor(es) encontrado(s)`;
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

        function formatarCidadeEstado(cidade, estado) {
            const cidadeVal = cidade ? cidade.trim() : '';
            const estadoVal = estado ? estado.trim() : '';
            if (!cidadeVal && !estadoVal) {
                return '-';
            }
            if (cidadeVal && estadoVal) {
                return `${cidadeVal}/${estadoVal}`;
            }
            return cidadeVal || estadoVal;
        }

        function formatarCnpj(cnpj) {
            const digits = String(cnpj || '').replace(/\D/g, '');
            if (digits.length !== 14) {
                return cnpj;
            }
            return `${digits.slice(0,2)}.${digits.slice(2,5)}.${digits.slice(5,8)}/${digits.slice(8,12)}-${digits.slice(12)}`;
        }

        function formatarData(data) {
            if (!data) return '';
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

        function escapeHtml(value) {
            if (value === null || value === undefined) return '';
            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/\"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function escapeJsString(value) {
            return String(value || '').replace(/\\/g, '\\\\').replace(/'/g, "\\'");
        }
    </script>
    <?php if (!defined('APP_SHELL')) { include __DIR__ . '/../components/app-foot.php'; } ?>
</body>
</html>
