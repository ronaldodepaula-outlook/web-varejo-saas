<?php
$config = require __DIR__ . '/../funcoes/config.php';

session_start();
if (!isset($_SESSION['authToken'], $_SESSION['usuario'], $_SESSION['empresa'], $_SESSION['licenca'])) {
    header('Location: login.php');
    exit;
}

// Verificar e converter o tipo de dados das variáveis de sessão
$usuario = is_array($_SESSION['usuario']) ? $_SESSION['usuario'] : ['nome' => $_SESSION['usuario']];
$id_usuario = $_SESSION['user_id']
    ?? ($_SESSION['usuario_id'] ?? ($_SESSION['id_usuario'] ?? null));
if ($id_usuario === null && is_array($usuario)) {
    $id_usuario = $usuario['id_usuario'] ?? ($usuario['id'] ?? null);
}
$empresa = $_SESSION['empresa'];
$id_empresa = $_SESSION['id_empresa'] ?? ($_SESSION['empresa_id'] ?? ($empresa['id_empresa'] ?? null));
$licenca = $_SESSION['licenca'];
$token = $_SESSION['authToken'];
$segmento = $_SESSION['segmento'] ?? '';
$isVarejo = strtolower((string)$segmento) === 'varejo';
$perfilUsuario = $_SESSION['userRole'] ?? ($usuario['perfil'] ?? ($usuario['role'] ?? ''));
$isAdminEmpresa = $perfilUsuario === 'admin_empresa';

// Extrair nome do usuário de forma segura
$nomeUsuario = '';
if (is_array($usuario)) {
    $nomeUsuario = $usuario['nome'] ?? $usuario['name'] ?? 'Usuário';
} else {
    $nomeUsuario = (string)$usuario;
}

