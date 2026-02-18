<?php
session_start();
$config = require __DIR__ . '/funcoes/config.php';
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    
    if (!$email) {
        $error = 'Por favor, informe seu e-mail.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Por favor, informe um e-mail valido.';
    } else {
        $payload = json_encode(['email' => $email]);
        
        $ch = curl_init($config['api_base'] . '/api/v1/esqueci-senha');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: */*',
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);
        
        if ($response === false) {
            $error = 'Falha ao comunicar com a API. ' . $curlErr;
        } else {
            // Tratamento de BOM
            if (is_string($response) && strncmp($response, "\xEF\xBB\xBF", 3) === 0) {
                $response = substr($response, 3);
            }
            
            $data = json_decode($response, true);
            
            if ($httpcode === 200 && isset($data['mensagem'])) {
                $success = $data['mensagem'];
            } elseif ($httpcode === 404 && isset($data['erro'])) {
                $error = $data['erro'];
            } elseif ($httpcode === 500 && isset($data['erro'])) {
                $error = 'Erro ao enviar e-mail de recuperacao. Tente novamente mais tarde.';
            } elseif (isset($data['erro'])) {
                $error = $data['erro'];
            } else {
                $error = 'Erro inesperado. Tente novamente.';
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?php $pageTitle = 'Recuperar Senha - NexusFlow'; include __DIR__ . '/components/app-head.php'; ?>
    
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
            padding: 2rem;
            background: white;
            position: relative;
            height: 100vh;
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
            max-width: 380px;
            width: 100%;
            position: relative;
            z-index: 2;
        }

        /* Logo */
        .nexus-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 2rem;
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

        /* Título */
        .page-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--nexus-dark);
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            color: var(--nexus-gray);
            font-size: 0.95rem;
            margin-bottom: 2rem;
        }

        /* Alertas */
        .nexus-alert {
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border: none;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.95rem;
        }

        .nexus-alert i {
            font-size: 1.25rem;
        }

        .nexus-alert-danger {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid var(--nexus-error);
        }

        .nexus-alert-success {
            background: #dcfce7;
            color: #166534;
            border-left: 4px solid var(--nexus-success);
        }

        /* Info Box */
        .info-box {
            background: #eff6ff;
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            border: 1px solid #dbeafe;
        }

        .info-box i {
            color: var(--nexus-primary);
            font-size: 1.1rem;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .info-box p {
            color: #1e40af;
            font-size: 0.9rem;
            line-height: 1.5;
            margin: 0;
        }

        /* Formulário */
        .nexus-form {
            width: 100%;
        }

        .form-floating {
            margin-bottom: 1rem;
            width: 100%;
        }

        .form-floating .form-control {
            border: 1.5px solid var(--nexus-border);
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

        .form-floating label i {
            margin-right: 0.5rem;
            color: var(--nexus-primary);
        }

        /* Botões */
        .btn-nexus-primary {
            background: var(--gradient-1);
            border: none;
            border-radius: 10px;
            padding: 1rem;
            font-weight: 600;
            color: white;
            width: 100%;
            margin: 1rem 0 0.5rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(37,99,235,0.2);
            font-size: 1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-nexus-primary:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(37,99,235,0.3);
        }

        .btn-nexus-primary:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .btn-nexus-outline {
            background: white;
            border: 1.5px solid var(--nexus-border);
            border-radius: 10px;
            padding: 1rem;
            font-weight: 600;
            color: var(--nexus-dark);
            width: 100%;
            transition: all 0.3s ease;
            font-size: 1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-nexus-outline:hover {
            border-color: var(--nexus-primary);
            background: #f8fafc;
            transform: translateY(-1px);
        }

        /* Link Voltar */
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--nexus-gray);
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }

        .back-link:hover {
            color: var(--nexus-primary);
            transform: translateX(-3px);
        }

        /* Loading Spinner */
        .spinner {
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Estado de Sucesso */
        .success-state {
            text-align: center;
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: #dcfce7;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 1rem auto 1.5rem;
            color: var(--nexus-success);
            font-size: 3rem;
        }

        .email-display {
            background: #f8fafc;
            border-radius: 10px;
            padding: 1rem;
            margin: 1.5rem 0;
            font-weight: 500;
            color: var(--nexus-dark);
            border: 1px solid var(--nexus-border);
            word-break: break-all;
        }

        .timer-text {
            color: var(--nexus-gray);
            font-size: 0.9rem;
            margin: 1rem 0;
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

        /* Cards de segmento */
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

        /* Depoimento */
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

        /* Footer texto */
        .footer-text {
            position: absolute;
            bottom: 2rem;
            left: 50%;
            transform: translateX(-50%);
            color: rgba(255,255,255,0.8);
            font-size: 0.85rem;
            z-index: 3;
            background: rgba(0,0,0,0.2);
            padding: 0.5rem 1rem;
            border-radius: 30px;
            backdrop-filter: blur(5px);
            white-space: nowrap;
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
                        <p>Recuperação de senha</p>
                    </div>
                </div>
                
                <?php if ($success): ?>
                    <!-- Estado de Sucesso -->
                    <div class="success-state animate-form">
                        <div class="success-icon delay-1">
                            <i class="fas fa-check"></i>
                        </div>
                        
                        <h3 class="mb-3 delay-1">E-mail enviado!</h3>
                        
                        <div class="nexus-alert nexus-alert-success delay-2">
                            <i class="fas fa-check-circle"></i>
                            <span><?php echo htmlspecialchars($success); ?></span>
                        </div>
                        
                        <div class="email-display delay-2">
                            <i class="fas fa-envelope me-2" style="color: var(--nexus-primary);"></i>
                            <?php echo htmlspecialchars($_POST['email'] ?? ''); ?>
                        </div>
                        
                        <div class="info-box delay-2">
                            <i class="fas fa-clock"></i>
                            <p>
                                <strong>O link expira em 60 minutos.</strong><br>
                                Se não encontrar o e-mail, verifique sua pasta de spam.
                            </p>
                        </div>
                        
                        <div class="timer-text delay-3">
                            <i class="fas fa-hourglass-half me-2"></i>
                            Não recebeu? <span id="timer">60</span> segundos para reenviar
                        </div>
                        
                        <button class="btn-nexus-outline delay-3" id="resendBtn" onclick="resendEmail()" disabled>
                            <i class="fas fa-redo-alt"></i>
                            Reenviar e-mail
                        </button>
                        
                        <a href="login.php" class="back-link delay-3">
                            <i class="fas fa-arrow-left"></i>
                            Voltar para o login
                        </a>
                    </div>
                
                <?php else: ?>
                    <!-- Formulário de Recuperação -->
                    <div class="page-title animate-form">
                        Recuperar senha
                    </div>
                    
                    <div class="page-subtitle animate-form delay-1">
                        Enviaremos instruções para redefinir sua senha
                    </div>
                    
                    <?php if ($error): ?>
                        <div class="nexus-alert nexus-alert-danger animate-form delay-1">
                            <i class="fas fa-exclamation-circle"></i>
                            <span><?php echo htmlspecialchars($error); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="info-box animate-form delay-1">
                        <i class="fas fa-info-circle"></i>
                        <p>
                            Digite o e-mail cadastrado na sua conta. Enviaremos um link seguro para redefinir sua senha.
                        </p>
                    </div>
                    
                    <form method="post" class="nexus-form" id="recoveryForm">
                        <div class="form-floating animate-form delay-2">
                            <input type="email" 
                                   class="form-control" 
                                   id="email" 
                                   name="email" 
                                   placeholder="seu@email.com" 
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                                   required 
                                   autocomplete="email">
                            <label for="email">
                                <i class="fas fa-envelope"></i>
                                E-mail corporativo
                            </label>
                        </div>
                        
                        <button type="submit" class="btn-nexus-primary animate-form delay-3" id="submitBtn">
                            <i class="fas fa-paper-plane"></i>
                            Enviar link de recuperação
                        </button>
                        
                        <div class="text-center">
                            <a href="login.php" class="back-link animate-form delay-3">
                                <i class="fas fa-arrow-left"></i>
                                Voltar para o login
                            </a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Lado Direito - Imagem (mesma da tela de login) -->
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
                    <i class="fas fa-industry"></i>
                    <span>Marcenaria</span>
                </div>
                <div class="segment-card">
                    <i class="fas fa-rocket"></i>
                    <span>Expansão contínua</span>
                </div>
            </div>
            
            
            
            <!-- Footer text -->

        </div>
    </div>
    
    <script>
        // Timer para reenvio
        <?php if ($success): ?>
        let timeLeft = 60;
        const timerElement = document.getElementById('timer');
        const resendBtn = document.getElementById('resendBtn');
        
        const countdown = setInterval(() => {
            timeLeft--;
            timerElement.textContent = timeLeft;
            
            if (timeLeft <= 0) {
                clearInterval(countdown);
                timerElement.textContent = '0';
                resendBtn.disabled = false;
            }
        }, 1000);
        <?php endif; ?>
        
        // Função para reenviar e-mail
        function resendEmail() {
            const email = '<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>';
            
            if (!email) {
                alert('E-mail não encontrado. Tente novamente.');
                return;
            }
            
            const btn = document.getElementById('resendBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner me-2"></span>Enviando...';
            
            // Simular envio (substitua pela sua chamada real)
            setTimeout(() => {
                location.reload();
            }, 1500);
        }
        
        // Validação do formulário
        document.getElementById('recoveryForm')?.addEventListener('submit', function(e) {
            const email = document.getElementById('email').value.trim();
            const submitBtn = document.getElementById('submitBtn');
            
            if (!email) {
                e.preventDefault();
                alert('Por favor, informe seu e-mail.');
                return false;
            }
            
            if (!isValidEmail(email)) {
                e.preventDefault();
                alert('Por favor, informe um e-mail válido.');
                return false;
            }
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner me-2"></span>Enviando...';
        });
        
        // Validação de e-mail
        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }
        
        // Auto-focus
        document.addEventListener('DOMContentLoaded', function() {
            const emailField = document.getElementById('email');
            if (emailField) {
                emailField.focus();
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
