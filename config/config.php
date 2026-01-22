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

/**
 * Classe DB - Singleton para gerenciar conexão MySQL
 * Garante apenas 1 conexão por request
 * NUNCA fecha a conexão, apenas os statements
 */
if (!class_exists('DB')) {
    class DB {
        private static ?mysqli $conn = null;
        
        /**
         * Obtém a conexão única (Singleton)
         * @return mysqli
         */
        public static function conn(): mysqli {
            // Se já existe conexão e está válida, reutiliza
            if (self::$conn !== null) {
                // Verifica se a conexão ainda está ativa
                if (self::$conn->ping()) {
                    return self::$conn;
                } else {
                    // Conexão foi perdida, reseta para criar nova
                    self::$conn = null;
                }
            }
            
            // Cria nova conexão apenas se não existir
            if (self::$conn === null) {
                $servername = getRequiredEnv('DB_HOST');
                $username = getRequiredEnv('DB_USER');
                $password = getRequiredEnv('DB_PASS');
                $dbname = getRequiredEnv('DB_NAME');
                
                // Tenta conectar com timeout reduzido para evitar travamentos
                $conn = @new mysqli($servername, $username, $password, $dbname);
                
                // Verifica a conexão
                if ($conn->connect_error) {
                    $error_msg = $conn->connect_error;
                    
                    // Se for erro de muitas conexões
                    if (strpos($error_msg, 'max_user_connections') !== false) {
                        die("ERRO: Muitas conexões ativas no banco de dados.<br><br>" .
                            "<strong>Solução:</strong><br>" .
                            "1. Execute o script SQL: <code>database/kill_old_connections.sql</code><br>" .
                            "2. Ou execute no MySQL:<br>" .
                            "<code>KILL QUERY WHERE Command = 'Sleep' AND Time > 300;</code><br><br>" .
                            "3. Aguarde alguns segundos e recarregue a página.<br><br>" .
                            "Erro técnico: " . htmlspecialchars($error_msg));
                    }
                    
                    die("Conexão falhou: " . htmlspecialchars($error_msg));
                }
                
                // Configura charset e timeout
                $conn->set_charset("utf8mb4");
                $conn->query("SET SESSION wait_timeout = 60"); // Timeout de 60 segundos
                $conn->query("SET SESSION interactive_timeout = 60");
                
                self::$conn = $conn;
            }
            
            return self::$conn;
        }
        
        /**
         * Reseta a conexão (útil apenas para testes ou casos especiais)
         * ATENÇÃO: Não deve ser usado em produção normal
         */
        public static function reset(): void {
            if (self::$conn !== null) {
                // NÃO fecha a conexão - apenas reseta a referência
                // A conexão será fechada automaticamente pelo PHP ao final do script
                self::$conn = null;
            }
        }
    }
}

// Função auxiliar para compatibilidade com código existente
if (!function_exists('getConnection')) {
    function getConnection(): mysqli {
        return DB::conn();
    }
}

// Cria a variável global $conn para compatibilidade com código existente
// Reutiliza a mesma conexão Singleton - só cria se não existir
if (!isset($GLOBALS['db_conn']) || $GLOBALS['db_conn'] === null) {
    $GLOBALS['db_conn'] = DB::conn();
}
$conn = $GLOBALS['db_conn'];

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