// Primeira letra para o avatar
$inicialUsuario = strtoupper(substr($nomeUsuario, 0, 1));

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?php if (!defined('APP_SHELL')) { $pageTitle = 'Gestão de Empresas - SAS Multi'; include __DIR__ . '/../components/app-head.php'; } ?>

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
        
        .status-ativa { background: rgba(39, 174, 96, 0.1); color: var(--success-color); }
        .status-pendente { background: rgba(243, 156, 18, 0.1); color: var(--warning-color); }
        .status-inativa { background: rgba(231, 76, 60, 0.1); color: var(--danger-color); }
        .status-ativo { background: rgba(39, 174, 96, 0.1); color: var(--success-color); }
        .status-inativo { background: rgba(231, 76, 60, 0.1); color: var(--danger-color); }
        
        .segmento-badge {
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            background: rgba(52, 152, 219, 0.1);
            color: var(--primary-color);
        }

        .perfil-badge {
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            background: rgba(52, 152, 219, 0.1);
            color: var(--primary-color);
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
                        <li class="breadcrumb-item active">Gestão de Empresas</li>
                    </ol>
                </nav>
            </div>
            
            <div class="header-right">
                <!-- Notificações -->
                <div class="dropdown">
                    <button class="btn btn-link text-dark position-relative" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-bell" style="font-size: 1.25rem;"></i>
                        <span class="badge bg-danger position-absolute translate-middle rounded-pill" style="font-size: 0.6rem; top: 8px; right: 8px;">3</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><h6 class="dropdown-header">Alertas do Sistema</h6></li>
                        <li><a class="dropdown-item" href="#">2 empresas com status pendente</a></li>
                        <li><a class="dropdown-item" href="#">1 nova empresa cadastrada</a></li>
                        <li><a class="dropdown-item" href="#">Backup executado com sucesso</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-center" href="#">Ver todos</a></li>
                    </ul>
                </div>
                
                <!-- Dropdown do Usuário -->
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
        
        <!-- Área de Conteúdo -->
        <div class="content-area">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="page-title">Gestão de Empresas</h1>
                    <p class="page-subtitle">Cadastre e gerencie as empresas do sistema</p>
                </div>
                <div class="d-flex gap-2">
                    <?php if ($isAdminEmpresa): ?>
                        <a class="btn btn-outline-primary" href="?view=admin-gestao-permissoes&id_empresa=<?php echo urlencode((string)$id_empresa); ?>">
                            <i class="bi bi-shield-lock me-2"></i>Administrar Usuários
                        </a>
                    <?php endif; ?>
                    <button class="btn btn-primary" onclick="abrirModalEmpresa()" <?php echo $isVarejo ? 'disabled aria-disabled="true" title="Somente administradores podem cadastrar empresas"' : ''; ?>>
                        <i class="bi bi-plus-circle me-2"></i>Nova Empresa
                    </button>
                </div>
            </div>
            
            <!-- Filtros e Busca -->
            <div class="card-custom mb-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="search-box">
                                <i class="bi bi-search"></i>
                                <input type="text" class="form-control" id="searchInput" placeholder="Buscar por nome da empresa ou CNPJ...">
                            </div>
                        </div>
                        <?php if (!$isVarejo): ?>
                        <div class="col-md-3">
                            <select class="form-select" id="filterSegmento">
                                <option value="">Todos os segmentos</option>
                                <option value="varejo">Varejo</option>
                                <option value="industria">Indústria</option>
                                <option value="construcao">Construção</option>
                                <option value="financeiro">Financeiro</option>
                                <option value="seguranca">Segurança</option>
                                <option value="outros">Outros</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="filterStatus">
                                <option value="">Todos os status</option>
                                <option value="ativa">Ativa</option>
                                <option value="pendente">Pendente</option>
                                <option value="inativa">Inativa</option>
                            </select>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Tabela de Empresas -->
            <div class="card-custom">
                <div class="card-header-custom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Lista de Empresas</h5>
                    <div class="d-flex align-items-center">
                        <span class="text-muted me-3" id="totalEmpresas">Carregando...</span>
                        <button class="btn btn-sm btn-outline-primary" onclick="carregarEmpresas()">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="tabelaEmpresas">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nome da Empresa</th>
                                    <th>CNPJ</th>
                                    <th>Segmento</th>
                                    <th>Status</th>
                                    <th>Data Cadastro</th>
                                    <th width="120">Ações</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyEmpresas">
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Carregando empresas...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Gestão de Usuários da Empresa -->
            <div class="card-custom mt-4" id="usuariosEmpresaSection">
                <div class="card-header-custom d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Gestão de Usuários</h5>
                        <small class="text-muted">Cadastre e gerencie os usuários da empresa selecionada</small>
                    </div>
                    <div class="d-flex gap-2 align-items-center">
                        <div id="usuariosEmpresaSelectWrapper">
                            <select class="form-select form-select-sm" id="usuariosEmpresaSelect" style="min-width: 220px;"></select>
                        </div>
                        <button class="btn btn-sm btn-primary" onclick="usuariosAbrirModalUsuario()">
                            <i class="bi bi-person-plus me-1"></i>Novo Usuário
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="search-box">
                                <i class="bi bi-search"></i>
                                <input type="text" class="form-control" id="usuariosSearchInput" placeholder="Buscar por nome ou email...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="usuariosFilterPerfil">
                                <option value="">Todos os perfis</option>
                                <option value="super_admin">Super Admin</option>
                                <option value="admin_empresa">Admin Empresa</option>
                                <option value="usuario">Usuário</option>
                                <option value="manager">Gestor</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="usuariosFilterStatus">
                                <option value="">Todos os status</option>
                                <option value="ativo">Ativo</option>
                                <option value="inativo">Inativo</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted" id="usuariosTotal">Carregando...</span>
                        <button class="btn btn-sm btn-outline-primary" onclick="usuariosCarregarUsuarios()">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover" id="usuariosTabela">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nome</th>
                                    <th>Email</th>
                                    <th>Perfil</th>
                                    <th>Status</th>
                                    <th>Termos</th>
                                    <th>Newsletter</th>
                                    <th>Data Cadastro</th>
                                    <th width="120">Ações</th>
                                </tr>
                            </thead>
                            <tbody id="usuariosTbody">
                                <tr>
                                    <td colspan="9" class="text-center text-muted">Selecione uma empresa.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal para Adicionar/Editar Empresa -->
    <div class="modal fade" id="modalEmpresa" tabindex="-1" aria-labelledby="modalEmpresaLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEmpresaLabel">Nova Empresa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formEmpresa">
                        <input type="hidden" id="empresaId">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label for="nome_empresa" class="form-label">Nome da Empresa *</label>
                                <input type="text" class="form-control" id="nome_empresa" required>
                                <div class="invalid-feedback">Por favor, informe o nome da empresa.</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="cnpj" class="form-label">CNPJ *</label>
                                <input type="text" class="form-control" id="cnpj" required <?php echo $isVarejo ? 'readonly' : ''; ?>>
                                <div class="invalid-feedback">Por favor, informe um CNPJ válido.</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="segmento" class="form-label">Segmento *</label>
                                <select class="form-select" id="segmento" required>
                                    <option value="">Selecione um segmento</option>
                                    <option value="varejo">Varejo</option>
                                    <option value="ecommerce">E-commerce</option>
                                    <option value="alimentacao">Alimentação</option>
                                    <option value="turismo_hotelaria">Turismo e Hotelaria</option>
                                    <option value="imobiliario">Imobiliário</option>
                                    <option value="esportes_lazer">Esportes e Lazer</option>
                                    <option value="midia_entretenimento">Mídia e Entretenimento</option>
                                    <option value="industria">Indústria</option>
                                    <option value="construcao">Construção</option>
                                    <option value="agropecuaria">Agropecuária</option>
                                    <option value="energia_utilities">Energia e Utilities</option>
                                    <option value="logistica_transporte">Logística e Transporte</option>
                                    <option value="financeiro">Financeiro</option>
                                    <option value="contabilidade_auditoria">Contabilidade e Auditoria</option>
                                    <option value="seguros">Seguros</option>
                                    <option value="marketing">Marketing</option>
                                    <option value="saude">Saúde</option>
                                    <option value="educacao">Educação</option>
                                    <option value="ciencia_pesquisa">Ciência e Pesquisa</option>
                                    <option value="rh_recrutamento">RH e Recrutamento</option>
                                    <option value="juridico">Jurídico</option>
                                    <option value="ongs_terceiro_setor">ONGs e Terceiro Setor</option>
                                    <option value="seguranca">Segurança</option>
                                    <option value="outros">Outros</option>
                                </select>
                                <div class="invalid-feedback">Por favor, selecione um segmento.</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="email_empresa" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email_empresa">
                            </div>
                            
                            <div class="col-md-6">
                                <label for="telefone" class="form-label">Telefone</label>
                                <input type="text" class="form-control" id="telefone">
                            </div>
                            
                            <div class="col-md-12">
                                <label for="website" class="form-label">Website</label>
                                <input type="url" class="form-control" id="website">
                            </div>
                            
                            <div class="col-md-8">
                                <label for="endereco" class="form-label">Endereço</label>
                                <input type="text" class="form-control" id="endereco">
                            </div>
                            
                            <div class="col-md-4">
                                <label for="cep" class="form-label">CEP</label>
                                <input type="text" class="form-control" id="cep">
                            </div>
                            
                            <div class="col-md-6">
                                <label for="cidade" class="form-label">Cidade</label>
                                <input type="text" class="form-control" id="cidade">
                            </div>
                            
                            <div class="col-md-6">
                                <label for="estado" class="form-label">Estado</label>
                                <select class="form-select" id="estado">
                                    <option value="">Selecione</option>
                                    <option value="AC">Acre</option>
                                    <option value="AL">Alagoas</option>
                                    <option value="AP">Amapá</option>
                                    <option value="AM">Amazonas</option>
                                    <option value="BA">Bahia</option>
                                    <option value="CE">Ceará</option>
                                    <option value="DF">Distrito Federal</option>
                                    <option value="ES">Espírito Santo</option>
                                    <option value="GO">Goiás</option>
                                    <option value="MA">Maranhão</option>
                                    <option value="MT">Mato Grosso</option>
                                    <option value="MS">Mato Grosso do Sul</option>
                                    <option value="MG">Minas Gerais</option>
                                    <option value="PA">Pará</option>
                                    <option value="PB">Paraíba</option>
                                    <option value="PR">Paraná</option>
                                    <option value="PE">Pernambuco</option>
                                    <option value="PI">Piauí</option>
                                    <option value="RJ">Rio de Janeiro</option>
                                    <option value="RN">Rio Grande do Norte</option>
                                    <option value="RS">Rio Grande do Sul</option>
                                    <option value="RO">Rondônia</option>
                                    <option value="RR">Roraima</option>
                                    <option value="SC">Santa Catarina</option>
                                    <option value="SP">São Paulo</option>
                                    <option value="SE">Sergipe</option>
                                    <option value="TO">Tocantins</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status *</label>
                                <select class="form-select" id="status" required>
                                    <option value="ativa">Ativa</option>
                                    <option value="pendente">Pendente</option>
                                    <option value="inativa">Inativa</option>
                                </select>
                                <div class="invalid-feedback">Por favor, selecione um status.</div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="salvarEmpresa()">Salvar Empresa</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmação de Exclusão -->
    <div class="modal fade" id="modalConfirmacao" tabindex="-1" aria-labelledby="modalConfirmacaoLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalConfirmacaoLabel">Confirmar Exclusão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza que deseja excluir a empresa <strong id="nomeEmpresaExcluir"></strong>?</p>
                    <p class="text-danger"><small>Esta ação não pode ser desfeita.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="btnConfirmarExclusao">Excluir Empresa</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Adicionar/Editar Usuário -->
    <div class="modal fade" id="modalUsuarioEmpresa" tabindex="-1" aria-labelledby="modalUsuarioEmpresaLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalUsuarioEmpresaLabel">Novo Usuário</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="usuariosForm">
                        <input type="hidden" id="usuariosUsuarioId">
                        <input type="hidden" id="usuariosEmpresaId">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nome Completo *</label>
                                <input type="text" class="form-control" id="usuariosNome" required>
                                <div class="invalid-feedback">Informe o nome completo.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email *</label>
                                <input type="email" class="form-control" id="usuariosEmail" required>
                                <div class="invalid-feedback">Informe um email válido.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" id="usuariosLabelSenha">Senha *</label>
                                <input type="password" class="form-control" id="usuariosSenha" minlength="6">
                                <div class="invalid-feedback">A senha deve ter pelo menos 6 caracteres.</div>
                                <small class="form-text text-muted" id="usuariosTextoAjudaSenha">Mínimo 6 caracteres</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Perfil *</label>
                                <select class="form-select" id="usuariosPerfil" required>
                                    <option value="">Selecione o perfil</option>
                                    <option value="super_admin">Super Admin</option>
                                    <option value="admin_empresa">Admin Empresa</option>
                                    <option value="usuario">Usuário</option>
                                    <option value="manager">Gestor</option>
                                </select>
                                <div class="invalid-feedback">Selecione o perfil.</div>
                            </div>
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-check form-switch mt-4">
                                            <input class="form-check-input" type="checkbox" id="usuariosAtivo" checked>
                                            <label class="form-check-label" for="usuariosAtivo">Usuário Ativo</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-switch mt-4">
                                            <input class="form-check-input" type="checkbox" id="usuariosAceitouTermos">
                                            <label class="form-check-label" for="usuariosAceitouTermos">Aceitou Termos</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-switch mt-4">
                                            <input class="form-check-input" type="checkbox" id="usuariosNewsletter">
                                            <label class="form-check-label" for="usuariosNewsletter">Newsletter</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="usuariosSalvarUsuario()">Salvar Usuário</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmação de Exclusão de Usuário -->
    <div class="modal fade" id="modalUsuariosConfirmacao" tabindex="-1" aria-labelledby="modalUsuariosConfirmacaoLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalUsuariosConfirmacaoLabel">Confirmar Exclusão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza que deseja excluir o usuário <strong id="usuariosNomeExcluir"></strong>?</p>
                    <p class="text-danger"><small>Esta ação não pode ser desfeita.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="usuariosBtnConfirmarExclusao">Excluir Usuário</button>
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

    <!-- Configuração da API -->
    <script>
        // config.js - Arquivo de configuração para URLs da API
        const API_CONFIG = {
            BASE_URL: '<?= addslashes($config['api_base']) ?>',
            API_VERSION: 'v1',
            
            // Endpoints
            EMPRESAS: '/api/v1/empresas',
            EMPRESAS_POR_USUARIO: '/api/empresas/por-usuario',
            LOGIN: '/api/v1/login',
            LOGOUT: '/api/v1/logout',
            
            // Headers padrão
            getHeaders: function(token) {
                return {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                };
            },

            getJsonHeaders: function(token) {
                return {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                };
            },
            
            // URL completa para empresas
            getEmpresasUrl: function() {
                return `${this.BASE_URL}${this.EMPRESAS}`;
            },

            // URL completa para empresas por usuario
            getEmpresasPorUsuarioUrl: function(userId) {
                return `${this.BASE_URL}${this.EMPRESAS_POR_USUARIO}/${userId}`;
            },
            
            // URL completa para uma empresa específica
            getEmpresaUrl: function(id) {
                return `${this.BASE_URL}${this.EMPRESAS}/${id}`;
            }
        };

        const SEGMENTO = <?php echo json_encode(strtolower((string)$segmento)); ?>;
        const ID_USUARIO = <?php echo json_encode($id_usuario); ?>;
        const PERFIL_VAREJO = <?php echo json_encode($isVarejo); ?>;
        const PERFIL_ADMIN_EMPRESA = <?php echo json_encode($isAdminEmpresa); ?>;
        const API_TOKEN = <?php echo json_encode($token); ?>;
        const ID_EMPRESA_SESSAO = <?php echo json_encode($id_empresa); ?>;
        const EMPRESA_NOME_SESSAO = <?php echo json_encode($empresa['nome_empresa'] ?? ''); ?>;
    </script>
    
    <script>
        const USUARIOS_API = {
            getUsuariosUrl: () => `${API_CONFIG.BASE_URL}/api/usuarios`,
            getUsuarioUrl: (id) => `${API_CONFIG.BASE_URL}/api/usuarios/${id}`,
            getUsuariosEmpresaUrl: (idEmpresa) => `${API_CONFIG.BASE_URL}/api/usuarios/empresa/${idEmpresa}`,
        };

        let usuariosEmpresa = [];
        let usuariosEmpresaAtualId = null;
        let usuariosModal = null;
        let usuariosModalConfirmacao = null;
        const usuariosPerfisPadrao = [
            { value: 'super_admin', label: 'Super Admin' },
            { value: 'admin_empresa', label: 'Admin Empresa' },
            { value: 'usuario', label: 'Usuário' },
            { value: 'manager', label: 'Gestor' }
        ];

        document.addEventListener('DOMContentLoaded', function() {
            usuariosModal = new bootstrap.Modal(document.getElementById('modalUsuarioEmpresa'));
            usuariosModalConfirmacao = new bootstrap.Modal(document.getElementById('modalUsuariosConfirmacao'));
            usuariosAtualizarPerfisDisponiveis();

            const selectEmpresa = document.getElementById('usuariosEmpresaSelect');
            if (selectEmpresa) {
                selectEmpresa.addEventListener('change', () => {
                    usuariosEmpresaAtualId = selectEmpresa.value ? parseInt(selectEmpresa.value) : null;
                    usuariosCarregarUsuarios();
                });
            }

            const searchInput = document.getElementById('usuariosSearchInput');
            const filterPerfil = document.getElementById('usuariosFilterPerfil');
            const filterStatus = document.getElementById('usuariosFilterStatus');
            if (searchInput) searchInput.addEventListener('input', usuariosFiltrarUsuarios);
            if (filterPerfil) filterPerfil.addEventListener('change', usuariosFiltrarUsuarios);
            if (filterStatus) filterStatus.addEventListener('change', usuariosFiltrarUsuarios);

            if (ID_EMPRESA_SESSAO && PERFIL_ADMIN_EMPRESA) {
                usuariosEmpresaAtualId = parseInt(ID_EMPRESA_SESSAO);
                usuariosAtualizarEmpresas([]);
            }
        });

        function usuariosAtualizarEmpresas(listaEmpresas) {
            const selectEmpresa = document.getElementById('usuariosEmpresaSelect');
            const wrapper = document.getElementById('usuariosEmpresaSelectWrapper');
            if (!selectEmpresa) return;

            const empresasLista = Array.isArray(listaEmpresas) ? listaEmpresas : [];
            selectEmpresa.innerHTML = '';

            if (empresasLista.length > 0) {
                empresasLista.forEach((empresa) => {
                    selectEmpresa.innerHTML += `<option value="${empresa.id_empresa}">${empresa.nome_empresa}</option>`;
                });
            } else if (ID_EMPRESA_SESSAO) {
                const label = EMPRESA_NOME_SESSAO ? EMPRESA_NOME_SESSAO : `Empresa ${ID_EMPRESA_SESSAO}`;
                selectEmpresa.innerHTML = `<option value="${ID_EMPRESA_SESSAO}">${label}</option>`;
            } else {
                selectEmpresa.innerHTML = '<option value="">Selecione uma empresa</option>';
            }

            if (PERFIL_ADMIN_EMPRESA && ID_EMPRESA_SESSAO) {
                selectEmpresa.value = ID_EMPRESA_SESSAO;
                selectEmpresa.disabled = true;
                if (wrapper) wrapper.style.display = 'none';
                usuariosEmpresaAtualId = parseInt(ID_EMPRESA_SESSAO);
                usuariosCarregarUsuarios();
                return;
            }

            if (selectEmpresa.value) {
                usuariosEmpresaAtualId = parseInt(selectEmpresa.value);
                usuariosCarregarUsuarios();
            }
        }

        async function usuariosCarregarUsuarios() {
            if (!usuariosEmpresaAtualId) {
                document.getElementById('usuariosTbody').innerHTML = '<tr><td colspan="9" class="text-center text-muted">Selecione uma empresa.</td></tr>';
                document.getElementById('usuariosTotal').textContent = '0 usuário(s) encontrado(s)';
                return;
            }

            mostrarLoading(true);
            try {
                const response = await fetch(USUARIOS_API.getUsuariosEmpresaUrl(usuariosEmpresaAtualId), {
                    method: 'GET',
                    headers: API_CONFIG.getHeaders(API_TOKEN)
                });

                if (!response.ok) {
                    throw new Error(`Erro ${response.status}: ${response.statusText}`);
                }

                const data = await response.json();
                usuariosEmpresa = data.data || data;
                usuariosAtualizarPerfisDisponiveis();
                usuariosExibirUsuarios(usuariosEmpresa);
                usuariosAtualizarTotal(usuariosEmpresa.length);
            } catch (error) {
                console.error('Erro ao carregar usuários:', error);
                mostrarNotificacao('Erro ao carregar usuários: ' + error.message, 'error');
                document.getElementById('usuariosTbody').innerHTML = '<tr><td colspan="9" class="text-center text-muted">Erro ao carregar dados</td></tr>';
                usuariosAtualizarTotal(0);
            } finally {
                mostrarLoading(false);
            }
        }

        function usuariosAtualizarPerfisDisponiveis() {
            const selectPerfil = document.getElementById('usuariosPerfil');
            const filterPerfil = document.getElementById('usuariosFilterPerfil');
            if (!selectPerfil || !filterPerfil) return;

            const perfisEncontrados = new Set(
                usuariosEmpresa
                    .map((u) => u.perfil)
                    .filter((p) => p && String(p).trim() !== '')
                    .map((p) => String(p))
            );

            const lista = [...usuariosPerfisPadrao];
            perfisEncontrados.forEach((value) => {
                if (!lista.some((item) => item.value === value)) {
                    lista.push({ value, label: usuariosFormatarPerfil(value) });
                }
            });

            selectPerfil.innerHTML = '<option value="">Selecione o perfil</option>';
            filterPerfil.innerHTML = '<option value="">Todos os perfis</option>';

            lista.forEach((perfil) => {
                selectPerfil.innerHTML += `<option value="${perfil.value}">${perfil.label}</option>`;
                filterPerfil.innerHTML += `<option value="${perfil.value}">${perfil.label}</option>`;
            });
        }

        function usuariosExibirUsuarios(lista) {
            const tbody = document.getElementById('usuariosTbody');
            if (!Array.isArray(lista) || lista.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9" class="text-center text-muted">Nenhum usuário encontrado</td></tr>';
                return;
            }

            tbody.innerHTML = lista.map((usuario) => {
                const usuarioId = usuario.id_usuario || usuario.id;
                const ativo = !!usuario.ativo;
                const botaoAtivo = ativo
                    ? '<button class="btn btn-action btn-outline-warning" onclick="usuariosAlternarAtivo(' + usuarioId + ', 1)" title="Inativar"><i class="bi bi-person-x"></i></button>'
                    : '<button class="btn btn-action btn-outline-success" onclick="usuariosAlternarAtivo(' + usuarioId + ', 0)" title="Ativar"><i class="bi bi-person-check"></i></button>';

                return `
                <tr>
                    <td>${usuarioId}</td>
                    <td><div class="fw-semibold">${usuario.nome || '-'}</div></td>
                    <td>${usuario.email || '-'}</td>
                    <td><span class="perfil-badge">${usuariosFormatarPerfil(usuario.perfil)}</span></td>
                    <td><span class="status-badge ${ativo ? 'status-ativo' : 'status-inativo'}">${ativo ? 'Ativo' : 'Inativo'}</span></td>
                    <td><i class="bi ${usuario.aceitou_termos ? 'bi-check-circle text-success' : 'bi-x-circle text-danger'}"></i></td>
                    <td><i class="bi ${usuario.newsletter ? 'bi-check-circle text-success' : 'bi-x-circle text-danger'}"></i></td>
                    <td>${usuariosFormatarData(usuario.created_at)}</td>
                    <td>
                        <div class="d-flex">
                            <button class="btn btn-action btn-outline-primary" onclick="usuariosEditarUsuario(${usuarioId})" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </button>
                            ${botaoAtivo}
                            <button class="btn btn-action btn-outline-danger" onclick="usuariosConfirmarExclusao(${usuarioId}, '${(usuario.nome || '').replace(/'/g, "\\'")}')" title="Excluir">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                `;
            }).join('');
        }

        function usuariosAbrirModalUsuario() {
            if (!usuariosEmpresaAtualId) {
                mostrarNotificacao('Selecione uma empresa para cadastrar usuários.', 'error');
                return;
            }
            document.getElementById('modalUsuarioEmpresaLabel').textContent = 'Novo Usuário';
            document.getElementById('usuariosForm').reset();
            document.getElementById('usuariosUsuarioId').value = '';
            document.getElementById('usuariosEmpresaId').value = usuariosEmpresaAtualId;
            document.getElementById('usuariosSenha').required = true;
            document.getElementById('usuariosLabelSenha').innerHTML = 'Senha *';
            document.getElementById('usuariosTextoAjudaSenha').style.display = 'block';
            document.getElementById('usuariosForm').classList.remove('was-validated');
            usuariosModal.show();
        }

        async function usuariosEditarUsuario(id) {
            if (!id) return;
            mostrarLoading(true);
            try {
                const response = await fetch(USUARIOS_API.getUsuarioUrl(id), {
                    method: 'GET',
                    headers: API_CONFIG.getHeaders(API_TOKEN)
                });
                if (!response.ok) {
                    throw new Error(`Erro ${response.status}: ${response.statusText}`);
                }
                const data = await response.json();
                const usuario = data.data || data;
                const usuarioId = usuario.id_usuario || usuario.id;

                document.getElementById('modalUsuarioEmpresaLabel').textContent = 'Editar Usuário';
                document.getElementById('usuariosUsuarioId').value = usuarioId;
                document.getElementById('usuariosEmpresaId').value = usuario.id_empresa || usuariosEmpresaAtualId || '';
                document.getElementById('usuariosNome').value = usuario.nome || '';
                document.getElementById('usuariosEmail').value = usuario.email || '';
                document.getElementById('usuariosPerfil').value = usuario.perfil || '';
                if (usuario.perfil && !Array.from(document.getElementById('usuariosPerfil').options).some(o => o.value === usuario.perfil)) {
                    const option = document.createElement('option');
                    option.value = usuario.perfil;
                    option.textContent = usuariosFormatarPerfil(usuario.perfil);
                    document.getElementById('usuariosPerfil').appendChild(option);
                    document.getElementById('usuariosFilterPerfil').appendChild(option.cloneNode(true));
                }
                document.getElementById('usuariosAtivo').checked = !!usuario.ativo;
                document.getElementById('usuariosAceitouTermos').checked = !!usuario.aceitou_termos;
                document.getElementById('usuariosNewsletter').checked = !!usuario.newsletter;

                document.getElementById('usuariosSenha').required = false;
                document.getElementById('usuariosLabelSenha').innerHTML = 'Senha <small class="text-muted">(opcional)</small>';
                document.getElementById('usuariosTextoAjudaSenha').style.display = 'none';
                document.getElementById('usuariosSenha').value = '';
                document.getElementById('usuariosForm').classList.remove('was-validated');
                usuariosModal.show();
            } catch (error) {
                console.error('Erro ao carregar usuário:', error);
                mostrarNotificacao('Erro ao carregar dados do usuário: ' + error.message, 'error');
            } finally {
                mostrarLoading(false);
            }
        }

        async function usuariosSalvarUsuario() {
            const form = document.getElementById('usuariosForm');
            const nome = document.getElementById('usuariosNome').value.trim();
            const email = document.getElementById('usuariosEmail').value.trim();
            const perfil = document.getElementById('usuariosPerfil').value;
            const senha = document.getElementById('usuariosSenha').value;
            const usuarioId = document.getElementById('usuariosUsuarioId').value;
            const empresaId = document.getElementById('usuariosEmpresaId').value || usuariosEmpresaAtualId;

            if (!nome || !email || !perfil) {
                form.classList.add('was-validated');
                mostrarNotificacao('Por favor, preencha todos os campos obrigatórios.', 'error');
                return;
            }

            if (!usuarioId) {
                if (!senha) {
                    mostrarNotificacao('Para novo usuário, a senha é obrigatória.', 'error');
                    return;
                }
                if (senha.length < 6) {
                    mostrarNotificacao('A senha deve ter pelo menos 6 caracteres.', 'error');
                    return;
                }
            }

            if (usuarioId && senha && senha.length < 6) {
                mostrarNotificacao('A senha deve ter pelo menos 6 caracteres.', 'error');
                return;
            }

            const dadosUsuario = {
                id_empresa: parseInt(empresaId),
                nome: nome,
                email: email,
                perfil: perfil,
                ativo: document.getElementById('usuariosAtivo').checked ? 1 : 0,
                aceitou_termos: document.getElementById('usuariosAceitouTermos').checked ? 1 : 0,
                newsletter: document.getElementById('usuariosNewsletter').checked ? 1 : 0
            };

            if ((!usuarioId || senha) && senha.length >= 6) {
                dadosUsuario.senha = senha;
            }

            mostrarLoading(true);
            try {
                let response;
                if (usuarioId) {
                    response = await fetch(USUARIOS_API.getUsuarioUrl(usuarioId), {
                        method: 'PUT',
                        headers: API_CONFIG.getJsonHeaders(API_TOKEN),
                        body: JSON.stringify(dadosUsuario)
                    });
                } else {
                    response = await fetch(USUARIOS_API.getUsuariosUrl(), {
                        method: 'POST',
                        headers: API_CONFIG.getJsonHeaders(API_TOKEN),
                        body: JSON.stringify(dadosUsuario)
                    });
                }

                if (!response.ok) {
                    let errorMessage = `Erro ${response.status}: ${response.statusText}`;
                    try {
                        const errorData = await response.json();
                        if (errorData.errors) {
                            const allErrors = [];
                            for (const [field, errors] of Object.entries(errorData.errors)) {
                                allErrors.push(`${field}: ${errors.join(', ')}`);
                            }
                            errorMessage = allErrors.join('; ');
                        } else if (errorData.message) {
                            errorMessage = errorData.message;
                        }
                    } catch (parseError) {}
                    if (errorMessage.includes('email') && errorMessage.includes('already been taken')) {
                        errorMessage = 'Este email já está em uso. Por favor, utilize outro email.';
                    }
                    if (errorMessage.includes('Data truncated') || errorMessage.includes('perfil')) {
                        errorMessage = 'Erro no campo perfil. Verifique se o valor é válido.';
                    }
                    throw new Error(errorMessage);
                }

                usuariosModal.hide();
                form.classList.remove('was-validated');
                mostrarNotificacao(`Usuário "${nome}" ${usuarioId ? 'atualizado' : 'criado'} com sucesso!`, 'success');
                usuariosCarregarUsuarios();
            } catch (error) {
                console.error('Erro ao salvar usuário:', error);
                mostrarNotificacao('Erro ao salvar usuário: ' + error.message, 'error');
            } finally {
                mostrarLoading(false);
            }
        }

        function usuariosConfirmarExclusao(id, nome) {
            document.getElementById('usuariosNomeExcluir').textContent = nome || '';
            const btnConfirmar = document.getElementById('usuariosBtnConfirmarExclusao');
            btnConfirmar.onclick = function() {
                usuariosExcluirUsuario(id);
            };
            usuariosModalConfirmacao.show();
        }

        async function usuariosExcluirUsuario(id) {
            if (!id) return;
            mostrarLoading(true);
            try {
                const response = await fetch(USUARIOS_API.getUsuarioUrl(id), {
                    method: 'DELETE',
                    headers: API_CONFIG.getHeaders(API_TOKEN)
                });
                if (!response.ok) {
                    let errorMessage = `Erro ${response.status}: ${response.statusText}`;
                    try {
                        const errorData = await response.json();
                        if (errorData.message) errorMessage = errorData.message;
                    } catch (parseError) {}
                    throw new Error(errorMessage);
                }
                usuariosModalConfirmacao.hide();
                mostrarNotificacao('Usuário excluído com sucesso!', 'success');
                usuariosCarregarUsuarios();
            } catch (error) {
                console.error('Erro ao excluir usuário:', error);
                mostrarNotificacao('Erro ao excluir usuário: ' + error.message, 'error');
                usuariosModalConfirmacao.hide();
            } finally {
                mostrarLoading(false);
            }
        }

        async function usuariosAlternarAtivo(id, ativoAtual) {
            const usuario = usuariosEmpresa.find(u => String(u.id_usuario || u.id) === String(id));
            if (!usuario) return;
            const payload = {
                id_empresa: parseInt(usuario.id_empresa || usuariosEmpresaAtualId),
                nome: usuario.nome,
                email: usuario.email,
                perfil: usuario.perfil,
                ativo: ativoAtual ? 0 : 1,
                aceitou_termos: usuario.aceitou_termos ? 1 : 0,
                newsletter: usuario.newsletter ? 1 : 0
            };
            mostrarLoading(true);
            try {
                const response = await fetch(USUARIOS_API.getUsuarioUrl(id), {
                    method: 'PUT',
                    headers: API_CONFIG.getJsonHeaders(API_TOKEN),
                    body: JSON.stringify(payload)
                });
                if (!response.ok) {
                    throw new Error(`Erro ${response.status}: ${response.statusText}`);
                }
                mostrarNotificacao('Status do usuário atualizado.', 'success');
                usuariosCarregarUsuarios();
            } catch (error) {
                mostrarNotificacao('Erro ao atualizar usuário: ' + error.message, 'error');
            } finally {
                mostrarLoading(false);
            }
        }

        function usuariosFiltrarUsuarios() {
            const termoBusca = document.getElementById('usuariosSearchInput').value.toLowerCase();
            const perfilFiltro = document.getElementById('usuariosFilterPerfil').value;
            const statusFiltro = document.getElementById('usuariosFilterStatus').value;

            const filtrados = usuariosEmpresa.filter((usuario) => {
                const matchBusca = !termoBusca ||
                    (usuario.nome || '').toLowerCase().includes(termoBusca) ||
                    (usuario.email || '').toLowerCase().includes(termoBusca);
                const matchPerfil = !perfilFiltro || usuario.perfil === perfilFiltro;
                const matchStatus = !statusFiltro ||
                    (statusFiltro === 'ativo' && usuario.ativo) ||
                    (statusFiltro === 'inativo' && !usuario.ativo);
                return matchBusca && matchPerfil && matchStatus;
            });

            usuariosExibirUsuarios(filtrados);
            usuariosAtualizarTotal(filtrados.length);
        }

        function usuariosFormatarPerfil(perfil) {
            const perfis = {
                'admin_empresa': 'Admin Empresa',
                'super_admin': 'Super Admin',
                'usuario': 'Usuário',
                'manager': 'Gestor',
            };
            return perfis[perfil] || perfil || '-';
        }

        function usuariosFormatarData(data) {
            if (!data) return '';
            try {
                const date = new Date(data);
                return date.toLocaleDateString('pt-BR');
            } catch (e) {
                return data;
            }
        }

        function usuariosAtualizarTotal(total) {
            const el = document.getElementById('usuariosTotal');
            if (el) {
                el.textContent = `${total} usuário(s) encontrado(s)`;
            }
        }
    </script>
    
    <script>
        // Variáveis globais
        let empresas = [];
        let empresaEditando = null;
        let modalEmpresa = null;
        let modalConfirmacao = null;

        function bloquearCriacaoEmpresa() {
            if (!PERFIL_VAREJO) {
                return false;
            }
            mostrarNotificacao('Somente administradores podem cadastrar empresas.', 'error');
            return true;
        }

        function bloquearExclusaoEmpresa() {
            if (!PERFIL_VAREJO) {
                return false;
            }
            mostrarNotificacao('Somente administradores podem excluir empresas.', 'error');
            return true;
        }

        function aplicarRestricoesCampos() {
            const bloquear = PERFIL_VAREJO;
            const camposReadonly = ['nome_empresa'];
            const camposDisabled = ['segmento', 'status'];
            const camposBloqueados = ['cnpj'];

            camposReadonly.forEach((id) => {
                const el = document.getElementById(id);
                if (el) {
                    el.readOnly = bloquear;
                }
            });

            camposDisabled.forEach((id) => {
                const el = document.getElementById(id);
                if (el) {
                    el.disabled = bloquear;
                }
            });

            camposBloqueados.forEach((id) => {
                const el = document.getElementById(id);
                if (el) {
                    el.readOnly = bloquear;
                    el.disabled = bloquear;
                }
            });
        }

        // Inicialização
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar modais
            modalEmpresa = new bootstrap.Modal(document.getElementById('modalEmpresa'));
            modalConfirmacao = new bootstrap.Modal(document.getElementById('modalConfirmacao'));
            
            // Carregar empresas
            carregarEmpresas();
            
            // Configurar eventos
            document.getElementById('searchInput').addEventListener('input', filtrarEmpresas);
            const filterSegmentoEl = document.getElementById('filterSegmento');
            const filterStatusEl = document.getElementById('filterStatus');
            if (filterSegmentoEl) {
                filterSegmentoEl.addEventListener('change', filtrarEmpresas);
            }
            if (filterStatusEl) {
                filterStatusEl.addEventListener('change', filtrarEmpresas);
            }
            
            // Logoff
            var logoutBtn = document.getElementById('logoutBtn');
            if (logoutBtn) {
                logoutBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    // Usando a função de logout da API
                    fazerLogout();
                });
            }
        });

        // Função para fazer logout
        async function fazerLogout() {
            try {
                const token = '<?php echo $token; ?>';
                const response = await fetch(API_CONFIG.BASE_URL + API_CONFIG.LOGOUT, {
                    method: 'POST',
                    headers: API_CONFIG.getHeaders(token)
                });
                
                // Redirecionar para login independente da resposta
                window.location.href = 'login.php';
                
            } catch (error) {
                console.error('Erro no logout:', error);
                // Mesmo com erro, redireciona para login
                window.location.href = 'login.php';
            }
        }

        // Função para carregar empresas da API
        async function carregarEmpresas() {
            mostrarLoading(true);
            
            try {
                const token = '<?php echo $token; ?>';
                console.log('Token sendo usado:', token); // Para debug
                const usarPorUsuario = (PERFIL_VAREJO && ID_USUARIO);
                if (PERFIL_VAREJO && !ID_USUARIO) {
                    throw new Error('ID do usuario nao encontrado na sessao.');
                }
                const url = usarPorUsuario
                    ? API_CONFIG.getEmpresasPorUsuarioUrl(ID_USUARIO)
                    : API_CONFIG.getEmpresasUrl();

                const response = await fetch(url, {
                    method: 'GET',
                    headers: API_CONFIG.getHeaders(token)
                });
                
                console.log('Response status:', response.status); // Para debug
                
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('Erro na resposta:', errorText);
                    throw new Error(`Erro ${response.status}: ${response.statusText}`);
                }
                
                const data = await response.json();
                console.log('Empresas carregadas:', data); // Para debug

                if (usarPorUsuario) {
                    if (data.empresa) {
                        empresas = [data.empresa];
                    } else if (Array.isArray(data)) {
                        empresas = data;
                    } else if (data.empresas && Array.isArray(data.empresas)) {
                        empresas = data.empresas;
                    } else {
                        empresas = [];
                    }
                } else {
                    empresas = Array.isArray(data) ? data : [];
                }

                exibirEmpresas(empresas);
                atualizarTotalEmpresas(empresas.length);
                if (typeof usuariosAtualizarEmpresas === 'function') {
                    usuariosAtualizarEmpresas(empresas);
                }
                
            } catch (error) {
                console.error('Erro ao carregar empresas:', error);
                mostrarNotificacao('Erro ao carregar empresas: ' + error.message, 'error');
                document.getElementById('tbodyEmpresas').innerHTML = '<tr><td colspan="7" class="text-center text-muted">Erro ao carregar dados</td></tr>';
                atualizarTotalEmpresas(0);
            } finally {
                mostrarLoading(false);
            }
        }

        // Função para exibir empresas na tabela
        function exibirEmpresas(listaEmpresas) {
            const tbody = document.getElementById('tbodyEmpresas');
            const podeGerenciar = !PERFIL_VAREJO;
            const podeExcluir = !PERFIL_VAREJO;
            
            if (listaEmpresas.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Nenhuma empresa encontrada</td></tr>';
                return;
            }
            
            tbody.innerHTML = listaEmpresas.map(empresa => `
                <tr>
                    <td>${empresa.id_empresa}</td>
                    <td>
                        <div class="fw-semibold">${empresa.nome_empresa}</div>
                        ${empresa.email_empresa ? `<small class="text-muted">${empresa.email_empresa}</small>` : ''}
                    </td>
                    <td>${formatarCNPJ(empresa.cnpj)}</td>
                    <td><span class="segmento-badge">${formatarSegmento(empresa.segmento)}</span></td>
                    <td><span class="status-badge status-${empresa.status}">${formatarStatus(empresa.status)}</span></td>
                    <td>${formatarData(empresa.data_cadastro || empresa.created_at)}</td>
                    <td>
                        <div class="d-flex">
                            <button class="btn btn-action btn-outline-primary" onclick="editarEmpresa(${empresa.id_empresa})" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </button>
                            ${podeGerenciar ? `
                                <a href="?view=gestao_usuarios&id_empresa=${empresa.id_empresa}" class="btn btn-action btn-outline-primary" title="Gestao de Usuarios">
                                    <i class="bi bi-people"></i>
                                </a>   
                                ${PERFIL_ADMIN_EMPRESA ? `
                                    <a href="?view=admin-gestao-permissoes&id_empresa=${empresa.id_empresa}" class="btn btn-action btn-outline-info" title="Gestão de Permissões">
                                        <i class="bi bi-shield-lock"></i>
                                    </a>
                                ` : ''}
                                <a href="?view=admin-filiais_empresa&id_empresas=${empresa.id_empresa}" class="btn btn-action btn-outline-primary" title="Gestao de Filiais">
                                    <i class="bi bi-building"></i>
                                </a>
                            ` : ''}
                            ${podeExcluir ? `
                                <button class="btn btn-action btn-outline-danger" onclick="confirmarExclusao(${empresa.id_empresa}, '${empresa.nome_empresa.replace(/'/g, "\\'")}')" title="Excluir">
                                    <i class="bi bi-trash"></i>
                                </button>
                            ` : ''}
                        </div>
                    </td>
                </tr>
            `).join('');
        }

        // Função para abrir modal de nova empresa
        function abrirModalEmpresa() {
            if (bloquearCriacaoEmpresa()) {
                return;
            }
            empresaEditando = null;
            document.getElementById('modalEmpresaLabel').textContent = 'Nova Empresa';
            document.getElementById('formEmpresa').reset();
            aplicarRestricoesCampos();
            document.getElementById('empresaId').value = '';
            
            // Limpar validação
            document.getElementById('formEmpresa').classList.remove('was-validated');
            
            modalEmpresa.show();
        }

        // Função para editar empresa
        async function editarEmpresa(id) {
            mostrarLoading(true);
            
            try {
                const token = '<?php echo $token; ?>';
                const response = await fetch(API_CONFIG.getEmpresaUrl(id), {
                    method: 'GET',
                    headers: API_CONFIG.getHeaders(token)
                });
                
                if (!response.ok) {
                    throw new Error(`Erro ${response.status}: ${response.statusText}`);
                }
                
                const empresa = await response.json();
                empresaEditando = empresa;
                
                // Preencher formulário
                document.getElementById('modalEmpresaLabel').textContent = 'Editar Empresa';
                document.getElementById('empresaId').value = empresa.id_empresa;
                document.getElementById('nome_empresa').value = empresa.nome_empresa || '';
                document.getElementById('cnpj').value = empresa.cnpj || '';
                document.getElementById('segmento').value = empresa.segmento || '';
                document.getElementById('email_empresa').value = empresa.email_empresa || '';
                document.getElementById('telefone').value = empresa.telefone || '';
                document.getElementById('website').value = empresa.website || '';
                document.getElementById('endereco').value = empresa.endereco || '';
                document.getElementById('cep').value = empresa.cep || '';
                document.getElementById('cidade').value = empresa.cidade || '';
                document.getElementById('estado').value = empresa.estado || '';
                document.getElementById('status').value = empresa.status || 'ativa';
                aplicarRestricoesCampos();
                
                // Limpar validação
                document.getElementById('formEmpresa').classList.remove('was-validated');
                
                modalEmpresa.show();
                
            } catch (error) {
                console.error('Erro ao carregar empresa:', error);
                mostrarNotificacao('Erro ao carregar dados da empresa: ' + error.message, 'error');
            } finally {
                mostrarLoading(false);
            }
        }

        // Função para salvar empresa (criar ou editar)
        async function salvarEmpresa() {
            const form = document.getElementById('formEmpresa');

            if (PERFIL_VAREJO && !empresaEditando) {
                mostrarNotificacao('Somente administradores podem cadastrar empresas.', 'error');
                return;
            }

            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                return;
            }
            
            mostrarLoading(true);
            
            const dadosEmpresa = {
                nome_empresa: document.getElementById('nome_empresa').value,
                cnpj: document.getElementById('cnpj').value,
                segmento: document.getElementById('segmento').value,
                email_empresa: document.getElementById('email_empresa').value,
                telefone: document.getElementById('telefone').value,
                website: document.getElementById('website').value,
                endereco: document.getElementById('endereco').value,
                cep: document.getElementById('cep').value,
                cidade: document.getElementById('cidade').value,
                estado: document.getElementById('estado').value,
                status: document.getElementById('status').value
            };

            if (PERFIL_VAREJO && empresaEditando) {
                dadosEmpresa.nome_empresa = empresaEditando.nome_empresa;
                dadosEmpresa.cnpj = empresaEditando.cnpj;
                dadosEmpresa.segmento = empresaEditando.segmento;
                dadosEmpresa.status = empresaEditando.status;
            }
            
            try {
                const token = '<?php echo $token; ?>';
                let response;
                let url;
                
                if (empresaEditando) {
                    // Editar empresa existente
                    url = API_CONFIG.getEmpresaUrl(empresaEditando.id_empresa);
                    response = await fetch(url, {
                        method: 'PUT',
                        headers: API_CONFIG.getHeaders(token),
                        body: JSON.stringify(dadosEmpresa)
                    });
                } else {
                    // Criar nova empresa
                    url = API_CONFIG.getEmpresasUrl();
                    response = await fetch(url, {
                        method: 'POST',
                        headers: API_CONFIG.getHeaders(token),
                        body: JSON.stringify(dadosEmpresa)
                    });
                }
                
                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}));
                    throw new Error(errorData.message || `Erro ${response.status}: ${response.statusText}`);
                }
                
                const empresaSalva = await response.json();
                
                modalEmpresa.hide();
                form.classList.remove('was-validated');
                
                mostrarNotificacao(
                    `Empresa ${empresaEditando ? 'atualizada' : 'criada'} com sucesso!`, 
                    'success'
                );
                
                // Recarregar lista
                carregarEmpresas();
                
            } catch (error) {
                console.error('Erro ao salvar empresa:', error);
                mostrarNotificacao('Erro ao salvar empresa: ' + error.message, 'error');
            } finally {
                mostrarLoading(false);
            }
        }

        // Função para confirmar exclusão
        function confirmarExclusao(id, nome) {
            if (bloquearExclusaoEmpresa()) {
                return;
            }
            document.getElementById('nomeEmpresaExcluir').textContent = nome;
            
            const btnConfirmar = document.getElementById('btnConfirmarExclusao');
            // Remover event listeners anteriores
            btnConfirmar.replaceWith(btnConfirmar.cloneNode(true));
            // Adicionar novo event listener
            document.getElementById('btnConfirmarExclusao').onclick = () => excluirEmpresa(id);
            
            modalConfirmacao.show();
        }

        // Função para excluir empresa
        async function excluirEmpresa(id) {
            if (bloquearExclusaoEmpresa()) {
                return;
            }
            mostrarLoading(true);
            
            try {
                const token = '<?php echo $token; ?>';
                const response = await fetch(API_CONFIG.getEmpresaUrl(id), {
                    method: 'DELETE',
                    headers: API_CONFIG.getHeaders(token)
                });
                
                if (!response.ok && response.status !== 204) {
                    throw new Error(`Erro ${response.status}: ${response.statusText}`);
                }
                
                modalConfirmacao.hide();
                mostrarNotificacao('Empresa excluída com sucesso!', 'success');
                
                // Recarregar lista
                carregarEmpresas();
                
            } catch (error) {
                console.error('Erro ao excluir empresa:', error);
                mostrarNotificacao('Erro ao excluir empresa: ' + error.message, 'error');
                modalConfirmacao.hide();
            } finally {
                mostrarLoading(false);
            }
        }

        // Função para filtrar empresas
        function filtrarEmpresas() {
            const termoBusca = document.getElementById('searchInput').value.toLowerCase();
            const segmentoEl = document.getElementById('filterSegmento');
            const statusEl = document.getElementById('filterStatus');
            const segmentoFiltro = segmentoEl ? segmentoEl.value : '';
            const statusFiltro = statusEl ? statusEl.value : '';
            
            const empresasFiltradas = empresas.filter(empresa => {
                const matchBusca = !termoBusca || 
                    empresa.nome_empresa.toLowerCase().includes(termoBusca) ||
                    (empresa.cnpj && empresa.cnpj.toLowerCase().includes(termoBusca));
                
                const matchSegmento = !segmentoFiltro || empresa.segmento === segmentoFiltro;
                const matchStatus = !statusFiltro || empresa.status === statusFiltro;
                
                return matchBusca && matchSegmento && matchStatus;
            });
            
            exibirEmpresas(empresasFiltradas);
            atualizarTotalEmpresas(empresasFiltradas.length);
        }

        // Funções auxiliares
        function formatarCNPJ(cnpj) {
            if (!cnpj) return '';
            // Remove qualquer caractere não numérico
            cnpj = cnpj.replace(/\D/g, '');
            // Aplica a formatação se tiver 14 dígitos
            if (cnpj.length === 14) {
                return cnpj.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5');
            }
            return cnpj;
        }

        function formatarSegmento(segmento) {
            const segmentos = {
                'varejo': 'Varejo',
                'industria': 'Indústria',
                'construcao': 'Construção',
                'financeiro': 'Financeiro',
                'seguranca': 'Segurança',
                'outros': 'Outros'
            };
            return segmentos[segmento] || segmento;
        }

        function formatarStatus(status) {
            const statusMap = {
                'ativa': 'Ativa',
                'pendente': 'Pendente',
                'inativa': 'Inativa'
            };
            return statusMap[status] || status;
        }

        function formatarData(data) {
            if (!data) return '';
            try {
                const date = new Date(data);
                return date.toLocaleDateString('pt-BR');
            } catch (e) {
                return data;
            }
        }

        function atualizarTotalEmpresas(total) {
            document.getElementById('totalEmpresas').textContent = `${total} empresa(s) encontrada(s)`;
        }

        function mostrarLoading(mostrar) {
            document.getElementById('loadingOverlay').classList.toggle('d-none', !mostrar);
        }

        // Função para mostrar notificações
        function mostrarNotificacao(message, type) {
            // Criar elemento de notificação
            const notification = document.createElement('div');
            notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
            notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            notification.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.body.appendChild(notification);
            
            // Remover automaticamente após 5 segundos
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 5000);
        }
    </script>
    <?php if (!defined('APP_SHELL')) { include __DIR__ . '/../components/app-foot.php'; } ?>
</body>
</html>










