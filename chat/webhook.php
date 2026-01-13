<?php
header("Content-Type: application/json");

// Dados de conexão com o banco de dados
$host = 'localhost';
$dbname = 'asolucaocerta_platform';
$user = 'asolucaocerta_platform';
$pass = '117910Campi!';

try {
    // Conecta ao banco de dados usando PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Captura o conteúdo bruto recebido no webhook
    $input = file_get_contents('php://input');

    // Decodifica o JSON diretamente, sem base64
    $data = json_decode($input, true);

    // Salva todos os dados recebidos no banco de dados para inspeção
    $stmt = $pdo->prepare("INSERT INTO webhook_logs (received_data) VALUES (:received_data)");
    $stmt->execute([
        ':received_data' => "Raw Input: " . $input . "\nDecoded JSON: " . json_encode($data)
    ]);

    // Verifica se o JSON foi decodificado corretamente e é um evento de mensagem recebida
    if ($data && isset($data['event']) && $data['event'] === 'messages.upsert') {
        $from = $data['data']['key']['remoteJid'];
        $body = $data['data']['message']['conversation'];
        $timestamp = $data['data']['messageTimestamp'];

        // Insere a mensagem recebida no banco de dados
        $stmt = $pdo->prepare("INSERT INTO mensagens (remetente, mensagem, timestamp, tipo) VALUES (:from, :body, :timestamp, 'received')");
        $stmt->execute([
            ':from' => $from,
            ':body' => $body,
            ':timestamp' => $timestamp
        ]);

        echo json_encode(["status" => "success", "message" => "Mensagem recebida e salva com sucesso"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Dados inválidos ou evento não reconhecido"]);
    }
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Erro na conexão com o banco de dados: " . $e->getMessage()]);
} finally {
    // Fecha a conexão com o banco de dados
    $pdo = null;
}
?>
