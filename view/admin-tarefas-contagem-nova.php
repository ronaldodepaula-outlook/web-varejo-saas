<?php
$config = require __DIR__ . '/../funcoes/config.php';

session_start();
if (!isset($_SESSION['authToken'], $_SESSION['usuario'], $_SESSION['empresa'], $_SESSION['licenca'])) {
    header('Location: login.php');
    exit;
}

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
    <?php if (!defined('APP_SHELL')) { $pageTitle = 'Nova Tarefa - SAS Multi'; include __DIR__ . '/../components/app-head.php'; } ?>
    <style>
        :root {
            --primary-color: #3498DB;
            --secondary-color: #2C3E50;
            --success-color: #27AE60;
            --warning-color: #F39C12;
            --danger-color: #E74C3C;
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

        .badge-item {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(52, 152, 219, 0.1);
            color: #3498DB;
            padding: 4px 10px;
            border-radius: 20px;
            margin: 4px 6px 0 0;
        }

        .badge-item button {
            border: none;
            background: transparent;
            color: inherit;
            padding: 0;
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
                        <li class="breadcrumb-item active">Nova Tarefa</li>
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
                    <h1 class="page-title">Nova Tarefa de Contagem</h1>
                    <p class="page-subtitle">Preencha os dados para criar a tarefa</p>
                </div>
            </div>

            <div class="card-custom">
                <div class="card-header-custom">
                    <h5 class="mb-0">Dados da Tarefa</h5>
                </div>
                <div class="card-body">
                    <form id="taskForm">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">ID da Capa de Inventário *</label>
                                <input type="number" class="form-control" id="inputInventario" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">ID do Usuário *</label>
                                <input type="number" class="form-control" id="inputUsuario" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">ID do Supervisor *</label>
                                <input type="number" class="form-control" id="inputSupervisor" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tipo de Tarefa *</label>
                                <select class="form-select" id="inputTipo" required>
                                    <option value="">Selecione</option>
                                    <option value="contagem_inicial">Contagem inicial</option>
                                    <option value="recontagem">Recontagem</option>
                                    <option value="conferencia">Conferência</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Observações</label>
                                <input type="text" class="form-control" id="inputObservacoes" placeholder="Observações opcionais">
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="form-label">Produtos (opcional)</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="produtoInput" placeholder="Digite o ID do produto">
                                <button class="btn btn-outline-primary" type="button" id="btnAddProduto">Adicionar</button>
                            </div>
                            <div id="produtosSelecionados" class="mt-2"></div>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Criar Tarefa
                            </button>
                            <a class="btn btn-outline-secondary" href="?view=admin-tarefas-contagem">
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

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

        const produtosSelecionados = new Set();

        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('btnAddProduto').addEventListener('click', adicionarProduto);
            document.getElementById('taskForm').addEventListener('submit', criarTarefa);

            const logoutBtn = document.getElementById('logoutBtn');
            if (logoutBtn) {
                logoutBtn.addEventListener('click', (event) => {
                    event.preventDefault();
                    fazerLogout();
                });
            }
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

        function adicionarProduto() {
            const input = document.getElementById('produtoInput');
            const valor = Number(input.value.trim());

            if (!Number.isInteger(valor) || valor <= 0) {
                mostrarNotificacao('Informe um ID de produto válido.', 'warning');
                return;
            }

            produtosSelecionados.add(valor);
            input.value = '';
            renderProdutosSelecionados();
        }

        function removerProduto(id) {
            produtosSelecionados.delete(id);
            renderProdutosSelecionados();
        }

        function renderProdutosSelecionados() {
            const container = document.getElementById('produtosSelecionados');
            if (produtosSelecionados.size === 0) {
                container.innerHTML = '<small class="text-muted">Nenhum produto adicionado.</small>';
                return;
            }

            container.innerHTML = Array.from(produtosSelecionados).map(id => `
                <span class="badge-item">
                    #${id}
                    <button type="button" onclick="removerProduto(${id})">
                        <i class="bi bi-x"></i>
                    </button>
                </span>
            `).join('');
        }

        async function criarTarefa(event) {
            event.preventDefault();

            const inventario = document.getElementById('inputInventario').value;
            const usuario = document.getElementById('inputUsuario').value;
            const supervisor = document.getElementById('inputSupervisor').value;
            const tipo = document.getElementById('inputTipo').value;
            const observacoes = document.getElementById('inputObservacoes').value.trim();

            if (!inventario || !usuario || !supervisor || !tipo) {
                mostrarNotificacao('Preencha todos os campos obrigatórios.', 'warning');
                return;
            }

            const payload = {
                id_capa_inventario: Number(inventario),
                id_usuario: Number(usuario),
                id_supervisor: Number(supervisor),
                tipo_tarefa: tipo,
                observacoes: observacoes || null
            };

            if (produtosSelecionados.size > 0) {
                payload.produtos = Array.from(produtosSelecionados);
            }

            try {
                mostrarLoading(true);
                const response = await fetch(API_CONFIG.TAREFAS, {
                    method: 'POST',
                    headers: API_CONFIG.getJsonHeaders(),
                    body: JSON.stringify(payload)
                });

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}));
                    throw new Error(errorData.message || `Erro ${response.status}: ${response.statusText}`);
                }

                const data = await response.json();
                const tarefaId = data.data?.id_tarefa || data.id_tarefa;

                mostrarNotificacao('Tarefa criada com sucesso!', 'success');
                if (tarefaId) {
                    window.location.href = `?view=admin-tarefas-contagem-detalhe&id=${tarefaId}`;
                } else {
                    window.location.href = '?view=admin-tarefas-contagem';
                }
            } catch (error) {
                console.error('Erro ao criar tarefa:', error);
                mostrarNotificacao(error.message || 'Erro ao criar tarefa.', 'error');
            } finally {
                mostrarLoading(false);
            }
        }

        renderProdutosSelecionados();
    </script>
    <?php if (!defined('APP_SHELL')) { include __DIR__ . '/../components/app-foot.php'; } ?>
</body>
</html>

