<?php
$config = require __DIR__ . '/../funcoes/config.php';

session_start();
if (!isset($_SESSION['authToken'], $_SESSION['usuario'], $_SESSION['empresa'], $_SESSION['licenca'])) {
    header('Location: login.php');
    exit;
}

$usuario = is_array($_SESSION['usuario']) ? $_SESSION['usuario'] : ['nome' => $_SESSION['usuario']];
$empresa = $_SESSION['empresa'];
$licenca = $_SESSION['licenca'];
$token = $_SESSION['authToken'];
$segmento = $_SESSION['segmento'] ?? '';

$nomeUsuario = '';
if (is_array($usuario)) {
    $nomeUsuario = $usuario['nome'] ?? $usuario['name'] ?? 'Usuário';
} else {
    $nomeUsuario = (string)$usuario;
}

$inicialUsuario = strtoupper(substr($nomeUsuario, 0, 1));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?php if (!defined('APP_SHELL')) { $pageTitle = 'Tarefas de Contagem - SAS Multi'; include __DIR__ . '/../components/app-head.php'; } ?>

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

        .status-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .status-pendente { background: rgba(243, 156, 18, 0.15); color: var(--warning-color); }
        .status-em_andamento { background: rgba(52, 152, 219, 0.15); color: var(--primary-color); }
        .status-pausada { background: rgba(231, 76, 60, 0.15); color: var(--danger-color); }
        .status-concluida { background: rgba(39, 174, 96, 0.15); color: var(--success-color); }
        .status-cancelada { background: rgba(108, 117, 125, 0.15); color: #6c757d; }

        .btn-action {
            width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            padding: 0;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
        }

        .table-custom th {
            border-top: none;
            font-weight: 600;
            color: #6c757d;
            font-size: 0.85rem;
            text-transform: uppercase;
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
                        <li class="breadcrumb-item active">Tarefas de Contagem</li>
                    </ol>
                </nav>
            </div>

            <div class="header-right">
                <div class="dropdown">
                    <button class="btn btn-link text-dark position-relative" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-bell" style="font-size: 1.25rem;"></i>
                        <span class="badge bg-danger position-absolute translate-middle rounded-pill" style="font-size: 0.6rem; top: 8px; right: 8px;">2</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><h6 class="dropdown-header">Alertas de Inventário</h6></li>
                        <li><a class="dropdown-item" href="#">Tarefas aguardando início</a></li>
                        <li><a class="dropdown-item" href="#">Tarefas em andamento</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-center" href="#">Ver todos</a></li>
                    </ul>
                </div>

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
                        <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Configurações</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="#" id="logoutBtn"><i class="bi bi-box-arrow-right me-2"></i>Sair</a></li>
                    </ul>
                </div>
            </div>
        </header>

        <div class="content-area">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="page-title">Tarefas de Contagem</h1>
                    <p class="page-subtitle">Crie, acompanhe e controle as tarefas de contagem de inventário</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary" id="btnReload">
                        <i class="bi bi-arrow-clockwise me-2"></i>Atualizar
                    </button>
                    <a class="btn btn-primary" href="?view=admin-tarefas-contagem-nova">
                        <i class="bi bi-plus-circle me-2"></i>Nova Tarefa
                    </a>
                </div>
            </div>
            <div class="card-custom mb-4">
                <div class="card-header-custom">
                    <h5 class="mb-0">Filtros</h5>
                </div>
                <div class="card-body">
                    <form id="filtersForm">
                        <div class="filter-grid">
                            <div>
                                <label class="form-label">Status</label>
                                <select class="form-select" id="filterStatus">
                                    <option value="">Todos</option>
                                    <option value="pendente">Pendente</option>
                                    <option value="em_andamento">Em andamento</option>
                                    <option value="pausada">Pausada</option>
                                    <option value="concluida">Concluída</option>
                                    <option value="cancelada">Cancelada</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Inventário (ID)</label>
                                <input type="number" class="form-control" id="filterInventario" placeholder="ID da capa">
                            </div>
                            <div>
                                <label class="form-label">Usuário (ID)</label>
                                <input type="number" class="form-control" id="filterUsuario" placeholder="ID do usuário">
                            </div>
                            <div>
                                <label class="form-label">Data início</label>
                                <input type="date" class="form-control" id="filterDataInicio">
                            </div>
                            <div>
                                <label class="form-label">Data fim</label>
                                <input type="date" class="form-control" id="filterDataFim">
                            </div>
                            <div>
                                <label class="form-label">Itens por página</label>
                                <select class="form-select" id="filterPerPage">
                                    <option value="15">15</option>
                                    <option value="30">30</option>
                                    <option value="50">50</option>
                                </select>
                            </div>
                        </div>
                        <div class="d-flex gap-2 mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-funnel me-2"></i>Aplicar filtros
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="btnClearFilters">
                                <i class="bi bi-x-circle me-2"></i>Limpar
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card-custom">
                <div class="card-header-custom d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Lista de Tarefas</h5>
                        <small class="text-muted" id="tarefasResumo">Carregando...</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted" id="tarefasPagina">Página 1</span>
                        <button class="btn btn-sm btn-outline-primary" id="btnPrevPage">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-primary" id="btnNextPage">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-custom align-middle">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Inventário</th>
                                    <th>Usuário</th>
                                    <th>Supervisor</th>
                                    <th>Tipo</th>
                                    <th>Status</th>
                                    <th>Datas</th>
                                    <th>Produtos</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody id="tarefasTableBody">
                                <tr>
                                    <td colspan="9" class="text-center text-muted">Carregando tarefas...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <div class="modal fade" id="actionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="actionModalTitle">Ação</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning d-none" id="actionModalAlert"></div>
                    <div class="mb-3" id="observacoesGroup">
                        <label class="form-label" id="observacoesLabel">Observações</label>
                        <textarea class="form-control" id="observacoesInput" rows="3"></textarea>
                    </div>
                    <div class="mb-3 d-none" id="motivoGroup">
                        <label class="form-label">Motivo</label>
                        <textarea class="form-control" id="motivoInput" rows="3"></textarea>
                    </div>
                    <div class="form-check d-none" id="forcarConclusaoGroup">
                        <input class="form-check-input" type="checkbox" id="forcarConclusaoInput">
                        <label class="form-check-label" for="forcarConclusaoInput">
                            Forçar conclusão (ignorar pendências)
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="actionModalConfirm">Confirmar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="loading-overlay d-none" id="loadingOverlay">
        <div class="spinner-border text-light" role="status"></div>
    </div>
    <script>
        const BASE_URL = '<?= addslashes($config['api_base']) ?>';
        const API_TOKEN = '<?= addslashes($token) ?>';
        const API_CONFIG = {
            TAREFAS: `${BASE_URL}/api/inventario/tarefas`,
            getHeaders() {
                return {
                    'Authorization': `Bearer ${API_TOKEN}`,
                    'Accept': 'application/json'
                };
            },
            getJsonHeaders() {
                return {
                    'Authorization': `Bearer ${API_TOKEN}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                };
            }
        };

        const STATUS_LABELS = {
            pendente: 'Pendente',
            em_andamento: 'Em andamento',
            pausada: 'Pausada',
            concluida: 'Concluída',
            cancelada: 'Cancelada'
        };

        const TIPO_LABELS = {
            contagem_inicial: 'Contagem inicial',
            recontagem: 'Recontagem',
            conferencia: 'Conferência'
        };

        const ACTION_CONFIG = {
            iniciar: {
                title: 'Iniciar tarefa',
                field: 'observacoes',
                placeholder: 'Iniciando contagem...'
            },
            pausar: {
                title: 'Pausar tarefa',
                field: 'motivo',
                placeholder: 'Informe o motivo da pausa'
            },
            retomar: {
                title: 'Retomar tarefa',
                field: 'observacoes',
                placeholder: 'Retomando contagem...'
            },
            concluir: {
                title: 'Concluir tarefa',
                field: 'observacoes',
                placeholder: 'Conclusão da contagem',
                force: true
            },
            cancelar: {
                title: 'Cancelar tarefa',
                field: 'motivo',
                placeholder: 'Informe o motivo do cancelamento'
            }
        };

        let tarefas = [];
        let currentPage = 1;
        let lastPage = 1;
        let actionModal;
        let actionTaskId = null;
        let actionType = null;

        document.addEventListener('DOMContentLoaded', () => {
            actionModal = new bootstrap.Modal(document.getElementById('actionModal'));
            document.getElementById('filtersForm').addEventListener('submit', (event) => {
                event.preventDefault();
                currentPage = 1;
                carregarTarefas();
            });

            document.getElementById('btnClearFilters').addEventListener('click', () => {
                limparFiltros();
                currentPage = 1;
                carregarTarefas();
            });

            document.getElementById('btnReload').addEventListener('click', () => {
                carregarTarefas();
            });

            document.getElementById('btnPrevPage').addEventListener('click', () => {
                if (currentPage > 1) {
                    currentPage -= 1;
                    carregarTarefas();
                }
            });

            document.getElementById('btnNextPage').addEventListener('click', () => {
                if (currentPage < lastPage) {
                    currentPage += 1;
                    carregarTarefas();
                }
            });

            document.getElementById('actionModalConfirm').addEventListener('click', confirmarAcao);

            const logoutBtn = document.getElementById('logoutBtn');
            if (logoutBtn) {
                logoutBtn.addEventListener('click', (event) => {
                    event.preventDefault();
                    fazerLogout();
                });
            }

            carregarTarefas();
        });

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

        async function fazerLogout() {
            try {
                await fetch(`${BASE_URL}/api/v1/logout`, {
                    method: 'POST',
                    headers: API_CONFIG.getHeaders()
                });
            } catch (error) {
                console.error('Erro no logout:', error);
            } finally {
                window.location.href = 'login.php';
            }
        }

        function limparFiltros() {
            document.getElementById('filterStatus').value = '';
            document.getElementById('filterInventario').value = '';
            document.getElementById('filterUsuario').value = '';
            document.getElementById('filterDataInicio').value = '';
            document.getElementById('filterDataFim').value = '';
            document.getElementById('filterPerPage').value = '15';
        }

        function buildQueryParams() {
            const params = new URLSearchParams();
            const status = document.getElementById('filterStatus').value;
            const inventario = document.getElementById('filterInventario').value;
            const usuario = document.getElementById('filterUsuario').value;
            const dataInicio = document.getElementById('filterDataInicio').value;
            const dataFim = document.getElementById('filterDataFim').value;
            const perPage = document.getElementById('filterPerPage').value;

            if (status) params.append('status', status);
            if (inventario) params.append('id_capa_inventario', inventario);
            if (usuario) params.append('id_usuario', usuario);
            if (dataInicio) params.append('data_inicio', dataInicio);
            if (dataFim) params.append('data_fim', dataFim);
            if (perPage) params.append('per_page', perPage);
            if (currentPage > 1) params.append('page', currentPage);

            return params.toString();
        }
        async function carregarTarefas() {
            mostrarLoading(true);
            try {
                const query = buildQueryParams();
                const url = query ? `${API_CONFIG.TAREFAS}?${query}` : API_CONFIG.TAREFAS;
                const response = await fetch(url, {
                    method: 'GET',
                    headers: API_CONFIG.getHeaders()
                });

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}));
                    throw new Error(errorData.message || `Erro ${response.status}: ${response.statusText}`);
                }

                const payload = await response.json();
                const dataBlock = payload.data ?? payload;
                const lista = Array.isArray(dataBlock?.data)
                    ? dataBlock.data
                    : Array.isArray(dataBlock)
                        ? dataBlock
                        : [];

                tarefas = lista;
                lastPage = dataBlock?.last_page ?? 1;
                currentPage = dataBlock?.current_page ?? currentPage;

                renderTarefas(lista);
                atualizarResumo(dataBlock, lista.length);
            } catch (error) {
                console.error('Erro ao carregar tarefas:', error);
                document.getElementById('tarefasTableBody').innerHTML = `
                    <tr><td colspan="9" class="text-center text-muted">Erro ao carregar tarefas.</td></tr>
                `;
                mostrarNotificacao(error.message || 'Erro ao carregar tarefas.', 'error');
                atualizarResumo({}, 0);
            } finally {
                mostrarLoading(false);
            }
        }

        function atualizarResumo(dataBlock, count) {
            const total = dataBlock?.total ?? count;
            document.getElementById('tarefasResumo').textContent = `${total} tarefa(s) encontrada(s)`;
            document.getElementById('tarefasPagina').textContent = `Página ${currentPage}`;

            document.getElementById('btnPrevPage').disabled = currentPage <= 1;
            document.getElementById('btnNextPage').disabled = currentPage >= lastPage;
        }

        function renderTarefas(lista) {
            const tbody = document.getElementById('tarefasTableBody');

            if (!lista || lista.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9" class="text-center text-muted">Nenhuma tarefa encontrada.</td></tr>';
                return;
            }

            tbody.innerHTML = lista.map(tarefa => {
                const inventario = tarefa.inventario?.descricao ? `${tarefa.inventario.descricao} (#${tarefa.id_capa_inventario})` : `#${tarefa.id_capa_inventario}`;
                const usuario = tarefa.usuario?.nome || tarefa.usuario?.name || '-';
                const supervisor = tarefa.supervisor?.nome || tarefa.supervisor?.name || '-';
                const tipo = TIPO_LABELS[tarefa.tipo_tarefa] || tarefa.tipo_tarefa || '-';
                const statusLabel = STATUS_LABELS[tarefa.status] || tarefa.status || '-';
                const dataInicio = formatDateTime(tarefa.data_inicio);
                const dataFim = formatDateTime(tarefa.data_fim);
                const produtosTotal = tarefa.total_produtos ?? tarefa.produtos?.length ?? 0;
                const produtosContados = tarefa.produtos_contados ?? 0;

                return `
                    <tr>
                        <td>#${tarefa.id_tarefa}</td>
                        <td>${inventario}</td>
                        <td>${usuario}</td>
                        <td>${supervisor}</td>
                        <td>${tipo}</td>
                        <td><span class="status-badge status-${tarefa.status}">${statusLabel}</span></td>
                        <td>
                            <div><small class="text-muted">Início:</small> ${dataInicio}</div>
                            <div><small class="text-muted">Fim:</small> ${dataFim}</div>
                        </td>
                        <td>${produtosContados}/${produtosTotal}</td>
                        <td>
                            <div class="d-flex flex-wrap gap-1">
                                <a class="btn btn-outline-primary btn-action" href="?view=admin-tarefas-contagem-detalhe&id=${tarefa.id_tarefa}" title="Detalhes">
                                    <i class="bi bi-eye"></i>
                                </a>
                                ${renderActionButton('iniciar', tarefa)}
                                ${renderActionButton('pausar', tarefa)}
                                ${renderActionButton('retomar', tarefa)}
                                ${renderActionButton('concluir', tarefa)}
                                ${renderActionButton('cancelar', tarefa)}
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        function renderActionButton(action, tarefa) {
            const config = {
                iniciar: { icon: 'bi-play-fill', className: 'btn-outline-success', can: tarefa.status === 'pendente' },
                pausar: { icon: 'bi-pause-fill', className: 'btn-outline-warning', can: tarefa.status === 'em_andamento' },
                retomar: { icon: 'bi-play-circle', className: 'btn-outline-info', can: tarefa.status === 'pausada' },
                concluir: { icon: 'bi-check2-circle', className: 'btn-outline-primary', can: tarefa.status === 'em_andamento' },
                cancelar: { icon: 'bi-x-circle', className: 'btn-outline-danger', can: tarefa.status !== 'concluida' && tarefa.status !== 'cancelada' }
            };

            const actionConfig = config[action];
            if (!actionConfig) return '';

            const disabled = actionConfig.can ? '' : 'disabled';
            const title = action.charAt(0).toUpperCase() + action.slice(1);
            return `
                <button class="btn ${actionConfig.className} btn-action" ${disabled} title="${title}" onclick="abrirAcao('${action}', ${tarefa.id_tarefa})">
                    <i class="bi ${actionConfig.icon}"></i>
                </button>
            `;
        }

        function abrirAcao(action, idTarefa) {
            actionType = action;
            actionTaskId = idTarefa;

            const config = ACTION_CONFIG[action];
            if (!config) return;

            document.getElementById('actionModalTitle').textContent = config.title;
            document.getElementById('actionModalAlert').classList.add('d-none');

            const observacoesGroup = document.getElementById('observacoesGroup');
            const motivoGroup = document.getElementById('motivoGroup');
            const forceGroup = document.getElementById('forcarConclusaoGroup');

            observacoesGroup.classList.toggle('d-none', config.field !== 'observacoes');
            motivoGroup.classList.toggle('d-none', config.field !== 'motivo');
            forceGroup.classList.toggle('d-none', !config.force);

            document.getElementById('observacoesInput').value = '';
            document.getElementById('motivoInput').value = '';
            document.getElementById('forcarConclusaoInput').checked = false;

            if (config.field === 'observacoes') {
                document.getElementById('observacoesInput').placeholder = config.placeholder;
            } else {
                document.getElementById('motivoInput').placeholder = config.placeholder;
            }

            actionModal.show();
        }

        async function confirmarAcao() {
            if (!actionType || !actionTaskId) return;

            const config = ACTION_CONFIG[actionType];
            const payload = {};

            if (config.field === 'observacoes') {
                const observacoes = document.getElementById('observacoesInput').value.trim();
                if (observacoes) payload.observacoes = observacoes;
            }

            if (config.field === 'motivo') {
                const motivo = document.getElementById('motivoInput').value.trim();
                if (!motivo) {
                    mostrarActionAlert('Informe o motivo para continuar.');
                    return;
                }
                payload.motivo = motivo;
            }

            if (config.force) {
                payload.forcar_conclusao = document.getElementById('forcarConclusaoInput').checked;
            }

            try {
                mostrarLoading(true);
                const response = await fetch(`${API_CONFIG.TAREFAS}/${actionTaskId}/${actionType}`, {
                    method: 'PUT',
                    headers: API_CONFIG.getJsonHeaders(),
                    body: JSON.stringify(payload)
                });

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}));
                    throw new Error(errorData.message || `Erro ${response.status}: ${response.statusText}`);
                }

                actionModal.hide();
                mostrarNotificacao('Ação executada com sucesso!', 'success');
                carregarTarefas();
            } catch (error) {
                console.error('Erro na ação:', error);
                mostrarActionAlert(error.message || 'Não foi possível executar a ação.');
            } finally {
                mostrarLoading(false);
            }
        }

        function mostrarActionAlert(message) {
            const alert = document.getElementById('actionModalAlert');
            alert.textContent = message;
            alert.classList.remove('d-none');
        }

        function formatDateTime(value) {
            if (!value) return '-';
            const date = new Date(value);
            if (Number.isNaN(date.getTime())) return value;
            return date.toLocaleString('pt-BR');
        }
    </script>
    <?php if (!defined('APP_SHELL')) { include __DIR__ . '/../components/app-foot.php'; } ?>
</body>
</html>

