<?php
/**
 * Carrega variáveis de ambiente do arquivo .env
 */
function loadEnv($path) {
    if (!file_exists($path)) {
        throw new Exception("Arquivo .env não encontrado em: {$path}");
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Remove espaços em branco
        $line = trim($line);
        
        // Ignora linhas vazias e comentários
        if (empty($line) || strpos($line, '#') === 0) {
            continue;
        }

        // Verifica se a linha contém o separador =
        if (strpos($line, '=') === false) {
            continue;
        }

        // Separa chave e valor
        $parts = explode('=', $line, 2);
        
        // Verifica se tem pelo menos a chave
        if (count($parts) < 1 || empty(trim($parts[0]))) {
            continue;
        }
        
        $name = trim($parts[0]);
        $value = isset($parts[1]) ? trim($parts[1]) : '';

        // Remove aspas se existirem
        $value = trim($value, '"\''); 
        
        // Define a variável de ambiente se ainda não estiver definida
        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

// Carrega o arquivo .env
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    loadEnv($envPath);
}

