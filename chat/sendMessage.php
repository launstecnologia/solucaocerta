<?php
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);
$message = $data['message'] ?? '';
$number = $data['number'] ?? '';

if ($message && $number) {
    // Conecte ao banco de dados
    $pdo = new PDO('mysql:host=localhost;dbname=asolucaocerta_platform', 'asolucaocerta_platform', '117910Campi!');

    // Insira a mensagem no banco de dados como uma mensagem "enviada"
    $stmt = $pdo->prepare("INSERT INTO mensagens (remetente, mensagem, timestamp, tipo) VALUES (:number, :message, :timestamp, 'sent')");
    $stmt->execute([
        ':number' => $number,
        ':message' => $message,
        ':timestamp' => time()
    ]);

    // Configurações para a API da Evolution
    $instanceName = "solucaocerta"; // Substitua pelo nome da sua instância
    $token = "k334bvuk6t88wdxsyy8njl"; // Substitua pelo seu token

    // Estrutura de dados da mensagem
    $messageData = [
        "number" => $number,
        "textMessage" => ["text" => $message]
    ];

    // Enviar a mensagem usando a API correta
    $url = "https://api.ovortex.tech/message/sendText/{$instanceName}";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "ApiKey: $token",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($messageData));

    // Executa a requisição e captura a resposta
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Verifique se o envio foi bem-sucedido e retorne a resposta para o frontend
    if ($httpCode === 200) {
        echo json_encode(["success" => true, "message" => "Mensagem enviada com sucesso", "response" => json_decode($response)]);
    } else {
        echo json_encode(["success" => false, "error" => "Falha ao enviar mensagem via API", "http_code" => $httpCode, "response" => $response]);
    }
} else {
    echo json_encode(["success" => false, "error" => "Mensagem ou número não fornecido"]);
}
