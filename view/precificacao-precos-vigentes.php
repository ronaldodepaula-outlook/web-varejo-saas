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
                <button class="btn btn-outline-secondary" type="button" onclick="toggleSelecionarTodos()">
                    <i class="bi bi-check2-square me-2"></i>Selecionar todos
                </button>
                <button class="btn btn-outline-success" type="button" onclick="abrirModalEtiquetas()">
                    <i class="bi bi-printer me-2"></i>Gerar etiquetas
                </button>
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
                    <div class="text-muted small" id="totalSelecionados">0 selecionado(s)</div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-custom">
                        <thead>
                            <tr>
                                <th style="width:40px;">
                                    <input type="checkbox" id="checkTodos" onchange="toggleSelecionarTodos(true)">
                                </th>
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
                                <td colspan="9" class="text-center text-muted">Carregando precos...</td>
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

    <div class="modal fade" id="modalEtiquetas" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Gerar etiquetas de gondola</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Formato *</label>
                            <select class="form-select" id="etiquetaFormato">
                                <option value="GONDOLA">Gondola - Padrao mercado</option>
                                <option value="A1">Cartaz A1</option>
                                <option value="A2">Cartaz A2</option>
                                <option value="A3">Cartaz A3</option>
                                <option value="A4">Cartaz A4</option>
                                <option value="1B">1B - Testeira 1/2 Frente</option>
                                <option value="1C">1C - Testeira Preco</option>
                                <option value="1D">1D - Testeira Frente Inteira</option>
                                <option value="1E">1E - Etiqueta Pequena EAN13</option>
                                <option value="1A">1A - Etiqueta Pequena EAN8</option>
                                <option value="1G">1G - Testeira 1/2 Frente (Zebra)</option>
                                <option value="1H">1H - Testeira Preco (Zebra)</option>
                                <option value="1I">1I - Testeira Frente Inteira (Zebra)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Largura (mm)</label>
                            <input type="number" class="form-control" id="etiquetaLargura" value="120" min="30" step="1">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Altura (mm)</label>
                            <input type="number" class="form-control" id="etiquetaAltura" value="50" min="20" step="1">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Qtd. por produto</label>
                            <input type="number" class="form-control" id="etiquetaQuantidade" value="1" min="1">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Mostrar codigo barras</label>
                            <select class="form-select" id="etiquetaMostrarCodigo">
                                <option value="1">Sim</option>
                                <option value="0">Nao</option>
                            </select>
                        </div>
                        <div class="col-12 text-muted small">
                            O PDF sera gerado seguindo o padrao RMS indicado. Use a impressao do navegador para salvar.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" onclick="gerarEtiquetasPDF()">
                        <i class="bi bi-printer me-2"></i>Gerar PDF
                    </button>
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
        let modalEtiquetas = null;
        let etiquetasSelecionadas = [];

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
            modalEtiquetas = new bootstrap.Modal(document.getElementById('modalEtiquetas'));
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
                document.getElementById('tbodyPrecos').innerHTML = '<tr><td colspan="9" class="text-center text-muted">Erro ao carregar dados</td></tr>';
            } finally {
                mostrarLoading(false);
            }
        }

        function renderizarPrecos() {
            const tbody = document.getElementById('tbodyPrecos');
            const totalEl = document.getElementById('totalPrecos');
            const totalSelecionadosEl = document.getElementById('totalSelecionados');

            if (!Array.isArray(precos) || precos.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9" class="text-center text-muted">Nenhum preco encontrado</td></tr>';
                if (totalEl) totalEl.textContent = '0 registro(s)';
                if (totalSelecionadosEl) totalSelecionadosEl.textContent = '0 selecionado(s)';
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
                        <td>
                            <input type="checkbox" class="select-preco" data-id="${item.id}" onchange="atualizarSelecao()">
                        </td>
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
            atualizarSelecao();
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

        function toggleSelecionarTodos(fromHeader) {
            const checkHeader = document.getElementById('checkTodos');
            const checkboxes = Array.from(document.querySelectorAll('.select-preco'));
            if (checkboxes.length === 0) return;

            let shouldCheck = true;
            if (fromHeader && checkHeader) {
                shouldCheck = checkHeader.checked;
            } else if (checkHeader) {
                shouldCheck = !checkHeader.checked;
                checkHeader.checked = shouldCheck;
            }

            checkboxes.forEach(cb => cb.checked = shouldCheck);
            atualizarSelecao();
        }

        function atualizarSelecao() {
            const totalSelecionadosEl = document.getElementById('totalSelecionados');
            const checkHeader = document.getElementById('checkTodos');
            const checkboxes = Array.from(document.querySelectorAll('.select-preco'));
            const selecionados = checkboxes.filter(cb => cb.checked);

            if (totalSelecionadosEl) {
                totalSelecionadosEl.textContent = `${selecionados.length} selecionado(s)`;
            }

            if (checkHeader) {
                checkHeader.checked = checkboxes.length > 0 && selecionados.length === checkboxes.length;
                checkHeader.indeterminate = selecionados.length > 0 && selecionados.length < checkboxes.length;
            }
        }

        function obterSelecionados() {
            const ids = Array.from(document.querySelectorAll('.select-preco:checked'))
                .map(cb => parseInt(cb.getAttribute('data-id') || '0', 10))
                .filter(id => id);
            return precos.filter(item => ids.includes(item.id));
        }

        function abrirModalEtiquetas() {
            const selecionados = obterSelecionados();
            if (!selecionados.length) {
                mostrarNotificacao('Selecione pelo menos um produto para gerar etiquetas.', 'warning');
                return;
            }

            etiquetasSelecionadas = selecionados;
            modalEtiquetas.show();
        }

        function gerarEtiquetasPDF() {
            if (!Array.isArray(etiquetasSelecionadas) || etiquetasSelecionadas.length === 0) {
                mostrarNotificacao('Nenhum produto selecionado.', 'warning');
                return;
            }

            const formato = document.getElementById('etiquetaFormato').value;
            const quantidade = parseInt(document.getElementById('etiquetaQuantidade').value || '1', 10);
            const mostrarCodigo = document.getElementById('etiquetaMostrarCodigo').value === '1';

            const formatMap = {
                'GONDOLA': { width: 120, height: 50, layout: 'gondola' },
                'A1': { width: 594, height: 841, layout: 'cartaz' },
                'A2': { width: 420, height: 594, layout: 'cartaz' },
                'A3': { width: 297, height: 420, layout: 'cartaz' },
                'A4': { width: 210, height: 297, layout: 'cartaz' },
                '1A': { width: 50, height: 30, layout: 'small', ean: 'EAN8' },
                '1E': { width: 50, height: 30, layout: 'small', ean: 'EAN13' },
                '1B': { width: 100, height: 50, layout: 'half' },
                '1G': { width: 100, height: 50, layout: 'half' },
                '1C': { width: 100, height: 40, layout: 'price' },
                '1H': { width: 100, height: 40, layout: 'price' },
                '1D': { width: 100, height: 80, layout: 'full' },
                '1I': { width: 100, height: 80, layout: 'full' }
            };

            const config = { ...(formatMap[formato] || formatMap['1B']) };
            if (formato === 'GONDOLA') {
                const largura = parseInt(document.getElementById('etiquetaLargura').value || '120', 10);
                const altura = parseInt(document.getElementById('etiquetaAltura').value || '50', 10);
                if (largura > 0) config.width = largura;
                if (altura > 0) config.height = altura;
            }
            const labels = [];
            etiquetasSelecionadas.forEach(item => {
                for (let i = 0; i < Math.max(1, quantidade); i += 1) {
                    labels.push(renderEtiquetaHTML(item, config, mostrarCodigo));
                }
            });

            const bleed = config.layout === 'cartaz' ? 3 : 0;
            const pageMargin = config.layout === 'cartaz' ? 0 : 8;
            const pageWidthMm = config.layout === 'cartaz' ? (config.width + bleed * 2) : 210;
            const pageHeightMm = config.layout === 'cartaz' ? (config.height + bleed * 2) : 297;
            const usableWidth = pageWidthMm - pageMargin * 2;
            const gap = config.layout === 'cartaz' ? 0 : 6;
            const cols = config.layout === 'cartaz'
                ? 1
                : Math.max(1, Math.floor((usableWidth + gap) / (config.width + gap)));

            const printWindow = window.open('', '_blank');
            if (!printWindow) {
                mostrarNotificacao('Nao foi possivel abrir a janela de impressao.', 'error');
                return;
            }

            printWindow.document.write(`
                <!DOCTYPE html>
                <html lang="pt-BR">
                <head>
                    <meta charset="UTF-8">
                    <title>Etiquetas de Gondola</title>
                    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"><\/script>
                    <style>
                        @page { size: ${pageWidthMm}mm ${pageHeightMm}mm; margin: ${pageMargin}mm; }
                        body { font-family: Arial, sans-serif; margin: 0; }
                        .labels {
                            display: grid;
                            grid-template-columns: repeat(${cols}, ${config.width}mm);
                            gap: ${gap}mm;
                            height: 100%;
                        }
                        .labels.cartaz {
                            display: block;
                            height: 100%;
                            padding: 0;
                        }
                        .label {
                            width: ${config.width}mm;
                            height: ${config.height}mm;
                            border: 1px solid #000;
                            border-radius: 4px;
                            padding: 4mm;
                            box-sizing: border-box;
                            display: flex;
                            flex-direction: column;
                            justify-content: space-between;
                        }
                        .label.cartaz {
                            width: ${pageWidthMm}mm;
                            height: ${pageHeightMm}mm;
                            border: 1px solid #e5e5e5;
                            border-radius: 8px;
                            background: #fff;
                            box-sizing: border-box;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                        }
                        .cartaz-safe {
                            width: ${config.width}mm;
                            height: ${config.height}mm;
                            padding: 12mm;
                            box-sizing: border-box;
                            display: flex;
                            flex-direction: column;
                            gap: 8mm;
                            background: #fff;
                            --cartaz-scale: 1;
                            --cartaz-banner: calc(42px * var(--cartaz-scale));
                            --cartaz-title: calc(38px * var(--cartaz-scale));
                            --cartaz-subtitle: calc(16px * var(--cartaz-scale));
                            --cartaz-de: calc(18px * var(--cartaz-scale));
                            --cartaz-por: calc(18px * var(--cartaz-scale));
                            --cartaz-price-main: calc(120px * var(--cartaz-scale));
                            --cartaz-price-comma: calc(120px * var(--cartaz-scale));
                            --cartaz-price-cents: calc(60px * var(--cartaz-scale));
                            --cartaz-unit: calc(18px * var(--cartaz-scale));
                            --cartaz-validade: calc(14px * var(--cartaz-scale));
                        }
                        .cartaz-banner {
                            background: linear-gradient(135deg, #f44336 0%, #b71c1c 100%);
                            color: #fff;
                            font-size: var(--cartaz-banner);
                            font-weight: 800;
                            text-transform: uppercase;
                            text-align: center;
                            padding: 6mm 4mm;
                            border-radius: 6px;
                            letter-spacing: 2px;
                        }
                        .cartaz-title {
                            font-size: var(--cartaz-title);
                            font-weight: 800;
                            text-transform: uppercase;
                            text-align: center;
                            color: #111;
                            line-height: 1.1;
                        }
                        .cartaz-subtitle {
                            text-align: center;
                            font-size: var(--cartaz-subtitle);
                            color: #666;
                        }
                        .cartaz-price-block {
                            display: flex;
                            flex-direction: column;
                            align-items: center;
                            gap: 6mm;
                            margin-top: auto;
                        }
                        .cartaz-de {
                            font-size: var(--cartaz-de);
                            color: #666;
                            text-decoration: line-through;
                        }
                        .cartaz-por {
                            font-size: var(--cartaz-por);
                            font-weight: 700;
                            color: #c62828;
                        }
                        .cartaz-price {
                            display: flex;
                            align-items: baseline;
                            gap: 2mm;
                            color: #c62828;
                            font-weight: 800;
                        }
                        .cartaz-price-main {
                            font-size: var(--cartaz-price-main);
                            line-height: 0.9;
                        }
                        .cartaz-price-comma {
                            font-size: var(--cartaz-price-comma);
                            line-height: 0.9;
                        }
                        .cartaz-price-cents {
                            font-size: var(--cartaz-price-cents);
                            line-height: 0.9;
                        }
                        .cartaz-unit {
                            font-size: var(--cartaz-unit);
                            color: #444;
                            text-transform: uppercase;
                        }
                        .cartaz-footer {
                            display: flex;
                            justify-content: space-between;
                            align-items: center;
                            margin-top: auto;
                            gap: 6mm;
                        }
                        .cartaz-validade {
                            font-size: var(--cartaz-validade);
                            color: #777;
                        }
                        .cartaz-barcode {
                            width: 80mm;
                            height: 20mm;
                        }
                        .cartaz-barcode svg {
                            width: 80mm;
                            height: 20mm;
                        }
                        .label-title {
                            font-size: 11px;
                            text-transform: uppercase;
                            letter-spacing: 0.5px;
                            color: #555;
                        }
                        .label-name {
                            font-size: 13px;
                            font-weight: 600;
                            line-height: 1.2;
                        }
                        .label-name.small { font-size: 10px; }
                        .label-code {
                            font-size: 11px;
                            color: #333;
                        }
                        .label-date {
                            font-size: 10px;
                            color: #555;
                        }
                        .label-price {
                            font-size: 26px;
                            font-weight: 800;
                            text-align: right;
                        }
                        .label-price.small { font-size: 18px; }
                        .label-promo {
                            font-size: 12px;
                            font-weight: 700;
                            color: #c0392b;
                            margin-top: 4px;
                        }
                        .label-barcode {
                            font-family: 'Courier New', monospace;
                            font-size: 11px;
                            letter-spacing: 1px;
                            text-align: center;
                            margin-top: 2px;
                        }
                        .label-footer {
                            display: flex;
                            justify-content: space-between;
                            align-items: flex-end;
                            font-size: 10px;
                            color: #333;
                        }
                        .label.gondola {
                            padding: 3mm;
                            background: #fff;
                            position: relative;
                        }
                        .gondola-inner {
                            border: 2px solid #bfbfbf;
                            border-radius: 4px;
                            height: 100%;
                            display: grid;
                            grid-template-columns: 13mm 1fr 24mm;
                            gap: 2mm;
                            padding: 2mm 10mm 2mm 3mm;
                            box-sizing: border-box;
                            background: #f3f3f3;
                        }
                        .gondola-promo {
                            background: #c0392b;
                            color: #fff;
                            font-weight: 700;
                            letter-spacing: 1px;
                            text-align: center;
                            writing-mode: vertical-rl;
                            transform: rotate(180deg);
                            border-radius: 2px;
                            font-size: 11px;
                            padding: 1.5mm 0;
                            margin: 0 0.5mm;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                        }
                        .gondola-body {
                            display: flex;
                            flex-direction: column;
                            justify-content: space-between;
                            gap: 2mm;
                        }
                        .gondola-title {
                            font-size: 15px;
                            font-weight: 700;
                            color: #222;
                            margin-top: 1mm;
                            line-height: 1.15;
                            display: -webkit-box;
                            -webkit-line-clamp: 2;
                            -webkit-box-orient: vertical;
                            overflow: hidden;
                        }
                        .gondola-depor {
                            font-size: 11px;
                            font-weight: 600;
                            color: #333;
                        }
                        .gondola-price-right {
                            display: flex;
                            flex-direction: column;
                            align-items: center;
                            justify-content: center;
                            gap: 1mm;
                            width: 100%;
                        }
                        .gondola-price-right-line {
                            display: flex;
                            align-items: baseline;
                            gap: 1px;
                        }
                        .gondola-price-right-main {
                            font-size: 57.1px;
                            font-weight: 800;
                            color: #111;
                            line-height: 1;
                        }
                        .gondola-price-right-comma {
                            font-size: 47.6px;
                            font-weight: 800;
                            color: #111;
                            line-height: 1;
                        }
                        .gondola-price-right-cents {
                            font-size: 22.4px;
                            font-weight: 800;
                            line-height: 1;
                        }
                        .gondola-price-right-currency {
                            font-size: 14px;
                            font-weight: 700;
                            line-height: 1;
                        }
                        .gondola-validade {
                            font-size: 10px;
                            font-weight: 600;
                            color: #333;
                        }
                        .gondola-right {
                            display: flex;
                            flex-direction: column;
                            justify-content: space-between;
                            align-items: center;
                            gap: 2mm;
                        }
                        .gondola-barcode-horizontal {
                            width: 100%;
                            height: 14mm;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                        }
                        .gondola-barcode-horizontal svg {
                            width: 58mm;
                            height: 14mm;
                        }
                        .gondola-tag {
                            border: 1px solid #111;
                            width: 100%;
                            text-align: center;
                            font-size: 9px;
                            font-weight: 700;
                            background: #fff;
                        }
                        .gondola-tag-title {
                            background: #111;
                            color: #fff;
                            padding: 1mm 0;
                            font-size: 9px;
                            letter-spacing: 1px;
                        }
                        .gondola-tag-value {
                            padding: 1mm 0;
                            font-size: 9px;
                            text-transform: uppercase;
                        }
                        
                    </style>
                </head>
                <body>
                    <div class="labels ${config.layout === 'cartaz' ? 'cartaz' : ''}">
                        ${labels.join('')}
                    </div>
                    <script>
                        window.addEventListener('load', function() {
                            if (typeof JsBarcode === 'undefined') return;
                            document.querySelectorAll('.gondola-barcode-horizontal[data-ean], .cartaz-barcode[data-ean]').forEach(function(el) {
                                const value = el.getAttribute('data-ean') || '';
                                if (!value) return;
                                const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                                el.appendChild(svg);
                                try {
                                    JsBarcode(svg, value, {
                                        format: value.length === 8 ? 'EAN8' : 'EAN13',
                                        displayValue: false,
                                        width: 1.4,
                                        height: 36,
                                        margin: 0
                                    });
                                } catch (e) {
                                    el.textContent = value;
                                }
                            });
                        });
                    <\/script>
                </body>
                </html>
            `);

            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
        }

        function renderEtiquetaHTML(item, config, mostrarCodigo) {
            const descricao = item.produto_nome || '';
            const descricaoReduzida = reduzirTexto(descricao, 18);
            const descricaoMinuscula = descricao.toLowerCase();
            const codigo = (item.id_produto ?? '') + '';
            const digito = codigo ? codigo.slice(-1) : '';
            const ean13 = extrairCodigoBarras(item, 13);
            const ean8 = extrairCodigoBarras(item, 8);
            const mmdd = formatarMMDD(new Date());

            const emPromo = item.em_promocao && item.preco_promocional;
            const precoOferta = emPromo ? formatarMoeda(item.preco_promocional) : '';
            const precoVenda = emPromo
                ? precoOferta
                : formatarMoeda(item.preco_atual || item.preco_base);

            const dePor = emPromo ? `<div class="label-promo">De ${formatarMoeda(item.preco_base)} por ${precoOferta}</div>` : '';
            const codigoLinha = mostrarCodigo ? `<div class="label-code">${escapeHtml(codigo)}-${escapeHtml(digito)}</div>` : '';
            const barcode = mostrarCodigo ? `<div class="label-barcode">${escapeHtml(config.ean === 'EAN8' ? (ean8 || '') : (ean13 || ''))}</div>` : '';

            if (config.layout === 'gondola') {
                const precoBase = formatarMoeda(item.preco_base);
                const precoNumero = emPromo ? item.preco_promocional : (item.preco_atual || item.preco_base);
                const partes = formatarPrecoPartes(precoNumero);
                const validade = item.data_fim_promocao ? formatarDDMM(item.data_fim_promocao) : '';
                const eanValue = ean13 || ean8;
                const tagProduto = (eanValue || codigo || '').toString();

                const barcodeHorizontal = mostrarCodigo && eanValue
                    ? `<div class="gondola-barcode-horizontal" data-ean="${escapeHtml(eanValue)}"></div>`
                    : '';

                return `
                    <div class="label gondola">
                        <div class="gondola-inner">
                            <div class="gondola-promo" style="${emPromo ? '' : 'background:#333;'}">${emPromo ? 'OFERTA' : 'APROVEITE'}</div>
                            <div class="gondola-body">
                                <div class="gondola-title">${escapeHtml(descricao)}</div>
                                ${emPromo ? `<div class="gondola-depor">De ${precoBase} por</div>` : '<div class="gondola-depor">&nbsp;</div>'}
                                ${barcodeHorizontal}
                                <div class="gondola-validade">${validade ? `Valida ate ${validade}` : ''}</div>
                            </div>
                            <div class="gondola-right">
                                <div class="gondola-price-right">
                                    <div class="gondola-price-right-line">
                                        <span class="gondola-price-right-main">${partes.inteiro}</span>
                                        <span class="gondola-price-right-comma">,</span>
                                        <span class="gondola-price-right-cents">${partes.centavos}</span>
                                    </div>
                                    <div class="gondola-price-right-currency">R$</div>
                                </div>
                                <div class="gondola-tag">
                                    <div class="gondola-tag-title">PRODUTO</div>
                                    <div class="gondola-tag-value">${escapeHtml(tagProduto)}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }

            if (config.layout === 'cartaz') {
                const fator = Math.min(config.width / 594, config.height / 841);
                const escala = fator;
                const baseA1 = {
                    banner: 290,
                    title: 160,
                    subtitle: 10,
                    de: 120,
                    por: 120,
                    priceMain: 900,
                    priceComma: 300,
                    priceCents: 400,
                    unit: 90,
                    validade: 90
                };
                let cartazVars = `--cartaz-scale:${escala.toFixed(4)};`;
                cartazVars += `--cartaz-banner:${(baseA1.banner * fator).toFixed(2)}px;`;
                cartazVars += `--cartaz-title:${(baseA1.title * fator).toFixed(2)}px;`;
                cartazVars += `--cartaz-subtitle:${(baseA1.subtitle * fator).toFixed(2)}px;`;
                cartazVars += `--cartaz-de:${(baseA1.de * fator).toFixed(2)}px;`;
                cartazVars += `--cartaz-por:${(baseA1.por * fator).toFixed(2)}px;`;
                cartazVars += `--cartaz-price-main:${(baseA1.priceMain * fator).toFixed(2)}px;`;
                cartazVars += `--cartaz-price-comma:${(baseA1.priceComma * fator).toFixed(2)}px;`;
                cartazVars += `--cartaz-price-cents:${(baseA1.priceCents * fator).toFixed(2)}px;`;
                cartazVars += `--cartaz-unit:${(baseA1.unit * fator).toFixed(2)}px;`;
                cartazVars += `--cartaz-validade:${(baseA1.validade * fator).toFixed(2)}px;`;
                const precoNumero = emPromo ? item.preco_promocional : (item.preco_atual || item.preco_base);
                const partes = formatarPrecoPartes(precoNumero);
                const validade = item.data_fim_promocao ? formatarDDMM(item.data_fim_promocao) : '';
                const eanValue = ean13 || ean8;
                const unidade = item.unidade_medida ? `/${item.unidade_medida}` : 'unidade';
                const bannerTexto = emPromo ? 'OFERTA' : 'APROVEITE';

                const barcode = mostrarCodigo && eanValue
                    ? `<div class="cartaz-barcode" data-ean="${escapeHtml(eanValue)}"></div>`
                    : '';

                return `
                    <div class="label cartaz">
                        <div class="cartaz-safe" style="${cartazVars}">
                            <div class="cartaz-banner">${bannerTexto}</div>
                            <div class="cartaz-title">${escapeHtml(descricao)}</div>
                            ${emPromo ? `<div class="cartaz-de">DE: ${formatarMoeda(item.preco_base)}</div>` : '<div class="cartaz-subtitle">&nbsp;</div>'}
                            <div class="cartaz-price-block">
                                <div class="cartaz-por">POR: R$</div>
                                <div class="cartaz-price">
                                    <span class="cartaz-price-main">${partes.inteiro}</span>
                                    <span class="cartaz-price-comma">,</span>
                                    <span class="cartaz-price-cents">${partes.centavos}</span>
                                </div>
                                <div class="cartaz-unit">${escapeHtml(unidade)}</div>
                            </div>
                            <div class="cartaz-footer">
                                ${barcode}
                                <div class="cartaz-validade">${validade ? `Validade ate ${validade}` : ''}</div>
                            </div>
                        </div>
                    </div>
                `;
            }

            if (config.layout === 'small') {
                return `
                    <div class="label">
                        <div class="label-name small">${escapeHtml(descricaoReduzida)}</div>
                        ${barcode}
                        ${codigoLinha}
                    </div>
                `;
            }

            if (config.layout === 'price') {
                return `
                    <div class="label">
                        <div>
                            ${codigoLinha}
                            <div class="label-date">${mmdd}</div>
                            <div class="label-name">${escapeHtml(descricaoMinuscula)}</div>
                        </div>
                        <div class="label-price">${precoVenda}</div>
                        ${dePor}
                    </div>
                `;
            }

            if (config.layout === 'full') {
                return `
                    <div class="label">
                        <div>
                            <div class="label-name">${escapeHtml(descricao)}</div>
                            ${barcode}
                            ${codigoLinha}
                            <div class="label-date">${mmdd}</div>
                        </div>
                        <div class="label-price">${precoVenda}</div>
                        ${dePor}
                    </div>
                `;
            }

            return `
                <div class="label">
                    <div class="label-title">Testeira</div>
                    <div class="label-name">${escapeHtml(descricao)}</div>
                    ${barcode}
                    ${codigoLinha}
                    <div class="label-date">${mmdd}</div>
                    <div class="label-price">${precoVenda}</div>
                    ${dePor}
                </div>
            `;
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
                codigo_barras: item.codigo_barras ?? produtoObj?.codigo_barras ?? '',
                unidade_medida: item.unidade_medida ?? produtoObj?.unidade_medida ?? '',
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

        function formatarPrecoPartes(valor) {
            const numero = parseFloat(valor || 0);
            if (Number.isNaN(numero)) {
                return { inteiro: '0', centavos: '00' };
            }
            const formatted = new Intl.NumberFormat('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(numero);
            const partes = formatted.split(',');
            return { inteiro: partes[0], centavos: partes[1] ?? '00' };
        }

        function formatarDDMM(data) {
            if (!data) return '';
            try {
                const date = new Date(data);
                if (Number.isNaN(date.getTime())) return '';
                const dia = String(date.getDate()).padStart(2, '0');
                const mes = String(date.getMonth() + 1).padStart(2, '0');
                return `${dia}/${mes}`;
            } catch (e) {
                return '';
            }
        }

        function reduzirTexto(texto, limite) {
            if (!texto) return '';
            const clean = String(texto).trim();
            if (clean.length <= limite) return clean;
            return clean.slice(0, Math.max(0, limite - 1)) + '.';
        }

        function extrairCodigoBarras(item, tamanho) {
            const codigo = (item.codigo_barras ?? '').toString().replace(/\D/g, '');
            if (codigo.length >= tamanho) {
                return codigo.slice(0, tamanho);
            }
            return '';
        }

        function formatarMMDD(data) {
            const date = data instanceof Date ? data : new Date();
            const mm = String(date.getMonth() + 1).padStart(2, '0');
            const dd = String(date.getDate()).padStart(2, '0');
            return `${mm}${dd}`;
        }

        function calcularEscalaCartaz(config) {
            if (!config || config.layout !== 'cartaz') return 1;
            const baseWidth = 594;
            const baseHeight = 841;
            return Math.min(config.width / baseWidth, config.height / baseHeight);
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
