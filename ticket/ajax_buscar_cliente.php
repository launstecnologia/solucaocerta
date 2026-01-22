<?php
session_start();
require_once '../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$filtros = [];
$parametros = [];
$types = '';

// Construir filtros
if (!empty($data['cnpj'])) {
    $filtros[] = "c.cnpj LIKE ?";
    $parametros[] = "%" . trim($data['cnpj']) . "%";
    $types .= 's';
}

if (!empty($data['cpf'])) {
    $filtros[] = "c.adm_cpf LIKE ?";
    $parametros[] = "%" . trim($data['cpf']) . "%";
    $types .= 's';
}

if (!empty($data['nome_fantasia'])) {
    $filtros[] = "c.nome_fantasia LIKE ?";
    $parametros[] = "%" . trim($data['nome_fantasia']) . "%";
    $types .= 's';
}

if (!empty($data['razao_social'])) {
    $filtros[] = "c.razao_social LIKE ?";
    $parametros[] = "%" . trim($data['razao_social']) . "%";
    $types .= 's';
}

if (!empty($data['cidade'])) {
    $filtros[] = "c.cidade LIKE ?";
    $parametros[] = "%" . trim($data['cidade']) . "%";
    $types .= 's';
}

if (!empty($data['representante'])) {
    $filtros[] = "c.id IN (SELECT id_cliente FROM cliente_representante WHERE id_representante = ?)";
    $parametros[] = (int)$data['representante'];
    $types .= 'i';
}

if (!empty($data['pdv'])) {
    $filtros[] = "c.id IN (SELECT id_cliente FROM brasil_card WHERE pdv LIKE ?)";
    $parametros[] = "%" . trim($data['pdv']) . "%";
    $types .= 's';
}

if (!empty($data['produto'])) {
    $tabelasPermitidas = ['brasil_card', 'parcelex', 'fgts', 'pagseguro', 'soufacil', 'fliper', 'parcela_facil', 'boltcard'];
    if (in_array($data['produto'], $tabelasPermitidas)) {
        $filtros[] = "c.id IN (SELECT id_cliente FROM " . $data['produto'] . ")";
    }
}

$sql = "SELECT c.id, c.nome_fantasia, c.razao_social, c.cidade, c.uf, c.cnpj 
        FROM cliente c";
        
if (!empty($filtros)) {
    $sql .= " WHERE " . implode(" AND ", $filtros);
}

$sql .= " ORDER BY c.nome_fantasia ASC LIMIT 50";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Erro na consulta: ' . $conn->error]);
    exit;
}

if (!empty($parametros)) {
    $stmt->bind_param($types, ...$parametros);
}

$stmt->execute();
$result = $stmt->get_result();

$clientes = [];
while ($row = $result->fetch_assoc()) {
    $clientes[] = $row;
}

$stmt->close();

echo json_encode(['success' => true, 'clientes' => $clientes]);
?>








