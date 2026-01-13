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

    if (!empty($data) && isset($data['id_representante'])) {
        $id_representante = $data['id_representante'];

        // Consulta para obter o total de clientes associados ao representante
        $sql = "SELECT COUNT(*) as total_clientes 
                FROM cliente_representante 
                WHERE id_representante = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_representante);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $totalClientes = $row['total_clientes'];

            echo json_encode([
                'status' => 'success',
                'total_clientes' => $totalClientes
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Nenhum cliente encontrado para o representante.'
            ]);
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'ID do representante é obrigatório.'
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Método inválido. Use POST.'
    ]);
}
?>
