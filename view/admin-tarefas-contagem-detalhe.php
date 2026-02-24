<?php
$config = require __DIR__ . '/../funcoes/config.php';

session_start();
if (!isset($_SESSION['authToken'], $_SESSION['usuario'], $_SESSION['empresa'], $_SESSION['licenca'])) {
    header('Location: login.php');
    exit;
}

$id_tarefa = $_GET['id'] ?? '';

$usuario = is_array($_SESSION['usuario']) ? $_SESSION['usuario'] : ['nome' => $_SESSION['usuario']];
$token = $_SESSION['authToken'];

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
    <?php if (!defined('APP_SHELL')) { $pageTitle = 'Detalhe da Tarefa - SAS Multi'; include __DIR__ . '/../components/app-head.php'; } ?>
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

        .table-custom th {
            border-top: none;
            font-weight: 600;
            color: #6c757d;
            font-size: 0.85rem;
            text-transform: uppercase;
        }

        .timeline {
            border-left: 2px solid #e2e6ea;
            margin-left: 10px;
            padding-left: 20px;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 20px;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -29px;
            top: 4px;
            width: 12px;
            height: 12px;
            background: var(--primary-color);
            border-radius: 50%;
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
                        <li class="breadcrumb-item"><a href="?view=admin-tarefas-contagem">Tarefas de Contagem</a></li>
                        <li class="breadcrumb-item active">Detalhe</li>
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
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="#" id="logoutBtn"><i class="bi bi-box-arrow-right me-2"></i>Sair</a></li>
                    </ul>
                </div>
            </div>
        </header>

        <div class="content-area">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="page-title">Detalhe da Tarefa</h1>
                    <p class="page-subtitle" id="taskSubtitle">Carregando informações...</p>
                </div>
                <div class="d-flex gap-2" id="actionButtons">
                    <button class="btn btn-outline-success" id="btnIniciar" disabled>
                        <i class="bi bi-play-fill me-2"></i>Iniciar
                    </button>
                    <button class="btn btn-outline-warning" id="btnPausar" disabled>
                        <i class="bi bi-pause-fill me-2"></i>Pausar
                    </button>
                    <button class="btn btn-outline-info" id="btnRetomar" disabled>
                        <i class="bi bi-play-circle me-2"></i>Retomar
                    </button>
                    <button class="btn btn-outline-primary" id="btnConcluir" disabled>
                        <i class="bi bi-check2-circle me-2"></i>Concluir
                    </button>
                    <button class="btn btn-outline-danger" id="btnCancelar" disabled>
                        <i class="bi bi-x-circle me-2"></i>Cancelar
                    </button>
                </div>
            </div>
            <div class="row g-3 mb-4">
                <div class="col-lg-7">
                    <div class="card-custom">
                        <div class="card-header-custom">
                            <h5 class="mb-0">Resumo</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="text-muted">Inventário</div>
                                    <div class="fw-semibold" id="resumoInventario">-</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-muted">Status</div>
                                    <div id="resumoStatus">-</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-muted">Usuário</div>
                                    <div class="fw-semibold" id="resumoUsuario">-</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-muted">Supervisor</div>
                                    <div class="fw-semibold" id="resumoSupervisor">-</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-muted">Tipo</div>
                                    <div class="fw-semibold" id="resumoTipo">-</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-muted">Datas</div>
                                    <div class="fw-semibold" id="resumoDatas">-</div>
                                </div>
                                <div class="col-md-12">
                                    <div class="text-muted">Observações</div>
                                    <div id="resumoObservacoes">-</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="card-custom">
                        <div class="card-header-custom">
                            <h5 class="mb-0">Produtos</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-muted">Adicionar produtos por ID</span>
                            </div>
                            <div class="input-group mb-2">
                                <input type="text" class="form-control" id="produtosInput" placeholder="Ex: 191, 192, 193">
                                <button class="btn btn-primary" id="btnAdicionarProdutos">Adicionar</button>
                            </div>
                            <small class="text-muted">Separe os IDs com vírgula.</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-custom mb-4">
                <div class="card-header-custom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Lista de Produtos</h5>
                    <span class="text-muted" id="produtosResumo">0 itens</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-custom align-middle">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Descrição</th>
                                    <th>Quantidade Contada</th>
                                    <th>Status</th>
                                    <th>Observação</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody id="produtosTableBody">
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Carregando produtos...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card-custom">
                <div class="card-header-custom">
                    <h5 class="mb-0">Histórico</h5>
                </div>
                <div class="card-body">
                    <div class="timeline" id="historicoTimeline">
                        <div class="text-muted">Carregando histórico...</div>
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
                        <label class="form-label">Observações</label>
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
        const TAREFA_ID = '<?= addslashes($id_tarefa) ?>';

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
            iniciar: { title: 'Iniciar tarefa', field: 'observacoes', placeholder: 'Iniciando contagem...' },
            pausar: { title: 'Pausar tarefa', field: 'motivo', placeholder: 'Informe o motivo da pausa' },
            retomar: { title: 'Retomar tarefa', field: 'observacoes', placeholder: 'Retomando contagem...' },
            concluir: { title: 'Concluir tarefa', field: 'observacoes', placeholder: 'Conclusão da contagem', force: true },
            cancelar: { title: 'Cancelar tarefa', field: 'motivo', placeholder: 'Informe o motivo do cancelamento' }
        };

        let tarefaAtual = null;
        let actionModal;
        let actionType = null;

        document.addEventListener('DOMContentLoaded', () => {
            actionModal = new bootstrap.Modal(document.getElementById('actionModal'));
            document.getElementById('actionModalConfirm').addEventListener('click', confirmarAcao);
            document.getElementById('btnAdicionarProdutos').addEventListener('click', adicionarProdutos);

            document.getElementById('btnIniciar').addEventListener('click', () => abrirAcao('iniciar'));
            document.getElementById('btnPausar').addEventListener('click', () => abrirAcao('pausar'));
            document.getElementById('btnRetomar').addEventListener('click', () => abrirAcao('retomar'));
            document.getElementById('btnConcluir').addEventListener('click', () => abrirAcao('concluir'));
            document.getElementById('btnCancelar').addEventListener('click', () => abrirAcao('cancelar'));

            const logoutBtn = document.getElementById('logoutBtn');
            if (logoutBtn) {
                logoutBtn.addEventListener('click', (event) => {
                    event.preventDefault();
                    fazerLogout();
                });
            }

            if (!TAREFA_ID) {
                mostrarNotificacao('ID da tarefa não informado.', 'error');
                return;
            }

            carregarDetalhes();
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

        async function carregarDetalhes() {
            mostrarLoading(true);
            try {
                const response = await fetch(`${API_CONFIG.TAREFAS}/${TAREFA_ID}`, {
                    method: 'GET',
                    headers: API_CONFIG.getHeaders()
                });

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}));
                    throw new Error(errorData.message || `Erro ${response.status}: ${response.statusText}`);
                }

                const payload = await response.json();
                tarefaAtual = payload.data ?? payload;

                renderResumo(tarefaAtual);
                await Promise.all([carregarProdutos(), carregarHistorico()]);
            } catch (error) {
                console.error('Erro ao carregar detalhes:', error);
                mostrarNotificacao(error.message || 'Erro ao carregar detalhes da tarefa.', 'error');
            } finally {
                mostrarLoading(false);
            }
        }
        function renderResumo(tarefa) {
            const inventario = tarefa.inventario?.descricao ? `${tarefa.inventario.descricao} (#${tarefa.id_capa_inventario})` : `#${tarefa.id_capa_inventario}`;
            const statusLabel = STATUS_LABELS[tarefa.status] || tarefa.status || '-';
            const tipoLabel = TIPO_LABELS[tarefa.tipo_tarefa] || tarefa.tipo_tarefa || '-';

            document.getElementById('taskSubtitle').textContent = `Tarefa #${tarefa.id_tarefa}`;
            document.getElementById('resumoInventario').textContent = inventario;
            document.getElementById('resumoStatus').innerHTML = `<span class="status-badge status-${tarefa.status}">${statusLabel}</span>`;
            document.getElementById('resumoUsuario').textContent = tarefa.usuario?.nome || tarefa.usuario?.name || '-';
            document.getElementById('resumoSupervisor').textContent = tarefa.supervisor?.nome || tarefa.supervisor?.name || '-';
            document.getElementById('resumoTipo').textContent = tipoLabel;
            document.getElementById('resumoDatas').textContent = `${formatDateTime(tarefa.data_inicio)} até ${formatDateTime(tarefa.data_fim)}`;
            document.getElementById('resumoObservacoes').textContent = tarefa.observacoes || '-';

            atualizarAcoes(tarefa.status);
        }

        function atualizarAcoes(status) {
            document.getElementById('btnIniciar').disabled = status !== 'pendente';
            document.getElementById('btnPausar').disabled = status !== 'em_andamento';
            document.getElementById('btnRetomar').disabled = status !== 'pausada';
            document.getElementById('btnConcluir').disabled = status !== 'em_andamento';
            document.getElementById('btnCancelar').disabled = status === 'concluida' || status === 'cancelada';
        }

        async function carregarProdutos() {
            try {
                const response = await fetch(`${API_CONFIG.TAREFAS}/${TAREFA_ID}/produtos`, {
                    method: 'GET',
                    headers: API_CONFIG.getHeaders()
                });

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}));
                    throw new Error(errorData.message || `Erro ${response.status}: ${response.statusText}`);
                }

                const payload = await response.json();
                const produtos = payload.data ?? payload;

                renderProdutos(Array.isArray(produtos) ? produtos : []);
            } catch (error) {
                console.error('Erro ao carregar produtos:', error);
                document.getElementById('produtosTableBody').innerHTML = '<tr><td colspan="6" class="text-center text-muted">Erro ao carregar produtos.</td></tr>';
            }
        }

        function renderProdutos(lista) {
            const tbody = document.getElementById('produtosTableBody');
            document.getElementById('produtosResumo').textContent = `${lista.length} item(ns)`;

            if (!lista || lista.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Nenhum produto vinculado.</td></tr>';
                return;
            }

            tbody.innerHTML = lista.map(produto => {
                const statusLabel = STATUS_LABELS[produto.status] || produto.status || '-';
                const quantidade = produto.quantidade_contada ?? '';
                return `
                    <tr>
                        <td>${produto.id_produto}</td>
                        <td>${produto.descricao || '-'}</td>
                        <td style="max-width: 160px;">
                            <input type="number" step="0.01" class="form-control form-control-sm" id="quantidade-${produto.id_produto}" value="${quantidade}">
                        </td>
                        <td><span class="status-badge status-${produto.status}">${statusLabel}</span></td>
                        <td style="max-width: 200px;">
                            <input type="text" class="form-control form-control-sm" id="observacao-${produto.id_produto}" placeholder="Observação">
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <button class="btn btn-outline-success btn-action" onclick="salvarContagem(${produto.id_produto})" title="Salvar">
                                    <i class="bi bi-check2"></i>
                                </button>
                                <button class="btn btn-outline-danger btn-action" onclick="removerProduto(${produto.id_produto})" title="Remover">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        async function carregarHistorico() {
            try {
                const response = await fetch(`${API_CONFIG.TAREFAS}/${TAREFA_ID}/historico`, {
                    method: 'GET',
                    headers: API_CONFIG.getHeaders()
                });

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}));
                    throw new Error(errorData.message || `Erro ${response.status}: ${response.statusText}`);
                }

                const payload = await response.json();
                const historico = payload.data ?? payload;
                renderHistorico(Array.isArray(historico) ? historico : []);
            } catch (error) {
                console.error('Erro ao carregar histórico:', error);
                document.getElementById('historicoTimeline').innerHTML = '<div class="text-muted">Erro ao carregar histórico.</div>';
            }
        }

        function renderHistorico(lista) {
            const container = document.getElementById('historicoTimeline');
            if (!lista || lista.length === 0) {
                container.innerHTML = '<div class="text-muted">Nenhum registro encontrado.</div>';
                return;
            }

            container.innerHTML = lista.map(item => `
                <div class="timeline-item">
                    <div class="fw-semibold">${item.descricao || item.acao || 'Ação'}</div>
                    <small class="text-muted">${formatDateTime(item.data)}</small>
                </div>
            `).join('');
        }

        async function adicionarProdutos() {
            const input = document.getElementById('produtosInput');
            const raw = input.value.trim();
            if (!raw) {
                mostrarNotificacao('Informe os IDs dos produtos.', 'warning');
                return;
            }

            const ids = raw.split(',').map((item) => Number(item.trim())).filter((id) => Number.isInteger(id) && id > 0);
            if (!ids.length) {
                mostrarNotificacao('IDs de produtos inválidos.', 'warning');
                return;
            }

            try {
                mostrarLoading(true);
                const response = await fetch(`${API_CONFIG.TAREFAS}/${TAREFA_ID}/produtos`, {
                    method: 'POST',
                    headers: API_CONFIG.getJsonHeaders(),
                    body: JSON.stringify({ produtos: ids })
                });

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}));
                    throw new Error(errorData.message || `Erro ${response.status}: ${response.statusText}`);
                }

                input.value = '';
                mostrarNotificacao('Produtos adicionados com sucesso!', 'success');
                carregarProdutos();
            } catch (error) {
                console.error('Erro ao adicionar produtos:', error);
                mostrarNotificacao(error.message || 'Erro ao adicionar produtos.', 'error');
            } finally {
                mostrarLoading(false);
            }
        }

        async function salvarContagem(idProduto) {
            const quantidadeInput = document.getElementById(`quantidade-${idProduto}`);
            const observacaoInput = document.getElementById(`observacao-${idProduto}`);
            const quantidade = quantidadeInput.value;

            if (quantidade === '') {
                mostrarNotificacao('Informe a quantidade contada.', 'warning');
                return;
            }
            const quantidadeNumber = Number(quantidade);
            if (Number.isNaN(quantidadeNumber)) {
                mostrarNotificacao('Quantidade inválida.', 'warning');
                return;
            }

            try {
                mostrarLoading(true);
                const response = await fetch(`${API_CONFIG.TAREFAS}/${TAREFA_ID}/produtos/${idProduto}`, {
                    method: 'PUT',
                    headers: API_CONFIG.getJsonHeaders(),
                    body: JSON.stringify({
                        quantidade_contada: quantidadeNumber,
                        observacao: observacaoInput.value.trim()
                    })
                });

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}));
                    throw new Error(errorData.message || `Erro ${response.status}: ${response.statusText}`);
                }

                mostrarNotificacao('Contagem registrada.', 'success');
                carregarProdutos();
            } catch (error) {
                console.error('Erro ao registrar contagem:', error);
                mostrarNotificacao(error.message || 'Erro ao registrar contagem.', 'error');
            } finally {
                mostrarLoading(false);
            }
        }

        async function removerProduto(idProduto) {
            if (!confirm('Deseja remover este produto da tarefa?')) {
                return;
            }

            try {
                mostrarLoading(true);
                const response = await fetch(`${API_CONFIG.TAREFAS}/${TAREFA_ID}/produtos/${idProduto}`, {
                    method: 'DELETE',
                    headers: API_CONFIG.getHeaders()
                });

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}));
                    throw new Error(errorData.message || `Erro ${response.status}: ${response.statusText}`);
                }

                mostrarNotificacao('Produto removido.', 'success');
                carregarProdutos();
            } catch (error) {
                console.error('Erro ao remover produto:', error);
                mostrarNotificacao(error.message || 'Erro ao remover produto.', 'error');
            } finally {
                mostrarLoading(false);
            }
        }

        function abrirAcao(acao) {
            actionType = acao;
            const config = ACTION_CONFIG[acao];
            if (!config) return;

            document.getElementById('actionModalTitle').textContent = config.title;
            document.getElementById('actionModalAlert').classList.add('d-none');

            document.getElementById('observacoesGroup').classList.toggle('d-none', config.field !== 'observacoes');
            document.getElementById('motivoGroup').classList.toggle('d-none', config.field !== 'motivo');
            document.getElementById('forcarConclusaoGroup').classList.toggle('d-none', !config.force);

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
            if (!actionType) return;

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
                const response = await fetch(`${API_CONFIG.TAREFAS}/${TAREFA_ID}/${actionType}`, {
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
                carregarDetalhes();
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

