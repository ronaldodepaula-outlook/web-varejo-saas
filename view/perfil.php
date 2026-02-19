<?php
$config = require __DIR__ . '/../funcoes/config.php';

session_start();
if (!isset($_SESSION['authToken'], $_SESSION['usuario'], $_SESSION['empresa'], $_SESSION['licenca'])) {
    header('Location: login.php');
    exit;
}

$usuarioSession = is_array($_SESSION['usuario']) ? $_SESSION['usuario'] : ['nome' => $_SESSION['usuario']];
$empresaSession = $_SESSION['empresa'] ?? [];
$token = $_SESSION['authToken'];
$segmento = $_SESSION['segmento'] ?? '';

$id_usuario = $_SESSION['user_id']
    ?? ($_SESSION['usuario_id'] ?? ($_SESSION['id_usuario'] ?? null));
if ($id_usuario === null && is_array($usuarioSession)) {
    $id_usuario = $usuarioSession['id_usuario'] ?? ($usuarioSession['id'] ?? null);
}

$perfilData = null;
$empresaData = null;
$erroApi = '';

if ($id_usuario && $token) {
    $url = rtrim($config['api_base'], '/') . '/api/usuarios/' . urlencode((string)$id_usuario);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: */*',
        'Authorization: Bearer ' . $token
    ]);
    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    unset($ch);

    if ($response === false) {
        $erroApi = 'Falha ao comunicar com a API: ' . $curlErr;
    } else {
        if (is_string($response) && strncmp($response, "\xEF\xBB\xBF", 3) === 0) {
            $response = substr($response, 3);
        }
        $data = json_decode($response, true);
        if ($httpcode === 200 && is_array($data)) {
            $perfilData = $data;
            $empresaData = $data['empresa'] ?? null;
        } else {
            $erroApi = isset($data['message']) ? $data['message'] : 'Não foi possível carregar o perfil.';
        }
    }
}

if (!$perfilData) {
    $perfilData = $usuarioSession;
    $empresaData = is_array($empresaSession) ? $empresaSession : [];
}

function formatar_data($data) {
    if (!$data) {
        return '-';
    }
    $timestamp = strtotime($data);
    if ($timestamp === false) {
        return $data;
    }
    return date('d/m/Y', $timestamp);
}

function formatar_perfil($perfil) {
    $map = [
        'admin' => 'Administrador',
        'admin_empresa' => 'Admin Empresa',
        'user' => 'Usuário',
        'usuario' => 'Usuário',
        'manager' => 'Gestor',
        'gestor' => 'Gestor',
        'super_admin' => 'Super Admin'
    ];
    return $map[$perfil] ?? ($perfil ?: '-');
}

function formatar_status($ativo) {
    return ((string)$ativo === '1' || $ativo === true) ? 'Ativo' : 'Inativo';
}

$nomeUsuario = $perfilData['nome'] ?? ($usuarioSession['nome'] ?? 'Usuário');
$emailUsuario = $perfilData['email'] ?? ($usuarioSession['email'] ?? '-');
$perfilUsuario = $perfilData['perfil'] ?? ($usuarioSession['perfil'] ?? '');
$inicialUsuario = strtoupper(substr((string)$nomeUsuario, 0, 1));
$empresaNome = $empresaData['nome_empresa'] ?? ($empresaSession['nome_empresa'] ?? '-');
$empresaCnpj = $empresaData['cnpj'] ?? ($empresaSession['cnpj'] ?? '-');
$empresaStatus = $empresaData['status'] ?? ($empresaSession['status'] ?? '-');

// Avatar URL (usando UI Avatars como fallback)
$avatarUrl = $empresaData['avatar_url'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($nomeUsuario) . '&size=128&background=3498DB&color=fff&bold=true';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?php if (!defined('APP_SHELL')) { $pageTitle = 'Meu Perfil - NexusFlow'; include __DIR__ . '/../components/app-head.php'; } ?>

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
        
        .header-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .sidebar-toggle {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--secondary-color);
            cursor: pointer;
            padding: 0;
            line-height: 1;
        }
        
        .sidebar-toggle:hover {
            color: var(--primary-color);
        }
        
        .breadcrumb-custom {
            background: transparent;
            padding: 0;
            margin: 0;
        }
        
        .breadcrumb-custom .breadcrumb-item a {
            color: var(--secondary-color);
            text-decoration: none;
        }
        
        .breadcrumb-custom .breadcrumb-item a:hover {
            color: var(--primary-color);
        }
        
        .breadcrumb-custom .breadcrumb-item.active {
            color: var(--primary-color);
        }
        
        .header-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .btn-link {
            text-decoration: none;
            color: var(--secondary-color);
        }
        
        .btn-link:hover {
            color: var(--primary-color);
        }
        
        .position-relative {
            position: relative;
        }
        
        .badge {
            font-size: 0.6rem;
            padding: 0.25rem 0.5rem;
        }
        
        .user-dropdown {
            position: relative;
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
            text-transform: uppercase;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .user-avatar:hover {
            background: #2980b9;
        }
        
        .dropdown-menu {
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-radius: 8px;
            padding: 8px 0;
        }
        
        .dropdown-header {
            font-weight: 600;
            color: var(--secondary-color);
            padding: 8px 16px;
        }
        
        .dropdown-header.user-name {
            font-size: 0.95rem;
            margin-bottom: 2px;
        }
        
        .dropdown-header.user-email {
            font-size: 0.8rem;
            color: #6c757d;
            padding-top: 0;
        }
        
        .dropdown-item {
            padding: 8px 16px;
            color: var(--secondary-color);
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .dropdown-item i {
            width: 18px;
            color: var(--primary-color);
        }
        
        .dropdown-item:hover {
            background-color: #f8f9fa;
            color: var(--primary-color);
        }
        
        .dropdown-item.text-danger i {
            color: var(--danger-color);
        }
        
        .dropdown-item.text-danger:hover {
            background-color: #fee2e2;
            color: var(--danger-color);
        }
        
        .dropdown-divider {
            margin: 4px 0;
            border-top: 1px solid #e9ecef;
        }
        
        .page-title {
            font-weight: 700;
            color: var(--secondary-color);
            margin-bottom: 5px;
            font-size: 1.8rem;
        }
        
        .page-subtitle {
            color: #6c757d;
            margin-bottom: 0;
        }
        
        .card-custom {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            overflow: hidden;
            margin-bottom: 20px;
        }
        
        .card-header-custom {
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            background: rgba(248, 249, 250, 0.8);
        }
        
        .card-header-custom h5 {
            margin: 0;
            font-weight: 600;
            color: var(--secondary-color);
        }
        
        .card-body {
            padding: 20px;
        }
        
        /* Profile Image */
        .profile-avatar {
            width: 128px;
            height: 128px;
            border-radius: 50%;
            margin: 20px auto 15px;
            display: block;
            object-fit: cover;
            border: 4px solid white;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        
        .text-center {
            text-align: center;
        }
        
        .profile-username {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--secondary-color);
            margin-bottom: 5px;
        }
        
        .profile-role {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 12px;
            background: rgba(52, 152, 219, 0.1);
            color: var(--primary-color);
            border-radius: 20px;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }
        
        /* List Group */
        .list-group {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .list-group-item {
            padding: 12px 15px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.95rem;
        }
        
        .list-group-item:last-child {
            border-bottom: none;
        }
        
        .list-group-item b {
            color: var(--secondary-color);
            font-weight: 600;
        }
        
        .list-group-item .float-right {
            color: #6c757d;
        }
        
        /* Badges */
        .badge-status {
            display: inline-flex;
            align-items: center;
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            gap: 4px;
        }
        
        .badge-ativo { 
            background: rgba(39, 174, 96, 0.1); 
            color: var(--success-color); 
        }
        
        .badge-inativo { 
            background: rgba(231, 76, 60, 0.1); 
            color: var(--danger-color); 
        }
        
        /* Tags */
        .tag {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            margin-right: 5px;
            margin-bottom: 5px;
        }
        
        .tag-primary { background: rgba(52, 152, 219, 0.1); color: var(--primary-color); }
        .tag-success { background: rgba(39, 174, 96, 0.1); color: var(--success-color); }
        .tag-warning { background: rgba(243, 156, 18, 0.1); color: var(--warning-color); }
        .tag-danger { background: rgba(231, 76, 60, 0.1); color: var(--danger-color); }
        
        /* Buttons */
        .btn-action {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            margin: 0 2px;
            background: none;
            border: 1px solid #dee2e6;
            color: var(--secondary-color);
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-action:hover {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        .btn-primary {
            background: var(--primary-color);
            border: none;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary:hover {
            background: #2980b9;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(52, 152, 219, 0.2);
        }
        
        .btn-outline-primary {
            background: transparent;
            border: 1px solid var(--primary-color);
            color: var(--primary-color);
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-outline-primary:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-1px);
        }
        
        .btn-outline-secondary {
            background: transparent;
            border: 1px solid #dee2e6;
            color: var(--secondary-color);
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-outline-secondary:hover {
            background: #f8f9fa;
            border-color: #c1c9d0;
        }
        
        .btn-sm {
            padding: 4px 8px;
            font-size: 0.85rem;
        }
        
        .btn-block {
            display: block;
            width: 100%;
            text-align: center;
        }
        
        /* Nav Pills */
        .nav-pills {
            display: flex;
            gap: 10px;
            margin: 0;
            padding: 0;
            list-style: none;
        }
        
        .nav-pills .nav-item {
            margin: 0;
        }
        
        .nav-pills .nav-link {
            padding: 8px 16px;
            border-radius: 6px;
            color: var(--secondary-color);
            text-decoration: none;
            font-size: 0.95rem;
            transition: all 0.2s;
        }
        
        .nav-pills .nav-link:hover {
            background: #f8f9fa;
            color: var(--primary-color);
        }
        
        .nav-pills .nav-link.active {
            background: var(--primary-color);
            color: white;
        }
        
        /* Tab Content */
        .tab-content {
            margin-top: 20px;
        }
        
        .tab-pane {
            display: none;
        }
        
        .tab-pane.active {
            display: block;
        }
        
        /* Posts */
        .post {
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .post:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .user-block {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .user-block i {
            font-size: 2.2rem;
            color: var(--primary-color);
            background: rgba(52, 152, 219, 0.1);
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }
        
        .username {
            font-weight: 600;
            color: var(--secondary-color);
        }
        
        .username a {
            color: var(--secondary-color);
            text-decoration: none;
        }
        
        .username a:hover {
            color: var(--primary-color);
        }
        
        .description {
            color: #6c757d;
            font-size: 0.85rem;
        }
        
        /* Formulários */
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group.row {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .col-sm-3 {
            flex: 0 0 25%;
            max-width: 25%;
            padding-right: 15px;
            text-align: right;
        }
        
        .col-sm-9 {
            flex: 0 0 75%;
            max-width: 75%;
            padding-left: 15px;
        }
        
        .form-label {
            font-weight: 500;
            color: var(--secondary-color);
            margin-bottom: 5px;
            display: block;
        }
        
        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            font-size: 0.95rem;
            transition: all 0.2s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }
        
        .form-control-sm {
            padding: 6px 10px;
            font-size: 0.85rem;
        }
        
        .form-control[readonly] {
            background-color: #f8f9fa;
            cursor: default;
        }
        
        .form-check {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
        }
        
        .form-check-input {
            width: 16px;
            height: 16px;
            cursor: pointer;
        }
        
        .form-check-label {
            color: var(--secondary-color);
            font-size: 0.95rem;
            cursor: pointer;
        }
        
        .form-check-label a {
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .form-check-label a:hover {
            text-decoration: underline;
        }
        
        /* Loading Overlay */
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
            border: 0.25em solid currentColor;
            border-right-color: transparent;
            border-radius: 50%;
            animation: spinner-border .75s linear infinite;
        }
        
        .text-primary {
            color: var(--primary-color) !important;
        }
        
        @keyframes spinner-border {
            to { transform: rotate(360deg); }
        }
        
        .d-none {
            display: none !important;
        }
        
        /* Alert */
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid;
        }
        
        .alert-danger {
            background: #fee2e2;
            border-left-color: var(--danger-color);
            color: #991b1b;
        }
        
        .alert-success {
            background: #dcfce7;
            border-left-color: var(--success-color);
            color: #166534;
        }
        
        /* Responsividade */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
            }
            
            .col-sm-3, .col-sm-9 {
                flex: 0 0 100%;
                max-width: 100%;
                text-align: left;
                padding: 5px 0;
            }
            
            .form-group.row {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .nav-pills {
                flex-wrap: wrap;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar será incluída separadamente -->
    
    <main class="main-content">
        <!-- Header -->
        <header class="main-header">
            <div class="header-left">
                <button class="sidebar-toggle" type="button" onclick="toggleSidebar()">
                    <i class="bi bi-list"></i>
                </button>
                
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-custom">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Meu Perfil</li>
                    </ol>
                </nav>
            </div>
            
            <div class="header-right">
                <!-- Dropdown do Usuário -->
                <div class="dropdown user-dropdown">
                    <div class="user-avatar" data-bs-toggle="dropdown" aria-expanded="false">
                        <?php echo $inicialUsuario; ?>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><h6 class="dropdown-header user-name" id="dropdownUserName"><?php echo htmlspecialchars($nomeUsuario); ?></h6></li>
                        <li><small class="dropdown-header text-muted user-email" id="dropdownUserEmail"><?php echo htmlspecialchars($emailUsuario); ?></small></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="?view=perfil"><i class="bi bi-person me-2"></i>Meu Perfil</a></li>
                        <li><a class="dropdown-item" href="#" onclick="abrirConfiguracoes()"><i class="bi bi-gear me-2"></i>Configurações</a></li>
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
                    <h1 class="page-title">Meu Perfil</h1>
                    <p class="page-subtitle">Gerencie suas informações pessoais e da empresa</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary" id="btnEditarPerfil">
                        <i class="bi bi-pencil me-2"></i>Editar Perfil
                    </button>
                </div>
            </div>
            
            <?php if ($erroApi): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <?php echo htmlspecialchars($erroApi); ?>
                </div>
            <?php endif; ?>
            
            <div class="row">
                <!-- Coluna Esquerda - Perfil -->
                <div class="col-md-4">
                    <!-- Card de Perfil -->
                    <div class="card-custom">
                        <div class="text-center">
                            <img src="<?php echo $avatarUrl; ?>" alt="Avatar" class="profile-avatar">
                            <h3 class="profile-username" id="profileName"><?php echo htmlspecialchars($nomeUsuario); ?></h3>
                            <div class="profile-role">
                                <i class="bi bi-tag"></i>
                                <?php echo htmlspecialchars(formatar_perfil($perfilUsuario)); ?>
                            </div>
                        </div>
                        
                        <div class="card-body">
                            <ul class="list-group">
                                <li class="list-group-item">
                                    <b><i class="bi bi-envelope me-2"></i>E-mail</b>
                                    <span class="float-right" id="profileEmail"><?php echo htmlspecialchars($emailUsuario); ?></span>
                                </li>
                                <li class="list-group-item">
                                    <b><i class="bi bi-circle me-2"></i>Status</b>
                                    <span class="float-right">
                                        <span class="badge-status <?php echo formatar_status($perfilData['ativo'] ?? 0) === 'Ativo' ? 'badge-ativo' : 'badge-inativo'; ?>" id="profileStatusBadge">
                                            <i class="bi bi-<?php echo formatar_status($perfilData['ativo'] ?? 0) === 'Ativo' ? 'check-circle' : 'x-circle'; ?>"></i>
                                            <?php echo htmlspecialchars(formatar_status($perfilData['ativo'] ?? 0)); ?>
                                        </span>
                                    </span>
                                </li>
                                <li class="list-group-item">
                                    <b><i class="bi bi-calendar me-2"></i>Membro desde</b>
                                    <span class="float-right"><?php echo htmlspecialchars(formatar_data($perfilData['data_criacao'] ?? ($perfilData['created_at'] ?? ''))); ?></span>
                                </li>
                                <li class="list-group-item">
                                    <b><i class="bi bi-check-circle me-2"></i>E-mail verificado</b>
                                    <span class="float-right"><?php echo !empty($perfilData['email_verificado_em']) ? formatar_data($perfilData['email_verificado_em']) : '-'; ?></span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    
                </div>
                
                <!-- Coluna Direita - Detalhes -->
                <div class="col-md-8">
                    <!-- Tabs -->
                    <div class="card-custom">
                        <div class="card-header-custom">
                            <ul class="nav nav-pills" id="profileTabs">
                                <li class="nav-item">
                                    <a class="nav-link active" href="#dados-usuario" data-tab="dados-usuario">
                                        <i class="bi bi-person me-1"></i>Dados do Usuário
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#dados-empresa" data-tab="dados-empresa">
                                        <i class="bi bi-building me-1"></i>Dados da Empresa
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#preferencias" data-tab="preferencias">
                                        <i class="bi bi-sliders2 me-1"></i>Preferências
                                    </a>
                                </li>
                            </ul>
                        </div>
                        
                        <div class="card-body">
                            <!-- Tab Dados do Usuário -->
                            <div class="tab-pane active" id="dados-usuario">
                                <div class="post">
                                    <div class="user-block">
                                        <i class="bi bi-person-circle"></i>
                                        <span class="username">
                                            <a href="#">Informações Básicas</a>
                                        </span>
                                        <span class="description">Dados cadastrais do usuário</span>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">Nome Completo</label>
                                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($nomeUsuario); ?>" readonly id="usuarioNome">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">E-mail</label>
                                                <input type="email" class="form-control" value="<?php echo htmlspecialchars($emailUsuario); ?>" readonly id="usuarioEmail">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">Perfil</label>
                                                <input type="text" class="form-control" value="<?php echo htmlspecialchars(formatar_perfil($perfilUsuario)); ?>" readonly id="usuarioPerfil">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">Status</label>
                                                <input type="text" class="form-control" value="<?php echo htmlspecialchars(formatar_status($perfilData['ativo'] ?? 0)); ?>" readonly id="usuarioStatus">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Tab Empresa -->
                            <div class="tab-pane" id="dados-empresa">
                                <div class="post">
                                    <div class="user-block">
                                        <i class="bi bi-building"></i>
                                        <span class="username">
                                            <a href="#">Dados da Empresa</a>
                                        </span>
                                        <span class="description">Informações cadastrais da empresa</span>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">Nome da Empresa</label>
                                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($empresaNome); ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">CNPJ</label>
                                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($empresaCnpj); ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">Segmento</label>
                                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($empresaData['segmento'] ?? '-'); ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">Telefone</label>
                                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($empresaData['telefone'] ?? '-'); ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">E-mail</label>
                                                <input type="email" class="form-control" value="<?php echo htmlspecialchars($empresaData['email_empresa'] ?? '-'); ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">Status</label>
                                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($empresaStatus); ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="form-label">Endereço</label>
                                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($empresaData['endereco'] ?? '-'); ?>" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Tab Preferências -->
                            <div class="tab-pane" id="preferencias">
                                <div class="post">
                                    <div class="user-block">
                                        <i class="bi bi-sliders2"></i>
                                        <span class="username">
                                            <a href="#">Preferências</a>
                                        </span>
                                        <span class="description">Configurações da sua conta</span>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">Termos aceitos</label>
                                                <input type="text" class="form-control" value="<?php echo !empty($perfilData['aceitou_termos']) ? 'Sim' : 'Não'; ?>" readonly id="prefTermos">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">Newsletter</label>
                                                <input type="text" class="form-control" value="<?php echo !empty($perfilData['newsletter']) ? 'Ativo' : 'Inativo'; ?>" readonly id="prefNewsletter">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal Editar Perfil -->
    <div class="modal fade" id="modalEditarPerfil" tabindex="-1" aria-labelledby="modalEditarPerfilLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarPerfilLabel">Editar Perfil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formEditarPerfil">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Nome Completo</label>
                                    <input type="text" class="form-control" id="editarNome" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">E-mail</label>
                                    <input type="email" class="form-control" id="editarEmail" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Nova Senha</label>
                                    <input type="password" class="form-control" id="editarSenha" minlength="6" placeholder="Deixe em branco para não alterar">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Confirmar Nova Senha</label>
                                    <input type="password" class="form-control" id="editarSenhaConfirm" minlength="6" placeholder="Repita a nova senha">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="editarNewsletter">
                                    <label class="form-check-label" for="editarNewsletter">
                                        Receber newsletter
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="editarTermos">
                                    <label class="form-check-label" for="editarTermos">
                                        Aceitar termos de uso
                                    </label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnSalvarPerfil" form="formEditarPerfil">
                        <i class="bi bi-check-circle me-2"></i>Salvar Alterações
                    </button>
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
        const API_BASE = <?php echo json_encode($config['api_base']); ?>;
        const token = '<?php echo $token; ?>';
        const PERFIL_DADOS = {
            idUsuario: <?php echo json_encode($perfilData['id_usuario'] ?? $id_usuario); ?>,
            idEmpresa: <?php echo json_encode($perfilData['id_empresa'] ?? ($empresaData['id_empresa'] ?? ($empresaSession['id_empresa'] ?? null))); ?>,
            nome: <?php echo json_encode($nomeUsuario); ?>,
            email: <?php echo json_encode($emailUsuario); ?>,
            perfil: <?php echo json_encode($perfilData['perfil'] ?? $perfilUsuario); ?>,
            ativo: <?php echo json_encode($perfilData['ativo'] ?? 1); ?>,
            aceitouTermos: <?php echo json_encode($perfilData['aceitou_termos'] ?? 0); ?>,
            newsletter: <?php echo json_encode($perfilData['newsletter'] ?? 0); ?>
        };

        const modalEditarPerfil = document.getElementById('modalEditarPerfil')
            ? new bootstrap.Modal(document.getElementById('modalEditarPerfil'))
            : null;
        
        // Toggle sidebar
        function toggleSidebar() {
            // Implementar toggle da sidebar
            document.querySelector('.main-content').classList.toggle('sidebar-collapsed');
        }
        
        // Tabs
        document.querySelectorAll('.nav-pills .nav-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Remove active de todas as tabs
                document.querySelectorAll('.nav-pills .nav-link').forEach(l => l.classList.remove('active'));
                document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
                
                // Ativa a tab clicada
                this.classList.add('active');
                const targetId = this.getAttribute('href').substring(1);
                document.getElementById(targetId).classList.add('active');
            });
        });
        
        // Logout
        document.getElementById('logoutBtn')?.addEventListener('click', async function(e) {
            e.preventDefault();
            mostrarLoading(true);
            
            try {
                await fetch(API_BASE + '/api/v1/logout', {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                });
            } catch (e) {}
            
            window.location.href = 'login.php';
        });
        
        // Editar Perfil
        function abrirModalEditarPerfil() {
            if (!modalEditarPerfil) {
                return;
            }
            document.getElementById('editarNome').value = PERFIL_DADOS.nome || '';
            document.getElementById('editarEmail').value = PERFIL_DADOS.email || '';
            document.getElementById('editarSenha').value = '';
            document.getElementById('editarSenhaConfirm').value = '';
            document.getElementById('editarNewsletter').checked = !!PERFIL_DADOS.newsletter;
            document.getElementById('editarTermos').checked = !!PERFIL_DADOS.aceitouTermos;
            modalEditarPerfil.show();
        }

        document.getElementById('btnEditarPerfil')?.addEventListener('click', function() {
            abrirModalEditarPerfil();
        });
        
        // Configurações
        function abrirConfiguracoes() {
            window.location.href = 'configuracoes.php';
        }
        
        // Salvar Perfil
        document.getElementById('formEditarPerfil')?.addEventListener('submit', async function(event) {
            event.preventDefault();

            const nome = document.getElementById('editarNome').value.trim();
            const email = document.getElementById('editarEmail').value.trim();
            const senha = document.getElementById('editarSenha').value.trim();
            const senhaConfirm = document.getElementById('editarSenhaConfirm').value.trim();
            const newsletter = document.getElementById('editarNewsletter').checked ? 1 : 0;
            const aceitouTermos = document.getElementById('editarTermos').checked ? 1 : 0;

            if (!nome || !email) {
                mostrarNotificacao('Preencha nome e e-mail.', 'error');
                return;
            }

            if (senha || senhaConfirm) {
                if (senha.length < 6) {
                    mostrarNotificacao('A senha deve ter pelo menos 6 caracteres.', 'error');
                    return;
                }
                if (senha != senhaConfirm) {
                    mostrarNotificacao('As senhas não coincidem.', 'error');
                    return;
                }
            }

            const payload = {
                id_empresa: PERFIL_DADOS.idEmpresa,
                nome: nome,
                email: email,
                perfil: PERFIL_DADOS.perfil,
                ativo: PERFIL_DADOS.ativo ? 1 : 0,
                aceitou_termos: aceitouTermos,
                newsletter: newsletter
            };

            if (senha) {
                payload.senha = senha;
            }

            mostrarLoading(true);

            try {
                const response = await fetch(`${API_BASE}/api/usuarios/${PERFIL_DADOS.idUsuario}`, {
                    method: 'PUT',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}));
                    const message = errorData.message || `Erro ${response.status}: ${response.statusText}`;
                    throw new Error(message);
                }

                const data = await response.json().catch(() => ({}));

                PERFIL_DADOS.nome = data.nome || nome;
                PERFIL_DADOS.email = data.email || email;
                PERFIL_DADOS.newsletter = data.newsletter ?? newsletter;
                PERFIL_DADOS.aceitouTermos = data.aceitou_termos ?? aceitouTermos;

                document.getElementById('profileName').textContent = PERFIL_DADOS.nome;
                document.getElementById('profileEmail').textContent = PERFIL_DADOS.email;
                document.getElementById('dropdownUserName').textContent = PERFIL_DADOS.nome;
                document.getElementById('dropdownUserEmail').textContent = PERFIL_DADOS.email;
                document.getElementById('usuarioNome').value = PERFIL_DADOS.nome;
                document.getElementById('usuarioEmail').value = PERFIL_DADOS.email;
                document.getElementById('prefTermos').value = PERFIL_DADOS.aceitouTermos ? 'Sim' : 'Não';
                document.getElementById('prefNewsletter').value = PERFIL_DADOS.newsletter ? 'Ativo' : 'Inativo';

                modalEditarPerfil?.hide();
                mostrarNotificacao('Perfil atualizado com sucesso!', 'success');
            } catch (error) {
                console.error('Erro ao atualizar perfil:', error);
                mostrarNotificacao('Erro ao atualizar perfil: ' + error.message, 'error');
            } finally {
                mostrarLoading(false);
            }
        });

// Loading
        function mostrarLoading(mostrar) {
            document.getElementById('loadingOverlay').classList.toggle('d-none', !mostrar);
        }
        
        // Notificações
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
    </script>
    
    <?php if (!defined('APP_SHELL')) { include __DIR__ . '/../components/app-foot.php'; } ?>
</body>
</html>
