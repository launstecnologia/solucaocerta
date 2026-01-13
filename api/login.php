<?php
// Configuração CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Max-Age: 86400"); // Cache por 1 dia

// Responder a requisições OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawData = file_get_contents('php://input');
    $data = json_decode($rawData, true);

    if (!empty($data) && isset($data['telefone'])) {
        $telefone = $data['telefone'];
        $telefoneFormatado = preg_replace('/[^0-9]/', '', $telefone);
        $telefoneComCodigo = "55" . $telefoneFormatado;

        // Ajuste no SQL para retornar id, nome, cpf, e email
        $sql = "SELECT id, nome, cpf, email FROM representante WHERE 
                    REPLACE(REPLACE(REPLACE(REPLACE(telefone1, '(', ''), ')', ''), '-', ''), ' ', '') = ? 
                OR REPLACE(REPLACE(REPLACE(REPLACE(telefone2, '(', ''), ')', ''), '-', ''), ' ', '') = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $telefoneFormatado, $telefoneFormatado);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $representante = $result->fetch_assoc(); // Agora pega os campos id, nome, cpf, email
            $id_representante = $representante['id'];
            $nome = $representante['nome'];
            $cpf = $representante['cpf'];
            $email = $representante['email'];

            $codigo = random_int(100000, 999999);
            $expira_em = date('Y-m-d H:i:s', strtotime('+5 minutes'));

            $insert_sql = "INSERT INTO login_codes (id_representante, codigo, expira_em) VALUES (?, ?, ?)";
            $stmt_insert = $conn->prepare($insert_sql);
            $stmt_insert->bind_param("iss", $id_representante, $codigo, $expira_em);

            if ($stmt_insert->execute()) {
                $mensagem = "Seu código de acesso é: $codigo. Ele é válido por 5 minutos.";
                $instanceName = "solucaocerta";
                $token = "k334bvuk6t88wdxsyy8njl";

                // Payload ajustado para a API
                $payload = json_encode([
                    "number" => $telefoneComCodigo,
                    "textMessage" => [
                        "text" => $mensagem
                    ]
                ]);

                $ch = curl_init("https://api.ovortex.tech/message/sendText/{$instanceName}");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "ApiKey: $token",
                    "Content-Type: application/json"
                ]);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                $responseData = json_decode($response, true);

                if ($httpCode == 200 || (isset($responseData['status']) && $responseData['status'] === 'PENDING')) {
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Código enviado via WhatsApp.',
                        'id_representante' => $id_representante,
                        'nome' => $nome,
                        'cpf' => $cpf,
                        'email' => $email
                    ]);
                } else {
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Erro ao enviar mensagem via WhatsApp.',
                        'response' => $response
                    ]);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Erro ao gerar código de acesso.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Telefone não encontrado.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Informe o telefone no formato JSON.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Método inválido. Use POST.']);
}
