<?php
session_start();
$config = require __DIR__ . '/funcoes/config.php';
$error = '';
$success = $_GET['success'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['password'] ?? '';
    
    if (!$email || !$senha) {
        $error = 'Preencha todos os campos obrigatórios.';
    } else {
        $payload = json_encode(['email' => $email, 'senha' => $senha]);
        $ch = curl_init($config['api_base'] . '/api/login');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if (is_string($response) && strncmp($response, "\xEF\xBB\xBF", 3) === 0) {
            $response = substr($response, 3);
        }
        
        $data = json_decode($response, true);
        
        if ($httpcode === 200 && isset($data['token'], $data['usuario'], $data['empresa'], $data['licenca'])) {
            $_SESSION['user_id'] = $data['usuario']['id_usuario'];
            $_SESSION['authToken'] = $data['token'];
            $_SESSION['usuario'] = $data['usuario'];
            $_SESSION['empresa'] = $data['empresa'];
            $_SESSION['empresa_id'] = $data['empresa']['id_empresa'];
            $_SESSION['segmento'] = $data['segmento'] ?? ($data['empresa']['segmento'] ?? null);
            $_SESSION['licenca'] = $data['licenca'];
            $_SESSION['userEmail'] = $data['usuario']['email'];
            $_SESSION['userRole'] = $data['usuario']['perfil'];
            
            // Mapeamento de segmentos para views
            $segmento = $_SESSION['segmento'];
            $viewMap = [
                'admin' => 'home-admins',
                'varejo' => 'home-varejo',
                'ecommerce' => 'home-iecommerce',
                'alimentacao' => 'home-alimentacao',
                'turismo_hotelaria' => 'home-turismo_hotelaria',
                'imobiliario' => 'home-imobiliario',
                'esportes_lazer' => 'home-esportes_lazer',
                'midia_entretenimento' => 'home-midia_entretenimento',
                'industria' => 'home-industria',
                'construcao' => 'home-construcao',
                'agropecuaria' => 'home-agropecuaria',
                'energia_utilities' => 'home-energia_utilities',
                'logistica_transporte' => 'home-logistica_transporte',
                'financeiro' => 'home-financeiro',
                'contabilidade_auditoria' => 'home-contabilidade_auditoria',
                'seguros' => 'home-seguros',
                'marketing' => 'home-marketing',
                'saude' => 'home-saude',
                'educacao' => 'home-educacao',
                'ciencia_pesquisa' => 'home-ciencia_pesquisa',
                'rh_recrutamento' => 'home-rh_recrutamento',
                'juridico' => 'home-juridico',
                'ongs_terceiro_setor' => 'home-ongs_terceiro_setor',
                'seguranca' => 'home-seguranca',
                'seguranca_patrimonial' => 'home-seguranca_patrimonial',
                'marcenaria' => 'home-marcenaria',
                'outros' => 'home'
            ];
            
            $defaultView = 'home-admin';
            $targetView = $viewMap[$segmento] ?? $defaultView;
            $licencaParam = urlencode(base64_encode(json_encode($data['licenca'])));
            
            header('Location: index.php?view=' . $targetView . '&licenca=' . $licencaParam);
            exit;
        } elseif ($httpcode === 403 && isset($data['message'])) {
            $error = 'Usuário inativo ou e-mail não verificado. Por favor, valide sua conta no e-mail.';
        } elseif ($httpcode === 401 && isset($data['message'])) {
            $error = 'Credenciais inválidas.';
        } elseif (isset($data['message'])) {
            $error = $data['message'];
        } else {
            $error = 'Erro ao realizar login. Tente novamente.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?php $pageTitle = 'Login - NexusFlow'; include __DIR__ . '/components/app-head.php'; ?>
    
    <!-- Font Awesome para ícones adicionais -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --nexus-primary: #2563eb;
            --nexus-secondary: #1e293b;
            --nexus-accent: #0ea5e9;
            --nexus-success: #10b981;
            --nexus-warning: #f59e0b;
            --nexus-dark: #0f172a;
            --nexus-light: #f8fafc;
            --nexus-gray: #64748b;
            --gradient-1: linear-gradient(135deg, #2563eb 0%, #0ea5e9 100%);
            --gradient-2: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: white;
            overflow: hidden;
            height: 100vh;
        }

        /* Container Principal */
        .nexus-login-container {
            height: 100vh;
            width: 100vw;
            display: flex;
            background: white;
            overflow: hidden;
        }

        /* Lado Esquerdo - Formulário */
        .login-form-side {
            flex: 0 0 40%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background: white;
            position: relative;
            overflow-y: auto; /* Permite scroll apenas se necessário */
            height: 100vh;
        }

        .login-form-side::-webkit-scrollbar {
            width: 6px;
        }

        .login-form-side::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .login-form-side::-webkit-scrollbar-thumb {
            background: var(--nexus-gray);
            border-radius: 3px;
        }

        .login-form-side::before {
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

        .login-form-wrapper {
            max-width: 380px;
            width: 100%;
            position: relative;
            z-index: 2;
            padding: 1rem 0;
        }

        /* Logo e Título */
        .nexus-brand {
            margin-bottom: 1.5rem;
            text-align: left;
        }

        .nexus-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.5rem;
        }

        .nexus-logo-icon {
            width: 48px;
            height: 48px;
            background: var(--gradient-1);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.75rem;
            box-shadow: 0 10px 20px rgba(37,99,235,0.2);
        }

        .nexus-logo-text h1 {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--nexus-dark);
            margin: 0;
            line-height: 1.2;
        }

        .nexus-logo-text p {
            color: var(--nexus-gray);
            margin: 0;
            font-size: 0.9rem;
        }

        .nexus-tagline {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--nexus-dark);
            margin: 0.75rem 0 0.25rem;
            line-height: 1.2;
        }

        .nexus-tagline span {
            background: var(--gradient-1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .nexus-description {
            color: var(--nexus-gray);
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
        }

        /* Formulário - AGORA VISÍVEL */
        .nexus-form {
            margin-top: 0.5rem;
            width: 100%;
        }

        .form-floating {
            margin-bottom: 1rem;
            width: 100%;
        }

        .form-floating .form-control {
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            padding: 1rem 0.75rem;
            height: 60px;
            font-size: 1rem;
            transition: all 0.3s ease;
            width: 100%;
        }

        .form-floating .form-control:focus {
            border-color: var(--nexus-primary);
            box-shadow: 0 0 0 4px rgba(37,99,235,0.1);
            outline: none;
        }

        .form-floating label {
            padding: 1rem 0.75rem;
            color: var(--nexus-gray);
            font-size: 1rem;
        }

        .form-check {
            display: flex;
            align-items: center;
        }

        .form-check-input {
            width: 18px;
            height: 18px;
            margin-right: 8px;
            cursor: pointer;
        }

        .form-check-input:checked {
            background-color: var(--nexus-primary);
            border-color: var(--nexus-primary);
        }

        .form-check-label {
            color: var(--nexus-gray);
            font-size: 0.9rem;
            cursor: pointer;
        }

        /* Botão */
        .btn-nexus-primary {
            background: var(--gradient-1);
            border: none;
            border-radius: 10px;
            padding: 1rem;
            font-weight: 600;
            color: white;
            width: 100%;
            margin: 1.5rem 0 0.5rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(37,99,235,0.2);
            font-size: 1rem;
            cursor: pointer;
        }

        .btn-nexus-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(37,99,235,0.3);
        }

        /* Links */
        .nexus-links {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 0.5rem 0;
            font-size: 0.9rem;
        }

        .nexus-links a {
            color: var(--nexus-gray);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .nexus-links a:hover {
            color: var(--nexus-primary);
            text-decoration: underline;
        }

        .nexus-signup {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1.5px solid #e2e8f0;
            font-size: 0.95rem;
        }

        .nexus-signup a {
            color: var(--nexus-primary);
            font-weight: 600;
            text-decoration: none;
        }

        .nexus-signup a:hover {
            text-decoration: underline;
        }

        /* Lado Direito - Imagem */
        .login-image-side {
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

        /* Elementos flutuantes sobre a imagem */
        .floating-badge {
            position: absolute;
            top: 2rem;
            right: 2rem;
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(10px);
            padding: 0.75rem 1.5rem;
            border-radius: 40px;
            color: white;
            font-size: 1rem;
            border: 1px solid rgba(255,255,255,0.2);
            z-index: 3;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        }

        .floating-badge i {
            color: #fbbf24;
        }

        .segment-cards {
            position: absolute;
            bottom: 2rem;
            left: 2rem;
            display: flex;
            gap: 1rem;
            z-index: 3;
            flex-wrap: wrap;
        }

        .segment-card {
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(10px);
            padding: 0.75rem 1.5rem;
            border-radius: 40px;
            color: white;
            font-size: 1rem;
            border: 1px solid rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .segment-card i {
            color: var(--nexus-accent);
        }

        .segment-card.highlight {
            background: var(--gradient-1);
            border: none;
        }

        .testimonial-card {
            position: absolute;
            bottom: 2rem;
            right: 2rem;
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(10px);
            padding: 1.5rem;
            border-radius: 16px;
            color: white;
            max-width: 280px;
            border: 1px solid rgba(255,255,255,0.2);
            z-index: 3;
            box-shadow: 0 4px 25px rgba(0,0,0,0.3);
        }

        .testimonial-card i {
            color: var(--nexus-accent);
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            opacity: 0.9;
        }

        .testimonial-card p {
            font-style: italic;
            margin-bottom: 1rem;
            font-size: 0.95rem;
            line-height: 1.5;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .testimonial-author img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(255,255,255,0.3);
        }

        .testimonial-author div {
            font-size: 0.85rem;
        }

        .testimonial-author strong {
            display: block;
            font-size: 1rem;
            margin-bottom: 0.2rem;
        }

        /* Alertas */
        .nexus-alert {
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border: none;
            font-size: 0.95rem;
        }

        .nexus-alert-danger {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #dc2626;
        }

        .nexus-alert-success {
            background: #dcfce7;
            color: #166534;
            border-left: 4px solid #16a34a;
        }

        /* Responsividade */
        @media (max-width: 1024px) {
            .nexus-login-container {
                flex-direction: column;
                overflow-y: auto;
            }
            
            .login-form-side, .login-image-side {
                flex: 0 0 auto;
                height: auto;
                min-height: 100vh;
            }
            
            body {
                overflow: auto;
            }
        }

        /* Animações */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-form {
            animation: fadeInUp 0.6s ease forwards;
        }

        .delay-1 { animation-delay: 0.2s; opacity: 0; animation-fill-mode: forwards; }
        .delay-2 { animation-delay: 0.4s; opacity: 0; animation-fill-mode: forwards; }
        .delay-3 { animation-delay: 0.6s; opacity: 0; animation-fill-mode: forwards; }
    </style>
</head>
<body>
    <div class="nexus-login-container">
        <!-- Lado Esquerdo - Formulário (AGORA COMPLETO) -->
        <div class="login-form-side">
            <div class="login-form-wrapper">
                <!-- Logo e branding -->
                <div class="nexus-brand">
                    <div class="nexus-logo">
                        <div class="nexus-logo-icon">
                            <i class="fas fa-project-diagram"></i>
                        </div>
                        <div class="nexus-logo-text">
                            <h1>NexusFlow</h1>
                            <p>Sistema de Gestão Multi-Empresas</p>
                        </div>
                    </div>
                    <p><p></p></p>
                    <div class="nexus-tagline">
                       <small> <small>Centralize sua <span>operação</span><br>
                        em um único ambiente </small></small>
                    </div>
                    
                    <div class="nexus-description">
                        Vendas, estoque, financeiro e indicadores integrados para empresas com múltiplas unidades.
                    </div>
                </div>
                
                <!-- Mensagens de Alerta -->
                <?php if ($error): ?>
                    <div class="nexus-alert nexus-alert-danger animate-form delay-1">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="nexus-alert nexus-alert-success animate-form delay-1">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>
                
                <!-- FORMULÁRIO DE LOGIN - COMPLETO E VISÍVEL -->
                <form method="post" class="nexus-form">
                    <!-- Campo de E-mail -->
                    <div class="form-floating animate-form delay-1">
                        <input type="email" class="form-control" id="email" name="email" placeholder="seu@email.com" required autocomplete="username" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        <label for="email">
                            <i class="fas fa-envelope me-2"></i>E-mail corporativo
                        </label>
                    </div>
                    
                    <!-- Campo de Senha -->
                    <div class="form-floating animate-form delay-2">
                        <input type="password" class="form-control" id="password" name="password" placeholder="Senha" required autocomplete="current-password">
                        <label for="password">
                            <i class="fas fa-lock me-2"></i>Senha
                        </label>
                    </div>
                    
                    <!-- Links e opções -->
                    <div class="nexus-links animate-form delay-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="remember" name="remember">
                            <label class="form-check-label" for="remember">
                                Manter conectado
                            </label>
                        </div>
                        <a href="esqueci_senha.php">
                            <i class="fas fa-key me-1"></i>
                            Esqueci minha senha
                        </a>
                    </div>
                    
                    <!-- Botão de Login -->
                    <button type="submit" class="btn-nexus-primary animate-form delay-3">
                        <i class="fas fa-sign-in-alt me-2"></i>
                        Acessar NexusFlow
                    </button>
                </form>
                
                <!-- Link para cadastro -->
                <div class="nexus-signup animate-form delay-3">
                    <p class="mb-0">
                        Não tem uma conta?
                        <a href="cadastro.php">
                            Cadastre-se gratuitamente <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Lado Direito - Imagem -->
        <div class="login-image-side">
            <div class="image-container">
                <!-- Imagem multi-segmentos com pessoas e ambiente de trabalho -->
                <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=1200&h=900&fit=crop&auto=format" alt="Equipe multi-segmentos NexusFlow">
                <div class="image-overlay"></div>
            </div>
            
            <!-- Badge de confiança -->
            <div class="floating-badge">
                <i class="fas fa-star"></i>
                <span><strong>+2.500</strong> empresas confiam</span>
            </div>
            
            <!-- Cards dos segmentos atuais -->
            <div class="segment-cards">
                <div class="segment-card highlight">
                    <i class="fas fa-store"></i>
                    <span>Varejo</span>
                </div>
                <div class="segment-card highlight">
                    <i class="fas fa-industry"></i>
                    <span>Marcenaria</span>
                </div>
                <div class="segment-card">
                    <i class="fas fa-rocket"></i>
                    <span>Expansão contínua</span>
                </div>
            </div>
            
            <!-- Depoimento -->
            
        </div>
    </div>
    
    <script>
        function showForgotPassword() {
            window.location.href = 'esqueci_senha.php';
        }

        // Prevenir scroll acidental apenas em desktop
        if (window.innerWidth > 1024) {
            document.body.addEventListener('wheel', (e) => {
                e.preventDefault();
            }, { passive: false });

            document.body.addEventListener('touchmove', (e) => {
                e.preventDefault();
            }, { passive: false });
        }
    </script>
    
    <?php include __DIR__ . '/components/app-foot.php'; ?>
</body>
</html>