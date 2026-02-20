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
    <?php if (!defined('APP_SHELL')) { $pageTitle = 'Precificacao - Precos Vigentes'; include __DIR__ . '/../components/app-head.php'; } ?>
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

        .status-sim { background: rgba(39, 174, 96, 0.15); color: var(--success-color); }
        .status-nao { background: rgba(108, 117, 125, 0.15); color: #6c757d; }

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
                        <li class="breadcrumb-item active">Precos Vigentes</li>
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
                <h2 class="page-title"><i class="bi bi-currency-dollar me-2 text-primary"></i>Precos Vigentes</h2>
                <p class="page-subtitle">Acompanhe os precos ativos e o status promocional.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <button class="btn btn-outline-primary" type="button" onclick="carregarPrecos()">
                    <i class="bi bi-arrow-clockwise me-2"></i>Atualizar
                </button>
                <button class="btn btn-primary" type="button" onclick="abrirModalPreco()">
                    <i class="bi bi-plus-circle me-2"></i>Novo Preco
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
                        <label class="form-label">Filial</label>
                        <select class="form-select" id="filtroFilial">
                            <option value="">Carregando filiais...</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Produto</label>
                        <select class="form-select" id="filtroProduto">
                            <option value="">Carregando produtos...</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Em promocao</label>
                        <select class="form-select" id="filtroPromocao">
                            <option value="">Todos</option>
                            <option value="1">Sim</option>
                            <option value="0">Nao</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-custom">
            <div class="card-header-custom d-flex justify-content-between align-items-center">
                <div>
                    <strong>Lista de precos</strong>
                    <div class="text-muted small" id="totalPrecos">0 registro(s)</div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-custom">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Filial</th>
                                <th>Preco base</th>
                                <th>Preco atual</th>
                                <th>Promocao</th>
                                <th>Preco promo</th>
                                <th>Vigencia</th>
                                <th>Acoes</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyPrecos">
                            <tr>
                                <td colspan="8" class="text-center text-muted">Carregando precos...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <div class="modal fade" id="modalPreco" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalPrecoTitulo">Novo preco vigente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formPreco">
                        <input type="hidden" id="precoId">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Filial *</label>
                                <select class="form-select" id="precoFilial" required>
                                    <option value="">Carregando filiais...</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Produto *</label>
                                <select class="form-select" id="precoProduto" required>
                                    <option value="">Carregando produtos...</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Preco base *</label>
                                <input type="number" class="form-control" id="precoBase" step="0.01" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Preco atual</label>
                                <input type="number" class="form-control" id="precoAtual" step="0.01">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Em promocao</label>
                                <select class="form-select" id="precoEmPromocao">
                                    <option value="0">Nao</option>
                                    <option value="1">Sim</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Preco promocional</label>
                                <input type="number" class="form-control" id="precoPromocional" step="0.01">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">ID promocao</label>
                                <input type="number" class="form-control" id="precoPromocaoId">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Inicio promocao</label>
                                <input type="datetime-local" class="form-control" id="precoInicioPromocao">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Fim promocao</label>
                                <input type="datetime-local" class="form-control" id="precoFimPromocao">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-danger" id="btnExcluirPreco" onclick="excluirPreco()" style="display:none;">
                        <i class="bi bi-trash me-2"></i>Excluir
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="salvarPreco()">Salvar</button>
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

        let precos = [];
        let filiais = [];
        let produtos = [];
        let modalPreco = null;

        const API_CONFIG = {
            BASE_URL: BASE_URL,
            PRECOS_VIGENTES: '/api/v1/precificacao/precos-vigentes',
            FILIAIS_EMPRESA: '/api/filiais/empresa',
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
            modalPreco = new bootstrap.Modal(document.getElementById('modalPreco'));
            carregarFiliais();
            carregarProdutos();
            carregarPrecos();

            document.getElementById('filtroFilial').addEventListener('change', carregarPrecos);
            document.getElementById('filtroProduto').addEventListener('change', carregarPrecos);
            document.getElementById('filtroPromocao').addEventListener('change', carregarPrecos);

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

        async function carregarPrecos() {
            mostrarLoading(true);
            try {
                const filtros = {
                    id_empresa: idEmpresa,
                    id_filial: document.getElementById('filtroFilial').value,
                    id_produto: document.getElementById('filtroProduto').value,
                    em_promocao: document.getElementById('filtroPromocao').value
                };

                const query = buildQuery(filtros);
                const response = await fetch(`${API_CONFIG.BASE_URL}${API_CONFIG.PRECOS_VIGENTES}?${query}`, {
                    method: 'GET',
                    headers: API_CONFIG.getHeaders()
                });

                if (!response.ok) {
                    throw new Error(`Erro ${response.status}: ${response.statusText}`);
                }

                const data = await parseJsonResponse(response);
                const raw = normalizarLista(data);
                precos = raw.map(normalizarPrecoVigente);
                renderizarPrecos();
            } catch (error) {
                console.error('Erro ao carregar precos:', error);
                mostrarNotificacao('Erro ao carregar precos: ' + error.message, 'error');
                document.getElementById('tbodyPrecos').innerHTML = '<tr><td colspan="8" class="text-center text-muted">Erro ao carregar dados</td></tr>';
            } finally {
                mostrarLoading(false);
            }
        }

        function renderizarPrecos() {
            const tbody = document.getElementById('tbodyPrecos');
            const totalEl = document.getElementById('totalPrecos');

            if (!Array.isArray(precos) || precos.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">Nenhum preco encontrado</td></tr>';
                if (totalEl) totalEl.textContent = '0 registro(s)';
                return;
            }

            tbody.innerHTML = precos.map(item => {
                const promoClass = item.em_promocao ? 'status-sim' : 'status-nao';
                const promoTexto = item.em_promocao ? 'Sim' : 'Nao';
                const vigencia = item.data_inicio_promocao || item.data_fim_promocao
                    ? `${formatarDataHora(item.data_inicio_promocao)} - ${formatarDataHora(item.data_fim_promocao)}`
                    : '-';

                return `
                    <tr>
                        <td>${escapeHtml(item.produto_nome)}</td>
                        <td>${escapeHtml(item.filial_nome)}</td>
                        <td>${formatarMoeda(item.preco_base)}</td>
                        <td>${formatarMoeda(item.preco_atual)}</td>
                        <td><span class="status-badge ${promoClass}">${promoTexto}</span></td>
                        <td>${item.em_promocao ? formatarMoeda(item.preco_promocional) : '-'}</td>
                        <td>${vigencia}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="editarPreco(${item.id})">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="excluirPreco(${item.id})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');

            if (totalEl) totalEl.textContent = `${precos.length} registro(s)`;
        }

        function abrirModalPreco() {
            document.getElementById('formPreco').reset();
            document.getElementById('precoId').value = '';
            document.getElementById('modalPrecoTitulo').textContent = 'Novo preco vigente';
            document.getElementById('btnExcluirPreco').style.display = 'none';
            modalPreco.show();
        }

        function editarPreco(id) {
            const item = precos.find(p => p.id === id);
            if (!item) return;

            document.getElementById('precoId').value = item.id;
            document.getElementById('precoFilial').value = item.id_filial || '';
            document.getElementById('precoProduto').value = item.id_produto || '';
            document.getElementById('precoBase').value = item.preco_base ?? '';
            document.getElementById('precoAtual').value = item.preco_atual ?? '';
            document.getElementById('precoEmPromocao').value = item.em_promocao ? '1' : '0';
            document.getElementById('precoPromocional').value = item.preco_promocional ?? '';
            document.getElementById('precoPromocaoId').value = item.id_promocao_ativa ?? '';
            document.getElementById('precoInicioPromocao').value = formatarDateTimeInput(item.data_inicio_promocao);
            document.getElementById('precoFimPromocao').value = formatarDateTimeInput(item.data_fim_promocao);

            document.getElementById('modalPrecoTitulo').textContent = `Editar preco #${item.id}`;
            document.getElementById('btnExcluirPreco').style.display = 'inline-flex';
            modalPreco.show();
        }

        async function salvarPreco() {
            const id = document.getElementById('precoId').value;
            const payload = {
                id_empresa: idEmpresa,
                id_filial: parseInt(document.getElementById('precoFilial').value || '0', 10),
                id_produto: parseInt(document.getElementById('precoProduto').value || '0', 10),
                preco_base: parseFloat(document.getElementById('precoBase').value || '0'),
                preco_atual: parseFloat(document.getElementById('precoAtual').value || '0') || null,
                em_promocao: parseInt(document.getElementById('precoEmPromocao').value || '0', 10)
            };

            if (!payload.id_filial || !payload.id_produto || !payload.preco_base) {
                mostrarNotificacao('Preencha os campos obrigatorios.', 'warning');
                return;
            }

            if (!payload.preco_atual) {
                payload.preco_atual = payload.preco_base;
            }

            const precoPromocional = parseFloat(document.getElementById('precoPromocional').value || '0');
            if (precoPromocional) {
                payload.preco_promocional = precoPromocional;
            }

            const idPromocao = parseInt(document.getElementById('precoPromocaoId').value || '0', 10);
            if (idPromocao) {
                payload.id_promocao_ativa = idPromocao;
            }

            const inicioPromo = formatarDateTimeLocal(document.getElementById('precoInicioPromocao').value);
            const fimPromo = formatarDateTimeLocal(document.getElementById('precoFimPromocao').value);
            if (inicioPromo) payload.data_inicio_promocao = inicioPromo;
            if (fimPromo) payload.data_fim_promocao = fimPromo;

            mostrarLoading(true);
            try {
                const url = id ? `${API_CONFIG.BASE_URL}${API_CONFIG.PRECOS_VIGENTES}/${id}` : `${API_CONFIG.BASE_URL}${API_CONFIG.PRECOS_VIGENTES}`;
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

                modalPreco.hide();
                mostrarNotificacao('Preco salvo.', 'success');
                carregarPrecos();
            } catch (error) {
                console.error('Erro ao salvar preco:', error);
                mostrarNotificacao('Erro ao salvar preco: ' + error.message, 'error');
            } finally {
                mostrarLoading(false);
            }
        }

        async function excluirPreco(idParam) {
            const id = idParam || document.getElementById('precoId').value;
            if (!id) return;
            if (!confirm('Deseja excluir este preco vigente?')) return;

            mostrarLoading(true);
            try {
                const response = await fetch(`${API_CONFIG.BASE_URL}${API_CONFIG.PRECOS_VIGENTES}/${id}`, {
                    method: 'DELETE',
                    headers: API_CONFIG.getHeaders()
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    throw new Error(errorText || `Erro ${response.status}`);
                }

                modalPreco.hide();
                mostrarNotificacao('Preco excluido.', 'success');
                carregarPrecos();
            } catch (error) {
                console.error('Erro ao excluir preco:', error);
                mostrarNotificacao('Erro ao excluir preco: ' + error.message, 'error');
            } finally {
                mostrarLoading(false);
            }
        }
        async function carregarFiliais() {
            const selects = [document.getElementById('filtroFilial'), document.getElementById('precoFilial')].filter(Boolean);
            selects.forEach(select => select.innerHTML = '<option value="">Carregando filiais...</option>');

            try {
                const response = await fetch(`${API_CONFIG.BASE_URL}${API_CONFIG.FILIAIS_EMPRESA}/${idEmpresa}`, {
                    method: 'GET',
                    headers: API_CONFIG.getHeaders()
                });

                if (!response.ok) {
                    throw new Error(`Erro ${response.status}: ${response.statusText}`);
                }

                const data = await parseJsonResponse(response);
                const raw = normalizarLista(data);
                filiais = raw.map(item => ({
                    id_filial: item.id_filial ?? item.id ?? null,
                    nome_filial: item.nome_filial ?? item.nome ?? '',
                    cidade: item.cidade ?? '',
                    estado: item.estado ?? ''
                })).filter(item => item.id_filial);

                if (filiais.length === 0) {
                    selects.forEach(select => select.innerHTML = '<option value="">Nenhuma filial encontrada</option>');
                    return;
                }

                selects.forEach(select => {
                    select.innerHTML = '<option value="">Selecione</option>';
                    filiais.forEach(filial => {
                        const option = document.createElement('option');
                        option.value = filial.id_filial;
                        const local = [filial.cidade, filial.estado].filter(Boolean).join('/');
                        option.textContent = `${filial.nome_filial}${local ? ' - ' + local : ''}`;
                        select.appendChild(option);
                    });
                });
            } catch (error) {
                console.error('Erro ao carregar filiais:', error);
                selects.forEach(select => select.innerHTML = '<option value="">Nenhuma filial encontrada</option>');
            }
        }

        async function carregarProdutos() {
            const selects = [document.getElementById('filtroProduto'), document.getElementById('precoProduto')].filter(Boolean);
            selects.forEach(select => select.innerHTML = '<option value="">Carregando produtos...</option>');

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

            selects.forEach(select => select.innerHTML = '<option value="">Nenhum produto encontrado</option>');
        }

        function preencherSelectProdutos() {
            const selects = [document.getElementById('filtroProduto'), document.getElementById('precoProduto')].filter(Boolean);
            selects.forEach(select => {
                select.innerHTML = '<option value="">Selecione o produto</option>';
                produtos.forEach(produto => {
                    const option = document.createElement('option');
                    option.value = produto.id_produto;
                    option.textContent = `${produto.descricao} (#${produto.id_produto})`;
                    select.appendChild(option);
                });
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

        function normalizarPrecoVigente(item) {
            const emPromoRaw = item.em_promocao ?? item.emPromocao ?? item.em_promocao === 1;
            const emPromocao = emPromoRaw === true || emPromoRaw === 1 || emPromoRaw === '1' || String(emPromoRaw).toLowerCase() === 'sim';

            const produtoObj = (item.produto && typeof item.produto === 'object') ? item.produto : null;
            const filialObj = (item.filial && typeof item.filial === 'object') ? item.filial : null;

            return {
                id: item.id_preco_vigente ?? item.id ?? null,
                id_filial: item.id_filial ?? null,
                id_produto: item.id_produto ?? null,
                filial_nome: item.nome_filial
                    ?? filialObj?.nome_filial
                    ?? filialObj?.nome
                    ?? item.filial_nome
                    ?? (typeof item.filial === 'string' ? item.filial : '')
                    ?? '',
                produto_nome: item.produto_nome
                    ?? item.nome_produto
                    ?? item.descricao
                    ?? produtoObj?.descricao
                    ?? produtoObj?.nome
                    ?? produtoObj?.produto
                    ?? (typeof item.produto === 'string' ? item.produto : '')
                    ?? '',
                preco_base: parseFloat(item.preco_base ?? item.preco_normal ?? 0),
                preco_atual: parseFloat(item.preco_atual ?? item.preco_vigente ?? item.preco ?? 0),
                preco_promocional: parseFloat(item.preco_promocional ?? 0),
                em_promocao: emPromocao,
                id_promocao_ativa: item.id_promocao_ativa ?? null,
                data_inicio_promocao: item.data_inicio_promocao ?? item.inicio_promocao ?? '',
                data_fim_promocao: item.data_fim_promocao ?? item.fim_promocao ?? ''
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
