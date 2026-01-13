<?php
require_once 'config/config.php';

// Configurar headers para JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Lidar com requisições OPTIONS (CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Log para debug
$logFile = 'webhook_whatsapp_log.txt';
$timestamp = date('Y-m-d H:i:s');

function logWebhook($message) {
    global $logFile, $timestamp;
    $logMessage = "[$timestamp] $message" . PHP_EOL;
    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
}

// Verificar se é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido. Use POST.']);
    exit();
}

// Obter dados do POST
$input = file_get_contents('php://input');
$data = json_decode($input, true);

logWebhook("Webhook recebido: " . $input);

// Verificar se os dados foram recebidos corretamente
if (!$data) {
    logWebhook("Erro: Dados JSON inválidos");
    http_response_code(400);
    echo json_encode(['error' => 'Dados JSON inválidos']);
    exit();
}

try {
    // Verificar se os dados vêm em array ou diretamente
    if (isset($data[0])) {
        // Dados vêm em array (estrutura original)
        $items = $data;
    } else {
        // Dados vêm diretamente (nova estrutura)
        $items = [$data];
    }
    
    // Processar cada item
    foreach ($items as $item) {
        // Determinar se os dados estão em 'body' ou diretamente no item
        if (isset($item['body'])) {
            $body = $item['body'];
        } else {
            $body = $item;
        }
        
        // Extrair informações necessárias
        $remoteJid = $body['key']['remoteJid'] ?? null;
        $pushName = $body['pushName'] ?? null;
        $message = $body['message']['conversation'] ?? '';
        
        if (!$remoteJid) {
            logWebhook("Erro: remoteJid não encontrado");
            continue;
        }
        
        // Formatar número do WhatsApp
        // remoteJid vem como: 5516992422354@s.whatsapp.net
        $whatsappNumber = preg_replace('/@.*$/', '', $remoteJid);
        
        // Remover código do país se necessário (55 = Brasil)
        if (strpos($whatsappNumber, '55') === 0) {
            $whatsappNumber = substr($whatsappNumber, 2);
        }
        
        // Adicionar formatação brasileira
        if (strlen($whatsappNumber) == 11) {
            $whatsappFormatted = '(' . substr($whatsappNumber, 0, 2) . ') ' . 
                               substr($whatsappNumber, 2, 5) . '-' . 
                               substr($whatsappNumber, 7);
        } else {
            $whatsappFormatted = $whatsappNumber;
        }
        
        // Verificar se o lead já existe pelo WhatsApp
        $checkSql = "SELECT id FROM lead WHERE whatsapp = ? OR whatsapp LIKE ?";
        $stmt = $conn->prepare($checkSql);
        $whatsappLike = '%' . $whatsappNumber . '%';
        $stmt->bind_param("ss", $whatsappFormatted, $whatsappLike);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            logWebhook("Lead já existe para WhatsApp: $whatsappFormatted");
            continue;
        }
        
        // Criar novo lead
        $nome = $pushName ?: 'Lead WhatsApp';
        $whatsapp = $whatsappFormatted;
        $email = '';
        $cpf = '';
        $obs = "Lead criado automaticamente via WhatsApp.\nMensagem: " . $message;
        $status_id = 1; // Entrada do Lead
        $usuario_id = 1; // Usuário padrão (ajuste conforme necessário)
        $produto_id = '';
        $origem = 'WhatsApp';
        
        $insertSql = "INSERT INTO lead (
            nome, whatsapp, email, cpf, obs, status_id, usuario_id, produto_id, origem, criado_em
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $conn->prepare($insertSql);
        $stmt->bind_param("sssssiiss", 
            $nome, $whatsapp, $email, $cpf, $obs, $status_id, $usuario_id, $produto_id, $origem
        );
        
        if ($stmt->execute()) {
            $leadId = $conn->insert_id;
            logWebhook("Lead criado com sucesso - ID: $leadId, Nome: $nome, WhatsApp: $whatsapp");
        } else {
            logWebhook("Erro ao criar lead: " . $stmt->error);
        }
        
        $stmt->close();
    }
    
    // Resposta de sucesso
    echo json_encode([
        'success' => true,
        'message' => 'Webhook processado com sucesso',
        'timestamp' => $timestamp
    ]);
    
} catch (Exception $e) {
    logWebhook("Erro geral: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Erro interno do servidor',
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>
