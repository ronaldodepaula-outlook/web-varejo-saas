<?php
// Configuracoes globais do sistema

// Carrega .env se existir (sem dependencias externas)
$envPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';
if (file_exists($envPath)) {
    foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }
        $parts = explode('=', $line, 2);
        if (count($parts) !== 2) {
            continue;
        }
        $key = trim($parts[0]);
        $value = trim($parts[1]);
        $value = trim($value, "\"'");
        if ($key !== '' && getenv($key) === false) {
            putenv($key . '=' . $value);
        }
    }
}

$env = static function (string $key, $default = null) {
    $value = getenv($key);
    if ($value === false || $value === null || $value === '') {
        return $default;
    }
    return $value;
};

return [
    // URLs base do sistema
    'url_base' => $env('APP_URL_BASE', 'https://rdpsolutions.online/saas-multiempresas/public/app/'),
    'api_base' => $env('API_BASE_URL', 'https://rdpsolutions.online/saas-multiempresas/public'),

    // Nome do sistema
    'nome_sistema' => $env('APP_NAME', 'NexusFlow'),

    // Configuracoes de banco de dados
    'db' => [
        'host' => $env('DB_HOST', 'localhost'),
        'dbname' => $env('DB_NAME', 'saas_multiempresas'),
        'user' => $env('DB_USER', 'root'),
        'pass' => $env('DB_PASS', ''),
        'charset' => $env('DB_CHARSET', 'utf8mb4'),
    ],

    // Configuracoes de e-mail
    'email' => [
        'from_name' => $env('MAIL_FROM_NAME', 'NexusFlow Suporte'),
        'from_email' => $env('MAIL_FROM_ADDRESS', 'suporte@nexusflow.com'),
        'smtp_host' => $env('MAIL_HOST', 'smtp.nexusflow.com'),
        'smtp_port' => (int)$env('MAIL_PORT', 587),
        'smtp_user' => $env('MAIL_USER', 'suporte@nexusflow.com'),
        'smtp_pass' => $env('MAIL_PASS', 'SENHA_AQUI'),
        'smtp_secure' => $env('MAIL_SECURE', 'tls'),
    ],

    // Outras configuracoes
    'versao' => $env('APP_VERSION', '1.0.0'),
    'idioma' => $env('APP_LOCALE', 'pt-BR'),
    'timezone' => $env('APP_TIMEZONE', 'America/Sao_Paulo'),
];


