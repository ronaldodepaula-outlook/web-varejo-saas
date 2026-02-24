<?php
$config = require __DIR__ . '/../funcoes/config.php';

session_start();
if (!isset($_SESSION['authToken'], $_SESSION['usuario'], $_SESSION['empresa'], $_SESSION['licenca'])) {
    header('Location: login.php');
    exit;
}

$usuario = is_array($_SESSION['usuario']) ? $_SESSION['usuario'] : ['nome' => $_SESSION['usuario']];
$empresa = $_SESSION['empresa'] ?? [];
$token = $_SESSION['authToken'];
$id_usuario = $_SESSION['user_id'] ?? ($_SESSION['usuario_id'] ?? ($usuario['id_usuario'] ?? null));
$id_empresa = $_GET['id_empresa']
    ?? ($_SESSION['id_empresa'] ?? ($_SESSION['empresa_id'] ?? ($empresa['id_empresa'] ?? null)));
$segmento = $_SESSION['segmento'] ?? '';
$perfilUsuario = $_SESSION['userRole'] ?? ($usuario['perfil'] ?? ($usuario['role'] ?? ''));

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
    <?php if (!defined('APP_SHELL')) { $pageTitle = 'Gestão de Permissões - SAS Multi'; include __DIR__ . '/../components/app-head.php'; } ?>
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

        .badge-permitido {
            background: rgba(39, 174, 96, 0.15);
            color: var(--success-color);
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 20px;
        }

        .badge-negado {
            background: rgba(231, 76, 60, 0.15);
            color: var(--danger-color);
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 20px;
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

        .tab-content {
            margin-top: 20px;
        }

        .table-custom th {
            border-top: none;
            font-weight: 600;
            color: #6c757d;
            font-size: 0.85rem;
            text-transform: uppercase;
        }

        .form-label {
            font-weight: 600;
            color: var(--secondary-color);
        }

        .btn-action {
            padding: 6px 10px;
            border-radius: 8px;
            font-size: 0.85rem;
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
                <div>
                    <h1 class="page-title">Gestão de Permissões</h1>
                    <p class="page-subtitle">Administre perfis, usuários e permissões da empresa</p>
                </div>
            </div>
            <div class="header-right">
                <a class="btn btn-outline-primary" href="?view=admin-empresas">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
                <div class="dropdown user-dropdown">
                    <div class="user-avatar" data-bs-toggle="dropdown" aria-expanded="false">
                        <?php echo $inicialUsuario; ?>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><h6 class="dropdown-header user-name"><?php echo htmlspecialchars($nomeUsuario); ?></h6></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="?view=perfil"><i class="bi bi-person me-2"></i>Meu Perfil</a></li>
                        <li><a class="dropdown-item text-danger" href="#" id="logoutBtn"><i class="bi bi-box-arrow-right me-2"></i>Sair</a></li>
                    </ul>
                </div>
            </div>
        </header>

        <ul class="nav nav-tabs" id="tabPermissoes" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="usuarios-tab" data-bs-toggle="tab" data-bs-target="#usuarios" type="button" role="tab">
                    Usuários & Permissões
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="perfis-tab" data-bs-toggle="tab" data-bs-target="#perfis" type="button" role="tab">
                    Perfis
                </button>
            </li>
        </ul>

        <div class="tab-content" id="tabPermissoesContent">
            <div class="tab-pane fade show active" id="usuarios" role="tabpanel">
                <div class="card-custom">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Selecionar Usuário</label>
                                <select class="form-select" id="usuarioSelect"></select>
                            </div>
                            <div class="col-md-6 d-flex align-items-end gap-2">
                                <button class="btn btn-outline-secondary" onclick="carregarDadosIniciais()">
                                    <i class="bi bi-arrow-clockwise"></i> Atualizar
                                </button>
                                <span class="text-muted" id="usuarioResumo"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4 mt-1">
                    <div class="col-lg-5">
                        <div class="card-custom">
                            <div class="card-header-custom">
                                <strong>Perfis do Usuário</strong>
                            </div>
                            <div class="card-body">
                                <div id="perfisUsuarioLista" class="mb-3 text-muted">Selecione um usuário.</div>
                                <div class="row g-2">
                                    <div class="col-8">
                                        <select class="form-select" id="perfilAdicionarSelect"></select>
                                    </div>
                                    <div class="col-4">
                                        <button class="btn btn-primary w-100" onclick="atribuirPerfil()">Adicionar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-7">
                        <div class="card-custom">
                            <div class="card-header-custom">
                                <strong>Permissões Diretas</strong>
                            </div>
                            <div class="card-body">
                                <div class="row g-2 mb-3">
                                    <div class="col-md-5">
                                        <select class="form-select" id="moduloSelect"></select>
                                    </div>
                                    <div class="col-md-4">
                                        <select class="form-select" id="acaoSelect"></select>
                                    </div>
                                    <div class="col-md-3">
                                        <button class="btn btn-primary w-100" onclick="adicionarPermissaoUsuario()">Adicionar</button>
                                    </div>
                                    <div class="col-md-12 mt-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="permitidoCheck" checked>
                                            <label class="form-check-label" for="permitidoCheck">Permitido</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-custom">
                                            <tr>
                                                <th>Módulo</th>
                                                <th>Ação</th>
                                                <th>Status</th>
                                                <th>Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody id="permissoesUsuarioTabela">
                                            <tr><td colspan="4" class="text-center text-muted">Selecione um usuário.</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="perfis" role="tabpanel">
                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="card-custom">
                            <div class="card-header-custom d-flex justify-content-between align-items-center">
                                <strong>Perfis</strong>
                                <button class="btn btn-primary btn-action" onclick="abrirModalPerfil()">
                                    <i class="bi bi-plus-circle"></i> Novo Perfil
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-custom">
                                            <tr>
                                                <th>Nome</th>
                                                <th>Nível</th>
                                                <th>Padrão</th>
                                                <th>Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody id="perfisTabela">
                                            <tr><td colspan="4" class="text-center text-muted">Carregando...</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="card-custom">
                            <div class="card-header-custom">
                                <strong>Permissões do Perfil</strong>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Perfil selecionado</label>
                                    <select class="form-select" id="perfilPermissaoSelect"></select>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-custom">
                                            <tr>
                                                <th>Módulo</th>
                                                <th>Ação</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody id="permissoesPerfilTabela">
                                            <tr><td colspan="3" class="text-center text-muted">Selecione um perfil.</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="row g-2 mt-3">
                                    <div class="col-md-5">
                                        <select class="form-select" id="moduloPerfilSelect"></select>
                                    </div>
                                    <div class="col-md-4">
                                        <select class="form-select" id="acaoPerfilSelect"></select>
                                    </div>
                                    <div class="col-md-3">
                                        <button class="btn btn-outline-primary w-100" onclick="adicionarPermissaoPerfil()">Adicionar</button>
                                    </div>
                                    <div class="col-12 mt-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="permitidoPerfilCheck" checked>
                                            <label class="form-check-label" for="permitidoPerfilCheck">Permitido</label>
                                        </div>
                                        <small class="text-muted">Se o endpoint de gravação não estiver disponível, será exibido erro.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <div class="modal fade" id="modalPerfil" tabindex="-1" aria-labelledby="modalPerfilLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalPerfilLabel">Novo Perfil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="perfilId">
                    <div class="mb-3">
                        <label class="form-label">Nome do Perfil</label>
                        <input type="text" class="form-control" id="perfilNome" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descrição</label>
                        <input type="text" class="form-control" id="perfilDescricao">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nível</label>
                        <input type="number" class="form-control" id="perfilNivel" value="100">
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="perfilPadrao">
                        <label class="form-check-label" for="perfilPadrao">Perfil padrão</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="salvarPerfil()">Salvar</button>
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
        const API_CONFIG = {
            BASE_URL: '<?= addslashes($config['api_base']) ?>',
            PERFIS: '/api/perfis',
            USUARIOS_EMPRESA: '/api/usuarios/empresa',
            USUARIO_PERFIS: '/api/usuarios',
            USUARIO_PERMISSOES: '/api/usuarios',
            PERFIL_PERMISSOES: '/api/perfis',
            MODULOS: '/api/modulos',
            ACOES: '/api/permissoes-acoes',
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
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-ID-EMPRESA': idEmpresa.toString()
                };
            }
        };

        const token = <?php echo json_encode($token); ?>;
        const idEmpresa = <?php echo json_encode((int)$id_empresa); ?>;

        let usuarios = [];
        let perfis = [];
        let modulos = [];
        let acoes = [];
        let perfisUsuario = [];
        let permissoesUsuario = [];
        let permissoesPerfil = [];
        let usuarioSelecionado = null;
        let perfilSelecionado = null;
        let modalPerfil = null;

        document.addEventListener('DOMContentLoaded', function() {
            modalPerfil = new bootstrap.Modal(document.getElementById('modalPerfil'));
            carregarDadosIniciais();
            document.getElementById('usuarioSelect').addEventListener('change', onUsuarioChange);
            document.getElementById('perfilPermissaoSelect').addEventListener('change', onPerfilChange);

            const logoutBtn = document.getElementById('logoutBtn');
            if (logoutBtn) {
                logoutBtn.addEventListener('click', async (e) => {
                    e.preventDefault();
                    await fetch(API_CONFIG.BASE_URL + API_CONFIG.LOGOUT, {
                        method: 'POST',
                        headers: API_CONFIG.getHeaders()
                    });
                    window.location.href = 'login.php';
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
            setTimeout(() => notification.remove(), 5000);
        }

        function normalizarLista(data) {
            if (Array.isArray(data)) return data;
            if (data && Array.isArray(data.data)) return data.data;
            if (data && data.data && Array.isArray(data.data.data)) return data.data.data;
            if (data && Array.isArray(data.items)) return data.items;
            const maybeArray = data && typeof data === 'object' ? Object.values(data).find(v => Array.isArray(v)) : null;
            return maybeArray || [];
        }

        async function fetchJson(url, options = {}) {
            const response = await fetch(url, options);
            const text = await response.text();
            let data = null;
            if (text) {
                try {
                    data = JSON.parse(text);
                } catch (err) {
                    data = null;
                }
            }
            if (!response.ok) {
                let errorMessage = `Erro ${response.status}: ${response.statusText}`;
                if (data && data.message) errorMessage = data.message;
                throw new Error(errorMessage);
            }
            return data ?? {};
        }

        async function carregarDadosIniciais() {
            mostrarLoading(true);
            try {
                const [usuariosData, perfisData, modulosData, acoesData] = await Promise.all([
                    fetchJson(`${API_CONFIG.BASE_URL}${API_CONFIG.USUARIOS_EMPRESA}/${idEmpresa}`, { headers: API_CONFIG.getHeaders() }),
                    fetchJson(`${API_CONFIG.BASE_URL}${API_CONFIG.PERFIS}`, { headers: API_CONFIG.getHeaders() }),
                    fetchJson(`${API_CONFIG.BASE_URL}${API_CONFIG.MODULOS}`, { headers: API_CONFIG.getHeaders() }),
                    fetchJson(`${API_CONFIG.BASE_URL}${API_CONFIG.ACOES}`, { headers: API_CONFIG.getHeaders() })
                ]);

                usuarios = normalizarLista(usuariosData);
                perfis = normalizarLista(perfisData);
                modulos = normalizarLista(modulosData);
                acoes = normalizarLista(acoesData);

                preencherSelects();
                renderPerfis();
                if (usuarios.length > 0) {
                    document.getElementById('usuarioSelect').value = usuarios[0].id_usuario || '';
                    onUsuarioChange();
                }
            } catch (error) {
                console.error(error);
                mostrarNotificacao('Erro ao carregar dados: ' + error.message, 'error');
            } finally {
                mostrarLoading(false);
            }
        }

        function preencherSelects() {
            const usuarioSelect = document.getElementById('usuarioSelect');
            usuarioSelect.innerHTML = '<option value="">Selecione...</option>';
            usuarios.forEach(u => {
                usuarioSelect.innerHTML += `<option value="${u.id_usuario}">${u.nome || u.name || u.email || u.id_usuario}</option>`;
            });

            const perfilSelect = document.getElementById('perfilAdicionarSelect');
            const perfilPermissaoSelect = document.getElementById('perfilPermissaoSelect');
            perfilSelect.innerHTML = '<option value="">Selecione perfil</option>';
            perfilPermissaoSelect.innerHTML = '<option value="">Selecione perfil</option>';
            perfis.forEach(p => {
                const label = `${p.nome_perfil || p.nome || p.id_perfil}`;
                perfilSelect.innerHTML += `<option value="${p.id_perfil}">${label}</option>`;
                perfilPermissaoSelect.innerHTML += `<option value="${p.id_perfil}">${label}</option>`;
            });

            const moduloSelect = document.getElementById('moduloSelect');
            const moduloPerfilSelect = document.getElementById('moduloPerfilSelect');
            moduloSelect.innerHTML = '<option value="">Selecione módulo</option>';
            moduloPerfilSelect.innerHTML = '<option value="">Selecione módulo</option>';
            modulos.forEach(m => {
                const label = `${m.nome_modulo || m.nome || m.id_modulo}`;
                moduloSelect.innerHTML += `<option value="${m.id_modulo}">${label}</option>`;
                moduloPerfilSelect.innerHTML += `<option value="${m.id_modulo}">${label}</option>`;
            });

            const acaoSelect = document.getElementById('acaoSelect');
            const acaoPerfilSelect = document.getElementById('acaoPerfilSelect');
            acaoSelect.innerHTML = '<option value="">Selecione ação</option>';
            acaoPerfilSelect.innerHTML = '<option value="">Selecione ação</option>';
            acoes.forEach(a => {
                const label = `${a.nome_acao || a.nome || a.codigo_acao || a.id_acao}`;
                acaoSelect.innerHTML += `<option value="${a.id_acao}">${label}</option>`;
                acaoPerfilSelect.innerHTML += `<option value="${a.id_acao}">${label}</option>`;
            });
        }

        function getNomeModulo(id) {
            const modulo = modulos.find(m => String(m.id_modulo) === String(id));
            return modulo ? (modulo.nome_modulo || modulo.nome || modulo.id_modulo) : id;
        }

        function getNomeAcao(id) {
            const acao = acoes.find(a => String(a.id_acao) === String(id));
            return acao ? (acao.nome_acao || acao.nome || acao.codigo_acao || acao.id_acao) : id;
        }

        async function onUsuarioChange() {
            const id = document.getElementById('usuarioSelect').value;
            usuarioSelecionado = id ? parseInt(id) : null;
            document.getElementById('usuarioResumo').textContent = usuarioSelecionado ? `ID ${usuarioSelecionado}` : '';
            if (!usuarioSelecionado) return;
            await Promise.all([
                carregarPerfisUsuario(),
                carregarPermissoesUsuario()
            ]);
        }
        async function carregarPerfisUsuario() {
            try {
                const data = await fetchJson(`${API_CONFIG.BASE_URL}${API_CONFIG.USUARIO_PERFIS}/${usuarioSelecionado}/perfis`, {
                    headers: API_CONFIG.getHeaders()
                });
                perfisUsuario = normalizarLista(data);
                renderPerfisUsuario();
            } catch (error) {
                mostrarNotificacao('Erro ao carregar perfis do usuário: ' + error.message, 'error');
            }
        }

        function renderPerfisUsuario() {
            const container = document.getElementById('perfisUsuarioLista');
            if (!usuarioSelecionado) {
                container.innerHTML = '<span class="text-muted">Selecione um usuário.</span>';
                return;
            }
            if (!perfisUsuario.length) {
                container.innerHTML = '<span class="text-muted">Nenhum perfil atribuído.</span>';
                return;
            }
            container.innerHTML = perfisUsuario.map(p => `
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>${p.nome_perfil || p.nome || p.id_perfil}</span>
                    <button class="btn btn-outline-danger btn-sm" onclick="revogarPerfil(${p.id_perfil})">Remover</button>
                </div>
            `).join('');
        }

        async function atribuirPerfil() {
            const perfilId = document.getElementById('perfilAdicionarSelect').value;
            if (!usuarioSelecionado || !perfilId) return;
            try {
                await fetchJson(`${API_CONFIG.BASE_URL}${API_CONFIG.USUARIO_PERFIS}/${usuarioSelecionado}/perfis/${perfilId}`, {
                    method: 'POST',
                    headers: API_CONFIG.getHeaders()
                });
                mostrarNotificacao('Perfil atribuído.', 'success');
                carregarPerfisUsuario();
            } catch (error) {
                mostrarNotificacao('Erro ao atribuir perfil: ' + error.message, 'error');
            }
        }

        async function revogarPerfil(perfilId) {
            if (!usuarioSelecionado || !perfilId) return;
            try {
                await fetchJson(`${API_CONFIG.BASE_URL}${API_CONFIG.USUARIO_PERFIS}/${usuarioSelecionado}/perfis/${perfilId}`, {
                    method: 'DELETE',
                    headers: API_CONFIG.getJsonHeaders(),
                    body: JSON.stringify({ motivo_revogacao: 'Removido pelo administrador' })
                });
                mostrarNotificacao('Perfil removido.', 'success');
                carregarPerfisUsuario();
            } catch (error) {
                mostrarNotificacao('Erro ao remover perfil: ' + error.message, 'error');
            }
        }

        async function carregarPermissoesUsuario() {
            try {
                const data = await fetchJson(`${API_CONFIG.BASE_URL}${API_CONFIG.USUARIO_PERMISSOES}/${usuarioSelecionado}/permissoes`, {
                    headers: API_CONFIG.getHeaders()
                });
                permissoesUsuario = normalizarLista(data);
                renderPermissoesUsuario();
            } catch (error) {
                mostrarNotificacao('Erro ao carregar permissões do usuário: ' + error.message, 'error');
            }
        }

        function renderPermissoesUsuario() {
            const tbody = document.getElementById('permissoesUsuarioTabela');
            if (!usuarioSelecionado) {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">Selecione um usuário.</td></tr>';
                return;
            }
            if (!permissoesUsuario.length) {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">Nenhuma permissão direta.</td></tr>';
                return;
            }
            tbody.innerHTML = permissoesUsuario.map(p => `
                <tr>
                    <td>${getNomeModulo(p.id_modulo)}</td>
                    <td>${getNomeAcao(p.id_acao)}</td>
                    <td>${p.permitido ? '<span class="badge-permitido">Permitido</span>' : '<span class="badge-negado">Negado</span>'}</td>
                    <td>
                        <button class="btn btn-outline-secondary btn-sm" onclick="togglePermissaoUsuario(${p.id_permissao_usuario || p.id || p.id_permissao})">Alternar</button>
                        <button class="btn btn-outline-danger btn-sm" onclick="excluirPermissaoUsuario(${p.id_permissao_usuario || p.id || p.id_permissao})">Excluir</button>
                    </td>
                </tr>
            `).join('');
        }

        async function adicionarPermissaoUsuario() {
            const moduloId = document.getElementById('moduloSelect').value;
            const acaoId = document.getElementById('acaoSelect').value;
            const permitido = document.getElementById('permitidoCheck').checked;
            if (!usuarioSelecionado || !moduloId || !acaoId) {
                mostrarNotificacao('Selecione módulo e ação.', 'error');
                return;
            }
            try {
                await fetchJson(`${API_CONFIG.BASE_URL}${API_CONFIG.USUARIO_PERMISSOES}/${usuarioSelecionado}/permissoes`, {
                    method: 'POST',
                    headers: API_CONFIG.getJsonHeaders(),
                    body: JSON.stringify({ id_modulo: parseInt(moduloId), id_acao: parseInt(acaoId), permitido })
                });
                mostrarNotificacao('Permissão adicionada.', 'success');
                carregarPermissoesUsuario();
            } catch (error) {
                mostrarNotificacao('Erro ao adicionar permissão: ' + error.message, 'error');
            }
        }

        async function togglePermissaoUsuario(idPermissao) {
            const permissao = permissoesUsuario.find(p => String(p.id_permissao_usuario || p.id || p.id_permissao) === String(idPermissao));
            if (!permissao) return;
            const novoPermitido = !permissao.permitido;
            try {
                await fetchJson(`${API_CONFIG.BASE_URL}${API_CONFIG.USUARIO_PERMISSOES}/${usuarioSelecionado}/permissoes/${idPermissao}`, {
                    method: 'PUT',
                    headers: API_CONFIG.getJsonHeaders(),
                    body: JSON.stringify({ permitido: novoPermitido })
                });
                mostrarNotificacao('Permissão atualizada.', 'success');
                carregarPermissoesUsuario();
            } catch (error) {
                mostrarNotificacao('Erro ao atualizar permissão: ' + error.message, 'error');
            }
        }

        async function excluirPermissaoUsuario(idPermissao) {
            try {
                await fetchJson(`${API_CONFIG.BASE_URL}${API_CONFIG.USUARIO_PERMISSOES}/${usuarioSelecionado}/permissoes/${idPermissao}`, {
                    method: 'DELETE',
                    headers: API_CONFIG.getHeaders()
                });
                mostrarNotificacao('Permissão removida.', 'success');
                carregarPermissoesUsuario();
            } catch (error) {
                mostrarNotificacao('Erro ao remover permissão: ' + error.message, 'error');
            }
        }

        function renderPerfis() {
            const tbody = document.getElementById('perfisTabela');
            if (!perfis.length) {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">Nenhum perfil encontrado.</td></tr>';
                return;
            }
            tbody.innerHTML = perfis.map(p => `
                <tr>
                    <td>${p.nome_perfil || p.nome || p.id_perfil}</td>
                    <td>${p.nivel || '-'}</td>
                    <td>${p.is_default ? 'Sim' : 'Não'}</td>
                    <td>
                        <button class="btn btn-outline-primary btn-sm" onclick="editarPerfil(${p.id_perfil})">Editar</button>
                        <button class="btn btn-outline-danger btn-sm" onclick="excluirPerfil(${p.id_perfil})">Excluir</button>
                    </td>
                </tr>
            `).join('');
        }

        function abrirModalPerfil() {
            document.getElementById('modalPerfilLabel').textContent = 'Novo Perfil';
            document.getElementById('perfilId').value = '';
            document.getElementById('perfilNome').value = '';
            document.getElementById('perfilDescricao').value = '';
            document.getElementById('perfilNivel').value = '100';
            document.getElementById('perfilPadrao').checked = false;
            modalPerfil.show();
        }

        function editarPerfil(idPerfil) {
            const perfil = perfis.find(p => String(p.id_perfil) === String(idPerfil));
            if (!perfil) return;
            document.getElementById('modalPerfilLabel').textContent = 'Editar Perfil';
            document.getElementById('perfilId').value = perfil.id_perfil;
            document.getElementById('perfilNome').value = perfil.nome_perfil || '';
            document.getElementById('perfilDescricao').value = perfil.descricao || '';
            document.getElementById('perfilNivel').value = perfil.nivel || 100;
            document.getElementById('perfilPadrao').checked = !!perfil.is_default;
            modalPerfil.show();
        }

        async function salvarPerfil() {
            const idPerfil = document.getElementById('perfilId').value;
            const payload = {
                nome_perfil: document.getElementById('perfilNome').value,
                descricao: document.getElementById('perfilDescricao').value,
                nivel: parseInt(document.getElementById('perfilNivel').value || '100'),
                is_default: document.getElementById('perfilPadrao').checked
            };
            if (!payload.nome_perfil) {
                mostrarNotificacao('Informe o nome do perfil.', 'error');
                return;
            }
            try {
                const url = idPerfil
                    ? `${API_CONFIG.BASE_URL}${API_CONFIG.PERFIS}/${idPerfil}`
                    : `${API_CONFIG.BASE_URL}${API_CONFIG.PERFIS}`;
                const method = idPerfil ? 'PUT' : 'POST';
                await fetchJson(url, {
                    method,
                    headers: API_CONFIG.getJsonHeaders(),
                    body: JSON.stringify(payload)
                });
                modalPerfil.hide();
                mostrarNotificacao('Perfil salvo.', 'success');
                const perfisData = await fetchJson(`${API_CONFIG.BASE_URL}${API_CONFIG.PERFIS}`, { headers: API_CONFIG.getHeaders() });
                perfis = normalizarLista(perfisData);
                preencherSelects();
                renderPerfis();
            } catch (error) {
                mostrarNotificacao('Erro ao salvar perfil: ' + error.message, 'error');
            }
        }

        async function excluirPerfil(idPerfil) {
            if (!confirm('Tem certeza que deseja excluir este perfil?')) return;
            try {
                await fetchJson(`${API_CONFIG.BASE_URL}${API_CONFIG.PERFIS}/${idPerfil}`, {
                    method: 'DELETE',
                    headers: API_CONFIG.getHeaders()
                });
                mostrarNotificacao('Perfil excluído.', 'success');
                perfis = perfis.filter(p => String(p.id_perfil) !== String(idPerfil));
                preencherSelects();
                renderPerfis();
            } catch (error) {
                mostrarNotificacao('Erro ao excluir perfil: ' + error.message, 'error');
            }
        }

        async function onPerfilChange() {
            const id = document.getElementById('perfilPermissaoSelect').value;
            perfilSelecionado = id ? parseInt(id) : null;
            if (!perfilSelecionado) {
                document.getElementById('permissoesPerfilTabela').innerHTML = '<tr><td colspan="3" class="text-center text-muted">Selecione um perfil.</td></tr>';
                return;
            }
            await carregarPermissoesPerfil();
        }

        async function carregarPermissoesPerfil() {
            try {
                const data = await fetchJson(`${API_CONFIG.BASE_URL}${API_CONFIG.PERFIL_PERMISSOES}/${perfilSelecionado}/permissoes`, {
                    headers: API_CONFIG.getHeaders()
                });
                permissoesPerfil = normalizarLista(data);
                renderPermissoesPerfil();
            } catch (error) {
                mostrarNotificacao('Erro ao carregar permissões do perfil: ' + error.message, 'error');
            }
        }

        function renderPermissoesPerfil() {
            const tbody = document.getElementById('permissoesPerfilTabela');
            if (!permissoesPerfil.length) {
                tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted">Nenhuma permissão cadastrada.</td></tr>';
                return;
            }
            tbody.innerHTML = permissoesPerfil.map(p => `
                <tr>
                    <td>${getNomeModulo(p.id_modulo)}</td>
                    <td>${getNomeAcao(p.id_acao)}</td>
                    <td>${p.permitido ? '<span class="badge-permitido">Permitido</span>' : '<span class="badge-negado">Negado</span>'}</td>
                </tr>
            `).join('');
        }

        async function adicionarPermissaoPerfil() {
            const moduloId = document.getElementById('moduloPerfilSelect').value;
            const acaoId = document.getElementById('acaoPerfilSelect').value;
            const permitido = document.getElementById('permitidoPerfilCheck').checked;
            if (!perfilSelecionado || !moduloId || !acaoId) {
                mostrarNotificacao('Selecione perfil, módulo e ação.', 'error');
                return;
            }
            try {
                await fetchJson(`${API_CONFIG.BASE_URL}${API_CONFIG.PERFIL_PERMISSOES}/${perfilSelecionado}/permissoes`, {
                    method: 'POST',
                    headers: API_CONFIG.getJsonHeaders(),
                    body: JSON.stringify({ id_modulo: parseInt(moduloId), id_acao: parseInt(acaoId), permitido })
                });
                mostrarNotificacao('Permissão adicionada ao perfil.', 'success');
                carregarPermissoesPerfil();
            } catch (error) {
                mostrarNotificacao('Erro ao adicionar permissão ao perfil: ' + error.message, 'error');
            }
        }
    </script>
    <?php if (!defined('APP_SHELL')) { include __DIR__ . '/../components/app-foot.php'; } ?>
</body>
</html>
