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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "status" => "error",
        "message" => "Método não permitido. Use POST."
    ]);
    exit;
}

// Captura os dados enviados no POST
$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['codigo'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Código é obrigatório."
    ]);
    exit;
}

// Captura o código enviado
$codigo = $data['codigo'];

// Consulta no banco de dados para verificar o código
$queryCode = "
    SELECT id_representante, expira_em 
    FROM login_codes 
    WHERE codigo = ? 
    ORDER BY expira_em DESC 
    LIMIT 1";
$stmtCode = $conn->prepare($queryCode);

if (!$stmtCode) {
    echo json_encode([
        "status" => "error",
        "message" => "Erro na consulta SQL: " . $conn->error
    ]);
    exit;
}

$stmtCode->bind_param("s", $codigo);
$stmtCode->execute();
$resultCode = $stmtCode->get_result();

if ($resultCode->num_rows === 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Código inválido ou não encontrado."
    ]);
    exit;
}

$row = $resultCode->fetch_assoc();
$idRepresentante = $row['id_representante'];
$dbExpiraEm = $row['expira_em'];

// Verifica se o código ainda é válido
$currentTimestamp = date('Y-m-d H:i:s');
if ($currentTimestamp > $dbExpiraEm) {
    echo json_encode([
        "status" => "error",
        "message" => "O código expirou."
    ]);
    exit;
}

// Retorna o sucesso e o ID do representante
echo json_encode([
    "status" => "success",
    "message" => "Código verificado com sucesso.",
    "id_representante" => $idRepresentante
]);
