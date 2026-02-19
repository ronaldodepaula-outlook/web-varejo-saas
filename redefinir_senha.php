<?php
session_start();
$config = require __DIR__ . '/funcoes/config.php';

$success = '';
$error = '';
$token = $_GET['token'] ?? '';
$email = $_GET['email'] ?? '';
$tokenValid = false;

$validateToken = function ($tokenValue, $emailValue) use ($config) {
    $payload = json_encode([
        'token' => $tokenValue,
        'email' => $emailValue,
    ]);

    $ch = curl_init($config['api_base'] . '/api/v1/password/validar-token');
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

    if ($response === false) {
        return [false, 'Falha ao comunicar com a API. ' . $curlErr];
    }

    if (is_string($response) && strncmp($response, "\xEF\xBB\xBF", 3) === 0) {
        $response = substr($response, 3);
    }

    $data = json_decode($response, true);
    if ($httpcode === 200 && isset($data['valid']) && $data['valid'] === true) {
        return [true, ''];
    }

    return [false, 'Link invalido ou expirado.'];
};

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? $token;
    $email = $_POST['email'] ?? $email;
    $senha = $_POST['senha'] ?? '';
    $senhaConfirmation = $_POST['senha_confirmation'] ?? '';

    if (!$token || !$email) {
        $error = 'Token ou e-mail invalido.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Por favor, informe um e-mail valido.';
    } elseif (!$senha || !$senhaConfirmation) {
        $error = 'Informe a nova senha e a confirmacao.';
    } elseif ($senha !== $senhaConfirmation) {
        $error = 'As senhas nao conferem.';
    } else {
        $payload = json_encode([
            'email' => $email,
            'token' => $token,
            'senha' => $senha,
            'senha_confirmation' => $senhaConfirmation,
        ]);

        $ch = curl_init($config['api_base'] . '/api/v1/password/resetar-senha');
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

        if ($response === false) {
            $error = 'Falha ao comunicar com a API. ' . $curlErr;
        } else {
            if (is_string($response) && strncmp($response, "\xEF\xBB\xBF", 3) === 0) {
                $response = substr($response, 3);
            }

            $data = json_decode($response, true);

            if ($httpcode === 200 && isset($data['mensagem'])) {
                $success = $data['mensagem'];
            } elseif ($httpcode === 422 && isset($data['errors'])) {
                $firstError = '';
                foreach ($data['errors'] as $messages) {
                    if (is_array($messages) && count($messages) > 0) {
                        $firstError = $messages[0];
                        break;
                    }
                }
                $error = $firstError ?: 'Dados invalidos.';
            } elseif (isset($data['erro'])) {
                $error = $data['erro'];
            } else {
                $error = 'Erro inesperado. Tente novamente.';
            }
        }
    }

    if (!$success && $token && $email) {
        $tokenValid = strpos($error, 'Token') === false;
    }
} else {
    if ($token && $email) {
        [$tokenValid, $validateError] = $validateToken($token, $email);
        if (!$tokenValid) {
            $error = $validateError;
        }
    } else {
        $error = 'Link invalido ou expirado.';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?php $pageTitle = 'Redefinir Senha - NexusFlow'; include __DIR__ . '/components/app-head.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

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

        .nexus-container {
            height: 100vh;
            width: 100vw;
            display: flex;
            background: white;
            overflow: hidden;
        }

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

        .nexus-alert i { font-size: 1.25rem; }

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

        .btn-nexus-primary:disabled { opacity: 0.7; cursor: not-allowed; }

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

        .floating-badge i { color: #fbbf24; }

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

        .segment-card i { color: var(--nexus-accent); }

        .segment-card.highlight {
            background: var(--gradient-1);
            border: none;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-form { animation: fadeInUp 0.6s ease forwards; }
        .delay-1 { animation-delay: 0.2s; opacity: 0; animation-fill-mode: forwards; }
        .delay-2 { animation-delay: 0.4s; opacity: 0; animation-fill-mode: forwards; }
        .delay-3 { animation-delay: 0.6s; opacity: 0; animation-fill-mode: forwards; }

        @media (max-width: 1024px) {
            body { overflow: auto; }
            .nexus-container { flex-direction: column; overflow: auto; }
            .form-side, .image-side { flex: 0 0 auto; height: auto; min-height: 100vh; }
        }
    </style>
</head>
<body>
    <div class="nexus-container">
        <div class="form-side">
            <div class="form-wrapper">
                <div class="nexus-logo animate-form">
                    <div class="nexus-logo-icon">
                        <i class="fas fa-project-diagram"></i>
                    </div>
                    <div class="nexus-logo-text">
                        <h1>NexusFlow</h1>
                        <p>Redefinicao de senha</p>
                    </div>
                </div>

                <?php if ($success): ?>
                    <div class="nexus-alert nexus-alert-success animate-form">
                        <i class="fas fa-check-circle"></i>
                        <span><?php echo htmlspecialchars($success); ?></span>
                    </div>
                    <a href="login.php" class="back-link">
                        <i class="fas fa-arrow-left"></i>
                        Voltar para o login
                    </a>
                <?php else: ?>
                    <div class="page-title animate-form">Redefinir senha</div>
                    <div class="page-subtitle animate-form delay-1">Crie uma nova senha para sua conta</div>

                    <?php if ($error): ?>
                        <div class="nexus-alert nexus-alert-danger animate-form delay-1">
                            <i class="fas fa-exclamation-circle"></i>
                            <span><?php echo htmlspecialchars($error); ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if ($tokenValid): ?>
                        <form method="post" class="nexus-form" id="resetForm">
                            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                            <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">

                            <div class="form-floating animate-form delay-2">
                                <input type="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" disabled>
                                <label><i class="fas fa-envelope"></i>E-mail</label>
                            </div>

                            <div class="form-floating animate-form delay-2">
                                <input type="password" class="form-control" id="senha" name="senha" placeholder="Nova senha" required>
                                <label for="senha"><i class="fas fa-lock"></i>Nova senha</label>
                            </div>

                            <div class="form-floating animate-form delay-3">
                                <input type="password" class="form-control" id="senha_confirmation" name="senha_confirmation" placeholder="Confirmar senha" required>
                                <label for="senha_confirmation"><i class="fas fa-lock"></i>Confirmar senha</label>
                            </div>

                            <button type="submit" class="btn-nexus-primary animate-form delay-3" id="submitBtn">
                                <i class="fas fa-save"></i>
                                Salvar nova senha
                            </button>

                            <div class="text-center">
                                <a href="login.php" class="back-link animate-form delay-3">
                                    <i class="fas fa-arrow-left"></i>
                                    Voltar para o login
                                </a>
                            </div>
                        </form>
                    <?php else: ?>
                        <a href="esqueci_senha.php" class="back-link">
                            <i class="fas fa-arrow-left"></i>
                            Ir para recuperar senha
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="image-side">
            <div class="image-container">
                <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=1200&h=900&fit=crop&auto=format" alt="Equipe NexusFlow">
                <div class="image-overlay"></div>
            </div>

            <div class="floating-badge">
                <i class="fas fa-star"></i>
                <span><strong>+2.500</strong> empresas confiam</span>
            </div>

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
                    <span>Expansao continua</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('resetForm')?.addEventListener('submit', function(e) {
            const senha = document.getElementById('senha').value.trim();
            const confirmacao = document.getElementById('senha_confirmation').value.trim();
            const btn = document.getElementById('submitBtn');

            if (!senha || !confirmacao) {
                e.preventDefault();
                alert('Informe a nova senha e a confirmacao.');
                return;
            }
            if (senha !== confirmacao) {
                e.preventDefault();
                alert('As senhas nao conferem.');
                return;
            }
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner me-2"></span>Salvando...';
        });
    </script>

    <?php include __DIR__ . '/components/app-foot.php'; ?>
</body>
</html>
