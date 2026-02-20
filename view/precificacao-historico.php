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
    <?php if (!defined('APP_SHELL')) { $pageTitle = 'Precificacao - Historico'; include __DIR__ . '/../components/app-head.php'; } ?>
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

        .badge-tipo {
            font-size: 0.75rem;
            font-weight: 600;
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
                        <li class="breadcrumb-item active">Historico de Precos</li>
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
                <h2 class="page-title"><i class="bi bi-clock-history me-2 text-primary"></i>Historico de Precos</h2>
                <p class="page-subtitle">Auditoria completa de alteracoes de custo e venda.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <button class="btn btn-outline-primary" type="button" onclick="carregarHistorico()">
                    <i class="bi bi-arrow-clockwise me-2"></i>Atualizar
                </button>
                <button class="btn btn-primary" type="button" onclick="abrirModalHistorico()">
                    <i class="bi bi-plus-circle me-2"></i>Novo Registro
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
                        <label class="form-label">Produto</label>
                        <select class="form-select" id="filtroProduto">
                            <option value="">Carregando produtos...</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tipo de alteracao</label>
                        <select class="form-select" id="filtroTipo">
                            <option value="">Todos</option>
                            <option value="custo">Custo</option>
                            <option value="venda">Venda</option>
                            <option value="promocao">Promocao</option>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Periodo</label>
                        <div class="input-group">
                            <input type="date" class="form-control" id="filtroDataInicio">
                            <span class="input-group-text">ate</span>
                            <input type="date" class="form-control" id="filtroDataFim">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-custom">
            <div class="card-header-custom d-flex justify-content-between align-items-center">
                <div>
                    <strong>Registros</strong>
                    <div class="text-muted small" id="totalHistorico">0 registro(s)</div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-custom">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Produto</th>
                                <th>Tipo</th>
                                <th>Preco anterior</th>
                                <th>Preco novo</th>
                                <th>Ajuste</th>
                                <th>Motivo</th>
                                <th>Acoes</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyHistorico">
                            <tr>
                                <td colspan="8" class="text-center text-muted">Carregando registros...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <div class="modal fade" id="modalHistorico" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalHistoricoTitulo">Novo registro</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formHistorico">
                        <input type="hidden" id="historicoId">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Produto *</label>
                                <select class="form-select" id="historicoProduto" required>
                                    <option value="">Carregando produtos...</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tipo *</label>
                                <select class="form-select" id="historicoTipo" required>
                                    <option value="custo">Custo</option>
                                    <option value="venda">Venda</option>
                                    <option value="promocao">Promocao</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Preco anterior *</label>
                                <input type="number" class="form-control" id="historicoPrecoAnterior" step="0.01" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Preco novo *</label>
                                <input type="number" class="form-control" id="historicoPrecoNovo" step="0.01" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Fornecedor</label>
                                <select class="form-select" id="historicoFornecedor">
                                    <option value="">Selecione</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Motivo</label>
                                <input type="text" class="form-control" id="historicoMotivo">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Data da alteracao</label>
                                <input type="datetime-local" class="form-control" id="historicoData">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-danger" id="btnExcluirHistorico" onclick="excluirHistorico()" style="display:none;">
                        <i class="bi bi-trash me-2"></i>Excluir
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="salvarHistorico()">Salvar</button>
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

        let historico = [];
        let produtos = [];
        let fornecedores = [];
        let modalHistorico = null;

        const API_CONFIG = {
            BASE_URL: BASE_URL,
            HISTORICO: '/api/v1/precificacao/historico',
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
            modalHistorico = new bootstrap.Modal(document.getElementById('modalHistorico'));
            carregarProdutos();
            carregarFornecedores();
            carregarHistorico();

            document.getElementById('filtroProduto').addEventListener('change', carregarHistorico);
            document.getElementById('filtroTipo').addEventListener('change', carregarHistorico);
            document.getElementById('filtroDataInicio').addEventListener('change', carregarHistorico);
            document.getElementById('filtroDataFim').addEventListener('change', carregarHistorico);

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

        async function carregarHistorico() {
            mostrarLoading(true);
            try {
                const filtros = {
                    id_empresa: idEmpresa,
                    id_produto: document.getElementById('filtroProduto').value,
                    tipo_alteracao: document.getElementById('filtroTipo').value,
                    data_inicio: document.getElementById('filtroDataInicio').value,
                    data_fim: document.getElementById('filtroDataFim').value
                };

                const query = buildQuery(filtros);
                const response = await fetch(`${API_CONFIG.BASE_URL}${API_CONFIG.HISTORICO}?${query}`, {
                    method: 'GET',
                    headers: API_CONFIG.getHeaders()
                });

                if (!response.ok) {
                    throw new Error(`Erro ${response.status}: ${response.statusText}`);
                }

                const data = await parseJsonResponse(response);
                const raw = normalizarLista(data);
                historico = raw.map(normalizarHistorico);
                renderizarHistorico();
            } catch (error) {
                console.error('Erro ao carregar historico:', error);
                mostrarNotificacao('Erro ao carregar historico: ' + error.message, 'error');
                document.getElementById('tbodyHistorico').innerHTML = '<tr><td colspan="8" class="text-center text-muted">Erro ao carregar dados</td></tr>';
            } finally {
                mostrarLoading(false);
            }
        }

        function renderizarHistorico() {
            const tbody = document.getElementById('tbodyHistorico');
            const totalEl = document.getElementById('totalHistorico');
            if (!Array.isArray(historico) || historico.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">Nenhum registro encontrado</td></tr>';
                if (totalEl) totalEl.textContent = '0 registro(s)';
                return;
            }

            tbody.innerHTML = historico.map(item => {
                const badgeClass = {
                    custo: 'bg-warning',
                    venda: 'bg-success',
                    promocao: 'bg-info'
                }[item.tipo_alteracao] || 'bg-secondary';

                return `
                    <tr>
                        <td>${formatarDataHora(item.data_alteracao)}</td>
                        <td>${escapeHtml(item.produto_nome)}</td>
                        <td><span class="badge ${badgeClass} badge-tipo">${escapeHtml(item.tipo_alteracao)}</span></td>
                        <td>${formatarMoeda(item.preco_anterior)}</td>
                        <td>${formatarMoeda(item.preco_novo)}</td>
                        <td>${formatarPercentual(item.percentual_ajuste)}</td>
                        <td>${escapeHtml(item.motivo)}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="editarHistorico(${item.id})">
                                <i class="bi bi-pencil"></i>
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');

            if (totalEl) totalEl.textContent = `${historico.length} registro(s)`;
        }

        function abrirModalHistorico() {
            document.getElementById('formHistorico').reset();
            document.getElementById('historicoId').value = '';
            document.getElementById('modalHistoricoTitulo').textContent = 'Novo registro';
            document.getElementById('btnExcluirHistorico').style.display = 'none';
            modalHistorico.show();
        }

        function editarHistorico(id) {
            const item = historico.find(h => h.id === id);
            if (!item) return;

            document.getElementById('historicoId').value = item.id;
            document.getElementById('historicoProduto').value = item.id_produto || '';
            document.getElementById('historicoTipo').value = item.tipo_alteracao || 'venda';
            document.getElementById('historicoPrecoAnterior').value = item.preco_anterior ?? '';
            document.getElementById('historicoPrecoNovo').value = item.preco_novo ?? '';
            document.getElementById('historicoFornecedor').value = item.id_fornecedor || '';
            document.getElementById('historicoMotivo').value = item.motivo || '';
            document.getElementById('historicoData').value = formatarDateTimeInput(item.data_alteracao);

            document.getElementById('modalHistoricoTitulo').textContent = `Editar registro #${item.id}`;
            document.getElementById('btnExcluirHistorico').style.display = 'inline-flex';
            modalHistorico.show();
        }

        async function salvarHistorico() {
            const id = document.getElementById('historicoId').value;
            const payload = {
                id_empresa: idEmpresa,
                id_produto: parseInt(document.getElementById('historicoProduto').value || '0', 10),
                tipo_alteracao: document.getElementById('historicoTipo').value,
                preco_anterior: parseFloat(document.getElementById('historicoPrecoAnterior').value || '0'),
                preco_novo: parseFloat(document.getElementById('historicoPrecoNovo').value || '0'),
                motivo: document.getElementById('historicoMotivo').value.trim()
            };

            const fornecedor = document.getElementById('historicoFornecedor').value;
            if (fornecedor) {
                payload.id_fornecedor = parseInt(fornecedor, 10);
            }

            const dataAlteracao = document.getElementById('historicoData').value;
            if (dataAlteracao) {
                payload.data_alteracao = formatarDateTimeLocal(dataAlteracao);
            }

            if (!payload.id_produto || !payload.tipo_alteracao) {
                mostrarNotificacao('Preencha os campos obrigatorios.', 'warning');
                return;
            }

            mostrarLoading(true);
            try {
                const url = id ? `${API_CONFIG.BASE_URL}${API_CONFIG.HISTORICO}/${id}` : `${API_CONFIG.BASE_URL}${API_CONFIG.HISTORICO}`;
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

                modalHistorico.hide();
                mostrarNotificacao('Registro salvo com sucesso!', 'success');
                carregarHistorico();
            } catch (error) {
                console.error('Erro ao salvar historico:', error);
                mostrarNotificacao('Erro ao salvar historico: ' + error.message, 'error');
            } finally {
                mostrarLoading(false);
            }
        }

        async function excluirHistorico() {
            const id = document.getElementById('historicoId').value;
            if (!id) return;

            if (!confirm('Deseja excluir este registro?')) return;

            mostrarLoading(true);
            try {
                const response = await fetch(`${API_CONFIG.BASE_URL}${API_CONFIG.HISTORICO}/${id}`, {
                    method: 'DELETE',
                    headers: API_CONFIG.getHeaders()
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    throw new Error(errorText || `Erro ${response.status}`);
                }

                modalHistorico.hide();
                mostrarNotificacao('Registro excluido.', 'success');
                carregarHistorico();
            } catch (error) {
                console.error('Erro ao excluir historico:', error);
                mostrarNotificacao('Erro ao excluir historico: ' + error.message, 'error');
            } finally {
                mostrarLoading(false);
            }
        }

        async function carregarProdutos() {
            const select = document.getElementById('filtroProduto');
            const selectModal = document.getElementById('historicoProduto');
            const selects = [select, selectModal].filter(Boolean);

            selects.forEach(el => el.innerHTML = '<option value="">Carregando produtos...</option>');

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

            selects.forEach(el => el.innerHTML = '<option value="">Nenhum produto encontrado</option>');
        }

        function preencherSelectProdutos() {
            const select = document.getElementById('filtroProduto');
            const selectModal = document.getElementById('historicoProduto');
            const selects = [select, selectModal].filter(Boolean);

            selects.forEach(el => {
                el.innerHTML = '<option value="">Selecione o produto</option>';
                produtos.forEach(produto => {
                    const option = document.createElement('option');
                    option.value = produto.id_produto;
                    option.textContent = `${produto.descricao} (#${produto.id_produto})`;
                    el.appendChild(option);
                });
            });
        }

        async function carregarFornecedores() {
            const select = document.getElementById('historicoFornecedor');
            if (!select) return;

            select.innerHTML = '<option value="">Carregando fornecedores...</option>';
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

                select.innerHTML = '<option value="">Selecione</option>';
                fornecedores.forEach(fornecedor => {
                    const option = document.createElement('option');
                    option.value = fornecedor.id_fornecedor;
                    option.textContent = `${fornecedor.razao_social}`;
                    select.appendChild(option);
                });
            } catch (error) {
                console.error('Erro ao carregar fornecedores:', error);
                select.innerHTML = '<option value="">Nenhum fornecedor encontrado</option>';
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

        function normalizarHistorico(item) {
            const precoAnterior = parseFloat(item.preco_anterior ?? item.precoAnterior ?? 0);
            const precoNovo = parseFloat(item.preco_novo ?? item.precoNovo ?? 0);
            let percentual = null;
            if (precoAnterior) {
                percentual = ((precoNovo - precoAnterior) / precoAnterior) * 100;
            }

            const produtoObj = item.produto && typeof item.produto === 'object' ? item.produto : null;
            const produtoNome = item.produto_nome
                ?? item.nome_produto
                ?? item.descricao
                ?? produtoObj?.descricao
                ?? produtoObj?.nome
                ?? produtoObj?.produto
                ?? (typeof item.produto === 'string' ? item.produto : '')
                ?? '';

            return {
                id: item.id_historico ?? item.id ?? null,
                id_produto: item.id_produto ?? item.produto_id ?? null,
                produto_nome: produtoNome,
                tipo_alteracao: item.tipo_alteracao ?? item.tipo ?? '',
                preco_anterior: precoAnterior,
                preco_novo: precoNovo,
                percentual_ajuste: item.percentual_ajuste ?? percentual,
                motivo: item.motivo ?? '',
                id_fornecedor: item.id_fornecedor ?? item.fornecedor_id ?? null,
                data_alteracao: item.data_alteracao ?? item.created_at ?? ''
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

        function formatarMoeda(valor) {
            const numero = parseFloat(valor || 0);
            return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(numero);
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

        function formatarPercentual(valor) {
            if (valor === null || valor === undefined || valor === '') return '-';
            const numero = parseFloat(valor);
            if (Number.isNaN(numero)) return '-';
            return `${numero.toFixed(2)}%`;
        }

        function formatarDateTimeInput(data) {
            if (!data) return '';
            try {
                const normalized = String(data).includes('T') ? data : String(data).replace(' ', 'T');
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
