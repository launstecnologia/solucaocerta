<?php
require_once '../config/config.php'; // conexão com o banco
$logFile = 'webhook_log.txt';
$input = file_get_contents('php://input');
$data = json_decode($input, true);
$timestamp = date('Y-m-d H:i:s');

$logEntry = "[$timestamp] Webhook recebido:\n";
$logEntry .= json_last_error() === JSON_ERROR_NONE ? print_r($data, true) : "Dados inválidos: $input\n";
$logEntry .= "\n--------------------------\n";
file_put_contents($logFile, $logEntry, FILE_APPEND);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo "JSON inválido";
    exit;
}

try {
    // Pega dados do webhook
    $nome = $data['messages'][0]['sender']['name'] ?? null;
    $whatsapp = $data['contact_inbox']['source_id'] ?? null;
    $obs = $data['messages'][0]['content'] ?? null;

    // Ajuste se precisar preencher outros campos obrigatórios
    $email = '';
    $cpf = '';
    $status_id = 1;      // ou o ID padrão que desejar
    $usuario_id = 1;     // ou associe com base na origem
    $produto_id = 1;     // idem acima

    // Evita insert se não vier nome ou telefone
    if (!$nome || !$whatsapp) {
        throw new Exception("Nome ou WhatsApp ausente.");
    }

    // Insere no banco
    $sql = "INSERT INTO lead 
            (nome, whatsapp, email, cpf, obs, status_id, usuario_id, produto_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssii", $nome, $whatsapp, $email, $cpf, $obs, $status_id, $usuario_id, $produto_id);

    if (!$stmt->execute()) {
        throw new Exception("Erro ao inserir lead: " . $stmt->error);
    }

    http_response_code(200);
    echo "Lead inserido com sucesso";

} catch (Exception $e) {
    file_put_contents($logFile, "[$timestamp] Erro: " . $e->getMessage() . "\n", FILE_APPEND);
    http_response_code(500);
    echo "Erro ao processar webhook";
}
?>
