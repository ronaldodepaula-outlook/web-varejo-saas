<?php
session_start();
$config = require __DIR__ . '/funcoes/config.php';
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $empresa = [
        'nome_empresa' => $_POST['nome_empresa'] ?? '',
        'cnpj' => $_POST['cnpj'] ?? '',
        'email_empresa' => $_POST['email_empresa'] ?? '',
        'telefone' => $_POST['telefone'] ?? '',
        'website' => $_POST['website'] ?? '',
        'endereco' => $_POST['endereco'] ?? '',
        'cep' => $_POST['cep'] ?? '',
        'cidade' => $_POST['cidade'] ?? '',
        'estado' => $_POST['estado'] ?? '',
        'segmento' => $_POST['segmento'] ?? ''
    ];
    
    $usuario = [
        'nome' => $_POST['nome'] ?? '',
        'email' => $_POST['email'] ?? '',
        'senha' => $_POST['senha'] ?? '',
        'aceitou_termos' => isset($_POST['aceitou_termos']) ? 1 : 0,
        'newsletter' => isset($_POST['newsletter']) ? 1 : 0
    ];
    
    // Campos obrigatórios
    if (empty($empresa['nome_empresa']) || empty($usuario['nome']) || empty($usuario['email']) || empty($usuario['senha'])) {
        $error = 'Preencha todos os campos obrigatórios.';
    } else {
        $data = json_encode(['empresa' => $empresa, 'usuario' => $usuario]);
        
        $ch = curl_init($config['api_base'] . '/api/registrar');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // Tratamento de BOM
        if (is_string($response) && strncmp($response, "\xEF\xBB\xBF", 3) === 0) {
            $response = substr($response, 3);
        }
        
        $resp = json_decode($response, true);
        
        if ($httpcode === 201) {
            header('Location: cadastro-sucesso.php');
            exit;
        } else {
            $error = $resp['message'] ?? 'Erro ao cadastrar.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?php $pageTitle = 'Cadastro - NexusFlow'; include __DIR__ . '/components/app-head.php'; ?>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --nexus-primary: #2563eb;
            --nexus-primary-dark: #1d4ed8;
            --nexus-secondary: #1e293b;
            --nexus-accent: #0ea5e9;
            --nexus-success: #16a34a;
            --nexus-error: #dc2626;
            --nexus-warning: #f59e0b;
            --nexus-dark: #0f172a;
            --nexus-light: #f8fafc;
            --nexus-gray: #64748b;
            --nexus-border: #e2e8f0;
            --gradient-1: linear-gradient(135deg, #2563eb 0%, #0ea5e9 100%);
            --gradient-2: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: white;
            overflow: hidden;
            height: 100vh;
            width: 100vw;
        }

        /* Container Principal */
        .nexus-container {
            height: 100vh;
            width: 100vw;
            display: flex;
            background: white;
            overflow: hidden;
        }

        /* Lado Esquerdo - Formulário */
        .form-side {
            flex: 0 0 40%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            background: white;
            position: relative;
            height: 100vh;
            overflow: hidden; /* Remove scroll */
        }

        .form-side::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(37,99,235,0.03) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .form-wrapper {
            max-width: 480px;
            width: 100%;
            position: relative;
            z-index: 2;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        /* Logo */
        .nexus-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .nexus-logo-icon {
            width: 42px;
            height: 42px;
            background: var(--gradient-1);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            box-shadow: 0 8px 16px rgba(37,99,235,0.2);
        }

        .nexus-logo-text h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--nexus-dark);
            margin: 0;
            line-height: 1.2;
        }

        .nexus-logo-text p {
            color: var(--nexus-gray);
            margin: 0;
            font-size: 0.8rem;
        }

        /* Título */
        .page-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--nexus-dark);
            margin-bottom: 0.1rem;
        }

        .page-subtitle {
            color: var(--nexus-gray);
            font-size: 0.85rem;
            margin-bottom: 1rem;
        }

        /* Trial Info */
        .trial-info {
            background: var(--gradient-1);
            color: white;
            padding: 0.75rem 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            box-shadow: 0 4px 10px rgba(37,99,235,0.2);
        }

        .trial-info i {
            font-size: 1.25rem;
        }

        .trial-info strong {
            font-size: 1rem;
        }

        .trial-info p {
            font-size: 0.8rem !important;
        }

        /* Alertas */
        .nexus-alert {
            border-radius: 8px;
            padding: 0.75rem 1rem;
            margin-bottom: 1rem;
            border: none;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.85rem;
        }

        .nexus-alert i {
            font-size: 1rem;
        }

        .nexus-alert-danger {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid var(--nexus-error);
        }

        /* Indicador de Passos */
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 1rem;
            gap: 1.5rem;
        }

        .step {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #f1f5f9;
            color: var(--nexus-gray);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            position: relative;
            font-size: 0.9rem;
        }

        .step.active {
            background: var(--gradient-1);
            color: white;
            box-shadow: 0 4px 10px rgba(37,99,235,0.3);
        }

        .step.completed {
            background: var(--nexus-success);
            color: white;
        }

        .step:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 100%;
            width: 1.5rem;
            height: 2px;
            background: #e2e8f0;
            transform: translateY(-50%);
        }

        .step.completed:not(:last-child)::after {
            background: var(--nexus-success);
        }

        /* Seções do Formulário */
        .form-section {
            display: none;
        }

        .form-section.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateX(5px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .section-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--nexus-dark);
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section-title i {
            color: var(--nexus-primary);
            font-size: 1rem;
        }

        /* Formulário */
        .nexus-form {
            width: 100%;
        }

        .form-floating {
            margin-bottom: 0.75rem;
            width: 100%;
        }

        .form-floating .form-control,
        .form-floating .form-select {
            border: 1.5px solid var(--nexus-border);
            border-radius: 8px;
            padding: 0.75rem 0.75rem;
            height: 52px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            width: 100%;
            background: white;
        }

        .form-floating .form-control:focus,
        .form-floating .form-select:focus {
            border-color: var(--nexus-primary);
            box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
            outline: none;
        }

        .form-floating label {
            padding: 0.75rem 0.75rem;
            color: var(--nexus-gray);
            font-size: 0.9rem;
        }

        .form-floating label i {
            margin-right: 0.4rem;
            color: var(--nexus-primary);
            font-size: 0.85rem;
        }

        /* Grid de Segmentos */
        .segmento-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr); /* Reduzido para 2 colunas */
            gap: 0.5rem;
            margin: 0.5rem 0 1rem;
        }

        .segmento-option {
            border: 1.5px solid var(--nexus-border);
            border-radius: 8px;
            padding: 0.6rem 0.25rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
        }

        .segmento-option:hover {
            border-color: var(--nexus-primary);
            background: rgba(37,99,235,0.05);
            transform: translateY(-1px);
        }

        .segmento-option.selected {
            border-color: var(--nexus-primary);
            background: rgba(37,99,235,0.1);
            box-shadow: 0 2px 8px rgba(37,99,235,0.1);
        }

        .segmento-option i {
            font-size: 1.25rem;
            color: var(--nexus-primary);
            margin-bottom: 0.15rem;
            display: block;
        }

        .segmento-option div {
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--nexus-dark);
        }

        /* Checkboxes */
        .form-check {
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            margin-bottom: 0.75rem;
        }

        .form-check-input {
            width: 16px;
            height: 16px;
            margin-top: 2px;
            cursor: pointer;
            border: 1.5px solid var(--nexus-border);
        }

        .form-check-input:checked {
            background-color: var(--nexus-primary);
            border-color: var(--nexus-primary);
        }

        .form-check-label {
            color: var(--nexus-dark);
            font-size: 0.85rem;
            line-height: 1.4;
            cursor: pointer;
        }

        .form-check-label a {
            color: var(--nexus-primary);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.85rem;
        }

        /* Botões */
        .btn-group {
            display: flex;
            justify-content: space-between;
            gap: 0.75rem;
            margin-top: 1rem;
        }

        .btn-nexus-primary,
        .btn-nexus-secondary,
        .btn-nexus-success {
            padding: 0.7rem 1rem;
            font-size: 0.85rem;
            border-radius: 8px;
            flex: 1;
        }

        .btn-nexus-primary {
            background: var(--gradient-1);
            border: none;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(37,99,235,0.2);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
        }

        .btn-nexus-primary:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 6px 15px rgba(37,99,235,0.3);
        }

        .btn-nexus-secondary {
            background: white;
            border: 1.5px solid var(--nexus-border);
            font-weight: 600;
            color: var(--nexus-dark);
            transition: all 0.3s ease;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
        }

        .btn-nexus-secondary:hover {
            border-color: var(--nexus-primary);
            background: #f8fafc;
            transform: translateY(-1px);
        }

        .btn-nexus-success {
            background: var(--nexus-success);
            border: none;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(22,163,74,0.2);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
        }

        .btn-nexus-success:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 15px rgba(22,163,74,0.3);
        }

        /* Link Voltar */
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            color: var(--nexus-gray);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-top: 0.75rem;
        }

        .back-link:hover {
            color: var(--nexus-primary);
        }

        /* Loading Spinner */
        .spinner {
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Lado Direito - Imagem */
        .image-side {
            flex: 0 0 60%;
            position: relative;
            overflow: hidden;
            height: 100vh;
        }

        .image-container {
            position: relative;
            width: 100%;
            height: 100%;
        }

        .image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .image-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(0,0,0,0.4) 0%, rgba(0,0,0,0.2) 100%);
            z-index: 1;
        }

        /* Badge flutuante */
        .floating-badge {
            position: absolute;
            top: 2rem;
            right: 2rem;
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(10px);
            padding: 0.6rem 1.2rem;
            border-radius: 30px;
            color: white;
            font-size: 0.9rem;
            border: 1px solid rgba(255,255,255,0.2);
            z-index: 3;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .floating-badge i {
            color: #fbbf24;
        }

        /* Cards de segmento */
        .segment-cards {
            position: absolute;
            bottom: 2rem;
            left: 2rem;
            display: flex;
            gap: 0.75rem;
            z-index: 3;
            flex-wrap: wrap;
        }

        .segment-card {
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(10px);
            padding: 0.6rem 1.2rem;
            border-radius: 30px;
            color: white;
            font-size: 0.9rem;
            border: 1px solid rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            gap: 0.6rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        .segment-card i {
            color: var(--nexus-accent);
        }

        .segment-card.highlight {
            background: var(--gradient-1);
            border: none;
        }

        /* Footer texto */
        .footer-text {
            position: absolute;
            bottom: 2rem;
            left: 50%;
            transform: translateX(-50%);
            color: rgba(255,255,255,0.8);
            font-size: 0.8rem;
            z-index: 3;
            background: rgba(0,0,0,0.2);
            padding: 0.4rem 1rem;
            border-radius: 30px;
            backdrop-filter: blur(5px);
            white-space: nowrap;
        }

        /* Animações */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-form {
            animation: fadeInUp 0.5s ease forwards;
        }

        .delay-1 { animation-delay: 0.15s; opacity: 0; animation-fill-mode: forwards; }
        .delay-2 { animation-delay: 0.3s; opacity: 0; animation-fill-mode: forwards; }
        .delay-3 { animation-delay: 0.45s; opacity: 0; animation-fill-mode: forwards; }

        /* Responsividade */
        @media (max-width: 1024px) {
            body { overflow: auto; }
            .nexus-container { flex-direction: column; overflow: auto; }
            .form-side, .image-side { flex: 0 0 auto; height: auto; min-height: 100vh; }
        }
    </style>
</head>
<body>
    <div class="nexus-container">
        <!-- Lado Esquerdo - Formulário -->
        <div class="form-side">
            <div class="form-wrapper">
                <!-- Logo -->
                <div class="nexus-logo animate-form">
                    <div class="nexus-logo-icon">
                        <i class="fas fa-project-diagram"></i>
                    </div>
                    <div class="nexus-logo-text">
                        <h1>NexusFlow</h1>
                        <p>Criação de conta</p>
                    </div>
                </div>
                
                <!-- Título -->
                <div class="page-title animate-form">
                    Cadastre sua empresa
                </div>
                
                <div class="page-subtitle animate-form delay-1">
                    Comece com 3 meses gratuitos
                </div>
                
                <!-- Mensagem de erro -->
                <?php if ($error): ?>
                    <div class="nexus-alert nexus-alert-danger animate-form delay-1">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?php echo htmlspecialchars($error); ?></span>
                    </div>
                <?php endif; ?>
                
                <!-- Trial Info -->
                <div class="trial-info animate-form delay-1">
                    <i class="fas fa-gift"></i>
                    <div>
                        <strong>3 meses gratuitos</strong>
                        <p style="margin:0; font-size:0.8rem; opacity:0.9;">Teste todas as funcionalidades sem compromisso</p>
                    </div>
                </div>
                
                <!-- Indicador de Passos -->
                <div class="step-indicator animate-form delay-1">
                    <div class="step active" data-step="1">1</div>
                    <div class="step" data-step="2">2</div>
                    <div class="step" data-step="3">3</div>
                </div>
                
                <!-- Formulário -->
                <form method="post" class="nexus-form" id="registerForm">
                    <!-- Passo 1: Dados da Empresa -->
                    <div class="form-section active" data-section="1">
                        <div class="section-title">
                            <i class="fas fa-building"></i>
                            Dados da Empresa
                        </div>
                        
                        <div class="row g-2">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="nome_empresa" name="nome_empresa" placeholder="Nome da Empresa" required>
                                    <label for="nome_empresa"><i class="fas fa-store"></i>Nome da Empresa</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="cnpj" name="cnpj" placeholder="CNPJ" required>
                                    <label for="cnpj"><i class="fas fa-id-card"></i>CNPJ</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-floating">
                            <input type="email" class="form-control" id="email_empresa" name="email_empresa" placeholder="E-mail da Empresa" required>
                            <label for="email_empresa"><i class="fas fa-envelope"></i>E-mail da Empresa</label>
                        </div>
                        
                        <div class="row g-2">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="tel" class="form-control" id="telefone" name="telefone" placeholder="Telefone" required>
                                    <label for="telefone"><i class="fas fa-phone"></i>Telefone</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="website" name="website" placeholder="Website (opcional)">
                                    <label for="website"><i class="fas fa-globe"></i>Website</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="btn-group">
                            <button type="button" class="btn-nexus-primary" onclick="nextStep()">
                                Próximo <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Passo 2: Segmento e Endereço -->
                    <div class="form-section" data-section="2">
                        <div class="section-title">
                            <i class="fas fa-map-marker-alt"></i>
                            Segmento e Localização
                        </div>
                        
                        <div class="mb-2">
                            <label class="form-label" style="font-weight:500; color:var(--nexus-dark); font-size:0.85rem;">Selecione o segmento da sua empresa:</label>
                            <div class="segmento-grid">
                                <div class="segmento-option" data-segmento="varejo">
                                    <i class="fas fa-store"></i>
                                    <div>Varejo</div>
                                </div>
                                <div class="segmento-option" data-segmento="marcenaria">
                                    <i class="fas fa-tools"></i>
                                    <div>Marcenaria</div>
                                </div>
                                <!--
                                <div class="segmento-option" data-segmento="industria">
                                    <i class="fas fa-industry"></i>
                                    <div>Indústria</div>
                                </div>
                                <div class="segmento-option" data-segmento="construcao">
                                    <i class="fas fa-hard-hat"></i>
                                    <div>Construção</div>
                                </div>
                                <div class="segmento-option" data-segmento="servicos">
                                    <i class="fas fa-concierge-bell"></i>
                                    <div>Serviços</div>
                                </div>
                                <div class="segmento-option" data-segmento="outros">
                                    <i class="fas fa-ellipsis-h"></i>
                                    <div>Outros</div>
                                </div>
                             -->
                            </div>
                            <input type="hidden" id="segmento" name="segmento" required>
                        </div>
                        
                        <div class="row g-2">
                            <div class="col-md-8">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="endereco" name="endereco" placeholder="Endereço" required>
                                    <label for="endereco"><i class="fas fa-map-pin"></i>Endereço</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="cep" name="cep" placeholder="CEP" required>
                                    <label for="cep"><i class="fas fa-mail-bulk"></i>CEP</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row g-2">
                            <div class="col-md-8">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="cidade" name="cidade" placeholder="Cidade" required>
                                    <label for="cidade"><i class="fas fa-city"></i>Cidade</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <select class="form-select" id="estado" name="estado" required>
                                        <option value="">UF</option>
                                        <option value="AC">AC</option>
                                        <option value="AL">AL</option>
                                        <option value="AP">AP</option>
                                        <option value="AM">AM</option>
                                        <option value="BA">BA</option>
                                        <option value="CE">CE</option>
                                        <option value="DF">DF</option>
                                        <option value="ES">ES</option>
                                        <option value="GO">GO</option>
                                        <option value="MA">MA</option>
                                        <option value="MT">MT</option>
                                        <option value="MS">MS</option>
                                        <option value="MG">MG</option>
                                        <option value="PA">PA</option>
                                        <option value="PB">PB</option>
                                        <option value="PR">PR</option>
                                        <option value="PE">PE</option>
                                        <option value="PI">PI</option>
                                        <option value="RJ">RJ</option>
                                        <option value="RN">RN</option>
                                        <option value="RS">RS</option>
                                        <option value="RO">RO</option>
                                        <option value="RR">RR</option>
                                        <option value="SC">SC</option>
                                        <option value="SP">SP</option>
                                        <option value="SE">SE</option>
                                        <option value="TO">TO</option>
                                    </select>
                                    <label for="estado"><i class="fas fa-map"></i>UF</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="btn-group">
                            <button type="button" class="btn-nexus-secondary" onclick="prevStep()">
                                <i class="fas fa-arrow-left"></i> Anterior
                            </button>
                            <button type="button" class="btn-nexus-primary" onclick="nextStep()">
                                Próximo <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Passo 3: Dados do Administrador -->
                    <div class="form-section" data-section="3">
                        <div class="section-title">
                            <i class="fas fa-user-tie"></i>
                            Dados do Administrador
                        </div>
                        
                        <div class="row g-2">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="nome" name="nome" placeholder="Nome Completo" required>
                                    <label for="nome"><i class="fas fa-user"></i>Nome Completo</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="email" class="form-control" id="email" name="email" placeholder="E-mail" required>
                                    <label for="email"><i class="fas fa-envelope"></i>E-mail</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row g-2">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="password" class="form-control" id="senha" name="senha" placeholder="Senha" required minlength="6">
                                    <label for="senha"><i class="fas fa-lock"></i>Senha (6+ caracteres)</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" placeholder="Confirmar Senha" required>
                                    <label for="confirmPassword"><i class="fas fa-lock"></i>Confirmar Senha</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="aceitou_termos" name="aceitou_termos" required>
                            <label class="form-check-label" for="aceitou_termos">
                                Concordo com os <a href="#">Termos de Uso</a> e <a href="#">Política de Privacidade</a>
                            </label>
                        </div>
                        
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="newsletter" name="newsletter">
                            <label class="form-check-label" for="newsletter">
                                Desejo receber novidades e atualizações
                            </label>
                        </div>
                        
                        <div class="btn-group">
                            <button type="button" class="btn-nexus-secondary" onclick="prevStep()">
                                <i class="fas fa-arrow-left"></i> Anterior
                            </button>
                            <button type="submit" class="btn-nexus-success">
                                <i class="fas fa-check-circle"></i> Criar Conta
                            </button>
                        </div>
                    </div>
                </form>
                
                <!-- Link para login -->
                <div class="text-center">
                    <a href="login.php" class="back-link">
                        <i class="fas fa-arrow-left"></i>
                        Já tenho uma conta
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Lado Direito - Imagem -->
        <div class="image-side">
            <div class="image-container">
                <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=1200&h=900&fit=crop&auto=format" alt="Equipe NexusFlow">
                <div class="image-overlay"></div>
            </div>
            
            <!-- Badge de confiança -->
            <div class="floating-badge">
                <i class="fas fa-star"></i>
                <span><strong>+2.500</strong> empresas confiam</span>
            </div>
            
            <!-- Cards dos segmentos -->
            <div class="segment-cards">
                <div class="segment-card highlight">
                    <i class="fas fa-store"></i>
                    <span>Varejo</span>
                </div>
                <div class="segment-card highlight">
                    <i class="fas fa-tools"></i>
                    <span>Marcenaria</span>
                </div>
                <div class="segment-card">
                    <i class="fas fa-rocket"></i>
                    <span>Expansão</span>
                </div>
            </div>
            
            <!-- Footer text -->
            <div class="footer-text">
                <i class="fas fa-shield-alt me-2"></i>
                3 meses grátis · Sem fidelidade
            </div>
        </div>
    </div>
    
    <script>
        let currentStep = 1;
        
        // Seleção de segmento
        document.querySelectorAll('.segmento-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.segmento-option').forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');
                document.getElementById('segmento').value = this.dataset.segmento;
            });
        });
        
        // Máscaras de entrada
        document.getElementById('cnpj')?.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/^(\d{2})(\d)/, '$1.$2');
            value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
            value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
            value = value.replace(/(\d{4})(\d)/, '$1-$2');
            e.target.value = value.substring(0, 18);
        });
        
        document.getElementById('cep')?.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/^(\d{5})(\d)/, '$1-$2');
            e.target.value = value.substring(0, 9);
        });
        
        document.getElementById('telefone')?.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/^(\d{2})(\d)/, '($1) $2');
            value = value.replace(/(\d{5})(\d)/, '$1-$2');
            e.target.value = value.substring(0, 15);
        });
        
        // Funções de navegação
        function nextStep() {
            if (validateCurrentStep()) {
                currentStep++;
                updateStepDisplay();
            }
        }
        
        function prevStep() {
            currentStep--;
            updateStepDisplay();
        }
        
        function updateStepDisplay() {
            document.querySelectorAll('.step').forEach((step, index) => {
                step.classList.remove('active', 'completed');
                if (index + 1 < currentStep) {
                    step.classList.add('completed');
                } else if (index + 1 === currentStep) {
                    step.classList.add('active');
                }
            });
            
            document.querySelectorAll('.form-section').forEach((section, index) => {
                section.classList.remove('active');
                if (index + 1 === currentStep) {
                    section.classList.add('active');
                }
            });
        }
        
        function validateCurrentStep() {
            const currentSection = document.querySelector(`[data-section="${currentStep}"]`);
            const requiredFields = currentSection?.querySelectorAll('[required]') || [];
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.style.borderColor = 'var(--nexus-error)';
                    isValid = false;
                } else {
                    field.style.borderColor = 'var(--nexus-border)';
                }
            });
            
            if (currentStep === 2 && !document.getElementById('segmento')?.value) {
                alert('Por favor, selecione o segmento da sua empresa.');
                isValid = false;
            }
            
            if (currentStep === 3) {
                const senha = document.getElementById('senha')?.value;
                const confirmSenha = document.getElementById('confirmPassword')?.value;
                
                if (senha?.length < 6) {
                    document.getElementById('senha').style.borderColor = 'var(--nexus-error)';
                    alert('A senha deve ter pelo menos 6 caracteres');
                    isValid = false;
                }
                
                if (senha !== confirmSenha) {
                    document.getElementById('confirmPassword').style.borderColor = 'var(--nexus-error)';
                    alert('As senhas não coincidem');
                    isValid = false;
                }
                
                if (!document.getElementById('aceitou_termos')?.checked) {
                    alert('Você deve aceitar os Termos de Uso para continuar.');
                    isValid = false;
                }
            }
            
            return isValid;
        }
        
        // Submit do formulário
        document.getElementById('registerForm')?.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            
            if (!validateCurrentStep()) {
                e.preventDefault();
                return false;
            }
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner me-2"></span>Criando conta...';
            
            return true;
        });
        
        // Auto-focus no primeiro campo
        document.addEventListener('DOMContentLoaded', function() {
            const firstField = document.getElementById('nome_empresa');
            if (firstField) {
                setTimeout(() => firstField.focus(), 100);
            }
        });
        
        // Prevenir scroll acidental
        if (window.innerWidth > 1024) {
            document.body.addEventListener('wheel', (e) => e.preventDefault(), { passive: false });
            document.body.addEventListener('touchmove', (e) => e.preventDefault(), { passive: false });
        }
    </script>
    
    <?php include __DIR__ . '/components/app-foot.php'; ?>
</body>
</html>