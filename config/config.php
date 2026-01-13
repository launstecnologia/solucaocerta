<?php
// Carrega variáveis de ambiente
require_once __DIR__ . '/env_loader.php';

/**
 * Função auxiliar para obter variáveis de ambiente
 * Lança exceção se a variável não estiver definida
 */
function getRequiredEnv($key, $default = null) {
    $value = getenv($key);
    if ($value === false && $default === null) {
        throw new Exception("Variável de ambiente obrigatória não definida: {$key}");
    }
    return $value !== false ? $value : $default;
}

// Configurações do Banco de Dados
$servername = getRequiredEnv('DB_HOST');
$username = getRequiredEnv('DB_USER');
$password = getRequiredEnv('DB_PASS');
$dbname = getRequiredEnv('DB_NAME');

// Cria a conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica a conexão
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

// URL da aplicação
$url = getRequiredEnv('APP_URL');

// Configurações de Email
define('MAIL_HOST', getRequiredEnv('MAIL_HOST', 'smtp.gmail.com'));
define('MAIL_PORT', getRequiredEnv('MAIL_PORT', 587));
define('MAIL_USERNAME', getRequiredEnv('MAIL_USERNAME', ''));
define('MAIL_PASSWORD', getRequiredEnv('MAIL_PASSWORD', ''));
define('MAIL_FROM_EMAIL', getRequiredEnv('MAIL_FROM_EMAIL', 'noreply@asolucaocerta.com.br'));
define('MAIL_FROM_NAME', getRequiredEnv('MAIL_FROM_NAME', 'Solução Certa'));
define('MAIL_ENCRYPTION', getRequiredEnv('MAIL_ENCRYPTION', 'tls'));

// Configurações de ambiente
$app_env = getRequiredEnv('APP_ENV', 'production');
$debug = getRequiredEnv('DEBUG', 'false') === 'true' || getRequiredEnv('DEBUG', 'false') === '1';

// Configuração de exibição de erros
if ($debug) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

?>
