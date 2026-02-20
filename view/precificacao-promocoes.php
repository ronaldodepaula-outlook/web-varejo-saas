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
    <?php if (!defined('APP_SHELL')) { $pageTitle = 'Precificacao - Promocoes'; include __DIR__ . '/../components/app-head.php'; } ?>
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
        .status-ativa { background: rgba(39, 174, 96, 0.1); color: var(--success-color); }
        .status-pausada { background: rgba(243, 156, 18, 0.15); color: var(--warning-color); }
        .status-encerrada { background: rgba(127, 140, 141, 0.15); color: var(--secondary-color); }
        .status-cancelada { background: rgba(231, 76, 60, 0.1); color: var(--danger-color); }

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
                        <li class="breadcrumb-item active">Promocoes</li>
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
                <h2 class="page-title"><i class="bi bi-lightning-charge me-2 text-primary"></i>Promocoes</h2>
                <p class="page-subtitle">Crie campanhas e acompanhe produtos em promocao.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <button class="btn btn-outline-primary" type="button" onclick="carregarPromocoes()">
                    <i class="bi bi-arrow-clockwise me-2"></i>Atualizar
                </button>
                <button class="btn btn-primary" type="button" onclick="abrirModalPromocao()">
                    <i class="bi bi-plus-circle me-2"></i>Nova Promocao
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
                            <option value="ativa">Ativa</option>
                            <option value="pausada">Pausada</option>
                            <option value="encerrada">Encerrada</option>
                            <option value="cancelada">Cancelada</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tipo</label>
                        <select class="form-select" id="filtroTipo">
                            <option value="">Todos</option>
                            <option value="desconto_percentual">Desconto percentual</option>
                            <option value="desconto_fixo">Desconto fixo</option>
                            <option value="leve_pague">Leve e pague</option>
                            <option value="combo">Combo</option>
                            <option value="tabloid">Tabloid</option>
                        </select>
                    </div>
                    <div class="col-md-4">
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
                    <strong>Campanhas</strong>
                    <div class="text-muted small" id="totalPromocoes">0 promocao(oes)</div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-custom">
                        <thead>
                            <tr>
                                <th>Codigo</th>
                                <th>Nome</th>
                                <th>Tipo</th>
                                <th>Inicio</th>
                                <th>Fim</th>
                                <th>Status</th>
                                <th>Acoes</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyPromocoes">
                            <tr>
                                <td colspan="7" class="text-center text-muted">Carregando promocoes...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
    <div class="modal fade" id="modalPromocao" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalPromocaoTitulo">Nova promocao</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formPromocao">
                        <input type="hidden" id="promocaoId">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Filial *</label>
                                <select class="form-select" id="promocaoFilial" required>
                                    <option value="">Carregando filiais...</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Codigo *</label>
                                <input type="text" class="form-control" id="promocaoCodigo" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Nome *</label>
                                <input type="text" class="form-control" id="promocaoNome" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tipo *</label>
                                <select class="form-select" id="promocaoTipo" required>
                                    <option value="desconto_percentual">Desconto percentual</option>
                                    <option value="desconto_fixo">Desconto fixo</option>
                                    <option value="leve_pague">Leve e pague</option>
                                    <option value="combo">Combo</option>
                                    <option value="tabloid">Tabloid</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Inicio *</label>
                                <input type="datetime-local" class="form-control" id="promocaoInicio" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Fim *</label>
                                <input type="datetime-local" class="form-control" id="promocaoFim" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <select class="form-select" id="promocaoStatus">
                                    <option value="rascunho">Rascunho</option>
                                    <option value="ativa">Ativa</option>
                                    <option value="pausada">Pausada</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Prioridade</label>
                                <input type="number" class="form-control" id="promocaoPrioridade" value="1">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Aplicar em todas</label>
                                <select class="form-select" id="promocaoTodas">
                                    <option value="0">Nao</option>
                                    <option value="1">Sim</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Descricao</label>
                                <input type="text" class="form-control" id="promocaoDescricao">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-danger" id="btnExcluirPromocao" onclick="excluirPromocao()" style="display:none;">
                        <i class="bi bi-trash me-2"></i>Excluir
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="salvarPromocao()">Salvar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDetalhesPromocao" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="detalhePromocaoTitulo">Promocao</h5>
                        <small class="text-muted" id="detalhePromocaoSubtitulo"></small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs" id="promocaoTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="promocaoResumoTab" data-bs-toggle="tab" data-bs-target="#tabPromocaoResumo" type="button" role="tab">
                                <i class="bi bi-card-text"></i>Resumo
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="promocaoProdutosTab" data-bs-toggle="tab" data-bs-target="#tabPromocaoProdutos" type="button" role="tab">
                                <i class="bi bi-box"></i>Produtos
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content pt-3">
                        <div class="tab-pane fade show active" id="tabPromocaoResumo" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Codigo</label>
                                    <input type="text" class="form-control" id="detalheCodigo" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Nome</label>
                                    <input type="text" class="form-control" id="detalheNome">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tipo</label>
                                    <input type="text" class="form-control" id="detalheTipo" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Inicio</label>
                                    <input type="datetime-local" class="form-control" id="detalheInicio">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Fim</label>
                                    <input type="datetime-local" class="form-control" id="detalheFim">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" id="detalheStatus">
                                        <option value="rascunho">Rascunho</option>
                                        <option value="ativa">Ativa</option>
                                        <option value="pausada">Pausada</option>
                                        <option value="encerrada">Encerrada</option>
                                        <option value="cancelada">Cancelada</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Prioridade</label>
                                    <input type="number" class="form-control" id="detalhePrioridade">
                                </div>
                                <div class="col-md-12 d-flex gap-2 flex-wrap">
                                    <button class="btn btn-primary" type="button" onclick="atualizarPromocaoDetalhe()">
                                        <i class="bi bi-save me-2"></i>Salvar alteracoes
                                    </button>
                                    <button class="btn btn-outline-success" type="button" onclick="ativarPromocao()">
                                        <i class="bi bi-play-circle me-2"></i>Ativar
                                    </button>
                                    <button class="btn btn-outline-warning" type="button" onclick="pausarPromocao()">
                                        <i class="bi bi-pause-circle me-2"></i>Pausar
                                    </button>
                                    <button class="btn btn-outline-danger" type="button" onclick="encerrarPromocao()">
                                        <i class="bi bi-stop-circle me-2"></i>Encerrar
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="tabPromocaoProdutos" role="tabpanel">
                            <div class="row g-3 align-items-end mb-3">
                                <div class="col-md-5">
                                    <label class="form-label">Produto</label>
                                    <select class="form-select" id="promocaoProduto">
                                        <option value="">Carregando produtos...</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Preco normal</label>
                                    <input type="number" class="form-control" id="promocaoPrecoNormal" step="0.01">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Preco promocional</label>
                                    <input type="number" class="form-control" id="promocaoPrecoPromocional" step="0.01">
                                </div>
                                <div class="col-md-1">
                                    <button class="btn btn-success" type="button" onclick="adicionarProdutoPromocao()">
                                        <i class="bi bi-plus"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover table-custom">
                                    <thead>
                                        <tr>
                                            <th>Produto</th>
                                            <th>Preco normal</th>
                                            <th>Preco promocional</th>
                                            <th>Desconto</th>
                                            <th>Acoes</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodyPromocaoProdutos">
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">Nenhum produto vinculado</td>
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

        let promocoes = [];
        let filiais = [];
        let produtos = [];
        let promocaoDetalhe = null;
        let promocaoSelecionadaId = null;
        let modalPromocao = null;
        let modalDetalhes = null;

        const API_CONFIG = {
            BASE_URL: BASE_URL,
            PROMOCOES: '/api/v1/precificacao/promocoes',
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
            modalPromocao = new bootstrap.Modal(document.getElementById('modalPromocao'));
            modalDetalhes = new bootstrap.Modal(document.getElementById('modalDetalhesPromocao'));
            carregarFiliais();
            carregarProdutos();
            carregarPromocoes();

            document.getElementById('filtroStatus').addEventListener('change', carregarPromocoes);
            document.getElementById('filtroTipo').addEventListener('change', carregarPromocoes);
            document.getElementById('filtroDataInicio').addEventListener('change', carregarPromocoes);
            document.getElementById('filtroDataFim').addEventListener('change', carregarPromocoes);

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

        async function carregarPromocoes() {
            mostrarLoading(true);
            try {
                const filtros = {
                    id_empresa: idEmpresa,
                    status: document.getElementById('filtroStatus').value,
                    tipo_promocao: document.getElementById('filtroTipo').value,
                    data_inicio: document.getElementById('filtroDataInicio').value,
                    data_fim: document.getElementById('filtroDataFim').value
                };

                const query = buildQuery(filtros);
                const response = await fetch(`${API_CONFIG.BASE_URL}${API_CONFIG.PROMOCOES}?${query}`, {
                    method: 'GET',
                    headers: API_CONFIG.getHeaders()
                });

                if (!response.ok) {
                    throw new Error(`Erro ${response.status}: ${response.statusText}`);
                }

                const data = await parseJsonResponse(response);
                const raw = normalizarLista(data);
                promocoes = raw.map(normalizarPromocao);
                renderizarPromocoes();
            } catch (error) {
                console.error('Erro ao carregar promocoes:', error);
                mostrarNotificacao('Erro ao carregar promocoes: ' + error.message, 'error');
                document.getElementById('tbodyPromocoes').innerHTML = '<tr><td colspan="7" class="text-center text-muted">Erro ao carregar dados</td></tr>';
            } finally {
                mostrarLoading(false);
            }
        }

        function renderizarPromocoes() {
            const tbody = document.getElementById('tbodyPromocoes');
            const totalEl = document.getElementById('totalPromocoes');
            if (!Array.isArray(promocoes) || promocoes.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Nenhuma promocao encontrada</td></tr>';
                if (totalEl) totalEl.textContent = '0 promocao(oes)';
                return;
            }

            tbody.innerHTML = promocoes.map(item => {
                const statusClass = `status-${item.status}`;
                return `
                    <tr>
                        <td>${escapeHtml(item.codigo_promocao)}</td>
                        <td>${escapeHtml(item.nome_promocao)}</td>
                        <td>${escapeHtml(item.tipo_promocao)}</td>
                        <td>${formatarDataHora(item.data_inicio)}</td>
                        <td>${formatarDataHora(item.data_fim)}</td>
                        <td><span class="status-badge ${statusClass}">${escapeHtml(item.status)}</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="abrirDetalhesPromocao(${item.id})">
                                <i class="bi bi-eye"></i>
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');

            if (totalEl) totalEl.textContent = `${promocoes.length} promocao(oes)`;
        }

        function abrirModalPromocao() {
            document.getElementById('formPromocao').reset();
            document.getElementById('promocaoId').value = '';
            document.getElementById('modalPromocaoTitulo').textContent = 'Nova promocao';
            document.getElementById('btnExcluirPromocao').style.display = 'none';
            modalPromocao.show();
        }

        async function salvarPromocao() {
            const id = document.getElementById('promocaoId').value;
            const payload = {
                id_empresa: idEmpresa,
                id_filial: parseInt(document.getElementById('promocaoFilial').value || '0', 10),
                codigo_promocao: document.getElementById('promocaoCodigo').value.trim(),
                nome_promocao: document.getElementById('promocaoNome').value.trim(),
                tipo_promocao: document.getElementById('promocaoTipo').value,
                data_inicio: formatarDateTimeLocal(document.getElementById('promocaoInicio').value),
                data_fim: formatarDateTimeLocal(document.getElementById('promocaoFim').value),
                status: document.getElementById('promocaoStatus').value,
                prioridade: parseInt(document.getElementById('promocaoPrioridade').value || '1', 10),
                aplicar_em_todas_filiais: parseInt(document.getElementById('promocaoTodas').value || '0', 10)
            };

            const descricao = document.getElementById('promocaoDescricao').value.trim();
            if (descricao) {
                payload.descricao = descricao;
            }

            if (!payload.id_filial || !payload.codigo_promocao || !payload.nome_promocao || !payload.tipo_promocao || !payload.data_inicio || !payload.data_fim) {
                mostrarNotificacao('Preencha os campos obrigatorios.', 'warning');
                return;
            }

            mostrarLoading(true);
            try {
                const url = id ? `${API_CONFIG.BASE_URL}${API_CONFIG.PROMOCOES}/${id}` : `${API_CONFIG.BASE_URL}${API_CONFIG.PROMOCOES}`;
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

                modalPromocao.hide();
                mostrarNotificacao('Promocao salva.', 'success');
                carregarPromocoes();
            } catch (error) {
                console.error('Erro ao salvar promocao:', error);
                mostrarNotificacao('Erro ao salvar promocao: ' + error.message, 'error');
            } finally {
                mostrarLoading(false);
            }
        }

        async function excluirPromocao() {
            const id = document.getElementById('promocaoId').value;
            if (!id) return;
            if (!confirm('Deseja excluir esta promocao?')) return;

            mostrarLoading(true);
            try {
                const response = await fetch(`${API_CONFIG.BASE_URL}${API_CONFIG.PROMOCOES}/${id}`, {
                    method: 'DELETE',
                    headers: API_CONFIG.getHeaders()
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    throw new Error(errorText || `Erro ${response.status}`);
                }

                modalPromocao.hide();
                mostrarNotificacao('Promocao excluida.', 'success');
                carregarPromocoes();
            } catch (error) {
                console.error('Erro ao excluir promocao:', error);
                mostrarNotificacao('Erro ao excluir promocao: ' + error.message, 'error');
            } finally {
                mostrarLoading(false);
            }
        }

        function abrirDetalhesPromocao(id) {
            promocaoSelecionadaId = id;
            modalDetalhes.show();
            carregarDetalhesPromocao();
        }

        async function carregarDetalhesPromocao() {
            if (!promocaoSelecionadaId) return;
            mostrarLoading(true);
            try {
                const response = await fetch(`${API_CONFIG.BASE_URL}${API_CONFIG.PROMOCOES}/${promocaoSelecionadaId}`, {
                    method: 'GET',
                    headers: API_CONFIG.getHeaders()
                });

                if (!response.ok) {
                    throw new Error(`Erro ${response.status}: ${response.statusText}`);
                }

                const data = await parseJsonResponse(response);
                promocaoDetalhe = normalizarPromocaoDetalhe(data);
                renderizarDetalhesPromocao();
            } catch (error) {
                console.error('Erro ao carregar detalhes:', error);
                mostrarNotificacao('Erro ao carregar detalhes: ' + error.message, 'error');
            } finally {
                mostrarLoading(false);
            }
        }

        function renderizarDetalhesPromocao() {
            if (!promocaoDetalhe) return;

            document.getElementById('detalhePromocaoTitulo').textContent = `Promocao ${promocaoDetalhe.codigo_promocao}`;
            document.getElementById('detalhePromocaoSubtitulo').textContent = `Status: ${promocaoDetalhe.status} | Produtos: ${promocaoDetalhe.produtos.length}`;

            document.getElementById('detalheCodigo').value = promocaoDetalhe.codigo_promocao || '';
            document.getElementById('detalheNome').value = promocaoDetalhe.nome_promocao || '';
            document.getElementById('detalheTipo').value = promocaoDetalhe.tipo_promocao || '';
            document.getElementById('detalheInicio').value = formatarDateTimeInput(promocaoDetalhe.data_inicio);
            document.getElementById('detalheFim').value = formatarDateTimeInput(promocaoDetalhe.data_fim);
            document.getElementById('detalheStatus').value = promocaoDetalhe.status || 'rascunho';
            document.getElementById('detalhePrioridade').value = promocaoDetalhe.prioridade || 1;

            renderizarProdutosPromocao();
        }

        function renderizarProdutosPromocao() {
            const tbody = document.getElementById('tbodyPromocaoProdutos');
            if (!tbody) return;

            if (!Array.isArray(promocaoDetalhe.produtos) || promocaoDetalhe.produtos.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Nenhum produto vinculado</td></tr>';
                return;
            }

            tbody.innerHTML = promocaoDetalhe.produtos.map(item => {
                const desconto = calcularDesconto(item.preco_normal, item.preco_promocional);
                return `
                    <tr>
                        <td>${escapeHtml(item.produto_nome)}</td>
                        <td>${formatarMoeda(item.preco_normal)}</td>
                        <td>${formatarMoeda(item.preco_promocional)}</td>
                        <td>${desconto}%</td>
                        <td>
                            ${item.id_promocao_produto ? `<button class=\"btn btn-sm btn-outline-danger\" onclick=\"removerProdutoPromocao(${item.id_promocao_produto})\"><i class=\"bi bi-trash\"></i></button>` : ''}
                        </td>
                    </tr>
                `;
            }).join('');
        }

        async function atualizarPromocaoDetalhe() {
            if (!promocaoSelecionadaId) return;

            const payload = {
                nome_promocao: document.getElementById('detalheNome').value.trim(),
                data_inicio: formatarDateTimeLocal(document.getElementById('detalheInicio').value),
                data_fim: formatarDateTimeLocal(document.getElementById('detalheFim').value),
                status: document.getElementById('detalheStatus').value,
                prioridade: parseInt(document.getElementById('detalhePrioridade').value || '1', 10)
            };

            mostrarLoading(true);
            try {
                const response = await fetch(`${API_CONFIG.BASE_URL}${API_CONFIG.PROMOCOES}/${promocaoSelecionadaId}`, {
                    method: 'PUT',
                    headers: API_CONFIG.getHeaders(),
                    body: JSON.stringify(payload)
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    throw new Error(errorText || `Erro ${response.status}`);
                }

                mostrarNotificacao('Promocao atualizada.', 'success');
                carregarDetalhesPromocao();
                carregarPromocoes();
            } catch (error) {
                console.error('Erro ao atualizar promocao:', error);
                mostrarNotificacao('Erro ao atualizar promocao: ' + error.message, 'error');
            } finally {
                mostrarLoading(false);
            }
        }

        async function ativarPromocao() {
            await acionarPromocao('ativar');
        }

        async function pausarPromocao() {
            await acionarPromocao('pausar');
        }

        async function encerrarPromocao() {
            await acionarPromocao('encerrar');
        }

        async function acionarPromocao(acao) {
            if (!promocaoSelecionadaId) return;
            mostrarLoading(true);
            try {
                const response = await fetch(`${API_CONFIG.BASE_URL}${API_CONFIG.PROMOCOES}/${promocaoSelecionadaId}/${acao}`, {
                    method: 'POST',
                    headers: API_CONFIG.getHeaders()
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    throw new Error(errorText || `Erro ${response.status}`);
                }

                mostrarNotificacao('Promocao atualizada.', 'success');
                carregarDetalhesPromocao();
                carregarPromocoes();
            } catch (error) {
                console.error('Erro ao alterar promocao:', error);
                mostrarNotificacao('Erro ao alterar promocao: ' + error.message, 'error');
            } finally {
                mostrarLoading(false);
            }
        }

        async function adicionarProdutoPromocao() {
            if (!promocaoSelecionadaId) return;
            const idProduto = document.getElementById('promocaoProduto').value;
            if (!idProduto) {
                mostrarNotificacao('Selecione um produto.', 'warning');
                return;
            }

            const payload = {
                id_produto: parseInt(idProduto, 10),
                preco_normal: parseFloat(document.getElementById('promocaoPrecoNormal').value || '0') || null,
                preco_promocional: parseFloat(document.getElementById('promocaoPrecoPromocional').value || '0') || null
            };

            if (!payload.preco_promocional) {
                mostrarNotificacao('Informe o preco promocional.', 'warning');
                return;
            }

            mostrarLoading(true);
            try {
                const response = await fetch(`${API_CONFIG.BASE_URL}${API_CONFIG.PROMOCOES}/${promocaoSelecionadaId}/produtos`, {
                    method: 'POST',
                    headers: API_CONFIG.getHeaders(),
                    body: JSON.stringify(payload)
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    throw new Error(errorText || `Erro ${response.status}`);
                }

                document.getElementById('promocaoProduto').value = '';
                document.getElementById('promocaoPrecoNormal').value = '';
                document.getElementById('promocaoPrecoPromocional').value = '';

                mostrarNotificacao('Produto adicionado.', 'success');
                carregarDetalhesPromocao();
            } catch (error) {
                console.error('Erro ao adicionar produto:', error);
                mostrarNotificacao('Erro ao adicionar produto: ' + error.message, 'error');
            } finally {
                mostrarLoading(false);
            }
        }

        async function removerProdutoPromocao(idProdutoPromocao) {
            if (!promocaoSelecionadaId || !idProdutoPromocao) return;
            if (!confirm('Remover este produto da promocao?')) return;

            mostrarLoading(true);
            try {
                const response = await fetch(`${API_CONFIG.BASE_URL}${API_CONFIG.PROMOCOES}/${promocaoSelecionadaId}/produtos/${idProdutoPromocao}`, {
                    method: 'DELETE',
                    headers: API_CONFIG.getHeaders()
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    throw new Error(errorText || `Erro ${response.status}`);
                }

                mostrarNotificacao('Produto removido.', 'success');
                carregarDetalhesPromocao();
            } catch (error) {
                console.error('Erro ao remover produto:', error);
                mostrarNotificacao('Erro ao remover produto: ' + error.message, 'error');
            } finally {
                mostrarLoading(false);
            }
        }


        async function carregarFiliais() {
            const select = document.getElementById('promocaoFilial');
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
                const raw = normalizarLista(data);
                filiais = raw.map(item => ({
                    id_filial: item.id_filial ?? item.id ?? null,
                    nome_filial: item.nome_filial ?? item.nome ?? '',
                    cidade: item.cidade ?? '',
                    estado: item.estado ?? ''
                })).filter(item => item.id_filial);

                if (filiais.length === 0) {
                    select.innerHTML = '<option value="">Nenhuma filial encontrada</option>';
                    return;
                }

                select.innerHTML = '<option value="">Selecione a filial</option>';
                filiais.forEach(filial => {
                    const option = document.createElement('option');
                    option.value = filial.id_filial;
                    const local = [filial.cidade, filial.estado].filter(Boolean).join('/');
                    option.textContent = `${filial.nome_filial}${local ? ' - ' + local : ''}`;
                    select.appendChild(option);
                });
            } catch (error) {
                console.error('Erro ao carregar filiais:', error);
                select.innerHTML = '<option value="">Nenhuma filial encontrada</option>';
            }
        }

        async function carregarProdutos() {
            const select = document.getElementById('promocaoProduto');
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
            const select = document.getElementById('promocaoProduto');
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

        function normalizarPromocao(item) {
            const status = item.status ?? 'rascunho';
            return {
                id: item.id_promocao ?? item.id ?? null,
                codigo_promocao: item.codigo_promocao ?? item.codigo ?? '',
                nome_promocao: item.nome_promocao ?? item.nome ?? '',
                tipo_promocao: item.tipo_promocao ?? item.tipo ?? '',
                data_inicio: item.data_inicio ?? item.inicio ?? '',
                data_fim: item.data_fim ?? item.fim ?? '',
                status: String(status).toLowerCase(),
                prioridade: item.prioridade ?? 1
            };
        }

        function normalizarPromocaoDetalhe(data) {
            let origem = data ?? {};
            if (origem.success && origem.data) origem = origem.data;
            if (origem.data) origem = origem.data;

            const promo = normalizarPromocao(origem);
            promo.prioridade = origem.prioridade ?? promo.prioridade ?? 1;

            const produtosRaw = origem.produtos ?? origem.produtos_promocao ?? origem.itens ?? origem.items ?? [];
            promo.produtos = Array.isArray(produtosRaw) ? produtosRaw.map(normalizarProdutoPromocao) : [];
            return promo;
        }

        function normalizarProdutoPromocao(item) {
            return {
                id_promocao_produto: item.id_promocao_produto ?? item.id ?? null,
                produto_nome: item.produto?.descricao ?? item.produto?.nome ?? item.nome_produto ?? item.produto_nome ?? item.descricao ?? '',
                preco_normal: parseFloat(item.preco_normal ?? item.preco_base ?? item.preco ?? 0),
                preco_promocional: parseFloat(item.preco_promocional ?? item.preco_atual ?? item.preco ?? 0)
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

        function calcularDesconto(precoNormal, precoPromocional) {
            const normal = parseFloat(precoNormal || 0);
            const promo = parseFloat(precoPromocional || 0);
            if (!normal || !promo) return '0.00';
            return ((normal - promo) / normal * 100).toFixed(2);
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
